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
    public function index($token = "")
    {
        if ($token == "") {
            $this->load->view('template/header_mobile');
            $this->load->view('mobile/404');
        } else {
            $users_m = $this->crud->read("users_m", [], ["token" => $token]);

            $this->db->select('a.*,
                c.name as division_name, 
                d.name as departement_name, 
                e.name as departement_sub_name,
                e.type, 
                g.name as position_name,
                h.name as contract_name,
                i.name as source_name,
                j.name as group_name,
                k.name as religion_name,
                l.name as marital_name');
            $this->db->from('employees a');
            $this->db->join('notifications b', "a.id = b.table_id and b.table_name = 'employees'", 'left');
            $this->db->join('divisions c', 'c.id = a.division_id');
            $this->db->join('departements d', 'd.id = a.departement_id');
            $this->db->join('departement_subs e', 'e.id = a.departement_sub_id');
            $this->db->join('agreements f', 'a.number = f.number and f.status = 0');
            $this->db->join('positions g', 'g.id = a.position_id', 'left');
            $this->db->join('contracts h', 'h.id = a.contract_id', 'left');
            $this->db->join('sources i', 'i.id = a.source_id', 'left');
            $this->db->join('groups j', 'j.id = a.group_id', 'left');
            $this->db->join('religions k', 'k.id = a.religion_id', 'left');
            $this->db->join('maritals l', 'l.id = a.marital_id', 'left');
            $this->db->where('a.deleted', 0);
            $this->db->where('a.number', $users_m->number);
            $this->db->order_by('a.name', 'ASC');

            $employee = $this->db->get()->row();

            $data['number'] = $users_m->number;
            $data['employee'] = $employee;
            $data['service'] = $this->readService($employee->date_sign);

            $this->load->view('template/header_mobile');
            $this->load->view('mobile/employees', $data);
        }
    }

    //GET DATE SERVICE
    public function readService($dateSign = "")
    {
        if ($dateSign == "") {
            $date = $this->input->post('date');
        } else {
            $date = $dateSign;
        }

        $start  = date_create($date);
        $end = date_create(); // waktu sekarang
        $diff  = date_diff($start, $end);
        $d = $diff->d . ' Days ';

        if ($diff->y == 0) {
            $y = '';
        } else {
            $y = $diff->y . ' Years, ';
        }

        if ($diff->m == 0) {
            $m = '';
        } else {
            $m = $diff->m . ' Month, ';
        }

        if ($dateSign == "") {
            echo $y . $m . $d;
        } else {
            return $y . $m . $d;
        }
    }
}
