<?php
date_default_timezone_set("Asia/Bangkok");
defined('BASEPATH') or exit('No direct script access allowed');

class Config extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->helper('url');
        $this->load->helper(array('form', 'url'));
        $this->load->library('form_validation');
        $this->load->library('session');
        $this->load->library('Ciqrcode');
        $this->load->model('crud');
    }

    public function index()
    {
        if (empty($this->session->username)) {
            redirect('error_session');
        } elseif ($this->checkuserAccess($this->id_menu()) > 0) {
            $data['button'] = $this->getbutton($this->id_menu());

            //Configuration Data
            $data['config'] = $this->crud->read('config');

            $this->load->view('template/header', $data);
            $this->load->view('admin/config');
        } else {
            redirect('error_access');
        }
    }

    public function update()
    {
        if ($this->input->post()) {
            $post = $this->input->post();
            $uploadLogo = $this->crud->upload('logo', ['png', 'jpg', 'jpeg'], 'assets/image/config/logo/', ["number" => "BANSHU"], "config", "logo");
            $uploadFavicon = $this->crud->upload('favicon', ['png', 'jpg', 'jpeg'], 'assets/image/config/favicon/', ["number" => "BANSHU"], "config", "favicon");
            $uploadLogin = $this->crud->upload('image', ['png', 'jpg', 'jpeg'], 'assets/image/config/login/', ["number" => "BANSHU"], "config", "image");

            $post_final = array_merge($post, ['logo' => @$uploadLogo, 'favicon' => @$uploadFavicon, 'image' => @$uploadLogin]);
            $send = $this->db->update("config", $post_final);
            if ($send) {
                echo json_encode(array("title" => "Good Job", "message" => "Data Updated Successfully", "theme" => "success"));
            } else {
                echo log_message('error', 'There is an error in your system or data');
            }
        } else {
            show_error("Cannot Process your request");
        }
    }

    public function generateQrCode()
    {
        $permitted_chars = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $token = 'QR_' . substr(str_shuffle($permitted_chars), 0, 45);
        $update = $this->db->update("config", ["token" => $token]);

        if ($update) {
            $config = $this->crud->read('config');
            $this->createQrcode($config->token, "assets/image/qrcode/");

            die(json_encode(array("message" => "Success Generated QR Code", "theme" => "success")));
        } else {
            die(json_encode(array("message" => "Failed Generated QR Code", "theme" => "error")));
        }
    }
}
