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

    public function forgotPasswordV2()
    {
        $this->apps = $this->load->database('apps', TRUE);
        header('Content-Type: application/json; charset=utf-8');

        $this->output->delete_cache();
        if ($this->input->post()) {
            $post = $this->input->post();
            $users_m = $this->crud->read("users", [], ["email" => $post['email'], "status" => 0]);

            $karakter = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
            $shuffle  = substr(str_shuffle($karakter), 0, 6);

            $newPassword = $shuffle;

            if ($users_m) {
                $data = [
                    "updated_date" => date('Y-m-d H:i:s'),
                    "password" => $newPassword,
                ];

                if ($this->db->update("users", $data, ["email" => $users_m->email])) {
                    $this->crud->logs("Reset Password", json_encode($data), "users_m");

                    // update ke db mobile
                    $this->apps->where('email', $post['email']);
                    $this->apps->update('users', $data);

                    // Kirim email melalui API
                    $url = "https://app.hris-server.com/mail.php";

                    $ch = curl_init($url);

                    curl_setopt_array($ch, [
                        CURLOPT_RETURNTRANSFER => true,
                        CURLOPT_POST => true,
                        CURLOPT_POSTFIELDS => [
                            'to' => 'forgot_password',
                            'email' => $post['email'],
                            'new_password' => $newPassword,
                        ],
                    ]);

                    $response = curl_exec($ch);
                    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

                    // if (curl_errno($ch)) {
                    //     die(curl_error($ch));
                    //     // log_message('error', curl_error($ch));
                    // }

                    // if ($response === false) {
                    //     die(json_encode([
                    //         "curl_error" => curl_error($ch),
                    //         "http_code" => curl_getinfo($ch, CURLINFO_HTTP_CODE),
                    //     ]));
                    // }

                    // $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

                    // curl_close($ch);

                    // die(json_encode([
                    //     "http_code" => $httpCode,
                    //     "response" => $response,
                    //     "payload" => $payload,
                    // ]));

                    curl_close($ch);

                    $result = json_decode($response, true);


                    if ($httpCode == 200 
                        && isset($result['theme']) 
                        && $result['theme'] === 'success') {
                        die(json_encode([
                            "title" => "Success",
                            "message" => "Success to send your Email",
                            "theme" => "success"
                        ]));
                    } else {
                        log_message('error', 'Email API Response: '.$response);

                        die(json_encode([
                            "title" => "Error",
                            "message" => "Failed to send email",
                            "theme" => "error",
                            "result" => $response
                        ]));
                    }


                    // $this->emails->emailForgotMobile($post['email'], $newPassword);
                    // die(json_encode(array("title" => "Success", "message" => "Success to send your Email", "theme" => "success")));
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

    // REGISTER USER BARU
    public function registerUser()
    {
        $this->apps = $this->load->database('apps', TRUE);
        header('Content-Type: application/json; charset=utf-8');
        
        $this->output->delete_cache();
        if ($this->input->post()) {
            if ($this->form_validation->run() == TRUE) {
                $post = $this->input->post();
                $employee = $this->crud->read("employees", [], ["number" => $post['number'], "status" => 0]);
                $users= $this->crud->read("users", [], ["number" => $post['number'], "status" => 0]);
                $users_email = $this->crud->read("users", [], ["email" => $post['email'], "status" => 0]);

                 // cek apakah data employee kosong ?
                if(empty($employee)) {
                    show_error("Your Employee ID Not Found");
                    return;
                }

                if(!empty($users_email) && $users_email->number != $post['number']) {
                    die(json_encode(array(
                        "title" => "Duplicate", 
                        "message" => "Your Email has been registered with different Employee ID", 
                        "theme" => "error"
                    )));
                }
                
                // check di db hris mobile
                $this->apps->select("*");
                $this->apps->from('users');
                $this->apps->where('email', $post['email']);
                $user_mobile = $this->apps->get()->result_array();
                
                // cek apakah di table hris mobile dan di table users bpi ada data nya ?
                if(!empty($user_mobile) && !empty($users)) {
                    die(json_encode(array(
                        "title" => "Duplicate", 
                        "message" => "Your Account has been registered", 
                        "theme" => "error"
                    )));
                    return;
                } else {
                    $id_users = $this->autoid("users");

                    $permitted_chars = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
                    $string = explode(' ', strtolower($employee->name));
                    $username = $string[0] . "_" . substr(str_shuffle($permitted_chars), 0, 4);
                    $api_key = substr(str_shuffle($permitted_chars), 0, 30);

                    $position = $this->crud->read("positions", [], ["id" => $employee->position_id]);

                    $dataUsers = [
                        "id" => $id_users,
                        "created_date" => date('Y-m-d H:i:s'),
                        "created_by" => "admin",
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
                        "departement_id" => $employee->departement_id,
                    ];

                    // cek apakah user mobile kosong ? maka insert baru jika tidak update
                    if(empty($user_mobile)) {
                        $this->apps->select("*");
                        $this->apps->from('companies');
                        $this->apps->where('number', $post['company']);
                        $companies = $this->apps->get()->result_array();

                        $dataApps = [
                            "email" => $post['email'],
                            "password" => $post['password'],
                            "company" => $post['company'],
                            "link" => $companies[0]['link'],
                            "api_key" => $api_key,
                            "status" => "0",
                            "created_date" => date('Y-m-d H:i:s'),
                        ];

                        $this->apps->insert("users", $dataApps);
                    } else {
                        $updateData = [
                            "password"     => $post['password'],
                            "api_key"      => $api_key,
                            "updated_date" => date('Y-m-d H:i:s'), // jika kolom ini ada
                        ];

                        $this->apps->where('email', $post['email']);
                        $this->apps->update('users', $updateData);
                    }

                    // cek apakah user bpi kosong ? maka insert baru jika tidak update
                    if(empty($users)) {
                        $this->db->insert("users", $dataUsers);
                    } else {
                        $updateData = [
                            "password"     => $post['password'],
                            "api_key"      => $api_key,
                            "updated_by" => $dataUsers['name'], // jika kolom ini ada
                            "updated_date" => date('Y-m-d H:i:s'), // jika kolom ini ada
                        ];

                        $this->db->where('email', $post['email']);
                        $this->db->update('users', $updateData);
                    }

                    $this->crud->logs("Create", json_encode($dataUsers), "users");
                    die(json_encode(array("title" => "Success", "message" => "Success to Registered", "theme" => "success")));
                }
            } else {
                show_error(validation_errors());
            }
        } else {
            show_error("Cannot Process your request");
        }
    }
}
