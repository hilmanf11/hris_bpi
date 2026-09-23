<?php
date_default_timezone_set("Asia/Bangkok");
defined('BASEPATH') or exit('No direct script access allowed');

#[\AllowDynamicProperties]
class Qrattandances extends CI_Controller
{
    private $secret_key = "BANS-HU-PLASTIC-2026";

    public function __construct()
    {
        parent::__construct();
        $this->load->helper('url');
        $this->load->helper(array('form', 'url'));
        $this->load->library('form_validation');
        $this->load->model('crud');
    }

    private function check_access() {
        $token_dari_url = $this->input->get('token');

        if (!$token_dari_url) {
            show_error('Akses Ditolak: Anda tidak memiliki akses.', 403);
            exit;
        }

        $decrypted = openssl_decrypt($token_dari_url, "AES-128-ECB", $this->secret_key);

        if ($decrypted !== "TERMINAL-BPI-2026") {
            show_error('Akses Ditolak: Kunci perangkat tidak valid.', 403);
            exit;
        }
    }

    public function index()
    {
        $this->load->view('attandance/qrattandances');
    }

    public function createData()
    {
        $post = $this->input->post();
        $number = isset($post['qrcode']) ? trim($post['qrcode']) : null;

        if (!$number) {
            die(json_encode(["title" => "Error", "message" => "Data QR tidak valid", "theme" => "error"]));
        }

        $this->db->select('e.*, d.name as department_name, u.username as username');
        $this->db->from('employees e');
        $this->db->join('departements d', 'd.id = e.departement_id', 'left'); 
        $this->db->join('users u', 'u.number = e.number', 'left'); 
        $this->db->where('e.number', $number);
        $employee = $this->db->get()->row();
        
        if (!$employee) {
            die(json_encode(["title" => "Error", "message" => "Silahkan coba lagi. ID Anda tidak terdaftar", "theme" => "error"]));
        }

        // Simpan Foto dari base64 diawal agar siap digunakan di semua skenario
        $image_base64 = isset($post['image']) ? $post['image'] : null;
        $saved_filename = null;

        if (!empty($image_base64) && strpos($image_base64, 'data:image') === 0) {
            $folder = FCPATH . 'assets/image/attandances/';
            if (!file_exists($folder)) {
                mkdir($folder, 0777, true);
            }

            $image_parts = explode(";base64,", $image_base64);
            if (isset($image_parts[1])) {
                $image_base64_decoded = base64_decode($image_parts[1]);
                $saved_filename = $number . '_' . date('Ymd_His') . '.jpg';
                $filepath = $folder . $saved_filename;
                file_put_contents($filepath, $image_base64_decoded);
            }
        }

        $date_now = date('Y-m-d');
        $time_now = date('H:i:s');
        $is_saturday = (date('N', strtotime($date_now)) == 6); // True jika hari Sabtu (N: 1=Senin, 6=Sabtu, 7=Minggu)

        $formatted_date = date('d/m/Y', strtotime($date_now));
        $formatted_time = date('h:i:s A', strtotime($time_now));
        
        $emp_name = isset($employee->name) ? $employee->name : '-';
        $emp_dept = isset($employee->department_name) ? $employee->department_name : '-'; 
        // Pastikan created_by valid di database (tidak boleh '-' agar tidak melanggar foreign key)
        $created_by = (!empty($employee->username)) ? $employee->username : 'admin'; 

        // =========================================================================
        // 1. AMBIL JADWAL MASTER SHIFT KARYAWAN
        // =========================================================================
        $this->db->select("d.start, d.end, d.briefing, d.tolerance, d.working, d.days, c.name as shift_name, d.name as shift_detail");
        $this->db->from('shift_employees b');
        $this->db->join('shifts c', 'c.id = b.shift_id');
        $this->db->join('shift_details d', 'd.shift_id = c.id');
        $this->db->where('b.employee_id', $employee->id);
        $all_shifts = $this->db->get()->result();

        if (empty($all_shifts)) {
            die(json_encode(["title" => "Error", "message" => "Jadwal shift karyawan tidak ditemukan", "theme" => "error"]));
        }

        // Normalisasi jam 24:00:00 -> 00:00:00
        foreach ($all_shifts as $s) {
            if ($s->start == '24:00:00') $s->start = '00:00:00';
            if ($s->end == '24:00:00') $s->end = '00:00:00';
        }

        // Filter detail shift aktif berdasarkan hari (Weekday vs Weekend)
        $active_shifts = [];
        if ($is_saturday) {
            // Pada hari Sabtu: Ambil detail WEEKEND dan pertahankan detail WEEKDAY yang tidak punya padanan WEEKEND
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
            // Hari kerja normal (Senin - Jumat): Abaikan detail WEEKEND
            foreach ($all_shifts as $s) {
                $working_type = isset($s->working) ? strtoupper($s->working) : 'WEEKDAY';
                if ($working_type !== 'WEEKEND') {
                    $active_shifts[] = $s;
                }
            }
        }

        if (empty($active_shifts)) {
            $active_shifts = $all_shifts;
        }

        // =========================================================================
        // 2. CEK STATUS ABSENSI TERBUKA / TERTUTUP DI DATABASE
        // =========================================================================
        $this->db->where('number', $number);
        $this->db->where('deleted', 0);
        $this->db->where('(time_out IS NULL OR time_out = "" OR time_out = "00:00:00")', NULL, FALSE);
        $this->db->order_by('id', 'DESC');
        $open_attendance = $this->db->get('attandances')->row();

        $this->db->where('number', $number);
        $this->db->where('deleted', 0);
        $this->db->where('time_out IS NOT NULL AND time_out != "" AND time_out != "00:00:00"', NULL, FALSE);
        $this->db->order_by('id', 'DESC');
        $closed_attendance = $this->db->get('attandances')->row();

        $is_multi_in  = false;
        $is_multi_out = false;
        $current_ts   = strtotime("$date_now $time_now");

        // Multi-In check (Scan ulang masuk dalam rentang 2 jam sejak tap masuk)
        if ($open_attendance && !empty($open_attendance->time_in) && $open_attendance->time_in !== '00:00:00') {
            $last_in_ts = strtotime($open_attendance->date_in . ' ' . $open_attendance->time_in);
            if (abs($current_ts - $last_in_ts) <= 7200) {
                $is_multi_in = true;
                // Ketentuan: Selalu pertahankan jam masuk (time_in) yang paling awal
                if ($current_ts < $last_in_ts) {
                    $update_in = [
                        'time_in' => $time_now,
                        'updated_by' => $created_by
                    ];
                    if ($saved_filename) {
                        $update_in['foto_in'] = $saved_filename;
                    }
                    $this->crud->update_nosession('attandances', ["id" => $open_attendance->id], $update_in, "no_log", $created_by);
                    $open_attendance->time_in = $time_now;
                }
            }
        }

        // Multi-Out check (Scan ulang pulang dalam rentang 30 menit sejak tap pulang terakhir)
        if (!$is_multi_in && $closed_attendance && !empty($closed_attendance->time_out)) {
            $last_out_ts = strtotime($closed_attendance->date_out . ' ' . $closed_attendance->time_out);
            if (($current_ts - $last_out_ts) >= 0 && ($current_ts - $last_out_ts) <= 1800) {
                $is_multi_out = true;
            }
        }

        // =========================================================================
        // 3. EKSEKUSI ABSENSI
        // =========================================================================

        if ($is_multi_in) {
            die(json_encode([
                "title" => "Success", "theme" => "success", "type" => "CHECK IN",
                "results" => [
                    "id"      => $number, "name" => $emp_name, "dept" => $emp_dept,
                    "time"    => date('h:i:s A', strtotime($open_attendance->time_in)),
                    "date"    => date('d/m/Y', strtotime($open_attendance->date_in)),
                    "shift"   => ($open_attendance->shift_name ?? '-') . " (" . ($open_attendance->shift_detail ?? '-') . ")",
                    "foto_in" => $open_attendance->foto_in ?? $saved_filename
                ]
            ]));

        } elseif ($is_multi_out) {
            $data = [
                'date_out'   => $date_now, 
                'time_out'   => $time_now,
                'updated_by' => $created_by
            ];
            if ($saved_filename) {
                $data['foto_out'] = $saved_filename;
            }
            $this->crud->update_nosession('attandances', ["id" => $closed_attendance->id], $data, "no_log", $created_by);

            die(json_encode([
                "title" => "Success",
                "theme" => "success",
                "type" => "CHECK OUT",
                "results" => [
                    "id"       => $number,
                    "name"     => $emp_name,
                    "dept"     => $emp_dept,
                    "time"     => $formatted_time, 
                    "date"     => $formatted_date,
                    "shift"    => ($closed_attendance->shift_name ?? '-') . " (" . ($closed_attendance->shift_detail ?? '-') . ")",
                    "foto_out" => $saved_filename
                ]
            ]));

        } elseif ($open_attendance) {
            // Check Out Normal
            if (empty($open_attendance->time_in) || $open_attendance->time_in == '00:00:00') {
                $checkin_ts = strtotime($open_attendance->date_in . ' ' . ($open_attendance->shift_start ?: '08:00:00'));
            } else {
                $checkin_ts = strtotime($open_attendance->date_in . ' ' . $open_attendance->time_in);
            }
            $durasi_detik = $current_ts - $checkin_ts;

            // Toleransi max stay 16 jam (57.600 detik) untuk menangani shift 12 jam (Security) + lembur
            if ($durasi_detik > 57600) {
                // Record lama sudah expired / terbengkalai, lanjut buat record presensi baru
                goto resolve_new_attendance;
            } else {
                $data = [
                    'date_out'   => $date_now, 
                    'time_out'   => $time_now,
                    'updated_by' => $created_by
                ];
                if ($saved_filename) {
                    $data['foto_out'] = $saved_filename;
                }
                $this->crud->update_nosession('attandances', ["id" => $open_attendance->id], $data, "no_log", $created_by);

                die(json_encode([
                    "title" => "Success",
                    "theme" => "success",
                    "type" => "CHECK OUT",
                    "results" => [
                        "id"       => $number,
                        "name"     => $emp_name,
                        "dept"     => $emp_dept,
                        "time"     => $formatted_time, 
                        "date"     => $formatted_date,
                        "shift"    => ($open_attendance->shift_name ?? '-') . " (" . ($open_attendance->shift_detail ?? '-') . ")",
                        "foto_out" => $saved_filename
                    ]
                ]));
            }

        } else {
            resolve_new_attendance:
            // Tidak ada absen terbuka -> Tentukan Shift & Aksi secara otomatis (IN vs OUT Lupa IN)
            $resolution     = $this->resolve_shift_action($active_shifts, $time_now, $date_now, $is_saturday, $employee->id);
            $selected_shift = $resolution['shift'];
            $action_type    = $resolution['action']; // 'IN' atau 'OUT'
            $date_in_target = $resolution['date_in'];
            $date_out_pred  = $resolution['date_out'];

            if ($action_type === 'OUT') {
                // KASUS LUPA ABSEN MASUK -> Langsung catat Check Out (time_in = NULL)
                $data = [
                    'number'          => $number,
                    'date_in'         => $date_in_target,
                    'time_in'         => NULL, // Menandakan Lupa Tap Masuk
                    'date_out'        => $date_now,
                    'time_out'        => $time_now,
                    'shift_start'     => $selected_shift->start,
                    'shift_end'       => $selected_shift->end,
                    'shift_name'      => $selected_shift->shift_name,
                    'shift_detail'    => $selected_shift->shift_detail,
                    'shift_days'      => isset($selected_shift->days) ? $selected_shift->days : NULL,
                    'created_by'      => $created_by,
                    'attandance_type' => 'QR Code',
                    'deleted'         => 0
                ];
                if ($saved_filename) {
                    $data['foto_out'] = $saved_filename;
                }

                $this->crud->create_nosession('attandances', $data, "no_log", $created_by);

                die(json_encode([
                    "title" => "Success",
                    "theme" => "success",
                    "type" => "CHECK OUT",
                    "results" => [
                        "id"       => $number,
                        "name"     => $emp_name,
                        "dept"     => $emp_dept,
                        "time"     => $formatted_time, 
                        "date"     => $formatted_date,
                        "shift"    => ($selected_shift->shift_name ?? '-') . " (" . ($selected_shift->shift_detail ?? '-') . ")",
                        "foto_out" => $saved_filename
                    ]
                ]));

            } else {
                // KASUS CHECK IN BARU (Normal / Datang Awal / Terlambat)
                $data = [
                    'number'          => $number,
                    'date_in'         => $date_in_target,
                    'time_in'         => $time_now,
                    'date_out'        => $date_out_pred,
                    'shift_start'     => $selected_shift->start,
                    'shift_end'       => $selected_shift->end,
                    'shift_name'      => $selected_shift->shift_name,
                    'shift_detail'    => $selected_shift->shift_detail,
                    'shift_days'      => isset($selected_shift->days) ? $selected_shift->days : NULL,
                    'created_by'      => $created_by,
                    'attandance_type' => 'QR Code',
                    'deleted'         => 0
                ];
                if ($saved_filename) {
                    $data['foto_in'] = $saved_filename;
                }
                
                $this->crud->create_nosession('attandances', $data, "no_log", $created_by);

                die(json_encode([
                    "title" => "Success", "theme" => "success", "type" => "CHECK IN",
                    "results" => [
                        "id"      => $number, "name" => $emp_name, "dept" => $emp_dept,
                        "time"    => $formatted_time, "date" => $formatted_date,
                        "shift"   => ($selected_shift->shift_name ?? '-') . " (" . ($selected_shift->shift_detail ?? '-') . ")",
                        "foto_in" => $saved_filename
                    ]
                ]));
            }
        }
    }

