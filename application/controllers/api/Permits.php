<?php
header('Content-Type: application/json; charset=utf-8');

date_default_timezone_set("Asia/Bangkok");
defined('BASEPATH') or exit('No direct script access allowed');

class Permits extends CI_Controller
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
                    c.number as employee_number,
                    c.name as employee_name,
                    d.name as division_name,
                    e.name as departement_name,
                    f.name as departement_sub_name,
                    g.name as permit_type_name,
                    h.name as reason_name,
                    i.name as request_name
                ');
                $this->db->from('permits a');
                $this->db->join('employees c', 'a.employee_id = c.id');
                $this->db->join('divisions d', 'c.division_id = d.id');
                $this->db->join('departements e', 'c.departement_id = e.id');
                $this->db->join('departement_subs f', 'c.departement_sub_id = f.id');
                $this->db->join('permit_types g', 'a.permit_type_id = g.id');
                $this->db->join('reasons h', 'a.reason_id = h.id');
                $this->db->join('users i', 'a.created_by = i.username');
                $this->db->where('YEAR(a.permit_date)', $year);
                $this->db->where('a.deleted', 0);
                $this->db->where('c.deleted', 0);
                $this->db->where('c.status', 0);
                $this->db->like('c.id', $employee->id);
                $this->db->order_by('a.permit_date', 'DESC');
                // $this->db->limit($limit);
                $records = $this->db->get()->result_array();

                $data = array();
                foreach ($records as $record) {
                    if(!empty($record['attachment'])){
                        if(substr($record['attachment'], -4) == "jpeg"){
                            $attachment = "assets/image/permits/" . substr($record['attachment'], -15);
                        }else{
                            $attachment = "assets/image/permits/" . substr($record['attachment'], -14);
                        }
                    }else{
                        $attachment = null;
                    }

                    $data[] = array_merge($record, array("file_attachment" => $attachment));
                }

                die(json_encode(array(
                    "title" => "Success",
                    "message" => "Data Found",
                    "theme" => "success",
                    "results" => $data
                )));
            } else {
                show_error("Api Key Invalid");
            }
        } else {
            show_error("Cannot Process your request");
        }
    }

    public function readTypes()
    {
        $send = $this->mobile->reads('permit_types');
        echo json_encode($send);
    }

    // OLD
    public function readReason($permit_type_id)
    {
        if ($permit_type_id != "") {
            $send = $this->mobile->reads('reasons', [], ["number" => $permit_type_id]);
            echo json_encode($send);
        }else{
            show_error("Cannot Process your request");
        }
    }

    // UBAH JADI NUMBER
    public function readReasonV2($number)
    {
        if ($number != "") {
            $send = $this->mobile->reads('reasons', ["number" => $number]);
            echo json_encode($send);
        }else{
            show_error("Cannot Process your request");
        }
    }

    public function readLeave($api_key, $permit_type_id) {
        if ($api_key != "" && $permit_type_id != "") {
            $user = $this->mobile->read("users", [], ["api_key" => $api_key]);

            if ($user) {
                $employee = $this->mobile->read("employees", [], ["number" => $user->number]);

                $year = date("Y");
                $calendar = $this->mobile->reads("calendars", ["trans_date" => $year], ["permit" => 1]);

                $permitType = $this->mobile->read('permit_types', [], ["id" => $permit_type_id, "cutoff" => "YES"]);
                $permits = $this->mobile->reads('permits', ["employee_id" => $employee->id, "DATE_FORMAT(permit_date, '%Y')" => $year], ["permit_type_id" => @$permitType->id]);
                $totalPermit = 0;
                foreach ($permits as $permit) {
                    $totalPermit += $permit->duration;
                }

                echo json_encode(["total" => (12 - $totalPermit - count($calendar))]);
            } else {
                show_error("Api Key Invalid");
            }
        } else {
            show_error("Cannot Process your request");
        }
    }

    public function createData($api_key = "")
    {
        if ($api_key != "") {
            $user = $this->mobile->read("users", [], ["api_key" => $api_key]);

            if ($user) {
                if($this->input->post()){
                    $post = $this->input->post();

                    $employee = $this->mobile->read("employees", [], ["number" => $user->number]);
                    $attachment = $this->mobile->upload('attachment', ['png', 'jpg', 'jpeg'], 'assets/image/permits/');

                    // approval
                    // $approval = $this->mobile->read("approvals", [], ["departement_id" => $employee->departement_id, "departement_sub_id" => $employee->departement_sub_id, "table_name" => "permits"]);
                    $approval = $this->mobile->read("approvals", [], ["departement_id" => $employee->departement_id, "table_name" => "permits"]);

                    if($approval === null){
                        show_error("Approval Not Found, Please Contact Your Administrator for setting approval permits.");
                        return;
                    }
                    

                    $date_from = strtotime($post['date_from']);
                    $date_to = strtotime($post['date_to']);

                    $send = "";
                    $leave = $post['leave'];
                    for ($i = $date_from; $i <= $date_to; $i += (60 * 60 * 24)) {
                        $permit_date = date('Y-m-d', $i);

                        if(($leave - 1) >= 0){
                            $post_final = array(
                                "employee_id" => $employee->id,
                                "permit_type_id" => $post['permit_type_id'],
                                "reason_id" => $post['reason_id'],
                                "trans_date" => date("Y-m-d"),
                                "permit_date" => $permit_date,
                                "duration" => "1",
                                "leave" => ($leave - 1),
                                "note" => $post['remarks'],
                                "attachment" => $attachment
                            );
        
                            $permits = $this->mobile->reads('permits', [], ["employee_id" => $employee->id, "permit_date" => $permit_date]);
                            
                            if (count($permits) > 0) {
                                show_error("Your Permit application has been sent, please change to another date");
                            }else{
                                // if($this->mobile->create('permits', $post_final, $user->username)){
                                //     $send = json_encode(array(
                                //         "title" => "Success",
                                //         "message" => "Permit Success Send",
                                //         "theme" => "success"
                                //     ));
                                // }else{
                                //     show_error("Permit Failed To Send");
                                // }

                                $result = $this->mobile->create('permits', $post_final, $user->username);
                                if($result){
                                    // buat fungsi update approved by berdasarkan siapa approved to nya
                                    // fungsi ini agar disamakan dengan web
                                    $values = array("approved_by" => @$approval->user_approval_1);
                                    $send = $this->db->update("permits", $values, ["employee_id" => $employee->id, "permit_type_id" => $post['permit_type_id'], "trans_date" => date("Y-m-d"), "permit_date" => $permit_date]);

                                    if($send){
                                        // ambil apiKey berdasarkan user_approval_1
                                        $user_approval = $this->mobile->read('users', [], ["username" => $approval->user_approval_1]);
                                        if($user_approval) {
                                            //load db
                                            $this->apps = $this->load->database('apps', TRUE);
                                            //ambil data ke db hris mobile
                                            $this->apps->select("*");
                                            $this->apps->from('users');
                                            $this->apps->where('api_key', $user_approval->api_key);
                                            $this->apps->limit(1);

                                            $userM = $this->apps->get()->row();

                                            $message = "Sent a request to approve data Permit";

                                            $this->mobile->pushNotification($userM->token, $user_approval->name, $message);
                                        }

                                        die(json_encode(array(
                                            "title" => "Success",
                                            "message" => "Permit Success Send",
                                            "theme" => "success",
                                        )));
                                    } else {
                                        show_error("Permit Failed To Send");
                                    }
                                } else {
                                    show_error("Permit Failed To Send");
                                }
                            }
                        }else{
                            show_error("Your permit has exceeded the limit");
                        }
                        
                        $leave = ($leave - 1);
                    }

                    echo $send;
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

                    $delete = $this->mobile->delete("permits", ['id' => $data['id']], $user->username);
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