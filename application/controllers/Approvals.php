<?php
date_default_timezone_set("Asia/Bangkok");
defined('BASEPATH') or exit('No direct script access allowed');

class Approvals extends CI_Controller
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

    public function approveall()
    {
        $approved_to = $this->input->post('approved_to');
        $created_by = $this->input->post('created_by');
        $table_name = $this->input->post('table_name');

        $datas = $this->crud->reads($table_name, [], ["approved_to" => $approved_to, "created_by" => $created_by]);
        
        foreach ($datas as $data) {
            $id = $data->id;
            $user = $this->crud->read('users', [], ["username" => $data->created_by]);
            $approval = $this->crud->read('approvals', [], ["table_name" => $table_name, "departement_id" => @$user->departement_id]);
            
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

            //Approval Mutation
            if ($table_name == "mutations" && $users_id == "") {
                $mutations = $this->crud->read('mutations', [], ["id" => $id]);
                $postEmployee = array(
                    "division_id" => $mutations->division_id,
                    "departement_id" => $mutations->departement_id,
                    "departement_sub_id" => $mutations->departement_sub_id
                );

                if($mutations->type == "PERMANENT"){
                    $send = $this->db->update('employees', $postEmployee, ["id" => $mutations->employee_id]);
                }
            }

            //Approval Agreement
            if ($table_name == "agreements" && $users_id == "") {
                $agreements = $this->crud->read('agreements', [], ["id" => $id]);
                $postEmployee = array(
                    "position_id" => $agreements->position_id,
                    "group_id" => $agreements->group_id,
                    "contract_id" => $agreements->contract_id,
                    "date_sign" => $agreements->date_sign,
                    "date_expired" => $agreements->date_expired,
                );

                $send = $this->db->update('employees', $postEmployee, ["number" => $agreements->number]);
            }

            $values = array(
                "approved_by" => $this->session->username,
                "approved_date" => date('Y-m-d H:i:s'),
                "approved_to" => $users_id,
                "approved" => $approved,
            );

            $send = $this->db->update($table_name, $values, ["id" => $id]);
        }

        echo json_encode(array("title" => "Approved", "message" => "Data Approved Successfully", "theme" => "success"));
    }

    public function approve()
    {
        $id = $this->input->post('id');
        $tablename = $this->input->post('tablename');
        $data = $this->crud->read($tablename, [], ["id" => $id]);
        $user = $this->crud->read('users', [], ["username" => $data->created_by]);
        $approval = $this->crud->read('approvals', [], ["table_name" => $tablename, "departement_id" => @$user->departement_id]);

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

        //Approval Mutation
        if ($tablename == "mutations" && $users_id == "") {
            $mutations = $this->crud->read('mutations', [], ["id" => $id]);
            $postEmployee = array(
                "division_id" => $mutations->division_id,
                "departement_id" => $mutations->departement_id,
                "departement_sub_id" => $mutations->departement_sub_id
            );

            if($mutations->type == "PERMANENT"){
                $send = $this->db->update('employees', $postEmployee, ["id" => $mutations->employee_id]);
                if($send){
                    echo json_encode(array("title" => "Approved", "message" => "Data Approved Successfully", "theme" => "success"));
                } else {
                    echo log_message('error', 'There is an error in your system or data');
                }
            }
        }

        //Approval Agreement
        if ($tablename == "agreements" && $users_id == "") {
            $agreements = $this->crud->read('agreements', [], ["id" => $id]);
            $postEmployee = array(
                "position_id" => $agreements->position_id,
                "group_id" => $agreements->group_id,
                "contract_id" => $agreements->contract_id,
                "date_sign" => $agreements->date_sign,
                "date_expired" => $agreements->date_expired,
            );

            $send = $this->db->update('employees', $postEmployee, ["number" => $agreements->number]);
            if($send){
                echo json_encode(array("title" => "Approved", "message" => "Data Approved Successfully", "theme" => "success"));
            } else {
                echo log_message('error', 'There is an error in your system or data');
            }
        }

        $values = array(
            "approved_by" => $this->session->username,
            "approved_date" => date('Y-m-d H:i:s'),
            "approved_to" => $users_id,
            "approved" => $approved,
        );

        $send = $this->db->update($tablename, $values, ["id" => $id]);
        if($send){
            echo json_encode(array("title" => "Approved", "message" => "Data Approved Successfully", "theme" => "success"));
        } else {
            echo log_message('error', 'There is an error in your system or data');
        }
    }

    public function approvePayrolls()
    {
        $id = $this->input->post('id');
        $tablename = $this->input->post('tablename');
        $period_start = base64_decode($this->input->post('period_start'));
        $period_end = base64_decode($this->input->post('period_end'));

        $this->db->select('a.id');
        $this->db->from('payrolls a');
        $this->db->join('employees b', 'a.employee_id = b.id');
        $this->db->where('b.group_id', $id);
        $this->db->where('a.period_start', $period_start);
        $this->db->where('a.period_end', $period_end);
        $this->db->where('b.deleted', 0);
        $this->db->where('b.status', 0);
        $this->db->where('a.status', 0);
        $this->db->group_by('a.employee_id');
        $records = $this->db->get()->result_array();

        foreach ($records as $record) {
            $payroll_id = $record['id'];

            $data = $this->crud->read($tablename, [], ["id" => $payroll_id]);
            $user = $this->crud->read('users', [], ["username" => $data->created_by]);
            $approval = $this->crud->read('approvals', [], ["table_name" => $tablename, "departement_id" => @$user->departement_id]);

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

            $values = array(
                "approved_by" => $this->session->username,
                "approved_date" => date('Y-m-d H:i:s'),
                "approved_to" => $users_id,
                "approved" => $approved,
            );
            
            $send = $this->db->update($tablename, $values, ["id" => $payroll_id]);
        }

        echo json_encode(array("title" => "Approved", "message" => "Data Approved Successfully", "theme" => "success"));
    }

    public function disapproveall()
    {
        $created_by = $this->input->post('created_by');
        $approved_to = $this->input->post('approved_to');
        $table_name = $this->input->post('table_name');
        $datas = $this->crud->reads($table_name, [], ["approved_to" => $approved_to, "created_by" => $created_by]);

        /* Default */
        foreach ($datas as $data) {
            $send = $this->crud->delete($table_name, ["id" => $data->id]);
        }

        echo json_encode(array("title" => "Disapproved", "message" => "Data Disapproved Successfully", "theme" => "success"));
    }

    public function disapprove()
    {
        $id = $this->input->post('id');
        $tablename = $this->input->post('tablename');

        /* Default */
        $send = $this->crud->delete($tablename, ["id" => $id]);
        echo json_encode(array("title" => "Disapproved", "message" => "Data Disapproved Successfully", "theme" => "success"));
    }

    public function disapprovePayrolls()
    {
        $id = $this->input->post('id');
        $tablename = $this->input->post('tablename');
        $period_start = base64_decode($this->input->post('period_start'));
        $period_end = base64_decode($this->input->post('period_end'));

        $this->db->select('a.id');
        $this->db->from('payrolls a');
        $this->db->join('employees b', 'a.employee_id = b.id');
        $this->db->where('b.group_id', $id);
        $this->db->where('a.period_start', $period_start);
        $this->db->where('a.period_end', $period_end);
        $this->db->where('b.deleted', 0);
        $this->db->where('b.status', 0);
        $this->db->where('a.status', 0);
        $this->db->group_by('a.employee_id');
        $records = $this->db->get()->result_array();

        foreach ($records as $record) {
            $payroll_id = $record['id'];
            $send = $this->crud->delete($tablename, ["id" => $payroll_id]);
        }

        echo $send;
    }

    public function approvalCount()
    {
        $this->db->select('b.name as fullname, a.approved_to, a.created_by, b.avatar');
        $this->db->from('cash_carries a');
        $this->db->join('users b', 'a.approved_by = b.username');
        $this->db->join('users c', 'a.approved_to = c.username');
        $this->db->where('a.approved_to', $this->session->username);
        $this->db->group_by('a.created_by');
        $cash_carries = $this->db->get()->result_object();

        $this->db->select('b.name as fullname, a.approved_to, a.created_by, b.avatar');
        $this->db->from('payrolls a');
        $this->db->join('users b', 'a.approved_by = b.username');
        $this->db->join('users c', 'a.approved_to = c.username');
        $this->db->where('a.approved_to', $this->session->username);
        $this->db->group_by('a.created_by');
        $payrolls = $this->db->get()->result_object();
        
        $this->db->select('b.name as fullname, a.approved_to, a.created_by, b.avatar');
        $this->db->from('setup_salaries a');
        $this->db->join('users b', 'a.approved_by = b.username');
        $this->db->join('users c', 'a.approved_to = c.username');
        $this->db->where('a.approved_to', $this->session->username);
        $this->db->group_by('a.created_by');
        $setup_salaries = $this->db->get()->result_object();

        $this->db->select('b.name as fullname, a.approved_to, a.created_by, b.avatar');
        $this->db->from('permits a');
        $this->db->join('users b', 'a.approved_by = b.username');
        $this->db->join('users c', 'a.approved_to = c.username');
        $this->db->where('a.approved_to', $this->session->username);
        $this->db->group_by('a.created_by');
        $permits = $this->db->get()->result_object();

        $this->db->select('b.name as fullname, a.approved_to, a.created_by, b.avatar');
        $this->db->from('change_days a');
        $this->db->join('users b', 'a.approved_by = b.username');
        $this->db->join('users c', 'a.approved_to = c.username');
        $this->db->where('a.approved_to', $this->session->username);
        $this->db->group_by('a.created_by');
        $change_days = $this->db->get()->result_object();

        $totalRows = (count($cash_carries) + count($payrolls) + count($setup_salaries) + count($permits) + count($change_days));
        if ($totalRows > 0) {
            echo '<span class="badge">' . $totalRows . '</span>';
        } else {
            echo '';
        }
    }

    public function approvalList()
    {
        //Cash Carries
        $this->db->select('b.name as fullname, a.approved_to, a.created_by, b.avatar');
        $this->db->from('cash_carries a');
        $this->db->join('users b', 'a.approved_by = b.username');
        $this->db->join('users c', 'a.approved_to = c.username');
        $this->db->where('a.approved_to', $this->session->username);
        $this->db->group_by('a.created_by');
        $cash_carries = $this->db->get()->result_object();

        //Payrolls
        $this->db->select('b.name as fullname, a.approved_to, a.created_by, b.avatar');
        $this->db->from('payrolls a');
        $this->db->join('users b', 'a.approved_by = b.username');
        $this->db->join('users c', 'a.approved_to = c.username');
        $this->db->where('a.approved_to', $this->session->username);
        $this->db->group_by('a.created_by');
        $payrolls = $this->db->get()->result_object();

        //Setup Salary
        $this->db->select('b.name as fullname, a.approved_to, a.created_by, b.avatar');
        $this->db->from('setup_salaries a');
        $this->db->join('users b', 'a.approved_by = b.username');
        $this->db->join('users c', 'a.approved_to = c.username');
        $this->db->where('a.approved_to', $this->session->username);
        $this->db->group_by('a.created_by');
        $setup_salaries = $this->db->get()->result_object();

        //Permits
        $this->db->select('b.name as fullname, a.approved_to, a.created_by, b.avatar');
        $this->db->from('permits a');
        $this->db->join('users b', 'a.approved_by = b.username');
        $this->db->join('users c', 'a.approved_to = c.username');
        $this->db->where('a.approved_to', $this->session->username);
        $this->db->group_by('a.created_by');
        $permits = $this->db->get()->result_object();

        //Change Days
        $this->db->select('b.name as fullname, a.approved_to, a.created_by, b.avatar');
        $this->db->from('change_days a');
        $this->db->join('users b', 'a.approved_by = b.username');
        $this->db->join('users c', 'a.approved_to = c.username');
        $this->db->where('a.approved_to', $this->session->username);
        $this->db->group_by('a.created_by');
        $change_days = $this->db->get()->result_object();

        if (count($cash_carries) > 0) {
            foreach ($cash_carries as $cash_carry) {
                $this->approvalMessage($cash_carry->avatar, $cash_carry->fullname, $cash_carry->approved_to, $cash_carry->created_by, "cash_carries");
            }
        }

        if(count($payrolls) > 0) {
            foreach ($payrolls as $payroll) {
                $this->approvalMessage($payroll->avatar, $payroll->fullname, $payroll->approved_to, $payroll->created_by, "payrolls");
            }
        }

        if(count($setup_salaries) > 0) {
            foreach ($setup_salaries as $setup_salarie) {
                $this->approvalMessage($setup_salarie->avatar, $setup_salarie->fullname, $setup_salarie->approved_to, $setup_salarie->created_by, "setup_salaries");
            }
        }

        if(count($permits) > 0) {
            foreach ($permits as $permit) {
                $this->approvalMessage($permit->avatar, $permit->fullname, $permit->approved_to, $permit->created_by, "permits");
            }
        }

        if(count($change_days) > 0) {
            foreach ($change_days as $change_day) {
                $this->approvalMessage($change_day->avatar, $change_day->fullname, $change_day->approved_to, $change_day->created_by, "change_days");
            }
        }
    }

    public function approvalMessage($foto, $fullname, $approved_to, $created_by, $table){
        if ($foto == "") {
            $avatar = base_url('assets/image/users/default.png');
        } else {
            $avatar = $foto;
        }

        $link = "approvalDetail('$table', '$approved_to', '$created_by')";
        echo '  <li class="list-isi">
                    <a onclick="' . $link . '">
                        <table style="width: 100%;">
                            <tr>
                                <td>
                                    <div class="icon-container">
                                        <img src="' . $avatar . '" class="user-online" />
                                        <div class="status-circle"></div>
                                    </div>
                                </td>
                                <td style="padding-left: 10px;">
                                    <b>' . $fullname . '</b><br>
                                    <small>Sent a request to approve data <b>' . strtoupper(str_replace("_", " ", $table)) . '</b></small>
                                </td>
                            </tr>
                        </table>
                    </a>
                </li>';
    }

    public function approvalUsers($approved_to, $created_by)
    {
        $this->db->select('*');
        $this->db->from('users a');
        $this->db->where('approved_to', $approved_to);
        $this->db->where('created_by', $created_by);
        $this->db->order_by('created_date', 'DESC');
        $records = $this->db->get()->result_array();

        die(json_encode($records));
    }

    public function approvalAgreements($approved_to, $created_by)
    {
        $this->db->select('a.*, 
                b.name as employee_name, 
                c.name as division_name, 
                d.name as departement_name, 
                e.name as departement_sub_name,
                e.type, 
                f.name as position_name,
                g.name as contract_name');
        $this->db->from('agreements a');
        $this->db->join('employees b', 'a.number = b.number');
        $this->db->join('divisions c', 'b.division_id = c.id');
        $this->db->join('departements d', 'b.departement_id = d.id');
        $this->db->join('departement_subs e', 'b.departement_sub_id = e.id');
        $this->db->join('positions f', 'f.id = a.position_id');
        $this->db->join('contracts g', 'g.id = a.contract_id');
        $this->db->join('groups h', 'h.id = a.group_id');
        $this->db->where('a.approved_to', $approved_to);
        $this->db->where('a.created_by', $created_by);
        $records = $this->db->get()->result_array();

        die(json_encode($records));
    }

    public function approvalMutations($approved_to, $created_by)
    {
        $this->db->select('a.*, 
                b.number as employee_number, 
                b.name as employee_name, 
                c.name as division_name, 
                d.name as departement_name,
                e.name as departement_sub_name');
        $this->db->from('mutations a');
        $this->db->join('employees b', 'a.employee_id = b.id');
        $this->db->join('divisions c', 'a.division_id = c.id');
        $this->db->join('departements d', 'a.departement_id = d.id');
        $this->db->join('departement_subs e', 'a.departement_sub_id = e.id');
        $this->db->where('a.deleted', 0);
        $this->db->where('a.approved_to', $approved_to);
        $this->db->where('a.created_by', $created_by);
        $records = $this->db->get()->result_array();

        die(json_encode($records));
    }

    public function approvalPermits($approved_to, $created_by)
    {
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

        die(json_encode($records));
    }

    public function approvalOvertimes($approved_to, $created_by)
    {
        $this->db->select('a.*,
                c.name as division_name,
                d.name as departement_name,
                e.name as departement_sub_name,
                b.number as employee_number,
                b.name as employee_name,
                f.name as fullname
            ');
        $this->db->from('overtimes a');
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

        die(json_encode($records));
    }

    public function approvalCashCarries($approved_to, $created_by)
    {
        $this->db->select('a.*, 
                c.name as division_name,
                d.name as departement_name,
                e.name as departement_sub_name,
                b.number as employee_number,
                b.name as employee_name,
                f.name as fullname
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

        die(json_encode($records));
    }

    public function approvalChangeDays($approved_to, $created_by)
    {
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
        $this->db->where('b.deleted', 0);
        $this->db->where('b.status', 0);
        $this->db->where('a.deleted', 0);
        $this->db->where('a.approved_to', $approved_to);
        $this->db->where('a.created_by', $created_by);
        $this->db->group_by('a.start');
        $this->db->group_by('a.employee_id');
        $this->db->order_by('a.created_date', 'DESC');
        $records = $this->db->get()->result_array();

        die(json_encode($records));
    }

    public function approvalPayrolls($approved_to, $created_by)
    {
        $this->db->select('a.period_start, a.period_end, b.group_id, d.name as group_name, c.name, a.created_date, COUNT(a.employee_id) as employee, SUM(a.net_income) as amount');
        $this->db->from('payrolls a');
        $this->db->join('employees b', 'a.employee_id = b.id');
        $this->db->join('users c', 'a.created_by = c.username');
        $this->db->join('groups d', 'b.group_id = d.id');
        $this->db->where('b.deleted', 0);
        $this->db->where('b.status', 0);
        $this->db->where('a.status', 0);
        $this->db->where('a.approved_to', $approved_to);
        $this->db->where('a.created_by', $created_by);
        $this->db->group_by('b.group_id');
        $records = $this->db->get()->result_array();

        $total_employee = 0;
        $total_amount = 0;
        foreach ($records as $record) {
            $total_employee += $record['employee'];
            $total_amount += $record['amount'];
        }

        $data['total'] = count($records);
        $data['rows'] = $records;
        $data['footer'] = array(['group_name' => 'GRAND TOTAL', 'employee' => $total_employee, 'amount' => $total_amount, 'action' => "-"]);

        die(json_encode($data));
    }

    public function approvalSetupSalary($approved_to, $created_by)
    {
        $this->db->select('a.*,
                c.name as division_name,
                d.name as departement_name,
                e.name as departement_sub_name,
                b.number as employee_number,
                b.name as employee_name,
                f.name as fullname,
                h.name as salary_component_name
            ');
        $this->db->from('setup_salaries a');
        $this->db->join('employees b', 'a.employee_id = b.id');
        $this->db->join('divisions c', 'b.division_id = c.id');
        $this->db->join('departements d', 'b.departement_id = d.id');
        $this->db->join('departement_subs e', 'b.departement_sub_id = e.id');
        $this->db->join('users f', "a.created_by = f.username");
        $this->db->join('salary_components h', 'a.salary_component_id = h.id', 'left');
        $this->db->where('b.deleted', 0);
        $this->db->where('b.status', 0);
        $this->db->where('a.approved_to', $approved_to);
        $this->db->where('a.created_by', $created_by);
        $this->db->group_by('a.employee_id');
        $this->db->order_by('b.name', 'ASC');
        $records = $this->db->get()->result_array();

        die(json_encode($records));
    }

    public function approvalWarningLetters($approved_to, $created_by)
    {
        $this->db->select('a.*,
                c.name as division_name,
                d.name as departement_name,
                e.name as departement_sub_name,
                b.number as employee_number,
                b.name as employee_name,
                f.name as fullname,
                h.name as violation_name
            ');
        $this->db->from('warning_letters a');
        $this->db->join('employees b', 'a.employee_id = b.id');
        $this->db->join('divisions c', 'b.division_id = c.id');
        $this->db->join('departements d', 'b.departement_id = d.id');
        $this->db->join('departement_subs e', 'b.departement_sub_id = e.id');
        $this->db->join('users f', "a.created_by = f.username");
        $this->db->join('violations h', 'a.violation_id = h.id', 'left');
        $this->db->where('b.deleted', 0);
        $this->db->where('b.status', 0);
        $this->db->where('a.approved_to', $approved_to);
        $this->db->where('a.created_by', $created_by);
        $this->db->group_by('a.employee_id');
        $this->db->order_by('b.name', 'ASC');
        $records = $this->db->get()->result_array();

        die(json_encode($records));
    }

    public function approvalResignations($approved_to, $created_by)
    {
        $this->db->select('a.*, 
                g.users_id_from as status_check,
                g.users_id_to as status_notification, 
                g.updated_date as status_date,
                c.name as division_name,
                d.name as departement_name,
                e.name as departement_sub_name,
                b.number as employee_number,
                b.name as employee_name,
                f.name as fullname,
                h.name as reason_name
            ');
        $this->db->from('resignations a');
        $this->db->join('employees b', 'a.employee_id = b.id');
        $this->db->join('divisions c', 'b.division_id = c.id');
        $this->db->join('departements d', 'b.departement_id = d.id');
        $this->db->join('departement_subs e', 'b.departement_sub_id = e.id');
        $this->db->join('users f', "a.created_by = f.username");
        $this->db->join('notifications g', "a.id = g.table_id and g.table_name = 'resignations'", 'left');
        $this->db->join('reason_resignations h', 'a.reason_resignation_id = h.id', 'left');
        $this->db->where('b.deleted', 0);
        $this->db->where('b.status', 0);
        $this->db->where('a.approved_to', $approved_to);
        $this->db->where('a.created_by', $created_by);
        $this->db->group_by('a.employee_id');
        $this->db->order_by('b.name', 'ASC');
        $records = $this->db->get()->result_array();

        die(json_encode($records));
    }
}
