<?php
header('Content-Type: application/json; charset=utf-8');

date_default_timezone_set("Asia/Bangkok");
defined('BASEPATH') or exit('No direct script access allowed');

class Profiles extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->helper('url');
        $this->load->helper(array('form', 'url'));
        $this->load->library('form_validation');
        $this->load->library('session');
        $this->load->model('mobile');
    }

    //HALAMAN UTAMA
    public function index()
    {
        show_error("Cannot Process your request");
    }

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

    public function reads($api_key = "")
    {
        if ($api_key != "") {
            $user = $this->mobile->read("users", [], ["api_key" => $api_key]);

            if ($user) {
                $employee = $this->mobile->read("employees", [], ["number" => $user->number]);

                $this->db->select('a.*, 
                    NULL as approved_data,
                    b.users_id_from as status_check,
                    b.users_id_to as status_notification, 
                    c.name as division_name, 
                    d.name as departement_name, 
                    e.name as departement_sub_name,
                    e.type, 
                    g.name as position_name,
                    h.name as contract_name,
                    g.access', FALSE);
                $this->db->from('employees a');
                $this->db->join('notifications b', "a.id = b.table_id and b.table_name = 'employees'", 'left');
                $this->db->join('divisions c', 'c.id = a.division_id');
                $this->db->join('departements d', 'd.id = a.departement_id');
                $this->db->join('departement_subs e', 'e.id = a.departement_sub_id');
                $this->db->join('agreements f', 'a.number = f.number and f.status = 0');
                $this->db->join('positions g', 'g.id = a.position_id', 'left');
                $this->db->join('contracts h', 'h.id = a.contract_id', 'left');
                $this->db->where('a.deleted', 0);
                $this->db->where("a.id", $employee->id);
                $this->db->order_by('a.name', 'ASC');
                $records = $this->db->get()->result_array();

                $data  = array();
                foreach ($records as $record) {
                    if (!empty($record['image_profile'])) {
                        $image_profile = "assets/image/employee/profile/" . $record['image_profile'];
                    } else {
                        $image_profile = "assets/image/users/default.png";
                    }

                    $data = array_merge($record, array("service" => $this->readService($record['date_sign']), "link_foto" => $image_profile));
                }

                die(json_encode(array(
                    "title" => "Success",
                    "message" => "Data Found",
                    "theme" => "success",
                    "results" => $data,
                )));
            } else {
                show_error("Api Key Invalid");
            }
        } else {
            show_error("Cannot Process your request");
        }

        echo json_encode($send);
    }
}
