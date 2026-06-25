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
            $data['bpjs'] = $this->crud->reads('bpjs', ['status' => 0], [], "", "flag", "asc");

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

        if (empty($readPayroll->approved_to)) {
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

            $this->db->select('a.*, b.national_id, d.name as source_name, e.ter as ter_persentase');
            $this->db->from('payrolls a');
            $this->db->join('employees b', 'a.employee_id = b.id');
            $this->db->join('sources d', 'b.source_id = d.id', 'left');
            $this->db->join('marital_categories e', 'a.ter_number = e.number', 'left');
            $this->db->join('privilege_groups c', "(b.group_id = c.group_id or b.group_id is null or b.group_id = '') and c.username = '$username' and c.status = '1'");
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
            $this->db->group_by('a.employee_id');
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
                    "ter_number" => $record['ter_number'],
                    "ter_persentase" => $record['ter_persentase'],
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
                    "ter" => $record['ter'],
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
                    i.calculate as group_calculate,
                    n.name as shift_name,
                    l.name as shift_name_2,
                    l.days,
                    p.amount as salary,
                    p.bpjs,
                    q.number as marital,
                    q.ter_type,
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
                JOIN privilege_groups m ON (i.id = m.group_id or m.group_id is null or m.group_id = '') and m.username = '$username' and m.status = '1'
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

            //ambil data dari variabel filter
            $filter_from = $this->input->get('filter_from');
            $filter_to = $this->input->get('filter_to');

            //Ubah format tanggal menjadi tahun dan bulan
            $period_start = date("Y-m", strtotime($filter_from));
            $period_end = date("Y-m", strtotime($filter_to));

            //Convert tanggal ke time supaya hitungan tanggal nya mudah
            $start = strtotime($filter_from);
            $finish = strtotime($filter_to);

            //Looping tanggal awal sampai tanggal akhir
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

            if ($record['days'] == "6") {
                $weekend_day = $weekend2;
                $weekday_day = $weekday2;
            } else {
                $weekend_day = $weekend;
                $weekday_day = $weekday;
            }

            //Setting Payroll
            $config = $this->db->get('payroll_config')->result();

            //Tanggal merah di master calendar
            $this->db->select('trans_date');
            $this->db->from('calendars');
            $this->db->where('trans_date >=', $filter_from);
            $this->db->where('trans_date <=', $filter_to);
            if(count($weekend_day) > 0){
                $this->db->where_not_in('trans_date', $weekend_day);
            }
            $calendar = $this->db->get()->result_array();
            $calendar_amount = empty($calendar) ? 0 : count($calendar);

            $w_calendars = array();
            foreach ($calendar as $cal) {
                $w_calendars[] = $cal['trans_date'];
            }

            //Permit yang non deduction atau tidak potong gaji
            $permit_date = "'" . implode("', '", $weekend_day) . "'";
            $calendar_date = "'" . implode("', '", $w_calendars) . "'";
            $q_permit = $this->db->query("SELECT b.number, b.name, COUNT(a.permit_date) as amount
                    FROM permit_types b
                    LEFT JOIN permits a ON a.permit_type_id = b.id and a.employee_id = '$record[id]' and a.permit_date >= '$filter_from' and a.permit_date <= '$filter_to' and a.permit_date not in ($permit_date)
                    WHERE b.payroll = 'NON DEDUCTION' and (a.approved_to = '' or a.approved_to is null)
                    GROUP BY b.id");
            $r_permit = $q_permit->result_array();
            $arr_permit_number = "";
            $arr_permit_amount = "";
            $arr_total_permit = 0;
            foreach ($r_permit as $permit_data) {
                $arr_permit_number .= strtolower($permit_data['number']) . "a,";
                $arr_permit_amount .= $permit_data['amount'] . ",";
                $arr_total_permit += $permit_data['amount'];
            }

            $arr_permit_number_ex = explode(",", substr($arr_permit_number, 0, -1));
            $arr_permit_amount_ex = explode(",", substr($arr_permit_amount, 0, -1));
            $arr_permit_combine = array_combine($arr_permit_number_ex, $arr_permit_amount_ex);

            //Permit yang deduction atau potong gaji
            $q_permit_deduction = $this->db->query("SELECT b.number, b.name, COUNT(a.permit_date) as amount
                    FROM permit_types b
                    LEFT JOIN permits a ON a.permit_type_id = b.id and a.employee_id = '$record[id]' and a.permit_date >= '$filter_from' and a.permit_date <= '$filter_to' and a.permit_date not in ($permit_date) and a.permit_date not in ($calendar_date)
                    WHERE b.payroll = 'DEDUCTION' and (a.approved_to = '' or a.approved_to is null)
                    GROUP BY b.id");

            $r_permit_deduction = $q_permit_deduction->result_array();
            $arr_total_permit_deduction = 0;
            foreach ($r_permit_deduction as $permit_data_deduction) {
                $arr_total_permit_deduction += $permit_data_deduction['amount'];
            }

            //Permit atau absen nya YES di anggap masuk kerja
            $q_permit_absence = $this->db->query("SELECT a.permit_date, COUNT(a.permit_date) as amount
                    FROM permit_types b
                    LEFT JOIN permits a ON a.permit_type_id = b.id and a.employee_id = '$record[id]' and a.permit_date >= '$filter_from' and a.permit_date <= '$filter_to' and a.permit_date not in ($permit_date) and a.permit_date not in ($calendar_date)
                    WHERE b.absence = 'YES' and (a.approved_to = '' or a.approved_to is null)
                    GROUP BY a.permit_date");

            $r_permit_absence = $q_permit_absence->result_array();
            $arr_total_permit_absence = 0;
            $permit_yes = array();
            foreach ($r_permit_absence as $permit_data_absence) {
                if(!empty($permit_data_absence['permit_date'])){
                    $permit_yes[] = $permit_data_absence['permit_date'];
                }
                $arr_total_permit_absence += $permit_data_absence['amount'];
            }

            //Permit atau absen nya NO di anggap tidak kerja
            $q_permit_absence_no = $this->db->query("SELECT b.number, b.name, COUNT(a.permit_date) as amount
                    FROM permit_types b
                    LEFT JOIN permits a ON a.permit_type_id = b.id and a.employee_id = '$record[id]' and a.permit_date >= '$filter_from' and a.permit_date <= '$filter_to' and a.permit_date not in ($permit_date) and a.permit_date not in ($calendar_date)
                    WHERE b.absence = 'NO' and (a.approved_to = '' or a.approved_to is null)
                    GROUP BY b.id");

            $r_permit_absence_no = $q_permit_absence_no->result_array();
            $arr_total_permit_absence_no = 0;
            foreach ($r_permit_absence_no as $permit_data_absence_no) {
                $arr_total_permit_absence_no += $permit_data_absence_no['amount'];
            }

            //Change Days
            $this->db->select("COUNT(*) as days");
            $this->db->from('change_days');
            $this->db->where('employee_id', $record['id']);
            $this->db->where("(start between '$filter_from' and '$filter_to')");
            if (count($weekend_day) > 0) {
                $this->db->where_not_in('start', $weekend_day);
            }
            if (count($w_calendars) > 0) {
                $this->db->where_not_in('start', $w_calendars);
            }
            $changeDays = $this->db->get()->row();
            $changeDays_amount = empty($changeDays->days) ? 0 : $changeDays->days;

            //Correction PLUS
            $this->db->select("SUM(amount) as amount");
            $this->db->from('corrections');
            $this->db->where('employee_id', $record['id']);
            $this->db->where('trans_date >=', $filter_from);
            $this->db->where('trans_date <=', $filter_to);
            $this->db->where('correction_type', 'PLUS');
            $correction_plus = $this->db->get()->row();
            $correction_plus_amount = empty($correction_plus->amount) ? 0 : $correction_plus->amount;

            //Correction MINUS
            $this->db->select("SUM(amount) as amount");
            $this->db->from('corrections');
            $this->db->where('employee_id', $record['id']);
            $this->db->where('trans_date >=', $filter_from);
            $this->db->where('trans_date <=', $filter_to);
            $this->db->where('correction_type', 'MINUS');
            $correction_minus = $this->db->get()->row();
            $correction_minus_amount = empty($correction_minus->amount) ? 0 : $correction_minus->amount;

            //Loan Bank
            $this->db->select("SUM(amount) as amount");
            $this->db->from('loans');
            $this->db->where('employee_id', $record['id']);
            $this->db->where('trans_date >=', $filter_from);
            $this->db->where('trans_date <=', $filter_to);
            $this->db->where('loan_type', 'BANK');
            $loan_bank = $this->db->get()->row();
            $loan_bank_amount = empty($loan_bank->amount) ? 0 : $loan_bank->amount;

            //Loan Cooperative
            $this->db->select("SUM(amount) as amount");
            $this->db->from('loans');
            $this->db->where('employee_id', $record['id']);
            $this->db->where('trans_date >=', $filter_from);
            $this->db->where('trans_date <=', $filter_to);
            $this->db->where('loan_type', 'COOPERATIVE');
            $loan_cooperative = $this->db->get()->row();
            $loan_cooperative_amount = empty($loan_cooperative->amount) ? 0 : $loan_cooperative->amount;

            //Loan Other
            $this->db->select("SUM(amount) as amount");
            $this->db->from('loans');
            $this->db->where('employee_id', $record['id']);
            $this->db->where('trans_date >=', $filter_from);
            $this->db->where('trans_date <=', $filter_to);
            $this->db->where('loan_type', 'OTHER');
            $loan_other = $this->db->get()->row();
            $loan_other_amount = empty($loan_other->amount) ? 0 : $loan_other->amount;


            $tomorow = date('Y-m-d', strtotime("+1 day", strtotime($filter_from)));
            //Attandances
            $this->db->select("COUNT(a.date_in) as amount");
            $this->db->from('(SELECT DISTINCT number, date_in FROM attandances) a');
            $this->db->where('a.number', $record['number']);
            $this->db->where("(a.date_in >= '$filter_from' and a.date_in <= '$filter_to')");
            if (count($weekend_day) > 0) {
                $this->db->where_not_in('a.date_in', $weekend_day);
            }
            if (count($w_calendars) > 0) {
                $this->db->where_not_in('a.date_in', $w_calendars);
            }
            if ($arr_total_permit_absence > 0){
                $this->db->where_not_in('a.date_in', $permit_yes);
            }
            $attandance = $this->db->get()->row();
            $attandance_amount = empty($attandance->amount) ? 0 : $attandance->amount;

            $employee_id = $record['id'];
            //cash carries
            $cash_carries = $this->crud->query("SELECT
                a.employee_id,
                a.trans_date,
                a.duration_hour,
                a.meal,
                COALESCE(h.weekday, 0) as total_weekday,
                COALESCE(h.sunday, 0) as total_sunday,
                COALESCE(h.saturday, 0) as total_saturday,
                COALESCE(h.holiday, 0) as total_holiday,
                COALESCE(h.meal, 0) as total_meal,
                f.days 
            FROM cash_carries a 
            JOIN employees b ON a.employee_id = b.id
            JOIN shift_employees d ON a.employee_id = d.employee_id
            JOIN shifts e ON e.id = d.shift_id
            JOIN shift_details f ON e.id = f.shift_id
            JOIN setup_cash_carries g ON a.employee_id = g.employee_id
            JOIN allowance_cash_carries h ON g.allowance_id = h.id
            WHERE a.employee_id = '$employee_id' and a.trans_date between '$filter_from' and '$filter_to'
            GROUP BY a.employee_id, a.trans_date");

            $overtime_weekday = 0;
            $overtime_convert_weekday = 0;
            $overtime_amount_weekday = 0;
            $overtime_holiday = 0;
            $overtime_convert_holiday = 0;
            $overtime_amount_holiday = 0;
            foreach ($cash_carries as $cash_carry) {
                $this->db->select('trans_date');
                $this->db->from('calendars');
                $this->db->where('trans_date', $cash_carry->trans_date);
                $calendars = $this->db->get()->result_array();

                $this->db->select("*, SUM(duration_hour) as total_duration");
                $this->db->from('cash_carries');
                $this->db->where('employee_id', $cash_carry->employee_id);
                $this->db->where('trans_date', $cash_carry->trans_date);
                $this->db->group_by('employee_id');
                $this->db->group_by('trans_date');
                $cash_carry2 = $this->db->get()->row();

                $start = strtotime($cash_carry->trans_date);
                $hour = $cash_carry2->total_duration;

                //Validasi Uang makan
                if ($cash_carry->meal == 0 or @$cash_carry->time_in == "") {
                    $meal = 0;
                } else {
                    $meal = @$cash_carry->total_meal;
                }

                if (@$cash_carry->days == "5") {
                    if (date('w', $start) !== '0' && date('w', $start) !== '6') {

                        //Kalo ada tanggal Merah
                        if (count($calendars) > 0) {
                            $overtime_holiday += 1;
                            $overtime_convert_holiday += $hour;
                            $overtime_amount_holiday += ((@$cash_carry->total_holiday * $hour) + $meal);
                        } else {
                            $overtime_weekday += 1;
                            $overtime_convert_weekday += $hour;
                            $overtime_amount_weekday += ((@$cash_carry->total_weekday * $hour) + $meal);
                        }
                    } else {
                        if (date('w', $start) === '0') {
                            $overtime_holiday += 1;
                            $overtime_convert_holiday += $hour;
                            $overtime_amount_holiday += ((@$cash_carry->total_sunday * $hour) + $meal);
                        } else {
                            $overtime_holiday += 1;
                            $overtime_convert_holiday += $hour;
                            $overtime_amount_holiday += ((@$cash_carry->total_saturday * $hour) + $meal);
                        }
                    }
                } else {
                    if (date('w', $start) !== '0') {

                        //Kalo ada tanggal Merah
                        if (count($calendars) > 0) {
                            $overtime_holiday += 1;
                            $overtime_convert_holiday += $hour;
                            $overtime_amount_holiday += ((@$cash_carry->total_holiday * $hour) + $meal);
                        } else {
                            $overtime_weekday += 1;
                            $overtime_convert_weekday += $hour;
                            $overtime_amount_weekday += ((@$cash_carry->total_weekday * $hour) + $meal);
                        }
                    } else {
                        if (date('w', $start) === '0') {
                            $overtime_holiday += 1;
                            $overtime_convert_holiday += $hour;
                            $overtime_amount_holiday += ((@$cash_carry->total_sunday * $hour) + $meal);
                        } else {
                            $overtime_holiday += 1;
                            $overtime_convert_holiday += $hour;
                            $overtime_amount_holiday += ((@$cash_carry->total_saturday * $hour) + $meal);
                        }
                    }
                }
            }

            $total_overtime = ($overtime_weekday + $overtime_holiday);
            $total_overtime_convert = ($overtime_convert_weekday + $overtime_convert_holiday);
            $total_overtime_amount = ($overtime_amount_weekday + $overtime_amount_holiday);

            $hkw = (@count($weekday_day) - @$calendar_amount);
            $absen = (@count($weekday_day) - @$calendar_amount - @$attandance_amount - @$changeDays_amount - @$arr_total_permit_absence_no - $arr_total_permit_absence);

            //Hitung Hari dia masuk kerja
            $working_days = ($attandance_amount + $arr_total_permit_absence + @$changeDays_amount);

            //Jika hari kerja nya lebih besar dari HKW maka nilai ambil HKW
            if ($working_days >= $hkw) {
                $wd = $hkw;
            } else {
                $wd = $working_days;
            }

            //Total Masuk kerja
            $masuk = @$wd;

            //Allowances / Tunjangan kerja
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

            //Deduction atau Potongan Gaji
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
            //Kalo dia mempunyai BPJS potong gaji BPJS employee
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
                            $arr_bpjs_emp_amount .= round((($record['salary'] + $arr_allowance_amount_total_bpjs) * ($bpjs_emp_data->employee / 100))) . ",";
                            $arr_bpjs_emp_amount_total += round((($record['salary'] + $arr_allowance_amount_total_bpjs) * ($bpjs_emp_data->employee / 100)));
                        } else {
                            $arr_bpjs_emp_amount .= round(($record['bpjs'] * ($bpjs_emp_data->employee / 100))) . ",";
                            $arr_bpjs_emp_amount_total += round(($record['bpjs'] * ($bpjs_emp_data->employee / 100)));
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
                        $arr_bpjs_emp_amount .= round((($record['salary'] + $arr_allowance_amount_total_bpjs) * ($bpjs_emp_data->employee / 100))) . ",";
                        $arr_bpjs_emp_amount_total += round((($record['salary'] + $arr_allowance_amount_total_bpjs) * ($bpjs_emp_data->employee / 100)));
                    }
                }
            }

            $arr_bpjs_emp_number_ex = explode(",", substr($arr_bpjs_emp_number, 0, -1));
            $arr_bpjs_emp_amount_ex = explode(",", substr($arr_bpjs_emp_amount, 0, -1));
            $arr_bpjs_emp_combine = array_combine($arr_bpjs_emp_number_ex, $arr_bpjs_emp_amount_ex);
            //-------------------------------------------------------------------------------------------------------------------------------------------------------

            //BPJS Company atau  potongan gaji dari perusahaan
            $r_bpjs_com = $this->crud->reads('bpjs', ['status' => 0]);
            $arr_bpjs_com_number = "";
            $arr_bpjs_com_amount = "";
            $arr_bpjs_com_amount_total = 0;
            $arr_bpjs_com_amount_total_ter = 0;
            $arr_bpjs_com_amount_salary_total = 0;
            $arr_bpjs_com_amount_jabatan_total = 0;
            foreach ($r_bpjs_com as $bpjs_com_data) {
                $arr_bpjs_com_number .= strtolower($bpjs_com_data->number) . "_company,";

                if ($record['jamsostek'] == "" && $record['jkn'] == "") {
                    $arr_bpjs_com_amount .= 0 . ",";
                    $arr_bpjs_com_amount_total += 0;
                    $arr_bpjs_com_amount_total_ter += 0;
                    $arr_bpjs_com_amount_salary_total += 0;
                    $arr_bpjs_com_amount_jabatan_total += 0;
                } elseif ($record['jamsostek'] == "" && $record['jkn'] != "") {
                    if ($bpjs_com_data->number == "BPJS") {
                        if ($record['bpjs'] == "0") {
                            $arr_bpjs_com_amount .= round((($record['salary'] + $arr_allowance_amount_total_bpjs) * ($bpjs_com_data->company / 100)) * $record['jkn_family']) . ",";
                            $arr_bpjs_com_amount_total += round((($record['salary'] + $arr_allowance_amount_total_bpjs) * ($bpjs_com_data->company / 100)) * $record['jkn_family']);
                            $arr_bpjs_com_amount_total_ter += round((($record['salary'] + $arr_allowance_amount_total_bpjs) * ($bpjs_com_data->company / 100)) * $record['jkn_family']);
                            $arr_bpjs_com_amount_salary_total += round((($record['salary'] + $arr_allowance_amount_total_bpjs) * ($bpjs_com_data->company / 100)) * $record['jkn_family']);
                            $arr_bpjs_com_amount_jabatan_total += 0;
                        } else {
                            $arr_bpjs_com_amount .= round((($record['bpjs']) * ($bpjs_com_data->company / 100))) . ",";
                            $arr_bpjs_com_amount_total += round((($record['bpjs']) * ($bpjs_com_data->company / 100)));
                            $arr_bpjs_com_amount_total_ter += round((($record['bpjs']) * ($bpjs_com_data->company / 100)));
                            $arr_bpjs_com_amount_salary_total += round((($record['bpjs']) * ($bpjs_com_data->company / 100)));
                            $arr_bpjs_com_amount_jabatan_total += 0;
                        }
                    } else {
                        $arr_bpjs_com_amount .= 0 . ",";
                        $arr_bpjs_com_amount_total += 0;
                        $arr_bpjs_com_amount_total_ter += 0;
                        $arr_bpjs_com_amount_salary_total += 0;
                        $arr_bpjs_com_amount_jabatan_total += 0;
                    }
                } elseif ($record['jamsostek'] != "" && $record['jkn'] == "") {
                    if ($bpjs_com_data->number != "BPJS") {
                        if ($record['bpjs'] == "0") {
                            $arr_bpjs_com_amount .= round((($record['salary'] + $arr_allowance_amount_total_bpjs) * ($bpjs_com_data->company / 100))) . ",";
                            $arr_bpjs_com_amount_total += round((($record['salary'] + $arr_allowance_amount_total_bpjs) * ($bpjs_com_data->company / 100)));

                            //Perhitungan PPH Salary - Jabatan
                            if ($bpjs_com_data->number == "JKK") {
                                $arr_bpjs_com_amount_total_ter += round((($record['salary'] + $arr_allowance_amount_total_bpjs) * ($bpjs_com_data->company / 100)));
                                $arr_bpjs_com_amount_salary_total += round((($record['salary'] + $arr_allowance_amount_total_bpjs) * ($bpjs_com_data->company / 100)));
                                $arr_bpjs_com_amount_jabatan_total += 0;
                            } elseif ($bpjs_com_data->number == "JKM") {
                                $arr_bpjs_com_amount_total_ter += round((($record['salary'] + $arr_allowance_amount_total_bpjs) * ($bpjs_com_data->company / 100)));
                                $arr_bpjs_com_amount_salary_total += round((($record['salary'] + $arr_allowance_amount_total_bpjs) * ($bpjs_com_data->company / 100)));
                                $arr_bpjs_com_amount_jabatan_total += 0;
                            } elseif ($bpjs_com_data->number == "JHT") {
                                $arr_bpjs_com_amount_total_ter += 0;
                                $arr_bpjs_com_amount_salary_total += 0;
                                $arr_bpjs_com_amount_jabatan_total += round((($record['salary'] + $arr_allowance_amount_total_bpjs) * ($bpjs_com_data->company / 100)));
                            } elseif ($bpjs_com_data->number == "JP") {
                                $arr_bpjs_com_amount_total_ter += 0;
                                $arr_bpjs_com_amount_salary_total += 0;
                                $arr_bpjs_com_amount_jabatan_total += round((($record['salary'] + $arr_allowance_amount_total_bpjs) * ($bpjs_com_data->company / 100)));
                            } else {
                                $arr_bpjs_com_amount_total_ter += round((($record['salary'] + $arr_allowance_amount_total_bpjs) * ($bpjs_com_data->company / 100)));
                                $arr_bpjs_com_amount_salary_total += 0;
                                $arr_bpjs_com_amount_jabatan_total += 0;
                            }
                        } else {
                            $arr_bpjs_com_amount .= round((($record['bpjs']) * ($bpjs_com_data->company / 100))) . ",";
                            $arr_bpjs_com_amount_total += round((($record['bpjs']) * ($bpjs_com_data->company / 100)));

                            //Perhitungan PPH Salary - Jabatan
                            if ($bpjs_com_data->number == "JKK") {
                                $arr_bpjs_com_amount_total_ter += round((($record['salary'] + $arr_allowance_amount_total_bpjs) * ($bpjs_com_data->company / 100)));
                                $arr_bpjs_com_amount_salary_total += round((($record['bpjs']) * ($bpjs_com_data->company / 100)));
                                $arr_bpjs_com_amount_jabatan_total += 0;
                            } elseif ($bpjs_com_data->number == "JKM") {
                                $arr_bpjs_com_amount_total_ter += round((($record['salary'] + $arr_allowance_amount_total_bpjs) * ($bpjs_com_data->company / 100)));
                                $arr_bpjs_com_amount_salary_total += round((($record['bpjs']) * ($bpjs_com_data->company / 100)));
                                $arr_bpjs_com_amount_jabatan_total += 0;
                            } elseif ($bpjs_com_data->number == "JHT") {
                                $arr_bpjs_com_amount_total_ter += 0;
                                $arr_bpjs_com_amount_salary_total += 0;
                                $arr_bpjs_com_amount_jabatan_total += round((($record['bpjs']) * ($bpjs_com_data->company / 100)));
                            } elseif ($bpjs_com_data->number == "JP") {
                                $arr_bpjs_com_amount_total_ter += 0;
                                $arr_bpjs_com_amount_salary_total += 0;
                                $arr_bpjs_com_amount_jabatan_total += round((($record['bpjs']) * ($bpjs_com_data->company / 100)));
                            } else {
                                $arr_bpjs_com_amount_total_ter += round((($record['salary'] + $arr_allowance_amount_total_bpjs) * ($bpjs_com_data->company / 100)));
                                $arr_bpjs_com_amount_salary_total += 0;
                                $arr_bpjs_com_amount_jabatan_total += 0;
                            }
                        }
                    } else {
                        $arr_bpjs_com_amount .= 0 . ",";
                        $arr_bpjs_com_amount_total += 0;
                        $arr_bpjs_com_amount_total_ter += 0;
                        $arr_bpjs_com_amount_salary_total += 0;
                        $arr_bpjs_com_amount_jabatan_total += 0;
                    }
                } else {
                    if ($record['bpjs'] == "0") {
                        $arr_bpjs_com_amount .= round((($record['salary'] + $arr_allowance_amount_total_bpjs) * ($bpjs_com_data->company / 100))) . ",";
                        $arr_bpjs_com_amount_total += round((($record['salary'] + $arr_allowance_amount_total_bpjs) * ($bpjs_com_data->company / 100)));

                        //Perhitungan PPH Salary - Jabatan
                        if ($bpjs_com_data->number == "JKK") {
                            $arr_bpjs_com_amount_total_ter += round((($record['salary'] + $arr_allowance_amount_total_bpjs) * ($bpjs_com_data->company / 100)));
                            $arr_bpjs_com_amount_salary_total += round((($record['salary'] + $arr_allowance_amount_total_bpjs) * ($bpjs_com_data->company / 100)));
                            $arr_bpjs_com_amount_jabatan_total += 0;
                        } elseif ($bpjs_com_data->number == "JKM") {
                            $arr_bpjs_com_amount_total_ter += round((($record['salary'] + $arr_allowance_amount_total_bpjs) * ($bpjs_com_data->company / 100)));
                            $arr_bpjs_com_amount_salary_total += round((($record['salary'] + $arr_allowance_amount_total_bpjs) * ($bpjs_com_data->company / 100)));
                            $arr_bpjs_com_amount_jabatan_total += 0;
                        } elseif ($bpjs_com_data->number == "JHT") {
                            $arr_bpjs_com_amount_total_ter += 0;
                            $arr_bpjs_com_amount_salary_total += 0;
                            $arr_bpjs_com_amount_jabatan_total += round((($record['salary'] + $arr_allowance_amount_total_bpjs) * ($bpjs_com_data->company / 100)));
                        } elseif ($bpjs_com_data->number == "JP") {
                            $arr_bpjs_com_amount_total_ter += 0;
                            $arr_bpjs_com_amount_salary_total += 0;
                            $arr_bpjs_com_amount_jabatan_total += round((($record['salary'] + $arr_allowance_amount_total_bpjs) * ($bpjs_com_data->company / 100)));
                        } else {
                            $arr_bpjs_com_amount_total_ter += round((($record['salary'] + $arr_allowance_amount_total_bpjs) * ($bpjs_com_data->company / 100)));
                            $arr_bpjs_com_amount_salary_total += 0;
                            $arr_bpjs_com_amount_jabatan_total += 0;
                        }
                    } else {
                        $arr_bpjs_com_amount .= round((($record['bpjs']) * ($bpjs_com_data->company / 100))) . ",";
                        $arr_bpjs_com_amount_total += round((($record['bpjs']) * ($bpjs_com_data->company / 100)));

                        //Perhitungan PPH Salary - Jabatan
                        if ($bpjs_com_data->number == "JKK") {
                            $arr_bpjs_com_amount_total_ter += round((($record['bpjs']) * ($bpjs_com_data->company / 100)));
                            $arr_bpjs_com_amount_salary_total += round((($record['bpjs']) * ($bpjs_com_data->company / 100)));
                            $arr_bpjs_com_amount_jabatan_total += 0;
                        } elseif ($bpjs_com_data->number == "JKM") {
                            $arr_bpjs_com_amount_total_ter += round((($record['bpjs']) * ($bpjs_com_data->company / 100)));
                            $arr_bpjs_com_amount_salary_total += round((($record['bpjs']) * ($bpjs_com_data->company / 100)));
                            $arr_bpjs_com_amount_jabatan_total += 0;
                        } elseif ($bpjs_com_data->number == "JHT") {
                            $arr_bpjs_com_amount_total_ter += 0;
                            $arr_bpjs_com_amount_salary_total += 0;
                            $arr_bpjs_com_amount_jabatan_total += round((($record['bpjs']) * ($bpjs_com_data->company / 100)));
                        } elseif ($bpjs_com_data->number == "JP") {
                            $arr_bpjs_com_amount_total_ter += 0;
                            $arr_bpjs_com_amount_salary_total += 0;
                            $arr_bpjs_com_amount_jabatan_total += round((($record['bpjs']) * ($bpjs_com_data->company / 100)));
                        } else {
                            $arr_bpjs_com_amount_total_ter += round((($record['bpjs']) * ($bpjs_com_data->company / 100)));
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
                    LEFT JOIN permits a ON a.permit_type_id = b.id and a.employee_id = '$record[id]' and a.permit_date >= '$filter_from' and a.permit_date <= '$filter_to' and a.permit_date not in ($permit_date) and a.permit_date not in ($calendar_date)
                    WHERE b.payroll = 'DEDUCTION' and (a.approved_to = '' or a.approved_to is null)
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
            $absence_qty = $absen;

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
            $total_all_allowance = ($record['salary'] + $arr_allowance_amount_total + $arr_bpjs_com_amount_total + @$correction_plus_amount + $total_overtime_amount);
            $total_all_allowance_ter = ($record['salary'] + $arr_allowance_amount_total + $arr_bpjs_com_amount_total_ter + @$correction_plus_amount + $total_overtime_amount);

            //Total Potongan Gaji (Ijin/Sakit + Koreksi Minus + alpha)
            $total_all_deduction = ($arr_permit_type_amount_b_total + @$correction_minus_amount + $absence_amount);
            $income = ($total_all_allowance - $total_all_deduction - $arr_bpjs_com_amount_total);
            $income_pajak = ($record['salary'] + $arr_allowance_amount_total);

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
            
            //Rumus TER
            $this->db->select("number, ter");
            $this->db->from('marital_categories');
            $this->db->where('ter_from <', $total_all_allowance_ter);
            $this->db->where('ter_to >', $total_all_allowance_ter);
            $this->db->where('type', $record['ter_type']);
            $marital_category = $this->db->get()->row();
            

            if(empty($marital_category)){
                $ter_number = "-";
                $ter = 0;
            }else{
                $ter_number = $marital_category->number;
                $ter = (($total_all_allowance_ter * $marital_category->ter) / 100);
            }

            //Total keseluruhan
            $netincome = ($income - $ter - $arr_deduction_amount_total - $arr_bpjs_emp_amount_total - @$loan_cooperative->amount - @$loan_bank->amount - @$loan_other->amount);
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
                "ter_number" => $ter_number,
                "tax_id" => $record['tax_id'],
                "shift_name" => $record['shift_name_2'],
                "attandance" => json_encode($arr_permit_combine),
                "attandance_wd" => ($masuk),
                "working_day" => $hkw,
                "salary" => $record['salary'],
                "allowence" => json_encode($arr_allowance_combine),
                "bpjs_company" => json_encode($arr_bpjs_com_combine),
                "bpjs_company_total" => $arr_bpjs_com_amount_total,
                "correction_plus" => @$correction_plus_amount,
                "overtime_weekday" => $overtime_weekday,
                "overtime_convert_weekday" => $overtime_convert_weekday,
                "overtime_amount_weekday" => $overtime_amount_weekday,
                "overtime_holiday" => $overtime_holiday,
                "overtime_convert_holiday" => $overtime_convert_holiday,
                "overtime_amount_holiday" => $overtime_amount_holiday,
                "overtime_correction" => 0,
                "overtime_convert_correction" => 0,
                "overtime_amount_correction" => 0,
                "total_overtime" => $total_overtime,
                "total_overtime_convert" => $total_overtime_convert,
                "total_overtime_amount" => $total_overtime_amount,
                "total_all_allowance" => $total_all_allowance,
                "deduction_number" => json_encode($arr_permit_type_combine_b),
                "deduction_amount" => json_encode($arr_permit_type_combine),
                "deduction_absence" => ($absence_qty_final),
                "deduction_absence_amount" => $absence_amount,
                "correction_minus" => @$correction_minus_amount,
                "total_all_deduction" => $total_all_deduction,
                "income" => ($income),
                "deduction" => json_encode($arr_deduction_combine),
                "loan_cooperative" => @$loan_cooperative_amount,
                "loan_bank" => @$loan_bank_amount,
                "loan_other" => @$loan_other_amount,
                "bpjs_employee" => json_encode($arr_bpjs_emp_combine),
                "bpjs_employee_total" => $arr_bpjs_emp_amount_total,
                "pph" => ($pph_final),
                "ter" => $ter,
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

        $query = $this->db->query("SELECT a.*, b.national_id, d.name as source_name, e.ter as ter_persentase FROM payrolls a
                JOIN employees b on a.employee_id = b.id
                LEFT JOIN sources d on b.source_id = d.id
                LEFT JOIN marital_categories e ON a.ter_number = e.number
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
                <th rowspan="2">TER Code</th>
                <th rowspan="2">TER %</th>
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
                <th colspan="3">Overtime (Weekday)</th>
                <th colspan="3">Overtime (Weekend)</th>
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
                <th rowspan="2">TER</th>
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
        $html .= '  <th>Day</th>
                    <th>Hour</th>
                    <th>Amount</th>
                    <th>Day</th>
                    <th>Hour</th>
                    <th>Amount</th>
                    <th>Day</th>
                    <th>Hour</th>
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
                    <td>' . $record['ter_number'] . '</td>
                    <td>' . $record['ter_persentase'] . '</td>
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
            // $html .= '<td>' . $record['pph'] . '</td>';
            $html .= '<td>' . $record['ter'] . '</td>';
            $html .= '<td>' . $record['net_income'] . '</td>';
            $no++;
        }

        $html .= '</table></body></html>';
        echo $html;
    }

    //PRINT & EXCEL DATA
    public function print2($option = "")
    {
        if ($option == "excel") {
            $format  = date("Ymd");
            header("Content-type: application/vnd-ms-excel");
            header("Content-Disposition: attachment; filename=payrolls_report_$format.xls");
        }

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

        $query = $this->db->query("SELECT a.*, b.national_id, d.name as source_name, e.ter as ter_persentase
                FROM payrolls a
                JOIN employees b on a.employee_id = b.id
                LEFT JOIN sources d on b.source_id = d.id
                LEFT JOIN marital_categories e ON a.ter_number = e.number
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

        $query2 = $this->db->query("SELECT a.position_name, SUM(a.net_income) as net_income
                FROM payrolls a
                JOIN employees b on a.employee_id = b.id
                LEFT JOIN sources d on b.source_id = d.id
                LEFT JOIN marital_categories e ON a.ter_number = e.number
                JOIN privilege_groups c ON b.group_id = c.group_id and c.username = '$username' and c.status = '1'
                WHERE a.period_start = '$period_start' 
                AND a.period_end = '$period_end'
                AND b.division_id LIKE '%$filter_division%'
                AND b.departement_id LIKE '%$filter_departement%'
                AND b.departement_sub_id LIKE '%$filter_departement_sub%'
                AND a.employee_id LIKE '%$filter_employee%'
                AND b.contract_id LIKE '%$filter_employee_type%'
                AND b.group_id LIKE '%$filter_group%'
                GROUP BY a.position_name
                ORDER BY a.position_name ASC");
        $records2 = $query2->result_array();

        $html_sum = "";
        $total_sum = 0;
        foreach ($records2 as $data2) {
            $html_sum .= '  <tr>
                                <th style="text-align:left;">'.$data2['position_name'].'</th>
                                <th style="text-align:right;">'.$data2['net_income'].'</th>
                            </tr>';
            $total_sum += $data2['net_income'];
        }

        //Config
        $this->db->select('*');
        $this->db->from('config');
        $config = $this->db->get()->row();

        //Allowances
        $queryAT = $this->db->query("SELECT * FROM allowances");
        $dataAT = $queryAT->result_array();

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
                <th width="20">No</th>
                <th>Employee ID</th>
                <th>Full Name</th>
                <th>Status</th>
                <th>No. KTP</th>
                <th>Status PTKP</th>
                <th>Division</th>
                <th>Group</th>
                <th>Job Position</th>
                <th>Organization</th>
                <th>Gaji Harian</th>
                <th>Uang Makan</th>
                <th>Basic Salary</th>
                <th>Tunjangan Tetap</th>
                <th>Tunjangan Jabatan</th>
                <th>Tunjangan Kinerja</th>
                <th>Tunjangan Transport</th>
                <th>Correction Plus</th>
                <th>Total Overtime</th>
                <th>Tax Allowance</th>
                <th>Total Allowance</th>
                <th>Potongan Absen</th>
                <th>Potongan Lain-lain</th>
                <th>Jaminan Pensiun Employee</th>
                <th>BPJSK Employee</th>
                <th>JHT Employee</th>
                <th>PPH 21</th>
                <th>Total Deduction</th>
                <th>PPH 21 Payment</th>
                <th>Take Home Pay</th>
                <th>BPJS TK Rate</th>
                <th>BPJS KES Rate</th>
            </tr>';

        $no = 1;
        foreach ($records as $record) {
            $total_tunjangan_tetap = 0;
            $total_tunjangan_jabatan = 0;
            $total_tunjangan_kinerja = 0;
            $total_tunjangan_transport = 0;
            foreach ($dataAT as $rowAT) {
                $allowence = (array) json_decode($record['allowence'], false);
                if($rowAT['type'] == "FIX"){
                    $total_tunjangan_tetap += $allowence[strtolower($rowAT['number'])];
                }
                
                if(substr($rowAT['number'], 0, 5) == "TJ-JB"){
                    $total_tunjangan_jabatan += $allowence[strtolower($rowAT['number'])];
                }

                if(substr($rowAT['number'], 0, 5) == "TJ-KN"){
                    $total_tunjangan_kinerja += $allowence[strtolower($rowAT['number'])];
                }

                if(substr($rowAT['number'], 0, 5) == "TJ-TR"){
                    $total_tunjangan_transport += $allowence[strtolower($rowAT['number'])];
                }
            }
            
            $total_deduction = 0;
            foreach (json_decode($record['deduction'], true) as $deduction => $val_deduction) {
                $total_deduction += (int) $val_deduction;
            }

            $total_deduction_amount = 0;
            foreach (json_decode($record['deduction_amount'], true) as $deduction2 => $val_deduction2) {
                $total_deduction_amount += (int) $val_deduction2;
            }

            $total_jp_employee = 0;
            $total_bpjs_employee = 0;
            $total_jht_employee = 0;
            foreach (json_decode($record['bpjs_employee'], true) as $bpjs_employee => $val_bpjs_employee) {
                if(substr($bpjs_employee, 0, 2) == "jp"){
                    $total_jp_employee += (int) $val_bpjs_employee;
                }

                if(substr($bpjs_employee, 0, 4) == "bpjs"){
                    $total_bpjs_employee += (int) $val_bpjs_employee;
                }

                if(substr($bpjs_employee, 0, 3) == "jht"){
                    $total_jht_employee += (int) $val_bpjs_employee;
                }
            }

            if($record['group_name'] == "GOLONGAN I"){
                $bpjsTotal = 0;
            }else{
                $bpjsTotal = ($record['salary'] + $total_tunjangan_tetap);
            }

            if($record['status'] == "0"){
                $status = "ACTIVE";
            }else{
                $status = "INACTIVE";
            }

            $html .= '<tr>
                    <td>' . $no . '</td>
                    <td class="str">' . $record['number'] . '</td>
                    <td>' . $record['name'] . '</td>
                    <td>' . $status . '</td>
                    <td class="str">' . $record['national_id'] . '</td>
                    <td>' . $record['marital'] . '</td>
                    <td>' . $record['division_name'] . '</td>
                    <td>' . $record['group_name'] . '</td>
                    <td>' . $record['position_name'] . '</td>
                    <td>' . $record['departement_name'] . '</td>
                    <td>' . 0 . '</td>
                    <td>' . 0 . '</td>
                    <td>' . $record['salary'] . '</td>
                    <td>' . $total_tunjangan_tetap . '</td>
                    <td>' . $total_tunjangan_jabatan . '</td>
                    <td>' . $total_tunjangan_kinerja . '</td>
                    <td>' . $total_tunjangan_transport . '</td>
                    <td>' . $record['correction_plus'] . '</td>
                    <td>' . $record['total_overtime_amount'] . '</td>
                    <td>' . $record['ter'] . '</td>
                    <td>' . $record['total_all_allowance'] . '</td>
                    <td>' . $record['deduction_absence_amount'] . '</td>
                    <td>' . ($total_deduction + $total_deduction_amount) . '</td>
                    <td>' . $total_jp_employee . '</td>
                    <td>' . $total_bpjs_employee . '</td>
                    <td>' . $total_jht_employee . '</td>
                    <td>' . $record['ter'] . '</td>
                    <td>' . $record['total_all_deduction'] . '</td>
                    <td>' . $record['ter'] . '</td>
                    <td>' . $record['net_income'] . '</td>
                    <td>' . $bpjsTotal . '</td>
                    <td>' . $bpjsTotal . '</td>
                    </tr>';
            $no++;
        }

        $html .= '</table><br><br>';
        $html .= '  <table border="0" cellpadding="0" cellspacing="0">
                        <tr>
                            <td style="vertical-align: top; padding-right: 20px;">
                                <table style="width:500px;" border="1">
                                    <tr>
                                        <th colspan="2" style="text-align:center;">PT Banshu Plastic Indoensia</th>
                                    </tr>
                                    <tr>
                                        <th style="text-align:center;">Group</th>
                                        <th style="text-align:center;">Take Home Pay</th>
                                    </tr>
                                    '.$html_sum.'
                                    <tr>
                                        <th style="text-align:left;">Total</th>
                                        <th style="text-align:right;">'.$total_sum.'</th>
                                    </tr>
                                </table>
                            </td>
                            <td colspan="6"></td>
                            <td style="vertical-align: top;">
                                <table style="width:500px;" border="1">
                                    <tr>
                                        <th colspan="2" style="text-align:center;">Approval By</th>
                                    </tr>
                                    <tr>
                                        <th height="80"></th>
                                        <th height="80"></th>
                                    </tr>
                                    <tr>
                                        <th style="text-align:center;">Rendhika Harsono</th>
                                        <th style="text-align:center;">Askara Harsono</th>
                                    </tr>
                                </table>
                            </td>
                        </tr>
                    </table>';
        $html .= '</body>
                </html>';
        echo $html;
    }
}
