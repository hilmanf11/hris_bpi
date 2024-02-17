<?php
date_default_timezone_set("Asia/Bangkok");
defined('BASEPATH') or exit('No direct script access allowed');

class Pph_21 extends CI_Controller
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
            $this->load->view('report/pph_21');
        } else {
            redirect('error_access');
        }
    }

    //GET DATA
    public function groups()
    {
        $post = isset($_POST['q']) ? $_POST['q'] : "";
        $send = $this->crud->reads('groups', ["name" => $post], ["ppt" => "1"]);
        echo json_encode($send);
    }

    public function csv()
    {
        if ($this->input->get()) {

            header("Content-type: text/csv; charset=utf-8");
            header("Content-Disposition: attachment; filename=pph21_" . time() . ".csv");
            $output = fopen("php://output", "w");
            fputcsv($output, array(
                "Masa Pajak", "Tahun Pajak", "Pembetulan", "Nomor Bukti Potong", "NPWP", "NIK", "Nama",
                "Alamat", "WP Luar Negeri", "Kode Negara", "Kode Pajak", "Jumlah Bruto", "Jumlah DPP", "Tanpa NPWP", "Tarif",
                "Jumlah Pph", "NPWP Pemotong", "Nama Pemotong", "Tanggal Bukti Potong"
            ));

            $filter_from = $this->input->get('filter_from');
            $filter_to = $this->input->get('filter_to');
            $filter_group = $this->input->get('filter_group');

            $tgl_bp = date("d-M-y", strtotime($filter_from));
            $period_start = date("Y-m", strtotime($filter_from));
            $period_end = date("Y-m", strtotime($filter_to));

            $query = $this->db->query("SELECT b.name, b.tax_id, b.national_id, b.address, a.attandance_wd, a.marital, a.income
                FROM payrolls a
                JOIN employees b ON a.employee_id = b.id
                JOIN groups d ON b.group_id = d.id
                LEFT JOIN sources c ON b.source_id = c.id
                WHERE a.period_start = '$period_start' and a.period_end = '$period_end' and d.id like '%$filter_group%'
                GROUP BY a.id
                ORDER BY c.name, a.name ASC");
            $records = $query->result_array();

            //Config
            $this->db->select('*');
            $this->db->from('config');
            $config = $this->db->get()->row();

            $no = 1;
            foreach ($records as $record) {
                $number = "1.3-" . date("m", strtotime($filter_from)) . "." . date("y", strtotime($filter_from)) . "-" .  sprintf("%07s", $no);

                if ($record['tax_id'] == "") {
                    $tarif = "5";
                } else {
                    $tarif = "6";
                }

                $data = array(
                    date("m", strtotime($filter_from)),
                    date("Y", strtotime($filter_from)),
                    "0",
                    "$number",
                    str_replace(array('.', '-'), '', $record['tax_id']),
                    $record['national_id'],
                    $record['name'],
                    $record['address'],
                    "N",
                    "",
                    "21-100-03",
                    $record['income'],
                    "",
                    "N",
                    $tarif,
                    "",
                    $config->npwp,
                    $config->name,
                    date("30/m/Y", strtotime($filter_from)),
                );

                fputcsv($output, $data);

                $no++;
            }
        }
    }

    public function print($option = "")
    {
        if ($option == "excel") {
            $format  = date("Ymd");
            header("Content-type: application/vnd-ms-excel");
            header("Content-Disposition: attachment; filename=pph21_$format.xls");
        }

        if ($this->input->get()) {
            $filter_from = $this->input->get('filter_from');
            $filter_to = $this->input->get('filter_to');
            $filter_group = $this->input->get('filter_group');

            $tgl_bp = date("d-M-y", strtotime($filter_from));
            $period_start = date("Y-m", strtotime($filter_from));
            $period_end = date("Y-m", strtotime($filter_to));

            $query = $this->db->query("SELECT b.name, b.tax_id, b.national_id, c.name as alamat, a.attandance_wd, a.marital, a.net_income
                FROM payrolls a
                JOIN employees b ON a.employee_id = b.id
                JOIN groups d ON b.group_id = d.id
                LEFT JOIN sources c ON b.source_id = c.id
                WHERE a.period_start = '$period_start' and a.period_end = '$period_end' and d.id like '%$filter_group%'
                GROUP BY a.id
                ORDER BY c.name, a.name ASC");
            $records = $query->result_array();

            //Config
            $this->db->select('*');
            $this->db->from('config');
            $config = $this->db->get()->row();

            $html = '<html><head><title>Print Data</title></head><style>body {font-family: Arial, Helvetica, sans-serif;}#customers {border-collapse: collapse;width: 100%;font-size: 10px;}#customers td, #customers th {border: 1px solid #ddd;padding: 2px;}#customers tr:nth-child(even){background-color: #f2f2f2;}#customers tr:hover {background-color: #ddd;}#customers th {padding-top: 2px;padding-bottom: 2px;text-align: left;color: black;}</style>
                    <style> .str{ mso-number-format:\@; } </style>
                    <body>
                    <center>
                        <h3 style="margin:0;">TEMPLATE ESINDO (PPH 21) PPT</h3>
                        <b>Period ' . $filter_from . ' and ' . $filter_to . '</b>
                    </center>
                    <br><br>
                    <table id="customers" border="1">
                        <tr>
                            <th width="30">No</th>
                            <th style="text-align:center;">No. Urut</th>
                            <th style="text-align:center;">No. Urut BP</th>
                            <th style="text-align:center;">No BP</th>
                            <th style="text-align:center;">Tgl BP</th>
                            <th style="text-align:center;">Penerima Penghasilan</th>
                            <th style="text-align:center;">Nama</th>
                            <th style="text-align:center;">NPWP</th>
                            <th style="text-align:center;">NIK/No. Pasport</th>
                            <th style="text-align:center;">Alamat</th>
                            <th style="text-align:center;">Asal Negara</th>
                            <th style="text-align:center;">Kode Negara</th>
                            <th style="text-align:center;">Status PTKP</th>
                            <th style="text-align:center;">GU</th>
                            <th style="text-align:center;">Jml HK</th>
                            <th style="text-align:center;">PTT<br>Bulanan</th>
                            <th style="text-align:center;">Pembayaran</th>
                        </tr>';
            $no = 1;
            foreach ($records as $record) {
                $html .= '<tr>
                            <td style="text-align:center;">' . $no . '</td>
                            <td style="text-align:center;">Non Final</td>
                            <td style="text-align:center;">' . sprintf("%05s", $no) . '</td>
                            <td style="text-align:center;">1.3-01.23-' . sprintf("%07s", $no) . '</td>
                            <td style="text-align:center;">' . $tgl_bp . '</td>
                            <td>PTT atau Pegawai Lepas</td>
                            <td>' . $record['name'] . '</td>
                            <td style="text-align:center;">' . $record['tax_id'] . '</td>
                            <td style="text-align:center;" class="str">' . $record['national_id'] . '</td>
                            <td>' . $record['alamat'] . '</td>
                            <td></td>
                            <td></td>
                            <td style="text-align:center;">' . $record['marital'] . '</td>
                            <td style="text-align:center;">X</td>
                            <td style="text-align:center;">' . $record['attandance_wd'] . '</td>
                            <td style="text-align:center;">X</td>
                            <td style="text-align:right;">' . number_format($record['net_income']) . '</td>
                        </tr>';
                $no++;
            }
            $html .= '</table>';
        }

        $html .= '</body></html>';
        echo $html;
    }
}
