<?php
date_default_timezone_set("Asia/Bangkok");
defined('BASEPATH') or exit('No direct script access allowed');

class Cash_carry extends CI_Controller
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
            $this->load->view('report/cash_carry');
        } else {
            redirect('error_access');
        }
    }

    public function print($option = "")
    {
        if ($option == "excel") {
            $format = date("Ymd");
            header("Content-type: application/vnd-ms-excel");
            header("Content-Disposition: attachment; filename=report_cash_carry_$format.xls");
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

            // Query Data
            $this->db->select('a.*,
                b.bank_no,
                b.bank_name,
                b.number as employee_number,
                b.name as employee_name,
                b.departement_id,
                b.departement_sub_id,
                b.group_id,
                b.position_id,
                a.start as time_in,
                a.end as time_out,
                g.time_in as att_time_in,
                g.time_out as att_time_out,
                g.date_in as att_date_in,
                g.date_out as att_date_out,
                j.round_duration,
                c.name as departement_name,
                d.name as departement_sub_name,
                h.name as division_name,
                e.name as group_name,
                COALESCE(i.weekday, 0) as total_weekday,
                COALESCE(i.sunday, 0) as total_sunday,
                COALESCE(i.saturday, 0) as total_saturday,
                COALESCE(i.holiday, 0) as total_holiday,
                COALESCE(i.meal, 0) as total_meal');
            $this->db->from('cash_carry a');
            $this->db->join('employees b', "a.employee_id = b.id", 'left');
            $this->db->join('departements c', "b.departement_id = c.id", 'left');
            $this->db->join('departement_subs d', "b.departement_sub_id = d.id", 'left');
            $this->db->join('divisions h', "b.division_id = h.id", 'left');
            $this->db->join('`groups` e', "b.group_id = e.id", 'left');
            $this->db->join('attandances g', "b.number = g.number and a.trans_date = g.date_in",'inner');
            $this->db->join('setup_cash_carry j', 'a.employee_id = j.employee_id', 'left');
            $this->db->join('allowance_cash_carry i', 'j.allowance_id = i.id', 'left');
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
            $this->db->order_by('c.name', 'ASC');
            $this->db->order_by('d.name', 'ASC');
            $this->db->order_by('b.name', 'ASC');
            $this->db->order_by('a.trans_date', 'ASC');
            
            $cash_carry = $this->db->get()->result_array();

            // echo json_encode($cash_carry);

            if ($cash_carry) {

                // Config
                $this->db->select('*');
                $this->db->from('config');
                $config = $this->db->get()->row();

                // =========================================================================
                // PROSES 1: HITUNG PER TRANSAKSI LALU SUM / GROUPING BERDASARKAN KARYAWAN
                // =========================================================================
                $grouped_data = array();

                foreach ($cash_carry as $record) {
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

                    if (empty($record['time_in']) || empty($record['time_out'])) {
                        $att_hour = 0;
                    } else {
                        // Gunakan time_in dan time_out dari tabel cash_carry (a.start & a.end)
                        $att_time_begin = strtotime($record['trans_date'] . " " . $record['time_in']);
                        $att_time_end   = strtotime($record['trans_date'] . " " . $record['time_out']);

                        if ($att_time_end < $att_time_begin) {
                            $att_time_end = strtotime($record['trans_date'] . " " . $record['time_out'] . " +1 days");
                        }

                        $break = isset($record['break']) ? $record['break'] : 0;
                        $total_minutes = ($att_time_end - $att_time_begin) / 60 - $break;

                        if ($total_minutes < 0) {
                            $total_minutes = 0;
                        }

                        $round_duration = isset($record['round_duration']) ? (float)$record['round_duration'] : 0.5;

                        if ($round_duration == 0.5) {
                            $final_minutes_total = floor($total_minutes / 30) * 30;
                        } else if ($round_duration == 1.0) {
                            $final_minutes_total = floor($total_minutes / 60) * 60;
                        } else {
                            $final_minutes_total = $total_minutes;
                        }

                        $att_hour = $final_minutes_total / 60;
                    }

                    $cc_hour = $record['duration_hour'];
                    $hour    = ($att_hour > $cc_hour) ? $cc_hour : $att_hour;
                    $meal    = ($record['meal'] == 0) ? 0 : @$record['total_meal'];

                    if (@$shift_employee->days == "5") {
                        if (date('w', $start) !== '0' && date('w', $start) !== '6') {
                            $total = (count($calendars) > 0) ? (@$record['total_holiday'] * $hour) : (@$record['total_weekday'] * $hour);
                        } else {
                            $total = (date('w', $start) === '0') ? (@$record['total_sunday'] * $hour) : (@$record['total_saturday'] * $hour);
                        }
                    } else {
                        if (date('w', $start) !== '0') {
                            $total = (count($calendars) > 0) ? (@$record['total_holiday'] * $hour) : (@$record['total_weekday'] * $hour);
                        } else {
                            $total = (date('w', $start) === '0') ? (@$record['total_sunday'] * $hour) : (@$record['total_saturday'] * $hour);
                        }
                    }

                    $row_total = $total + $meal;

                    // Gunakan ID karyawan sebagai key untuk melakukan SUM
                    $emp_id = $record['employee_id'];

                    if (!isset($grouped_data[$emp_id])) {
                        $grouped_data[$emp_id] = array(
                            'employee_number'      => $record['employee_number'],
                            'employee_name'        => $record['employee_name'],
                            'departement_name'     => $record['departement_name'],
                            'departement_sub_name' => $record['departement_sub_name'],
                            'bank_no'              => $record['bank_no'],
                            'total_hour'         => 0,
                            'total_amount'         => 0
                        );
                    }

                    // Penjumlahan Jam dan Total Nominal per Karyawan
                    $grouped_data[$emp_id]['total_hour']   += $hour;
                    $grouped_data[$emp_id]['total_amount'] += $row_total;
                }

                // =========================================================================
                // PROSES 2: RENDER HTML HASIL YANG SUDAH TER-SUM PER KARYAWAN
                // =========================================================================
                $html = '<html><head><title>CASH CARRY REPORT</title></head>
                <style>
                    body { font-family: Arial, sans-serif; font-size: 12px; color: #000; margin: 20px; }
                    .header-table { width: 95%; margin: 0 auto 10px auto; border-collapse: collapse; }
                    .report-title { text-align: center; font-size: 16px; font-weight: bold; margin: 15px 0; }
                    .info-table { width: 95%; margin: 0 auto 10px auto; font-size: 12px; border-collapse: collapse; }
                    #reportTable { border-collapse: collapse; width: 95%; margin: 0 auto; font-size: 12px; }
                    #reportTable th, #reportTable td { border: 1px solid #000; padding: 5px 8px; }
                    #reportTable th { background-color: #ffffff; text-align: center; font-weight: bold; font-size: 13px; }
                    .subtotal-subdept { background-color: #f2f2f2; font-weight: bold; }
                    .grandtotal-dept { background-color: #d9d9d9; font-weight: bold; }
                    .grandtotal-all { background-color: #bfbfbf; font-weight: bold; }
                    
                    /* Style untuk Tabel Approval */
                    .approval-container { width: 95%; margin: 25px auto 0 auto; text-align: center; }
                    .approval-table { border-collapse: collapse; width: 600px; margin: 0 auto; font-size: 12px; }
                    .approval-table th, .approval-table td { border: 1px solid #000; text-align: center; }
                    .approval-table th { font-weight: bold; padding: 6px; }
                    .approval-space { height: 110px; }
                    .approval-name { height: 30px; }
                </style>
                <body>
                    <table class="header-table">
                        <tr>
                            <td width="50" style="border:none;">
                                <img src="' . $config->favicon . '" width="35">
                            </td>
                            <td style="border:none; text-align:left;">
                                <b style="font-size: 14px;">' . strtoupper($config->name) . '</b><br>
                                <span style="font-size: 11px;">' . strtoupper($config->description) . '</span>
                            </td>
                            <td style="border:none; text-align:right; font-size:11px;" vertical-align="top">
                                Print Date ' . date("d M Y H:i:s") . '<br>
                                Print By ' . $this->session->username . '<br>
                                page 1/1
                            </td>
                        </tr>
                    </table>

                    <div class="report-title">CASH CARRY REPORT</div>

                    <table class="info-table">
                        <tr>
                            <td width="90" style="border:none;">Date</td>
                            <td width="10" style="border:none;">:</td>
                            <td style="border:none;">' . $filter_from . ' &nbsp;to&nbsp; ' . $filter_to . '</td>
                        </tr>
                        <tr>
                            <td style="border:none;">Plant</td>
                            <td style="border:none;">:</td>
                            <td style="border:none;">ALL</td>
                        </tr>
                        <tr>
                            <td style="border:none;">Department</td>
                            <td style="border:none;">:</td>
                            <td style="border:none;">ALL</td>
                        </tr>
                    </table>

                    <table id="reportTable">
                        <thead>
                            <tr>
                                <th width="40">No.</th>
                                <th width="180">NIK</th>
                                <th width="220">Nama</th>
                                <th width="140">No. Rek</th>
                                <th width="150">Amount (IDR)</th>
                                <th width="150">Source</th>
                            </tr>
                        </thead>
                        <tbody>';

                $no               = 1;
                $grand_total_all  = 0;
                $grand_total_dept = 0;
                $subtotal_subdept = 0;
                $current_dept     = null;
                $current_subdept  = null;

                foreach ($grouped_data as $row) {

                    // --- 1. Deteksi Ganti Department ---
                    if ($current_dept !== null && $current_dept !== $row['departement_name']) {
                        $html .= '<tr class="subtotal-subdept">
                                    <td colspan="4" style="text-align:right;">Sub Total ' . $current_dept . ' - ' . $current_subdept . '</td>
                                    <td style="text-align:right;">' . number_format($subtotal_subdept, 2) . '</td>
                                    <td></td>
                                </tr>';
                        $html .= '<tr class="grandtotal-dept">
                                    <td colspan="4" style="text-align:right;">Grand Total ' . $current_dept . '</td>
                                    <td style="text-align:right;">' . number_format($grand_total_dept, 2) . '</td>
                                    <td></td>
                                </tr>';
                                
                        $grand_total_all  += $grand_total_dept;
                        $grand_total_dept  = 0;
                        $subtotal_subdept  = 0;
                        $current_subdept   = null;
                    }

                    // --- 2. Deteksi Ganti Sub Department ---
                    if ($current_subdept !== null && $current_subdept !== $row['departement_sub_name']) {
                        $html .= '<tr class="subtotal-subdept">
                                    <td colspan="4" style="text-align:right;">Sub Total ' . $current_dept . ' - ' . $current_subdept . '</td>
                                    <td style="text-align:right;">' . number_format($subtotal_subdept, 2) . '</td>
                                    <td></td>
                                </tr>';
                        $subtotal_subdept = 0;
                    }

                    $current_dept    = $row['departement_name'];
                    $current_subdept = $row['departement_sub_name'];

                    // Render Baris Karyawan (Hasil SUM)
                    $html .= '<tr>
                                <td style="text-align:center;">' . $no . '</td>
                                <td>' . $row['employee_number'] . '</td>
                                <td>' . $row['employee_name'] . '</td>
                                <td style="text-align:center;">' . $row['bank_no'] . '</td>
                                <td style="text-align:right;">' . number_format($row['total_amount'], 2) . '</td>
                                <td></td>
                            </tr>';

                    $subtotal_subdept += $row['total_amount'];
                    $grand_total_dept += $row['total_amount'];
                    $no++;
                }

                // Subtotal Sub Department Terakhir
                if ($current_subdept !== null) {
                    $html .= '<tr class="subtotal-subdept">
                                <td colspan="4" style="text-align:right;">Sub Total ' . $current_dept . ' - ' . $current_subdept . '</td>
                                <td style="text-align:right;">' . number_format($subtotal_subdept, 2) . '</td>
                                <td></td>
                            </tr>';
                }

                // Grand Total Department Terakhir
                if ($current_dept !== null) {
                    $html .= '<tr class="grandtotal-dept">
                                <td colspan="4" style="text-align:right;">Grand Total ' . $current_dept . '</td>
                                <td style="text-align:right;">' . number_format($grand_total_dept, 2) . '</td>
                                <td></td>
                            </tr>';
                    $grand_total_all += $grand_total_dept;
                }

                // Grand Total Keseluruhan
                $html .= '<tr class="grandtotal-all">
                            <td colspan="4" style="text-align:right;">Grand Total</td>
                            <td style="text-align:right;">' . number_format($grand_total_all, 2) . '</td>
                            <td></td>
                        </tr>';

                $html .= '</tbody></table>';

                // TABEL APPROVAL (PREPARED, CHECKED, APPROVED)
                $html .= '<div class="approval-container">
                        <table class="approval-table">
                            <tr>
                                <th width="33%">Prepared</th>
                                <th width="33%">Checked</th>
                                <th width="34%">Approved</th>
                            </tr>
                            <tr>
                                <td class="approval-space"></td>
                                <td class="approval-space"></td>
                                <td class="approval-space"></td>
                            </tr>
                            <tr>
                                <td class="approval-name"></td>
                                <td class="approval-name"></td>
                                <td class="approval-name"></td>
                            </tr>
                        </table>
                        <div class="clearfix"></div>
                      </div>';

            $html .= '</body></html>';
                
                echo $html;
            } else {
                echo "<center><br><br><br><h2>Data Not Complete to View</h2></center>";
            }
        }
    }
}
