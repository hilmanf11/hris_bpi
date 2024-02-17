<?php
date_default_timezone_set("Asia/Bangkok");
defined('BASEPATH') or exit('No direct script access allowed');

class Calendars extends CI_Controller
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
        $this->form_validation->set_rules('departement_id', 'Departement', 'required|min_length[1]|max_length[30]|is_unique[overtime_budgets.departement_id]');
    }

    //HALAMAN UTAMA
    public function index()
    {
        if (empty($this->session->username)) {
            redirect('error_session');
        } elseif ($this->checkuserAccess($this->id_menu()) > 0) {
            $data['button'] = $this->getbutton($this->id_menu());

            $this->load->view('template/header', $data);
            $this->load->view('attandance/calendars');
        } else {
            redirect('error_access');
        }
    }

    public function reads()
    {
        $bulan = $this->input->post('month');
        $tahun = $this->input->post('year');

        if ($bulan == "" or $tahun == "") {
            $bulan = date('m');
            $tahun = date('Y');
        }


        $hari = "01";
        $jumlahhari = date("t", mktime(0, 0, 0, $bulan, $hari, $tahun));

        $html = '<style>
                    body {
                        font-family: Arial, Helvetica, sans-serif;
                    }

                    #customers {
                        border-collapse: collapse;
                        width: 100%;
                        font-size: 10px;
                    }

                    #customers td,
                    #customers th {
                        border: 1px solid #ddd;
                        padding: 2px;
                        height:50px;
                    }

                    #customers tr:nth-child(even) {
                        background-color: #f2f2f2;
                    }

                    #customers tr:hover {
                        background-color: #ddd;
                    }

                    #customers th {
                        padding-top: 2px;
                        padding-bottom: 2px;
                        text-align: left;
                        color: black;
                    }

                    textarea { 
                        border: 0;
                        background:transparent;
                    }
                </style>
                <table id="customers" style="width: 100%;">
                    <tr>
                        <td align=center width="200">
                            <font color="#FF0000">Sunday</font>
                        </td>
                        <td align=center width="200">Monday</td>
                        <td align=center width="200">Tuesday</td>
                        <td align=center width="180">Wednesday</td>
                        <td align=center width="200">Thursday</td>
                        <td align=center width="200">Friday</td>
                        <td align=center width="200">Saturday</td>
                    </tr>';
        $s = date("w", mktime(0, 0, 0, $bulan, 1, $tahun));

        for ($ds = 1; $ds <= $s; $ds++) {
            $html .= "<td></td>";
        }

        for ($d = 1; $d <= $jumlahhari; $d++) {
            if (date("w", mktime(0, 0, 0, $bulan, $d, $tahun)) == 0) {
                $html .= "<tr>";
            }

            $tanggal = $tahun . "-" . $bulan . "-" . $d;
            $this->db->select('description, permit');
            $this->db->from('calendars');
            $this->db->where('deleted', 0);
            $this->db->where('trans_date', $tanggal);
            $data = $this->db->get()->result_array();

            if (@$data[0]['permit'] == 1) {
                $options = "<option value='0'>No</option><option selected value='1'>Yes</option>";
            } else {
                $options = "<option value='0' selected>No</option><option value='1'>Yes</option>";
            }

            //Jika Hari Minggu
            $style = "background:white !important;";
            $checkbox = "<input hidden checked class='checked' type='checkbox' value='" . $d . "' name='days[]' style='float: left; width: 20px;'/>";
            $permit = "Cut Leave <select name='permit[]' style='width:100%;'>" . $options . "</select>";
            $note = "<textarea rows='4' style='width:100%;' name='description[]'>" . @$data[0]['description'] . "</textarea>";

            if (@$data[0]['description'] != "") {
                $style = "background:#FFDADA !important;";
            }

            if (date("l", mktime(0, 0, 0, $bulan, $d, $tahun)) == "Sunday") {
                $style = "background:#FFDADA !important;";
                $note = "<textarea rows='2' hidden name='description[]'></textarea>";
                $permit = "<select hidden name='permit[]'><option value='0'>No</option><option value='1'>Yes</option></select>";
            }

            $html .= "  <td align=center style='" . $style . "' valign=middle>
                            $checkbox
                            <b style='font-size: 20px;'>$d</b><br>
                            $note
                            $permit
                        </td>";

            //Jika Sudah seminggu
            if (date("w", mktime(0, 0, 0, $bulan, $d, $tahun)) == 6) {
                $html .= "</tr>";
            }
        }
        $html .= '</table>';

        echo $html;
    }

    public function create()
    {
        if ($this->input->post()) {
            $month = $this->input->post('filter_month');
            $year = $this->input->post('filter_year');
            $days = $this->input->post('days');
            $permits = $this->input->post('permit');
            $description = $this->input->post('description');

            for ($i = 0; $i < count($days); $i++) {
                $date = $year . "-" . $month . "-" . $days[$i];
                $remark = @$description[$i];
                $permit = @$permits[$i];

                // if (empty($permits[$i])) {
                //     $permit = 0;
                // } else {
                //     $permit = 1;
                // }

                $this->db->select('*');
                $this->db->from('calendars');
                $this->db->where('deleted', 0);
                $this->db->where('trans_date', $date);
                $records = $this->db->get()->num_rows();

                if ($remark != "") {
                    if ($records > 0) {
                        $this->crud->update('calendars', ["trans_date" => $date], ["description" => $remark, "permit" => $permit]);
                    } else {
                        $this->crud->create('calendars', ["trans_date" => $date, "description" => $remark, "permit" => $permit]);
                    }
                } else {
                    if ($records > 0) {
                        $this->db->delete('calendars', ['trans_date' => $date]);
                    }
                }
            }

            echo json_encode(array("title" => "Good Job", "message" => "Data Saved Successfully", "theme" => "success"));
        } else {
            show_error("Cannot Process your request");
        }
    }
}
