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
    }

    //HALAMAN UTAMA
    public function index()
    {
        if (empty($this->session->username)) {
            redirect('error_session');
        } elseif ($this->checkuserAccess($this->id_menu()) > 0) {
            $data['button'] = $this->getbutton($this->id_menu());

            $this->load->view('template/header', $data);
            $this->load->view('report/cash_carries');
        } else {
            redirect('error_access');
        }
    }

    //GET DATATABLES
    public function datatables()
    {
        $filter_from = $this->input->get('filter_from');
        $filter_to = $this->input->get('filter_to');
        $filter_division = $this->input->get('filter_division');
        $filter_departement = $this->input->get('filter_departement');
        $filter_departement_sub = $this->input->get('filter_departement_sub');
        $filter_employee = $this->input->get('filter_employee');
        $filter_group = $this->input->get('filter_group');
        $filter_bank = $this->input->get('filter_bank');
        $username = $this->session->username;

        $period_start = date("Y-m", strtotime($filter_from));
        $period_end = date("Y-m", strtotime($filter_to));

        //Select Query
        $this->db->select('a.*,
            b.number as employee_number,
            b.name as employee_name,
            b.departement_id,
            b.departement_sub_id,
            b.group_id,
            b.position_id,
            c.name as departement_name, 
            d.name as departement_sub_name, 
            e.name as group_name');
        $this->db->from('cash_carries a');
        $this->db->join('employees b', "a.employee_id = b.id");
        $this->db->join('departements c', "b.departement_id = c.id");
        $this->db->join('departement_subs d', "b.departement_sub_id = d.id");
        $this->db->join('groups e', "b.group_id = e.id");
        //$this->db->join('privilege_groups f', "b.group_id = f.id and f.username = '$username' and f.status = '1'", "left");
        $this->db->where('a.deleted', 0);
        $this->db->where('a.status', 0);
        $this->db->where("(a.approved_to = '' or a.approved_to is null)");
        if ($filter_from != "" && $filter_to != "") {
            $this->db->where('a.trans_date >=', $filter_from);
            $this->db->where('a.trans_date <=', $filter_to);
        }
        $this->db->like('b.id', $filter_employee);
        $this->db->like('b.division_id', $filter_division);
        $this->db->like('b.departement_id', $filter_departement);
        $this->db->like('b.departement_sub_id', $filter_departement_sub);
        $this->db->like('b.group_id', $filter_group);
        $this->db->like('b.bank_name', $filter_bank);
        $this->db->group_by("b.id");
        $this->db->order_by('b.name', 'ASC');
        //Get Data Array
        $cash_carries = $this->db->get()->result_array();

        $datas = [];
        foreach ($cash_carries as $cash_carry) {
            //Select Query
            $this->db->select('a.*,
                b.bank_no,
                b.bank_name,
                b.number as employee_number,
                b.name as employee_name,
                b.departement_id,
                b.departement_sub_id,
                b.group_id,
                b.position_id,
                g.time_in,
                g.time_out,
                c.name as departement_name, 
                d.name as departement_sub_name,
                h.name as division_name, 
                e.name as group_name,
                COALESCE(i.weekday, 0) as total_weekday,
                COALESCE(i.sunday, 0) as total_sunday,
                COALESCE(i.saturday, 0) as total_saturday,
                COALESCE(i.holiday, 0) as total_holiday,
                COALESCE(i.meal, 0) as total_meal');
            $this->db->from('cash_carries a');
            $this->db->join('employees b', "a.employee_id = b.id");
            $this->db->join('departements c', "b.departement_id = c.id");
            $this->db->join('departement_subs d', "b.departement_sub_id = d.id");
            $this->db->join('divisions h', "b.division_id = h.id");
            $this->db->join('groups e', "b.group_id = e.id");
            //$this->db->join('privilege_groups f', "b.group_id = f.id and f.username = '$username' and f.status = '1'", "left");
            $this->db->join('attandances g', "b.number = g.number and a.trans_date = g.date_in");
            $this->db->join('setup_cash_carries j', 'a.employee_id = j.employee_id', 'left');
            $this->db->join('allowance_cash_carries i', 'j.allowance_id = i.id', 'left');
            $this->db->where('a.deleted', 0);
            $this->db->where('a.status', 0);
            $this->db->where("(a.approved_to = '' or a.approved_to is null)");
            if ($filter_from != "" && $filter_to != "") {
                $this->db->where('a.trans_date >=', $filter_from);
                $this->db->where('a.trans_date <=', $filter_to);
            }
            $this->db->where('b.id', $cash_carry['employee_id']);
            $this->db->order_by('b.name', 'ASC');
            $records = $this->db->get()->result_array();

            $total = 0;
            foreach ($records as $record) {
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
                $att_time_begin = strtotime(@$record['date_in'] . " " . @$record['time_in']);
                $att_time_end = strtotime(@$record['date_out'] . " " . @$record['time_out']);
                
                $tomorrow = strtotime(date('Y-m-d', strtotime(@$record['date_out'] . "+1 days")) . " " . @$record['time_out']);

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
                if ($record['meal'] == 0) {
                    $meal = 0;
                } else {
                    $meal = @$record['total_meal'];
                }

                if (@$shift_employee->days == "5") {
                    if (date('w', $start) !== '0' && date('w', $start) !== '6') {

                        //Kalo ada tanggal Merah
                        if (count($calendars) > 0) {
                            $total += ((@$record['total_holiday'] * $hour) + $meal);
                        } else {
                            $total += ((@$record['total_weekday'] * $hour) + $meal);
                        }
                    } else {
                        if (date('w', $start) === '0'){
                            $total += ((@$record['total_sunday'] * $hour) + $meal);
                        }else{
                            $total += ((@$record['total_saturday'] * $hour) + $meal);
                        }
                    }
                } else {
                    if (date('w', $start) !== '0') {

                        //Kalo ada tanggal Merah
                        if (count($calendars) > 0) {
                            $total += ((@$record['total_holiday'] * $hour) + $meal);
                        } else {
                            $total += ((@$record['total_weekday'] * $hour) + $meal);
                        }
                    } else {
                        if (date('w', $start) === '0'){
                            $total += ((@$record['total_sunday'] * $hour) + $meal);
                        }else{
                            $total += ((@$record['total_saturday'] * $hour) + $meal);
                        }
                    }
                }
            }

            $amount = ["amount" => $total];
            $datas[] = array_merge($cash_carry, $amount);
        }

        echo json_encode(@$datas);
    }

    public function print($option = "")
    {
        if ($option == "excel") {
            $format  = date("Ymd");
            header("Content-type: application/vnd-ms-excel");
            header("Content-Disposition: attachment; filename=report_summary_payroll_$format.xls");
        }

        if ($this->input->get()) {
            $filter_from = $this->input->get('filter_from');
            $filter_to = $this->input->get('filter_to');
            $filter_employee = $this->input->get('filter_employee');
            $username = $this->session->username;

            //Select Query
            $this->db->select('a.*,
                b.bank_no,
                b.bank_name,
                b.number as employee_number,
                b.name as employee_name,
                b.departement_id,
                b.departement_sub_id,
                b.group_id,
                b.position_id,
                g.time_in,
                g.time_out,
                c.name as departement_name, 
                d.name as departement_sub_name,
                h.name as division_name, 
                e.name as group_name,
                COALESCE(i.weekday, 0) as total_weekday,
                COALESCE(i.sunday, 0) as total_sunday,
                COALESCE(i.saturday, 0) as total_saturday,
                COALESCE(i.holiday, 0) as total_holiday,
                COALESCE(i.meal, 0) as total_meal');
            $this->db->from('cash_carries a');
            $this->db->join('employees b', "a.employee_id = b.id");
            $this->db->join('departements c', "b.departement_id = c.id");
            $this->db->join('departement_subs d', "b.departement_sub_id = d.id");
            $this->db->join('divisions h', "b.division_id = h.id");
            $this->db->join('groups e', "b.group_id = e.id");
            //$this->db->join('privilege_groups f', "b.group_id = f.id and f.username = '$username' and f.status = '1'", "left");
            $this->db->join('attandances g', "b.number = g.number and a.trans_date = g.date_in");
            $this->db->join('setup_cash_carries j', 'a.employee_id = j.employee_id', 'left');
            $this->db->join('allowance_cash_carries i', 'j.allowance_id = i.id', 'left');
            $this->db->where('a.deleted', 0);
            $this->db->where('a.status', 0);
            $this->db->where("(a.approved_to = '' or a.approved_to is null)");
            if ($filter_from != "" && $filter_to != "") {
                $this->db->where('a.trans_date >=', $filter_from);
                $this->db->where('a.trans_date <=', $filter_to);
            }
            $this->db->where('b.id', $filter_employee);
            $this->db->order_by('b.name', 'ASC');
            //Get Data Array
            $cash_carries = $this->db->get()->result_array();

            if($cash_carries){

                //Config
                $this->db->select('*');
                $this->db->from('config');
                $config = $this->db->get()->row();

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
                                                <small>' . $config->description . '</small>
                                            </td>
                                        </tr>
                                    </table>
                                </div>
                                <div style="float: right; font-size: 12px; text-align: right;">
                                    Print Date ' . date("d M Y H:i:s") . ' <br>
                                    Print By ' . $this->session->username . ' <br>
                                </div>
                            </center><br><br><br>
                            <center>
                                <h3 style="margin:0;">REPORT DETAIL CASH CARRY</h3>
                                <span style="font-size:12px;">' . date("d F Y", strtotime($filter_from)) . ' to ' . date("d F Y", strtotime($filter_to)) . '</span>
                            </center>
                            <br>
                            <div style="width:100%;">
                                <table style="font-size:12px; width:50%; float:left; margin-bottom:20px;">
                                    <tr>
                                        <td>Employee ID</td>
                                        <td>:</td>
                                        <td><b>' . $cash_carries[0]['employee_number'] . '</b></td>
                                    </tr>
                                    <tr>
                                        <td>Employee Name</td>
                                        <td>:</td>
                                        <td><b>' . $cash_carries[0]['employee_name'] . '</b></td>
                                    </tr>
                                    <tr>
                                        <td>Plant</td>
                                        <td>:</td>
                                        <td><b>' . $cash_carries[0]['division_name'] . '</b></td>
                                    </tr>
                                </table>
                                <table style="font-size:12px; width:50%; float:left;">
                                    <tr>
                                        <td>Departement</td>
                                        <td>:</td>
                                        <td><b>' . $cash_carries[0]['departement_name'] . '</b></td>
                                    </tr>
                                    <tr>
                                        <td>Departement Sub</td>
                                        <td>:</td>
                                        <td><b>' . $cash_carries[0]['departement_sub_name'] . '</b></td>
                                    </tr>
                                    <tr>
                                        <td>Group</td>
                                        <td>:</td>
                                        <td><b>' . $cash_carries[0]['group_name'] . '</b></td>
                                    </tr>
                                </table>
                            </div>
                            
                            <table id="customers" border="1">
                                <tr>
                                    <th width="20">No</th>
                                    <th style="text-align:center;">Bank No</th>
                                    <th style="text-align:center;">Bank Name</th>
                                    <th style="text-align:center;">Trans Date</th>
                                    <th style="text-align:center;">Type</th>
                                    <th style="text-align:center;">Hour</th>
                                    <th style="text-align:center;">Amount</th>
                                    <th style="text-align:center;">Meal</th>
                                    <th style="text-align:center;">Total</th>
                                </tr>';
                $no = 1;
                $grand_total = 0;
                foreach ($cash_carries as $record) {
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
                    $att_time_begin = strtotime(@$record['date_in'] . " " . @$record['time_in']);
                    $att_time_end = strtotime(@$record['date_out'] . " " . @$record['time_out']);
                    
                    $tomorrow = strtotime(date('Y-m-d', strtotime(@$record['date_out'] . "+1 days")) . " " . @$record['time_out']);

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
                    if ($record['meal'] == 0) {
                        $meal = 0;
                    } else {
                        $meal = @$record['total_meal'];
                    }

                    if (@$shift_employee->days == "5") {
                        if (date('w', $start) !== '0' && date('w', $start) !== '6') {

                            //Kalo ada tanggal Merah
                            if (count($calendars) > 0) {
                                $total = ((@$record['total_holiday'] * $hour));
                            } else {
                                $total = ((@$record['total_weekday'] * $hour));
                            }
                        } else {
                            if (date('w', $start) === '0'){
                                $total = ((@$record['total_sunday'] * $hour));
                            }else{
                                $total = ((@$record['total_saturday'] * $hour));
                            }
                        }
                    } else {
                        if (date('w', $start) !== '0') {

                            //Kalo ada tanggal Merah
                            if (count($calendars) > 0) {
                                $total = ((@$record['total_holiday'] * $hour));
                            } else {
                                $total = ((@$record['total_weekday'] * $hour));
                            }
                        } else {
                            if (date('w', $start) === '0'){
                                $total = ((@$record['total_sunday'] * $hour));
                            }else{
                                $total = ((@$record['total_saturday'] * $hour));
                            }
                        }
                    }

                    $html .= '  <tr>
                                    <td>' . $no . '</td>
                                    <td style="mso-number-format:\@;">' . $record['bank_no'] . '</td>
                                    <td>' . $record['bank_name'] . '</td>
                                    <td>' . $record['trans_date'] . '</td>
                                    <td>' . $record['type'] . '</td>
                                    <td style="text-align:center;">' . number_format($hour) . '</td>
                                    <td style="text-align:right;">' . number_format($total) . '</td>
                                    <td style="text-align:right;">' . number_format($meal) . '</td>
                                    <td style="text-align:right;">' . number_format($total + $meal) . '</td>
                                </tr>';
                    $grand_total += ($total + $meal);
                    $no++;
                }

                $html .= '  <tr>
                                <th style="text-align:right;" colspan="8">GRAND TOTAL</th>
                                <th style="text-align:right;">' . number_format($grand_total) . '</th>
                            </tr>';
                $html .= '</table>
                            <br>
                            <center>
                                <table id="customers" style="width:60%;">
                                    <tr>
                                        <th width="100" style="text-align:center;">BOD</th>
                                        <th width="100" style="text-align:center;">ASSISTANT MANAGER</th>
                                        <th width="100" style="text-align:center;">PAYROLL STAFF</th>
                                    </tr>
                                    <tr>
                                        <td style="height:60px;"></td>
                                        <td style="height:60px;"></td>
                                        <td style="height:60px;"></td>
                                    </tr>
                                    <tr>
                                        <th style="text-align:center; height:20px;"></th>
                                        <th style="text-align:center;"></th>
                                        <th style="text-align:center;"></th>
                                    </tr>
                                </table>
                            </center>
                            </body></html>';
                echo $html;
            }else{
                echo "<center><br><br><br><h2>Data Not Complete to View</h2></center>";
            }
        }
    }

    public function print_recap($option = "")
    {
        if ($option == "excel") {
            $format  = date("Ymd");
            header("Content-type: application/vnd-ms-excel");
            header("Content-Disposition: attachment; filename=report_cash_carries_summary_$format.xls");
        }

        if ($this->input->get()) {
            $filter_from = $this->input->get('filter_from');
            $filter_to = $this->input->get('filter_to');
            $filter_division = $this->input->get('filter_division');
            $filter_departement = $this->input->get('filter_departement');
            $filter_departement_sub = $this->input->get('filter_departement_sub');
            $filter_employee = $this->input->get('filter_employee');
            $filter_group = $this->input->get('filter_group');
            $filter_bank = $this->input->get('filter_bank');
            $username = $this->session->username;

            //Config
            $this->db->select('*');
            $this->db->from('config');
            $config = $this->db->get()->row();

            //Select Query
            $this->db->select('
                b.division_id,
                b.departement_id,
                b.bank_name,
                c.name as division_name, 
                d.name as departement_name');
            $this->db->from('cash_carries a');
            $this->db->join('employees b', "a.employee_id = b.id");
            $this->db->join('divisions c', "b.division_id = c.id");
            $this->db->join('departements d', "b.departement_id = d.id");
            //$this->db->join('privilege_groups f', "b.group_id = f.id and f.username = '$username' and f.status = '1'", "left");
            $this->db->where('a.deleted', 0);
            $this->db->where('a.status', 0);
            if ($filter_from != "" && $filter_to != "") {
                $this->db->where('a.trans_date >=', $filter_from);
                $this->db->where('a.trans_date <=', $filter_to);
            }
            $this->db->like('b.id', $filter_employee);
            $this->db->like('b.division_id', $filter_division);
            $this->db->like('b.departement_id', $filter_departement);
            $this->db->like('b.departement_sub_id', $filter_departement_sub);
            $this->db->like('b.group_id', $filter_group);
            $this->db->like('b.bank_name', $filter_bank);
            $this->db->group_by('d.id');
            $this->db->order_by('d.name', 'ASC');
            //Get Data Array
            $departements = $this->db->get()->result_array();

            foreach ($departements as $departement) {
                $html = '<html><head><title>Print Data</title></head><style>body {font-family: Arial, Helvetica, sans-serif;}#customers {border-collapse: collapse;width: 100%;font-size: 12px;}#customers td, #customers th {border: 1px solid #ddd;padding: 2px;}#customers tr:nth-child(even){background-color: #f2f2f2;}#customers tr:hover {background-color: #ddd;}#customers th {padding-top: 2px;padding-bottom: 2px;text-align: left;color: black;}</style>
                <style> .str{ mso-number-format:\@; } </style><body>';
                $html .= '  <center>
                            <div style="float: left; font-size: 12px; text-align: left;">
                                <table style="width: 100%;">
                                    <tr>
                                        <td width="50" style="font-size: 12px; vertical-align: top; text-align: center; vertical-align:jus margin-right:10px;">
                                            <img src="' . $config->favicon . '" width="30">
                                        </td>
                                        <td style="font-size: 14px; text-align: left; margin:2px;">
                                            <b>' . $config->name . '</b><br>
                                            <small>' . $config->description . '</small>
                                        </td>
                                    </tr>
                                </table>
                            </div>
                            <div style="float: right; font-size: 12px; text-align: right;">
                                Print Date ' . date("d M Y H:m:s") . ' <br>
                                Print By ' . $this->session->username . ' <br>
                            </div>
                        </center>
                        <br><br><br>
                        <center>
                            <h3 style="margin:0;">REPORT SUMMARY CASH CARRY</h3>
                            <p style="margin:0; font-size:12px;">Period <b>' . date("d F Y", strtotime($filter_from)) . '</b> to <b>' . date("d F Y", strtotime($filter_to)) . '</b></p>
                            <br>
                        </center>
                        <table style="font-size:12px;">
                            <tr>
                                <td width="100">Division</td>
                                <td width="20">:</td>
                                <td><b>' . $departement['division_name'] . '</b></td>
                            </tr>
                            <tr>
                                <td width="100">Departement</td>
                                <td width="20">:</td>
                                <td><b>' . $departement['departement_name'] . '</b></td>
                            </tr>
                            <tr>
                                <td width="100">Bank Name</td>
                                <td width="20">:</td>
                                <td><b>' . $departement['bank_name'] . '</b></td>
                            </tr>
                        </table>
                        <br>
                        <table id="customers" border="1">
                            <tr>
                                <th width="20" style="text-align:center;">No</th>
                                <th style="text-align:center;">Employee ID</th>
                                <th style="text-align:center;">Employee Name</th>
                                <th style="text-align:center;">Departement Sub</th>
                                <th style="text-align:center;">Group</th>
                                <th style="text-align:center;">Bank Account</th>
                                <th style="text-align:center;">Pay Amount</th>
                            </tr>';

                //Select Query
                $this->db->select('a.*,
                    b.bank_no,
                    b.number as employee_number,
                    b.name as employee_name,
                    b.departement_id,
                    b.departement_sub_id,
                    b.group_id,
                    b.position_id,
                    c.name as departement_name, 
                    d.name as departement_sub_name, 
                    e.name as group_name');
                $this->db->from('cash_carries a');
                $this->db->join('employees b', "a.employee_id = b.id");
                $this->db->join('departements c', "b.departement_id = c.id");
                $this->db->join('departement_subs d', "b.departement_sub_id = d.id");
                $this->db->join('groups e', "b.group_id = e.id");
                //$this->db->join('privilege_groups f', "b.group_id = f.id and f.username = '$username' and f.status = '1'", "left");
                $this->db->where('a.deleted', 0);
                $this->db->where('a.status', 0);
                $this->db->where("(a.approved_to = '' or a.approved_to is null)");
                if ($filter_from != "" && $filter_to != "") {
                    $this->db->where('a.trans_date >=', $filter_from);
                    $this->db->where('a.trans_date <=', $filter_to);
                }
                $this->db->where('b.bank_name', $departement['bank_name']);
                $this->db->where('b.division_id', $departement['division_id']);
                $this->db->where('b.departement_id', $departement['departement_id']);
                $this->db->like('b.id', $filter_employee);
                $this->db->like('b.departement_sub_id', $filter_departement_sub);
                $this->db->like('b.group_id', $filter_group);
                $this->db->group_by("b.id");
                $this->db->order_by('b.name', 'ASC');
                //Get Data Array
                $cash_carries = $this->db->get()->result_array();

                $totalAmount = 0;
                $no = 1;
                foreach ($cash_carries as $cash_carry) {
                    //Select Query
                    $this->db->select('a.*,
                        b.number as employee_number,
                        b.name as employee_name,
                        b.departement_id,
                        b.departement_sub_id,
                        b.group_id,
                        b.position_id,
                        g.time_in,
                        g.time_out,
                        c.name as departement_name, 
                        d.name as departement_sub_name, 
                        e.name as group_name,
                        COALESCE(i.weekday, 0) as total_weekday,
                        COALESCE(i.sunday, 0) as total_sunday,
                        COALESCE(i.saturday, 0) as total_saturday,
                        COALESCE(i.holiday, 0) as total_holiday,
                        COALESCE(i.meal, 0) as total_meal');
                    $this->db->from('cash_carries a');
                    $this->db->join('employees b', "a.employee_id = b.id");
                    $this->db->join('departements c', "b.departement_id = c.id");
                    $this->db->join('departement_subs d', "b.departement_sub_id = d.id");
                    $this->db->join('groups e', "b.group_id = e.id");
                    $this->db->join('attandances g', "b.number = g.number and a.trans_date = g.date_in", 'left');
                    $this->db->join('setup_cash_carries h', 'a.employee_id = h.employee_id', 'left');
                    $this->db->join('allowance_cash_carries i', 'h.allowance_id = i.id', 'left');
                    $this->db->where('a.deleted', 0);
                    $this->db->where('a.status', 0);
                    $this->db->where("(a.approved_to = '' or a.approved_to is null)");
                    $this->db->where('a.employee_id =', $cash_carry['employee_id']);
                    $this->db->group_by("a.employee_id");
                    $this->db->order_by('b.name', 'ASC');
                    $records = $this->db->get()->result_array();

                    $total = 0;
                    foreach ($records as $record) {
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
                        $att_time_begin = strtotime(@$record['date_in'] . " " . @$record['time_in']);
                        $att_time_end = strtotime(@$record['date_out'] . " " . @$record['time_out']);
                        
                        $tomorrow = strtotime(date('Y-m-d', strtotime(@$record['date_out'] . "+1 days")) . " " . @$record['time_out']);

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
                        if ($record['meal'] == 0) {
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
                                if (date('w', $start) === '0'){
                                    $total = ((@$record['total_sunday'] * $hour) + $meal);
                                }else{
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
                                if (date('w', $start) === '0'){
                                    $total = ((@$record['total_sunday'] * $hour) + $meal);
                                }else{
                                    $total = ((@$record['total_saturday'] * $hour) + $meal);
                                }
                            }
                        }
                    }

                    $amount = ["amount" => $total];
                    $datas[] = array_merge($cash_carry, $amount);

                    $html .= '  <tr>
                                    <td style="text-align:center;">' . $no . '</td>
                                    <td style="mso-number-format:\@;">' . $cash_carry['employee_number'] . '</td>
                                    <td>' . $cash_carry['employee_name'] . '</td>
                                    <td>' . $cash_carry['departement_sub_name'] . '</td>
                                    <td>' . $cash_carry['group_name'] . '</td>
                                    <td>' . $cash_carry['bank_no'] . '</td>
                                    <td style="text-align:right;">' . number_format($total) . '</td>
                                </tr>';
                    $totalAmount += $total;
                    $no++;
                }

                $html .= '  <tr>
                            <th style="text-align:right;" colspan="6">GRAND TOTAL</th>
                            <th style="text-align:right;">' . number_format($totalAmount) . '</th>
                        </tr>
                        </table>
                        <br>
                        <center>
                            <table id="customers" style="width:70%;">
                                <tr>
                                    <th width="100" style="text-align:center;">BOD</th>
                                    <th width="100" style="text-align:center;">ASSISTANT MANAGER</th>
                                    <th width="100" style="text-align:center;">PAYROLL STAFF</th>
                                </tr>
                                <tr>
                                    <td style="height:60px;"></td>
                                    <td style="height:60px;"></td>
                                    <td style="height:60px;"></td>
                                </tr>
                                <tr>
                                    <th style="text-align:center; height:20px;"></th>
                                    <th style="text-align:center;"></th>
                                    <th style="text-align:center;"></th>
                                </tr>
                            </table>
                        </center>';

                $html .= '<div style="page-break-after:always;"></div>';
            }

            $html .= '</body></html>';
            echo $html;
        }
    }
}
