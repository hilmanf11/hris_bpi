<?php
date_default_timezone_set("Asia/Bangkok");
defined('BASEPATH') or exit('No direct script access allowed');

class Auth extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->helper('url');
        $this->load->helper(array('form', 'url'));
        $this->load->library('form_validation');
        $this->load->library('session');
        $this->load->model('crud');
        $this->load->model('emails');

        //VALIDASI FORM
        $this->form_validation->set_rules('number', 'Employee ID', 'required|min_length[5]|max_length[50]');
        $this->form_validation->set_rules('email', 'Email', 'required|min_length[5]|max_length[50]');
        $this->form_validation->set_rules('password', 'Password', 'required|min_length[5]|max_length[30]');
        //$this->form_validation->set_rules('api_key', 'API', 'required|is_unique[users.api_key]');
    }

    //HALAMAN UTAMA
    public function index()
    {
        show_error("Cannot Process your request");
    }

    function autoid($table)
    {
        $date = date("Ymd");
        $sql = $this->db->query("SELECT max(`id`) as kode FROM $table WHERE id like '%$date%'");
        $row = $sql->row();
        $kode = $row->kode;

        if ($kode == NULL) {
            $autoid = $date . sprintf("%06s", $kode + 1);
        } else {
            $autoid = (int) $kode + 1;
        }

        return $autoid;
    }

    public function register()
    {
        header('Content-Type: application/json; charset=utf-8');
        
        $this->output->delete_cache();
        if ($this->input->post()) {
            if ($this->form_validation->run() == TRUE) {
                $post = $this->input->post();
                $employee = $this->crud->read("employees", [], ["number" => $post['number'], "status" => 0]);
                $users= $this->crud->read("users", [], ["number" => $post['number'], "status" => 0]);
                $users_email = $this->crud->read("users", [], ["email" => $post['email'], "status" => 0]);

                if (!empty($employee)) {
                    if (empty($users)) {
                        if (empty($users_email)) {
                            $id_users = $this->autoid("users");

                            $permitted_chars = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
                            $string = explode(' ', strtolower($employee->name));
                            $username = $string[0] . "_" . substr(str_shuffle($permitted_chars), 0, 4);
                            $api_key = substr(str_shuffle($permitted_chars), 0, 30);

                            $position = $this->crud->read("positions", [], ["id" => $employee->position_id]);

                            $dataUsers = [
                                "id" => $id_users,
                                "created_by" => "admin",
                                "created_date" => date('Y-m-d H:i:s'),
                                "departement_id" => $employee->departement_id,
                                "number" => $post['number'],
                                "name" => $employee->name,
                                "description" => "MOBILE",
                                "email" => $post['email'],
                                "username" => $username,
                                "phone" => $employee->mobile_phone,
                                "position" => $position->name,
                                "access" => "1",
                                "password" => $post['password'],
                                "api_key" => $api_key,
                            ];

                            if ($this->db->insert("users", $dataUsers)) {
                                $this->crud->logs("Create", json_encode($dataUsers), "users");
                                die(json_encode(array("title" => "Success", "message" => "Success to Registered", "theme" => "success", "results" => array("api_key" => $api_key))));
                            } else {
                                show_error("Failed to Register");
                            }
                        } else {
                            die(json_encode(array("title" => "Duplicate", "message" => "Your Email has been registered", "theme" => "success", "results" => array("api_key" => $users_email->api_key))));
                        }
                    } else {
                        die(json_encode(array("title" => "Duplicate", "message" => "Your Employee ID has been registered", "theme" => "error", "results" => array("api_key" => $users->api_key))));
                    }
                } else {
                    show_error("Your Employee ID Not Found");
                }
            } else {
                show_error(validation_errors());
            }
        } else {
            show_error("Cannot Process your request");
        }
    }

    public function forgotPassword()
    {
        $this->output->delete_cache();
        if ($this->input->post()) {
            $post = $this->input->post();
            $users_m = $this->crud->read("users_m", [], ["email" => $post['email'], "status" => 0]);

            $karakter = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz123456789';
            $shuffle  = substr(str_shuffle($karakter), 0, 10);

            $newPassword = $shuffle;

            if ($users_m) {
                $data = [
                    "updated_date" => date('Y-m-d H:i:s'),
                    "password" => md5($newPassword),
                ];

                if ($this->db->update("users_m", $data, ["id" => $users_m->id])) {
                    $this->crud->logs("Reset Password", json_encode($data), "users_m");
                    $this->emails->emailForgotMobile($post['email'], $newPassword);
                    die(json_encode(array("title" => "Success", "message" => "Success to send your Email", "theme" => "success")));
                } else {
                    show_error("Failed to Register");
                }
            } else {
                die(json_encode(array("title" => "Registered", "message" => "Your Email not found", "theme" => "info")));
            }
        } else {
            show_error("Cannot Process your request");
        }
    }
}
