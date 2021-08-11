<?php
class ControllerEventEnderecoCnpj extends Controller {

    // model/account/customer/deleteLoginAttempts/after
    public function index(&$route, &$args, &$output) {
        if ($this->config->get('module_endereco_cnpj_status')) {

            $customer_info = $this->model_account_customer->getCustomer($this->customer->getId());
            $cnpj = preg_replace("/[^0-9]/", "", json_decode($customer_info['custom_field'], true)[$this->config->get('module_endereco_cnpj')]); // CAMPO PERSONALIZADO CNPJ

            if (isset($this->request->get['route']) && ($this->request->get['route'] == 'account/login' || $this->request->get['route'] == 'checkout/login/save')) {
                $this->load->model('account/customer');

                if (!$this->getTotalCustomersByAddrresId($this->customer->getId())) {
                    $this->cnpj($cnpj);
                }
            }
        }
    }

    public function cnpj($cnpj) {

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, "https://www.receitaws.com.br/v1/cnpj/" . $cnpj);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $resp = curl_exec($ch);
        curl_close($ch);
        $json = json_decode($resp, true);
        $code = $this->cep($json['cep']);

        $zone_query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "zone` WHERE code = '" . $this->db->escape($code) . "'");

        if ($zone_query->num_rows) {
            $zone_id = $zone_query->row['zone_id'];
        }
        $this->db->query("INSERT INTO " . DB_PREFIX .
                "address SET customer_id = '" . (int) $this->customer->getId() .
                "', firstname = '" . $this->db->escape($this->customer->getFirstName()) .
                "', lastname = '" . $this->db->escape($json['fantasia']) .
                "', company = '" . $this->db->escape($json['complemento']) .
                "', address_1 = '" . $this->db->escape($json['logradouro'] . ',' . $json['numero']) .
                "', address_2 = '" . $this->db->escape($json['bairro']) .
                "', postcode = '" . $this->db->escape($json['cep']) .
                "', city = '" . $this->db->escape($json['municipio']) .
                "', zone_id = '" . (int) $zone_id . "',"
                . " country_id = '30'");

        $this->db->query("UPDATE " . DB_PREFIX . "customer SET address_id = '" . (int) $this->db->getLastId() . "' WHERE customer_id = '" . (int) $this->customer->getId() . "'");
    }

    public function cep($cep) {

        $cep1 = preg_replace("/[^0-9]/", "", $cep);
        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => 'viacep.com.br/ws/' . $cep1 . '/json/',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'GET',
        ));

        $dados = curl_exec($curl);
        $json = json_decode($dados, true);
        curl_close($curl);

        return $json['uf'];
    }

    public function getTotalCustomersByAddrresId($customer_id) {
        $query = $this->db->query("SELECT COUNT(*) AS total FROM `" . DB_PREFIX . "address` WHERE customer_id = '" . (int) $customer_id . "'");

        return $query->row['total'];
    }

}
