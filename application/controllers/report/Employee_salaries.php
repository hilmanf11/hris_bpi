<?php
date_default_timezone_set("Asia/Bangkok");
defined('BASEPATH') or exit('No direct script access allowed');

class Employee_salaries extends CI_Controller
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
            $this->load->view('report/employee_salaries');
        } else {
            redirect('error_access');
        }
    }

    public function print($option = "")
    {
        if ($option == "excel") {
            $format  = date("Ymd");
            header("Content-type: application/vnd-ms-excel");
            header("Content-Disposition: attachment; filename=employee_salaries_$format.xls");
        }

        if ($this->input->get()) {
            $filter_from = $this->input->get('filter_from');
            $filter_to = $this->input->get('filter_to');
            $filter_division = $this->input->get('filter_division');
            $filter_departement = $this->input->get('filter_departement');
            $filter_departement_sub = $this->input->get('filter_departement_sub');
            $filter_group = $this->input->get('filter_group');
            $filter_employee = $this->input->get('filter_employee');
            $filter_bank = $this->input->get('filter_bank');
            $username = $this->session->username;

            $period_start = date("Y-m", strtotime($filter_from));
            $period_end = date("Y-m", strtotime($filter_to));

            $query = $this->db->query("SELECT b.bank_no, a.net_income, a.number, a.name, d.name as source_name FROM payrolls a
                JOIN employees b ON a.employee_id = b.id
                JOIN privilege_groups c ON b.group_id = c.group_id and c.username = '$username' and c.status = '1'
                LEFT JOIN sources d ON b.source_id = d.id
                WHERE a.period_start = '$period_start' and a.period_end = '$period_end'
                AND b.division_id LIKE '%$filter_division%'
                AND b.departement_id LIKE '%$filter_departement%'
                AND b.departement_sub_id LIKE '%$filter_departement_sub%'
                AND c.group_id LIKE '%$filter_group%'
                AND a.employee_id LIKE '%$filter_employee%'
                AND b.bank_name LIKE '%$filter_bank%'
                ORDER BY d.name, a.`name` ASC");
            $records = $query->result_array();

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
                        <h3 style="margin:0;">Monthly Employee Salary Report</h3>
                        <p style="margin:0;">Period ' . $filter_from . ' to ' . $filter_to . '</p>
                    </center>
                    <br>';

            if ($filter_bank == "Bank Central Asia") {
                $html .= '  <table id="customers" border="1">
                                <tr>
                                    <th width="20">No</th>
                                    <th style="text-align:center;">Acc No</th>
                                    <th style="text-align:center;">Trans Amount</th>
                                    <th style="text-align:center;">Employee Number</th>
                                    <th style="text-align:center;">Employee Name</th>
                                </tr>';
                $no = 1;
                foreach ($records as $record) {
                    $html .= '<tr>
                                <td>' . $no . '</td>
                                <td style="mso-number-format:\@;">' . $record['bank_no'] . '</td>
                                <td style="text-align:right;">' . $record['net_income'] . '</td>
                                <td style="mso-number-format:\@;" class="str">' . $record['number'] . '</td>
                                <td>' . $record['name'] . '</td>
                            </tr>';
                    $no++;
                }
                $html .= '</table>';
            } else {
                $html .= '  <table id="customers" border="1">
                                <tr>
                                    <th width="20">No</th>
                                    <th style="text-align:center;">NIK</th>
                                    <th style="text-align:center;">NAMA</th>
                                    <th style="text-align:center;">NO REK</th>
                                    <th style="text-align:center;">JUMLAH</th>
                                    <th style="text-align:center;">SOURCE</th>
                                </tr>';
                $no = 1;
                $grand_total = 0;
                foreach ($records as $record) {
                    $grand_total +=  $record['net_income'];
                    $html .= '<tr>
                                <td>' . $no . '</td>
                                <td style="mso-number-format:\@;">' . $record['number'] . '</td>
                                <td>' . $record['name'] . '</td>
                                <td style="mso-number-format:\@;">' . $record['bank_no'] . '</td>
                                <td style="text-align:right;">' . $record['net_income'] . '</td>
                                <td>' . $record['source_name'] . '</td>
                            </tr>';
                    $no++;
                }


                $html .= '  <tr>
                                <th colspan="4" style="text-align:right;">GRAND TOTAL</th>
                                <th style="text-align:right;">'.$grand_total.'</th>
                                <th></th>
                            </tr>
                            </table>';
            }
        }

        $html .= '</body></html>';
        echo $html;
    }

    public function csv()
    {
        $filename = "employee_salaries.csv";
        $file = fopen('php://output', 'w');
        header('Content-Disposition: attachment; filename=' . $filename);
        header('Content-Type: text/csv');

        $filter_from = $this->input->get('filter_from');
        $filter_to = $this->input->get('filter_to');
        $filter_division = $this->input->get('filter_division');
        $filter_departement = $this->input->get('filter_departement');
        $filter_departement_sub = $this->input->get('filter_departement_sub');
        $filter_group = $this->input->get('filter_group');
        $filter_employee = $this->input->get('filter_employee');
        $filter_bank = $this->input->get('filter_bank');
        $username = $this->session->username;

        $period_start = date("Y-m", strtotime($filter_from));
        $period_end = date("Y-m", strtotime($filter_to));

        if ($filter_bank == "Bank Central Asia") {
            $query = $this->db->query("SELECT b.bank_no, a.net_income, a.number, a.name FROM payrolls a
                JOIN employees b ON a.employee_id = b.id
                JOIN privilege_groups c ON b.group_id = c.group_id and c.username = '$username' and c.status = '1'
                WHERE a.period_start = '$period_start' and a.period_end = '$period_end'
                AND b.division_id LIKE '%$filter_division%'
                AND b.departement_id LIKE '%$filter_departement%'
                AND b.departement_sub_id LIKE '%$filter_departement_sub%'
                AND c.group_id LIKE '%$filter_group%'
                AND a.employee_id LIKE '%$filter_employee%'
                AND b.bank_name LIKE '%$filter_bank%'
                ORDER BY a.`name` ASC");
            $records = $query->result_array();
            $no = 1;
            fputcsv($file, array('Acc No', 'Trans Amount', 'Employee Number', 'Employee Name'));
            foreach ($records as $record => $line) {
                fputcsv($file, $line);
            }
            fclose($file);
            exit;
        } else {
            $query = $this->db->query("SELECT a.number, a.name, b.bank_no, a.net_income FROM payrolls a
                JOIN employees b ON a.employee_id = b.id
                JOIN privilege_groups c ON b.group_id = c.group_id and c.username = '$username' and c.status = '1'
                WHERE a.period_start = '$period_start' and a.period_end = '$period_end'
                AND b.division_id LIKE '%$filter_division%'
                AND b.departement_id LIKE '%$filter_departement%'
                AND b.departement_sub_id LIKE '%$filter_departement_sub%'
                AND a.employee_id LIKE '%$filter_employee%'
                AND c.group_id LIKE '%$filter_group%'
                AND b.bank_name LIKE '%$filter_bank%'
                ORDER BY a.`name` ASC");
            $records = $query->result_array();
            $no = 1;
            fputcsv($file, array('NIK', 'Nama', 'No Rek', 'Jumlah'));
            foreach ($records as $record => $line) {
                fputcsv($file, $line);
            }
            fclose($file);
            exit;
        }
    }
}
