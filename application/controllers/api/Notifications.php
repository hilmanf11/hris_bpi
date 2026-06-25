<?php
header('Content-Type: application/json; charset=utf-8');

date_default_timezone_set("Asia/Bangkok");
defined('BASEPATH') or exit('No direct script access allowed');

class Notifications extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->helper('url');
        $this->load->helper(array('form', 'url'));
        $this->load->library('form_validation');
        $this->load->library('session');
        $this->load->model('crud');
        $this->load->model('mobile');
    }

    //HALAMAN UTAMA
    public function index()
    {
        show_error("Cannot Process your request");
    }

    public function approve($api_key)
    {
        $this->apps = $this->load->database('apps', TRUE);

        if ($api_key != "") {
            $dataUser = $this->mobile->read("users", [], ["api_key" => $api_key]);

            if ($dataUser) {
                $id = $this->input->post('id');
                $tablename = $this->input->post('module');
                $data = $this->mobile->read($tablename, [], ["id" => $id]);
                $user = $this->mobile->read('users', [], ["username" => $data->created_by]);
                $approval = $this->mobile->read('approvals', [], ["table_name" => $tablename, "departement_id" => @$user->departement_id]);

                if ($data->approved == 1) {
                    $users_id = @$approval->user_approval_2;
                    $approved = 2;
                } elseif ($data->approved == 2) {
                    $users_id = @$approval->user_approval_3;
                    $approved = 3;
                } elseif ($data->approved == 3) {
                    $users_id = @$approval->user_approval_4;
                    $approved = 4;
                } elseif ($data->approved == 4) {
                    $users_id = @$approval->user_approval_5;
                    $approved = 5;
                } else {
                    $users_id = "";
                    $approved = 0;
                }

                // $values = array(
                //     "approved_by" => $dataUser->username,
                //     "approved_date" => date('Y-m-d H:i:s'),
                //     "approved_to" => $users_id,
                //     "approved" => $approved,
                // );

                if ($users_id != "") {
                    $values = array(
                        "approved_by" => $dataUser->username,
                        "approved_date" => date('Y-m-d H:i:s'),
                        "approved_to" => $users_id,
                        "approved" => $approved,
                    );
                } else {
                    $values = array(
                        "approved_date" => date('Y-m-d H:i:s'),
                        "approved_to" => $users_id,
                        "approved" => $approved,
                    );
                }

                $send = $this->db->update($tablename, $values, ["id" => $id]);

                if ($send) {
                    $message = "Sent a request to approve data " . strtoupper(str_replace("_", " ", $tablename));

                    if ($approved == 0) {
                        $users_id = $data->created_by;
                        $message = "Your a Request " . strtoupper(str_replace("_", " ", $tablename)) . " has been Approved by " . $dataUser->name;
                    }

                    $userFr = $this->mobile->read("users", [], ["username" => $users_id]);

                    if (!empty(@$userFr->api_key)) {
                        $this->apps->where('api_key', @$userFr->api_key);
                        $userAp = $this->apps->get('users')->row();

                        $this->mobile->pushNotification(@$userAp->token, $userFr->name, $message);
                    }

                    // untuk user yang mengajukan
                    $users_id = $data->created_by;
                    $userFr = $this->mobile->read("users", [], ["username" => $users_id]);
                    $message = "Your a Request " . strtoupper(str_replace("_", " ", $tablename)) . " has been Approved by " . $dataUser->name;

                    if (!empty(@$userFr->api_key)) {
                        $this->apps->where('api_key', @$userFr->api_key);
                        $userAp = $this->apps->get('users')->row();

                        $this->mobile->pushNotification(@$userAp->token, $userFr->name, $message);
                    }

                    echo json_encode(array("title" => "Approved", "message" => "Data Approved Successfully", "theme" => "success"));
                } else {
                    echo log_message('error', 'There is an error in your system or data');
                }
            } else {
                show_error("Api Key Invalid");
            }
        } else {
            show_error("Cannot Process your request");
        }
    }

    public function approveAll($api_key)
    {
        $this->apps = $this->load->database('apps', TRUE);

        if ($api_key != "") {
            $dataUser = $this->mobile->read("users", [], ["api_key" => $api_key]);

            if ($dataUser) {
                $approved_to = $this->input->post('approved_to');
                $created_by = $this->input->post('created_by');
                $table_name = $this->input->post('module');

                $datas = $this->mobile->reads($table_name, [], ["approved_to" => $approved_to, "created_by" => $created_by]);

                /// cek penanganan approval
                // if ($tablename == 'overtimes') {
                //     $tableApproval = 'cash_carries';
                // } else {
                //     $tableApproval = $tablename;
                // }

                foreach ($datas as $data) {
                    $id = $data->id;
                    $user = $this->mobile->read('users', [], ["username" => $data->created_by]);
                    $approval = $this->mobile->read('approvals', [], ["table_name" => $table_name, "departement_id" => @$user->departement_id]);
                    // $approval = $this->mobile->read('approvals', [], ["table_name" => $tableApproval, "employee_id" => @$data->employee_id]);

                    if ($data->approved == 1) {
                        $users_id = @$approval->user_approval_2;
                        $approved = 2;
                    } elseif ($data->approved == 2) {
                        $users_id = @$approval->user_approval_3;
                        $approved = 3;
                    } elseif ($data->approved == 3) {
                        $users_id = @$approval->user_approval_4;
                        $approved = 4;
                    } elseif ($data->approved == 4) {
                        $users_id = @$approval->user_approval_5;
                        $approved = 5;
                    } else {
                        $users_id = "";
                        $approved = 0;
                    }

                    // $values = array(
                    //     "approved_by" => $dataUser->username,
                    //     "approved_date" => date('Y-m-d H:i:s'),
                    //     "approved_to" => $users_id,
                    //     "approved" => $approved,
                    // );

                    if ($users_id != "") {
                        $values = array(
                            "approved_by" => $dataUser->username,
                            "approved_date" => date('Y-m-d H:i:s'),
                            "approved_to" => $users_id,
                            "approved" => $approved,
                        );
                    } else {
                        $values = array(
                            "approved_date" => date('Y-m-d H:i:s'),
                            "approved_to" => $users_id,
                            "approved" => $approved,
                        );
                    }

                    $send = $this->db->update($table_name, $values, ["id" => $id]);

                    if ($send) {
                        // check jika ada approval lagi, maka kirimkan notifikasi
                        if ($users_id != "") {
                            $message = "Sent a request to approve data " . strtoupper(str_replace("_", " ", $table_name));
                            $userApproval = $this->mobile->read('users', [], ["username" => $users_id]);

                            if (!empty(@$userApproval->api_key)) {
                                $this->apps->where('api_key', @$userApproval->api_key);
                                $userAp = $this->apps->get('users')->row();

                                if ($userAp) {
                                    $this->mobile->pushNotification(@$userAp->token, $userApproval->name, $message);
                                }
                            }
                        }

                        // Notifikasi untuk user yang mengajukan
                        $users_id = $data->created_by;
                        $userFr = $this->mobile->read("users", [], ["username" => $users_id]);
                        $message = "Your a Request " . strtoupper(str_replace("_", " ", $table_name)) . " has been Approved by " . $dataUser->name;

                        if (!empty(@$userFr->api_key)) {
                            $this->apps->where('api_key', @$userFr->api_key);
                            $userAp = $this->apps->get('users')->row();

                            $this->mobile->pushNotification(@$userAp->token, $userFr->name, $message);
                        }
                    }
                }

                echo json_encode(array("title" => "Approved", "message" => "Data Approved Successfully", "theme" => "success"));
            } else {
                show_error("Api Key Invalid");
            }
        } else {
            show_error("Cannot Process your request");
        }
    }

    public function disapprove($api_key)
    {
        if ($api_key != "") {
            $dataUser = $this->mobile->read("users", [], ["api_key" => $api_key]);

            if ($dataUser) {
                $id = $this->input->post('id');
                $tablename = $this->input->post('module');
                $username = $dataUser->username;

                /* Default */
                $this->mobile->delete($tablename, ["id" => $id], $username);
                echo json_encode(array("title" => "Disapproved", "message" => "Data Disapproved Successfully", "theme" => "success"));
            } else {
                show_error("Api Key Invalid");
            }
        } else {
            show_error("Cannot Process your request");
        }
    }

    public function disapproveAll($api_key)
    {
        $this->apps = $this->load->database('apps', TRUE);
        if ($api_key != "") {
            $dataUser = $this->mobile->read("users", [], ["api_key" => $api_key]);

            if ($dataUser) {
                $created_by = $this->input->post('created_by');
                $approved_to = $this->input->post('approved_to');
                $table_name = $this->input->post('module');
                $username = $dataUser->username;
                $datas = $this->mobile->reads($table_name, [], ["approved_to" => $approved_to, "created_by" => $created_by]);

                /* Default */
                foreach ($datas as $data) {
                    //
                    $this->mobile->delete($table_name, ["id" => $data->id], $username);
                    // UPDATE DISAPPROVE
                    // $values = array(
                    //     "approved_to" => "",
                    //     "approved_by" => "",
                    //     "disapproved_by" => $username,
                    //     "disapproved_date" => date('Y-m-d H:i:s'),
                    //     "deleted" => 1,
                    // );

                    // $send = $this->db->update($table_name, $values, ["id" => $data->id]);

                    // if ($send) {
                    //     // Notifikasi untuk user yang mengajukan
                    //     $users_id = $data->created_by;
                    //     $userFr = $this->mobile->read("users", [], ["username" => $users_id]);
                    //     $message = "Your a Request " . strtoupper(str_replace("_", " ", $table_name)) . " has been Disapproved by " . $dataUser->name;

                    //     if (!empty(@$userFr->api_key)) {
                    //         $this->apps->where('api_key', @$userFr->api_key);
                    //         $userAp = $this->apps->get('users')->row();

                    //         $this->pushNotification($userAp->token, $userFr->name, $message);
                    //     }
                    // }
                }

                echo json_encode(array("title" => "Disapproved", "message" => "Data Disapproved Successfully", "theme" => "success"));
            } else {
                show_error("Api Key Invalid");
            }
        } else {
            show_error("Cannot Process your request");
        }
    }

    public function approvalList($api_key)
    {
        if ($api_key != "") {
            $user = $this->mobile->read("users", [], ["api_key" => $api_key]);

            if ($user) {
                //Cash Carries
                $this->db->select('b.name as fullname, a.approved_to, a.created_by, a.created_date, b.avatar');
                $this->db->from('cash_carries a');
                $this->db->join('users b', 'a.approved_by = b.username');
                $this->db->join('users c', 'a.approved_to = c.username');
                $this->db->where('a.approved_to', $user->username);
                $this->db->group_by('a.created_by');
                $cash_carries = $this->db->get()->result_object();

                //Permits
                $this->db->select('b.name as fullname, a.approved_to, a.created_by, a.created_date, b.avatar');
                $this->db->from('permits a');
                $this->db->join('users b', 'a.approved_by = b.username');
                $this->db->join('users c', 'a.approved_to = c.username');
                $this->db->where('a.approved_to', $user->username);
                $this->db->group_by('a.created_by');
                $permits = $this->db->get()->result_object();

                //Change Days
                $this->db->select('b.name as fullname, a.approved_to, a.created_by, a.created_date, b.avatar');
                $this->db->from('change_days a');
                $this->db->join('users b', 'a.approved_by = b.username');
                $this->db->join('users c', 'a.approved_to = c.username');
                $this->db->where('a.approved_to', $user->username);
                $this->db->group_by('a.created_by');
                $change_days = $this->db->get()->result_object();

                $records = array();
                if (count($cash_carries) > 0) {
                    foreach ($cash_carries as $cash_carry) {
                        $records[] = $this->approvalMessage($cash_carry->avatar, $cash_carry->fullname, $cash_carry->approved_to, $cash_carry->created_by, $cash_carry->created_date, "cash_carries");
                    }
                }

                if (count($permits) > 0) {
                    foreach ($permits as $permit) {
                        $records[] = $this->approvalMessage($permit->avatar, $permit->fullname, $permit->approved_to, $permit->created_by, $permit->created_date, "permits");
                    }
                }

                if (count($change_days) > 0) {
                    foreach ($change_days as $change_day) {
                        $records[] = $this->approvalMessage($change_day->avatar, $change_day->fullname, $change_day->approved_to, $change_day->created_by, $change_day->created_date, "change_days");
                    }
                }

                die(json_encode(array(
                    "total" => count($records),
                    "results" => $records
                )));
            } else {
                show_error("Api Key Invalid");
            }
        } else {
            show_error("Cannot Process your request");
        }
    }

    public function approvalMessage($foto, $fullname, $approved_to, $created_by, $created_date, $table)
    {
        if ($foto == "") {
            $avatar = base_url('assets/image/users/default.png');
        } else {
            $avatar = "assets/image/users/" . $foto;
        }

        return array(
            "module" => $table,
            "approved_to" => $approved_to,
            "created_by" => $created_by,
            "created_date" => $created_date,
            "avatar" => $avatar,
            "fullname" => $fullname,
            "message" => "Sent a request to approve data " . strtoupper(str_replace("_", " ", $table))
        );
    }

    public function approvalDetails($approved_to, $created_by, $module)
    {
        switch ($module) {
            case "cash_carries":
                $this->db->select('a.*, 
                        c.name as division_name,
                        d.name as departement_name,
                        e.name as departement_sub_name,
                        b.number as employee_number,
                        b.name as employee_name
                    ');
                $this->db->from('cash_carries a');
                $this->db->join('employees b', 'a.employee_id = b.id');
                $this->db->join('divisions c', 'b.division_id = c.id');
                $this->db->join('departements d', 'b.departement_id = d.id');
                $this->db->join('departement_subs e', 'b.departement_sub_id = e.id');
                $this->db->join('users f', "a.created_by = f.username");
                $this->db->where('b.deleted', 0);
                $this->db->where('b.status', 0);
                $this->db->where('a.deleted', 0);
                $this->db->where('a.approved_to', $approved_to);
                $this->db->where('a.created_by', $created_by);
                $this->db->group_by('a.trans_date');
                $this->db->group_by('a.employee_id');
                $this->db->group_by('a.type');
                $this->db->order_by('a.trans_date', 'DESC');
                $records = $this->db->get()->result_array();

                die(json_encode(array(
                    "title" => "Success",
                    "message" => "Data Found",
                    "theme" => "success",
                    "results" => $records
                )));

                break;
            case "permits":
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
                $this->db->where('c.status', 0);
                $this->db->where('a.approved_to', $approved_to);
                $this->db->where('a.created_by', $created_by);
                $this->db->order_by('a.permit_date', 'DESC');
                $records = $this->db->get()->result_array();

                die(json_encode(array(
                    "title" => "Success",
                    "message" => "Data Found",
                    "theme" => "success",
                    "results" => $records
                )));

                break;
            case "change_days":
                $this->db->select('a.*,
                        c.name as division_name,
                        d.name as departement_name,
                        e.name as departement_sub_name,
                        b.number as employee_number,
                        b.name as employee_name
                    ');
                $this->db->from('change_days a');
                $this->db->join('employees b', 'a.employee_id = b.id');
                $this->db->join('divisions c', 'b.division_id = c.id');
                $this->db->join('departements d', 'b.departement_id = d.id');
                $this->db->join('departement_subs e', 'b.departement_sub_id = e.id');
                $this->db->where('b.deleted', 0);
                $this->db->where('b.status', 0);
                $this->db->where('a.deleted', 0);
                $this->db->where('a.approved_to', $approved_to);
                $this->db->where('a.created_by', $created_by);
                $this->db->group_by('a.start');
                $this->db->group_by('a.employee_id');
                $this->db->order_by('a.created_date', 'DESC');
                $records = $this->db->get()->result_array();

                die(json_encode(array(
                    "title" => "Success",
                    "message" => "Data Found",
                    "theme" => "success",
                    "results" => $records
                )));

                break;
            default:
                die(json_encode(array("title" => "Module Not Found")));
        }
    }

    public function testPushNotification()
    {

        try {
            $post = $this->input->post();

            $title = $post['title'];
            $message = $post['message'];
            $token = $post['token'];
            $this->mobile->pushNotification($token, $title, $message);
        } catch (Exception $e) {
            die(json_encode(array("message" => $e->getMessage())));
        }
    }
}