    /**
     * Helper Penentuan Shift & Aksi Mandiri (Otomatis 100% tanpa setting harian HR)
     * Mendukung penyesuaian patokan IN jika ada lembur awal di cash_carries
     */
    private function resolve_shift_action($shifts, $time_now, $date_now, $is_saturday, $employee_id = null)
    {
        $yesterday = date("Y-m-d", strtotime("-1 day", strtotime($date_now)));
        $tomorrow  = date("Y-m-d", strtotime("+1 day", strtotime($date_now)));
        $cur_hour  = date('H:i:s', strtotime($time_now));

        $is_multi_shift = count($shifts) > 1;

        // -------------------------------------------------------------
        // KASUS 1: KARYAWAN NON-SHIFT (Hanya 1 shift aktif)
        // -------------------------------------------------------------
        if (!$is_multi_shift) {
            $s = $shifts[0];
            $ot_nonshift = $this->get_early_overtime($employee_id, $date_now, $s->start);
            $start_val   = ($ot_nonshift && !empty($ot_nonshift->start)) ? $ot_nonshift->start : $s->start;
            $start_ts    = strtotime($start_val);
            $end_val     = ($s->end == '00:00:00') ? '24:00:00' : $s->end;
            $end_ts      = strtotime($end_val);
            
            // Titik tengah (midpoint) jam kerja disesuaikan jika ada lembur awal
            $mid_ts = $start_ts + (($end_ts - $start_ts) / 2);
            $mid_hi = date('H:i:s', $mid_ts);

            if ($cur_hour < $mid_hi) {
                $date_out = (strtotime($s->end) < strtotime($s->start)) ? $tomorrow : $date_now;
                return [
                    'shift'    => $s,
                    'action'   => 'IN',
                    'date_in'  => $date_now,
                    'date_out' => $date_out
                ];
            } else {
                return [
                    'shift'    => $s,
                    'action'   => 'OUT',
                    'date_in'  => $date_now,
                    'date_out' => $date_now
                ];
            }
        }

        // -------------------------------------------------------------
        // KASUS 2: KARYAWAN SECURITY (2 Shift 12 Jam: 07:00-19:00 & 19:00-07:00)
        // -------------------------------------------------------------
        $is_security = false;
        foreach ($shifts as $s) {
            if (stripos($s->shift_name, 'SECURITY') !== false || stripos($s->shift_detail, 'SECURITY') !== false) {
                $is_security = true;
                break;
            }
        }

        if ($is_security) {
            $sec_pagi = null;
            $sec_malam = null;
            foreach ($shifts as $s) {
                if (strtotime($s->start) < strtotime("12:00:00")) {
                    $sec_pagi = $s;
                } else {
                    $sec_malam = $s;
                }
            }

            $ot_sec_pagi  = $sec_pagi  ? $this->get_early_overtime($employee_id, $date_now, $sec_pagi->start)  : null;
            $ot_sec_malam = $sec_malam ? $this->get_early_overtime($employee_id, $date_now, $sec_malam->start) : null;

            $sec_pagi_in_start  = $ot_sec_pagi  ? date('H:i:s', strtotime('-75 minutes', strtotime($ot_sec_pagi->start)))  : '06:30:00';
            $sec_malam_in_start = $ot_sec_malam ? date('H:i:s', strtotime('-75 minutes', strtotime($ot_sec_malam->start))) : '18:45:00';

            if ($cur_hour >= $sec_pagi_in_start && $cur_hour <= '13:00:00') {
                return ['shift' => $sec_pagi ?: $shifts[0], 'action' => 'IN', 'date_in' => $date_now, 'date_out' => $date_now];
            } elseif ($cur_hour >= '16:30:00' && $cur_hour < $sec_malam_in_start) {
                return ['shift' => $sec_pagi ?: $shifts[0], 'action' => 'OUT', 'date_in' => $date_now, 'date_out' => $date_now];
            } elseif ($cur_hour >= $sec_malam_in_start || $cur_hour <= '01:00:00') {
                return ['shift' => $sec_malam ?: $shifts[0], 'action' => 'IN', 'date_in' => $date_now, 'date_out' => $tomorrow];
            } elseif ($cur_hour >= '04:30:00' && $cur_hour < $sec_pagi_in_start) {
                return ['shift' => $sec_malam ?: $shifts[0], 'action' => 'OUT', 'date_in' => $yesterday, 'date_out' => $date_now];
            }
        }

        // -------------------------------------------------------------
        // KASUS 3: KARYAWAN 3 SHIFT / MULTI SHIFT REGULAR
        // -------------------------------------------------------------
        $shift_pagi  = null;
        $shift_siang = null;
        $shift_malam = null;
        $shift_sabtu = null;

        foreach ($shifts as $s) {
            $is_sabtu_detail = (stripos($s->shift_detail, 'SABTU') !== false) || (isset($s->working) && strtoupper($s->working) === 'WEEKEND');
            if ($is_sabtu_detail) {
                $shift_sabtu = $s;
            }

            $st = $s->start;
            // Shift Malam: start 00:00 / 23:00 / 24:00
            if ($st == '00:00:00' || strtotime($st) >= strtotime('22:00:00') || strtotime($st) < strtotime('04:00:00')) {
                $shift_malam = $s;
            }
            // Shift Siang / Sore: start 14:00 - 18:00
            elseif (strtotime($st) >= strtotime('14:00:00') && strtotime($st) < strtotime('20:00:00')) {
                $shift_siang = $s;
            }
            // Shift Pagi: start 07:00 - 10:00
            else {
                if (!$is_sabtu_detail || !$shift_pagi) {
                    $shift_pagi = $s;
                }
            }
        }

        // Fallback jika ada slot yang kosong
        if (!$shift_pagi)  $shift_pagi  = $shifts[0];
        if (!$shift_siang) $shift_siang = (isset($shifts[1])) ? $shifts[1] : $shifts[0];
        if (!$shift_malam) $shift_malam = (isset($shifts[2])) ? $shifts[2] : $shifts[0];

        // Cek apakah karyawan memiliki lembur awal di cash_carries
        $ot_siang = $shift_siang ? $this->get_early_overtime($employee_id, $date_now, $shift_siang->start) : null;
        $ot_pagi  = $shift_pagi  ? $this->get_early_overtime($employee_id, $date_now, $shift_pagi->start)  : null;
        $ot_malam = $shift_malam ? $this->get_early_overtime($employee_id, $date_now, $shift_malam->start) : null;

        // Tentukan ambang batas check-in awal (buka jendela presensi masuk)
        $siang_in_start = ($ot_siang && !empty($ot_siang->start)) ? date('H:i:s', strtotime('-75 minutes', strtotime($ot_siang->start))) : '14:45:00';
        $pagi_in_start  = ($ot_pagi  && !empty($ot_pagi->start))  ? date('H:i:s', strtotime('-75 minutes', strtotime($ot_pagi->start)))  : '06:45:00';
        $malam_in_start = ($ot_malam && !empty($ot_malam->start)) ? date('H:i:s', strtotime('-75 minutes', strtotime($ot_malam->start))) : '22:45:00';

        // Penanganan Khusus Hari Sabtu (6 HKW: 08:00 - 13:00)
        if ($is_saturday && $shift_sabtu) {
            if ($cur_hour >= '12:30:00' && $cur_hour <= '14:30:00') {
                return ['shift' => $shift_sabtu, 'action' => 'OUT', 'date_in' => $date_now, 'date_out' => $date_now];
            }
        }

        // Matriks Jendela Waktu 24 Jam
        // 1. Rentang Datang Awal / Lembur Awal Shift Malam (sejak $malam_in_start s/d 23:59:59)
        if ($cur_hour >= $malam_in_start) {
            return [
                'shift'    => $shift_malam,
                'action'   => 'IN',
                'date_in'  => $date_now,
                'date_out' => $tomorrow
            ];
        }
        // 2. Rentang 00:00:00 - 04:00:00 : CHECK IN Shift Malam (Normal / Telat)
        elseif ($cur_hour <= '04:00:00') {
            return [
                'shift'    => $shift_malam,
                'action'   => 'IN',
                'date_in'  => $date_now,
                'date_out' => $date_now
            ];
        }
        // 3. Rentang 04:01:00 s/d sebelum $pagi_in_start : CHECK OUT Shift Malam (Lupa IN) -> date_in = Kemarin
        elseif ($cur_hour > '04:00:00' && $cur_hour < $pagi_in_start) {
            return [
                'shift'    => $shift_malam,
                'action'   => 'OUT',
                'date_in'  => $yesterday,
                'date_out' => $date_now
            ];
        }
        // 4. Rentang Datang Awal / Normal Shift Pagi ($pagi_in_start s/d 12:59:59)
        elseif ($cur_hour >= $pagi_in_start && $cur_hour < min('13:00:00', $siang_in_start)) {
            $pagi_target = ($is_saturday && $shift_sabtu) ? $shift_sabtu : $shift_pagi;
            return [
                'shift'    => $pagi_target,
                'action'   => 'IN',
                'date_in'  => $date_now,
                'date_out' => $date_now
            ];
        }
        // 5. Rentang $siang_in_start s/d 21:59:59 : CHECK IN Shift Siang (Datang Awal / Lembur Awal / Normal / Telat)
        elseif ($cur_hour >= $siang_in_start && $cur_hour < '22:00:00') {
            $is_siang_overnight = (strtotime($shift_siang->end) < strtotime($shift_siang->start) || $shift_siang->end == '00:00:00' || $shift_siang->end == '24:00:00');
            $date_out_pred      = $is_siang_overnight ? $tomorrow : $date_now;
            return [
                'shift'    => $shift_siang,
                'action'   => 'IN',
                'date_in'  => $date_now,
                'date_out' => $date_out_pred
            ];
        }
        // 6. Rentang 13:00:00 s/d sebelum $siang_in_start : CHECK OUT Shift Pagi (Lupa IN)
        elseif ($cur_hour >= '13:00:00' && $cur_hour < $siang_in_start) {
            $pagi_target = ($is_saturday && $shift_sabtu) ? $shift_sabtu : $shift_pagi;
            return [
                'shift'    => $pagi_target,
                'action'   => 'OUT',
                'date_in'  => $date_now,
                'date_out' => $date_now
            ];
        }
        // 7. Rentang 22:00:00 s/d sebelum $malam_in_start : CHECK OUT Shift Siang (Lupa IN)
        else {
            return [
                'shift'    => $shift_siang,
                'action'   => 'OUT',
                'date_in'  => $date_now,
                'date_out' => $date_now
            ];
        }
    }

    /**
     * Cari data lembur awal di tabel cash_carries untuk karyawan pada tanggal tertentu.
     * Mengembalikan record lembur jika terdapat lembur yang dimulai sebelum shift dan terhubung ke shift.
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
}