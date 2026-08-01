<?php
header('Content-Type: application/json; charset=utf-8');

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
        $this->load->model('mobile');
    }

    //HALAMAN UTAMA
    public function index()
    {
        show_error("Cannot Process your request");
    }
    
    public function reads($api_key = "", $year = "")
    {
        if ($api_key != "") {
            $user = $this->mobile->read("users", [], ["api_key" => $api_key]);

            if ($user) {
                $employee = $this->mobile->read("employees", [], ["number" => $user->number]);

                $this->db->select('a.*,
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
                $this->db->where('YEAR(a.start)', $year);
                $this->db->where('b.deleted', 0);
                $this->db->where('b.status', 0);
                $this->db->where('a.deleted', 0);
                $this->db->like('b.id', $employee->id);
                // $this->db->group_by('a.start');
                // $this->db->group_by('a.employee_id');
                $this->db->order_by('a.start', 'DESC');
                // $this->db->limit($limit);
                $records = $this->db->get()->result_array();

                die(json_encode(array(
                    "title" => "Success",
                    "message" => "Data Found",
                    "theme" => "success",
                    "results" => $records
                )));
            } else {
                show_error("Api Key Invalid");
            }
        } else {
            show_error("Cannot Process your request");
        }
    }

    public function requestCode($trans_date)
    {
        $date = date("ymd", strtotime($trans_date));
        $this->db->select('max(SUBSTRING(request_code, -4)) as code');
        $this->db->from('change_days');
        $this->db->where('deleted', 0);
        $this->db->where('SUBSTRING(request_code, 4, 6)=', $date);
        $records = $this->db->get()->row();

        $requestcode = (int) $records->code;
        $requestcode++;

        $template = "CD/" . $date . "/";
        return $template . sprintf("%04s", $requestcode);
    }

    public function createData($api_key = "")
    {
        if ($api_key != "") {
            $user = $this->mobile->read("users", [], ["api_key" => $api_key]);

            if ($user) {
                if($this->input->post()){
                    $post = $this->input->post();

                    $employee = $this->mobile->read("employees", [], ["number" => $user->number]);

                    // approval user
                    $approval = $this->mobile->read("approvals", [], ["departement_id" => $employee->departement_id, "table_name" => "change_days"]);
                    // $approvalUser = $this->mobile->read("users", [], ["username" => $approval->user_approval_1]);

                    if($approval === null){
                        show_error("Approval Not Found, Please Contact Your Administrator for setting approval change days.");
                        return;
                    }

                    $employee_id = $employee->id;
                    $request_code = $this->requestCode($post['start']);
                    $start = $post['start'];
                    $end = $post['end'];
                    $remarks = $post['remarks'];

                    $post_final = array(
                        "employee_id" =>  $employee_id,
                        "request_code" =>  $request_code,
                        "start" =>  $start,
                        "end" =>  $end,
                        "remarks" =>  $remarks,
                    );

                    $change_days = $this->mobile->reads('change_days', [], ["employee_id" => $employee_id, "start" => $start]);

                    if (count($change_days) > 0) {
                        show_error("Your change days application has been sent, please change to another date");
                    }else{
                        $result = $this->mobile->create('change_days', $post_final, $user->username);
                        if($result){

                            // buat fungsi update approved by berdasarkan siapa approved to nya
                            // fungsi ini agar disamakan dengan web
                            $values = array("approved_by" => @$approval->user_approval_1);
                            $send = $this->db->update("change_days", $values, ["employee_id" => $employee_id, "request_code" => $request_code]);

                            if($send){
                                // ambil apiKey berdasarkan user_approval_1
                                $user_approval = $this->mobile->read('users', [], ["username" => $approval->user_approval_1]);
                                if($user_approval) {
                                    $this->apps = $this->load->database('apps', TRUE);
                                    //ambil data ke db hris mobile
                                    $this->apps->select("*");
                                    $this->apps->from('users');
                                    $this->apps->where('api_key', $user_approval->api_key);
                                    $this->apps->limit(1);

                                    $userM = $this->apps->get()->row();

                                    $message = "Sent a request to approve data Change Day";

                                    $this->mobile->pushNotification($userM->token, $user_approval->name, $message);
                                }

                                die(json_encode(array(
                                    "title" => "Success",
                                    "message" => "Change Day Success Send",
                                    "theme" => "success",
                                )));
                            } else {
                                show_error("Change Days Failed To Send");
                            }
                        }else{
                            show_error("Change Days Failed To Send");
                        }
                    }
                }else{
                    show_error("Cannot Process your request");
                }
            } else {
                show_error("Api Key Invalid");
            }
        } else {
            show_error("Cannot Process your request");
        }
    }

    public function cancel($api_key = "")
    {
        if ($api_key != "") {
            $user = $this->mobile->read("users", [], ["api_key" => $api_key]);
            if ($user) {
                if($this->input->post()){
                    $data = $this->input->post();

                    $delete = $this->mobile->delete("change_days", ['id' => $data['id']], $user->username);
                    echo $delete;
                }else{
                    show_error("Cannot Process your request");
                }
            } else {
                show_error("Api Key Invalid");
            }
        }else{
            show_error("Cannot Process your request");
        }
    }
}