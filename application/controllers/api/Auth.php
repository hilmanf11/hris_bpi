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

    public function login()
    {
        $this->output->delete_cache();
        if ($this->input->post()) {
            $post = $this->input->post();
            $password = md5($post['password']);
            $user = $this->crud->read("users_m", [], [
                "deleted" => 0,
                "email" => $post['email'],
                "password" => $password,
            ]);

            if ($user) {
                if ($user->status == 1) {
                    die(json_encode(array(
                        "title" => "Not Active",
                        "message" => "Cannot Login because your account is not active",
                        "theme" => "info",
                        "results" => array(
                            "number" => $user->number,
                            "email" => $post['email'],
                            "token" => $user->token,
                            "device_id" => $user->device_id,
                            "status" => "Not Active"
                        )
                    )));
                } else {
                    $this->db->update("users_m", ["device_id" => $post['device_id']], ["id" => $user->id]);
                    die(json_encode(array(
                        "title" => "Login",
                        "message" => "Login Success",
                        "theme" => "success",
                        "results" => array(
                            "number" => $user->number,
                            "email" => $post['email'],
                            "token" => $user->token,
                            "device_id" => $user->device_id,
                            "status" => "Active"
                        )
                    )));
                }
            } else {
                die(json_encode(array(
                    "title" => "Not Exist",
                    "message" => "Email or Password is wrong",
                    "theme" => "info",
                    "results" => []
                )));
            }
        } else {
            show_error("Cannot Process your request");
        }
    }

    public function register()
    {
        $this->output->delete_cache();
        if ($this->input->post()) {
            if ($this->form_validation->run() == TRUE) {
                $post = $this->input->post();
                $employee = $this->crud->read("employees", [], ["number" => $post['number'], "status" => 0]);
                $users_m = $this->crud->read("users_m", [], ["number" => $post['number'], "status" => 0]);
                $users_email = $this->crud->read("users_m", [], ["email" => $post['email'], "status" => 0]);
                $users = $this->crud->read("users", [], ["number" => $post['number'], "status" => 0]);

                if (!empty($employee)) {
                    if (empty($users_m)) {
                        if (empty($users_email)) {
                            $id = $this->autoid("users_m");
                            $id_users = $this->autoid("users");

                            $permitted_chars = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
                            $token = 'key_' . substr(str_shuffle($permitted_chars), 0, 45);
                            $string = explode(' ', strtolower($employee->name));
                            $username = $string[0] . "_" . substr(str_shuffle($permitted_chars), 0, 4);

                            $dataUsers = [
                                "id" => $id_users,
                                "created_date" => date('Y-m-d H:i:s'),
                                "departement_id" => $employee->departement_id,
                                "number" => $post['number'],
                                "name" => $employee->name,
                                "description" => "MOBILE REGISTER",
                                "email" => $post['email'],
                                "username" => $username,
                                "phone" => $employee->mobile_phone,
                                "position" => "USER MOBILE",
                                "actived" => "1",
                                "access" => "1",
                                "password" => $post['password'],
                            ];

                            $data = [
                                "id" => $id,
                                "created_date" => date('Y-m-d H:i:s'),
                                "departement_id" => $employee->departement_id,
                                "number" => $post['number'],
                                "email" => $post['email'],
                                "password" => md5($post['password']),
                                "token" => $token,
                                "device_id" => $post['device_id'],
                            ];

                            if ($this->db->insert("users_m", $data)) {
                                if (empty($users->number)) {
                                    $this->db->insert("users", $dataUsers);
                                }
                                $this->crud->logs("Create", json_encode($data), "users_m");
                                die(json_encode(array("title" => "Success", "message" => "Success to Registered", "theme" => "success")));
                            } else {
                                show_error("Failed to Register");
                            }
                        } else {
                            die(json_encode(array("title" => "Registered", "message" => "Your Email has been registered", "theme" => "info")));
                        }
                    } else {
                        die(json_encode(array("title" => "Registered", "message" => "Your Employee ID has been registered", "theme" => "info")));
                    }
                } else {
                    die(json_encode(array("title" => "Not Exist", "message" => "Your Employee ID Not Found", "theme" => "info")));
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
