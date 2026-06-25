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

    public function reads($api_key = "")
    {
        if (!empty($api_key)) {
            $user = $this->crud->read("users", [], ["api_key" => $api_key]);

            if ($user) {
                if($user->avatar == null || $user->avatar == ""){
                    $avatar = "https://askara.hris-server.com/assets/image/users/default.png";
                }else{
                    $avatar = $user->avatar;
                }

                $this->db->select('a.*, c.name as division_name, d.name as departement_name, e.name as departement_sub_name, f.name as position_name');
                $this->db->from('employees a');
                $this->db->join('notifications b', 'a.id = b.table_id', 'left');
                $this->db->join('divisions c', 'a.division_id = c.id');
                $this->db->join('departements d', 'a.departement_id = d.id');
                $this->db->join('departement_subs e', 'a.departement_sub_id = e.id');
                $this->db->join('positions f', 'a.position_id = f.id');
                $this->db->where('a.status', 0);
                $this->db->where('a.number', $user->number);
                $this->db->order_by('a.name', 'asc');
                $employees = $this->db->get()->result_array();

                die(json_encode(array(
                    "title" => "Success",
                    "message" => "Data Found",
                    "theme" => "success",
                    "results" => array($employees)
                )));
            }else{
                show_error("Api Key Invalid");
            }
        } else {
            show_error("Cannot Process your request");
        }
    }
}
