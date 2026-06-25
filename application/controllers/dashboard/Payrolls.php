<?php
date_default_timezone_set("Asia/Bangkok");
defined('BASEPATH') or exit('No direct script access allowed');

class Payrolls extends CI_Controller
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

    public function index()
    {
        if ($this->session->username != "") {
            $data['config'] = $this->crud->read('config');
            $data['users'] = $this->crud->reads('users', [], ["actived" => 0, "deleted" => 0], "", "name", "asc");
            $data['session_name'] = $this->session->name;

            $this->load->view('template/header');
            $this->load->view('dashboard/payrolls', $data);
        } else {
            redirect('error_session');
        }
    }

    public function myPayroll()
    {
        $filter_from = base64_decode($this->input->get("filter_from"));
        $filter_to = base64_decode($this->input->get("filter_to"));
        $period_start = date("Y-m", strtotime($filter_from));
        $period_end = date("Y-m", strtotime($filter_to));

        $mp = $this->crud->query("SELECT COUNT(employee_id) as total_mp, SUM(income) as total_bruto, SUM(net_income) as total_income, SUM(ter) as total_ter 
            FROM payrolls 
            WHERE period_start = '$period_start' and period_end = '$period_end'");

        echo json_encode(array("total_mp" => @$mp[0]->total_mp, "total_bruto" => @$mp[0]->total_bruto, "total_income" => @$mp[0]->total_income, "total_ter" => @$mp[0]->total_ter));
    }

    public function chartDepartement()
    {
        $filter_from = base64_decode($this->input->get("filter_from"));
        $filter_to = base64_decode($this->input->get("filter_to"));
        $period_start = date("Y-m", strtotime($filter_from));
        $period_end = date("Y-m", strtotime($filter_to));

        $query = $this->crud->query("SELECT c.name as departement_name, SUM(a.net_income) as income 
            FROM payrolls a
            JOIN employees b ON a.employee_id = b.id
            JOIN departements c ON b.departement_id = c.id
            WHERE a.period_start = '$period_start' and a.period_end = '$period_end' 
            GROUP BY c.name ORDER BY c.name ASC");
        
        $name = array();
        $income = array();
        foreach ($query as $data) {
            $name[] = $data->departement_name;
            $income[] = (int) $data->income;
        }

        die(json_encode(array("name" => $name, "income" => $income)));
    }

    public function chartPosition()
    {
        $filter_from = base64_decode($this->input->get("filter_from"));
        $filter_to = base64_decode($this->input->get("filter_to"));
        $period_start = date("Y-m", strtotime($filter_from));
        $period_end = date("Y-m", strtotime($filter_to));

        $query = $this->crud->query("SELECT position_name, SUM(net_income) as income 
            FROM `payrolls` 
            WHERE period_start = '$period_start' and period_end = '$period_end' 
            GROUP BY position_name ORDER BY position_name ASC");
        
        $name = array();
        $income = array();
        foreach ($query as $data) {
            $name[] = $data->position_name;
            $income[] = (int) $data->income;
        }

        die(json_encode(array("name" => $name, "income" => $income)));
    }

    public function chartGroup()
    {
        $filter_from = base64_decode($this->input->get("filter_from"));
        $filter_to = base64_decode($this->input->get("filter_to"));
        $period_start = date("Y-m", strtotime($filter_from));
        $period_end = date("Y-m", strtotime($filter_to));

        $query = $this->crud->query("SELECT group_name, SUM(net_income) as income 
            FROM `payrolls` 
            WHERE period_start = '$period_start' and period_end = '$period_end' 
            GROUP BY group_name ORDER BY group_name ASC");
        
        $name = array();
        $income = array();
        foreach ($query as $data) {
            $name[] = $data->group_name;
            $income[] = (int) $data->income;
        }

        die(json_encode(array("name" => $name, "income" => $income)));
    }

    public function tableDepartement()
    {
        $filter_from = base64_decode($this->input->get("filter_from"));
        $filter_to = base64_decode($this->input->get("filter_to"));
        $period_start = date("Y-m", strtotime($filter_from));
        $period_end = date("Y-m", strtotime($filter_to));

        $query = $this->crud->query("SELECT c.name, SUM(a.net_income) as total, COUNT(a.employee_id) as total_mp
            FROM payrolls a
            JOIN employees b ON a.employee_id = b.id
            JOIN departements c ON b.departement_id = c.id
            WHERE a.period_start = '$period_start' and a.period_end = '$period_end' 
            GROUP BY c.name ORDER BY SUM(a.net_income) DESC");

        $result['total'] = count($query);
        $result['rows'] = $query;

        die(json_encode($result));
    }

    public function tablePosition()
    {
        $filter_from = base64_decode($this->input->get("filter_from"));
        $filter_to = base64_decode($this->input->get("filter_to"));
        $period_start = date("Y-m", strtotime($filter_from));
        $period_end = date("Y-m", strtotime($filter_to));

        $query = $this->crud->query("SELECT position_name as name, SUM(net_income) as total, COUNT(employee_id) as total_mp
            FROM `payrolls` 
            WHERE period_start = '$period_start' and period_end = '$period_end' 
            GROUP BY position_name ORDER BY SUM(net_income) DESC");

        $result['total'] = count($query);
        $result['rows'] = $query;

        die(json_encode($result));
    }

    public function tableGroup()
    {
        $filter_from = base64_decode($this->input->get("filter_from"));
        $filter_to = base64_decode($this->input->get("filter_to"));
        $period_start = date("Y-m", strtotime($filter_from));
        $period_end = date("Y-m", strtotime($filter_to));
        
        $query = $this->crud->query("SELECT group_name as name, SUM(net_income) as total, COUNT(employee_id) as total_mp
            FROM `payrolls` 
            WHERE period_start = '$period_start' and period_end = '$period_end' 
            GROUP BY group_name ORDER BY SUM(net_income) DESC");

        $result['total'] = count($query);
        $result['rows'] = $query;

        die(json_encode($result));
    }
}
