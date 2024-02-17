<?php
date_default_timezone_set("Asia/Bangkok");
defined('BASEPATH') or exit('No direct script access allowed');

class Change_days extends CI_Controller
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
            if ($users_m) {
                $users = $this->crud->read("users", [], ["number" => $users_m->number]);
                if (empty($this->session->username)) {
                    $this->session->set_userdata(["username" => $users->username]);
                }

                $employee = $this->crud->read("employees", [], ["number" => $users_m->number]);

                $data['employee'] = $employee;
                $data['api_key'] = $token;

                $this->load->view('template/header_mobile');
                $this->load->view('mobile/change_days', $data);
            } else {
                $this->load->view('template/header_mobile');
                $this->load->view('mobile/404');
            }
        }
    }

    public function requestCode()
    {
        $date = date("ymd");

        $this->db->select('max(SUBSTRING(request_code, -4)) as code');
        $this->db->from('change_days');
        $this->db->where('deleted', 0);
        $this->db->where('SUBSTRING(request_code, 4, 6)=', $date);
        $records = $this->db->get()->result_array();

        $requestcode = (int) $records[0]['code'];
        $requestcode++;

        $template = "CD/" . $date . "/";
        $templatefinal = $template . sprintf("%04s", $requestcode);
        return $templatefinal;
    }

    public function lists($token = "")
    {
        if ($token == "") {
            $this->load->view('template/header_mobile');
            $this->load->view('mobile/404');
        } else {
            $users_m = $this->crud->read("users_m", [], ["token" => $token]);
            if ($users_m) {
                //Select Query
                $this->db->select('a.*, 
                    g.users_id_from as status_check,
                    g.users_id_to as status_notification, 
                    g.updated_date as status_date,
                    c.name as division_name,
                    d.name as departement_name,
                    e.name as departement_sub_name,
                    b.number as employee_number,
                    b.name as employee_name,
                    f.name as fullname
                ');
                $this->db->from('change_days a');
                $this->db->join('employees b', 'a.employee_id = b.id');
                $this->db->join('divisions c', 'b.division_id = c.id');
                $this->db->join('departements d', 'b.departement_id = d.id');
                $this->db->join('departement_subs e', 'b.departement_sub_id = e.id');
                $this->db->join('users f', "a.created_by = f.username");
                $this->db->join('notifications g', "a.id = g.table_id and g.table_name = 'change_days'", 'left');
                $this->db->where('b.number', $users_m->number);
                $this->db->where('b.status', "0");
                $this->db->like('a.start', date("Y"));
                $this->db->group_by('a.start');
                $this->db->group_by('a.employee_id');
                $this->db->order_by('a.start', 'DESC');
                $change_days = $this->db->get()->result_array();

                $data['number'] = $users_m->number;
                $data['change_days'] = $change_days;

                $this->load->view('template/header_mobile');
                $this->load->view('mobile/change_days_view', $data);
            } else {
                $this->load->view('template/header_mobile');
                $this->load->view('mobile/404');
            }
        }
    }

    //CREATE DATA
    public function create()
    {
        if ($this->input->post()) {
            $post = $this->input->post();

            $this->db->select('*');
            $this->db->from('change_days');
            $this->db->where("employee_id", $post['employee_id']);
            $this->db->where("start", $post['start']);
            $change_days = $this->db->get()->row();

            if (!empty($change_days)) {
                echo json_encode(array("title" => "Available", "message" => "The Change Days requestion for this employee and start date has been created", "theme" => "error"));
                exit;
            } else {
                $post_final = array(
                    "employee_id" => $post['employee_id'],
                    "request_code" => $this->requestCode(),
                    "start" => $post['start'],
                    "end" => $post['end'],
                    "remarks" => $post['remarks']
                );

                $send = $this->crud->create('change_days', $post_final);
            }

            echo $send;
        } else {
            show_error("Cannot Process your request");
        }
    }
}
