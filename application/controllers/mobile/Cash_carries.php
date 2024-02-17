<?php
date_default_timezone_set("Asia/Bangkok");
defined('BASEPATH') or exit('No direct script access allowed');

class Cash_carries extends CI_Controller
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
    public function index($token = "")
    {
        if ($token == "") {
            $this->load->view('template/header_mobile');
            $this->load->view('mobile/404');
        } else {
            $users_m = $this->crud->read("users_m", [], ["token" => $token]);
            $employee = $this->crud->read("employees", [], ["number" => $users_m->number]);

            $this->db->select('a.*, b.users_id_to, c.time_in, c.time_out, d.contract_id');
            $this->db->from('cash_carries a');
            $this->db->join('employees d', "a.employee_id = d.id");
            $this->db->join('notifications b', "a.id = b.table_id and b.table_name = 'cash_carries'", 'left');
            $this->db->join('attandances c', "c.number = d.number and a.trans_date = c.date_in", 'left');
            $this->db->where('a.employee_id', $employee->id);
            $this->db->where('d.status', "0");
            $this->db->group_by('a.trans_date');
            $this->db->group_by('a.employee_id');
            $this->db->group_by('a.type');
            $this->db->order_by('a.trans_date', 'DESC');
            $this->db->limit(31);
            $cash_carries = $this->db->get()->result_array();

            $data['number'] = $users_m->number;
            $data['cash_carries'] = $cash_carries;

            $this->load->view('template/header_mobile');
            $this->load->view('mobile/cash_carries', $data);
        }
    }
}
