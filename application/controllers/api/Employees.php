<?php
date_default_timezone_set("Asia/Bangkok");
defined('BASEPATH') or exit('No direct script access allowed');

class Employees extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->helper('url');
        $this->load->helper(array('form', 'url'));
        $this->load->library('form_validation');
        $this->load->library('session');
        $this->load->model('crud');
    }

    //HALAMAN UTAMA
    public function index()
    {
        show_error("Cannot Process your request");
    }

    public function read($number = "")
    {
        if ($number != "") {
            $employee = $this->crud->read("employees", [], ["number" => $number]);
            if ($employee) {
                die(json_encode(array(
                    "code" => "200",
                    "results" => $employee,
                    "status" => "success",
                )));
            } else {
                die(json_encode(array(
                    "code" => "200",
                    "results" => array(),
                    "status" => "Not Found",
                )));
            }
        } else {
            show_error("Cannot Process your request");
        }
    }
}
