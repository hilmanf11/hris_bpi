<?php
date_default_timezone_set("Asia/Bangkok");
defined('BASEPATH') or exit('No direct script access allowed');

class Payroll_records extends CI_Controller
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
            $this->load->view('report/payroll_records');
        } else {
            redirect('error_access');
        }
    }

    public function print($option = "")
    {
        if ($option == "excel") {
            $format  = date("Ymd");
            header("Content-type: application/vnd-ms-excel");
            header("Content-Disposition: attachment; filename=payroll_records_$format.xls");
        }

        if ($this->input->get()) {
            $filter_from = $this->input->get('filter_from');
            $filter_to = $this->input->get('filter_to');
            $filter_division = $this->input->get('filter_division');
            $filter_departement = $this->input->get('filter_departement');
            $filter_type = $this->input->get('filter_type');
            $username = $this->session->username;

            $period_start = date("Y-m", strtotime($filter_from));
            $period_end = date("Y-m", strtotime($filter_to));

            if($filter_type == "Karyawan"){
                $records = $this->crud->query("SELECT a.departement_id, 
                    b.name as departement_name, 
                    a.job_type, 
                    c.name as position_name, 
                    d.name as contract_name, 
                    e.name as account_name, 
                    (COALESCE(SUM(f.total), 0) + COALESCE(SUM(j.total), 0)) as total_mp, 
                    (COALESCE(SUM(f.total_income), 0) + COALESCE(SUM(j.total_income), 0)) as total_salary, 
                    (COALESCE(SUM(g.total), 0) + COALESCE(SUM(k.total), 0)) as cash_mp, 
                    (COALESCE(SUM(g.total_income), 0) + COALESCE(SUM(k.total_income), 0)) as total_cash, 
                    (COALESCE(SUM(h.total), 0) + COALESCE(SUM(l.total), 0)) as bsi_mp, 
                    (COALESCE(SUM(h.total_income), 0) + COALESCE(SUM(l.total_income), 0)) as total_bsi, 
                    (COALESCE(SUM(i.total), 0) + COALESCE(SUM(m.total), 0)) as mandiri_mp, 
                    (COALESCE(SUM(i.total_income), 0) + COALESCE(SUM(m.total_income), 0)) as total_mandiri
                FROM account_coa a
                JOIN departements b ON a.departement_id = b.id
                JOIN positions c ON a.position_id = c.id
                JOIN contracts d ON a.contract_id = d.id
                JOIN accounts e ON a.account_id = e.id
                LEFT JOIN (SELECT a.departement_id, a.position_id, a.contract_id, COUNT(a.id) as total, SUM(b.net_income) as total_income FROM employees a JOIN payrolls b ON a.id = b.employee_id WHERE a.status = 0 and b.period_start = '$period_start' and b.period_end = '$period_end' GROUP BY a.departement_id, a.position_id, a.contract_id) f ON a.departement_id = f.departement_id and a.position_id = f.position_id and a.contract_id = f.contract_id
                LEFT JOIN (SELECT a.departement_id, a.position_id, a.contract_id, COUNT(a.id) as total, SUM(b.net_income) as total_income FROM employees a JOIN payrolls b ON a.id = b.employee_id WHERE a.status = 0 and b.period_start = '$period_start' and b.period_end = '$period_end' and (a.bank_name = '-' or a.bank_name = '') GROUP BY a.departement_id, a.position_id, a.contract_id) g ON a.departement_id = g.departement_id and a.position_id = g.position_id and a.contract_id = g.contract_id
                LEFT JOIN (SELECT a.departement_id, a.position_id, a.contract_id, COUNT(a.id) as total, SUM(b.net_income) as total_income FROM employees a JOIN payrolls b ON a.id = b.employee_id WHERE a.status = 0 and b.period_start = '$period_start' and b.period_end = '$period_end' and a.bank_name = 'Bank Syariah Indonesia' GROUP BY a.departement_id, a.position_id, a.contract_id) h ON a.departement_id = h.departement_id and a.position_id = h.position_id and a.contract_id = h.contract_id
                LEFT JOIN (SELECT a.departement_id, a.position_id, a.contract_id, COUNT(a.id) as total, SUM(b.net_income) as total_income FROM employees a JOIN payrolls b ON a.id = b.employee_id WHERE a.status = 0 and b.period_start = '$period_start' and b.period_end = '$period_end' and a.bank_name = 'Bank Mandiri' GROUP BY a.departement_id, a.position_id, a.contract_id) i ON a.departement_id = i.departement_id and a.position_id = i.position_id and a.contract_id = i.contract_id
                LEFT JOIN (SELECT a.departement_id, a.position_id, a.contract_id, COUNT(a.id) as total, SUM(b.total_income) as total_income FROM employees a JOIN payroll_pkl b ON a.id = b.employee_id WHERE a.status = 0 and b.period_start = '$filter_from' and b.period_end = '$filter_to' GROUP BY a.departement_id, a.position_id, a.contract_id) j ON a.departement_id = j.departement_id and a.position_id = j.position_id and a.contract_id = j.contract_id
                LEFT JOIN (SELECT a.departement_id, a.position_id, a.contract_id, COUNT(a.id) as total, SUM(b.total_income) as total_income FROM employees a JOIN payroll_pkl b ON a.id = b.employee_id WHERE a.status = 0 and b.period_start = '$filter_from' and b.period_end = '$filter_to' and (a.bank_name = '-' or a.bank_name = '') GROUP BY a.departement_id, a.position_id, a.contract_id) k ON a.departement_id = k.departement_id and a.position_id = k.position_id and a.contract_id = k.contract_id
                LEFT JOIN (SELECT a.departement_id, a.position_id, a.contract_id, COUNT(a.id) as total, SUM(b.total_income) as total_income FROM employees a JOIN payroll_pkl b ON a.id = b.employee_id WHERE a.status = 0 and b.period_start = '$filter_from' and b.period_end = '$filter_to' and a.bank_name = 'Bank Syariah Indonesia' GROUP BY a.departement_id, a.position_id, a.contract_id) l ON a.departement_id = l.departement_id and a.position_id = l.position_id and a.contract_id = l.contract_id
                LEFT JOIN (SELECT a.departement_id, a.position_id, a.contract_id, COUNT(a.id) as total, SUM(b.total_income) as total_income FROM employees a JOIN payroll_pkl b ON a.id = b.employee_id WHERE a.status = 0 and b.period_start = '$filter_from' and b.period_end = '$filter_to' and a.bank_name = 'Bank Mandiri' GROUP BY a.departement_id, a.position_id, a.contract_id) m ON a.departement_id = m.departement_id and a.position_id = m.position_id and a.contract_id = m.contract_id
                WHERE a.departement_id LIKE '%$filter_departement%'
                GROUP BY a.departement_id, a.position_id, a.contract_id, a.account_id, a.job_type
                ORDER BY a.departement_id, a.job_type, a.position_id, a.contract_id asc");

                //Config
                $this->db->select('*');
                $this->db->from('config');
                $config = $this->db->get()->row();

                $html = '<html><head><title>Print Data</title></head><style>body {font-family: Arial, Helvetica, sans-serif;}#customers {border-collapse: collapse;width: 100%;font-size: 10px;}#customers td, #customers th {border: 1px solid #ddd;padding: 2px;}#customers tr:nth-child(even){background-color: #f2f2f2;}#customers tr:hover {background-color: #ddd;}#customers th {padding-top: 2px;padding-bottom: 2px;text-align: left;color: black;}</style>
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
                            <h3 style="margin:0;">Payroll Records</h3>
                            <p style="margin:0;"><b>Employee Payroll Method</b></p>
                            <p style="margin:0;">Period ' . $filter_from . ' to ' . $filter_to . '</p>
                        </center>
                        <br>';

                $html .= '  <table id="customers" border="1">
                                <tr>
                                    <th style="text-align:center;" width="50">No</th>
                                    <th style="text-align:center;" width="200">Departement</th>
                                    <th style="text-align:center;" width="80">Job Type</th>
                                    <th style="text-align:center;" width="150">Position</th>
                                    <th style="text-align:center;" width="150">Employee Type</th>
                                    <th style="text-align:center;" width="100">COA</th>
                                    <th style="text-align:center;" width="80">MP</th>
                                    <th style="text-align:center;" width="100">Amount</th>
                                    <th style="text-align:center;" width="80">MP</th>
                                    <th style="text-align:center;" width="100">CASH</th>
                                    <th style="text-align:center;" width="80">MP</th>
                                    <th style="text-align:center;" width="100">BSM</th>
                                    <th style="text-align:center;" width="80">MP</th>
                                    <th style="text-align:center;" width="100">Mandiri</th>
                                </tr>';
                $no = 1;
                $departement_name = "";
                $total_mp = 0;
                $total_salary = 0;
                $cash_mp = 0;
                $total_cash = 0;
                $bsi_mp = 0;
                $total_bsi = 0;
                $mandiri_mp = 0;
                $total_mandiri = 0;
                foreach ($records as $record) {
                    $departements = $this->crud->reads("account_coa", [], ["departement_id" => $record->departement_id]);
                    if($departement_name == $record->departement_name){
                        $rowspan_departement = "";
                    }else{
                        $rowspan_departement = "<td rowspan='".COUNT($departements)."'>".$no."</td><td rowspan='".COUNT($departements)."'>".$record->departement_name."</td>";
                        $no++;
                    }

                    $html .= "  <tr>
                                    $rowspan_departement
                                    <td>".$record->job_type."</td>
                                    <td>".$record->position_name."</td>
                                    <td>".$record->contract_name."</td>
                                    <td>".$record->account_name."</td>
                                    <td style='text-align:right;'>".number_format($record->total_mp)."</td>
                                    <td style='text-align:right;'>".number_format($record->total_salary)."</td>
                                    <td style='text-align:right;'>".number_format($record->cash_mp)."</td>
                                    <td style='text-align:right;'>".number_format($record->total_cash)."</td>
                                    <td style='text-align:right;'>".number_format($record->bsi_mp)."</td>
                                    <td style='text-align:right;'>".number_format($record->total_bsi)."</td>
                                    <td style='text-align:right;'>".number_format($record->mandiri_mp)."</td>
                                    <td style='text-align:right;'>".number_format($record->total_mandiri)."</td>
                                </tr>";
                    $departement_name = $record->departement_name;
                    $total_mp += $record->total_mp;
                    $total_salary += $record->total_salary;
                    $cash_mp += $record->cash_mp;
                    $total_cash += $record->total_cash;
                    $bsi_mp += $record->bsi_mp;
                    $total_bsi += $record->total_bsi;
                    $mandiri_mp += $record->mandiri_mp;
                    $total_mandiri += $record->total_mandiri;
                }
                $html .= "  <tr>
                                <td colspan='6' style='text-align:right; font-weight:bold;'>GRAND TOTAL</td>
                                <td style='text-align:right; font-weight:bold;'>".number_format($total_mp)."</td>
                                <td style='text-align:right; font-weight:bold;'>".number_format($total_salary)."</td>
                                <td style='text-align:right; font-weight:bold;'>".number_format($cash_mp)."</td>
                                <td style='text-align:right; font-weight:bold;'>".number_format($total_cash)."</td>
                                <td style='text-align:right; font-weight:bold;'>".number_format($bsi_mp)."</td>
                                <td style='text-align:right; font-weight:bold;'>".number_format($total_bsi)."</td>
                                <td style='text-align:right; font-weight:bold;'>".number_format($mandiri_mp)."</td>
                                <td style='text-align:right; font-weight:bold;'>".number_format($total_mandiri)."</td>
                            </tr>";
                $html .= '</table>
                <br>
                <center>
                    <table id="customers" style="width:70%;">
                        <tr>
                            <th rowspan="2" width="100" style="text-align:center;">APPROVED</th>
                            <th colspan="2" style="text-align:center;">CONFIRM OK</th>
                            <th rowspan="2" width="100" style="text-align:center;">PREPARED</th>
                        </tr>
                        <tr>
                            <th width="100" style="text-align:center;">COST CONTROL</th>
                            <th width="100" style="text-align:center;">HRD</th>
                        </tr>
                        <tr>
                            <td style="height:60px;"></td>
                            <td style="height:60px;"></td>
                            <td style="height:60px;"></td>
                            <td style="height:60px;"></td>
                        </tr>
                        <tr>
                            <th style="text-align:center;">BOD</th>
                            <th style="text-align:center;">ASSISTANT MANAGER</th>
                            <th style="text-align:center;">ASSISTANT MANAGER</th>
                            <th style="text-align:center;">PAYROLL STAFF</th>
                        </tr>
                    </table>
                </center>';
                
                $html .= '</body></html>';
                echo $html;
            }else if($filter_type == "Magang"){
                $records = $this->crud->query("SELECT a.name, 
                (COALESCE(c.total, 0)) as total_mp,
                (COALESCE(c.total_income, 0)) as total_salary,
                (COALESCE(d.total, 0)) as cash_mp,
                (COALESCE(d.total_income, 0)) as cash_salary,
                (COALESCE(e.total, 0)) as bsi_mp,
                (COALESCE(e.total_income, 0)) as bsi_salary,
                (COALESCE(f.total, 0)) as mandiri_mp,
                (COALESCE(f.total_income, 0)) as mandiri_salary
                FROM sources a
                JOIN employees b ON a.id = b.source_id
                LEFT JOIN (SELECT a.source_id, COUNT(a.id) as total, SUM(b.net_income) as total_income FROM employees a JOIN payrolls b ON a.id = b.employee_id WHERE a.status = 0 and b.period_start = '$period_start' and b.period_end = '$period_end' and a.source_id != '' GROUP BY a.source_id) c ON a.id = c.source_id
                LEFT JOIN (SELECT a.source_id, COUNT(a.id) as total, SUM(b.net_income) as total_income FROM employees a JOIN payrolls b ON a.id = b.employee_id WHERE a.status = 0 and b.period_start = '$period_start' and b.period_end = '$period_end' and a.source_id != '' and (a.bank_name = '-' or a.bank_name = '') GROUP BY a.source_id) d ON a.id = d.source_id
                LEFT JOIN (SELECT a.source_id, COUNT(a.id) as total, SUM(b.net_income) as total_income FROM employees a JOIN payrolls b ON a.id = b.employee_id WHERE a.status = 0 and b.period_start = '$period_start' and b.period_end = '$period_end' and a.source_id != '' and a.bank_name = 'Bank Syariah Indonesia' GROUP BY a.source_id) e ON a.id = e.source_id
                LEFT JOIN (SELECT a.source_id, COUNT(a.id) as total, SUM(b.net_income) as total_income FROM employees a JOIN payrolls b ON a.id = b.employee_id WHERE a.status = 0 and b.period_start = '$period_start' and b.period_end = '$period_end' and a.source_id != '' and a.bank_name = 'Bank Mandiri' GROUP BY a.source_id) f ON a.id = f.source_id
                WHERE a.contract_id = '20221119000003' and b.departement_id like '%$filter_departement%'
                GROUP BY a.id");

                //Config
                $this->db->select('*');
                $this->db->from('config');
                $config = $this->db->get()->row();

                $html = '<html><head><title>Print Data</title></head><style>body {font-family: Arial, Helvetica, sans-serif;}#customers {border-collapse: collapse;width: 100%;font-size: 10px;}#customers td, #customers th {border: 1px solid #ddd;padding: 2px;}#customers tr:nth-child(even){background-color: #f2f2f2;}#customers tr:hover {background-color: #ddd;}#customers th {padding-top: 2px;padding-bottom: 2px;text-align: left;color: black;}</style>
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
                            <h3 style="margin:0;">Payroll Records</h3>
                            <p style="margin:0;"><b>Payroll Method Magang</b></p>
                            <p style="margin:0;">Period ' . $filter_from . ' to ' . $filter_to . '</p>
                        </center>
                        <br>';

                $html .= '  <table id="customers" border="1">
                                <tr>
                                    <th style="text-align:center;" width="50">No</th>
                                    <th style="text-align:center;" width="200">Sources</th>
                                    <th style="text-align:center;" width="80">MP</th>
                                    <th style="text-align:center;" width="100">Amount</th>
                                    <th style="text-align:center;" width="80">MP</th>
                                    <th style="text-align:center;" width="100">CASH</th>
                                    <th style="text-align:center;" width="80">MP</th>
                                    <th style="text-align:center;" width="100">BSM</th>
                                    <th style="text-align:center;" width="80">MP</th>
                                    <th style="text-align:center;" width="100">Mandiri</th>
                                </tr>';
                $no = 1;
                $total_mp = 0;
                $total_salary = 0;
                $cash_mp = 0;
                $total_cash = 0;
                $bsi_mp = 0;
                $total_bsi = 0;
                $mandiri_mp = 0;
                $total_mandiri = 0;
                foreach ($records as $record) {
                    $html .= "  <tr>
                                    <td>".$no."</td>
                                    <td>".$record->name."</td>
                                    <td style='text-align:right;'>".number_format($record->total_mp)."</td>
                                    <td style='text-align:right;'>".number_format($record->total_salary)."</td>
                                    <td style='text-align:right;'>".number_format($record->cash_mp)."</td>
                                    <td style='text-align:right;'>".number_format($record->cash_salary)."</td>
                                    <td style='text-align:right;'>".number_format($record->bsi_mp)."</td>
                                    <td style='text-align:right;'>".number_format($record->bsi_salary)."</td>
                                    <td style='text-align:right;'>".number_format($record->mandiri_mp)."</td>
                                    <td style='text-align:right;'>".number_format($record->mandiri_salary)."</td>
                                </tr>";
                    $no++;
                    $total_mp += $record->total_mp;
                    $total_salary += $record->total_salary;
                    $cash_mp += $record->cash_mp;
                    $total_cash += $record->cash_salary;
                    $bsi_mp += $record->bsi_mp;
                    $total_bsi += $record->bsi_salary;
                    $mandiri_mp += $record->mandiri_mp;
                    $total_mandiri += $record->mandiri_salary;
                }
                $html .= "  <tr>
                                <td colspan='2' style='text-align:right; font-weight:bold;'>GRAND TOTAL</td>
                                <td style='text-align:right; font-weight:bold;'>".number_format($total_mp)."</td>
                                <td style='text-align:right; font-weight:bold;'>".number_format($total_salary)."</td>
                                <td style='text-align:right; font-weight:bold;'>".number_format($cash_mp)."</td>
                                <td style='text-align:right; font-weight:bold;'>".number_format($total_cash)."</td>
                                <td style='text-align:right; font-weight:bold;'>".number_format($bsi_mp)."</td>
                                <td style='text-align:right; font-weight:bold;'>".number_format($total_bsi)."</td>
                                <td style='text-align:right; font-weight:bold;'>".number_format($mandiri_mp)."</td>
                                <td style='text-align:right; font-weight:bold;'>".number_format($total_mandiri)."</td>
                            </tr>";
                $html .= '</table>
                <br>
                <center>
                    <table id="customers" style="width:70%;">
                        <tr>
                            <th rowspan="2" width="100" style="text-align:center;">APPROVED</th>
                            <th colspan="2" style="text-align:center;">CONFIRM OK</th>
                            <th rowspan="2" width="100" style="text-align:center;">PREPARED</th>
                        </tr>
                        <tr>
                            <th width="100" style="text-align:center;">COST CONTROL</th>
                            <th width="100" style="text-align:center;">HRD</th>
                        </tr>
                        <tr>
                            <td style="height:60px;"></td>
                            <td style="height:60px;"></td>
                            <td style="height:60px;"></td>
                            <td style="height:60px;"></td>
                        </tr>
                        <tr>
                            <th style="text-align:center;">BOD</th>
                            <th style="text-align:center;">ASSISTANT MANAGER</th>
                            <th style="text-align:center;">ASSISTANT MANAGER</th>
                            <th style="text-align:center;">PAYROLL STAFF</th>
                        </tr>
                    </table>
                </center>';
                
                $html .= '</body></html>';
                echo $html;
            }else if($filter_type == "PKL"){
                $records = $this->crud->query("SELECT a.name, 
                (COALESCE(c.total, 0)) as total_mp,
                (COALESCE(c.total_income, 0)) as total_salary,
                (COALESCE(d.total, 0)) as cash_mp,
                (COALESCE(d.total_income, 0)) as cash_salary
                FROM sources a
                JOIN employees b ON a.id = b.source_id
                LEFT JOIN (SELECT a.source_id, COUNT(a.id) as total, SUM(b.total_income) as total_income FROM employees a JOIN payroll_pkl b ON a.id = b.employee_id WHERE a.status = 0 and b.period_start = '$filter_from' and b.period_end = '$filter_to' and a.source_id != '' GROUP BY a.source_id) c ON a.id = c.source_id
                LEFT JOIN (SELECT a.source_id, COUNT(a.id) as total, SUM(b.total_income) as total_income FROM employees a JOIN payroll_pkl b ON a.id = b.employee_id WHERE a.status = 0 and b.period_start = '$filter_from' and b.period_end = '$filter_to' and a.source_id != '' and (a.bank_name = '-' or a.bank_name = '') GROUP BY a.source_id) d ON a.id = d.source_id
                WHERE a.contract_id = '20221119000005' and b.departement_id like '%$filter_departement%'
                GROUP BY a.id");

                //Config
                $this->db->select('*');
                $this->db->from('config');
                $config = $this->db->get()->row();

                $html = '<html><head><title>Print Data</title></head><style>body {font-family: Arial, Helvetica, sans-serif;}#customers {border-collapse: collapse;width: 100%;font-size: 10px;}#customers td, #customers th {border: 1px solid #ddd;padding: 2px;}#customers tr:nth-child(even){background-color: #f2f2f2;}#customers tr:hover {background-color: #ddd;}#customers th {padding-top: 2px;padding-bottom: 2px;text-align: left;color: black;}</style>
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
                            <h3 style="margin:0;">Payroll Records</h3>
                            <p style="margin:0;"><b>Payroll Method by PKL</b></p>
                            <p style="margin:0;">Period ' . $filter_from . ' to ' . $filter_to . '</p>
                        </center>
                        <br>';

                $html .= '  <table id="customers" border="1">
                                <tr>
                                    <th style="text-align:center;" width="50">No</th>
                                    <th style="text-align:center;" width="800">Sources</th>
                                    <th style="text-align:center;" width="80">MP</th>
                                    <th style="text-align:center;" width="100">Amount</th>
                                    <th style="text-align:center;" width="80">MP</th>
                                    <th style="text-align:center;" width="100">CASH</th>
                                </tr>';
                $no = 1;
                $total_mp = 0;
                $total_salary = 0;
                $cash_mp = 0;
                $total_cash = 0;
                foreach ($records as $record) {
                    $html .= "  <tr>
                                    <td>".$no."</td>
                                    <td>".$record->name."</td>
                                    <td style='text-align:right;'>".number_format($record->total_mp)."</td>
                                    <td style='text-align:right;'>".number_format($record->total_salary)."</td>
                                    <td style='text-align:right;'>".number_format($record->cash_mp)."</td>
                                    <td style='text-align:right;'>".number_format($record->cash_salary)."</td>
                                </tr>";
                    $no++;
                    $total_mp += $record->total_mp;
                    $total_salary += $record->total_salary;
                    $cash_mp += $record->cash_mp;
                    $total_cash += $record->cash_salary;
                }
                $html .= "  <tr>
                                <td colspan='2' style='text-align:right; font-weight:bold;'>GRAND TOTAL</td>
                                <td style='text-align:right; font-weight:bold;'>".number_format($total_mp)."</td>
                                <td style='text-align:right; font-weight:bold;'>".number_format($total_salary)."</td>
                                <td style='text-align:right; font-weight:bold;'>".number_format($cash_mp)."</td>
                                <td style='text-align:right; font-weight:bold;'>".number_format($total_cash)."</td>
                            </tr>";
                $html .= '</table>
                <br>
                <center>
                    <table id="customers" style="width:70%;">
                        <tr>
                            <th rowspan="2" width="100" style="text-align:center;">APPROVED</th>
                            <th colspan="2" style="text-align:center;">CONFIRM OK</th>
                            <th rowspan="2" width="100" style="text-align:center;">PREPARED</th>
                        </tr>
                        <tr>
                            <th width="100" style="text-align:center;">COST CONTROL</th>
                            <th width="100" style="text-align:center;">HRD</th>
                        </tr>
                        <tr>
                            <td style="height:60px;"></td>
                            <td style="height:60px;"></td>
                            <td style="height:60px;"></td>
                            <td style="height:60px;"></td>
                        </tr>
                        <tr>
                            <th style="text-align:center;">BOD</th>
                            <th style="text-align:center;">ASSISTANT MANAGER</th>
                            <th style="text-align:center;">ASSISTANT MANAGER</th>
                            <th style="text-align:center;">PAYROLL STAFF</th>
                        </tr>
                    </table>
                </center>';
                
                $html .= '</body></html>';
                echo $html;
            }else if($filter_type == "Group"){
                $records = $this->crud->query("SELECT a.name, 
                (COALESCE(c.total, 0) + COALESCE(g.total, 0)) as total_mp,
                (COALESCE(c.total_income, 0) + COALESCE(g.total_income, 0)) as total_salary,
                (COALESCE(d.total, 0) + COALESCE(h.total, 0)) as cash_mp,
                (COALESCE(d.total_income, 0) + COALESCE(h.total_income, 0)) as cash_salary,
                (COALESCE(e.total, 0)) as bsi_mp,
                (COALESCE(e.total_income, 0)) as bsi_salary,
                (COALESCE(f.total, 0)) as mandiri_mp,
                (COALESCE(f.total_income, 0)) as mandiri_salary
                FROM groups a
                JOIN employees b ON a.id = b.group_id
                LEFT JOIN (SELECT a.group_id, COUNT(a.id) as total, SUM(b.net_income) as total_income FROM employees a JOIN payrolls b ON a.id = b.employee_id WHERE a.status = 0 and b.period_start = '$period_start' and b.period_end = '$period_end' and a.group_id != '' GROUP BY a.group_id) c ON a.id = c.group_id
                LEFT JOIN (SELECT a.group_id, COUNT(a.id) as total, SUM(b.net_income) as total_income FROM employees a JOIN payrolls b ON a.id = b.employee_id WHERE a.status = 0 and b.period_start = '$period_start' and b.period_end = '$period_end' and a.group_id != '' and (a.bank_name = '-' or a.bank_name = '') GROUP BY a.group_id) d ON a.id = d.group_id
                LEFT JOIN (SELECT a.group_id, COUNT(a.id) as total, SUM(b.net_income) as total_income FROM employees a JOIN payrolls b ON a.id = b.employee_id WHERE a.status = 0 and b.period_start = '$period_start' and b.period_end = '$period_end' and a.group_id != '' and a.bank_name = 'Bank Syariah Indonesia' GROUP BY a.group_id) e ON a.id = e.group_id
                LEFT JOIN (SELECT a.group_id, COUNT(a.id) as total, SUM(b.net_income) as total_income FROM employees a JOIN payrolls b ON a.id = b.employee_id WHERE a.status = 0 and b.period_start = '$period_start' and b.period_end = '$period_end' and a.group_id != '' and a.bank_name = 'Bank Mandiri' GROUP BY a.group_id) f ON a.id = f.group_id
                LEFT JOIN (SELECT a.group_id, COUNT(a.id) as total, SUM(b.total_income) as total_income FROM employees a JOIN payroll_pkl b ON a.id = b.employee_id WHERE a.status = 0 and b.period_start = '$filter_from' and b.period_end = '$filter_to' and a.group_id != '' GROUP BY a.group_id) g ON a.id = g.group_id
                LEFT JOIN (SELECT a.group_id, COUNT(a.id) as total, SUM(b.total_income) as total_income FROM employees a JOIN payroll_pkl b ON a.id = b.employee_id WHERE a.status = 0 and b.period_start = '$filter_from' and b.period_end = '$filter_to' and a.group_id != '' and (a.bank_name = '-' or a.bank_name = '') GROUP BY a.group_id) h ON a.id = h.group_id
                WHERE b.departement_id like '%$filter_departement%'
                GROUP BY a.id");

                //Config
                $this->db->select('*');
                $this->db->from('config');
                $config = $this->db->get()->row();

                $html = '<html><head><title>Print Data</title></head><style>body {font-family: Arial, Helvetica, sans-serif;}#customers {border-collapse: collapse;width: 100%;font-size: 10px;}#customers td, #customers th {border: 1px solid #ddd;padding: 2px;}#customers tr:nth-child(even){background-color: #f2f2f2;}#customers tr:hover {background-color: #ddd;}#customers th {padding-top: 2px;padding-bottom: 2px;text-align: left;color: black;}</style>
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
                            <h3 style="margin:0;">Payroll Records</h3>
                            <p style="margin:0;"><b>Payroll Method By Group</b></p>
                            <p style="margin:0;">Period ' . $filter_from . ' to ' . $filter_to . '</p>
                        </center>
                        <br>';

                $html .= '  <table id="customers" border="1">
                                <tr>
                                    <th style="text-align:center;" width="50">No</th>
                                    <th style="text-align:center;" width="800">Group</th>
                                    <th style="text-align:center;" width="80">MP</th>
                                    <th style="text-align:center;" width="100">Amount</th>
                                    <th style="text-align:center;" width="80">MP</th>
                                    <th style="text-align:center;" width="100">CASH</th>
                                    <th style="text-align:center;" width="80">MP</th>
                                    <th style="text-align:center;" width="100">BSI</th>
                                    <th style="text-align:center;" width="80">MP</th>
                                    <th style="text-align:center;" width="100">Mandiri</th>
                                </tr>';
                $no = 1;
                $total_mp = 0;
                $total_salary = 0;
                $cash_mp = 0;
                $total_cash = 0;
                $bsi_mp = 0;
                $total_bsi = 0;
                $mandiri_mp = 0;
                $total_mandiri = 0;
                foreach ($records as $record) {
                    $html .= "  <tr>
                                    <td>".$no."</td>
                                    <td>".$record->name."</td>
                                    <td style='text-align:right;'>".number_format($record->total_mp)."</td>
                                    <td style='text-align:right;'>".number_format($record->total_salary)."</td>
                                    <td style='text-align:right;'>".number_format($record->cash_mp)."</td>
                                    <td style='text-align:right;'>".number_format($record->cash_salary)."</td>
                                    <td style='text-align:right;'>".number_format($record->bsi_mp)."</td>
                                    <td style='text-align:right;'>".number_format($record->bsi_salary)."</td>
                                    <td style='text-align:right;'>".number_format($record->mandiri_mp)."</td>
                                    <td style='text-align:right;'>".number_format($record->mandiri_salary)."</td>
                                </tr>";
                    $no++;
                    $total_mp += $record->total_mp;
                    $total_salary += $record->total_salary;
                    $cash_mp += $record->cash_mp;
                    $total_cash += $record->cash_salary;
                    $bsi_mp += $record->bsi_mp;
                    $total_bsi += $record->bsi_salary;
                    $mandiri_mp += $record->mandiri_mp;
                    $total_mandiri += $record->mandiri_salary;
                }
                $html .= "  <tr>
                                <td colspan='2' style='text-align:right; font-weight:bold;'>GRAND TOTAL</td>
                                <td style='text-align:right; font-weight:bold;'>".number_format($total_mp)."</td>
                                <td style='text-align:right; font-weight:bold;'>".number_format($total_salary)."</td>
                                <td style='text-align:right; font-weight:bold;'>".number_format($cash_mp)."</td>
                                <td style='text-align:right; font-weight:bold;'>".number_format($total_cash)."</td>
                                <td style='text-align:right; font-weight:bold;'>".number_format($bsi_mp)."</td>
                                <td style='text-align:right; font-weight:bold;'>".number_format($total_bsi)."</td>
                                <td style='text-align:right; font-weight:bold;'>".number_format($mandiri_mp)."</td>
                                <td style='text-align:right; font-weight:bold;'>".number_format($total_mandiri)."</td>
                            </tr>";
                $html .= '</table>
                <br>
                <center>
                    <table id="customers" style="width:70%;">
                        <tr>
                            <th rowspan="2" width="100" style="text-align:center;">APPROVED</th>
                            <th colspan="2" style="text-align:center;">CONFIRM OK</th>
                            <th rowspan="2" width="100" style="text-align:center;">PREPARED</th>
                        </tr>
                        <tr>
                            <th width="100" style="text-align:center;">COST CONTROL</th>
                            <th width="100" style="text-align:center;">HRD</th>
                        </tr>
                        <tr>
                            <td style="height:60px;"></td>
                            <td style="height:60px;"></td>
                            <td style="height:60px;"></td>
                            <td style="height:60px;"></td>
                        </tr>
                        <tr>
                            <th style="text-align:center;">BOD</th>
                            <th style="text-align:center;">ASSISTANT MANAGER</th>
                            <th style="text-align:center;">ASSISTANT MANAGER</th>
                            <th style="text-align:center;">PAYROLL STAFF</th>
                        </tr>
                    </table>
                </center>';
                
                $html .= '</body></html>';
                echo $html;
            }
        }
    }
}
