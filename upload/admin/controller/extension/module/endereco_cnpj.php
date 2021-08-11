<?php

class ControllerExtensionModuleEnderecoCnpj extends Controller {

    private $error = array();

    public function index() {
        $this->load->language('extension/module/endereco_cnpj');

        $this->document->setTitle($this->language->get('heading_title'));

        $this->load->model('setting/setting');

        if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
            $this->model_setting_setting->editSetting('module_endereco_cnpj', $this->request->post);

            $this->session->data['success'] = $this->language->get('text_success');

            $this->response->redirect($this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=module', true));
        }

        if (isset($this->error['warning'])) {
            $data['error_warning'] = $this->error['warning'];
        } else {
            $data['error_warning'] = '';
        }

        $data['breadcrumbs'] = array();

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_home'),
            'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'], true)
        );

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_extension'),
            'href' => $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=module', true)
        );

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('heading_title'),
            'href' => $this->url->link('extension/module/endereco_cnpj', 'user_token=' . $this->session->data['user_token'], true)
        );

        $data['action'] = $this->url->link('extension/module/endereco_cnpj', 'user_token=' . $this->session->data['user_token'], true);

        $data['cancel'] = $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=module', true);
        $this->load->model('customer/custom_field');
        $data['custom_fields'] = $this->model_customer_custom_field->getCustomFields();
        $data['link_custom_field'] = $this->url->link('customer/custom_field', 'user_token=' . $this->session->data['user_token'], true);

        if (isset($this->request->post['module_endereco_cnpj'])) {
            $data['module_endereco_cnpj'] = $this->request->post['module_endereco_cnpj'];
        } else {
            $data['module_endereco_cnpj'] = $this->config->get('module_endereco_cnpj');
        }
        if (isset($this->request->post['module_endereco_cnpj_status'])) {
            $data['module_endereco_cnpj_status'] = $this->request->post['module_endereco_cnpj_status'];
        } else {
            $data['module_endereco_cnpj_status'] = $this->config->get('module_endereco_cnpj_status');
        }

        $data['header'] = $this->load->controller('common/header');
        $data['column_left'] = $this->load->controller('common/column_left');
        $data['footer'] = $this->load->controller('common/footer');

        $this->response->setOutput($this->load->view('extension/module/endereco_cnpj', $data));
    }

    protected function validate() {
        if (!$this->user->hasPermission('modify', 'extension/module/endereco_cnpj')) {
            $this->error['warning'] = $this->language->get('error_permission');
        }

        return !$this->error;
    }

    public function install() {

        $this->load->model('setting/event');
        $this->model_setting_event->addEvent('customer_address_cnpj', 'catalog/model/account/customer/deleteLoginAttempts/after', 'event/endereco_cnpj');
    }

    public function uninstall() {

        $this->load->model('setting/event');
        $this->model_setting_event->deleteEventByCode('customer_address_cnpj');
    }

}
