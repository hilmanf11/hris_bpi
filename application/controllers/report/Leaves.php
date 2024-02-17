<?php
date_default_timezone_set("Asia/Bangkok");
defined('BASEPATH') or exit('No direct script access allowed');

class Leaves extends CI_Controller
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
            $this->load->view('report/leaves');
        } else {
            redirect('error_access');
        }
    }

    public function readYears()
    {
        $query  = $this->db->query("SELECT DATE_FORMAT(permit_date, '%Y') as `year` FROM permits WHERE DATE_FORMAT(permit_date, '%Y') != '0000' GROUP BY `year`");
        $data   = $query->result();

        echo json_encode($data);
    }

    public function print($option = "")
    {
        if ($option == "excel") {
            $format  = date("Ymd");
            header("Content-type: application/vnd-ms-excel");
            header("Content-Disposition: attachment; filename=leave_$format.xls");
        }

        if ($this->input->get()) {
            $filter_year = $this->input->get('filter_year');
            $filter_division = $this->input->get('filter_division');
            $filter_departement = $this->input->get('filter_departement');
            $filter_departement_sub = $this->input->get('filter_departement_sub');
            $filter_employee = $this->input->get('filter_employee');

            $calendar = $this->crud->reads("calendars", ["trans_date" => $filter_year], ["permit" => 1]);

            $html = '<html><head><title>Print Data</title></head><style>body {font-family: Arial, Helvetica, sans-serif;}#customers {border-collapse: collapse;width: 100%;font-size: 10px;}#customers td, #customers th {border: 1px solid #ddd;padding: 2px;}#customers tr:nth-child(even){background-color: #f2f2f2;}#customers tr:hover {background-color: #ddd;}#customers th {padding-top: 2px;padding-bottom: 2px;text-align: left;color: black;}</style>
            <style> .str{ mso-number-format:\@; } </style>
            <body>';
            $this->db->select("a.*, b.name as division_name, c.name as departement_name, d.name as departement_sub_name");
            $this->db->from('employees a');
            $this->db->join('divisions b', 'a.division_id = b.id');
            $this->db->join('departements c', 'a.departement_id = c.id');
            $this->db->join('departement_subs d', 'a.departement_sub_id = d.id');
            $this->db->join('permits e', 'a.id = e.employee_id');
            $this->db->join('permit_types f', 'f.id = e.permit_type_id');
            $this->db->where('a.deleted', 0);
            $this->db->where('a.status', 0);
            if ($filter_year != "") {
                $this->db->where('DATE_FORMAT(e.permit_date, "%Y") =', $filter_year);
            }
            $this->db->like('a.division_id', $filter_division);
            $this->db->like('a.departement_id', $filter_departement);
            $this->db->like('a.id', $filter_employee);
            $this->db->where('f.cutoff', 'YES');
            $this->db->group_by('a.departement_id');
            $this->db->order_by('b.name', 'asc');
            $this->db->order_by('c.name', 'asc');
            $records = $this->db->get()->result_array();

            //Config
            $this->db->select('*');
            $this->db->from('config');
            $config = $this->db->get()->row();

            foreach ($records as $header) {
                $header_division = $header['division_id'];
                $header_departement = $header['departement_id'];

                $html .= '<div style="page-break-after:always;"><center>
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
                    <h3 style="margin:0;">Report Leaves</h3>
                    <br>
                </center>
                <table style="font-size: 11px;">
                    <tr>
                        <td width="100">Division</td>
                        <td width="10">:</td>
                        <td><b>' . $header['division_name'] . '</b></td>
                    </tr>
                    <tr>
                        <td>Departement</td>
                        <td>:</td>
                        <td><b>' . $header['departement_name'] . '</b></td>
                    </tr>
                </table>
                <br>';
                $html .= '  <table id="customers" border="1">
                                <tr>
                                    <th rowspan="2" width="50" style="text-align:center;">No</th>
                                    <th rowspan="2" style="text-align:center;">Sub Departement</th>
                                    <th rowspan="2" style="text-align:center;">Employee ID</th>
                                    <th rowspan="2" style="text-align:center;">Employee Name</th>
                                    <th colspan="4" style="text-align:center;">' . $filter_year . '</th>
                                </tr>
                                <tr>
                                    <th style="text-align:center;">Available</th>
                                    <th style="text-align:center;">Used</th>
                                    <th style="text-align:center;">Calendar</th>
                                    <th style="text-align:center;">Balance</th>
                                </tr>';

                $this->db->select("a.*, SUM(e.duration) as leaves, b.name as departement_sub_name");
                $this->db->from('employees a');
                $this->db->join('departement_subs b', 'a.departement_sub_id = b.id');
                $this->db->join('permits e', 'a.id = e.employee_id');
                $this->db->join('permit_types f', 'f.id = e.permit_type_id');
                if ($filter_year != "") {
                    $this->db->where('DATE_FORMAT(e.permit_date, "%Y") =', $filter_year);
                }
                $this->db->where('a.division_id', $header_division);
                $this->db->where('a.departement_id', $header_departement);
                $this->db->where('f.cutoff', 'YES');
                $this->db->where('a.deleted', 0);
                $this->db->where('a.status', 0);
                if ($filter_departement_sub != "") {
                    $this->db->where('a.departement_sub_id', $filter_departement_sub);
                }
                if ($filter_employee != "") {
                    $this->db->where('a.id', $filter_employee);
                }
                $this->db->group_by('a.id');
                $this->db->order_by('b.name', 'asc');
                $this->db->order_by('a.name', 'asc');
                $employees = $this->db->get()->result_array();

                $no = 1;
                foreach ($employees as $employee) {
                    $balance = "<b>" . (12 - @$employee['leaves'] - count($calendar)) . "</b>";

                    $html .= '<tr>
                                <th>' . $no . '</th>
                                <th>' . $employee['departement_sub_name'] . '</th>
                                <th class="str">' . $employee['number'] . '</th>
                                <th>' . $employee['name'] . '</th>
                                <th style="text-align:center;">' . 12 . '</th>
                                <th style="text-align:center; color:red;">' . $employee['leaves'] . '</th>
                                <th style="text-align:center; color:red;">' . count($calendar) . '</th>
                                <th style="text-align:center;">' . @$balance . '</th>
                            </tr>';
                    $no++;
                }


                $html .= '</table></div><br><br>';
            }

            $html .= '</body></html>';
            echo $html;
        }
    }
}
