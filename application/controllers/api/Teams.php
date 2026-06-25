<?php
header('Content-Type: application/json; charset=utf-8');

date_default_timezone_set("Asia/Bangkok");
defined('BASEPATH') or exit('No direct script access allowed');

class Teams extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->helper('url');
        $this->load->helper(array('form', 'url'));
        $this->load->library('form_validation');
        $this->load->library('session');
        $this->load->model('crud');
        $this->load->model('mobile');
    }

    //HALAMAN UTAMA
    public function index()
    {
        show_error("Cannot Process your request");
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
            $y = $diff->y . ' Years ';
        }

        if ($diff->m == 0) {
            $m = '';
        } else {
            $m = $diff->m . ' Month';
        }

        if ($dateSign == "") {
            echo $y . $m . $d;
        } else {
            return $y . $m;
        }
    }

    public function reads($api_key = "")
    {

        if ($api_key != "") {
            $user = $this->crud->read("users", [], ["api_key" => $api_key]);

            if ($user) {
                $employee = $this->crud->read("employees", [], ["number" => $user->number, "status" => 0]);
                $positions = $this->crud->query("SELECT b.id, b.name FROM employees a JOIN positions b ON a.position_id = b.id WHERE a.status = '0' and a.departement_id = '$employee->departement_id' GROUP BY b.id ORDER BY b.level ASC");

                $datas = array();
                $today = date("Y-m-d");
                foreach ($positions as $position) {
                    $employees = $this->crud->reads("employees", [], ["position_id" => $position->id, "departement_id" => $employee->departement_id, "status" => 0], "20", "name", "asc");

                    $datas2 = array();
                    foreach ($employees as $employee) {
                        // cek apakah user resign ?
                        $isResign = $this->crud->read("resignations", [], ["employee_id" => $employee->id]);
                        if ($isResign) {
                            continue;
                        }

                        if (!empty($employee->image_profile)) {
                            if (substr($employee->image_profile, -4) == "jpeg") {
                                $avatar = "assets/image/employee/profile/" . substr($employee->image_profile, -15);
                            } else {
                                $avatar = "assets/image/employee/profile/" . substr($employee->image_profile, -14);
                            }
                        } else {
                            $avatar = "assets/image/users/default.png";
                        }

                        $datas2[] = array(
                            "avatar" => $avatar,
                            "name" => $employee->name,
                            "telp" => $employee->mobile_phone,
                            "employee_id" => $employee->number,
                            "date_sign" => $employee->date_sign,
                            "services" => $this->readService($employee->date_sign),
                        );
                    }

                    $datas[] = array(
                        "position" => $position->name,
                        "details" => $datas2
                    );
                }

                die(json_encode(array(
                    "title" => "Success",
                    "message" => "Data Found",
                    "theme" => "success",
                    "results" => $datas
                )));
            } else {
                show_error("Teams Not Found");
            }
        } else {
            show_error("Cannot Process your request");
        }
    }

    // public function reads($api_key = "")
    // {

    //     if ($api_key != "") {
    //         $user = $this->crud->read("users", [], ["api_key" => $api_key]);

    //         if ($user) {
    //             $employee = $this->crud->read("employees", [], ["number" => $user->number]);
    //             $positions = $this->crud->query("SELECT b.id, b.name FROM employees a JOIN positions b ON a.position_id = b.id WHERE a.departement_id = '$employee->departement_id' GROUP BY b.id ORDER BY b.level ASC");

    //             $datas = array();
    //             foreach ($positions as $position) {
    //                 $employees = $this->crud->reads("employees", [], ["position_id" => $position->id, "departement_id" => $employee->departement_id], "20", "name", "asc");

    //                 $datas2 = array();
    //                 foreach ($employees as $employee) {
    //                     if ($employee->image_profile == null || $employee->image_profile == "") {
    //                         $avatar = "assets/image/users/default.png";
    //                     } else {
    //                         $avatar = "assets/image/users/" . $employee->image_profile;
    //                     }

    //                     $datas2[] = array(
    //                         "avatar" => $avatar,
    //                         "name" => $employee->name,
    //                         "birthday" => date("d F Y", strtotime($employee->birthday)),
    //                         "services" => $this->readService($employee->date_sign),
    //                     );
    //                 }

    //                 $datas[] = array(
    //                     "position" => $position->name,
    //                     "details" => $datas2
    //                 );
    //             }

    //             die(json_encode(array(
    //                 "title" => "Success",
    //                 "message" => "Data Found",
    //                 "theme" => "success",
    //                 "results" => $datas
    //             )));
    //         } else {
    //             show_error("Teams Not Found");
    //         }
    //     } else {
    //         show_error("Cannot Process your request");
    //     }
    // }

    public function positions()
    {
        $positions = $this->crud->reads('positions', [], [], "", "level", "asc");

        die(json_encode(array(
            "title" => "Success",
            "message" => "Data Found",
            "theme" => "success",
            "results" => $positions
        )));
    }

    public function attendanceSummaryTeamPerson($api_key = "")
    {
        $this->output->delete_cache();

        if (empty($api_key)) {
            show_error("Cannot Process your request");
            return;
        }

        $user = $this->mobile->read("users", [], ["api_key" => $api_key]);

        if (!$user) {
            show_error("Api Key Invalid");
            return;
        }

        if (!$this->input->post()) {
            show_error("Request invalid");
            return;
        }

        $post = $this->input->post();
        $number = $user->number;
        $employeeReq = $this->mobile->read("employees", [], ["number" => $post['employee_id']]);


        if (!$employeeReq) {
            show_error("Employee not found");
            return;
        }

        $number = $employeeReq->number;
        $employee = $this->mobile->read("employees", [], ["number" => $number]);

        $month = $post['request_date'];

        $start = strtotime($month . '-01');
        $finish = strtotime(date("Y-m-t", $start));
        $today = date("Y-m-d");

        // Inisialisasi Counter
        $permits = 0;
        $working = 0;
        $absence = 0;
        $late = 0;
        $notyet = 0;

        $details = array();

        // Perulangan dari tanggal 1 sampai akhir bulan
        for ($i = $start; $i <= $finish; $i += (60 * 60 * 24)) {
            $working_date = date('Y-m-d', $i);
            $day_of_week = date('w', $i); // 0=Minggu, 6=Sabtu

            // Query Kalender Hari Libur (Tidak ada perubahan, ini adalah sumber utama Holiday)
            //Working Calendar
            $this->db->select('description');
            $this->db->from('calendars');
            $this->db->where('trans_date', $working_date);
            $holiday = $this->db->get()->row();

            // Query Absensi
            $this->db->select("b.time_in, b.time_out");
            $this->db->from('employees a');
            $this->db->join('attandances b', 'a.number = b.number');
            $this->db->where('b.date_in', $working_date);
            $this->db->where('a.number', $number);
            $attandance = $this->db->get()->row();

            // Query Resignation
            //Resignation
            $this->db->select('*');
            $this->db->from('resignations');
            $this->db->where('employee_id', $employee->id);
            $this->db->where('resign_date <', $working_date);
            $resignation = $this->db->get()->row();

            // Query Izin/Cuti
            $this->db->select("a.note, d.name as permit_name");
            $this->db->from('permits a');
            $this->db->join('employees b', 'a.employee_id = b.id');
            $this->db->join('permit_types d', 'a.permit_type_id = d.id');
            $this->db->where('b.number', $number);
            $this->db->where('a.permit_date', $working_date);
            $permit = $this->db->get()->row();

            // Query Shift (Diambil yang paling awal)
            $this->db->select("d.start, d.end, d.days, d.tolerance, c.id as shift_id");
            $this->db->from('employees a');
            $this->db->join('shift_employees b', 'a.id = b.employee_id');
            $this->db->join('shifts c', 'c.id = b.shift_id');
            $this->db->join('shift_details d', 'd.shift_id = c.id');
            $this->db->where('a.number', $number);
            $this->db->order_by('d.start', 'ASC');
            $shift = $this->db->get()->row();

            // Query Change Day
            $change_day = $this->mobile->read("change_days", ["*"], ["employee_id" => $employee->id, "start" => $working_date]);
            if (!$change_day) {
                $change_day = $this->mobile->read("change_days", ["*"], ["employee_id" => $employee->id, "end" => $working_date]);
            }

            // Tentukan hari weekend standar berdasarkan shift
            $isWeekend = false;
            if (@$shift->days == "5") {
                $isWeekend = in_array($day_of_week, [0, 6]);
            } else {
                $isWeekend = ($day_of_week == 0); // Standar 6 hari kerja, Minggu weekend
            }

            $status = "";
            $remarks = "";
            $color = "red";

            // ====================================================================
            // --- LOGIKA PENENTUAN STATUS & COUNTER (FINAL) ---
            // ====================================================================

            // 1. NOT JOIN YET
            if (strtotime($employee->date_sign) > $i) {
                $status = "NOT JOIN YET";
                if ($working_date < $today) {
                    $absence++;
                }
                $color = "pink";

                // 2. RESIGN (Tidak dihitung)
            } elseif (!empty($resignation)) {
                $status = "RESIGN";
                $remarks = $resignation->remarks;
                $color = "grey";

                // 3. HOLIDAY/HARI LIBUR (Prioritas Pengecekan)
            } elseif (!empty($holiday)) {
                if (!empty($attandance->time_in)) {
                    // RULE 1: Holiday dengan Absensi Masuk (Selalu ON TIME)
                    $status = "ON TIME";
                    $remarks = $holiday->description . " Working";
                    $working++;
                    $color = "green";
                } else {
                    // Holiday tanpa Absensi (Skip Counter)
                    $status = "HOLIDAY";
                    $remarks = $holiday->description;
                    $color = "red";
                }

                // 4. CHANGE DAY (Tukar Hari Kerja/Libur)
            } elseif (!empty($change_day) && empty($attandance->time_in)) {
                $status = "CHANGE DAY";
                $remarks = $change_day->remarks;
                $color = "purple";

                // 5. WEEKEND (DIPINDAHKAN KE ATAS NOT YET)
            } elseif ($isWeekend) {
                if (!empty($attandance->time_in)) {
                    $shift_days = @$shift->days;

                    if ($shift_days == "5" || ($shift_days == "6" && $day_of_week == 0)) {
                        $status = "ON TIME";
                        $remarks = "Weekend Working (Shift Days: " . $shift_days . ")";
                        $working++;
                        $color = "green";
                    } elseif ($shift_days == "6" && $day_of_week == 6 && !empty($shift) && !empty($shift->start)) {
                        if (strtotime($attandance->time_in) > strtotime($shift->start)) {
                            $status = "LATE";
                            $late++;
                            $remarks = "Masuk: " . $attandance->time_in . " (Sabtu Shift)";
                            $color = "orange";
                        } else {
                            $status = "ON TIME";
                            $remarks = "Masuk: " . $attandance->time_in . " (Sabtu Shift)";
                            $color = "green";
                        }
                        $working++;
                    } else {
                        $status = "ON TIME (Weekend Fallback)";
                        $working++;
                        $color = "green";
                    }
                } else {
                    // Weekend tanpa Absen, di masa depan atau masa lalu, tetap WEEKEND
                    $status = "WEEKEND";
                    $color = "red";
                }

                // 6. FUTURE DATE (NOT YET) - HANYA BERLAKU JIKA BUKAN WEEKEND / HOLIDAY
            } elseif ($working_date > $today) {
                $status = "NOT YET";
                $notyet++;
                $color = "gray";

                // 7. PERMIT/IZIN/CUTI
            } elseif (!empty($permit)) {
                $status = "PERMIT";
                $remarks = $permit->note . " (" . $permit->permit_name . ")";
                $permits++;
                $color = "blue";

                // 8. HARI KERJA NORMAL (Ada absensi masuk)
            } elseif (!empty($attandance->time_in)) {
                $working++;

                if (empty($shift) || empty($shift->start)) {
                    $status = "ON TIME (UN SETTING)";
                    $remarks = "Check Shift Employee (No Shift Start Time)";
                    $color = "orange";

                    // LOGIKA KHUSUS SHIFT 6 HARI KERJA (Jumat & Sabtu)
                } elseif (@$shift->shift_id == '20241126000001' && in_array($day_of_week, [5, 6])) {
                    if (strtotime($attandance->time_in) > strtotime($shift->start)) {
                        $status = "LATE (6 Days Shift)";
                        $late++;
                        $remarks = "Masuk: " . $attandance->time_in . " (Sabtu/Jumat Shift)";
                        $color = "orange";
                    } else {
                        $status = "ON TIME (6 Days Shift)";
                        $remarks = "Masuk: " . $attandance->time_in;
                        $color = "green";
                    }
                } elseif (strtotime($attandance->time_in) > strtotime($shift->start)) {
                    $status = "LATE";
                    $late++;
                    $remarks = "Masuk: " . $attandance->time_in;
                    $color = "orange";
                } else {
                    $status = "ON TIME";
                    $remarks = "Masuk: " . $attandance->time_in;
                    $color = "green";
                }

                // 9. ABSENCE/TIDAK HADIR
            } elseif ($working_date < $today) {

                if (empty($shift) || empty($shift->start)) {
                    $status = "UN SETTING";
                    $remarks = "No Shift Setup & No Attendance";
                    $color = "grey";
                } else {
                    $status = "ABSENCE";
                    $absence++;
                    $remarks = "";
                    $color = "red";
                }
            }

            // ====================================================================
            // --- DATA DETAIL HARIAN ---
            // ====================================================================

            $details[] = array(
                "trans_date" => $working_date,
                // "shift_start" => @$shift->start,
                // "shift_end" => @$shift->end,
                "time_in" => @$attandance->time_in,
                "time_out" => @$attandance->time_out,
                "status" => $status,
                "color" => $color,
                "remarks" => $remarks,
            );
        }

        die(json_encode(array(
            "working" => $working,
            "permit" => $permits,
            "absence" => $absence,
            "notyet" => $notyet,
            "late" => $late,
            "details" => $details
        )));
    }
}
