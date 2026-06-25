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

    public function reads($api_key = "", $limit = 30)
    {
        if ($api_key != "") {
            $user = $this->mobile->read("users", [], ["api_key" => $api_key]);

            if ($user) {
                $employee = $this->mobile->read("employees", [], ["number" => $user->number]);

                $this->db->select("a.*,
                    c.number as employee_number,
                    c.name as employee_name,
                    d.name as division_name,
                    e.name as departement_name,
                    f.name as departement_sub_name,
                    h.name as reason_name,
                    i.name as request_name,

                    IFNULL(
                        CASE 
                            WHEN a.approved_to IS NOT NULL 
                                AND a.approved_to != '' 
                                AND u.name IS NOT NULL 
                                AND u.name != '' 
                                THEN CONCAT(
                                    IFNULL(g.name, ''),
                                    ' - ',
                                    SUBSTRING_INDEX(u.name, ' ', 2),
                                    ' (Waiting Approval)'
                                )
                            ELSE IFNULL(g.name, '')
                        END,
                        ''
                    ) AS permit_type_name

                ");
                $this->db->from('permits a');
                $this->db->join('employees c', 'a.employee_id = c.id');
                $this->db->join('divisions d', 'c.division_id = d.id');
                $this->db->join('departements e', 'c.departement_id = e.id');
                $this->db->join('departement_subs f', 'c.departement_sub_id = f.id');
                $this->db->join('permit_types g', 'a.permit_type_id = g.id');
                $this->db->join('reasons h', 'a.reason_id = h.id');
                $this->db->join('users i', 'a.created_by = i.username');
                $this->db->where('a.deleted', 0);
                $this->db->where('c.deleted', 0);
                $this->db->where('c.status', 0);
                $this->db->like('c.id', $employee->id);
                $this->db->order_by('a.permit_date', 'DESC');
                $this->db->limit($limit);
                $records = $this->db->get()->result_array();

                $data = array();
                foreach ($records as $record) {
                    $data[] = array_merge($record, array("file_attachment" => "assets/image/attandance/" . $record['attachment']));
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

    // LIST WITH YEAR
    public function list($api_key = "", $year = "")
    {
        if ($api_key != "") {
            $user = $this->mobile->read("users", [], ["api_key" => $api_key]);

            if ($user) {
                $employee = $this->mobile->read("employees", [], ["number" => $user->number]);

                $this->db->select("a.*,
                    c.number as employee_number,
                    c.name as employee_name,
                    d.name as division_name,
                    e.name as departement_name,
                    f.name as departement_sub_name,
                    h.name as reason_name,
                    i.name as request_name,
                    
                    IFNULL(
                        CASE 
                            WHEN a.approved_to IS NOT NULL 
                                AND a.approved_to != '' 
                                AND u.name IS NOT NULL 
                                AND u.name != '' 
                                THEN CONCAT(
                                    IFNULL(g.name, ''),
                                    ' - ',
                                    SUBSTRING_INDEX(u.name, ' ', 2),
                                    ' (Waiting Approval)'
                                )
                            ELSE IFNULL(g.name, '')
                        END,
                        ''
                    ) AS permit_type_name

                ");
                $this->db->from('permits a');
                $this->db->join('employees c', 'a.employee_id = c.id');
                $this->db->join('divisions d', 'c.division_id = d.id');
                $this->db->join('departements e', 'c.departement_id = e.id');
                $this->db->join('departement_subs f', 'c.departement_sub_id = f.id');
                $this->db->join('permit_types g', 'a.permit_type_id = g.id');
                $this->db->join('reasons h', 'a.reason_id = h.id');
                $this->db->join('users i', 'a.created_by = i.username');
                $this->db->join('users u', "a.approved_to = u.username", 'left');
                // $this->db->where('a.deleted', 0);
                // $this->db->where('YEAR(a.permit_date)', $year);
                $this->db->where('YEAR(a.trans_date)', $year);
                $this->db->where('c.deleted', 0);
                $this->db->where('c.status', 0);
                $this->db->like('c.id', $employee->id);
                $this->db->order_by('a.permit_date', 'DESC');
                // $this->db->limit($limit);
                $records = $this->db->get()->result_array();

                $data = array();
                foreach ($records as $record) {
                    if (!empty($record['attachment'])) {
                        if (substr($record['attachment'], -4) == "jpeg") {
                            $attachment = "assets/image/attandance/" . substr($record['attachment'], -15);
                        } else {
                            $attachment = "assets/image/attandance/" . substr($record['attachment'], -14);
                        }
                    } else {
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

    public function readReason($permit_type_id)
    {
        if ($permit_type_id != "") {
            $send = $this->mobile->reads('reasons', [], ["permit_type_id" => $permit_type_id]);
            echo json_encode($send);
        } else {
            show_error("Cannot Process your request");
        }
    }

    public function readLeave($api_key, $permit_type_id)
    {
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
                if ($this->input->post()) {
                    $post = $this->input->post();

                    $employee = $this->mobile->read("employees", [], ["number" => $user->number]);
                    $attachment = $this->mobile->upload('attachment', ['png', 'jpg', 'jpeg'], 'assets/image/attandance/');

                    //approvalUser
                    $approval = $this->mobile->read("approvals", [], ["departement_id" => $employee->departement_id, "table_name" => "permits"]);
                    // $approvalUser = $this->mobile->read("users", [], ["username" => $approval->user_approval_1]);

                    if (!$approval) {
                        show_error("Approval not found");
                    }

                    $post_final = array(
                        "approved" => 1,
                        "approved_to" => $approval->user_approval_1,
                        "approved_by" => $approval->user_approval_1,
                        "employee_id" => $employee->id,
                        "permit_type_id" => $post['permit_type_id'],
                        "reason_id" => $post['reason_id'],
                        "trans_date" => date("Y-m-d"),
                        "permit_date" => $post['permit_date'],
                        "duration" => "1",
                        "leave" => $post['leave'],
                        "note" => $post['remarks'],
                        "attachment" => $attachment
                    );

                    $permits = $this->mobile->reads('permits', [], ["employee_id" => $employee->id, "permit_date" => $post['permit_date']]);

                    if (count($permits) > 0) {
                        show_error("Your Permit application has been sent, please change to another date");
                    } else {
                        if ($this->mobile->create('permits', $post_final, $user->username)) {
                            if (!empty($approvalUser->api_key)) {
                                $this->apps->where('api_key', @$approvalUser->api_key);
                                $userAp = $this->apps->get('users')->row();

                                $message = "Sent a request to approve data Permit";

                                $this->mobile->pushNotification(@$userAp->token, $user->name, $message);
                            }

                            die(json_encode(array(
                                "title" => "Success",
                                "message" => "Permit Success Send",
                                "theme" => "success"
                            )));
                        } else {
                            show_error("Permit Failed To Send");
                        }
                    }
                } else {
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
                if ($this->input->post()) {
                    $data = $this->input->post();

                    $delete = $this->mobile->delete("permits", ['id' => $data['id']], $user->username);
                    echo $delete;
                } else {
                    show_error("Cannot Process your request");
                }
            } else {
                show_error("Api Key Invalid");
            }
        } else {
            show_error("Cannot Process your request");
        }
    }
}
