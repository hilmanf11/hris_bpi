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
            $this->db->order_by('h.name', 'ASC');
            $this->db->order_by('c.name', 'ASC');
            $this->db->order_by('b.name', 'ASC');
            $this->db->order_by('a.trans_date', 'ASC');
            
            $cash_carry = $this->db->get()->result_array();

            if ($cash_carry) {

                // Config
                $this->db->select('*');
                $this->db->from('config');
                $config = $this->db->get()->row();

                // =========================================================================
                // PROSES 1: HITUNG PER TRANSAKSI & PEMISAHAN KATEGORI LEMBUR PER KARYAWAN
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

                    // Kategori Lembur
                    $ot_type = 'weekday'; // Default: Hari Kerja

                    if (@$shift_employee->days == "5") {
                        if (date('w', $start) !== '0' && date('w', $start) !== '6') {
                            if (count($calendars) > 0) {
                                $ot_type = 'holiday';
                                $rate = @$record['total_holiday'];
                            } else {
                                $ot_type = 'weekday';
                                $rate = @$record['total_weekday'];
                            }
                        } else {
                            if (date('w', $start) === '0') {
                                $ot_type = 'holiday';
                                $rate = @$record['total_sunday'];
                            } else {
                                $ot_type = 'saturday';
                                $rate = @$record['total_saturday'];
                            }
                        }
                    } else {
                        if (date('w', $start) !== '0') {
                            if (count($calendars) > 0) {
                                $ot_type = 'holiday';
                                $rate = @$record['total_holiday'];
                            } else {
                                $ot_type = 'weekday';
                                $rate = @$record['total_weekday'];
                            }
                        } else {
                            $ot_type = 'holiday';
                            $rate = @$record['total_sunday'];
                        }
                    }

                    $amount = ($rate * $hour) + $meal;

                    // Grouping berdasarkan ID Karyawan
                    $emp_id = $record['employee_id'];

                    if (!isset($grouped_data[$emp_id])) {
                        $grouped_data[$emp_id] = array(
                            'employee_number'      => $record['employee_number'],
                            'employee_name'        => $record['employee_name'],
                            'division_name'        => $record['division_name'],
                            'departement_name'     => $record['departement_name'],
                            'departement_sub_name' => $record['departement_sub_name'],
                            'weekday_hour'         => 0,
                            'weekday_amount'       => 0,
                            'saturday_hour'        => 0,
                            'saturday_amount'      => 0,
                            'holiday_hour'         => 0,
                            'holiday_amount'       => 0,
                            'total_netto_hour'     => 0,
                            'total_amount'         => 0
                        );
                    }

                    // Akumulasi Durasi & Nominal per Kategori
                    if ($ot_type == 'weekday') {
                        $grouped_data[$emp_id]['weekday_hour']   += $hour;
                        $grouped_data[$emp_id]['weekday_amount'] += $amount;
                    } else if ($ot_type == 'saturday') {
                        $grouped_data[$emp_id]['saturday_hour']   += $hour;
                        $grouped_data[$emp_id]['saturday_amount'] += $amount;
                    } else if ($ot_type == 'holiday') {
                        $grouped_data[$emp_id]['holiday_hour']   += $hour;
                        $grouped_data[$emp_id]['holiday_amount'] += $amount;
                    }

                    $grouped_data[$emp_id]['total_netto_hour'] += $hour;
                    $grouped_data[$emp_id]['total_amount']     += $amount;
                }

                // =========================================================================
                // PROSES 2: RENDER HTML SESUAI TABLE LAYOUT GAMBAR
                // =========================================================================
                $html = '<html><head><title>CASH CARRY REPORT</title></head>
                <style>
                    body { font-family: Arial, sans-serif; font-size: 11px; color: #000; margin: 15px; }
                    .header-table { width: 100%; margin-bottom: 10px; border-collapse: collapse; }
                    .report-title { text-align: center; font-size: 16px; font-weight: bold; margin: 15px 0; }
                    
                    #reportTable { border-collapse: collapse; width: 100%; font-size: 11px; }
                    #reportTable th, #reportTable td { border: 1px solid #000; padding: 4px 6px; }
                    #reportTable th { background-color: #ffffff; text-align: center; font-weight: bold; vertical-align: middle; }
                    
                    .grandtotal-all { background-color: #e6e6e6; font-weight: bold; }
                    
                    .approval-container { width: 100%; margin-top: 30px; text-align: center; }
                    .approval-table { border-collapse: collapse; width: 500px; margin: 0 auto; font-size: 11px; }
                    .approval-table th, .approval-table td { border: 1px solid #000; text-align: center; }
                    .approval-table th { font-weight: bold; padding: 5px; }
                    .approval-space { height: 80px; }
                    .approval-name { height: 25px; }
                </style>
                <body>
                    <table class="header-table">
                        <tr>
                            <td width="50" style="border:none;">
                                <img src="' . $config->favicon . '" width="35">
                            </td>
                            <td style="border:none; text-align:left;">
                                <b style="font-size: 14px;">' . strtoupper($config->name) . '</b><br>
                                <span style="font-size: 10px;">' . strtoupper($config->description) . '</span>
                            </td>
                            <td style="border:none; text-align:right; font-size:10px;" vertical-align="top">
                                Print Date: ' . date("d M Y H:i:s") . '<br>
                                Print By: ' . $this->session->username . '
                            </td>
                        </tr>
                    </table>

                    <div class="report-title">CASH CARRY REPORT</div>

                    <table id="reportTable">
                        <thead>
                            <tr>
                                <th rowspan="2" width="30">No.</th>
                                <th rowspan="2" width="90">NIK</th>
                                <th rowspan="2" width="150">Nama</th>
                                <th rowspan="2" width="100">Division</th>
                                <th rowspan="2" width="110">Department</th>
                                <th colspan="2">OT Hari Kerja</th>
                                <th colspan="2">OT Hari Sabtu</th>
                                <th colspan="2">OT Hari Minggu/Libur Nasional</th>
                                <th rowspan="2" width="70">OT Netto</th>
                                <th rowspan="2" width="90">Total Dibayar</th>
                                <th rowspan="2" width="80">Di Terima</th>
                            </tr>
                            <tr>
                                <th width="70">Durasi (Jam)</th>
                                <th width="80">Nominal</th>
                                <th width="70">Durasi (Jam)</th>
                                <th width="80">Nominal</th>
                                <th width="70">Durasi (Jam)</th>
                                <th width="80">Nominal</th>
                            </tr>
                        </thead>
                        <tbody>';

                $no                  = 1;
                $tot_weekday_hr      = 0;
                $tot_weekday_amt     = 0;
                $tot_saturday_hr     = 0;
                $tot_saturday_amt    = 0;
                $tot_holiday_hr      = 0;
                $tot_holiday_amt     = 0;
                $tot_netto_hr        = 0;
                $grand_total_dibayar = 0;

                foreach ($grouped_data as $row) {
                    $html .= '<tr>
                                <td style="text-align:center;">' . $no . '</td>
                                <td style="text-align:center;">' . $row['employee_number'] . '</td>
                                <td>' . $row['employee_name'] . '</td>
                                <td>' . $row['division_name'] . '</td>
                                <td>' . $row['departement_name'] . '</td>
                                <td style="text-align:center;">' . number_format($row['weekday_hour'], 2) . '</td>
                                <td style="text-align:right;">' . number_format($row['weekday_amount'], 0, ',', '.') . '</td>
                                <td style="text-align:center;">' . number_format($row['saturday_hour'], 2) . '</td>
                                <td style="text-align:right;">' . number_format($row['saturday_amount'], 0, ',', '.') . '</td>
                                <td style="text-align:center;">' . number_format($row['holiday_hour'], 2) . '</td>
                                <td style="text-align:right;">' . number_format($row['holiday_amount'], 0, ',', '.') . '</td>
                                <td style="text-align:center;">' . number_format($row['total_netto_hour'], 2) . '</td>
                                <td style="text-align:right;">' . number_format($row['total_amount'], 0, ',', '.') . '</td>
                                <td></td>
                            </tr>';

                    // Accumulate Grand Totals
                    $tot_weekday_hr      += $row['weekday_hour'];
                    $tot_weekday_amt     += $row['weekday_amount'];
                    $tot_saturday_hr     += $row['saturday_hour'];
                    $tot_saturday_amt    += $row['saturday_amount'];
                    $tot_holiday_hr      += $row['holiday_hour'];
                    $tot_holiday_amt     += $row['holiday_amount'];
                    $tot_netto_hr        += $row['total_netto_hour'];
                    $grand_total_dibayar += $row['total_amount'];
                    $no++;
                }

                // Total Summary Row
                $html .= '<tr class="grandtotal-all">
                            <td colspan="5" style="text-align:center;">TOTAL</td>
                            <td style="text-align:center;">' . number_format($tot_weekday_hr, 2) . '</td>
                            <td style="text-align:right;">' . number_format($tot_weekday_amt, 0, ',', '.') . '</td>
                            <td style="text-align:center;">' . number_format($tot_saturday_hr, 2) . '</td>
                            <td style="text-align:right;">' . number_format($tot_saturday_amt, 0, ',', '.') . '</td>
                            <td style="text-align:center;">' . number_format($tot_holiday_hr, 2) . '</td>
                            <td style="text-align:right;">' . number_format($tot_holiday_amt, 0, ',', '.') . '</td>
                            <td style="text-align:center;">' . number_format($tot_netto_hr, 2) . '</td>
                            <td style="text-align:right;">' . number_format($grand_total_dibayar, 0, ',', '.') . '</td>
                            <td></td>
                        </tr>';

                $html .= '</tbody></table>';

                // TABEL APPROVAL
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
                    </div>';

                $html .= '</body></html>';
                
                echo $html;
            } else {
                echo "<center><br><br><br><h2>Data Not Complete to View</h2></center>";
            }
        }
    }
}
