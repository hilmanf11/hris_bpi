<?php
date_default_timezone_set("Asia/Bangkok");
defined('BASEPATH') or exit('No direct script access allowed');

class Attandance_days extends CI_Controller
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
            $this->load->view('report/attandance_days');
        } else {
            redirect('error_access');
        }
    }

    public function print($option = "")
    {
        ob_start();
        if ($option == "excel") {
            $format  = date("Ymd");
            header("Content-type: application/vnd-ms-excel");
            header("Content-Disposition: attachment; filename=attandance_days_$format.xls");
        }

        if ($this->input->get()) {
            $filter_from = $this->input->get('filter_from');
            $filter_to = $this->input->get('filter_to');
            $filter_division = $this->input->get('filter_division');
            $filter_departement = $this->input->get('filter_departement');
            $filter_departement_sub = $this->input->get('filter_departement_sub');
            $filter_employee = $this->input->get('filter_employee');
            $filter_status = $this->input->get('filter_status');

            $this->db->select('b.id as employee_id, b.number as employee_number, b.name as employee_name, c.name as division_name, 
            d.name as departement_name, e.name as departement_sub_name, g.id as shift_id, g.name as shift_name, h.days');
            $this->db->from('employees b');
            $this->db->join('divisions c', 'b.division_id = c.id');
            $this->db->join('departements d', 'b.departement_id = d.id');
            $this->db->join('departement_subs e', 'b.departement_sub_id = e.id');
            $this->db->join('shift_employees f', 'b.id = f.employee_id', 'left');
            $this->db->join('shifts g', 'f.shift_id = g.id');
            $this->db->join('shift_details h', 'h.shift_id = g.id');
            $this->db->where('b.deleted', 0);
            $this->db->where('b.status', 0);
            if (!empty($filter_employee)) {
                $this->db->where('b.id', $filter_employee);
            }
            if (!empty($filter_division)) {
                $this->db->where('c.id', $filter_division);
            }
            if (!empty($filter_departement)) {
                $this->db->where('d.id', $filter_departement);
            }
            if (!empty($filter_departement_sub)) {
                $this->db->where('e.id', $filter_departement_sub);
            }
            $this->db->group_by('b.id');
            $this->db->order_by('d.name', 'ASC');
            $this->db->order_by('e.name', 'ASC');
            $this->db->order_by('b.name', 'ASC');
            $records = $this->db->get()->result_array();

            // Config
            $this->db->select('*');
            $this->db->from('config');
            $config = $this->db->get()->row();

            // --- BATCH QUERY OPTIMIZATION ---
            $employee_ids = array_column($records, 'employee_id');
            $employee_numbers = array_column($records, 'employee_number');
            $shift_ids = array_filter(array_unique(array_column($records, 'shift_id')));

            // 1. Fetch Calendars
            $calendars_map = array();
            $this->db->select('trans_date, description');
            $this->db->from('calendars');
            $this->db->where('trans_date >=', $filter_from);
            $this->db->where('trans_date <=', $filter_to);
            $calendars_res = $this->db->get()->result_array();
            foreach ($calendars_res as $c_row) {
                $calendars_map[$c_row['trans_date']] = $c_row['description'];
            }

            // 2. Fetch Permits
            $permits_map = array();
            if (!empty($employee_ids)) {
                $this->db->select('a.employee_id, a.permit_date, d.name as permit_name');
                $this->db->from('permits a');
                $this->db->join('reasons c', 'a.reason_id = c.id');
                $this->db->join('permit_types d', 'a.permit_type_id = d.id');
                $this->db->where_in('a.employee_id', $employee_ids);
                $this->db->where('a.permit_date >=', $filter_from);
                $this->db->where('a.permit_date <=', $filter_to);
                $permits_res = $this->db->get()->result_array();
                foreach ($permits_res as $p_row) {
                    $permits_map[$p_row['employee_id']][$p_row['permit_date']] = $p_row['permit_name'];
                }
            }

            // 3. Fetch Attendances & Overtimes
            $attandances_map = array();
            if (!empty($employee_numbers)) {
                $this->db->select('a.id as employee_id, b.number, b.date_in, b.time_in, b.date_out, b.time_out');
                $this->db->from('employees a');
                $this->db->join('attandances b', 'a.number = b.number');
                $this->db->where_in('a.number', $employee_numbers);
                $this->db->where('b.date_in >=', $filter_from);
                $this->db->where('b.date_in <=', $filter_to);
                $attandances_res = $this->db->get()->result_array();
                foreach ($attandances_res as $att_row) {
                    $attandances_map[$att_row['employee_id']][$att_row['date_in']] = (object)$att_row;
                }
            }

            // 4. Fetch Shift Details
            $shift_details_map = array();
            if (!empty($shift_ids)) {
                $this->db->select('shift_id, start, end, days, working, tolerance, name as shift_detail_name');
                $this->db->from('shift_details');
                $this->db->where_in('shift_id', $shift_ids);
                $shift_details_res = $this->db->get()->result_array();
                foreach ($shift_details_res as $sd_row) {
                    $shift_details_map[$sd_row['shift_id']][] = (object)$sd_row;
                }
            }

            echo '<html><head><title>Print Data</title></head><style>body {font-family: Arial, Helvetica, sans-serif;}#customers {border-collapse: collapse;width: 100%;font-size: 10px;}#customers td, #customers th {border: 1px solid #ddd;padding: 2px;}#customers tr:nth-child(even){background-color: #f2f2f2;}#customers tr:hover {background-color: #ddd;}#customers th {padding-top: 2px;padding-bottom: 2px;text-align: left;color: black;}</style>
            <style> .str{ mso-number-format:\@; } </style>
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
                    Print By ' . $this->session->username . '  
                </div>
            </center><br><br><br>
            <center>
                <h3 style="margin:0;">Report Attandance Days</h3>
                <span>Period ' . $filter_from . ' to ' . $filter_to . '</span>
            </center>
            <br>
            
            <table id="customers" border="1">
                <tr>
                    <th width="20">No</th>
                    <th>Departement</th>
                    <th>Departement Sub</th>
                    <th>Employee ID</th>
                    <th>Employee Name</th>
                    <th>Shift</th>
                    <th>Trans Date</th>
                    <th>Time In/Out</th>
                    <th>Status</th>
                </tr>';
            $no = 1;

            $start = strtotime($filter_from);
            $finish = strtotime($filter_to);

            foreach ($records as $data) {
                $html = "";
                $emp_id = $data['employee_id'];
                $shift_id = $data['shift_id'];

                for ($i = $start; $i <= $finish; $i += (60 * 60 * 24)) {
                    $working_date = date('Y-m-d', $i);
                    $holiday_desc = isset($calendars_map[$working_date]) ? $calendars_map[$working_date] : null;

                    // Attendance row
                    $attandance = isset($attandances_map[$emp_id][$working_date]) ? $attandances_map[$emp_id][$working_date] : null;

                    // Shift match
                    $shift = null;
                    if ($attandance && !empty($attandance->time_in) && isset($shift_details_map[$shift_id])) {
                        $tolerance_hour_min = date("H:i:s", strtotime('-2 Hour', strtotime($attandance->time_in)));
                        $tolerance_hour_plus = date("H:i:s", strtotime('+2 Hour', strtotime($attandance->time_in)));

                        foreach ($shift_details_map[$shift_id] as $sd) {
                            if ($sd->start >= $tolerance_hour_min && $sd->start <= $tolerance_hour_plus) {
                                $shift = $sd;
                                break;
                            }
                        }
                    }

                    // Permit match
                    $permit_name = isset($permits_map[$emp_id][$working_date]) ? $permits_map[$emp_id][$working_date] : null;

                    if (@$data['days'] == "5") {
                        // sabtu dan minggu libur
                        if (date('w', $i) !== '0' && date('w', $i) !== '6') {
                            if (!empty($holiday_desc)) {
                                $status = "HOLIDAY";
                            } else {
                                if ($permit_name != null) {
                                    $status = "PERMIT";
                                } elseif (@$shift->start == null) {
                                    $status = "UN SETTING";
                                } elseif (@$shift->start <= @$attandance->time_in) {
                                    $status = "LATE";
                                } elseif (@$shift->start >= @$attandance->time_in) {
                                    $status = "ON TIME";
                                } else {
                                    $status = "ABSENCE";
                                }
                            }
                        } else {
                            $status = "WEEKEND";
                        }
                    } else {
                        // sabtu doang libur
                        if (date('w', $i) !== '0') {
                            if (!empty($holiday_desc)) {
                                $status = "HOLIDAY";
                            } else {
                                if ($permit_name != null) {
                                    $status = "PERMIT";
                                } elseif (@$shift->start == null) {
                                    $status = "UN SETTING";
                                } elseif (@$shift->start <= @$attandance->time_in) {
                                    $status = "LATE";
                                } elseif (@$shift->start >= @$attandance->time_in) {
                                    $status = "ON TIME";
                                } else {
                                    $status = "ABSENCE";
                                }
                            }
                        } else {
                            $status = "WEEKEND";
                        }
                    }

                    if ($status == "WEEKEND") {
                        $style = "style='color:red; font-weight:bold;'";
                    } elseif ($status == "PERMIT") {
                        $style = "style='color:blue; font-weight:bold;'";
                    } elseif ($status == "LATE") {
                        $style = "style='color:orange; font-weight:bold;'";
                    } elseif ($status == "ON TIME") {
                        $style = "style='color:green; font-weight:bold;'";
                    } else {
                        $style = "style='color:red; font-weight:bold;'";
                    }

                    $time_in_out = ($attandance) ? $attandance->time_in . ' - ' . $attandance->time_out : ' - ';

                    $html .= '   <tr>
                                    <td>' . $no . '</td>
                                    <td>' . $data['departement_name'] . '</td>
                                    <td>' . $data['departement_sub_name'] . '</td>
                                    <td style="mso-number-format:\@;">' . $data['employee_number'] . '</td>
                                    <td>' . $data['employee_name'] . '</td>
                                    <td>' . $data['shift_name'] . '</td>
                                    <td>' . date("d F Y", strtotime($working_date)) . '</td>
                                    <td>' . $time_in_out . '</td>
                                    <td ' . $style . '>' . $status . '</td>
                                </tr>';

                    $no++;
                }
                echo $html;

                ob_flush();
                flush();
            }

            echo '</table></body></html>';
        }

        ob_end_flush();
    }
}
