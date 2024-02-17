<?php
date_default_timezone_set("Asia/Bangkok");
defined('BASEPATH') or exit('No direct script access allowed');

class Payrolls extends CI_Controller
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
        $this->form_validation->set_rules('employee_id', 'Employee', 'required|min_length[1]|max_length[30]');
    }

    //HALAMAN UTAMA
    public function index()
    {
        if (empty($this->session->username)) {
            redirect('error_session');
        } elseif ($this->checkuserAccess($this->id_menu()) > 0) {
            $data['button'] = $this->getbutton($this->id_menu());
            $data['permit_type'] = $this->crud->reads('permit_types', [], ['payroll' => 'NON DEDUCTION']);
            $data['allowance'] = $this->crud->reads('allowances', [], [], "", "name", "asc");
            $data['deduction'] = $this->crud->reads('deductions');
            $data['permit_type_d'] = $this->crud->reads('permit_types', [], ['payroll' => 'DEDUCTION']);
            $data['bpjs'] = $this->crud->reads('bpjs', ['status' => 0]);

            $this->load->view('template/header', $data);
            $this->load->view('payroll/payrolls');
        } else {
            redirect('error_access');
        }
    }

    //GET DATA
    public function read()
    {
        $filter_from = $this->input->get('filter_from');
        $period_start = date("Y-m", strtotime($filter_from));
        $readPayroll = $this->crud->reads('payrolls', ["period_start" => $period_start]);

        if (count($readPayroll) > 0) {
            echo json_encode(array("generate" => "EXIST", "status" => @$readPayroll[0]->status));
        } else {
            echo json_encode(array("generate" => "OK", "status" => @$readPayroll[0]->status));
        }
    }

    public function readApproval()
    {
        $filter_from = $this->input->post('filter_from');
        $filter_to = $this->input->post('filter_to');
        $period_start = date("Y-m", strtotime($filter_from));
        $period_end = date("Y-m", strtotime($filter_to));
        $readPayroll = $this->crud->read('payrolls', [], ["period_start" => $period_start, "period_end" => $period_end]);
        $readNotification = $this->crud->read('notifications', [], ["table_id" => $readPayroll->id, "table_name" => "payrolls", "users_id_to" => ""]);

        if (!empty($readNotification->id)) {
            echo json_encode(["status" => "APPROVE"]);
        } else {
            echo json_encode(["status" => "CHECKED"]);
        }
    }

    //GET DATATABLES
    public function datatables()
    {
        if ($this->input->get()) {
            $filter_from = $this->input->get('filter_from');
            $filter_to = $this->input->get('filter_to');
            $filter_division = $this->input->get('filter_division');
            $filter_departement = $this->input->get('filter_departement');
            $filter_departement_sub = $this->input->get('filter_departement_sub');
            $filter_employee = $this->input->get('filter_employee');
            $filter_employee_type = $this->input->get('filter_employee_type');
            $filter_group = $this->input->get('filter_group');
            $username = $this->session->username;

            $page   = $this->input->post('page');
            $rows   = $this->input->post('rows');
            //Pagination 1-10
            $page   = isset($page) ? intval($page) : 1;
            $rows   = isset($rows) ? intval($rows) : 10;
            $offset = ($page - 1) * $rows;
            $result = array();
            //Select Query

            $period_start = date("Y-m", strtotime($filter_from));
            $period_end = date("Y-m", strtotime($filter_to));

            $this->db->select('a.*, b.national_id, d.name as source_name');
            $this->db->from('payrolls a');
            $this->db->join('employees b', 'a.employee_id = b.id');
            $this->db->join('sources d', 'b.source_id = d.id', 'left');
            $this->db->join('privilege_groups c', "b.group_id = c.group_id and c.username = '$username' and c.status = '1'");
            if ($filter_from != "" && $filter_to != "") {
                $this->db->where('a.period_start =', $period_start);
                $this->db->where('a.period_end =', $period_end);
            }
            $this->db->like('b.division_id', $filter_division);
            $this->db->like('b.departement_id', $filter_departement);
            $this->db->like('b.departement_sub_id', $filter_departement_sub);
            $this->db->like('b.id', $filter_employee);
            $this->db->like('b.contract_id', $filter_employee_type);
            $this->db->like('c.group_id', $filter_group);
            $this->db->order_by('a.name', 'ASC');

            //Total Data
            $totalRows = $this->db->count_all_results('', false);
            //Limit 1 - 10
            $this->db->limit($rows, $offset);
            //Get Data Array
            $records = $this->db->get()->result_array();
            foreach ($records as $record) {

                $arr = array(
                    "employee_id" => $record['employee_id'],
                    "number" => $record['number'],
                    "name" => $record['name'],
                    "period_start" => $record['period_start'],
                    "period_end" => $record['period_end'],
                    "division_name" => $record['division_name'],
                    "departement_name" => $record['departement_name'],
                    "departement_sub_name" => $record['departement_sub_name'],
                    "contract_name" => $record['contract_name'],
                    "position_name" => $record['position_name'],
                    "group_name" => $record['group_name'],
                    "job_type" => $record['job_type'],
                    "source_name" => $record['source_name'],
                    "level" => $record['level'],
                    "marital" => $record['marital'],
                    "tax_id" => $record['tax_id'],
                    "national_id" => $record['national_id'],
                    "shift_name" => $record['shift_name'],
                    //"attandance" => json_encode($arr_permit_combine),
                    "attandance_wd" => $record['attandance_wd'],
                    "working_day" => $record['working_day'],
                    "salary" => $record['salary'],
                    //"allowence" => json_encode($arr_allowance_combine),
                    //"bpjs_company" => json_encode($arr_bpjs_com_combine),
                    "bpjs_company_total" => $record['bpjs_company_total'],
                    "bpjs_company_total_2" => $record['bpjs_company_total'],
                    "correction_plus" => $record['correction_plus'],
                    "overtime_weekday" => $record['overtime_weekday'],
                    "overtime_convert_weekday" => $record['overtime_convert_weekday'],
                    "overtime_amount_weekday" => $record['overtime_amount_weekday'],
                    "overtime_holiday" => $record['overtime_holiday'],
                    "overtime_convert_holiday" => $record['overtime_convert_holiday'],
                    "overtime_amount_holiday" => $record['overtime_amount_holiday'],
                    "overtime_correction" => $record['overtime_correction'],
                    "overtime_convert_correction" => $record['overtime_convert_correction'],
                    "overtime_amount_correction" => $record['overtime_amount_correction'],
                    "total_overtime" => $record['total_overtime'],
                    "total_overtime_convert" => $record['total_overtime_convert'],
                    "total_overtime_amount" => $record['total_overtime_amount'],
                    "total_all_allowance" => $record['total_all_allowance'],
                    //"deduction_number" => json_encode($arr_permit_type_combine_b),
                    //"deduction_amount" => json_encode($arr_permit_type_combine),
                    "deduction_absence" => $record['deduction_absence'],
                    "deduction_absence_amount" => $record['deduction_absence_amount'],
                    "correction_minus" => $record['correction_minus'],
                    "total_all_deduction" => $record['total_all_deduction'],
                    "income" => $record['income'],
                    //"deduction" => json_encode($arr_deduction_combine),
                    "loan_cooperative" => $record['loan_cooperative'],
                    "loan_bank" => $record['loan_bank'],
                    "loan_other" => $record['loan_other'],
                    //"bpjs_employee" => json_encode($arr_bpjs_emp_combine),
                    "bpjs_employee_total" => $record['bpjs_employee_total'],
                    "pph" => $record['pph'],
                    "net_income" => $record['net_income'],
                );

                $arr_final[] = array_merge(
                    $arr,
                    json_decode($record['attandance'], true),
                    json_decode($record['allowence'], true),
                    json_decode($record['bpjs_company'], true),
                    json_decode($record['deduction_number'], true),
                    json_decode($record['deduction_amount'], true),
                    json_decode($record['deduction'], true),
                    json_decode($record['bpjs_employee'], true)
                );
            }

            if (@$arr_final == null) {
                $result = [];
            } else {
                $result = @$arr_final;
            }

            $datas = array();
            $datas['total'] = $totalRows;
            $datas['rows'] = $result;
            echo json_encode($datas);
        }
    }

    public function generatePayroll()
    {
        if ($this->input->get()) {
            $filter_from = $this->input->get('filter_from');
            $filter_to = $this->input->get('filter_to');
            $filter_division = $this->input->get('filter_division');
            $filter_departement = $this->input->get('filter_departement');
            $filter_departement_sub = $this->input->get('filter_departement_sub');
            $filter_employee = $this->input->get('filter_employee');
            $filter_employee_type = $this->input->get('filter_employee_type');
            $filter_group = $this->input->get('filter_group');
            $username = $this->session->username;

            $query = $this->db->query("SELECT a.*, 
                    b.name as division_name,
                    c.name as departement_name,
                    d.name as departement_sub_name,
                    d.type,
                    f.id as contract_id,
                    f.name as `contract_name`,
                    g.name as position_name,
                    g.level,
                    i.id as group_id,
                    i.name as group_name,
                    n.name as shift_name,
                    l.name as shift_name_2,
                    l.days,
                    p.amount as salary,
                    p.bpjs,
                    q.number as marital,
                    a.tax_id
                FROM employees a
                JOIN divisions b ON a.division_id = b.id
                JOIN departements c ON a.departement_id = c.id
                JOIN departement_subs d ON a.departement_sub_id = d.id
                LEFT JOIN contracts f ON a.contract_id = f.id
                LEFT JOIN positions g ON a.position_id = g.id
                LEFT JOIN groups i ON a.group_id = i.id
                LEFT JOIN shift_employees k ON a.id = k.employee_id
                LEFT JOIN shifts n ON k.shift_id = n.id
                LEFT JOIN shift_details l ON n.id = l.shift_id
                JOIN setup_salaries p ON p.employee_id = a.id
                LEFT JOIN maritals q ON a.marital_id = q.id
                JOIN privilege_groups m ON i.id = m.group_id and m.username = '$username' and m.status = '1'
                WHERE a.deleted = 0 and a.status = 0
                AND a.division_id LIKE '%$filter_division%'
                AND a.departement_id LIKE '%$filter_departement%'
                AND a.departement_sub_id LIKE '%$filter_departement_sub%'
                AND a.contract_id LIKE '%$filter_employee_type%'
                AND a.id LIKE '%$filter_employee%'
                AND a.group_id LIKE '%$filter_group%'
                GROUP BY a.id
                ORDER BY b.name ASC, c.name ASC, d.name ASC, a.number, a.name ASC");
            $records = $query->result_array();

            $datas = array();
            $datas['total'] = count($records);
            $datas['rows'] = $records;
            echo json_encode($datas);
        }
    }

    //CREATE DATA
    public function create()
    {
        if ($this->input->post()) {
            $record   = $this->input->post();

            $filter_from = $this->input->get('filter_from');
            $filter_to = $this->input->get('filter_to');

            $period_start = date("Y-m", strtotime($filter_from));
            $period_end = date("Y-m", strtotime($filter_to));

            //Convert tanggal ke time supaya hitungan tanggal nya mudah
            $start = strtotime($filter_from);
            $finish = strtotime($filter_to);

            //Setting Payroll
            $config = $this->db->get('payroll_config')->result();

            //Permit_allowance
            //Mengambil field dan isinya dari permit allowance
            $q_permit = $this->db->query("SELECT b.number, b.name, SUM(a.duration) as amount
                    FROM permit_types b
                    LEFT JOIN permits a ON a.permit_type_id = b.id and a.employee_id = '$record[id]' and a.permit_date >= '$filter_from' and a.permit_date <= '$filter_to'
                    LEFT JOIN `notifications` c ON a.id = c.table_id and c.table_name = 'permits'
                    WHERE b.payroll = 'NON DEDUCTION' and (c.users_id_to = '' or c.users_id_to is null)
                    GROUP BY b.id");
            $r_permit = $q_permit->result_array();
            $arr_permit_number = "";
            $arr_permit_amount = "";
            foreach ($r_permit as $permit_data) {
                $arr_permit_number .= strtolower($permit_data['number']) . "a,";
                $arr_permit_amount .= $permit_data['amount'] . ",";
            }

            $arr_permit_number_ex = explode(",", substr($arr_permit_number, 0, -1));
            $arr_permit_amount_ex = explode(",", substr($arr_permit_amount, 0, -1));
            $arr_permit_combine = array_combine($arr_permit_number_ex, $arr_permit_amount_ex);

            $q_permit2 = $this->db->query("SELECT b.number, b.name, SUM(a.duration) as amount
                    FROM permit_types b
                    LEFT JOIN permits a ON a.permit_type_id = b.id and a.employee_id = '$record[id]' and a.permit_date >= '$filter_from' and a.permit_date <= '$filter_to'
                    LEFT JOIN `notifications` c ON a.id = c.table_id and c.table_name = 'permits'
                    WHERE (c.users_id_to = '' or c.users_id_to is null)
                    GROUP BY b.id ORDER BY b.name asc");
            $r_permit2 = $q_permit2->result_array();
            $arr_total_permit = 0;
            foreach ($r_permit2 as $permit_data2) {
                $arr_total_permit += $permit_data2['amount'];
            }

            //-------------------------------------------------------------------------------------------------------------------------------------------------------
            $weekday = array();
            $weekend = array();
            $holiday = 0;

            $final_total_ovetime_amount_weekday = 0;
            $final_total_ovetime_amount_holiday = 0;
            $final_total_ovetime_amount_correction = 0;
            $final_total_ovetime_hour_weekday = 0;
            $final_total_ovetime_hour_holiday = 0;
            $final_total_ovetime_hour_correction = 0;
            $final_total_ovetime_convert_weekday = 0;
            $final_total_ovetime_convert_holiday = 0;
            $final_total_ovetime_convert_correction = 0;
            $total_bank = 0;
            $total_coorperative = 0;
            $total_other = 0;
            $total_correction_plus = 0;
            $total_correction_minus = 0;

            //Menentukan Masuk kerja dan libur
            //Looping berdasarkan cutoff periode per tanggal
            $masuk = 0;
            $absen = 0;
            $change_day_qty = 0;
            for ($i = $start; $i <= $finish; $i += (60 * 60 * 24)) {
                $working_date = date('Y-m-d', $i);

                //Attandance
                //cek apakah dia absen atau tidak per tanggal dari looping
                $this->db->select("*");
                $this->db->from('attandances');
                $this->db->where('number', $record['number']);
                $this->db->where("(date_in = '$working_date' or date_out = '$working_date')");
                $attandance = $this->db->get()->row();

                //Change Days
                $this->db->select("*");
                $this->db->from('change_days');
                $this->db->where('employee_id', $record['id']);
                $this->db->where('end', $working_date);
                $changeDays = $this->db->get()->row();

                //Overtime Regular
                //cek apakah dia ada lembur atau tidak per tanggal dari looping
                $this->db->select("a.*");
                $this->db->from('overtimes a');
                $this->db->join('notifications b', "a.id = b.table_id and b.table_name = 'overtimes'", 'left');
                $this->db->where('a.employee_id', $record['id']);
                $this->db->where('a.trans_date', $working_date);
                $this->db->where('a.type', 'REGULAR');
                $this->db->where("(b.users_id_to = '' or b.users_id_to is null)");
                $this->db->group_by('a.employee_id');
                $overtime = $this->db->get()->row();

                //Overtime Correction
                //cek apakah dia ada lembur atau tidak per tanggal dari looping
                $this->db->select("a.*");
                $this->db->from('overtimes a');
                $this->db->join('notifications b', "a.id = b.table_id and b.table_name = 'overtimes'", 'left');
                $this->db->where('a.employee_id', $record['id']);
                $this->db->where('a.trans_date', $working_date);
                $this->db->where('a.type', 'CORRECTION');
                $this->db->where("(b.users_id_to = '' or b.users_id_to is null)");
                $this->db->group_by('a.employee_id');
                $overtime_correction = $this->db->get()->row();

                //cek apakah ada hari libur nasional per tanggal dari looping
                $this->db->select('trans_date');
                $this->db->from('calendars');
                $this->db->where('trans_date', $working_date);
                $holiday_overtime = $this->db->get()->row();

                //Correction PLUS
                $this->db->select("SUM(amount) as amount");
                $this->db->from('corrections');
                $this->db->group_by('trans_date');
                $this->db->where('employee_id', $record['id']);
                $this->db->where('trans_date', $working_date);
                $this->db->where('correction_type', 'PLUS');
                $correction_plus = $this->db->get()->row();

                //Correction MINUS
                $this->db->select("SUM(amount) as amount");
                $this->db->from('corrections');
                $this->db->group_by('trans_date');
                $this->db->where('employee_id', $record['id']);
                $this->db->where('trans_date', $working_date);
                $this->db->where('correction_type', 'MINUS');
                $correction_minus = $this->db->get()->row();

                $queryPermit = $this->db->query("SELECT b.number, b.name, SUM(a.duration) as amount
                    FROM permit_types b
                    LEFT JOIN permits a ON a.permit_type_id = b.id and a.employee_id = '$record[id]' and a.permit_date = '$working_date'
                    LEFT JOIN `notifications` c ON a.id = c.table_id and c.table_name = 'permits'
                    WHERE (c.users_id_to = '' or c.users_id_to is null) and b.absence = 'YES'
                    GROUP BY b.id ORDER BY b.name asc");
                $rowPermit = $queryPermit->result_array();

                //Loan
                @$r_loan_bank = $this->crud->read('loans', ['status' => 0, 'employee_id' => $record['id'], 'loan_type' => 'BANK', 'trans_date' => $working_date]);
                @$r_loan_cooperative = $this->crud->read('loans', ['status' => 0, 'employee_id' => $record['id'], 'loan_type' => 'COOPERATIVE', 'trans_date' => $working_date]);
                @$r_loan_other = $this->crud->read('loans', ['status' => 0, 'employee_id' => $record['id'], 'loan_type' => 'OTHER', 'trans_date' => $working_date]);

                $total_bank += @$r_loan_bank->amount;
                $total_coorperative += @$r_loan_cooperative->amount;
                $total_other += @$r_loan_other->amount;
                //-------------------------------------------------------------------------------------------------------------------------------------------------------

                //ambil durasi berapa jam jika ada lemburan
                $time_begin = strtotime(@$overtime->trans_date . " " . @$overtime->start);
                $time_end = strtotime(@$overtime->trans_date . " " . @$overtime->end);
                $diff = $time_end - $time_begin;
                $hour = floor($diff / (60 * 60));

                //ambil durasi berapa jam jika ada lemburan
                $time_begin_correction = strtotime(@$overtime_correction->trans_date . " " . @$overtime_correction->start);
                $time_end_correction = strtotime(@$overtime_correction->trans_date . " " . @$overtime_correction->end);
                $diff_correction = $time_end_correction - $time_begin_correction;
                $hour_correction = floor($diff_correction / (60 * 60));

                //Rumus untuk mengambil upah lembur adalah Gaji / 173
                $standard_overtime = round($record['salary'] / 173);
                $total_ovetime_amount_weekday = 0;
                $total_ovetime_amount_holiday = 0;
                $total_ovetime_amount_correction = 0;
                $total_ovetime_hour_weekday = 0;
                $total_ovetime_hour_holiday = 0;
                $total_ovetime_hour_correction = 0;
                $total_ovetime_convert_weekday = 0;
                $total_ovetime_convert_holiday = 0;
                $total_ovetime_convert_correction = 0;

                //Jika tanggal yg di looping bukan hari sabtu dan minggu
                if (date('w', $i) !== '0' && date('w', $i) !== '6') {
                    $weekday[] = date('Y-m-d', $i);

                    //Working Calendar
                    //Untuk mengambil jumlah hari libur nasional
                    $this->db->select('trans_date');
                    $this->db->from('calendars');
                    $this->db->where('trans_date', $working_date);
                    $calendar = $this->db->get()->result_array();
                    $holiday += count($calendar);

                    //Ini untuk menghitung overtime
                    //Kalo ada tanggal Merah
                    if (!empty($holiday_overtime->trans_date)) {
                        //Looping dari durasi jam lembur
                        for ($o = 0; $o < $hour; $o++) {
                            $total_ovetime_amount_holiday += ($standard_overtime * 2);
                            $total_ovetime_convert_holiday += (1 * 2);
                            $total_ovetime_hour_holiday++;
                        }

                        for ($o = 0; $o < $hour_correction; $o++) {
                            $total_ovetime_amount_correction += ($standard_overtime * 2);
                            $total_ovetime_convert_correction += (1 * 2);
                            $total_ovetime_hour_correction++;
                        }

                        $change_day_qty += 0;
                        $masuk += 0;
                        $absen += 0;
                    } else {
                        //Perhitungan Overtime
                        for ($o = 0; $o < $hour; $o++) {
                            if ($o == 0) {
                                $total_ovetime_amount_weekday += ($standard_overtime * 1.5);
                                $total_ovetime_convert_weekday += (1 * 1.5);
                            } else {
                                $total_ovetime_amount_weekday += ($standard_overtime * 2);
                                $total_ovetime_convert_weekday += (1 * 2);
                            }
                            $total_ovetime_hour_weekday++;
                        }

                        for ($o = 0; $o < $hour_correction; $o++) {
                            if ($o == 0) {
                                $total_ovetime_amount_correction += ($standard_overtime * 1.5);
                                $total_ovetime_convert_correction += (1 * 1.5);
                            } else {
                                $total_ovetime_amount_correction += ($standard_overtime * 2);
                                $total_ovetime_convert_correction += (1 * 2);
                            }
                            $total_ovetime_hour_correction++;
                        }

                        //Jika dia tidak absen
                        if (@$changeDays->end != null) {
                            $change_day_qty += 1;
                        } else {
                            $change_day_qty += 0;
                        }

                        if (@$rowPermit[0]['amount'] > 0) {
                            $masuk += 1;
                            $absen += 1;
                        } elseif (@$attandance->time_in == null && @$attandance->time_out == null) {
                            $masuk += 0;
                            $absen += 1;
                        } else {
                            $masuk += 1;
                            $absen += 0;
                        }
                    }
                } else {
                    $weekend[] = date('Y-m-d', $i);

                    if (@$changeDays->end != null) {
                        $change_day_qty += 1;
                    } else {
                        $change_day_qty += 0;
                    }

                    $masuk += 0;
                    $absen += 0;

                    //Perhitungan Overtime
                    for ($o = 0; $o < $hour; $o++) {
                        $total_ovetime_amount_holiday += ($standard_overtime * 2);
                        $total_ovetime_convert_holiday += (1 * 2);
                        $total_ovetime_hour_holiday++;
                    }

                    for ($o = 0; $o < $hour_correction; $o++) {
                        $total_ovetime_amount_correction += ($standard_overtime * 2);
                        $total_ovetime_convert_correction += (1 * 2);
                        $total_ovetime_hour_correction++;
                    }
                }

                $final_total_ovetime_hour_weekday += $total_ovetime_hour_weekday;
                $final_total_ovetime_hour_holiday += $total_ovetime_hour_holiday;
                $final_total_ovetime_hour_correction += $total_ovetime_hour_correction;
                $final_total_ovetime_amount_weekday += round($total_ovetime_amount_weekday);
                $final_total_ovetime_amount_holiday += round($total_ovetime_amount_holiday);
                $final_total_ovetime_amount_correction += round($total_ovetime_amount_correction);
                $final_total_ovetime_convert_weekday += $total_ovetime_convert_weekday;
                $final_total_ovetime_convert_holiday += $total_ovetime_convert_holiday;
                $final_total_ovetime_convert_correction += $total_ovetime_convert_correction;
                $total_correction_plus += @$correction_plus->amount;
                $total_correction_minus += @$correction_minus->amount;
            }
            //-------------------------------------------------------------------------------------------------------------------------------------------------------

            $hkw = (@count($weekday) - $holiday);

            //Allowance Amount
            //jika dia ada tunjuangan ambil field dan isinya
            $q_allowance = $this->db->query("SELECT b.number, b.name, coalesce(a.amount, 0) as amount, b.calculate_days, b.type
                    FROM allowances b
                    LEFT JOIN setup_allowances a ON a.allowance_id = b.id and a.employee_id = '$record[id]'
                    GROUP BY b.id ORDER BY b.name asc");
            $r_allowance = $q_allowance->result_array();

            $arr_allowance_number = "";
            $arr_allowance_amount = "";
            $arr_allowance_amount_total = 0;
            $arr_allowance_amount_total_bpjs = 0;
            $arr_allowance_amount_number = 0;
            $count_allowance_number = 0;
            foreach ($r_allowance as $allowance_data) {
                if ($allowance_data['calculate_days'] == "1") {
                    $arr_allowance_number .= strtolower($allowance_data['number']) . ",";
                    $arr_allowance_amount .= ($allowance_data['amount'] * $hkw) . ",";
                    $arr_allowance_amount_total += ($allowance_data['amount'] * $hkw);
                } else {
                    $arr_allowance_number .= strtolower($allowance_data['number']) . ",";
                    $arr_allowance_amount .= $allowance_data['amount'] . ",";
                    $arr_allowance_amount_total += $allowance_data['amount'];
                }

                if ($allowance_data['type'] == "TEMPORARY" && $allowance_data['amount'] > 0) {
                    $arr_allowance_amount_total_bpjs += 0;
                    $count_allowance_number += 1;

                    if ($allowance_data['calculate_days'] == "1") {
                        $arr_allowance_amount_number += ($allowance_data['amount'] * $hkw);
                    } else {
                        $arr_allowance_amount_number += ($allowance_data['amount']);
                    }
                } elseif ($allowance_data['type'] == "FIX" && $allowance_data['amount'] > 0) {
                    $arr_allowance_amount_total_bpjs += ($allowance_data['amount']);
                    $count_allowance_number += 0;
                    $arr_allowance_amount_number += 0;
                } else {
                    $arr_allowance_amount_total_bpjs += 0;
                    $count_allowance_number += 0;
                    $arr_allowance_amount_number += 0;
                }
            }

            $arr_allowance_number_ex = explode(",", substr($arr_allowance_number, 0, -1));
            $arr_allowance_amount_ex = explode(",", substr($arr_allowance_amount, 0, -1));
            $arr_allowance_combine = array_combine($arr_allowance_number_ex, $arr_allowance_amount_ex);
            //-------------------------------------------------------------------------------------------------------------------------------------------------------

            //Deduction Amount
            //Jika dia ada pemotongan gaji ambil field dan isinya
            $q_deduction = $this->db->query("SELECT b.number, b.name, a.amount
                    FROM deductions b
                    LEFT JOIN setup_deductions a ON a.deduction_id = b.id and a.employee_id = '$record[id]'
                    GROUP BY b.id ORDER BY b.name asc");
            $r_deduction = $q_deduction->result_array();

            $arr_deduction_number = "";
            $arr_deduction_amount = "";
            $arr_deduction_amount_total = 0;
            foreach ($r_deduction as $deduction_data) {
                $arr_deduction_number .= strtolower($deduction_data['number']) . ",";
                $arr_deduction_amount .= $deduction_data['amount'] . ",";
                $arr_deduction_amount_total += $deduction_data['amount'];
            }

            $arr_deduction_number_ex = explode(",", substr($arr_deduction_number, 0, -1));
            $arr_deduction_amount_ex = explode(",", substr($arr_deduction_amount, 0, -1));
            $arr_deduction_combine = array_combine($arr_deduction_number_ex, $arr_deduction_amount_ex);
            //-------------------------------------------------------------------------------------------------------------------------------------------------------

            //BPJS Employee
            //Kalo dia mempunyai BPJS
            $r_bpjs_emp = $this->crud->reads('bpjs', ['status' => 0]);
            $arr_bpjs_emp_number = "";
            $arr_bpjs_emp_amount = "";
            $arr_bpjs_emp_amount_total = 0;
            foreach ($r_bpjs_emp as $bpjs_emp_data) {
                $arr_bpjs_emp_number .= strtolower($bpjs_emp_data->number) . "_employee,";
                if ($record['jamsostek'] == "" && $record['jkn'] == "") {
                    $arr_bpjs_emp_amount .= 0 . ",";
                    $arr_bpjs_emp_amount_total += 0;
                } elseif ($record['jamsostek'] == "" && $record['jkn'] != "") {
                    if ($bpjs_emp_data->number == "BPJS") {
                        if ($record['bpjs'] == "0") {
                            $arr_bpjs_emp_amount .= round((($record['salary'] + $arr_allowance_amount_total_bpjs) * ($bpjs_emp_data->employee / 100)) * $record['jkn_family']) . ",";
                            $arr_bpjs_emp_amount_total += round((($record['salary'] + $arr_allowance_amount_total_bpjs) * ($bpjs_emp_data->employee / 100)) * $record['jkn_family']);
                        } else {
                            $arr_bpjs_emp_amount .= round(($record['bpjs'] * ($bpjs_emp_data->employee / 100)) * $record['jkn_family']) . ",";
                            $arr_bpjs_emp_amount_total += round(($record['bpjs'] * ($bpjs_emp_data->employee / 100)) * $record['jkn_family']);
                        }
                    } else {
                        $arr_bpjs_emp_amount .= 0 . ",";
                        $arr_bpjs_emp_amount_total += 0;
                    }
                } elseif ($record['jamsostek'] != "" && $record['jkn'] == "") {
                    if ($bpjs_emp_data->number != "BPJS") {
                        if ($record['bpjs'] == "0") {
                            $arr_bpjs_emp_amount .= round((($record['salary'] + $arr_allowance_amount_total_bpjs) * ($bpjs_emp_data->employee / 100)) * $record['jkn_family']) . ",";
                            $arr_bpjs_emp_amount_total += round((($record['salary'] + $arr_allowance_amount_total_bpjs) * ($bpjs_emp_data->employee / 100)) * $record['jkn_family']);
                        } else {
                            $arr_bpjs_emp_amount .= round(($record['bpjs'] * ($bpjs_emp_data->employee / 100)) * $record['jkn_family']) . ",";
                            $arr_bpjs_emp_amount_total += round(($record['bpjs'] * ($bpjs_emp_data->employee / 100)) * $record['jkn_family']);
                        }
                    } else {
                        $arr_bpjs_emp_amount .= 0 . ",";
                        $arr_bpjs_emp_amount_total += 0;
                    }
                } else {
                    if ($bpjs_emp_data->number == "BPJS") {
                        if ($record['bpjs'] == "0") {
                            $arr_bpjs_emp_amount .= round((($record['salary'] + $arr_allowance_amount_total_bpjs) * ($bpjs_emp_data->employee / 100)) * $record['jkn_family']) . ",";
                            $arr_bpjs_emp_amount_total += round((($record['salary'] + $arr_allowance_amount_total_bpjs) * ($bpjs_emp_data->employee / 100)) * $record['jkn_family']);
                        } else {
                            $arr_bpjs_emp_amount .= round(($record['bpjs'] * ($bpjs_emp_data->employee / 100)) * $record['jkn_family']) . ",";
                            $arr_bpjs_emp_amount_total += round(($record['bpjs'] * ($bpjs_emp_data->employee / 100)) * $record['jkn_family']);
                        }
                    } else {
                        $arr_bpjs_emp_amount .= round((($record['salary'] + $arr_allowance_amount_total_bpjs) * ($bpjs_emp_data->employee / 100)) * $record['jkn_family']) . ",";
                        $arr_bpjs_emp_amount_total += round((($record['salary'] + $arr_allowance_amount_total_bpjs) * ($bpjs_emp_data->employee / 100)) * $record['jkn_family']);
                    }
                }
            }

            $arr_bpjs_emp_number_ex = explode(",", substr($arr_bpjs_emp_number, 0, -1));
            $arr_bpjs_emp_amount_ex = explode(",", substr($arr_bpjs_emp_amount, 0, -1));
            $arr_bpjs_emp_combine = array_combine($arr_bpjs_emp_number_ex, $arr_bpjs_emp_amount_ex);
            //-------------------------------------------------------------------------------------------------------------------------------------------------------

            //BPJS Company
            $r_bpjs_com = $this->crud->reads('bpjs', ['status' => 0]);
            $arr_bpjs_com_number = "";
            $arr_bpjs_com_amount = "";
            $arr_bpjs_com_amount_total = 0;
            $arr_bpjs_com_amount_salary_total = 0;
            $arr_bpjs_com_amount_jabatan_total = 0;
            foreach ($r_bpjs_com as $bpjs_com_data) {
                $arr_bpjs_com_number .= strtolower($bpjs_com_data->number) . "_company,";

                if ($record['jamsostek'] == "" && $record['jkn'] == "") {
                    $arr_bpjs_com_amount .= 0 . ",";
                    $arr_bpjs_com_amount_total += 0;
                    $arr_bpjs_com_amount_salary_total += 0;
                    $arr_bpjs_com_amount_jabatan_total += 0;
                } elseif ($record['jamsostek'] == "" && $record['jkn'] != "") {
                    if ($bpjs_com_data->number == "BPJS") {
                        if ($record['bpjs'] == "0") {
                            $arr_bpjs_com_amount .= round((($record['salary'] + $arr_allowance_amount_total_bpjs) * ($bpjs_com_data->company / 100)) * $record['jkn_family']) . ",";
                            $arr_bpjs_com_amount_total += round((($record['salary'] + $arr_allowance_amount_total_bpjs) * ($bpjs_com_data->company / 100)) * $record['jkn_family']);
                            $arr_bpjs_com_amount_salary_total += round((($record['salary'] + $arr_allowance_amount_total_bpjs) * ($bpjs_com_data->company / 100)) * $record['jkn_family']);
                            $arr_bpjs_com_amount_jabatan_total += 0;
                        } else {
                            $arr_bpjs_com_amount .= round((($record['bpjs']) * ($bpjs_com_data->company / 100)) * $record['jkn_family']) . ",";
                            $arr_bpjs_com_amount_total += round((($record['bpjs']) * ($bpjs_com_data->company / 100)) * $record['jkn_family']);
                            $arr_bpjs_com_amount_salary_total += round((($record['bpjs']) * ($bpjs_com_data->company / 100)) * $record['jkn_family']);
                            $arr_bpjs_com_amount_jabatan_total += 0;
                        }
                    } else {
                        $arr_bpjs_com_amount .= 0 . ",";
                        $arr_bpjs_com_amount_total += 0;
                        $arr_bpjs_com_amount_salary_total += 0;
                        $arr_bpjs_com_amount_jabatan_total += 0;
                    }
                } elseif ($record['jamsostek'] != "" && $record['jkn'] == "") {
                    if ($bpjs_com_data->number != "BPJS") {
                        if ($record['bpjs'] == "0") {
                            $arr_bpjs_com_amount .= round((($record['salary'] + $arr_allowance_amount_total_bpjs) * ($bpjs_com_data->company / 100)) * $record['jkn_family']) . ",";
                            $arr_bpjs_com_amount_total += round((($record['salary'] + $arr_allowance_amount_total_bpjs) * ($bpjs_com_data->company / 100)) * $record['jkn_family']);

                            //Perhitungan PPH Salary - Jabatan
                            if ($bpjs_com_data->number == "JKK") {
                                $arr_bpjs_com_amount_salary_total += round((($record['salary'] + $arr_allowance_amount_total_bpjs) * ($bpjs_com_data->company / 100)) * $record['jkn_family']);
                                $arr_bpjs_com_amount_jabatan_total += 0;
                            } elseif ($bpjs_com_data->number == "JKM") {
                                $arr_bpjs_com_amount_salary_total += round((($record['salary'] + $arr_allowance_amount_total_bpjs) * ($bpjs_com_data->company / 100)) * $record['jkn_family']);
                                $arr_bpjs_com_amount_jabatan_total += 0;
                            } elseif ($bpjs_com_data->number == "JHT") {
                                $arr_bpjs_com_amount_salary_total += 0;
                                $arr_bpjs_com_amount_jabatan_total += round((($record['salary'] + $arr_allowance_amount_total_bpjs) * ($bpjs_com_data->company / 100)) * $record['jkn_family']);
                            } elseif ($bpjs_com_data->number == "JP") {
                                $arr_bpjs_com_amount_salary_total += 0;
                                $arr_bpjs_com_amount_jabatan_total += round((($record['salary'] + $arr_allowance_amount_total_bpjs) * ($bpjs_com_data->company / 100)) * $record['jkn_family']);
                            } else {
                                $arr_bpjs_com_amount_salary_total += 0;
                                $arr_bpjs_com_amount_jabatan_total += 0;
                            }
                        } else {
                            $arr_bpjs_com_amount .= round((($record['bpjs']) * ($bpjs_com_data->company / 100)) * $record['jkn_family']) . ",";
                            $arr_bpjs_com_amount_total += round((($record['bpjs']) * ($bpjs_com_data->company / 100)) * $record['jkn_family']);

                            //Perhitungan PPH Salary - Jabatan
                            if ($bpjs_com_data->number == "JKK") {
                                $arr_bpjs_com_amount_salary_total += round((($record['bpjs']) * ($bpjs_com_data->company / 100)) * $record['jkn_family']);
                                $arr_bpjs_com_amount_jabatan_total += 0;
                            } elseif ($bpjs_com_data->number == "JKM") {
                                $arr_bpjs_com_amount_salary_total += round((($record['bpjs']) * ($bpjs_com_data->company / 100)) * $record['jkn_family']);
                                $arr_bpjs_com_amount_jabatan_total += 0;
                            } elseif ($bpjs_com_data->number == "JHT") {
                                $arr_bpjs_com_amount_salary_total += 0;
                                $arr_bpjs_com_amount_jabatan_total += round((($record['bpjs']) * ($bpjs_com_data->company / 100)) * $record['jkn_family']);
                            } elseif ($bpjs_com_data->number == "JP") {
                                $arr_bpjs_com_amount_salary_total += 0;
                                $arr_bpjs_com_amount_jabatan_total += round((($record['bpjs']) * ($bpjs_com_data->company / 100)) * $record['jkn_family']);
                            } else {
                                $arr_bpjs_com_amount_salary_total += 0;
                                $arr_bpjs_com_amount_jabatan_total += 0;
                            }
                        }
                    } else {
                        $arr_bpjs_com_amount .= 0 . ",";
                        $arr_bpjs_com_amount_total += 0;
                        $arr_bpjs_com_amount_salary_total += 0;
                        $arr_bpjs_com_amount_jabatan_total += 0;
                    }
                } else {
                    if ($record['bpjs'] == "0") {
                        $arr_bpjs_com_amount .= round((($record['salary'] + $arr_allowance_amount_total_bpjs) * ($bpjs_com_data->company / 100)) * $record['jkn_family']) . ",";
                        $arr_bpjs_com_amount_total += round((($record['salary'] + $arr_allowance_amount_total_bpjs) * ($bpjs_com_data->company / 100)) * $record['jkn_family']);

                        //Perhitungan PPH Salary - Jabatan
                        if ($bpjs_com_data->number == "JKK") {
                            $arr_bpjs_com_amount_salary_total += round((($record['salary'] + $arr_allowance_amount_total_bpjs) * ($bpjs_com_data->company / 100)) * $record['jkn_family']);
                            $arr_bpjs_com_amount_jabatan_total += 0;
                        } elseif ($bpjs_com_data->number == "JKM") {
                            $arr_bpjs_com_amount_salary_total += round((($record['salary'] + $arr_allowance_amount_total_bpjs) * ($bpjs_com_data->company / 100)) * $record['jkn_family']);
                            $arr_bpjs_com_amount_jabatan_total += 0;
                        } elseif ($bpjs_com_data->number == "JHT") {
                            $arr_bpjs_com_amount_salary_total += 0;
                            $arr_bpjs_com_amount_jabatan_total += round((($record['salary'] + $arr_allowance_amount_total_bpjs) * ($bpjs_com_data->company / 100)) * $record['jkn_family']);
                        } elseif ($bpjs_com_data->number == "JP") {
                            $arr_bpjs_com_amount_salary_total += 0;
                            $arr_bpjs_com_amount_jabatan_total += round((($record['salary'] + $arr_allowance_amount_total_bpjs) * ($bpjs_com_data->company / 100)) * $record['jkn_family']);
                        } else {
                            $arr_bpjs_com_amount_salary_total += 0;
                            $arr_bpjs_com_amount_jabatan_total += 0;
                        }
                    } else {
                        $arr_bpjs_com_amount .= round((($record['bpjs']) * ($bpjs_com_data->company / 100)) * $record['jkn_family']) . ",";
                        $arr_bpjs_com_amount_total += round((($record['bpjs']) * ($bpjs_com_data->company / 100)) * $record['jkn_family']);

                        //Perhitungan PPH Salary - Jabatan
                        if ($bpjs_com_data->number == "JKK") {
                            $arr_bpjs_com_amount_salary_total += round((($record['bpjs']) * ($bpjs_com_data->company / 100)) * $record['jkn_family']);
                            $arr_bpjs_com_amount_jabatan_total += 0;
                        } elseif ($bpjs_com_data->number == "JKM") {
                            $arr_bpjs_com_amount_salary_total += round((($record['bpjs']) * ($bpjs_com_data->company / 100)) * $record['jkn_family']);
                            $arr_bpjs_com_amount_jabatan_total += 0;
                        } elseif ($bpjs_com_data->number == "JHT") {
                            $arr_bpjs_com_amount_salary_total += 0;
                            $arr_bpjs_com_amount_jabatan_total += round((($record['bpjs']) * ($bpjs_com_data->company / 100)) * $record['jkn_family']);
                        } elseif ($bpjs_com_data->number == "JP") {
                            $arr_bpjs_com_amount_salary_total += 0;
                            $arr_bpjs_com_amount_jabatan_total += round((($record['bpjs']) * ($bpjs_com_data->company / 100)) * $record['jkn_family']);
                        } else {
                            $arr_bpjs_com_amount_salary_total += 0;
                            $arr_bpjs_com_amount_jabatan_total += 0;
                        }
                    }
                }
            }

            $arr_bpjs_com_number_ex = explode(",", substr($arr_bpjs_com_number, 0, -1));
            $arr_bpjs_com_amount_ex = explode(",", substr($arr_bpjs_com_amount, 0, -1));
            $arr_bpjs_com_combine = array_combine($arr_bpjs_com_number_ex, $arr_bpjs_com_amount_ex);
            //-------------------------------------------------------------------------------------------------------------------------------------------------------

            //Permit Deduction + Amount
            //Mengambil field dan isinya dari permit deduction + total amount 
            $q_permit_type = $this->db->query("SELECT b.number, b.name, SUM(a.duration) as amount, a.reason_id, b.cutoff
                    FROM permit_types b
                    LEFT JOIN permits a ON a.permit_type_id = b.id and a.employee_id = '$record[id]' and a.permit_date >= '$filter_from' and a.permit_date <= '$filter_to'
                    LEFT JOIN `notifications` c ON a.id = c.table_id and c.table_name = 'permits'
                    WHERE b.payroll = 'DEDUCTION' and (c.users_id_to = '' or c.users_id_to is null)
                    GROUP BY b.id ORDER BY b.name desc");
            $r_permit_type = $q_permit_type->result_array();
            $arr_permit_type_number = "";
            $arr_permit_type_amount = "";
            $arr_permit_type_number_b = "";
            $arr_permit_type_amount_b = "";
            $arr_permit_type_amount_b_total = 0;
            foreach ($r_permit_type as $permit_type_data) {
                if ($permit_type_data['cutoff'] == "YES") {
                    $arr_permit_type_number_b .= strtolower($permit_type_data['number']) . "b,";
                    $arr_permit_type_amount_b .= ($permit_type_data['amount'] / 2) . ",";
                    $arr_permit_type_amount_qty = ($permit_type_data['amount'] / 2);
                    $arr_permit_type_number .= strtolower($permit_type_data['number']) . "_amount,";
                } else {
                    $arr_permit_type_number_b .= strtolower($permit_type_data['number']) . "b,";
                    $arr_permit_type_amount_b .= $permit_type_data['amount'] . ",";
                    $arr_permit_type_amount_qty = $permit_type_data['amount'];
                    $arr_permit_type_number .= strtolower($permit_type_data['number']) . "_amount,";
                }

                //Untuk mengambil nilai harga deduction rumus nya adalah Gaji / 30 hari x jumlah ijinnya
                // $arr_permit_type_amount .= round(($record['salary'] / 30) * $arr_permit_type_amount_qty) . ",";
                // @$arr_permit_type_amount_b_total += round(($record['salary'] / 30) * $arr_permit_type_amount_qty) . ",";

                if ($record['group_name'] == "MAGANG") {
                    $arr_permit_type_amount .= round((($record['salary'] - $arr_deduction_amount_total) / $hkw) * $arr_permit_type_amount_qty) . ",";
                    @$arr_permit_type_amount_b_total += round((($record['salary'] - $arr_deduction_amount_total) / $hkw) * $arr_permit_type_amount_qty) . ",";
                } elseif ($count_allowance_number > 0) {
                    $arr_permit_type_amount .= round(($arr_allowance_amount_number / $hkw) * $arr_permit_type_amount_qty) . ",";
                    @$arr_permit_type_amount_b_total += round(($arr_allowance_amount_number / $hkw) * $arr_permit_type_amount_qty) . ",";
                } else {
                    $arr_permit_type_amount .= round((($record['salary'] + $arr_allowance_amount_total) / $hkw) * $arr_permit_type_amount_qty) . ",";
                    @$arr_permit_type_amount_b_total += round((($record['salary'] + $arr_allowance_amount_total) / $hkw) * $arr_permit_type_amount_qty) . ",";
                }
            }

            $arr_permit_type_number_ex = explode(",", substr($arr_permit_type_number, 0, -1));
            $arr_permit_type_amount_ex = explode(",", substr($arr_permit_type_amount, 0, -1));
            $arr_permit_type_combine = array_combine($arr_permit_type_number_ex, $arr_permit_type_amount_ex);

            $arr_permit_type_number_ex_b = explode(",", substr($arr_permit_type_number_b, 0, -1));
            $arr_permit_type_amount_ex_b = explode(",", substr($arr_permit_type_amount_b, 0, -1));
            $arr_permit_type_combine_b = array_combine($arr_permit_type_number_ex_b, $arr_permit_type_amount_ex_b);
            //-------------------------------------------------------------------------------------------------------------------------------------------------------

            //Menghitung Total
            //Potong gaji jika dia ga masuk kerja
            //Rumus nya Gaji / 30 hari x jumlah dia ga absen
            //$absence_qty = (@count($weekday) - @count($holiday) - $masuk - $arr_total_permit);
            $absence_qty = ($absen - $arr_total_permit - $change_day_qty);

            if ($absence_qty > 0) {
                $absence_qty_final = $absence_qty;
            } else {
                $absence_qty_final = 0;
            }

            //Harga Potong Gaji kalo dia ga masuk kerja
            if ($record['group_name'] == "MAGANG") {
                $absence_amount = round((($record['salary'] - $arr_deduction_amount_total) / $hkw) * $absence_qty_final);
            } elseif ($count_allowance_number > 0) {
                $absence_amount = round(($arr_allowance_amount_number / $hkw) * $absence_qty_final);
            } else {
                $absence_amount = round((($record['salary'] + $arr_allowance_amount_total) / $hkw) * $absence_qty_final);
            }

            //Total Pendapatan Gaji (Gaji + Tunjangan + BPJS dari perusahaan + Koreksi plus + lembur hari biasa + lembur hari libur)
            $total_all_allowance = ($record['salary'] + $arr_allowance_amount_total + $arr_bpjs_com_amount_total + $total_correction_plus + $final_total_ovetime_amount_weekday + $final_total_ovetime_amount_holiday + $final_total_ovetime_amount_correction);

            //Total Potongan Gaji (Ijin/Sakit + Koreksi Minus + alpha)
            $total_all_deduction = ($arr_permit_type_amount_b_total + $total_correction_minus + $absence_amount);
            $income = ($total_all_allowance - $total_all_deduction - $arr_bpjs_com_amount_total);
            $income_pajak = ($record['salary'] + $arr_allowance_amount_total + $final_total_ovetime_amount_weekday + $final_total_ovetime_amount_holiday + $final_total_ovetime_amount_correction);

            //Menghitung PPH Jabatan (Income + JKK + JKM + KES x 5%)
            $pph_position = ((($income_pajak + $arr_bpjs_com_amount_salary_total) * $config[0]->payroll_pph_position) / 100);

            if ($pph_position >= $config[0]->payroll_pph_position_limit) {
                $pph_position_final = $config[0]->payroll_pph_position_limit;
            } else {
                $pph_position_final = $pph_position;
            }

            //Potongan Pajak (Income + JKK + JKM + KES) - (Jabatan + JHT + JP)
            $income_pph = (($income_pajak + $arr_bpjs_com_amount_salary_total) - ($arr_bpjs_com_amount_jabatan_total + $pph_position_final));
            $netto_year = ($income_pph * 12);
            $r_marital = $this->crud->read('maritals', ["number" => $record['marital']]);
            $r_ptkp = $this->crud->read('ptkp', ["marital_id" => @$r_marital->id]);
            $tax_amount = ($netto_year - @$r_ptkp->amount);
            $pph_debt = (($tax_amount * $config[0]->payroll_pph_payable) / 100);
            $pph_pasal = round($pph_debt / 12);

            // die(json_encode(array(
            //     "income" => $income_pph . " (Total Income + BPJS Company - (Jabatan + (Total Income + BPJS Company * 5%)))",
            //     "netto_year" => $netto_year . " (income * 12)",
            //     "ptkp" => $r_ptkp[0]->amount . " (Master PTKP)",
            //     "tax_amount" => $tax_amount . " (netto_year - ptkp)",
            //     "pph_debt" => $pph_debt . " (tax_amount * 5%)",
            //     "hasil_akhir" => $pph_pasal . " (pph_debt / 12)",
            // )));

            //Kalo gaji per tahun nya lebih dari master ptkp
            if ($netto_year >= @$r_ptkp->amount) {
                //jika npwp nya kosong maka kena potongan 120%
                if ($record['tax_id'] == "" or $record['tax_id'] == null) {
                    $pph_final = round(($pph_pasal * $config[0]->payroll_npwp_null) / 100);
                } else {
                    $pph_final = $pph_pasal;
                }
                //kalo kurang ga kena pajak pph
            } else {
                $pph_final = 0;
            }

            //Total keseluruhan
            $netincome = ($income - $pph_final - $arr_deduction_amount_total - $arr_bpjs_emp_amount_total - @$total_coorperative - @$total_bank - @$total_other);
            $roundup = 500; // digit pembulatan, dalam hal ini ratusan
            $roundupIncome = ceil($netincome / $roundup) * $roundup;
            //-------------------------------------------------------------------------------------------------------------------------------------------------------


            $arr = array(
                "employee_id" => $record['id'],
                "number" => $record['number'],
                "name" => $record['name'],
                "period_start" => $period_start,
                "period_end" => $period_end,
                "division_name" => $record['division_name'],
                "departement_name" => $record['departement_name'],
                "departement_sub_name" => $record['departement_sub_name'],
                "contract_name" => $record['contract_name'],
                "position_name" => $record['position_name'],
                "group_name" => $record['group_name'],
                "job_type" => $record['type'],
                "level" => $record['level'],
                "marital" => $record['marital'],
                "tax_id" => $record['tax_id'],
                "shift_name" => $record['shift_name_2'],
                "attandance" => json_encode($arr_permit_combine),
                "attandance_wd" => ($masuk),
                "working_day" => @count($weekday) - $holiday,
                "salary" => $record['salary'],
                "allowence" => json_encode($arr_allowance_combine),
                "bpjs_company" => json_encode($arr_bpjs_com_combine),
                "bpjs_company_total" => $arr_bpjs_com_amount_total,
                "correction_plus" => $total_correction_plus,
                "overtime_weekday" => ($final_total_ovetime_hour_weekday),
                "overtime_convert_weekday" => ($final_total_ovetime_convert_weekday),
                "overtime_amount_weekday" => $final_total_ovetime_amount_weekday,
                "overtime_holiday" => ($final_total_ovetime_hour_holiday),
                "overtime_convert_holiday" => ($final_total_ovetime_convert_holiday),
                "overtime_amount_holiday" => $final_total_ovetime_amount_holiday,
                "overtime_correction" => ($final_total_ovetime_hour_correction),
                "overtime_convert_correction" => ($final_total_ovetime_convert_correction),
                "overtime_amount_correction" => $final_total_ovetime_amount_correction,
                "total_overtime" => ($final_total_ovetime_hour_weekday + $final_total_ovetime_hour_holiday + $final_total_ovetime_hour_correction),
                "total_overtime_convert" => ($final_total_ovetime_convert_weekday + $final_total_ovetime_convert_holiday + $final_total_ovetime_convert_correction),
                "total_overtime_amount" => ($final_total_ovetime_amount_weekday + $final_total_ovetime_amount_holiday + $final_total_ovetime_amount_correction),
                "total_all_allowance" => $total_all_allowance,
                "deduction_number" => json_encode($arr_permit_type_combine_b),
                "deduction_amount" => json_encode($arr_permit_type_combine),
                "deduction_absence" => ($absence_qty_final),
                "deduction_absence_amount" => $absence_amount,
                "correction_minus" => $total_correction_minus,
                "total_all_deduction" => $total_all_deduction,
                "income" => ($income),
                "deduction" => json_encode($arr_deduction_combine),
                "loan_cooperative" => @$total_coorperative,
                "loan_bank" => @$total_bank,
                "loan_other" => @$total_other,
                "bpjs_employee" => json_encode($arr_bpjs_emp_combine),
                "bpjs_employee_total" => $arr_bpjs_emp_amount_total,
                "pph" => ($pph_final),
                "net_income" => ($roundupIncome)
            );

            if (@$arr == null) {
                $result = [];
            } else {
                $result = @$arr;
            }

            $checkPayroll = $this->crud->read("payrolls", [], ["employee_id" => $record['id'], "period_start" => $period_start]);
            if (!empty($checkPayroll->employee_id)) {
                echo json_encode(array("title" => "Duplicate", "message" => "Duplicate Employee", "theme" => "error"));
            } else {
                $send = $this->crud->create('payrolls', $result);
                if ($send) {
                    echo json_encode(array("title" => "Saved", "message" => $record['name'] . " Data has been created", "theme" => "success"));
                } else {
                    echo json_encode(array("title" => "Error", "message" => $record['name'] . " Failed to created", "theme" => "error"));
                }
            }
        } else {
            echo json_encode(array("title" => "Error", "message" => "Cannot Process your request", "theme" => "error"));
        }
    }

    //UPDATE DATA
    public function update()
    {
        $filter_from = $this->input->post('filter_from');
        $period_start = date("Y-m", strtotime($filter_from));
        $this->db->where('period_start', $period_start);
        $send = $this->db->update('payrolls', ["status" => 1]);
        echo $send;
    }

    public function update_unlock()
    {
        $filter_from = $this->input->post('filter_from');
        $period_start = date("Y-m", strtotime($filter_from));
        $this->db->where('period_start', $period_start);
        $send = $this->db->update('payrolls', ["status" => 0]);
        echo $send;
    }

    //DELETE DATA
    public function delete()
    {
        $post = $this->input->post();
        $period_start = date("Y-m", strtotime($post['filter_from']));
        $period_end = date("Y-m", strtotime($post['filter_to']));
        $username = $this->session->username;

        $privileges = $this->crud->reads('privilege_groups', [], ["username" => $username, "status" => "1"]);

        foreach ($privileges as $privilege) {
            $group_id = $privilege->group_id;

            $this->db->select('a.*');
            $this->db->from('payrolls a');
            $this->db->join('employees b', 'a.employee_id = b.id');
            $this->db->where('a.period_start', $period_start);
            $this->db->where('a.period_end', $period_end);
            if ($post['filter_division'] != "") {
                $this->db->where('b.division_id', $post['filter_division']);
            }
            if ($post['filter_departement'] != "") {
                $this->db->where('b.departement_id', $post['filter_departement']);
            }
            if ($post['filter_departement_sub'] != "") {
                $this->db->where('b.departement_sub_id', $post['filter_departement_sub']);
            }
            if ($post['filter_employee'] != "") {
                $this->db->where('b.id', $post['filter_employee']);
            }
            if ($post['filter_employee_type'] != "") {
                $this->db->where('b.contract_id', $post['filter_employee_type']);
            }
            if ($post['filter_group'] != "") {
                $this->db->where('b.group_id', $post['filter_group']);
            } else {
                $this->db->where('b.group_id', $group_id);
            }
            $payrolls = $this->db->get()->result_object();

            foreach ($payrolls as $payroll) {
                $this->db->delete("notifications", ["table_id" => $payroll->id, "table_name" => "payrolls"]);
                $this->db->delete("payrolls", ['id' => $payroll->id]);
            }
        }
    }

    //PRINT & EXCEL DATA
    public function print($option = "")
    {
        if ($option == "excel") {
            $format  = date("Ymd");
            header("Content-type: application/vnd-ms-excel");
            header("Content-Disposition: attachment; filename=payrolls_$format.xls");
        }

        $permit_type = $this->crud->reads('permit_types', [], ['payroll' => 'NON DEDUCTION']);
        $allowance = $this->crud->reads('allowances', [], [], "", "name", "asc");
        $deduction = $this->crud->reads('deductions');
        $permit_type_d = $this->crud->reads('permit_types', [], ['payroll' => 'DEDUCTION']);
        $bpjs = $this->crud->reads('bpjs', ['status' => 0]);

        $filter_from = $this->input->get('filter_from');
        $filter_to = $this->input->get('filter_to');
        $filter_division = $this->input->get('filter_division');
        $filter_departement = $this->input->get('filter_departement');
        $filter_departement_sub = $this->input->get('filter_departement_sub');
        $filter_employee = $this->input->get('filter_employee');
        $filter_employee_type = $this->input->get('filter_employee_type');
        $filter_group = $this->input->get('filter_group');
        $username = $this->session->username;

        $period_start = date("Y-m", strtotime($filter_from));
        $period_end = date("Y-m", strtotime($filter_to));

        $query = $this->db->query("SELECT a.*, b.national_id, d.name as source_name FROM payrolls a
                JOIN employees b on a.employee_id = b.id
                LEFT JOIN sources d on b.source_id = d.id
                JOIN privilege_groups c ON b.group_id = c.group_id and c.username = '$username' and c.status = '1'
                WHERE a.period_start = '$period_start' 
                AND a.period_end = '$period_end'
                AND b.division_id LIKE '%$filter_division%'
                AND b.departement_id LIKE '%$filter_departement%'
                AND b.departement_sub_id LIKE '%$filter_departement_sub%'
                AND a.employee_id LIKE '%$filter_employee%'
                AND b.contract_id LIKE '%$filter_employee_type%'
                AND b.group_id LIKE '%$filter_group%'
                ORDER BY a.name ASC");
        $records = $query->result_array();

        //Config
        $this->db->select('*');
        $this->db->from('config');
        $config = $this->db->get()->row();

        $html = '<html><head><title>Print Data</title></head><style>body {font-family: Arial, Helvetica, sans-serif;}#customers {border-collapse: collapse;width: 100%;font-size: 12px;}#customers td, #customers th {border: 1px solid #ddd;padding: 2px;}#customers tr:nth-child(even){background-color: #f2f2f2;}#customers tr:hover {background-color: #ddd;}#customers th {padding-top: 2px;padding-bottom: 2px;text-align: left;color: black;}</style><body>
                <style> .str{ mso-number-format:\@; } </style>
                <div style="page-break-after:always;"><center>
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
                </center><br><br>
                <center>
                    <h3 style="margin:0;">Generate Payroll</h3>
                    Period : ' . $filter_from . ' to ' . $filter_to . '
                    <br>
                </center>
                <br>
        
        <table id="customers" border="1">
            <tr>
                <th rowspan="2" width="20">No</th>
                <th rowspan="2">Employee ID</th>
                <th rowspan="2">Employee Name</th>
                <th rowspan="2">Division</th>
                <th rowspan="2">Departement</th>
                <th rowspan="2">Departement Sub</th>
                <th rowspan="2">Job Type</th>
                <th rowspan="2">Status</th>
                <th rowspan="2">Source</th>
                <th rowspan="2">Position</th>
                <th rowspan="2">Shift</th>
                <th rowspan="2">Level</th>
                <th rowspan="2">Marital</th>
                <th rowspan="2">Group</th>
                <th rowspan="2">National ID</th>
                <th rowspan="2">NPWP</th>
                <th colspan="' . (count($permit_type) + 1) . '">Attandance</th>
                <th rowspan="2">Working Calendar</th>
                <th rowspan="2">Salary</th>
                <th colspan="' . (count($allowance)) . '">Allowence</th>
                <th colspan="' . (count($bpjs)) . '">BPJS Company</th>
                <th rowspan="2">Total BPJS Company</th>
                <th rowspan="2">Correction Plus</th>
                <th colspan="3">OT (Working Day)</th>
                <th colspan="3">OT (Holiday)</th>
                <th colspan="3">OT (Correction)</th>
                <th colspan="3">Total Overtime</th>
                <th rowspan="2">Bruto Income</th>
                <th colspan="' . (count($permit_type_d) + count($permit_type_d) + 2) . '">Deduction</th>
                <th rowspan="2">Correction Minus</th>
                <th rowspan="2">Total Deduction</th>
                <th rowspan="2">Total BPJS Company</th>
                <th rowspan="2">Income</th>
                <th colspan="' . (count($deduction)) . '">Deduction</th>
                <th colspan="3">LOAN</th>
                <th colspan="' . (count($bpjs)) . '">BPJS Employee</th>
                <th rowspan="2">Total BPJS Employee</th>
                <th rowspan="2">PPH21</th>
                <th rowspan="2">Net Income</th>
            </tr>
            <tr>';
        foreach ($permit_type as $permit_type_data) {
            $html .= '<th>' . $permit_type_data->number . '</th>';
        }
        $html .= '<th>WD</th>';
        foreach ($allowance as $allowance_data) {
            $html .= '<th>' . $allowance_data->number . '</th>';
        }
        foreach ($bpjs as $bpjs_data) {
            $html .= '<th>' . $bpjs_data->number . '</th>';
        }
        $html .= '  <th>Hour</th>
                    <th>Conversion</th>
                    <th>Amount</th>
                    <th>Hour</th>
                    <th>Conversion</th>
                    <th>Amount</th>
                    <th>Hour</th>
                    <th>Conversion</th>
                    <th>Amount</th>
                    <th>Hour</th>
                    <th>Conversion</th>
                    <th>Amount</th>';
        foreach ($permit_type_d as $permit_type_d_data) {
            $html .= '<th>' . $permit_type_d_data->number . '</th>
                              <th>Amount</th>';
        }
        $html .= '<th>Absence</th>
                          <th>Amount</th>';
        foreach ($deduction as $deduction_data) {
            $html .= '<th>' . $deduction_data->number . '</th>';
        }
        $html .= '<th>Koperasi</th>
                          <th>Bank</th>
                          <th>Other</th>';
        foreach ($bpjs as $bpjs_data) {
            $html .= '<th>' . $bpjs_data->number . '</th>';
        }
        $html .= '</tr>';

        $no = 1;
        foreach ($records as $record) {
            $html .= '<tr>
                    <td>' . $no . '</td>
                    <td class="str">' . $record['number'] . '</td>
                    <td>' . $record['name'] . '</td>
                    <td>' . $record['division_name'] . '</td>
                    <td>' . $record['departement_name'] . '</td>
                    <td>' . $record['departement_sub_name'] . '</td>
                    <td>' . $record['job_type'] . '</td>
                    <td>' . $record['contract_name'] . '</td>
                    <td>' . $record['source_name'] . '</td>
                    <td>' . $record['position_name'] . '</td>
                    <td>' . $record['shift_name'] . '</td>
                    <td>' . $record['level'] . '</td>
                    <td>' . $record['marital'] . '</td>
                    <td>' . $record['group_name'] . '</td>
                    <td class="str">' . $record['national_id'] . '</td>
                    <td>' . $record['tax_id'] . '</td>';
            foreach (json_decode($record['attandance'], true) as $attandance => $val_attandance) {
                $html .= '<td>' . $val_attandance . '</td>';
            }
            $html .= '<td>' . $record['attandance_wd'] . '</td>';
            $html .= '<td>' . $record['working_day'] . '</td>';
            $html .= '<td>' . $record['salary'] . '</td>';
            foreach (json_decode($record['allowence'], true) as $allowence => $val_allowence) {
                $html .= '<td>' . $val_allowence . '</td>';
            }
            foreach (json_decode($record['bpjs_company'], true) as $bpjs_company => $val_bpjs_company) {
                $html .= '<td>' . $val_bpjs_company . '</td>';
            }
            $html .= '<td>' . $record['bpjs_company_total'] . '</td>';
            $html .= '<td>' . $record['correction_plus'] . '</td>';
            $html .= '<td>' . $record['overtime_weekday'] . '</td>';
            $html .= '<td>' . $record['overtime_convert_weekday'] . '</td>';
            $html .= '<td>' . $record['overtime_amount_weekday'] . '</td>';
            $html .= '<td>' . $record['overtime_holiday'] . '</td>';
            $html .= '<td>' . $record['overtime_convert_holiday'] . '</td>';
            $html .= '<td>' . $record['overtime_amount_holiday'] . '</td>';
            $html .= '<td>' . $record['overtime_correction'] . '</td>';
            $html .= '<td>' . $record['overtime_convert_correction'] . '</td>';
            $html .= '<td>' . $record['overtime_amount_correction'] . '</td>';
            $html .= '<td>' . $record['total_overtime'] . '</td>';
            $html .= '<td>' . $record['total_overtime_convert'] . '</td>';
            $html .= '<td>' . $record['total_overtime_amount'] . '</td>';
            $html .= '<td>' . $record['total_all_allowance'] . '</td>';
            $deduction_amount = json_decode($record['deduction_amount'], true);
            $deduction_amount_data = array_values($deduction_amount);
            $index = 0;
            foreach (json_decode($record['deduction_number'], true) as $deduction_number => $val_deduction_number) {
                $html .= '<td>' . $val_deduction_number . '</td>';
                $html .= '<td>' . $deduction_amount_data[$index] . '</td>';
                $index++;
            }
            $html .= '<td>' . $record['deduction_absence'] . '</td>';
            $html .= '<td>' . $record['deduction_absence_amount'] . '</td>';
            $html .= '<td>' . $record['correction_minus'] . '</td>';
            $html .= '<td>' . $record['total_all_deduction'] . '</td>';
            $html .= '<td>' . $record['bpjs_company_total'] . '</td>';
            $html .= '<td>' . $record['income'] . '</td>';
            foreach (json_decode($record['deduction'], true) as $deduction => $val_deduction) {
                $html .= '<td>' . $val_deduction . '</td>';
            }
            $html .= '<td>' . $record['loan_cooperative'] . '</td>';
            $html .= '<td>' . $record['loan_bank'] . '</td>';
            $html .= '<td>' . $record['loan_other'] . '</td>';
            foreach (json_decode($record['bpjs_employee'], true) as $bpjs_employee => $val_bpjs_employee) {
                $html .= '<td>' . $val_bpjs_employee . '</td>';
            }
            $html .= '<td>' . $record['bpjs_employee_total'] . '</td>';
            $html .= '<td>' . $record['pph'] . '</td>';
            $html .= '<td>' . $record['net_income'] . '</td>';
            $no++;
        }

        $html .= '</table></body></html>';
        echo $html;
    }
}
