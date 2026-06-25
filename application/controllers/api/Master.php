<?php
header('Content-Type: application/json; charset=utf-8');

date_default_timezone_set("Asia/Bangkok");
defined('BASEPATH') or exit('No direct script access allowed');

class Master extends CI_Controller
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

    public function departements()
    {
        $departements = $this->crud->query("SELECT id, `number`, `name` FROM departements GROUP BY `name`");
        die(json_encode($departements));
    }

    public function departementSubs($departement_id = "")
    {
        if(!empty($departement_id)){
            $departementSubs = $this->crud->query("SELECT id, `number`, `name` FROM departement_subs WHERE departement_id = '$departement_id' GROUP BY `name`");
            die(json_encode($departementSubs));
        }else{
            show_error("Departement ID is NULL");
        }
    }

    public function employees($departement = "")
    {
        if($departement == "operator"){
            $employees = $this->crud->query("SELECT id, `number`, `name` FROM employees WHERE departement_id = '20231005000001' ORDER BY name ASC");
            die(json_encode($employees));
        }elseif($departement == "qc"){
            $employees = $this->crud->query("SELECT a.id, a.number, a.name, b.name as departement_name, c.name as departement_sub_name FROM employees a JOIN departements b ON a.departement_id = b.id JOIN departement_subs c ON a.departement_sub_id = c.id WHERE a.departement_sub_id IN ('20240528000008','20240528000007','20240528000006','20231220000005','20231005000005') and a.status = 0 ORDER BY name ASC");
            die(json_encode($employees));
        }
    }
}
