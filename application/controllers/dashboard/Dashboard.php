<?php
date_default_timezone_set("Asia/Bangkok");
defined('BASEPATH') or exit('No direct script access allowed');

class Dashboard extends CI_Controller
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

    public function index()
    {
        if ($this->session->username != "") {
            $data['config'] = $this->crud->read('config');
            $data['users'] = $this->crud->reads('users', [], ["actived" => 0, "deleted" => 0], "", "name", "asc");
            $data['session_name'] = $this->session->name;
            $data['attandance'] = $this->myattandance();
            $data['permittoday'] = $this->permitToday();
            $data['contracts'] = $this->contracts();
            $data['chartServices'] = $this->chartServices();
            $data['chartEmployee'] = $this->chartEmployee();

            if (date("H:i:s") >= "05:00:00" and date("H:i:s") <= "11:00:00") {
                $data['day'] = "Good Morning";
                $data['background'] = base_url('assets/image/morning.jpg');
                $data['color'] = "black";
            } elseif (date("H:i:s") >= "11:00:00" and date("H:i:s") <= "18:00:00") {
                $data['day'] = "Good Afternoon";
                $data['background'] = base_url('assets/image/afternoon.jpg');
                $data['color'] = "black";
            } else {
                $data['day'] = "Good Night";
                $data['background'] = base_url('assets/image/night.jpg');
                $data['color'] = "white";
            }

            $this->load->view('template/header');
            $this->load->view('dashboard/dashboard', $data);
        } else {
            redirect('error_session');
        }
    }

    public function readService($dateSign = "")
    {
        if ($dateSign == "") {
            $date = $this->input->post('date');
        } else {
            $date = $dateSign;
        }

        $start  = date_create($date);
        $end = date_create(); // waktu sekarang
        $diff  = date_diff($start, $end);
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

        if ($dateSign == "") {
            echo $y . $m . $d;
        } else {
            return $y . $m . $d;
        }
    }

    public function myattandance()
    {
        $month = date("Y-m");
        $number = $this->session->number;

        $start = strtotime($month . '-01');
        $finish = strtotime(date("Y-m-d"));

        $permits = 0;
        $working = 0;
        $absence = 0;
        $late = 0;
        for ($i = $start; $i <= $finish; $i += (60 * 60 * 24)) {
            $working_date = date('Y-m-d', $i);

            if (date('w', $i) !== '0' && date('w', $i) !== '6') {
                $status_working = "WEEKDAY";
            } else {
                $status_working = "WEEKEND";
            }

            //Working Calendar
            $this->db->select('description');
            $this->db->from('calendars');
            $this->db->where('trans_date', $working_date);
            $holiday = $this->db->get()->result_array();

            //Permit
            $this->db->select("a.*, c.name as reason_name, d.name as permit_name");
            $this->db->from('permits a');
            $this->db->join('employees b', 'a.employee_id = b.id');
            $this->db->join('reasons c', 'a.reason_id = c.id');
            $this->db->join('permit_types d', 'a.permit_type_id = d.id');
            $this->db->where('b.number', $number);
            $this->db->where('a.permit_date <=', $working_date);
            $this->db->where('a.permit_date >=', $working_date);
            $permit = $this->db->get()->row();

            //Attandance and Overtime
            $this->db->select("b.date_in, b.time_in, b.date_out, b.time_out, c.request_code, c.start, c.end, c.duration, c.remarks");
            $this->db->from('employees a');
            $this->db->join('attandances b', 'a.number = b.number');
            $this->db->join('overtimes c', 'a.id = c.employee_id and b.date_in = c.trans_date', 'left');
            $this->db->where('b.date_in =', $working_date);
            $this->db->where('a.number', $number);
            $this->db->order_by('a.name', 'asc');
            $this->db->order_by('b.date_in', 'asc');
            $attandance = $this->db->get()->row();

            //Shift and Setting Group
            $this->db->select("d.start, d.end, d.days, d.working, d.tolerance, d.name as shift_name");
            $this->db->from('employees a');
            $this->db->join('shift_employees b', 'a.id = b.employee_id');
            $this->db->join('shifts c', 'c.id = b.shift_id');
            $this->db->join('shift_details d', 'd.shift_id = c.id');
            $this->db->where('a.number', $number);
            $shift = $this->db->get()->row();

            $time_in = @$shift->start;
            $permit_remarks = @$permit->note;
            $permit_name = @$permit->permit_name;

            //Jika hari kerja nya adalah 5 hari
            if (@$shift->days == "5") {
                //sabtu dan minggu libur
                if (date('w', $i) !== '0' && date('w', $i) !== '6') {
                    $weekend = "";
                } else {
                    $weekend = "Weekend";
                }
            } else {
                //minggu doang libur
                if (date('w', $i) !== '0') {
                    $weekend = "";
                } else {
                    $weekend = "Weekend";
                }
            }

            if (@$time_in >= @$attandance->time_in) {
                $status_masuk = "ON TIME";
            } elseif (@$time_in == null) {
                $status_masuk = "UN SETTING";
            } elseif (@$time_in <= @$attandance->time_in) {
                $status_masuk = "LATE";
            } else {
                $status_masuk = "ERROR";
            }

            //jika status tanggal merah nya kosong
            if (@$holiday->description == null) {
                //cek apakah harinya minggu jika iya maka default Weekend
                if ($weekend == "Weekend") {
                    $holiday = $weekend;
                } elseif ($permit_name != null) {
                    $holiday = @$holiday->description;
                } else {
                    $holiday = $permit_remarks;
                }
                //isi dengan tanggal merah
            } else {
                $holiday = @$holiday->description;
            }

            if (@$attandance->time_in == null) {
                if ($holiday == "Weekend") {
                    $permits += 0;
                    $working += 0;
                    $absence += 0;
                    $late += 0;
                } elseif ($permit_name != null) {
                    $permits += 1;
                    $working += 0;
                    $absence += 0;
                    $late += 0;
                } else {
                    $permits += 0;
                    $working += 0;
                    $absence += 1;
                    $late += 0;
                }
            } else {
                if ($holiday == "Weekend") {
                    $permits += 0;
                    $working += 0;
                    $absence += 0;
                    $late += 0;
                } elseif (@$permit_name != null) {
                    $permits += 1;
                    $working += 0;
                    $absence += 0;
                    $late += 0;
                } else {
                    if (@$status_masuk == "LATE") {
                        $permits += 0;
                        $working += 1;
                        $absence += 0;
                        $late += 1;
                    } else if (@$status_masuk == "ON TIME") {
                        $permits += 0;
                        $working += 1;
                        $absence += 0;
                        $late += 0;
                    } else {
                        $permits += 0;
                        $working += 1;
                        $absence += 0;
                        $late += 0;
                    }
                }
            }
        }

        return array("total_working" => $working, "total_permit" => $permits, "total_absence" => $absence, "total_late" => $late);
    }

    public function mycalendar()
    {
        $bulan = date("m");
        $tahun = date("Y");

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

            $working_date = date("Y-m-$d");

            $this->db->select("b.date_in, b.time_in, b.date_out, b.time_out");
            $this->db->from('employees a');
            $this->db->join('attandances b', 'a.number = b.number');
            $this->db->where('b.date_in =', $working_date);
            $this->db->where('a.number', $this->session->number);
            $this->db->order_by('a.name', 'asc');
            $this->db->order_by('b.date_in', 'asc');
            $attandances = $this->db->get()->row();

            if (!empty($attandances->time_in)) {
                $in = "<div style='background:#65B451;padding:2px;color:white;margin:0;'> IN : " . $attandances->time_in . "</div>";
                if (!empty($attandances->time_out)) {
                    $out = "<div style='background:#B45151;padding:2px;color:white;margin:0;'>OUT : " . $attandances->time_out . "</div>";
                } else {
                    $out = "";
                }
            } else {
                $in = "";
                $out = "";
            }

            $tanggal = $tahun . "-" . $bulan . "-" . $d;
            $this->db->select('description');
            $this->db->from('calendars');
            $this->db->where('deleted', 0);
            $this->db->where('trans_date', $tanggal);
            $data = $this->db->get()->result_array();

            //Jika Hari Minggu
            $style = "background:white !important;";
            if (@$data[0]['description'] != "") {
                $style = "background:#FFDADA !important;";
            }

            if (date("l", mktime(0, 0, 0, $bulan, $d, $tahun)) == "Sunday") {
                $style = "background:#FFDADA !important;";
                $note = "";
            }

            $html .= "  <td align=center style='" . $style . " height:90px;' valign=middle>
                            <b style='font-size: 20px;'>$d</b><br>
                            " . $in . "" . $out . "
                        </td>";

            //Jika Sudah seminggu
            if (date("w", mktime(0, 0, 0, $bulan, $d, $tahun)) == 6) {
                $html .= "</tr>";
            }
        }
        $html .= '</table>';

        echo $html;
    }

    public function permitToday()
    {
        $permits = $this->crud->reads("permits", [], ["permit_date" => date("Y-m-d")]);
        if (count($permits) > 0) {
            $html = '<table class="user-header" style="width: 100%;">';
            foreach ($permits as $permit) {
                $employee = $this->crud->read("employees", [], ["id" => $permit->employee_id]);
                $position = $this->crud->read("positions", [], ["id" => $employee->position_id]);
                if ($employee->image_profile == "") {
                    $avatar = base_url('assets/image/users/default.png');
                } else {
                    $avatar = $employee->image_profile;
                }
                $html .= '	<tr>
                                <td width="50">
                                    <div class="icon-container">
                                        <img src="' . $avatar . '" class="user-online" />
                                        <div class="status-circle"></div>
                                    </div>
                                </td>
                                <td>
                                    <a href="#" style="text-decoration:none;">
                                        <b style="font-size:12px; color:black;">' . $employee->name . '</b><br>
                                        <small style="color:orange;">' . $permit->note . '</small>
                                    </a>
                                </td>
                                <td style="text-align:right;">
                                    <b style="font-size:10px !important; color:green;">' . $position->name . '</b>
                                </td>
                            </tr>';
            }

            $html .= "</table>";
        } else {
            $html = '<div class="alert alert-danger" role="alert">
                        No Permission Today
                    </div>';
        }

        return $html;
    }

    public function contracts()
    {
        $today = date("Y-m-d");
        $nextday = date("Y-m-d", strtotime($today . ' + 3 days'));
        $this->db->select('name, image_profile, date_expired, position_id, departement_id');
        $this->db->from('employees');
        $this->db->where("date_expired between '$today' and '$nextday'");
        $this->db->order_by('name', 'asc');
        $employees = $this->db->get()->result_object();
        if (count($employees) > 0) {
            $html = '<table class="user-header" style="width: 100%;">';
            foreach ($employees as $employee) {
                $position = $this->crud->read("positions", [], ["id" => $employee->position_id]);

                $expired = $this->readService($employee->date_expired);
                if ($employee->image_profile == "") {
                    $avatar = base_url('assets/image/users/default.png');
                } else {
                    $avatar = $employee->image_profile;
                }
                $html .= '	<tr>
                                <td width="50">
                                    <div class="icon-container">
                                        <img src="' . $avatar . '" class="user-online" />
                                        <div class="status-circle"></div>
                                    </div>
                                </td>
                                <td>
                                    <a href="#" style="text-decoration:none;">
                                        <b style="font-size:12px; color:black;">' . $employee->name . '</b><br>
                                        <small style="color:red;">' . $expired . '</small>
                                    </a>
                                </td>
                                <td style="text-align:right;">
                                    <b style="font-size:10px !important; color:green;">' . $position->name . '</b>
                                </td>
                            </tr>';
            }

            $html .= "</table>";
        } else {
            $html = '<div class="alert alert-danger" role="alert">
                        No Contract & Probation
                    </div>';
        }

        return $html;
    }

    public function chartServices()
    {
        $query = $this->crud->query("SELECT 
        SUM(CASE WHEN (timestampdiff(year, date_sign, curdate()) > 10) THEN 1 ELSE 0 END) as ten,
        SUM(CASE WHEN (timestampdiff(year, date_sign, curdate()) > 8) and (timestampdiff(year, date_sign, curdate()) <= 10) THEN 1 ELSE 0 END) as eightten, 
        SUM(CASE WHEN (timestampdiff(year, date_sign, curdate()) > 5) and (timestampdiff(year, date_sign, curdate()) <= 8) THEN 1 ELSE 0 END) as fiveeight,
        SUM(CASE WHEN (timestampdiff(year, date_sign, curdate()) > 2) and (timestampdiff(year, date_sign, curdate()) <= 5) THEN 1 ELSE 0 END) as twofive, 
        SUM(CASE WHEN (timestampdiff(year, date_sign, curdate()) > 1) and (timestampdiff(year, date_sign, curdate()) <= 2) THEN 1 ELSE 0 END) as onetwo, 
        SUM(CASE WHEN (timestampdiff(year, date_sign, curdate()) <= 1) THEN 1 ELSE 0 END) as one 
        FROM employees");

        foreach ($query as $row) {
            $arr = array(
                array("name" => "< 1 Year", "y" => $row->one),
                array("name" => "1 - 2 Year", "y" => $row->onetwo),
                array("name" => "2 - 5 Year", "y" => $row->twofive),
                array("name" => "5 - 8 Year", "y" => $row->fiveeight),
                array("name" => "8 - 10 Year", "y" => $row->eightten),
                array("name" => "> 10 Year", "y" => $row->ten),
            );
        }

        return $arr;
    }

    public function chartEmployee()
    {
        $query = $this->crud->query("SELECT b.name, COUNT(a.number) as employee FROM employees a JOIN contracts b  ON a.contract_id = b.id GROUP BY b.name");
        return $query;
    }
}
