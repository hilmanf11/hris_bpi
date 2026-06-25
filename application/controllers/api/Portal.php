<?php
header('Content-Type: application/json; charset=utf-8');

date_default_timezone_set("Asia/Bangkok");
defined('BASEPATH') or exit('No direct script access allowed');

class Portal extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->helper('url');
        $this->load->helper(array('form', 'url'));
        $this->load->library('form_validation');
        $this->load->library('session');
        $this->load->model('mobile');
        $this->load->model('crud');
    }

    //HALAMAN UTAMA
    public function index()
    {
        show_error("Cannot Process your request");
    }

    public function readUsers($code_company = "")
    {

        $this->apps = $this->load->database("apps", TRUE);

        if ($code_company != "") {

            $this->apps->select("*");
            $this->apps->from("companies");
            $this->apps->where("number", $code_company);
            $companies = $this->apps->get()->row();

            
            $this->apps->select("api_key, email, status");
            $this->apps->from("users");
            $this->apps->where("link", $companies->link);
            $users = $this->apps->get()->result();

            if($users) {

                foreach ($users as $user) {

                    $this->db->select("name,number,api_key,email");
                    $this->db->from("users");
                    $this->db->where("api_key", $user->api_key);
                    $userWeb = $this->db->get()->row();

                    $data[] = array(
                        "name" => $userWeb->name,
                        "employee_id" => @$userWeb->number,
                        "email" => @$user->email,
                        "status" => @$user->status, // 0 aktif , 1 nonaktif
                    );

                }

                die(json_encode(array(
                    "title" => "Success",
                    "message" => "Data Found",
                    "data" => $data,
                )));
                
            } else {
                die(json_encode(array(
                    "title"=> "Success",
                    "message" => "No Data Found",
                    // "company" => $companies,
                    "data" => $data,
                )));
                
            }
            
        } else {
            show_error("Cannot Process your request");
        }
        
        echo json_encode($send);
    }

    public function readUsersWeb($code_company = "")
    {

        if ($code_company != "") {

            $userWeb = $this->mobile->query("SELECT number as employee_id, name, email, api_key FROM users WHERE api_key IS NULL AND deleted = '0' ORDER BY name ASC");

            die(json_encode(array(
                "title" => "Success",
                "message" => "Data Found",
                "data" => $userWeb,
            )));
            
        } else {
            show_error("Cannot Process your request");
        }
        
        echo json_encode($send);
    }

    public function addUser() {

        header('Content-Type: application/json; charset=utf-8');
        $this->apps = $this->load->database("apps", TRUE);
        
        if ($this->input->post()) {
            $post = $this->input->post();

            $permitted_chars = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
            $api_key = substr(str_shuffle($permitted_chars), 0, 30);

            $this->apps->select("*");
            $this->apps->from("companies");
            $this->apps->where("number", $post['company_code']);
            $company = $this->apps->get()->row();

            $user = $this->crud->read("users", [], ["number" => $post['employee_id']]);

            $data = [
                "email" => @$user->email,
                "password" => $user->password,
                "link" => $company->link,
                "status" => 0,
                "api_key" => $api_key,
            ];

            $dataUpdate = [
                "updated_by" => "admin",
                "updated_date" => date('Y-m-d H:i:s'),
                "api_key" => $api_key,
            ];

            $update = $this->db->update("users", $dataUpdate, ["id" => $user->id]);

            if(!$update){
                die(json_encode(array(
                    "title" => "Failed",
                    "message" => "Add user is failed.",
                )));
            }

            $add = $this->apps->insert('users', $data);

            if($add){
                die(json_encode(array(
                    "title" => "Success",
                    "message" => "Insert database",
                )));
            } else {
                die(json_encode(array(
                    "title" => "Failed",
                    "message" => "Add user is failed.",
                )));
            }

            
        } else {
            // show_error("Cannot Process your request");
            show_error($this->input->post());
        }
        
        echo json_encode($send);
    }

    public function countAccount($code_company = "") {

        $this->apps = $this->load->database("apps", TRUE);
        
        if ($code_company != "") {

            $this->apps->select("*");
            $this->apps->from("companies");
            $this->apps->where("number", $code_company);
            $companies = $this->apps->get()->row();

            
            $this->apps->select("api_key, email, status");
            $this->apps->from("users");
            $this->apps->where("link", $companies->link);
            $userMobile = $this->apps->get()->result();

            $userWeb = $this->mobile->query("SELECT number as employee_id, name, email, api_key FROM users WHERE deleted = '0' ORDER BY name ASC");

            $countUserMobile = count($userMobile);
            $countUserWeb = count($userWeb);

            $data = [
                "count_web" => $countUserWeb,
                "count_mobile" => $countUserMobile,
            ];

            die(json_encode(array(
                "title" => "Success",
                "message" => "Data Found",
                "data" => $data,
            )));
            
        } else {
            show_error("Cannot Process your request");
        }
        
        echo json_encode($send);
    }
}
