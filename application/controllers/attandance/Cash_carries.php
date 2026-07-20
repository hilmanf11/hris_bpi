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

        //VALIDASI FORM
        $this->form_validation->set_rules('number', 'Employee', 'required|min_length[1]|max_length[30]');
    }

    //HALAMAN UTAMA
    public function index()
    {
        if (empty($this->session->username)) {
            redirect('error_session');
        } elseif ($this->checkuserAccess($this->id_menu()) > 0) {
            $data['button'] = $this->getbutton($this->id_menu());

            $this->load->view('template/header', $data);
            $this->load->view('attandance/cash_carries');
        } else {
            redirect('error_access');
        }
    }

    //GET DATA
    public function reads()
    {
        $post = isset($_POST['q']) ? $_POST['q'] : "";
        $send = $this->crud->reads('cash_carries', ["name" => $post]);
        echo json_encode($send);
    }

    public function readIdm()
    {
        $this->db->select('idm_no');
        $this->db->from('cash_carries');
        $this->db->group_by('idm_no');
        $records = $this->db->get()->result_array();
        echo json_encode($records);
    }

    public function readRequestCode()
    {
        $filter_from = $this->input->get('filter_from');
        $filter_to = $this->input->get('filter_to');
        $filter_division = $this->input->get('filter_division');
        $filter_departement = $this->input->get('filter_departement');
        $filter_departement_sub = $this->input->get('filter_departement_sub');

        $this->db->select('a.request_code');
        $this->db->from('cash_carries a');
        $this->db->join('employees b', 'a.employee_id = b.id');
        if ($filter_from != "" && $filter_to != "") {
            $this->db->where('a.trans_date >=', $filter_from);
            $this->db->where('a.trans_date <=', $filter_to);
        }
        $this->db->like('b.division_id', $filter_division);
        $this->db->like('b.departement_id', $filter_departement);
        $this->db->like('b.departement_sub_id', $filter_departement_sub);
        $this->db->group_by('a.request_code');
        $records = $this->db->get()->result_array();
        echo json_encode($records);
    }

    public function requestCode()
    {
        $trans_date = $this->input->post('trans_date');

        $date = date("ymd", strtotime($trans_date));
        $this->db->select('max(SUBSTRING(request_code, -4)) as code');
        $this->db->from('cash_carries');
        $this->db->where('deleted', 0);
        $this->db->where('SUBSTRING(request_code, 4, 6)=', $date);
        $records = $this->db->get()->row();

        $requestcode = (int) $records->code;
        $requestcode++;

        $template = "CC/" . $date . "/";
        echo $template . sprintf("%04s", $requestcode);
    }

    public function requestIdm()
    {
        $trans_date = $this->input->post('trans_date');
        $departement = $this->input->post('departement');

        $date = $departement . date("ym", strtotime($trans_date));
        $this->db->select('max(idm_no) as code');
        $this->db->from('cash_carries');
        $this->db->where('deleted', 0);
        $this->db->like('idm_no', $date);
        $records = $this->db->get()->row();

        $requestcode = (int) $records->code;
        $requestcode++;

        echo $date . sprintf("%04s", $requestcode);
    }

    public function readOvertimePrice($employee_id, $trans_date, $duration, $meal)
    {
        $employee = $this->crud->read("employees", [], ["id" => $employee_id]);
        $this->db->select('c.days');
        $this->db->from('shift_employees a');
        $this->db->join('shifts b', 'a.shift_id = b.id', 'left');
        $this->db->join('shift_details c', 'b.id = c.shift_id', 'left');
        $this->db->where('a.employee_id', $employee_id);
        $shift_employee = $this->db->get()->row();

        $data_employee = $this->crud->read("employees", [], ["id" => $employee_id]);
        $setup_cash_carries = $this->crud->read("setup_cash_carries", [], ["employee_id" => $employee_id]);
        $allowance_cash_carries = $this->crud->read("allowance_cash_carries", [], ["id" => @$setup_cash_carries->allowance_id]);

        if(empty($setup_cash_carries)){
            echo  json_encode(array("title" => "Not Found", "message" => "($data_employee->number - $data_employee->name) Data Setup Overtimes Not Found", "theme" => "danger"));
            exit();
        }

        if(empty($allowance_cash_carries)){
            echo  json_encode(array("title" => "Not Found", "message" => "($data_employee->number - $data_employee->name) Data Allowance Overtimes Not Found", "theme" => "danger"));
            exit();
        }

        //ambil durasi berapa jam jika ada lemburan
        $hourTrans = strtotime($trans_date);

        //cek apakah ada hari libur nasional per tanggal dari looping
        $this->db->select('trans_date');
        $this->db->from('calendars');
        $this->db->where('trans_date', $trans_date);
        $calendars = $this->db->get()->result_array();

        //Validasi Uang makan
        if ($meal == 0) {
            $meal = 0;
        } else {
            $meal = @$allowance_cash_carries->meal;
        }

        if (@$shift_employee->days == "5") {
            if (date('w', $hourTrans) !== '0' && date('w', $hourTrans) !== '6') {

                //Kalo ada tanggal Merah
                if (count($calendars) > 0) {
                    $total = ((@$allowance_cash_carries->holiday * $duration) + $meal);
                } else {
                    $total = ((@$allowance_cash_carries->weekday * $duration) + $meal);
                }
            } else {
                if (date('w', $hourTrans) === '0') {
                    $total = ((@$allowance_cash_carries->sunday * $duration) + $meal);
                } else {
                    $total = ((@$allowance_cash_carries->saturday * $duration) + $meal);
                }
            }
        } else {
            if (date('w', $hourTrans) !== '0') {

                //Kalo ada tanggal Merah
                if (count($calendars) > 0) {
                    $total = ((@$allowance_cash_carries->holiday * $duration) + $meal);
                } else {
                    $total = ((@$allowance_cash_carries->weekday * $duration) + $meal);
                }
            } else {
                if (date('w', $hourTrans) === '0') {
                    $total = ((@$allowance_cash_carries->sunday * $duration) + $meal);
                } else {
                    $total = ((@$allowance_cash_carries->saturday * $duration) + $meal);
                }
            }
        }

        return array(
            'amount' => $total,
            'amount_perhour' => $allowance_cash_carries->weekday,
            'meal_amount' => $meal,
        );
    }

    public function convertHour($trans_date, $start, $end, $employee_id, $break)
    {
        //Cek data Setup
        $this->db->select('b.name');
        $this->db->from('setup_cash_carries a');
        $this->db->join('allowance_cash_carries b', 'a.allowance_id = b.id');
        $this->db->where('a.employee_id', $employee_id);
        $setup = $this->db->get()->row();

        //Set Duration
        $time_begin = strtotime($trans_date . " " . $start);
        $time_end = strtotime($trans_date . " " . $end);
        $tomorrow = strtotime(date('Y-m-d', strtotime($trans_date . "+1 days")) . " " . $end);

        $diff = ($time_end - $time_begin - ($break * 60));
        $hour = floor($diff / (60 * 60));
        $minutes = ($diff - $hour * (60 * 60));

        if ($hour < 0) {
            $diff = ($tomorrow - $time_begin - ($break * 60));
            $hour = floor($diff / (60 * 60));
            $minutes = ($diff - $hour * (60 * 60));
        }

        $final_minutes = floor($minutes / 60);
        $final_duration = ($final_minutes / 60);

        //if($setup->name == "DRIVER"){
        //if (floor(($minutes / 60) - $break) == 59) {
        //$final_minutes = floor(($minutes / 60) - $break);
        // } else if (floor(($minutes / 60) - $break) >= 29 and floor(($minutes / 60) - $break) <= 58) {
        //     $final_minutes = "50";
        // } else {
        //     $final_minutes = "0";
        // }
        // }else{
        //     $final_minutes = floor($minutes / 60);
        // }

        $duration = $hour . " Hour " . $final_minutes . " Minutes";
        $duration_hour = ($hour + $final_duration);

        // $duration_hour = round($diff / (60 * 60), 2);
        $arr = array("duration" => $duration, "duration_hour" => $duration_hour);
        return $arr;
    }

    public function readGenerate(){
        $period = $this->input->post('period');
        $division_id = $this->input->post('division_id');
        $departement_id = $this->input->post('departement_id');
        $departement_sub_id = $this->input->post('departement_sub_id');

        $this->db->select('a.time_in, a.time_out, b.id, c.overtime_duration');
        $this->db->from('employees b');
        $this->db->join('groups c', 'b.group_id = c.id');
        $this->db->join('attandances a', "a.number = b.number and a.date_in = '$period'", 'left');
        $this->db->where('b.status', 0);
        $this->db->where('c.overtime_duration >', 0);
        $this->db->like('b.division_id', $division_id);
        $this->db->like('b.departement_id', $departement_id);
        $this->db->like('b.departement_sub_id', $departement_sub_id);
        $this->db->group_by('b.id');
        $employees = $this->db->get()->result_array();

        $date = date("ymd", strtotime($period));
        $this->db->select('max(SUBSTRING(request_code, -4)) as code');
        $this->db->from('cash_carries');
        $this->db->where('deleted', 0);
        $this->db->where('SUBSTRING(request_code, 4, 6)=', $date);
        $records = $this->db->get()->row();

        $requestcode = (int) $records->code;
        $requestcode++;

        $template = "GN/" . $date . "/";
        $templatefinal = $template . sprintf("%04s", $requestcode);

        //IDM NO
        $template_2 = "GNRT" . date("ym", strtotime($period));
        $this->db->select('max(idm_no) as code');
        $this->db->from('cash_carries');
        $this->db->where('deleted', 0);
        $this->db->like('idm_no', $date);
        $records2 = $this->db->get()->row();

        $requestcode2 = (int) $records2->code;
        $requestcode2++;

        $templatefinal2 = $template_2 . sprintf("%04s", $requestcode2);

        $arr = array();
        foreach ($employees as $employee) {
            $ot_amount = $this->readOvertimePrice($employee['id'], $period, $employee['overtime_duration'], 0);

            //Shift and Setting Group
            $tolerance_hour_min = date("H:i:s", strtotime('-2 Hour', strtotime(@$employee['time_in'])));
            $tolerance_hour_plus = date("H:i:s", strtotime('+2 Hour', strtotime(@$employee['time_in'])));
            $this->db->select("d.ot_start, d.ot_end");
            $this->db->from('shift_employees b');
            $this->db->join('shifts c', 'c.id = b.shift_id');
            $this->db->join('shift_details d', 'd.shift_id = c.id');
            $this->db->where('b.employee_id', $employee['id']);
            if (@$employee['time_in'] > "23:00:00") {
                $this->db->where("d.start >= '$tolerance_hour_min'");
            } elseif (@$employee['time_in'] != "") {
                $this->db->where("d.start >= '$tolerance_hour_min' and d.start <= '$tolerance_hour_plus'");
            }
            $shift = $this->db->get()->row();

            $arr[] = array(
                "trans_date" =>  $period,
                "employee_id" =>  $employee['id'],
                "request_code" =>  $templatefinal,
                "request_name" =>  $this->session->name,
                "start" => @$shift->ot_start,
                "end" =>  @$shift->ot_end,
                "type" =>  "REGULAR",
                "duration" =>  "-",
                "duration_hour" => $employee['overtime_duration'],
                "amount" =>  $ot_amount,
                "meal" =>  0,
                "plan" =>  0,
                "actual" =>  0,
                "break" =>  0,
                "remarks" =>  "GENERATE",
                "idm_no" => $templatefinal2,
            );
        }

        $data['total'] = count($arr);
        $data['rows'] = $arr;
        die(json_encode($data));
    }

    //GET DATATABLES
    public function datatables()
    {
        if ($this->input->post()) {
            $filter_from = $this->input->get('filter_from');
            $filter_to = $this->input->get('filter_to');
            $filter_division = $this->input->get('filter_division');
            $filter_departement = $this->input->get('filter_departement');
            $filter_departement_sub = $this->input->get('filter_departement_sub');
            $filter_employee = $this->input->get('filter_employee');
            $filter_request_code = base64_decode($this->input->get('filter_request_code'));
            $filter_idm = base64_decode($this->input->get('filter_idm'));
            $filter_approval = $this->input->get('filter_approval');
            $aprvDepartement = $this->checkApprovalAccess('cash_carries');

            $page   = $this->input->post('page');
            $rows   = $this->input->post('rows');

            //Pagination 1-10
            $page   = isset($page) ? intval($page) : 1;
            $rows   = isset($rows) ? intval($rows) : 10;
            $offset = ($page - 1) * $rows;
            $result = array();
            //Select Query
            $this->db->select('a.*, 
                b.position_id,
                c.name as division_name,
                d.name as departement_name,
                e.name as departement_sub_name,
                b.number as employee_number,
                b.name as employee_name,
                f.name as fullname,
                COALESCE(i.weekday, 0) as total_weekday,
                COALESCE(i.sunday, 0) as total_sunday,
                COALESCE(i.saturday, 0) as total_saturday,
                COALESCE(i.holiday, 0) as total_holiday,
                COALESCE(i.meal, 0) as total_meal
            ');

            $this->db->from('cash_carries a');
            $this->db->join('employees b', 'a.employee_id = b.id');
            $this->db->join('divisions c', 'b.division_id = c.id');
            $this->db->join('departements d', 'b.departement_id = d.id');
            $this->db->join('departement_subs e', 'b.departement_sub_id = e.id');
            $this->db->join('users f', "a.created_by = f.username");
            // $this->db->join('notifications g', "a.id = g.table_id and g.table_name = 'cash_carries'", 'left');
            $this->db->join('setup_cash_carries h', 'a.employee_id = h.employee_id', 'left');
            $this->db->join('allowance_cash_carries i', 'h.allowance_id = i.id', 'left');
            $this->db->where('b.deleted', 0);
            $this->db->where('b.status', 0);
            $this->db->where('a.deleted', 0);
            $this->db->like('b.departement_id', $aprvDepartement);
            if ($filter_from != "" && $filter_to != "") {
                $this->db->where('a.trans_date >=', $filter_from);
                $this->db->where('a.trans_date <=', $filter_to);
            } else {
                $this->db->where('a.trans_date >=', date("Y-m-01"));
                $this->db->where('a.trans_date <=', date("Y-m-t"));
            }
            $this->db->like('b.division_id', $filter_division);
            $this->db->like('b.departement_id', $filter_departement);
            $this->db->like('b.departement_sub_id', $filter_departement_sub);
            $this->db->like('b.id', $filter_employee);
            if ($filter_request_code != "") {
                $this->db->where('a.request_code', $filter_request_code);
            }
            if ($filter_idm != "") {
                $this->db->where('a.idm_no', $filter_idm);
            }
            if ($filter_approval == "0") {
                $this->db->where("(a.approved_to = '' or a.approved_to is null)");
            } elseif ($filter_approval == "1") {
                $this->db->where("(a.approved_to != '' and a.approved_to is not null)");
            }
            $this->db->group_by('a.trans_date');
            $this->db->group_by('a.employee_id');
            $this->db->group_by('a.request_code');
            $this->db->order_by('a.trans_date', 'DESC');

            //Total Data
            $totalRows = $this->db->count_all_results('', false);
            //Limit 1 - 10
            $this->db->limit($rows, $offset);
            //Get Data Array
            $records = $this->db->get()->result_array();

            $datas = [];
            $total = 0;
            foreach ($records as $record) {
                $this->db->select('*');
                $this->db->from('attandances');
                $this->db->where('number', $record['employee_number']);
                $this->db->where('date_in', $record['trans_date']);
                $attandance = $this->db->get()->row();

                $this->db->select('c.days');
                $this->db->from('shift_employees a');
                $this->db->join('shifts b', 'a.shift_id = b.id', 'left');
                $this->db->join('shift_details c', 'b.id = c.shift_id', 'left');
                $this->db->where('a.employee_id', $record['employee_id']);
                $shift_employee = $this->db->get()->row();

                $start = strtotime($record['trans_date']);
                $att_time_begin = strtotime(@$attandance->date_in . " " . @$attandance->time_in);
                $att_time_end = strtotime(@$attandance->date_out . " " . @$attandance->time_out);

                $tomorrow = strtotime(date('Y-m-d', strtotime(@$attandance->date_out . "+1 days")) . " " . @$attandance->time_out);

                $att_diff = $att_time_end - $att_time_begin;
                $att_hour = floor($att_diff / (60 * 60));

                if ($att_hour < 0) {
                    $att_diff = $tomorrow - $att_time_begin;
                    $att_hour = floor($att_diff / (60 * 60));
                }

                $cc_hour = $record['duration_hour'];

                 //Validasi Jam
                if ($att_hour > $cc_hour) {
                    $hour = $cc_hour;
                } else {
                    $hour = $att_hour;
                }



                //Validasi Uang makan
                $meal = $record['meal_amount'];

                $total = ((@$record['amount_perhour'] * $hour) + $meal);


                $amount = ["amount_actual" => $total, "duration_att" => number_format($hour, 2), "time_in" => @$attandance->time_in, "time_out" => @$attandance->time_out];
                $datas[] = array_merge($record, $amount);
            }

            //Mapping Data
            $result['total'] = $totalRows;
            $result = array_merge($result, ['rows' => @$datas]);
            echo json_encode($result);
        }
    }

    public function datatableTemp(){
        $departement_id = base64_decode($this->input->get('departement_id'));

        $this->db->select('id as employee_id, name as employee_name');
        $this->db->from('employees');
        $this->db->where('status', 0);
        $this->db->where('departement_id', $departement_id);
        $this->db->order_by('name', 'ASC');
        $records = $this->db->get()->result_array();
        
        echo json_encode($records);
    }

    public function datatable_updates()
    {
        $id = base64_decode($this->input->get('id'));

        $this->db->select('a.*, b.name as employee_name');
        $this->db->from('cash_carries a');
        $this->db->join('employees b', 'a.employee_id = b.id');
        $this->db->where('a.id', $id);
        $this->db->order_by('b.name', 'ASC');
        $records = $this->db->get()->result_array();
        echo json_encode($records);
    }

    //CREATE DATA
    public function create()
    {
        if ($this->input->post()) {
            $post = $this->input->post();
            $trans_date = $post['trans_date'];
            $employee_id = $post['employee_id'];
            $request_code = $post['request_code'];
            $request_name = $post['request_name'];
            $idm_no = $post['idm_no'];
            $start = $post['start'];
            $end = $post['end'];
            $break = $post['break'];
            $type = $post['type'];
            $meal = $post['meal'];
            $plan = $post['plan'];
            $actual = $post['actual'];
            $attachment = trim($post['attachment']);
            $attachment_idm = trim($post['attachment_idm']);
            $remarks = $post['remarks'];
            $duration = $this->convertHour($trans_date, $start, $end, $employee_id, $break);
            $data_amount = $this->readOvertimePrice($employee_id, $trans_date, $duration['duration_hour'], $meal);

            $post_final = array(
                "trans_date" =>  $trans_date,
                "employee_id" =>  $employee_id,
                "request_code" =>  $request_code,
                "request_name" =>  $request_name,
                "start" =>  $start,
                "end" =>  $end,
                "type" =>  $type,
                "duration" =>  $duration['duration'],
                "duration_hour" => $duration['duration_hour'],
                "amount_perhour" =>  $data_amount['amount_perhour'],
                "amount" =>  $data_amount['amount'],
                "meal" =>  $meal,
                "meal_amount" => $data_amount['meal_amount'],
                "plan" =>  $plan,
                "actual" =>  $actual,
                "break" =>  $break,
                "remarks" =>  $remarks,
                "idm_no" =>  $idm_no,
                "attachment_idm" =>  $attachment_idm,
                "attachment" =>  $attachment,
            );

            $cash_carries = $this->crud->reads('cash_carries', [], ["employee_id" => $employee_id, "trans_date" => $trans_date, "request_code" => $request_code]);

            if (count($cash_carries) > 0) {
                echo json_encode(array("title" => "Duplicate", "message" => "Overtime has been created", "theme" => "error"));
            } else {
                $send = $this->crud->create('cash_carries', $post_final);
                echo $send;
            }
        } else {
            show_error("Cannot Process your request");
        }
    }

    public function createGenerate(){
        if ($this->input->post()) {
            $post = $this->input->post();

            $cash_carries = $this->crud->reads('cash_carries', [], [
                "employee_id" => $post['employee_id'], 
                "trans_date" => $post['trans_date'], 
                "type" => $post['type']
            ]);

            if (count($cash_carries) > 0) {
                echo json_encode(array("title" => "Duplicate", "message" => "Overtime has been created", "theme" => "error"));
            } else {
                $send = $this->crud->create('cash_carries', $post);
                echo $send;
            }
        }else{
            show_error("Cannot Process your request");
        }
    }

    //UPDATE DATA
    public function update()
    {
        if ($this->input->post()) {
            $post = $this->input->post();
            $trans_date = $post['trans_date'];
            $employee_id = $post['employee_id'];
            $request_code = $post['request_code'];
            $request_name = $post['request_name'];
            $idm_no = $post['idm_no'];
            $start = $post['start'];
            $end = $post['end'];
            $break = $post['break'];
            $type = $post['type'];
            $meal = $post['meal'];
            $plan = $post['plan'];
            $actual = $post['actual'];
            $remarks = $post['remarks'];
            $duration = $this->convertHour($trans_date, $start, $end, $employee_id, $break);
            $data_amount = $this->readOvertimePrice($employee_id, $trans_date, $duration['duration_hour'], $meal);

            $post_final = array(
                "trans_date" =>  $trans_date,
                "employee_id" =>  $employee_id,
                "request_code" =>  $request_code,
                "request_name" =>  $request_name,
                "start" =>  $start,
                "end" =>  $end,
                "type" =>  $type,
                "duration" =>  $duration['duration'],
                "duration_hour" => $duration['duration_hour'],
                "amount_perhour" =>  $data_amount['amount_perhour'],
                "amount" =>  $data_amount['amount'],
                "meal" =>  $meal,
                "meal_amount" => $data_amount['meal_amount'],
                "plan" =>  $plan,
                "actual" =>  $actual,
                "break" =>  $break,
                "remarks" =>  $remarks,
                "idm_no" =>  $idm_no
            );

            if ($this->crud->update('cash_carries', ["request_code" => $request_code, "employee_id" => $employee_id], $post_final)) {
                echo json_encode(array("title" => "Good Job", "message" => "Data Updated Successfully", "theme" => "success"));
            } else {
                echo log_message('error', 'There is an error in your system or data');
            }
        } else {
            show_error("Cannot Process your request");
        }
    }

    //DELETE DATA
    public function delete()
    {
        $data = $this->input->post();
        $delete = $this->crud->delete("cash_carries", ['id' => $data['id']]);

        @unlink(base_url("assets/image/cash_carries/" . $data['attachment']));
        echo $delete;
    }

    //UPLOAD FILE
    public function uploadFile()
    {

        $newName = "";
        $newName2 = "";

        if (!empty($_FILES["attachment"]["name"])) {
            //Setting Upload Image
            $request_code = trim($this->input->post('request_code'));

            $file = $_FILES["attachment"]["name"];
            $extension_explode = explode('.', $file);
            $extension_final = strtolower(end($extension_explode));
            $size = $_FILES["attachment"]['size'];
            $temporary = $_FILES["attachment"]['tmp_name'];
            $newName =  "ATT_" . time() . "." . $extension_final;

            if (in_array($extension_final, ['png', 'jpg', 'jpeg', 'pdf']) === true || $file == "") {
                if ($size < 2097152) {
                    @move_uploaded_file($temporary, "assets/image/cash_carries/" . $newName);
                }
            }
        }

        if (!empty($_FILES["attachment_idm"]["name"])) {
            //Setting Upload Image
            $idm_no = trim($this->input->post('idm_no'));

            $file2 = $_FILES["attachment_idm"]["name"];
            $extension_explode2 = explode('.', $file2);
            $extension_final2 = strtolower(end($extension_explode2));
            $size2 = $_FILES["attachment_idm"]['size'];
            $temporary2 = $_FILES["attachment_idm"]['tmp_name'];
            $newName2 =  "IDM_" . time() . "." . $extension_final2;

            if (in_array($extension_final2, ['png', 'jpg', 'jpeg', 'pdf']) === true || $file2 == "") {
                if ($size2 < 2097152) {
                    @move_uploaded_file($temporary2, "assets/image/cash_carries/" . $newName2);
                }
            }
        }

        echo json_encode(array("attachment" => $newName, "attachment_idm" => $newName2));
    }

    function formatTanggal($date)
    {
        // menggunakan class Datetime
        $datetime = DateTime::createFromFormat('d/m/Y', $date);
        return @$datetime->format('Y-m-d');
    }

    //UPLOAD DATA
    public function upload()
    {
        error_reporting(0);
        require_once 'assets/vendors/excel_reader2.php';
        $target = basename($_FILES['file_upload']['name']);
        move_uploaded_file($_FILES['file_upload']['tmp_name'], $target);
        chmod($_FILES['file_upload']['name'], 0777);
        $file = $_FILES['file_upload']['name'];
        $data = new Spreadsheet_Excel_Reader($file, false);
        $total_row = $data->rowcount($sheet_index = 0);

        //Setting Upload Image
        $idm_no = trim($this->input->post('idm_no'));

        if (!empty($_FILES["attachment_idm"]["name"])) {
            $file2 = $_FILES["attachment_idm"]["name"];
            $extension_explode2 = explode('.', $file2);
            $extension_final2 = strtolower(end($extension_explode2));
            $size2 = $_FILES["attachment_idm"]['size'];
            $temporary2 = $_FILES["attachment_idm"]['tmp_name'];
            $newName2 =  "IDM_" . time() . "." . $extension_final2;

            if (in_array($extension_final2, ['png', 'jpg', 'jpeg', 'pdf']) === true || $file2 == "") {
                if ($size2 < 2097152) {
                    @move_uploaded_file($temporary2, "assets/image/cash_carries/" . $newName2);
                }
            }
        }

        for ($i = 3; $i <= $total_row; $i++) {
            // AMBIL KOLOM KUNCI (Contoh: Kolom 2 / EMPLOYE ID)
            $employee_id = trim($data->val($i, 2));
            $trans_date  = trim($data->val($i, 3));

            // JIKA KOLOM KUNCI KOSONG, BERHENTI MEMBACA
            if (empty($employee_id) && empty($trans_date)) {
                break; // Menghentikan loop jika baris sudah benar-benar kosong
            }
            $datas[] = array(
                'number' => $data->val($i, 2),
                'trans_date' => $data->val($i, 3),
                'start' => date("H:i", strtotime($data->val($i, 4))),
                'end' => date("H:i", strtotime($data->val($i, 5))),
                'duration_hour' => $data->val($i, 6),
                'break' => $data->val($i, 7),
                'type' => $data->val($i, 8),
                'meal' => $data->val($i, 9),
                'plan' => $data->val($i, 10),
                'actual' => $data->val($i, 11),
                'remarks' => $data->val($i, 12),
                'request_code' => $templatefinal,
                'idm_no' => $idm_no,
                'attachment_idm' => $newName2
            );
        }

        $datas['total'] = count($datas);
        echo json_encode($datas);
        unlink($_FILES['file_upload']['name']);
    }

    public function uploadclearFailed()
    {
        @unlink('failed/cash_carries.txt');
    }

    public function uploadcreateFailed()
    {
        if ($this->input->post()) {
            $message = $this->input->post('message');
            $textFailed = fopen('failed/cash_carries.txt', 'a');
            fwrite($textFailed, $message . "\n");
            fclose($textFailed);
        }
    }

    public function uploadDownloadFailed()
    {
        $file = "failed/cash_carries.txt";

        header('Content-Description: File Failed');
        header('Content-Disposition: attachment; filename=' . basename($file));
        header('Expires: 0');
        header('Cache-Control: must-revalidate');
        header('Pragma: public');
        header('Content-Length: ' . @filesize($file));
        header("Content-Type: text/plain");
        @readfile($file);
    }

    public function uploadcreate()
    {
        if ($this->input->post()) {
            $data = $this->input->post('data');
            if ($data['trans_date'] == "") {
                echo json_encode(array("title" => "Format Date", "message" => $data['number'] . " Format Date is Null", "theme" => "error"));
            } else {
                $employee = $this->crud->read('employees', [], ["number" => $data['number']]);

                if (!empty($employee)) {
                    $cash_carries = $this->crud->read('cash_carries', [], ["employee_id" => $employee->id, "trans_date" => $data['trans_date'], "type" => $data['type']]);

                    if (!empty($cash_carries)) {
                        echo json_encode(array("title" => "Available", "message" => $employee->name . " has been created", "theme" => "error"));
                    } else {
                        if ($data['meal'] == "YES") {
                            $meal = "1";
                        } else {
                            $meal = "0";
                        }

                        if ($data['break'] == "" || $data['break'] == "0") {
                            $break = 0;
                        } else {
                            $break = $data['break'];
                        }

                        $date = date("ymd", strtotime($data['trans_date']));
                        $this->db->select('max(SUBSTRING(request_code, -4)) as code');
                        $this->db->from('cash_carries');
                        $this->db->where('deleted', 0);
                        $this->db->where('SUBSTRING(request_code, 4, 6)=', $date);
                        $records = $this->db->get()->row();

                        $requestcode = (int) $records->code;
                        $requestcode++;

                        $template = "CC/" . $date . "/";
                        $templatefinal = $template . sprintf("%04s", $requestcode);

                        $duration = $this->convertHour($data['trans_date'], $data['start'], $data['end'], $employee->id, $break);
                        $data_amount = $this->readOvertimePrice($employee->id, $data['trans_date'], $duration['duration_hour'], $meal);

                        $post_cash_carries = array(
                            'employee_id' => $employee->id,
                            'trans_date' => $data['trans_date'],
                            'request_code' => $templatefinal,
                            'request_name' => $this->session->name,
                            'start' => $data['start'],
                            'end' => $data['end'],
                            'break' => $break,
                            'type' => $data['type'],
                            'meal' => $meal,
                            'meal_amount' => $data_amount['meal_amount'],
                            'plan' => $data['plan'],
                            'actual' => $data['actual'],
                            "duration" =>  $duration['duration'],
                            "duration_hour" => $duration['duration_hour'],
                            'remarks' => $data['remarks'],
                            'amount' =>  $data_amount['amount'],
                            'amount_perhour' =>  $data_amount['amount_perhour'],
                            'idm_no' => $data['idm_no'],
                            'attachment_idm' => $data['attachment_idm']
                        );

                        $send = $this->crud->create('cash_carries', $post_cash_carries);
                        echo $send;
                    }
                } else {
                    echo json_encode(array("title" => "Not Found", "message" => $data['number'] . " Employee ID Not Found", "theme" => "error"));
                }
            }
        }
    }

    //PRINT & EXCEL DATA
    public function print($option = "")
    {
        if ($option == "excel") {
            $format  = date("Ymd");
            header("Content-type: application/vnd-ms-excel");
            header("Content-Disposition: attachment; filename=cash_carries_$format.xls");
        }

        $filter_from = $this->input->get('filter_from');
        $filter_to = $this->input->get('filter_to');
        $filter_division = $this->input->get('filter_division');
        $filter_departement = $this->input->get('filter_departement');
        $filter_departement_sub = $this->input->get('filter_departement_sub');
        $filter_employee = $this->input->get('filter_employee');
        $filter_request_code = base64_decode($this->input->get('filter_request_code'));
        $filter_idm = base64_decode($this->input->get('filter_idm'));
        $filter_approval = $this->input->get('filter_approval');
        $aprvDepartement = $this->checkApprovalAccess('cash_carries');

        //Config
        $this->db->select('*');
        $this->db->from('config');
        $config = $this->db->get()->row();

        //Select Query
        $this->db->select('a.*, 
                b.position_id,
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
        // $this->db->join('notifications g', "a.id = g.table_id and g.table_name = 'cash_carries'", 'left');
        $this->db->where('b.deleted', 0);
        $this->db->where('b.status', 0);
        $this->db->where('a.deleted', 0);
        $this->db->like('b.departement_id', $aprvDepartement);
        if ($filter_from != "" && $filter_to != "") {
            $this->db->where('a.trans_date >=', $filter_from);
            $this->db->where('a.trans_date <=', $filter_to);
        } else {
            $this->db->where('a.trans_date >=', date("Y-m-01"));
            $this->db->where('a.trans_date <=', date("Y-m-t"));
        }
        $this->db->like('b.division_id', $filter_division);
        $this->db->like('b.departement_id', $filter_departement);
        $this->db->like('b.departement_sub_id', $filter_departement_sub);
        $this->db->like('b.id', $filter_employee);
        if ($filter_request_code != "") {
            $this->db->where('a.request_code', $filter_request_code);
        }
        if ($filter_idm != "") {
            $this->db->where('a.idm_no', $filter_idm);
        }
        if ($filter_approval == "0") {
            $this->db->where("(a.approved_to = '' or a.approved_to is null)");
        } elseif ($filter_approval == "1") {
            $this->db->where("(a.approved_to != '' and a.approved_to is not null)");
        }
        $this->db->group_by('a.trans_date');
        $this->db->group_by('a.employee_id');
        $this->db->group_by('a.type');
        $this->db->order_by('a.trans_date', 'DESC');
        $records = $this->db->get()->result_array();

        $html = '<html><head><title>Print Data</title></head><style>body {font-family: Arial, Helvetica, sans-serif;}#customers {border-collapse: collapse;width: 100%;font-size: 12px;}#customers td, #customers th {border: 1px solid #ddd;padding: 2px;}#customers tr:nth-child(even){background-color: #f2f2f2;}#customers tr:hover {background-color: #ddd;}#customers th {padding-top: 2px;padding-bottom: 2px;text-align: left;color: black;}</style>
        <body>
        <center>
            <div style="float: left; font-size: 12px; text-align: left;">
                <table style="width: 100%;">
                    <tr>
                        <td width="50" style="font-size: 12px; vertical-align: top; text-align: center; vertical-align:jus margin-right:10px;">
                            <img src="' . $config->favicon . '" width="30">
                        </td>
                        <td style="font-size: 14px; text-align: left; margin:2px;">
                            <b>' . $config->name . '</b><br>
                            <small>DATA OVERTIME</small>
                        </td>
                    </tr>
                </table>
            </div>
            <div style="float: right; font-size: 12px; text-align: right;">
                Print Date ' . date("d M Y H:m:s") . ' <br>
                Print By ' . $this->session->username . '  
            </div>
        </center>
        <br><br><br>
        
        <table id="customers" border="1">
            <tr>
                <th width="20">No</th>
                <th>Request Date</th>
                <th>Request No</th>
                <th>IDM No</th>
                <th>Request Name</th>
                <th>Employee ID</th>
                <th>Employee Name</th>
                <th>Division</th>
                <th>Departement</th>
                <th>Departement Sub</th>
                <th>Trans Date</th>
                <th>Start</th>
                <th>End</th>
                <th>Break</th>
                <th>Duration</th>
                <th>Type</th>
                <th>Amount</th>
                <th>Plan</th>
                <th>Actual</th>
                <th>Remarks</th>
            </tr>';
        $no = 1;
        foreach ($records as $data) {
            $html .= '  <tr>
                            <td>' . $no . '</td>
                            <td>' . $data['trans_date'] . '</td>
                            <td>' . $data['request_code'] . '</td>
                            <td>' . $data['idm_no'] . '</td>
                            <td>' . $data['fullname'] . '</td>
                            <td style="mso-number-format:\@;">' . $data['employee_number'] . '</td>
                            <td>' . $data['employee_name'] . '</td>
                            <td>' . $data['division_name'] . '</td>
                            <td>' . $data['departement_name'] . '</td>
                            <td>' . $data['departement_sub_name'] . '</td>
                            <td>' . date("d F Y", strtotime($data['trans_date'])) . '</td>
                            <td>' . $data['start'] . '</td>
                            <td>' . $data['end'] . '</td>
                            <td>' . $data['break'] . '</td>
                            <td>' . $data['duration_hour'] . '</td>
                            <td>' . $data['type'] . '</td>
                            <td>' . $data['amount'] . '</td>
                            <td>' . $data['plan'] . '</td>
                            <td>' . $data['actual'] . '</td>
                            <td>' . $data['remarks'] . '</td>
                        </tr>';
            $no++;
        }

        $html .= '</table></body></html>';
        echo $html;
    }
}
