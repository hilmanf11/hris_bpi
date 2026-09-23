<?php
header('Content-Type: application/json; charset=utf-8');

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
        $this->load->model('mobile');
    }

    //HALAMAN UTAMA
    public function index()
    {
        show_error("Cannot Process your request");
    }

    public function reads($api_key = "")
    {

        $post = $this->input->post();

        if ($api_key != "") {
            $user = $this->mobile->read("users", [], ["api_key" => $api_key]);


            if (!$post) {
                show_error("Request failed, date is empty");
            }

            $date = $post['date'];

            if ($user) {
                $attandances = $this->mobile->query("SELECT date_in, date_out, time_in, time_out, foto_in, foto_out FROM attandances WHERE number = '$user->number' AND date_in LIKE '$date%' ORDER BY date_in DESC");

                $datas = array();
                foreach ($attandances as $attandance) {
                    $folder_in = date("Ym", strtotime($attandance->date_in));
                    $folder_out = date("Ym", strtotime($attandance->date_out));

                    if(!empty($attandance->foto_in)){
                        if(substr($attandance->foto_in, -4) == "jpeg"){
                            $foto_in = 'assets/document/attendance/'.$folder_in.'/'. substr($attandance->foto_in, -15);
                        }else{
                            $foto_in = 'assets/document/attendance/'.$folder_in.'/'. substr($attandance->foto_in, -14);
                        }
                    }else{
                        $foto_in = null;
                    }
                    
                    if(!empty($attandance->foto_out)){
                        if(substr($attandance->foto_out, -4) == "jpeg"){
                            $foto_out = 'assets/document/attendance/'.$folder_out.'/'. substr($attandance->foto_out, -15);
                        }else{
                            $foto_out = 'assets/document/attendance/'.$folder_out.'/'. substr($attandance->foto_out, -14);
                        }
                    }else{
                        $foto_out = null;
                    }

                    $datas[] = array(
                        "date_in" => @$attandance->date_in,
                        "date_out" => @$attandance->date_out,
                        "time_in" => @$attandance->time_in,
                        "time_out" => @$attandance->time_out,
                        "foto_in" => $foto_in,
                        "foto_out" => $foto_out,
                    );
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

    public function list($api_key = "")
    {

        $post = $this->input->post();

        if ($api_key != "") {
            $user = $this->mobile->read("users", [], ["api_key" => $api_key]);

            if(!$post) {
                show_error("Request failed, date is empty");
            }

            $date = $post['date'];

            if ($user) {
                $attandances = $this->mobile->query("SELECT date_in, date_out, time_in, time_out, foto_in, foto_out FROM attandances WHERE number = '$user->number' AND date_in LIKE '$date%' ORDER BY date_in DESC");

                $datas = array();
                foreach ($attandances as $attandance) {
                    $folder_in = date("Ym", strtotime($attandance->date_in));
                    $folder_out = date("Ym", strtotime($attandance->date_out));

                    if(!empty($attandance->foto_in)){
                        if(substr($attandance->foto_in, -4) == "jpeg"){
                            $foto_in = 'assets/document/attendance/'.$folder_in.'/'. substr($attandance->foto_in, -15);
                        }else{
                            $foto_in = 'assets/document/attendance/'.$folder_in.'/'. substr($attandance->foto_in, -14);
                        }
                    }else{
                        $foto_in = null;
                    }
                    
                    if(!empty($attandance->foto_out)){
                        if(substr($attandance->foto_out, -4) == "jpeg"){
                            $foto_out = 'assets/document/attendance/'.$folder_out.'/'. substr($attandance->foto_out, -15);
                        }else{
                            $foto_out = 'assets/document/attendance/'.$folder_out.'/'. substr($attandance->foto_out, -14);
                        }
                    }else{
                        $foto_out = null;
                    }

                    $datas[] = array(
                        "date_in" => @$attandance->date_in,
                        "date_out" => @$attandance->date_out,
                        "time_in" => @$attandance->time_in,
                        "time_out" => @$attandance->time_out,
                        "foto_in" => $foto_in,
                        "foto_out" => $foto_out,
                    );
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

    public function location()
    {
        $this->apps = $this->load->database('apps', TRUE);

        if ($this->input->post()) {
            $post = $this->input->post();
            
            // $this->apps->where('link', $post['link']);
            // $group = $this->apps->get('companies')->row();

            $this->apps->select("*");
            $this->apps->from('companies');
            $this->apps->where('link', $post['link']);
            $companies = $this->apps->get()->result_array();

            die(json_encode(array(
                "title" => "Success",
                "message" => "Data Found",
                "theme" => "success",
                "results" => $companies
            )));
        } else {
            show_error("Cannot Process your request");
        }
    }

    public function locationUpdate()
    {
        $this->apps = $this->load->database('apps', TRUE);

        if ($this->input->post()) {
            $post = $this->input->post();

            $this->apps->where('number', $post['number']);
            $send = $this->apps->update("companies", ["latitude" => $post['latitude'], "longitude" => $post['longitude']]);

            if ($send) {
                echo json_encode(array("title" => "Good Job", "message" => "Data Updated Successfully", "theme" => "success"));
            } else {
                echo log_message('error', 'There is an error in your system or data');
            }
        } else {
            show_error("Cannot Process your request");
        }
    }

    public function createData($api_key = "")
    {
        if ($api_key != "") {
            $user = $this->mobile->read("users", [], ["api_key" => $api_key]);
            $folder = date("Ym");
            $path = 'assets/document/attendance/' . $folder;

            $time_now = date("H:i:s");
            $date_now = date("Y-m-d");

            if (!file_exists($path)) {
                mkdir($path, 0777, true);
            }

            if ($user) {
                if ($this->input->post()) {
                    $post = $this->input->post();

                    $employee = $this->mobile->read("employees", [], ["number" => $user->number]);

                    if ($post['date_in'] != "" && $post['time_in'] != "") {
                        if ($post['date_out'] != "" && $post['time_out'] != "") {
                            $date_in = $post['date_in'];
                            $foto = $this->mobile->upload('foto', ['png', 'jpg', 'jpeg'], 'assets/document/attendance/' . $folder . '/', ["number" => $user->number, "date_in" => $date_in], "attandances", "foto_out");

                            $this->db->select('*');
                            $this->db->from('attandances');
                            $this->db->where('number', $user->number);
                            $this->db->where("date_in = '$date_in'");
                            $this->db->where('(time_out IS NULL OR time_out = "" OR time_out = "00:00:00")', NULL, FALSE);
                            $this->db->order_by('id', 'DESC');
                            $attandance_in = $this->db->get()->row();

                            if (!$attandance_in) {
                                $this->db->select('*');
                                $this->db->from('attandances');
                                $this->db->where('number', $user->number);
                                $this->db->where('(time_out IS NULL OR time_out = "" OR time_out = "00:00:00")', NULL, FALSE);
                                $this->db->order_by('id', 'DESC');
                                $attandance_in = $this->db->get()->row();
                            }

                            if (!$attandance_in) {
                                // Cek apakah sudah pernah checkout (Multi-Out check)
                                $this->db->select('*');
                                $this->db->from('attandances');
                                $this->db->where('number', $user->number);
                                $this->db->where('time_out IS NOT NULL AND time_out != "" AND time_out != "00:00:00"', NULL, FALSE);
                                $this->db->order_by('id', 'DESC');
                                $last_checkout = $this->db->get()->row();

                                if ($last_checkout) {
                                    $last_out_ts = strtotime($last_checkout->date_out . ' ' . $last_checkout->time_out);
                                    if (abs(time() - $last_out_ts) <= 1800) {
                                        die(json_encode(array(
                                            "title" => "Success",
                                            "message" => "Attendance Out Completed",
                                            "theme" => "success"
                                        )));
                                    }
                                }

                                die(json_encode(array(
                                    "title" => "Info",
                                    "message" => "Anda sudah melakukan presensi pulang sebelumnya.",
                                    "theme" => "info"
                                )));
                            }

                            $data = array(
                                'number' => $user->number,
                                'date_out' => $date_now,
                                'time_out' => $time_now,
                                'foto_out' => @$foto,
                                'status' => $post['source'] ?? 'MOBILE',
                                'attandance_type' => 'MOBILE HRIS',
                            );

                            if ($this->mobile->update('attandances', ["id" => $attandance_in->id], $data, $user->username)) {
                                die(json_encode(array(
                                    "title" => "Success",
                                    "message" => "Attendance Out Completed",
                                    "theme" => "success"
                                )));
                            } else {
                                show_error("Attendance Out UnComplete");
                            }
                        } else {
                            // Cek apakah ada record presensi terbuka (seperti di Qrattandances)
                            $this->db->select('*');
                            $this->db->from('attandances');
                            $this->db->where('number', $user->number);
                            $this->db->where('(time_out IS NULL OR time_out = "" OR time_out = "00:00:00")', NULL, FALSE);
                            $this->db->order_by('id', 'DESC');
                            $open_attendance = $this->db->get()->row();

                            $current_ts = strtotime("$date_now $time_now");

                            if ($open_attendance) {
                                $checkin_time = (!empty($open_attendance->time_in) && $open_attendance->time_in != '00:00:00') 
                                    ? $open_attendance->time_in 
                                    : ($open_attendance->shift_start ?: '08:00:00');
                                $checkin_ts = strtotime($open_attendance->date_in . ' ' . $checkin_time);
                                $durasi_detik = $current_ts - $checkin_ts;

                                // Scan ulang masuk dalam 2 jam (Multi-In): pertahankan jam masuk awal
                                if ($durasi_detik >= 0 && $durasi_detik <= 7200) {
                                    die(json_encode(array(
                                        "title" => "Success",
                                        "message" => "Attendance In Completed",
                                        "theme" => "success",
                                        "results" => $open_attendance
                                    )));
                                }

                                $checkin_sec = $this->timeToSeconds($checkin_time);
                                $time_now_sec = $this->timeToSeconds($time_now);

                                // Cek apakah open_attendance lama ini sudah kadaluwarsa karena sudah masuk jendela shift baru:
                                // 1. Check in lama dari pagi/siang (< 14:00) dan sekarang sudah jam malam (>= 21:30 atau <= 04:00)
                                $is_expired_by_next_shift = false;
                                if ($checkin_sec < 50400 && ($time_now_sec >= 77400 || $time_now_sec <= 14400)) {
                                    $is_expired_by_next_shift = true;
                                }
                                // 2. Check in lama dari malam (>= 19:00) dan sekarang sudah siang (>= 12:00 dan < 19:00)
                                if ($checkin_sec >= 68400 && $time_now_sec >= 43200 && $time_now_sec < 68400) {
                                    $is_expired_by_next_shift = true;
                                }

                                // Jika durasi antara 2 jam sampai 16 jam DAN tidak kedaluwarsa oleh shift baru -> Otomatis diproses sebagai CHECK OUT
                                if ($durasi_detik > 7200 && $durasi_detik <= 57600 && !$is_expired_by_next_shift) {
                                    $foto = $this->mobile->upload('foto', ['png', 'jpg', 'jpeg'], 'assets/document/attendance/' . $folder . '/', ["number" => $user->number, "date_in" => $open_attendance->date_in], "attandances", "foto_out");

                                    $data = array(
                                        'date_out' => $date_now,
                                        'time_out' => $time_now,
                                        'foto_out' => @$foto,
                                        'status' => $post['source'] ?? 'MOBILE',
                                        'attandance_type' => 'MOBILE HRIS',
                                    );

                                    if ($this->mobile->update('attandances', ["id" => $open_attendance->id], $data, $user->username)) {
                                        die(json_encode(array(
                                            "title" => "Success",
                                            "message" => "Attendance Out Completed",
                                            "theme" => "success"
                                        )));
                                    } else {
                                        show_error("Attendance Out UnComplete");
                                    }
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
                            $active_shifts = $this->filterActiveShifts($shift_details, $date_now);

                            // Cek apakah karyawan SUDAH melakukan presensi masuk & pulang untuk shift ini (mencegah absen berkali-kali)
                            $this->db->from('attandances');
                            $this->db->where('number', $user->number);
                            $this->db->where('time_out IS NOT NULL AND time_out != "" AND time_out != "00:00:00"', null, false);
                            $this->db->where('(date_in = "' . $date_now . '" OR date_out = "' . $date_now . '")');
                            $this->db->order_by('id', 'DESC');
                            $this->db->limit(1);
                            $closed_attendance = $this->db->get()->row();

                            if ($closed_attendance) {
                                $checkout_ts = strtotime($closed_attendance->date_out . ' ' . $closed_attendance->time_out);
                                $elapsed_checkout = $current_ts - $checkout_ts;

                                // Shift normal (Shift 1/2/non-shift) yang date_in hari ini
                                if ($closed_attendance->date_in == $date_now) {
                                    die(json_encode(array(
                                        "title" => "Info",
                                        "message" => "Anda sudah melakukan presensi masuk dan pulang hari ini.",
                                        "theme" => "info",
                                        "results" => $closed_attendance
                                    )));
                                }
                                // Shift malam (Shift 3 atau Security Malam) yang baru checkout pagi ini dan belum masuk jendela shift malam berikutnya
                                elseif ($closed_attendance->date_out == $date_now && ($elapsed_checkout < 28800 || !$this->isNearUpcomingNightShift($time_now, $active_shifts, $closed_attendance->shift_start))) {
                                    die(json_encode(array(
                                        "title" => "Info",
                                        "message" => "Anda sudah menyelesaikan presensi untuk shift sebelumnya.",
                                        "theme" => "info",
                                        "results" => $closed_attendance
                                    )));
                                }
                            }

                            // Ambil presensi terakhir karyawan untuk mengecek apakah karyawan regu shift malam
                            $this->db->select('id, date_in, date_out, time_in, time_out, shift_start, shift_name');
                            $this->db->from('attandances');
                            $this->db->where('number', $user->number);
                            $this->db->order_by('id', 'DESC');
                            $this->db->limit(1);
                            $prev_att = $this->db->get()->row();

                            $night_shift = null;
                            foreach ($active_shifts as $s) {
                                $st_sec = $this->timeToSeconds($s->start);
                                $en_sec = $this->timeToSeconds($s->end);
                                if ($en_sec < $st_sec || $st_sec >= 68400 || $st_sec <= 14400) {
                                    $night_shift = $s;
                                    break;
                                }
                            }

                            $is_night_worker = false;
                            if ($prev_att && !empty($prev_att->shift_start)) {
                                $prev_st_sec = $this->timeToSeconds($prev_att->shift_start);
                                if ($prev_st_sec >= 68400 || $prev_st_sec <= 14400) {
                                    $is_night_worker = true;
                                }
                            }

                            $yesterday = date("Y-m-d", strtotime("-1 day", strtotime($date_now)));
                            $time_now_sec = $this->timeToSeconds($time_now);

                            // Cek apakah ada record kemarin
                            $this->db->from('attandances');
                            $this->db->where('number', $user->number);
                            $this->db->where('date_in', $yesterday);
                            $has_yesterday_record = ($this->db->count_all_results() > 0);

                            // SKENARIO LUPA ABSEN MASUK SHIFT MALAM:
                            // Jika karyawan regu shift malam, tap di pagi hari (jam 05:00 s/d 09:30) dan kemarin malam belum ada record:
                            // Maka tap pagi ini adalah CHECK OUT SHIFT MALAM (bukan check-in shift 1 pagi)!
                            if ($night_shift && $is_night_worker && !$has_yesterday_record && $time_now_sec >= 18000 && $time_now_sec <= 34200) {
                                $foto = $this->mobile->upload('foto', ['png', 'jpg', 'jpeg'], 'assets/document/attendance/' . $folder . '/');

                                $data = array(
                                    'number'          => $user->number,
                                    'date_in'         => $yesterday,
                                    'time_in'         => NULL, // Menandakan Lupa Presensi Masuk
                                    'date_out'        => $date_now,
                                    'time_out'        => $time_now,
                                    'foto_out'        => @$foto,
                                    'shift_start'     => @$night_shift->start,
                                    'shift_end'       => @$night_shift->end,
                                    'shift_name'      => @$night_shift->shift_name,
                                    'shift_detail'    => @$night_shift->name,
                                    'shift_days'      => @$night_shift->days,
                                    'status'          => $post['source'] ?? 'MOBILE',
                                    'attandance_type' => 'MOBILE HRIS',
                                );

                                if ($this->mobile->create('attandances', $data, $user->username)) {
                                    die(json_encode(array(
                                        "title" => "Success",
                                        "message" => "Attendance Out Completed (Lupa Presensi Masuk)",
                                        "theme" => "success",
                                        "results" => $night_shift
                                    )));
                                } else {
                                    show_error("Attendance Out UnComplete");
                                }
                            }

                            // Jika tidak ada absensi terbuka atau durasi > 16 jam -> Buat Record CHECK IN Baru
                            $foto = $this->mobile->upload('foto', ['png', 'jpg', 'jpeg'], 'assets/document/attendance/' . $folder . '/');

                            // Cari shift terdekat berdasarkan jam masuk (termasuk cross midnight & lembur awal)
                            $shift = $this->getClosestShiftDetail($post['time_in'], $active_shifts, $employee->id, $date_now);
                            if (!$shift && !empty($active_shifts)) {
                                $shift = $active_shifts[0];
                            }

                            $data = array(
                                'number' => $user->number,
                                'date_in' => $date_now,
                                'time_in' => $time_now,
                                'foto_in' => @$foto,
                                "shift_start" => @$shift->start,
                                "shift_end" => @$shift->end,
                                "shift_name" => @$shift->shift_name,
                                "shift_detail" => @$shift->name,
                                "shift_days" => @$shift->days,
                                'status' => $post['source'] ?? 'MOBILE',
                                'attandance_type' => 'MOBILE HRIS',
                            );

                            if ($this->mobile->create('attandances', $data, $user->username)) {
                                die(json_encode(array(
                                    "title" => "Success",
                                    "message" => "Attendance In Completed",
                                    "theme" => "success",
                                    "results" => $shift
                                )));
                            } else {
                                show_error("Attendance In UnComplete");
                            }
                        }
                    } else {
                        show_error("Cannot Process your request");
                    }
                } else {
                    show_error("Cannot Process your request");
                }
            } else {
                show_error("Api Key Invalid");
            }
        } else {
            show_error("Cannot Process your request");
        }
    }

    // Helper penentuan shift terdekat termasuk midnight cross dan lembur awal
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

            // Selisih jika melewati midnight (86400 detik)
            $diff = min($diff, 86400 - $diff);

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
