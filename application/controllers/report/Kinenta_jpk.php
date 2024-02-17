<?php
date_default_timezone_set("Asia/Bangkok");
defined('BASEPATH') or exit('No direct script access allowed');

class Kinenta_jpk extends CI_Controller
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
            $this->load->view('report/kinenta_jpk');
        } else {
            redirect('error_access');
        }
    }

    public function print($option = "")
    {
        if ($option == "excel") {
            $format  = date("Ymd");
            header("Content-type: application/vnd-ms-excel");
            header("Content-Disposition: attachment; filename=leave_$format.xls");
        }

        if ($this->input->get()) {
            $filter_from = $this->input->get('filter_from');
            $filter_to = $this->input->get('filter_to');
            $filter_group = $this->input->get('filter_group');
            $filter_employee = $this->input->get('filter_employee');

            $period_start = date("Y-m", strtotime($filter_from));
            $period_end = date("Y-m", strtotime($filter_to));

            if ($filter_group == "PKL" || $filter_group == "MAGANG") {
                $this->db->select("a.number, a.name, a.date_sign, a.gender, c.name as departement_name, g.number as marital_number, g.name as marital_name, d.name as group_name, f.amount");
                $this->db->from('employees a');
                $this->db->join('divisions b', 'a.division_id = b.id');
                $this->db->join('departements c', 'a.departement_id = c.id');
                $this->db->join('groups d', 'a.group_id = d.id');
                $this->db->join('sources e', 'a.source_id = e.id');
                $this->db->join('kinenta_jpk f', 'f.number = d.name');
                $this->db->join('maritals g', 'g.id = a.marital_id');
                $this->db->where('a.status', 0);
                $this->db->where('f.number', $filter_group);
                $this->db->like('a.id', $filter_employee);
                $this->db->group_by('a.number');
                $this->db->order_by('a.name', 'asc');
                $records = $this->db->get()->result_array();
            } else {
                $query = $this->db->query("SELECT a.number, a.name, a.date_sign, a.gender, b.name as departement_name, c.number as marital_number, c.name as marital_name, d.name as group_name, f.amount
                FROM employees a
                JOIN departements b ON a.departement_id = b.id
                JOIN maritals c ON a.marital_id = c.id
                JOIN groups d ON a.group_id = d.id
                LEFT JOIN payrolls e ON e.employee_id = a.id
                JOIN kinenta_jpk f ON (f.number = d.name or f.number = c.number)
                WHERE a.id like '%$filter_employee%' and e.period_start = '$period_start' and e.period_end = '$period_end' and f.number = '$filter_group' 
                GROUP BY a.id
                ORDER BY a.name ASC");
                $records = $query->result_array();
            }

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
                        <h3 style="margin:0;">REPORT REGISTERED JPK RSBH</h3>
                        <b>Period ' . $filter_from . ' and ' . $filter_to . '</b>
                    </center>
                    <br>';
            $html .= '  <table id="customers" border="1">
                        <tr>
                            <th width="20">No</th>
                            <th style="text-align:center;">Employee ID</th>
                            <th style="text-align:center;">Employee Name</th>
                            <th style="text-align:center;">Fit Of Services</th>
                            <th style="text-align:center;">Gender</th>
                            <th style="text-align:center;">Marital</th>
                            <th style="text-align:center;">Departement</th>
                            <th style="text-align:center;">Group</th>
                            <th style="text-align:center;">Premi (Rp)</th>
                        </tr>';
            $no = 1;
            foreach ($records as $record) {
                $start  = date_create($record['date_sign']);
                $end = date_create($filter_to);
                $diff  = date_diff($start, $end);

                $selisih = date_diff(date_create($record['date_sign']), date_create($filter_to));

                $d = $diff->d . ' Days ';

                if ($diff->y == 0) {
                    $y = '';
                } else {
                    $y = $diff->y . ' Years, ';
                }

                if ($diff->m == 0) {
                    $m = '';
                } else {
                    $m = $diff->m . ' Month, ';
                }

                $services = $y . $m . $d;

                if ($filter_group == "PKL" || $filter_group == "MAGANG") {
                    $html .= '<tr>
                            <td>' . $no . '</td>
                            <td style="text-align:center;" class="str">' . $record['number'] . '</td>
                            <td>' . $record['name'] . '</td>
                            <td>' . $services . '</td>
                            <td>' . $record['gender'] . '</td>
                            <td>' . $record['marital_name'] . '</td>
                            <td>' . $record['departement_name'] . '</td>
                            <td>' . $record['group_name'] . '</td>
                            <td>' . number_format($record['amount']) . '</td>
                        </tr>';
                    $no++;
                } else {
                    if ($selisih->format('%a') <= 31) {
                        $html .= '<tr>
                            <td>' . $no . '</td>
                            <td style="text-align:center;" class="str">' . $record['number'] . '</td>
                            <td>' . $record['name'] . '</td>
                            <td>' . $services . '</td>
                            <td>' . $record['gender'] . '</td>
                            <td>' . $record['marital_name'] . '</td>
                            <td>' . $record['departement_name'] . '</td>
                            <td>' . $record['group_name'] . '</td>
                            <td>' . number_format($record['amount']) . '</td>
                        </tr>';
                        $no++;
                    }
                }
            }
            $html .= '</table>';
        }

        $html .= '</body></html>';
        echo $html;
    }
}
