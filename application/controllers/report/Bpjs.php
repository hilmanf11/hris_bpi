<?php
date_default_timezone_set("Asia/Bangkok");
defined('BASEPATH') or exit('No direct script access allowed');

class Bpjs extends CI_Controller
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
            $this->load->view('report/bpjs');
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
            $filter_division = $this->input->get('filter_division');
            $filter_departement = $this->input->get('filter_departement');
            $filter_departement_sub = $this->input->get('filter_departement_sub');
            $filter_employee = $this->input->get('filter_employee');

            $period_start = date("Y-m", strtotime($filter_from));
            $period_end = date("Y-m", strtotime($filter_to));

            $query = $this->db->query("SELECT a.*, 
                b.name as division_name,
                c.name as departement_name,
                d.name as departement_sub_name,
                d.type,
                p.amount,
                e.number as marital,
                a.tax_id,
                a.national_id,
                a.birthday,
                a.jamsostek,
                a.jamsostek_date,
                a.bank_no,
                f.bpjs_company,
                f.bpjs_employee
                FROM employees a
                JOIN divisions b ON a.division_id = b.id
                JOIN departements c ON a.departement_id = c.id
                JOIN departement_subs d ON a.departement_sub_id = d.id
                JOIN maritals e ON a.marital_id = e.id
                JOIN payrolls f ON a.id = f.employee_id
                JOIN setup_salaries p ON p.employee_id = a.id
                WHERE a.deleted = 0 and a.jamsostek != '' and a.jamsostek != '-'
                AND f.period_start = '$period_start'
                AND a.division_id LIKE '%$filter_division%'
                AND a.departement_id LIKE '%$filter_departement%'
                AND a.departement_sub_id LIKE '%$filter_departement_sub%'
                AND a.id LIKE '%$filter_employee%'
                GROUP BY a.id
                ORDER BY a.name ASC
            ");
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
                        <h3 style="margin:0;">Report BPJS Payment</h3>
                    </center>
                    <br>';
            $html .= '  <table id="customers" border="1">
                        <tr>
                            <th rowspan="2" width="20">No</th>
                            <th rowspan="2" style="text-align:center;">Nomor Referensi</th>
                            <th rowspan="2" style="text-align:center;">Nomor Induk Kependudukan (NIK)</th>
                            <th rowspan="2" style="text-align:center;">Nama Tenaga Kerja</th>
                            <th rowspan="2" style="text-align:center;">Barcode</th>
                            <th rowspan="2" style="text-align:center;">Tanggal Lahir</th>
                            <th rowspan="2" style="text-align:center;">Tanggal Kepesertaan</th>
                            <th rowspan="2" style="text-align:center;">Jumlah Upah (Rp.)</th>
                            <th rowspan="2" style="text-align:center;">Jumlah Rapel (Rp.)</th>
                            <th rowspan="2" style="text-align:center;">Iuran JKK (Rp.)</th>
                            <th rowspan="2" style="text-align:center;">Iuran JKM (Rp.)</th>
                            <th colspan="2" style="text-align:center;">Iuran Kes</th>
                            <th colspan="2" style="text-align:center;">Iuran JHT TK</th>
                            <th colspan="2" style="text-align:center;">Iuran JP</th>
                            <th colspan="2" style="text-align:center;">Iuran JKP</th>
                            <th rowspan="2" style="text-align:center;">Total Iuran (Rp.)</th>
                            <th rowspan="2" style="text-align:center;">Porsi Perusahaan</th>
                            <th rowspan="2" style="text-align:center;">Porsi Karyawan</th>
                        </tr>
                        <tr>
                            <th style="text-align:center;">Pemberi Kerja (Rp.)</th>
                            <th style="text-align:center;">Tenaga Kerja (Rp.)</th>
                            <th style="text-align:center;">Pemberi Kerja (Rp.)</th>
                            <th style="text-align:center;">Tenaga Kerja (Rp.)</th>
                            <th style="text-align:center;">Pemberi Kerja (Rp.)</th>
                            <th style="text-align:center;">Tenaga Kerja (Rp.)</th>
                            <th style="text-align:center;">Pemberi Kerja (Rp.)</th>
                            <th style="text-align:center;">Pemerintah (Rp.)</th>
                        </tr>';
            $no = 1;
            foreach ($records as $record) {
                //BPJS Employee JKK
                $bpjs_jkk = json_decode($record['bpjs_company']);
                $total_bpjs_jkk = $bpjs_jkk->jkk_company;
                //-------------------------------------------------------------------------------------------------------------------------------------------------------

                //BPJS Employee JKM
                $bpjs_jkm = json_decode($record['bpjs_company']);
                $total_bpjs_jkm = $bpjs_jkm->jkm_company;
                //-------------------------------------------------------------------------------------------------------------------------------------------------------

                //BPJS Employee KES
                $bpjs_kes_emp = json_decode($record['bpjs_employee']);
                $bpjs_kes_com = json_decode($record['bpjs_company']);
                $total_bpjs_kes_emp = $bpjs_kes_emp->bpjs_employee;
                $total_bpjs_kes_com = $bpjs_kes_com->bpjs_company;

                //BPJS Employee JHT
                $bpjs_jht_emp = json_decode($record['bpjs_employee']);
                $bpjs_jht_com = json_decode($record['bpjs_company']);
                $total_bpjs_jht_emp = $bpjs_jht_emp->jht_employee;
                $total_bpjs_jht_com = $bpjs_jht_com->jht_company;

                //BPJS Employee JP
                $bpjs_jp_emp = json_decode($record['bpjs_employee']);
                $bpjs_jp_com = json_decode($record['bpjs_company']);
                $total_bpjs_jp_emp = $bpjs_jp_emp->jp_employee;
                $total_bpjs_jp_com = $bpjs_jp_com->jp_company;

                //BPJS Employee JKP
                $bpjs_jkp_emp = json_decode($record['bpjs_employee']);
                $bpjs_jkp_com = json_decode($record['bpjs_company']);
                $total_bpjs_jkp_emp = @$bpjs_jkp_emp->jkp_employee;
                $total_bpjs_jkp_com = @$bpjs_jkp_com->jkp_company;

                //BPJS TOTAL Iuran
                $total_bpjs = (@$total_bpjs_jkk + @$total_bpjs_jkm + @$total_bpjs_jht_com + @$total_bpjs_jht_emp + @$total_bpjs_jp_com + @$total_bpjs_jp_emp + @$total_bpjs_jkp_com + @$total_bpjs_jkp_emp + @$total_bpjs_kes_com);
                $total_company = (@$total_bpjs_jkk + @$total_bpjs_jkm + @$total_bpjs_jht_com + @$total_bpjs_jp_com + @$total_bpjs_jkp_com + @$total_bpjs_kes_com);
                $total_employee = (@$total_bpjs_jht_emp + @$total_bpjs_jp_emp + @$total_bpjs_jkp_emp + @$total_bpjs_kes_emp);
                //-------------------------------------------------------------------------------------------------------------------------------------------------------

                if ($record['jamsostek_date'] != "0000-00-00") {
                    $jamsostek = date("d-m-Y", strtotime($record['jamsostek_date']));
                } else {
                    $jamsostek = "-";
                }

                $html .= '<tr>
                            <td>' . $no . '</td>
                            <td style="text-align:center;" class="str">' . $record['jamsostek'] . '</td>
                            <td style="text-align:center;" class="str">' . $record['national_id'] . '</td>
                            <td>' . $record['name'] . '</td>
                            <td style="text-align:center;" class="str">' . $record['number'] . '</td>
                            <td style="text-align:center;">' . date("d-m-Y", strtotime($record['birthday'])) . '</td>
                            <td style="text-align:center;">' . $jamsostek . '</td>
                            <td style="text-align:center;">' . number_format($record['amount'], 0, ',', '.') . '</td>
                            <td style="text-align:center;">0,00</td>
                            <td style="text-align:center;">' . number_format(@$total_bpjs_jkk, 2, ',', '.') . '</td>
                            <td style="text-align:center;">' . number_format(@$total_bpjs_jkm, 2, ',', '.') . '</td>
                            <td style="text-align:center;">' . number_format(@$total_bpjs_kes_com, 2, ',', '.') . '</td>
                            <td style="text-align:center;">' . number_format(@$total_bpjs_kes_emp, 2, ',', '.') . '</td>
                            <td style="text-align:center;">' . number_format(@$total_bpjs_jht_com, 2, ',', '.') . '</td>
                            <td style="text-align:center;">' . number_format(@$total_bpjs_jht_emp, 2, ',', '.') . '</td>
                            <td style="text-align:center;">' . number_format(@$total_bpjs_jp_com, 2, ',', '.') . '</td>
                            <td style="text-align:center;">' . number_format(@$total_bpjs_jp_emp, 2, ',', '.') . '</td>
                            <td style="text-align:center;">' . number_format(@$total_bpjs_jkp_com, 2, ',', '.') . '</td>
                            <td style="text-align:center;">' . number_format(@$total_bpjs_jkp_emp, 2, ',', '.') . '</td>
                            <td style="text-align:center;">' . number_format(@$total_bpjs, 2, ',', '.') . '</td>
                            <td style="text-align:center;">' . number_format(@$total_company, 2, ',', '.') . '</td>
                            <td style="text-align:center;">' . number_format(@$total_employee, 2, ',', '.') . '</td>
                        </tr>';
                $no++;
            }
            $html .= '</table>';
        }

        $html .= '</body></html>';
        echo $html;
    }
}
