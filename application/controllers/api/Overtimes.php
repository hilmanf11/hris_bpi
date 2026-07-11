<?php
header('Content-Type: application/json; charset=utf-8');

date_default_timezone_set("Asia/Bangkok");
defined('BASEPATH') or exit('No direct script access allowed');

class Overtimes extends CI_Controller
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

    public function readShifts($api_key = ""){
        if ($api_key != "") {
            $user = $this->mobile->read("users", [], ["api_key" => $api_key]);

            if ($user) {
                $trans_date = $this->input->post("trans_date");
                $employee = $this->mobile->read("employees", [], ["number" => $user->number]);

                //Attandance and Overtime
                $this->db->select("date_in, time_in, date_out, time_out");
                $this->db->from('attandances');
                $this->db->where("date_in", $trans_date);
                $this->db->where('number', $user->number);
                $this->db->order_by('date_in', 'asc');
                $attandance = $this->db->get()->row();

                //Shift and Setting Group
                $tolerance_hour_min = date("H:i:s", strtotime('-2 Hour', strtotime(@$attandance->time_in)));
                $tolerance_hour_plus = date("H:i:s", strtotime('+2 Hour', strtotime(@$attandance->time_in)));
                $this->db->select("d.start, d.end, d.days, d.working, d.tolerance, c.name, d.name as shift_name");
                $this->db->from('shift_employees b');
                $this->db->join('shifts c', 'c.id = b.shift_id');
                $this->db->join('shift_details d', 'd.shift_id = c.id');
                $this->db->where('b.employee_id', $employee->id);
                $this->db->where("d.start >=  '$tolerance_hour_min' and d.start <= '$tolerance_hour_plus'");
                $shift = $this->db->get()->row();

                $today = date("Y-m-d");

                if (@$shift->start == null || empty($attandance->time_in)){
                    $start = "";
                    $end = "";
                }elseif(@$shift->start >= @$attandance->time_in){
                    $time_in = date("Y-m-d H:i:s", strtotime(@$attandance->date_in. " " .@$shift->start));
                    $time_out = date("Y-m-d H:i:s", strtotime("-30 minutes", strtotime(@$attandance->date_out. " " .@$attandance->time_out)));

                    if($time_in < $time_out){
                        $start = @$shift->end;
                        $end = @$attandance->time_out;
                    }elseif($today == @$attandance->date_in){
                        $start = @$shift->end;
                        $end = "";
                    }else{
                        $start = "";
                        $end = "";
                    }
                }else{
                    $start = @$shift->start;
                    $end = "";
                }

                die(json_encode(array(
                    "start" => $start,
                    "end" => @$end,
                )));
            }
        }
    }

    public function reads($api_key = "", $year = "")
    {

        if ($api_key != "") {
            $user = $this->mobile->read("users", [], ["api_key" => $api_key]);

            if ($user) {
                $employee = $this->mobile->read("employees", [], ["number" => $user->number]);

                $this->db->select('a.*, 
                    "" as approved_data,
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
                $this->db->where('YEAR(a.trans_date)', $year);
                $this->db->where('b.deleted', 0);
                $this->db->where('b.status', 0);
                $this->db->where('a.deleted', 0);
                $this->db->like('b.id', $employee->id);
                // $this->db->group_by('a.trans_date');
                // $this->db->group_by('a.employee_id');
                // $this->db->group_by('a.request_code');
                $this->db->order_by('a.trans_date', 'DESC');
                // $this->db->limit($limit);
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

                    $this->db->select('trans_date');
                    $this->db->from('calendars');
                    $this->db->where('trans_date', $record['trans_date']);
                    $calendars = $this->db->get()->result_array();

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
                    if ($record['meal'] == 0 or @$attandance->time_in == "") {
                        $meal = 0;
                    } else {
                        $meal = @$record['total_meal'];
                    }

                    if (@$shift_employee->days == "5") {
                        if (date('w', $start) !== '0' && date('w', $start) !== '6') {

                            //Kalo ada tanggal Merah
                            if (count($calendars) > 0) {
                                $total = ((@$record['total_holiday'] * $hour) + $meal);
                            } else {
                                $total = ((@$record['total_weekday'] * $hour) + $meal);
                            }
                        } else {
                            if (date('w', $start) === '0') {
                                $total = ((@$record['total_sunday'] * $hour) + $meal);
                            } else {
                                $total = ((@$record['total_saturday'] * $hour) + $meal);
                            }
                        }
                    } else {
                        if (date('w', $start) !== '0') {

                            //Kalo ada tanggal Merah
                            if (count($calendars) > 0) {
                                $total = ((@$record['total_holiday'] * $hour) + $meal);
                            } else {
                                $total = ((@$record['total_weekday'] * $hour) + $meal);
                            }
                        } else {
                            if (date('w', $start) === '0') {
                                $total = ((@$record['total_sunday'] * $hour) + $meal);
                            } else {
                                $total = ((@$record['total_saturday'] * $hour) + $meal);
                            }
                        }
                    }

                    if(!empty($record['attachment'])){
                        if(substr($record['attachment'], -4) == "jpeg"){
                            $attachment = "assets/image/cash_carry/" . substr($record['attachment'], -15);
                        }else{
                            $attachment = "assets/image/cash_carry/" . substr($record['attachment'], -14);
                        }
                    }else{
                        $attachment = null;
                    }

                    $amount = ["amount_actual" => $total, "duration_att" => number_format($att_hour, 2), "time_in" => @$attandance->time_in, "time_out" => @$attandance->time_out, "attachment_link" => $attachment];
                    $datas[] = array_merge($record, $amount);
                }

                die(json_encode(array(
                    "title" => "Success",
                    "message" => "Data Found",
                    "theme" => "success",
                    "results" => $datas
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
        $this->db->from('cash_carries');
        $this->db->where('deleted', 0);
        $this->db->where('SUBSTRING(request_code, 4, 6)=', $date);
        $records = $this->db->get()->row();

        $requestcode = (int) $records->code;
        $requestcode++;

        $template = "CC/" . $date . "/";
        return $template . sprintf("%04s", $requestcode);
    }

    public function idmNo($trans_date, $departement_id)
    {
        $date = $departement_id . "-" . date("ym", strtotime($trans_date));
        $this->db->select('max(SUBSTRING(request_code, -4)) as code');
        $this->db->from('cash_carries');
        $this->db->where('deleted', 0);
        $this->db->where('SUBSTRING(request_code, 4, 6)=', $date);
        $records = $this->db->get()->row();

        $requestcode = (int) $records->code;
        $requestcode++;

        $template = "OT" . $date;
        return $template . sprintf("%04s", $requestcode);
    }

    public function readOvertimePrice($employee_id, $trans_date, $duration, $meal)
    {
        $employee = $this->mobile->read("employees", [], ["id" => $employee_id]);
        $this->db->select('c.days');
        $this->db->from('shift_employees a');
        $this->db->join('shifts b', 'a.shift_id = b.id', 'left');
        $this->db->join('shift_details c', 'b.id = c.shift_id', 'left');
        $this->db->where('a.employee_id', $employee_id);
        $shift_employee = $this->db->get()->row();

        $setup_cash_carry = $this->mobile->read("setup_cash_carries", [], ["employee_id" => $employee_id]);
        $allowance_cash_carry = $this->mobile->read("allowance_cash_carries", [], ["id" => @$setup_cash_carry->allowance_id]);

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
            $meal = @$allowance_cash_carry->meal;
        }

        if (@$shift_employee->days == "5") {
            if (date('w', $hourTrans) !== '0' && date('w', $hourTrans) !== '6') {

                //Kalo ada tanggal Merah
                if (count($calendars) > 0) {
                    $total = ((@$allowance_cash_carry->holiday * $duration) + $meal);
                } else {
                    $total = ((@$allowance_cash_carry->weekday * $duration) + $meal);
                }
            } else {
                if (date('w', $hourTrans) === '0') {
                    $total = ((@$allowance_cash_carry->sunday * $duration) + $meal);
                } else {
                    $total = ((@$allowance_cash_carry->saturday * $duration) + $meal);
                }
            }
        } else {
            if (date('w', $hourTrans) !== '0') {

                //Kalo ada tanggal Merah
                if (count($calendars) > 0) {
                    $total = ((@$allowance_cash_carry->holiday * $duration) + $meal);
                } else {
                    $total = ((@$allowance_cash_carry->weekday * $duration) + $meal);
                }
            } else {
                if (date('w', $hourTrans) === '0') {
                    $total = ((@$allowance_cash_carry->sunday * $duration) + $meal);
                } else {
                    $total = ((@$allowance_cash_carry->saturday * $duration) + $meal);
                }
            }
        }

        return $total;
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

    public function createData($api_key = "")
    {
        if ($api_key != "") {
            $user = $this->mobile->read("users", [], ["api_key" => $api_key]);

            if ($user) {
                if($this->input->post()){
                    $post = $this->input->post();

                    $employee = $this->mobile->read("employees", [], ["number" => $user->number]);
                    $departement = $this->mobile->read("departements", [], ["id" => $employee->departement_id]);

                    //approval
                    $approval = $this->mobile->read("approvals", [], ["departement_id" => $employee->departement_id, "table_name" => "cash_carries"]);

                    $trans_date = $post['request_date'];
                    $employee_id = $employee->id;
                    $request_code = $this->requestCode($trans_date);
                    $request_name = $employee->name;
                    $idm_no = $this->idmNo($trans_date, $departement->number);
                    $start = $post['start'];
                    $end = $post['end'];
                    $break = $post['break'];
                    $type = "REGULAR";
                    $meal = $post['meal'];
                    $plan = $post['plan'];
                    $actual = $post['actual'];
                    
                    $attachment = $this->mobile->upload('attachment', ['png', 'jpg', 'jpeg'], 'assets/image/cash_carry/');
                    
                    //$attachment_idm = trim($post['attachment_idm']);
                    $remarks = $post['remarks'];
                    $duration = $this->convertHour($trans_date, $start, $end, $employee_id, $break);
                    $ot_amount = $this->readOvertimePrice($employee_id, $trans_date, $duration['duration_hour'], $meal);

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
                        "amount" =>  $ot_amount,
                        "meal" =>  $meal,
                        "plan" =>  $plan,
                        "actual" =>  $actual,
                        "break" =>  $break,
                        "remarks" =>  $remarks,
                        "idm_no" =>  $idm_no,
                        "attachment" =>  $attachment,
                        "attachment_idm" =>  $attachment,
                    );

                    $cash_carries = $this->mobile->reads('cash_carries', [], ["employee_id" => $employee_id, "trans_date" => $trans_date]);

                    if (count($cash_carries) > 0) {
                        show_error("Your overtime application has been sent, please change to another date");
                    }else{
                        $result = $this->mobile->create('cash_carries', $post_final, $user->username);
                        // if($this->mobile->create('cash_carries', $post_final, $user->username)){
                        //     die(json_encode(array(
                        //         "title" => "Success",
                        //         "message" => "Overtime Success Send",
                        //         "theme" => "success"
                        //     )));
                        // }else{
                        //     show_error("Overtimes Failed To Send");
                        // }

                        if ($result){

                            // buat fungsi update approved by berdasarkan siapa approved to nya
                            // fungsi ini agar disamakan dengan web
                            $values = array("approved_by" => @$approval->user_approval_1);
                            $send = $this->db->update("cash_carries", $values, ["employee_id" => $employee_id, "trans_date" =>  $trans_date]);

                            if($send){
                                die(json_encode(array(
                                    "title" => "Success",
                                    "message" => "Overtime Success Send",
                                    "theme" => "success",
                                )));
                            } else {
                                show_error("Overtime Failed To Send");
                            }
                        }else{
                            show_error("Overtime Failed To Send");
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

                    $delete = $this->mobile->delete("cash_carries", ['id' => $data['id']], $user->username);
                    @unlink("'".$data['attachment']."'");
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
