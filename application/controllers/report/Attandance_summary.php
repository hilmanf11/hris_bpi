<?php
date_default_timezone_set("Asia/Bangkok");
defined('BASEPATH') or exit('No direct script access allowed');

class Attandance_summary extends CI_Controller
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
            $username = $this->session->username;

            $data['button'] = $this->getbutton($this->id_menu());
            $data['user'] = $this->crud->read("users", [], ["username" => $username]);

            $this->load->view('template/header', $data);
            $this->load->view('report/attandance_summary');
        } else {
            redirect('error_access');
        }
    }

    public function print($option = "")
    {
        if ($option == "excel") {
            $format  = date("Ymd");
            header("Content-type: application/vnd-ms-excel");
            header("Content-Disposition: attachment; filename=report_attandance_summary_$format.xls");
        }

        if ($this->input->get()) {
            $filter_from = $this->input->get('filter_from');
            $filter_to = $this->input->get('filter_to');
            $filter_division = $this->input->get('filter_division');
            $filter_departement = $this->input->get('filter_departement');
            $filter_departement_sub = $this->input->get('filter_departement_sub');
            $filter_employee = $this->input->get('filter_employee');

            $header_html = '<html><head><title>Print Data</title></head><style>body {font-family: Arial, Helvetica, sans-serif;}#customers {border-collapse: collapse;width: 100%;font-size: 10px;}#customers td, #customers th {border: 1px solid #ddd;padding: 2px;}#customers tr:nth-child(even){background-color: #f2f2f2;}#customers tr:hover {background-color: #ddd;}#customers th {padding-top: 2px;padding-bottom: 2px;text-align: left;color: black;}</style>
            <style> .str{ mso-number-format:\@; } </style>
            <body>';
            $this->db->select("a.division_id, a.departement_id, a.departement_sub_id, c.name as division_name, d.name as departement_name, e.name as departement_sub_name");
            $this->db->from('divisions c');
            $this->db->join('departements d', 'd.division_id = c.id');
            $this->db->join('departement_subs e', 'e.departement_id = d.id');
            $this->db->join('employees a', 'a.division_id = c.id and a.departement_id = d.id and a.departement_sub_id = e.id');
            $this->db->where('a.status', 0);
            $this->db->like('a.division_id', $filter_division);
            $this->db->like('a.departement_id', $filter_departement);
            $this->db->like('a.departement_sub_id', $filter_departement_sub);
            $this->db->like('a.id', $filter_employee);
            $this->db->group_by('e.id');
            $this->db->order_by('e.name', 'asc');
            $records = $this->db->get()->result_array();

            //Config
            $this->db->select('*');
            $this->db->from('config');
            $config = $this->db->get()->row();

            $start = strtotime($filter_from);
            $finish = strtotime($filter_to);

            $weekday = [];
            $weekday2 = [];
            $weekend = [];
            $weekend2 = [];

            for ($i = $start; $i <= $finish; $i += (60 * 60 * 24)) {
                //Jika tanggal yg di looping bukan hari sabtu dan minggu
                if (date('w', $i) !== '0' && date('w', $i) !== '6') {
                    //Hari kerja
                    $weekday[] = date('Y-m-d', $i);
                } else {
                    //Hari libur
                    $weekend[] = date('Y-m-d', $i);
                }

                if (date('w', $i) !== '0') {
                    //Hari kerja
                    $weekday2[] = date('Y-m-d', $i);
                } else {
                    //Hari libur
                    $weekend2[] = date('Y-m-d', $i);
                }
            }

            //Tanggal merah di master calendar
            $this->db->select('trans_date');
            $this->db->from('calendars');
            $this->db->where('trans_date >=', $filter_from);
            $this->db->where('trans_date <=', $filter_to);
            if (count($weekend2) > 0) {
                $this->db->where_not_in('trans_date', $weekend2);
            }
            $calendar = $this->db->get()->result_array();
            $calendar_amount = empty($calendar) ? 0 : count($calendar);
            $w_calendars = array();
            foreach ($calendar as $cal) {
                $w_calendars[] = $cal['trans_date'];
            }

            $no = 1;
            foreach ($records as $record) {
                //Permit
                $permit_types = $this->crud->reads("permit_types", [], [], "", "name", "asc");

                $html = '<div style="page-break-after:always;">
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
                        Print By ' . $this->session->username . '  
                    </div>
                </center><br><br><br>
                <center>
                    <h3 style="margin:0;">Report Attandance Summary</h3>
                    <p style="margin:0;">Period ' . $filter_from . ' to ' . $filter_to . '</p>
                    <br><br>
                </center>
                <table style="font-size: 11px;">
                    <tr>
                        <td width="100">Division</td>
                        <td width="10">:</td>
                        <td><b>' . $record['division_name'] . '</b></td>
                    </tr>
                    <tr>
                        <td>Departement</td>
                        <td>:</td>
                        <td><b>' . $record['departement_name'] . '</b></td>
                    </tr>
                    <tr>
                        <td>Departement Sub</td>
                        <td>:</td>
                        <td><b>' . $record['departement_sub_name'] . '</b></td>
                    </tr>
                </table>
                <br>

            <table id="customers" border="1">
                <tr>
                    <th width="20" rowspan="2">No</th>
                    <th style="text-align:center;" rowspan="2">Departement Sub</th>
                    <th style="text-align:center;" rowspan="2">Employee ID</th>
                    <th style="text-align:center;" rowspan="2">Employee Name</th>
                    <th style="text-align:center;" rowspan="2">Shift</th>
                    <th style="text-align:center;" colspan="' . count($permit_types) . '">Permit</th>
                    <th style="text-align:center;" rowspan="2">Absence</th>
                    <th style="text-align:center;" rowspan="2">Change Days</th>
                    <th style="text-align:center;" rowspan="2">Working Days</th>
                </tr>
                <tr>';
                foreach ($permit_types as $permit_type) {
                    $html .= '<th style="text-align:center;">' . $permit_type->number . '</th>';
                }
                $html .= '</tr>';

                //Ambil Employee berdasarkan departement dan divisionnnya
                $this->db->select("
                    a.id as employee_id, 
                    a.number, a.name, 
                    a.division_id, 
                    a.departement_id, 
                    a.departement_sub_id, 
                    c.name as division_name, 
                    d.name as departement_name, 
                    e.name as departement_sub_name, 
                    g.name as shift_name,
                    h.days");
                $this->db->from('employees a');
                $this->db->join('divisions c', 'a.division_id = c.id');
                $this->db->join('departements d', 'a.departement_id = d.id');
                $this->db->join('departement_subs e', 'a.departement_sub_id = e.id');
                $this->db->join('shift_employees f', 'a.id = f.employee_id');
                $this->db->join('shifts g', 'f.shift_id = g.id');
                $this->db->join('shift_details h', 'h.shift_id = g.id');
                $this->db->where('a.status', '0');
                $this->db->where('a.division_id', $record['division_id']);
                $this->db->where('a.departement_id', $record['departement_id']);
                $this->db->like('a.departement_sub_id', $record['departement_sub_id']);
                $this->db->like('a.id', $filter_employee);
                $this->db->group_by('a.number');
                $this->db->order_by('e.name, a.name', 'asc');
                $employees = $this->db->get()->result_array();

                $attandance_absece = 0;
                $attandance_total = 0;
                $attandance_days = 0;
                $change_days = 0;
                $total_days = 0;

                foreach ($employees as $data) {

                    if ($data['days'] == "6") {
                        $weekend_day = $weekend2;
                        $weekday_day = $weekday2;
                    } else {
                        $weekend_day = $weekend;
                        $weekday_day = $weekday;
                    }

                    $tomorow = date('Y-m-d', strtotime("+1 day", strtotime($filter_from)));

                    //Attandances
                    $this->db->select("COUNT(a.date_in) as amount");
                    $this->db->from('(SELECT DISTINCT number, date_in FROM attandances) a');
                    $this->db->where('a.number', $data['number']);
                    $this->db->where("(a.date_in >= '$filter_from' and a.date_in <= '$filter_to')");
                    if (count($weekend_day) > 0) {
                        $this->db->where_not_in('a.date_in', $weekend_day);
                    }
                    if (count($w_calendars) > 0) {
                        $this->db->where_not_in('a.date_in', $w_calendars);
                    }
                    $attandance = $this->db->get()->row();
                    $attandance_amount = empty($attandance->amount) ? 0 : $attandance->amount;

                    //Change Days
                    $this->db->select("COUNT(*) as days");
                    $this->db->from('change_days');
                    $this->db->where('employee_id', $data['employee_id']);
                    $this->db->where("(start between '$filter_from' and '$filter_to' or end between '$filter_from' and '$filter_to')");
                    $changeDays = $this->db->get()->row();
                    $changeDays_amount = empty($changeDays->days) ? 0 : $changeDays->days;

                    //Permit yang non deduction atau tidak potong gaji
                    $permit_date = "'" . implode("', '", $weekend_day) . "'";
                    $calendar_date = "'" . implode("', '", $w_calendars) . "'";
                    $q_permit = $this->db->query("SELECT b.number, b.name, COUNT(a.permit_date) as amount
                            FROM permit_types b
                            LEFT JOIN permits a ON a.permit_type_id = b.id and a.employee_id = '$data[employee_id]' and a.permit_date >= '$filter_from' and a.permit_date <= '$filter_to' and a.permit_date not in ($permit_date) and a.permit_date not in ($calendar_date)
                            WHERE (a.approved_to = '' or a.approved_to is null)
                            GROUP BY b.id");
                    $r_permit = $q_permit->result_array();
                    $arr_total_permit = 0;
                    foreach ($r_permit as $permit_data) {
                        $arr_total_permit += $permit_data['amount'];
                    }

                    //Permit yang deduction atau potong gaji
                    // $q_permit_deduction = $this->db->query("SELECT b.number, b.name, COUNT(a.permit_date) as amount
                    // FROM permit_types b
                    // LEFT JOIN permits a ON a.permit_type_id = b.id and a.employee_id = '$data[employee_id]' and a.permit_date >= '$filter_from' and a.permit_date <= '$filter_to' and a.permit_date not in ($permit_date) and a.permit_date not in ($calendar_date)
                    // WHERE b.payroll = 'DEDUCTION' and (a.approved_to = '' or a.approved_to is null)
                    // GROUP BY b.id");

                    // $r_permit_deduction = $q_permit_deduction->result_array();
                    // $arr_total_permit_deduction = 0;
                    // foreach ($r_permit_deduction as $permit_data_deduction) {
                    //     $arr_total_permit_deduction += $permit_data_deduction['amount'];
                    // }

                    //Permit atau absen nya YES di anggap masuk kerja
                    $q_permit_absence = $this->db->query("SELECT b.number, b.name, COUNT(a.permit_date) as amount
                    FROM permit_types b
                    LEFT JOIN permits a ON a.permit_type_id = b.id and a.employee_id = '$data[employee_id]' and a.permit_date >= '$filter_from' and a.permit_date <= '$filter_to' and a.permit_date not in ($permit_date) and a.permit_date not in ($calendar_date)
                    WHERE b.absence = 'YES' and (a.approved_to = '' or a.approved_to is null)
                    GROUP BY b.id");

                    $r_permit_absence = $q_permit_absence->result_array();
                    $arr_total_permit_absence = 0;
                    foreach ($r_permit_absence as $permit_data_absence) {
                        $arr_total_permit_absence += $permit_data_absence['amount'];
                    }

                    $hkw = (@count($weekday_day) - @$calendar_amount);
                    $absence = (@count($weekday_day) - @$calendar_amount - @$attandance_amount - $arr_total_permit - @$changeDays_amount + $arr_total_permit_absence);

                    if ($absence < 0) {
                        $absence_final = 0;
                    } else {
                        $absence_final = $absence;
                    }

                    //Hitung Hari dia masuk kerja
                    $working_days = $attandance_amount;

                    //Permit
                    $q_permit = $this->db->query("SELECT b.name, COUNT(a.duration) as permit
                            FROM permit_types b
                            LEFT JOIN permits a ON a.permit_type_id = b.id and a.employee_id = '$data[employee_id]' and a.permit_date >= '$filter_from' and a.permit_date <= '$filter_to'
                            WHERE (a.approved_to = '' or a.approved_to is null)
                            GROUP BY b.id ORDER BY b.name asc");
                    $r_permit = $q_permit->result_array();

                    $html .= '<tr>
                                <td>' . $no . '</td>
                                <td>' . $data['departement_sub_name'] . '</td>
                                <td style="mso-number-format:\@;">' . $data['number'] . '</td>
                                <td>' . $data['name'] . '</td>
                                <td>' . $data['shift_name'] . '</td>';
                    $total_permit = 0;
                    foreach ($r_permit as $data_permit) {
                        $html .= '<td style="text-align:center;">' . $data_permit['permit'] . '</td>';
                        $total_permit += $data_permit['permit'];
                    }

                    $html .= '  <td style="text-align:center;">' . $absence_final . '</td>
                                <td style="text-align:center;">' . $changeDays_amount . '</td>
                                <td style="text-align:center;">' . $working_days . '</td>
                            </tr>';

                    $attandance_absece += $absence_final;
                    $attandance_total += $working_days;
                    $change_days += @$changeDays_amount;
                    $total_days += $hkw;
                    $attandance_days += count($weekday_day);
                    $no++;
                }

                //Total Permit Final
                $this->db->select("b.name, COUNT(a.duration) as permit");
                $this->db->from("permit_types b");
                $this->db->join(
                    "(SELECT a.employee_id, a.permit_type_id, a.permit_date, a.duration, b.departement_id, b.departement_sub_id FROM permits a JOIN employees b ON a.employee_id = b.id WHERE b.status = 0 and (a.approved_to = '' or a.approved_to is null)) a",
                    "a.permit_type_id = b.id and a.permit_date >= '$filter_from' and a.permit_date <= '$filter_to' and a.employee_id LIKE '%$filter_employee%' and a.departement_id = '$record[departement_id]' and a.departement_sub_id LIKE '%$record[departement_sub_id]%'",
                    "left"
                );
                $this->db->group_by("b.id");
                $this->db->order_by("b.name", "ASC");
                $rt_permit = $this->db->get()->result_array();

                //Total Permit Final MP
                $q_permit_mp = $this->db->query("SELECT a.name, COUNT(b.employee_id) as employee FROM permit_types a 
                LEFT JOIN (SELECT a.employee_id, a.permit_type_id, a.permit_date, a.duration, b.departement_id, b.departement_sub_id FROM permits a JOIN employees b ON a.employee_id = b.id WHERE b.status = 0 and (a.approved_to = '' or a.approved_to is null) 
                and a.permit_date BETWEEN '$filter_from' and '$filter_to' and a.employee_id LIKE '%$filter_employee%' and b.departement_id LIKE '%$record[departement_id]%' and b.departement_sub_id LIKE '%$record[departement_sub_id]%'
                GROUP BY a.employee_id, a.permit_type_id) b ON a.id = b.permit_type_id
                GROUP BY a.id ORDER BY a.name ASC");
                $rt_permit_mp = $q_permit_mp->result_array();

                //Grand Total Permit
                $html .= '  <tr><th colspan="5" style="text-align:right;">Grand Total Permit</th>';
                foreach ($rt_permit as $row) {
                    $html .= '<td style="text-align:center;">' . $row['permit'] . '</td>';
                }
                $html .= '  <th style="text-align:center;">' . $attandance_absece . '</th>
                            <th style="text-align:center;">' . $change_days . '</th>
                            <th style="text-align:center;">' . $attandance_total . '</th>
                        </tr>';

                //Grand Total MP
                $html .= '  <tr><th colspan="5" style="text-align:right;">Grand Total MP</th>';
                foreach ($rt_permit_mp as $row_mp) {
                    $html .= '<td style="text-align:center;">' . $row_mp['employee'] . '</td>';
                }
                $html .= '  <th style="text-align:center;"></th>
                            <th style="text-align:center;"></th>
                            <th style="text-align:center;"></th>
                        </tr>';

                $html .= '</table></div><br><br>';

                echo $header_html . $html;
            }

            echo '</body></html>';
        }
    }
}
