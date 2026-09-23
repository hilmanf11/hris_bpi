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
            "version" => '1.0.1',
            "link" => '', 
            "info" => 'Terdapat versi terbaru, silakan update.',
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
                $this->db->where('(time_out IS NULL OR time_out = "" OR time_out = "00:00:00")', null, false);
                $this->db->order_by('date_in', 'DESC');
                $this->db->order_by('time_in', 'DESC');
                $this->db->limit(1);
                $attandances = $this->db->get()->row();

                // Jika ada attendance yang belum checkout
                if ($attandances) {
                    $checkin_time = (!empty($attandances->time_in) && $attandances->time_in != '00:00:00') 
                        ? $attandances->time_in 
                        : ($attandances->shift_start ?: '08:00:00');
                    $checkin_ts = strtotime($attandances->date_in . ' ' . $checkin_time);
                    $durasi_detik = time() - $checkin_ts;

                    $checkin_sec = $this->timeToSeconds($checkin_time);
                    $time_now_sec = $this->timeToSeconds(date("H:i:s"));

                    // Cek apakah open attendance ini sudah kedaluwarsa karena sudah masuk jendela shift baru:
                    // 1. Check-in pagi/siang (< 14:00) tapi sekarang sudah jam malam (>= 21:30 atau <= 04:00)
                    $is_expired_by_next_shift = false;
                    if ($checkin_sec < 50400 && ($time_now_sec >= 77400 || $time_now_sec <= 14400)) {
                        $is_expired_by_next_shift = true;
                    }
                    // 2. Check-in malam (>= 19:00) tapi sekarang sudah siang (>= 12:00 dan < 19:00)
                    if ($checkin_sec >= 68400 && $time_now_sec >= 43200 && $time_now_sec < 68400) {
                        $is_expired_by_next_shift = true;
                    }

                    // Toleransi max stay 16 jam atau expired oleh shift baru
                    if ($durasi_detik > 57600 || $durasi_detik < 0 || $is_expired_by_next_shift) {
                        $attandances = null;
                    }
                }

                // Ambil daftar detail shift karyawan
                $this->db->select("d.start, d.end, d.days, d.working, d.tolerance, c.name, d.name as shift_name");
                $this->db->from('shift_employees b');
                $this->db->join('shifts c', 'c.id = b.shift_id');
                $this->db->join('shift_details d', 'd.shift_id = c.id');
                $this->db->where('b.employee_id', $employee->id);
                $shift_details = $this->db->get()->result();

                // Filter shift aktif sesuai hari (weekday vs weekend/Sabtu untuk 6 HK)
                $active_shifts = $this->filterActiveShifts($shift_details, date("Y-m-d"));

                // Kalau tidak ada attendance aktif, cari apakah sudah ada presensi yang selesai hari ini
                if (!$attandances) {
                    $date_today = date("Y-m-d");
                    $time_now = date("H:i:s");
                    $current_ts = time();

                    $this->db->from('attandances');
                    $this->db->where('number', $user->number);
                    $this->db->where('time_out IS NOT NULL AND time_out != "" AND time_out != "00:00:00"', null, false);
                    $this->db->where('(date_in = "' . $date_today . '" OR date_out = "' . $date_today . '")');
                    $this->db->order_by('id', 'DESC');
                    $this->db->limit(1);
                    $last_closed = $this->db->get()->row();

                    if ($last_closed) {
                        $checkout_ts = strtotime($last_closed->date_out . ' ' . $last_closed->time_out);
                        $elapsed_after_checkout = $current_ts - $checkout_ts;

                        // Jika shift normal hari ini (Shift 1/2/non-shift) sudah checkout -> tampilkan sudah selesai
                        if ($last_closed->date_in == $date_today) {
                            $attandances = $last_closed;
                        } 
                        // Jika shift malam (Shift 3 atau Security Malam) yang checkout pagi ini:
                        // Tampilkan sudah selesai selama dalam masa istirahat (kurang dari 8 jam ATAU belum masuk jendela shift baru)
                        elseif ($last_closed->date_out == $date_today) {
                            if ($elapsed_after_checkout < 28800 || !$this->isNearUpcomingNightShift($time_now, $active_shifts, $last_closed->shift_start)) {
                                $attandances = $last_closed;
                            }
                        }
                    }
                }

                $ref_time = !empty($attandances->time_in) ? $attandances->time_in : date("H:i:s");
                $shift = $this->getClosestShiftDetail($ref_time, $active_shifts, $employee->id, date("Y-m-d"));
                if (!$shift && !empty($active_shifts)) {
                    $shift = $active_shifts[0];
                }

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
                        "start" => !empty($attandances->shift_start) ? $attandances->shift_start : @$shift->start,
                        "end" => !empty($attandances->shift_end) ? $attandances->shift_end : @$shift->end,
                        "shift_name" => !empty($attandances->shift_name) ? $attandances->shift_name : @$shift->shift_name,
                        "shift_detail" => !empty($attandances->shift_detail) ? $attandances->shift_detail : @$shift->name,
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

    public function attendanceV2($api_key = "")
    {
        $this->output->delete_cache();

        if (empty($api_key)) {
            show_error("Cannot Process your request");
            return;
        }

        // =========================================================
        // 1. VALIDASI USER
        // =========================================================

        $user = $this->mobile->read("users", [], ["api_key" => $api_key]);

        if (!$user) {
            show_error("Api Key Invalid");
            return;
        }

        $employee = $this->mobile->read("employees", [], ["number" => $user->number]);

        if (!$employee) {
            show_error("Employee not found");
            return;
        }


        // =========================================================
        // 2. CARI ATTENDANCE YANG BELUM CHECKOUT
        //
        // Jangan hanya cari hari ini.
        // Karena attendance shift malam bisa:
        //
        // tgl 18 16:30 -> tgl 19 00:10
        //
        // atau:
        //
        // tgl 18 23:10 -> tgl 19 08:12
        //
        // =========================================================

        $this->db->from('attandances');
        $this->db->where('number',$user->number);
        $this->db->where('time_out IS NULL',null,false);
        $this->db->where('created_date >=',date('Y-m-d H:i:s', strtotime('-7 days'))); // ambil 7 hari terakhir
        $this->db->order_by('date_in','DESC');
        $this->db->order_by('time_in','DESC');
        $this->db->limit(1);
        $attandances = $this->db->get()->row();

        // =========================================================
        // 3. CEK APAKAH ATTENDANCE MASIH AKTIF
        // =========================================================

        if ($attandances) {
            /*
            * Ambil data shift dari attendance
            */
            $shiftStart = $attandances->shift_start;
            $shiftEnd   = $attandances->shift_end;

            $dateIn = $attandances->date_in;
            $timeIn = $attandances->time_in;

            /*
            * =====================================================
            * NORMALISASI 24:00
            *
            * 24:00 sebenarnya adalah 00:00 hari berikutnya.
            * =====================================================
            */

            if ($shiftEnd == '24:00:00') {

                $shiftEndTime = '00:00:00';

            } else {

                $shiftEndTime = $shiftEnd;
            }

            /*
            * =====================================================
            * TENTUKAN TANGGAL SHIFT END
            * =====================================================
            *
            * Default:
            *
            * date_in + shift_end
            *
            * Tetapi ada beberapa kondisi yang harus +1 hari.
            */

            $shiftEndDate = $dateIn;

            /*
            * -----------------------------------------------------
            * CASE 1
            *
            * Shift:
            * 16:00 -> 24:00
            *
            * 24:00 berarti:
            * 00:00 hari berikutnya
            *
            * -----------------------------------------------------
            */

            if ($shiftEnd == '24:00:00') {
                $shiftEndDate = date('Y-m-d', strtotime($dateIn . ' +1 day'));
            }


            /*
            * -----------------------------------------------------
            * CASE 2
            *
            * Shift:
            * 16:00 -> 04:00
            *
            * End lebih kecil dari start.
            * Berarti melewati midnight.
            * -----------------------------------------------------
            */

            elseif ( !empty($shiftStart) && $shiftEndTime <= $shiftStart) {
                $shiftEndDate = date('Y-m-d',strtotime($dateIn . ' +1 day'));
            }

            /*
            * -----------------------------------------------------
            * CASE 3
            *
            * Shift:
            * 00:00 -> 08:00
            *
            * Tetapi user check-in:
            *
            * tgl 18 23:10
            *
            * Maka shift end harus:
            *
            * tgl 19 08:00
            *
            * bukan:
            *
            * tgl 18 08:00
            *
            * -----------------------------------------------------
            */

            elseif (!empty($timeIn) && $timeIn > $shiftEndTime) {
                $shiftEndDate = date('Y-m-d',strtotime($dateIn . ' +1 day'));
            }

            /*
            * =====================================================
            * BUAT DATETIME SHIFT END
            * =====================================================
            */

            $shiftEnd = new DateTime($shiftEndDate . ' ' . $shiftEndTime);

            /*
            * =====================================================
            * TOLERANSI CHECKOUT 2 JAM
            * =====================================================
            */

            $shiftEndTolerance = clone $shiftEnd;
            $shiftEndTolerance->modify('+2 hours');

            /*
            * Waktu sekarang
            */

            $now = new DateTime();

            /*
            * =====================================================
            * CEK APAKAH MASIH DALAM WAKTU CHECKOUT
            * =====================================================
            *
            * Contoh:
            *
            * Shift:
            * 16:00 - 24:00
            *
            * End:
            * 19 00:00
            *
            * Tolerance:
            * 19 02:00
            *
            * Sekarang:
            * 19 00:10
            *
            * MASIH AKTIF
            *
            *
            * Contoh kedua:
            *
            * Shift:
            * 00:00 - 08:00
            *
            * Check-in:
            * 18 23:10
            *
            * End:
            * 19 08:00
            *
            * Tolerance:
            * 19 10:00
            *
            * Sekarang:
            * 19 08:12
            *
            * MASIH AKTIF
            */

            if ($now > $shiftEndTolerance) {
                $attandances = null;
            }
        }


        // =========================================================
        // 4. KALAU TIDAK ADA ATTENDANCE AKTIF
        //    CARI ATTENDANCE HARI INI
        // =========================================================

        if (!$attandances) {
            $attandances = $this->mobile->read( "attandances", [], [ "number" => $user->number, "date_in" => date("Y-m-d")],1);
        }

        // =========================================================
        // 5. TENTUKAN SHIFT
        // =========================================================

        $shift = null;

        /*
        * Kalau attendance ditemukan,
        * gunakan shift yang tersimpan pada attendance.
        *
        * Jangan query shift_details->row()
        * karena employee bisa mempunyai 3 shift.
        */

        if ($attandances) {
            $shift = (object) [
                'start'         => $attandances->shift_start,
                'end'           => $attandances->shift_end,
                'shift_name'    => @$attandances->shift_name,
                'name'          => @$attandances->shift_detail,
            ];
        } else {
            $this->db->select("d.start, d.end, d.days, d.working, d.tolerance, c.name, d.name as shift_name");
            $this->db->from('shift_employees b');
            $this->db->join('shifts c', 'c.id = b.shift_id');
            $this->db->join('shift_details d', 'd.shift_id = c.id');
            $this->db->where('b.employee_id', $employee->id);
            $shift = $this->db->get()->row();
        }


        // =========================================================
        // 6. FOTO MASUK
        // =========================================================

        if ($attandances && !empty($attandances->foto_in)) {
            $folder = date("Ym", strtotime($attandances->date_in));
            $foto_in = 'assets/document/attendance/' . $folder . '/' . $attandances->foto_in;
        } else {
            $foto_in = null;
        }

        // =========================================================
        // 7. FOTO KELUAR
        // =========================================================

        if ( $attandances && !empty($attandances->foto_out)) {
            $folder = date( "Ym", strtotime($attandances->date_out));
            $foto_out = 'assets/document/attendance/' . $folder . '/' . $attandances->foto_out;
        } else {
            $foto_out = null;
        }


        // =========================================================
        // 8. NORMALISASI SHIFT UNTUK RESPONSE
        //
        // Database:
        // 24:00:00
        //
        // API:
        // 00:00:00
        // =========================================================

        if ($shift) {
            if ( isset($shift->start) && $shift->start == '24:00:00') {
                $shift->start = '00:00:00';
            }

            if (isset($shift->end) && $shift->end == '24:00:00') {
                $shift->end = '00:00:00';
            }
        }


        // =========================================================
        // 9. RESPONSE
        // =========================================================

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
                    $this->db->select("d.start, d.end, d.days, d.working, d.tolerance, c.name, d.name as shift_name");
                    $this->db->from('shift_employees b');
                    $this->db->join('shifts c', 'c.id = b.shift_id');
                    $this->db->join('shift_details d', 'd.shift_id = c.id');
                    $this->db->where('b.employee_id', $employee->id);
                    $emp_shifts = $this->db->get()->result();
                    $active_emp_shifts = $this->filterActiveShifts($emp_shifts, $today);

                    $shift = null;
                    if (!empty($attandance->shift_start)) {
                        foreach ($active_emp_shifts as $es) {
                            if ($es->start == $attandance->shift_start) {
                                $shift = $es;
                                break;
                            }
                        }
                    }
                    if (!$shift && !empty($attandance->time_in)) {
                        $shift = $this->getClosestShiftDetail($attandance->time_in, $active_emp_shifts);
                    }

                    if (@$attandance->time_in == null) {
                        if (@$permit->employee_id != null) {
                            $attandance_status = @strtoupper($permit_type->name);
                            $color = "blue";
                        } else {
                            $attandance_status = "ABSENCE";
                            $color = "red";
                        }
                    } else {
                        if ($shift == null || empty($shift->start)) {
                            $attandance_status = "UN SETTING";
                            $color = "red";
                        } else {
                            $start_clean = ($shift->start === '24:00:00') ? '00:00:00' : $shift->start;
                            $st_sec = $this->timeToSeconds($start_clean);
                            $in_sec = $this->timeToSeconds($attandance->time_in);

                            if ($st_sec == 0 && $in_sec >= 79200) {
                                $diff = - (86400 - $in_sec);
                            } else {
                                $diff = $in_sec - $st_sec;
                            }

                            if ($diff <= 0) {
                                $attandance_status = "ON TIME";
                                $color = "green";
                            } else {
                                $attandance_status = "LATE";
                                $color = "orange";
                            }
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
                $shift_details,
                $employee->id,
                $working_date
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
                $shift_details,
                $employee->id,
                $working_date
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


    // Helpers untuk check shift terdekat termasuk lembur awal
    private function getClosestShiftDetail($timeIn, $shiftDetails, $employee_id = null, $date = null)
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

            $start_val = ($detail->start === '24:00:00') ? '00:00:00' : $detail->start;

            // Jika ada lembur awal di cash_carries, jadikan jam mulai lembur sebagai patokan IN
            if (!empty($employee_id) && !empty($date)) {
                $ot_early = $this->get_early_overtime($employee_id, $date, $detail->start);
                if ($ot_early && !empty($ot_early->start)) {
                    $start_val = ($ot_early->start === '24:00:00') ? '00:00:00' : $ot_early->start;
                }
            }

            $startSeconds = $this->timeToSeconds($start_val);

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

    /**
     * Cari data lembur awal di tabel cash_carries untuk karyawan pada tanggal tertentu.
     */
    private function get_early_overtime($employee_id, $date, $shift_start)
    {
        if (empty($employee_id) || empty($date) || empty($shift_start)) {
            return null;
        }

        $this->db->select('*');
        $this->db->from('cash_carries');
        $this->db->where('employee_id', $employee_id);
        $this->db->where('trans_date', $date);
        $this->db->where('deleted', 0);
        $overtimes = $this->db->get()->result();

        if (empty($overtimes)) {
            return null;
        }

        $shift_start_norm = ($shift_start == '24:00:00') ? '00:00:00' : $shift_start;
        $shift_start_ts   = strtotime("1970-01-01 " . $shift_start_norm);

        $matched_ot = null;
        $earliest_start_ts = PHP_INT_MAX;

        foreach ($overtimes as $ot) {
            if (empty($ot->start)) continue;

            $ot_start_norm = ($ot->start == '24:00:00') ? '00:00:00' : $ot->start;
            $ot_end_norm   = !empty($ot->end) ? (($ot->end == '24:00:00') ? '00:00:00' : $ot->end) : $ot_start_norm;

            $ot_start_ts = strtotime("1970-01-01 " . $ot_start_norm);
            $ot_end_ts   = strtotime("1970-01-01 " . $ot_end_norm);

            // Shift siang/pagi/reguler (bukan midnight 00:00:00)
            if ($shift_start_norm !== '00:00:00') {
                if ($ot_start_ts < $shift_start_ts) {
                    $gap_to_shift = $shift_start_ts - $ot_end_ts;
                    if ($ot_end_ts >= $shift_start_ts || $gap_to_shift <= 3600 || ($shift_start_ts - $ot_start_ts <= 28800)) {
                        if ($ot_start_ts < $earliest_start_ts) {
                            $earliest_start_ts = $ot_start_ts;
                            $matched_ot = $ot;
                        }
                    }
                }
            } else {
                // Shift malam (start 00:00:00 / midnight)
                if ($ot_start_ts >= strtotime("1970-01-01 18:00:00")) {
                    if ($ot_start_ts < $earliest_start_ts) {
                        $earliest_start_ts = $ot_start_ts;
                        $matched_ot = $ot;
                    }
                }
            }
        }

        return $matched_ot;
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

    // Filter detail shift aktif berdasarkan hari (Weekday vs Weekend) sesuai logika Qrattandances
    private function filterActiveShifts($all_shifts, $date)
    {
        if (empty($all_shifts)) {
            return [];
        }

        $is_saturday = (date('N', strtotime($date)) == 6);
        $active_shifts = [];

        if ($is_saturday) {
            foreach ($all_shifts as $s) {
                $working_type = isset($s->working) ? strtoupper($s->working) : 'WEEKDAY';
                if ($working_type === 'WEEKEND') {
                    $active_shifts[] = $s;
                } else {
                    $has_weekend_counterpart = false;
                    foreach ($all_shifts as $other) {
                        if (isset($other->working) && strtoupper($other->working) === 'WEEKEND' && $other->start == $s->start) {
                            $has_weekend_counterpart = true;
                            break;
                        }
                    }
                    if (!$has_weekend_counterpart) {
                        $active_shifts[] = $s;
                    }
                }
            }
        } else {
            foreach ($all_shifts as $s) {
                $working_type = isset($s->working) ? strtoupper($s->working) : 'WEEKDAY';
                if ($working_type !== 'WEEKEND') {
                    $active_shifts[] = $s;
                }
            }
        }

        return !empty($active_shifts) ? $active_shifts : $all_shifts;
    }

    // Cek apakah waktu saat ini sudah mendekati waktu mulai shift malam berikutnya (maks 2.5 jam sebelum shift)
    private function isNearUpcomingNightShift($timeNow, $activeShifts, $lastShiftStart = null)
    {
        $targetStart = null;

        if (!empty($lastShiftStart)) {
            $start_val = ($lastShiftStart === '24:00:00') ? '00:00:00' : $lastShiftStart;
            $start_sec = $this->timeToSeconds($start_val);
            if ($start_sec >= 61200 || $start_sec <= 14400) {
                $targetStart = $start_val;
            }
        }

        if (empty($targetStart) && !empty($activeShifts)) {
            foreach ($activeShifts as $shift) {
                if (empty($shift->start)) continue;
                $st = ($shift->start === '24:00:00') ? '00:00:00' : $shift->start;
                $en = ($shift->end === '24:00:00') ? '00:00:00' : $shift->end;
                $st_sec = $this->timeToSeconds($st);
                $en_sec = $this->timeToSeconds($en);

                if ($en_sec < $st_sec || $st_sec >= 61200 || $st_sec <= 14400) {
                    $targetStart = $st;
                    break;
                }
            }
        }

        if (empty($targetStart)) {
            return false;
        }

        $nowSec = $this->timeToSeconds($timeNow);
        $targetSec = $this->timeToSeconds($targetStart);

        $diff_before = ($targetSec - $nowSec + 86400) % 86400;

        // Maksimal 2.5 jam sebelum shift mulai ($diff_before <= 9000)
        // Atau maksimal 4 jam setelah shift mulai ($diff_before >= 72000)
        if ($diff_before <= 9000 || $diff_before >= 72000) {
            return true;
        }

        return false;
    }
}
