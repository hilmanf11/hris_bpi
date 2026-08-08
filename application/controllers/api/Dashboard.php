<?php
header('Content-Type: application/json; charset=utf-8');

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
        $this->load->model('mobile');
        $this->load->model('emails');
    }

    //HALAMAN UTAMA
    public function index()
    {
        show_error("Cannot Process your request");
    }

    // public function attendance($api_key = "")
    // {
    //     $this->output->delete_cache();
    //     if (!empty($api_key)) {
    //         $user = $this->mobile->read("users", [], ["api_key" => $api_key]);

    //         $employee = $this->mobile->read("employees", [], ["number" => $user->number]);

    //         if ($user) {
    //             $attandances = $this->mobile->read("attandances", [], ["number" => $user->number, "date_in" => date("Y-m-d")], 1, "", "", ["number", "date_in"]);

    //             $this->db->select("d.start, d.end, d.days, d.working, d.tolerance, c.name, d.name as shift_name");
    //             $this->db->from('shift_employees b');
    //             $this->db->join('shifts c', 'c.id = b.shift_id');
    //             $this->db->join('shift_details d', 'd.shift_id = c.id');
    //             $this->db->where('b.employee_id', $employee->id);
    //             $shift = $this->db->get()->row();

    //             if (!empty($attandances->foto_in)) {
    //                 $folder = date("Ym", strtotime($attandances->date_in));
    //                 $foto_in = 'assets/document/attendance/' . $folder . '/' . $attandances->foto_in;
    //             } else {
    //                 $foto_in = null;
    //             }

    //             if (!empty($attandances->foto_out)) {
    //                 $folder = date("Ym", strtotime($attandances->date_out));
    //                 $foto_out = 'assets/document/attendance/' . $folder . '/' . $attandances->foto_out;
    //             } else {
    //                 $foto_out = null;
    //             }

    //             die(json_encode(array(
    //                 "title" => "Success",
    //                 "message" => "Data Found",
    //                 "theme" => "success",
    //                 "results" => array(
    //                     "start" => @$shift->start,
    //                     "end" => @$shift->end,
    //                     "shift_name" => @$shift->shift_name,
    //                     "shift_detail" => @$shift->name,
    //                     "date_in" => @$attandances->date_in,
    //                     "date_out" => @$attandances->date_out,
    //                     "time_in" => @$attandances->time_in,
    //                     "time_out" => @$attandances->time_out,
    //                     "foto_in" => $foto_in,
    //                     "foto_out" => $foto_out,
    //                 )
    //             )));
    //         } else {
    //             show_error("Api Key Invalid");
    //         }
    //     } else {
    //         show_error("Cannot Process your request");
    //     }
    // }

    public function settingFeatures() 
    {
        $data = [
            "is_attendance" => 'Y',
            "is_overtime" => 'Y',
            "is_add_overtime" => 'Y',
            "is_cancel_overtime" => 'Y',
            "is_change_days" => 'Y',
            "is_add_change_days" => 'Y',
            "is_cancel_change_days" => 'Y',
            "is_permits" => 'Y',
            "is_add_permit" => 'Y',
            "is_cancel_permit" => 'Y',
        ];

        die(json_encode(array(
            "title" => "Success",
            "message" => "Data Found",
            "theme" => "success",
            "results" => $data,
        )));
    }

    public function attendance($api_key = "")
    {
        $this->output->delete_cache();
        if (!empty($api_key)) {
            $user = $this->mobile->read("users", [], ["api_key" => $api_key]);

            $employee = $this->mobile->read("employees", [], ["number" => $user->number]);

            if ($user) {
                // $attandances = $this->mobile->read("attandances", [], ["number" => $user->number, "date_in" => date("Y-m-d")], 1, "", "", ["number", "date_in"]);

                // Cari attendance yang belum checkout
                $this->db->from('attandances');
                $this->db->where('number', $user->number);
                $this->db->where('time_out IS NULL', null, false);
                $this->db->order_by('date_in', 'DESC');
                $this->db->order_by('time_in', 'DESC');
                $this->db->limit(1);
                $attandances = $this->db->get()->row();

                // Jika ada attendance yang belum checkout
                if ($attandances) {

                    // Hitung waktu shift selesai
                    $shiftEnd = new DateTime($attandances->date_in . ' ' . $attandances->shift_end);

                    // Jika shift melewati tengah malam
                    if ($attandances->shift_end <= $attandances->shift_start) {
                        $shiftEnd->modify('+1 day');
                    }

                    // Tambah toleransi checkout 2 jam
                    $shiftEnd->modify('+2 hours');

                    $now = new DateTime();

                    // Jika sudah melewati batas checkout
                    if ($now > $shiftEnd) {
                        $attandances = null;
                    }
                }

                // Kalau tidak ada attendance aktif, ambil attendance hari ini
                if (!$attandances) {
                    $attandances = $this->mobile->read(
                        "attandances", [], ["number" => $user->number, "date_in" => date("Y-m-d")], 1, "", "", ["number", "date_in"]
                    );
                }

                $this->db->select("d.start, d.end, d.days, d.working, d.tolerance, c.name, d.name as shift_name");
                $this->db->from('shift_employees b');
                $this->db->join('shifts c', 'c.id = b.shift_id');
                $this->db->join('shift_details d', 'd.shift_id = c.id');
                $this->db->where('b.employee_id', $employee->id);
                $shift = $this->db->get()->row();

                if (!empty($attandances->foto_in)) {
                    $folder = date("Ym", strtotime($attandances->date_in));
                    $foto_in = 'assets/document/attendance/' . $folder . '/' . $attandances->foto_in;
                } else {
                    $foto_in = null;
                }

                if (!empty($attandances->foto_out)) {
                    $folder = date("Ym", strtotime($attandances->date_out));
                    $foto_out = 'assets/document/attendance/' . $folder . '/' . $attandances->foto_out;
                } else {
                    $foto_out = null;
                }

                die(json_encode(array(
                    "title" => "Success",
                    "message" => "Data Found",
                    "theme" => "success",
                    "results" => array(
                        "start" => @$shift->start,
                        "end" => @$shift->end,
                        "shift_name" => @$shift->shift_name,
                        "shift_detail" => @$shift->name,
                        "date_in" => @$attandances->date_in,
                        "date_out" => @$attandances->date_out,
                        "time_in" => @$attandances->time_in,
                        "time_out" => @$attandances->time_out,
                        "foto_in" => $foto_in,
                        "foto_out" => $foto_out,
                    )
                )));
            } else {
                show_error("Api Key Invalid");
            }
        } else {
            show_error("Cannot Process your request");
        }
    }

    public function users($api_key = "")
    {
        $this->output->delete_cache();

        if (!empty($api_key)) {
            $user = $this->mobile->read("users", [], ["api_key" => $api_key]);
            $employee = $this->mobile->read("employees", [], ["number" => $user->number]);
            $position = $this->mobile->read("positions", [], ["id" => $employee->position_id]);

            if ($user) {
                if ($user->avatar == null || $user->avatar == "") {
                    $avatar = "assets/image/users/default.png";
                } else {
                    $avatar = "assets/image/users/" . $user->avatar;
                }

                die(json_encode(array(
                    "title" => "Success",
                    "message" => "Data Found",
                    "theme" => "success",
                    "results" => array(
                        "departement_id" => $user->departement_id,
                        "employee_id" => $user->number,
                        "name" => $user->name,
                        "position" => $user->position,
                        "access" => $position->access,
                        "avatar" => $avatar,
                    )
                )));
            } else {
                show_error("Api Key Invalid");
            }
        } else {
            show_error("Cannot Process your request");
        }
    }

    public function cutOff()
    {
        $send = $this->mobile->read('cutoff', [], [], "", "start", "desc");
        echo json_encode($send);
    }

    public function dayOff($api_key = "")
    {
        $this->output->delete_cache();
        if (!empty($api_key)) {
            $user = $this->mobile->read("users", [], ["api_key" => $api_key]);
            $today = date("Y-m-d");
            $departement_id = $user->departement_id;

            if ($user) {
                $permits = $this->mobile->query("SELECT a.* FROM permits a JOIN employees b ON a.employee_id = b.id  WHERE a.permit_date = '$today' and b.departement_id = '$departement_id'");

                if (count($permits) > 0) {
                    $datas = array();

                    foreach ($permits as $permit) {
                        $employee = $this->mobile->read("employees", [], ["id" => $permit->employee_id]);
                        $position = $this->mobile->read("positions", [], ["id" => $employee->position_id]);
                        $permit_type = $this->mobile->read("permit_types", [], ["id" => $permit->permit_type_id]);

                        if ($employee->image_profile == "") {
                            $avatar = "assets/image/users/default.png";
                        } else {
                            $avatar = "assets/image/users/" . $employee->image_profile;
                        }

                        $datas[] = array(
                            "image" => $avatar,
                            "name" => $employee->name,
                            "permit" => $permit_type->name,
                            "description" => $permit->note,
                            "position" => $position->name
                        );
                    }

                    die(json_encode(array(
                        "title" => "Success",
                        "message" => "Data Found",
                        "theme" => "success",
                        "results" => $datas
                    )));
                } else {
                    die(json_encode(array(
                        "title" => "Success",
                        "message" => "Data Found",
                        "theme" => "success",
                        "results" => []
                    )));
                }
            } else {
                show_error("Api Key Invalid");
            }
        } else {
            show_error("Cannot Process your request");
        }
    }

    public function dayOffV2($api_key = "")
    {
        $this->output->delete_cache();
        if (!empty($api_key)) {
            $user = $this->mobile->read("users", [], ["api_key" => $api_key]);
            $today = date("Y-m-d");
            $departement_id = @$user->departement_id;
            
            if ($user) {
                $employees = $this->mobile->query("SELECT * FROM employees WHERE departement_id = '$departement_id' and `status` = '0'");

                $datas = array();
                foreach ($employees as $employee) {
                    if($employee->number == $user->number){
                        continue; // Skip the current user
                    }

                    $attandance = $this->mobile->read("attandances", [], ["date_in" => $today, "number" => $employee->number]);
                    $permit = $this->mobile->read("permits", [], ["permit_date" => $today, "employee_id" => $employee->id]);
                    $permit_type = $this->mobile->read("permit_types", [], ["id" => @$permit->permit_type_id]);

                    if(!empty($employee->image_profile)){
                        if(substr($employee->image_profile, -4) == "jpeg"){
                            $avatar = "assets/image/employee/profile/" . substr($employee->image_profile, -15);
                        }else{
                            $avatar = "assets/image/employee/profile/" . substr($employee->image_profile, -14);
                        }
                    }else{
                        $avatar = "assets/image/users/default.png";
                    }

                    //Shift and Setting Group
                    $tolerance_hour_min = date("H:i:s", strtotime('-2 Hour', strtotime(@$attandance->time_in)));
                    $tolerance_hour_plus = date("H:i:s", strtotime('+2 Hour', strtotime(@$attandance->time_in)));
                    $this->db->select("d.start, d.end, d.days, d.working, d.tolerance, c.name, d.name as shift_name");
                    $this->db->from('shift_employees b');
                    $this->db->join('shifts c', 'c.id = b.shift_id');
                    $this->db->join('shift_details d', 'd.shift_id = c.id');
                    $this->db->where('b.employee_id', $employee->id);
                    if (@$attandance->time_in > "23:00:00") {
                        $this->db->where("d.start >= '$tolerance_hour_min'");
                    } elseif (@$attandance->time_in != "") {
                        $this->db->where("d.start >= '$tolerance_hour_min' and d.start <= '$tolerance_hour_plus'");
                    }
                    $shift = $this->db->get()->row();

                    if (@$attandance->time_in == null) {
                        if (@$permit->employee_id != null) {
                            $attandance_status = @strtoupper($permit_type->name);
                            $color = "blue";
                        } else {
                            $attandance_status = "ABSENCE";
                            $color = "red";
                        }
                    } else {
                        if (@$shift->start >= @$attandance->time_in) {
                            $attandance_status = "ON TIME";
                            $color = "green";
                        } elseif (@$shift->start == null) {
                            $attandance_status = "UN SETTING";
                            $color = "red";
                        } elseif (@$shift->start <= @$attandance->time_in) {
                            $attandance_status = "LATE";
                            $color = "orange";
                        } else {
                            $attandance_status = "ERROR";
                            $color = "red";
                        }
                    }

                    if($attandance_status != "ABSENCE"){
                        $datas[] = array(
                            "image" => $avatar,
                            "name" => $employee->name,
                            "time_in" => @$attandance->time_in,
                            "time_out" => @$attandance->time_out,
                            "description" => $attandance_status,
                            "color" => $color
                        );
                    }
                }

                die(json_encode(array(
                    "title" => "Success",
                    "message" => "Data Found",
                    "theme" => "success",
                    "results" => $datas
                )));
            } else {
                show_error("Api Key Invalid");
            }
        } else {
            show_error("Cannot Process your request");
        }
    }

    public function attandanceSummary($api_key = "")
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

        $month = date('Y-m');

        $number = $user->number;
        $employee = $this->mobile->read("employees", [], ["number" => $number]);

        if (!$employee) {
            show_error("Employee not found");
            return;
        }

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

            // // Query Shift (Diambil yang paling awal)
            // $this->db->select("d.start, d.end, d.days, d.tolerance, c.id as shift_id");
            // $this->db->from('employees a');
            // $this->db->join('shift_employees b', 'a.id = b.employee_id');
            // $this->db->join('shifts c', 'c.id = b.shift_id');
            // $this->db->join('shift_details d', 'd.shift_id = c.id');
            // $this->db->where('a.number', $number);
            // $this->db->order_by('d.start', 'ASC');
            // $shift = $this->db->get()->row();

            // ==========================================================
            // GET ALL SHIFT DETAILS EMPLOYEE
            // ==========================================================
            $this->db->select("d.start, d.end, d.days, d.tolerance, c.id as shift_id");
            $this->db->from('employees a');
            $this->db->join('shift_employees b', 'a.id = b.employee_id');
            $this->db->join('shifts c', 'c.id = b.shift_id');
            $this->db->join('shift_details d', 'd.shift_id = c.id');
            $this->db->where('a.number', $number);

            $shift_details = $this->db->get()->result();


            // ==========================================================
            // FIND CLOSEST SHIFT BASED ON TIME IN
            // ==========================================================
            $shift = $this->getClosestShiftDetail(
                @$attandance->time_in,
                $shift_details
            );

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
                        $lateStatus = $this->isLate( $working_date, $attandance->time_in, $shift->start, $shift->end);

                        if ($lateStatus) {

                            $status = "LATE";
                            $late++;
                            $remarks = "Shift: " . $shift->start . " - " . $shift->end . " (Sabtu Shift)";
                            $color = "orange";

                        } else {

                            $status = "ON TIME";

                            $remarks = "Shift: " . $shift->start . " - " . $shift->end ." (Sabtu Shift)";

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

                } else {

                    $lateStatus = $this->isLate($working_date, $attandance->time_in, $shift->start, $shift->end);

                    if ($lateStatus) {

                        $status = "LATE";
                        $late++;
                        $remarks = "Shift: " . $shift->start . " - " . $shift->end;
                        $color = "orange";

                    } else {

                        $status = "ON TIME";
                        $remarks = "Shift: " . $shift->start . " - " . $shift->end;
                        $color = "green";
                    }
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

    // ATTENDANCE NEW WITH DATE
    public function attendanceSummaryByDate($api_key = "")
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

        // Contoh input: 2025-10
        $month = $post['request_date'];

        $number = $user->number;
        $employee = $this->mobile->read("employees", [], ["number" => $number]);

        if (!$employee) {
            show_error("Employee not found");
            return;
        }

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

            // // Query Shift (Diambil yang paling awal)
            // $this->db->select("d.start, d.end, d.days, d.tolerance, c.id as shift_id");
            // $this->db->from('employees a');
            // $this->db->join('shift_employees b', 'a.id = b.employee_id');
            // $this->db->join('shifts c', 'c.id = b.shift_id');
            // $this->db->join('shift_details d', 'd.shift_id = c.id');
            // $this->db->where('a.number', $number);
            // $this->db->order_by('d.start', 'ASC');
            // $shift = $this->db->get()->row();

            // ==========================================================
            // GET ALL SHIFT DETAILS EMPLOYEE
            // ==========================================================
            $this->db->select("d.start, d.end, d.days, d.tolerance, c.id as shift_id");
            $this->db->from('employees a');
            $this->db->join('shift_employees b', 'a.id = b.employee_id');
            $this->db->join('shifts c', 'c.id = b.shift_id');
            $this->db->join('shift_details d', 'd.shift_id = c.id');
            $this->db->where('a.number', $number);

            $shift_details = $this->db->get()->result();


            // ==========================================================
            // FIND CLOSEST SHIFT BASED ON TIME IN
            // ==========================================================
            $shift = $this->getClosestShiftDetail(
                @$attandance->time_in,
                $shift_details
            );

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
                        $lateStatus = $this->isLate( $working_date, $attandance->time_in, $shift->start, $shift->end);

                        if ($lateStatus) {

                            $status = "LATE";
                            $late++;
                            $remarks = "Shift: " . $shift->start . " - " . $shift->end . " (Sabtu Shift)";
                            $color = "orange";

                        } else {

                            $status = "ON TIME";

                            $remarks = "Shift: " . $shift->start . " - " . $shift->end ." (Sabtu Shift)";

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

                } else {

                    $lateStatus = $this->isLate($working_date, $attandance->time_in, $shift->start, $shift->end);

                    if ($lateStatus) {

                        $status = "LATE";
                        $late++;
                        $remarks = "Shift: " . $shift->start . " - " . $shift->end;
                        $color = "orange";

                    } else {

                        $status = "ON TIME";
                        $remarks = "Shift: " . $shift->start . " - " . $shift->end;
                        $color = "green";
                    }
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


    // Helpers untuk check shift terdekan
    private function getClosestShiftDetail($timeIn, $shiftDetails)
    {
        if (empty($timeIn) || empty($shiftDetails)) {
            return null;
        }

        $attendanceSeconds = $this->timeToSeconds($timeIn);

        $closestShift = null;
        $closestDiff = PHP_INT_MAX;

        foreach ($shiftDetails as $detail) {

            if (empty($detail->start)) {
                continue;
            }

            $startSeconds = $this->timeToSeconds($detail->start);

            // Selisih waktu biasa
            $diff = abs($attendanceSeconds - $startSeconds);

            // Selisih jika melewati midnight
            $diff = min(
                $diff,
                86400 - $diff
            );

            if ($diff < $closestDiff) {
                $closestDiff = $diff;
                $closestShift = $detail;
            }
        }

        return $closestShift;
    }

    private function timeToSeconds($time)
    {
        $parts = explode(':', $time);

        return
            ((int) $parts[0] * 3600) +
            ((int) $parts[1] * 60) +
            ((int) ($parts[2] ?? 0));
    }

    private function isLate($workingDate, $timeIn, $shiftStart, $shiftEnd)
    {
        $attendanceSeconds = $this->timeToSeconds($timeIn);

        /*
        * 24:00 = midnight.
        */
        if ($shiftStart === '24:00:00') {

            // 00:00 tepat waktu
            if ($attendanceSeconds == 0) {
                return false;
            }

            /*
            * Time antara 00:00 - 23:59
            * Untuk summary berdasarkan trans_date:
            * 23:36 -> sebelum midnight => ON TIME
            * 00:25 -> setelah midnight  => LATE
            */

            // Jika jam masuk masih malam tanggal sebelumnya
            if ($attendanceSeconds > 12 * 3600) {
                return false;
            }

            // 00:01 - 23:59 dianggap sudah lewat 00:00
            return true;
        }

        /*
        * Shift biasa
        */
        $shiftStartSeconds = $this->timeToSeconds($shiftStart);

        return $attendanceSeconds > $shiftStartSeconds;
    }
}
