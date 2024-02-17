<?php
date_default_timezone_set("Asia/Bangkok");
defined('BASEPATH') or exit('No direct script access allowed');

class Salary_slips extends CI_Controller
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
    public function index($token = "")
    {
        if ($token == "") {
            $this->load->view('template/header_mobile');
            $this->load->view('mobile/404');
        } else {
            $users_m = $this->crud->read("users_m", [], ["token" => $token]);
            if ($users_m) {
                $users = $this->crud->read("users", [], ["number" => $users_m->number]);
                if (empty($this->session->username)) {
                    $this->session->set_userdata(["username" => $users->username]);
                }

                $qPeriod = $this->db->query("SELECT * FROM cutoff ORDER BY `start` DESC");
                $rPeriod = $qPeriod->result_array();

                $employee = $this->crud->read("employees", [], ["number" => $users_m->number]);

                $data['number'] = $users_m->number;
                $data['periods'] = $rPeriod;
                $data['employee'] = $employee;
                $data['api_key'] = $token;

                $this->load->view('template/header_mobile');
                $this->load->view('mobile/salary_slips', $data);
            } else {
                $this->load->view('template/header_mobile');
                $this->load->view('mobile/404');
            }
        }
    }

    public function datatables()
    {
        if ($this->input->post()) {
            $period_end = base64_decode($this->input->post('period_end'));
            $explode = explode("/", $period_end);
            $employee_id = $this->input->post('employee_id');

            $period_start = date("Y-m", strtotime($explode[0]));
            $period_end = date("Y-m", strtotime($explode[1]));

            $query = $this->db->query("SELECT a.*, b.bank_branch, b.bank_no, c.amount_plus_correction, d.amount_plus_salary FROM payrolls a
                JOIN employees b ON a.employee_id = b.id
                LEFT JOIN (SELECT employee_id, `trans_date`, SUM(amount) as amount_plus_correction FROM corrections WHERE trans_date between '$explode[0]' and '$explode[1]' and correction_type = 'PLUS' and correction_name = 'CORRECTION' GROUP BY employee_id) c ON a.employee_id = c.employee_id
                LEFT JOIN (SELECT employee_id, `trans_date`, SUM(amount) as amount_plus_salary FROM corrections WHERE trans_date between '$explode[0]' and '$explode[1]' and correction_type = 'PLUS' and correction_name = 'SALARY' GROUP BY employee_id) d ON a.employee_id = d.employee_id
                WHERE a.period_start = '$period_start'
                AND a.period_end = '$period_end'
                AND a.employee_id = '$employee_id'
                AND b.status = '0'
                ORDER BY a.`name` ASC");
            $records = $query->result_array();

            $html = '';
            if (!empty($records)) {
                foreach ($records as $record) {
                    //Deduction Amount
                    $total_deduction_amount = 0;
                    foreach (json_decode($record['deduction_amount'], true) as $deduction_amount => $val_deduction_amount) {
                        $total_deduction_amount += $val_deduction_amount;
                    }

                    //Allowance Amount
                    //jika dia ada tunjuangan ambil field dan isinya
                    $q_allowance = $this->db->query("SELECT b.number, b.name, a.amount
                    FROM allowances b
                    LEFT JOIN setup_allowances a ON a.allowance_id = b.id and a.employee_id = '$record[employee_id]'
                    GROUP BY b.id ORDER BY b.name asc");
                    $r_allowance = $q_allowance->result_array();

                    $arr_allowance_amount_total = 0;
                    $html_allowance = "";
                    foreach ($r_allowance as $allowance_data) {
                        $arr_allowance_amount_total += $allowance_data['amount'];
                        $html_allowance .= '    <tr>
                                                    <td style="text-align:left;">' . $allowance_data['name'] . '</td>
                                                    <td style="text-align:right;"><b>' . number_format($allowance_data['amount']) . '</b></td>
                                                </tr>';
                    }
                    //-------------------------------------------------------------------------------------------------------------------------------------------------------

                    //Deduction Amount
                    //Jika dia ada pemotongan gaji ambil field dan isinya
                    $q_deduction = $this->db->query("SELECT b.number, b.name, a.amount
                    FROM deductions b
                    LEFT JOIN setup_deductions a ON a.deduction_id = b.id and a.employee_id = '$record[employee_id]'
                    GROUP BY b.id ORDER BY b.name asc");
                    $r_deduction = $q_deduction->result_array();

                    $arr_deduction_amount_total = 0;
                    $html_deduction = "";
                    foreach ($r_deduction as $deduction_data) {
                        $arr_deduction_amount_total += $deduction_data['amount'];
                        $html_deduction .= ' <tr>
                                                <td style="text-align:left;">' . $deduction_data['name'] . '</td>
                                                <td style="text-align:right;"><b>' . number_format($deduction_data['amount']) . '</b></td>
                                            </tr>';
                    }
                    //-------------------------------------------------------------------------------------------------------------------------------------------------------

                    //BPJS Employee
                    //Kalo dia mempunyai BPJS
                    $bpjsKeys = json_decode($record['bpjs_employee'], true);
                    $html_bpjs_emp = "";
                    $arr_bpjs_emp_amount_total = 0;
                    foreach ($bpjsKeys as $bpjs_employee => $bpjs_value) {
                        $arr_bpjs = explode("_", $bpjs_employee);
                        $r_bpjs_emp = $this->crud->read('bpjs', ['status' => 0], ["number" => $arr_bpjs[0]]);

                        $arr_bpjs_emp_amount_total += !empty($bpjs_value) ? $bpjs_value : 0;
                        if (!empty($r_bpjs_emp->employee)) {
                            $html_bpjs_emp .= ' <tr>
                                                    <td style="text-align:left;">' . $r_bpjs_emp->name . '</td>
                                                    <td style="text-align:right;"><b>' . number_format($bpjs_value) . '</b></td>
                                                </tr>';
                        }
                    }
                    //-------------------------------------------------------------------------------------------------------------------------------------------------------

                    $total_income = $record['salary'] + $arr_allowance_amount_total + $record['total_overtime_amount'] + $record['correction_plus'];
                    $total_deduction = $record['deduction_absence_amount'] + $total_deduction_amount + $arr_deduction_amount_total + $record['loan_bank'] + $record['loan_cooperative'] + $record['loan_other'] + $record['correction_minus'];

                    $html .= '  <div class="card mb-2">
                                    <div class="card-body">
                                        <h5><span class="badge bg-success">Income</span></h5>
                                        <table style="width:100%; font-size:12px;">
                                            <tr>
                                                <td style="text-align:left;" width="150">Basic Salary</td>
                                                <td style="text-align:right;" width="150"><b>' . number_format($record['salary']) . '</b></td>
                                            </tr>
                                            <tr>
                                                <td style="text-align:left;">Allowences</td>
                                                <td style="text-align:right;"><b>' . number_format($arr_allowance_amount_total) . '</b></td>
                                            </tr>
                                            <tr>
                                                <td style="text-align:left;">Overtime</td>
                                                <td style="text-align:right;">(<b>' . ($record['overtime_weekday'] + $record['overtime_holiday']) . '</b> Hour) <b>' . number_format(($record['overtime_amount_weekday'] + $record['overtime_amount_holiday'])) . '</b></td>
                                            </tr>
                                            <tr>
                                                <td style="text-align:left;">Correction Plus</td>
                                                <td style="text-align:right;"><b>' . number_format(($record['amount_plus_correction'])) . '</b></td>
                                            </tr>
                                            <tr>
                                                <td style="text-align:left;">Correction Salary</td>
                                                <td style="text-align:right;"><b>' . number_format(($record['amount_plus_salary'])) . '</b></td>
                                            </tr>
                                            <tr>
                                                <td style="text-align:left;">Correction Overtime</td>
                                                <td style="text-align:right;">(<b>' . $record['overtime_correction'] . '</b> Hour) <b>' . number_format(($record['overtime_amount_correction'])) . '</b></td>
                                            </tr>
                                            <tr>
                                                <th style="text-align:left;">Total Income</th>
                                                <th style="text-align:right;"><b>' . number_format($total_income) . '</b></th>
                                            </tr>
                                        </table>
                                    </div>
                                </div>
                                <div class="card mb-2">
                                    <div class="card-body">
                                        <h5><span class="badge bg-danger">Deduction</span></h5>
                                        <table style="width:100%; font-size:12px;">
                                            <tr>
                                                <td style="text-align:left;" width="150">Absence</td>
                                                <td style="text-align:right;" width="150"><b>' . number_format($record['deduction_absence_amount'] + $total_deduction_amount) . '</b></td>
                                            </tr>
                                            ' . @$html_deduction . '
                                            <tr>
                                                <td style="text-align:left;">Pinjaman Bank</td>
                                                <td style="text-align:right;"><b>' . number_format($record['loan_bank']) . '</b></td>
                                            </tr>
                                            <tr>
                                                <td style="text-align:left;">Pinjaman Koperasi</td>
                                                <td style="text-align:right;"><b>' . number_format($record['loan_cooperative']) . '</b></td>
                                            </tr>
                                            <tr>
                                                <td style="text-align:left;">Pinjaman Lainnya</td>
                                                <td style="text-align:right;"><b>' . number_format($record['loan_other']) . '</b></td>
                                            </tr>
                                            <tr>
                                                <td style="text-align:left;">Correction Minus</td>
                                                <td style="text-align:right;"><b>' . number_format($record['correction_minus']) . '</b></td>
                                            </tr>
                                            <tr>
                                                <th style="text-align:left;">Total Deduction</th>
                                                <th style="text-align:right;"><b>' . number_format($total_deduction) . '</b></th>
                                            </tr>
                                        </table>
                                    </div>
                                </div>
                                <div class="card mb-2">
                                    <div class="card-body">
                                        <h5><span class="badge bg-warning">BPJS</span></h5>
                                        <table style="width:100%; font-size:12px;">
                                            ' . $html_bpjs_emp . '
                                            <tr>
                                                <th style="text-align:left;">Total BPJS</th>
                                                <th style="text-align:right;"><b>' . number_format(($arr_bpjs_emp_amount_total)) . '</b></th>
                                            </tr>
                                        </table>
                                    </div>
                                </div>
                                <div class="card mb-2">
                                    <div class="card-body">
                                        <h5><span class="badge bg-success">Net Income</span></h5>
                                        <table style="width:100%; font-size:12px;">
                                            <tr>
                                                <th style="text-align:left;">Netto</th>
                                                <th style="text-align:right;"><b>' . number_format(($total_income - $total_deduction)) . '</b></th>
                                            </tr>
                                            
                                            <tr>
                                                <th style="text-align:left;">PPH 21</th>
                                                <th style="text-align:right;"><b>' . number_format($record['pph']) . '</b></th>
                                            </tr>
                                            <tr>
                                                <th style="text-align:left;">Grand Total</th>
                                                <th style="text-align:right;"><b>' . number_format($record['net_income']) . '</b></th>
                                            </tr>
                                        </table>
                                    </div>
                                </div>';
                }

                echo $html;
            } else {
                echo '  <div div class="alert alert-warning mt-4" role="alert">
                            Data not Found
                        </div>';
            }
        } else {
            echo '  <div div class="alert alert-warning mt-4" role="alert">
                        Data not Found
                    </div>';
        }
    }
}
