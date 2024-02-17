<?php
date_default_timezone_set("Asia/Bangkok");
defined('BASEPATH') or exit('No direct script access allowed');

class Attandances extends CI_Controller
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
        show_error("Cannot Process your request");
    }

    public function dashboard($token = "")
    {
        if ($token != "") {
            $users_m = $this->crud->read("users_m", [], ["token" => $token]);
            $employee = $this->crud->read("employees", [], ["number" => $users_m->number]);
            $number = $users_m->number;
            $month = date("Y-m");

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
                $this->db->where('d.working', $status_working);
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

                if (@$attandance->time_in == null && $holiday != 'Weekend') {
                    if ($permit_name != null) {
                        $permits += 1;
                        $working += 0;
                        $absence += 0;
                        $late += 0;
                    } elseif ($holiday != null) {
                        $permits += 0;
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

            die(json_encode(array(
                "code" => "200",
                "results" => array(
                    "employee_name" => $employee->name,
                    "working" => $working,
                    "permission" => $permits,
                    "absence" => $absence,
                    "late" => $late
                ),
                "status" => "success",
            )));
        } else {
            show_error("Cannot Process your request");
        }
    }
}
