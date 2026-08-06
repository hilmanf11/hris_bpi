<?php
date_default_timezone_set("Asia/Bangkok");
defined('BASEPATH') or exit('No direct script access allowed');

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
        $number = isset($post['qrcode']) ? $post['qrcode'] : null;

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

        $date_now = date('Y-m-d');
        $time_now = date('H:i:s');
        $is_saturday = (date('N', strtotime($date_now)) == 6); // True jika hari Sabtu (N: 1=Senin, 6=Sabtu, 7=Minggu)

        $formatted_date = date('d/m/Y', strtotime($date_now));
        $formatted_time = date('H:i:s A', strtotime($time_now));
        
        $emp_name = isset($employee->name) ? $employee->name : '-';
        $emp_dept = isset($employee->department_name) ? $employee->department_name : '-'; 
        $created_by = isset($employee->username) ? $employee->username : '-'; 

        // =========================================================================
        // 1. AMBIL JADWAL SHIFT KARYAWAN
        // =========================================================================
        $this->db->select("d.start, d.end, c.name as shift_name, d.name as shift_detail");
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

        // =========================================================================
        // 2. CEK STATUS ABSENSI TERBUKA / TERTUTUP
        // =========================================================================
        $this->db->where('number', $number);
        $this->db->where('(date_out IS NULL OR date_out = "" OR date_out = "0000-00-00")', NULL, FALSE);
        $this->db->order_by('id', 'DESC');
        $open_attendance = $this->db->get('attandances')->row();

        $this->db->where('number', $number);
        $this->db->where('date_out IS NOT NULL AND date_out != "" AND date_out != "0000-00-00"', NULL, FALSE);
        $this->db->order_by('id', 'DESC');
        $closed_attendance = $this->db->get('attandances')->row();

        $is_multi_in  = false;
        $is_multi_out = false;
        $current_ts   = strtotime("$date_now $time_now");

        // Multi-In check (Scan ulang masuk dalam rentang 2 jam)
        if ($open_attendance && $open_attendance->date_in == $date_now) {
            $last_in_ts = strtotime($open_attendance->date_in . ' ' . $open_attendance->time_in);
            if (($current_ts - $last_in_ts) <= 7200 && ($current_ts - $last_in_ts) >= 0) {
                $is_multi_in = true;
            }
        }

        // Multi-Out check (Scan ulang pulang dalam rentang 12 jam di hari yang sama)
        if (!$is_multi_in && $closed_attendance && $closed_attendance->date_out == $date_now) {
            $last_out_ts = strtotime($closed_attendance->date_out . ' ' . $closed_attendance->time_out);
            if (($current_ts - $last_out_ts) >= 0 && ($current_ts - $last_out_ts) < 43200) {
                $is_multi_out = true;
            }
        }

        // Simpan Foto
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

        // =========================================================================
        // 3. EKSEKUSI ABSENSI
        // =========================================================================

        if ($is_multi_in) {
            die(json_encode([
                "title" => "Success", "theme" => "success", "type" => "CHECK IN",
                "results" => [
                    "id"      => $number, "name" => $emp_name, "dept" => $emp_dept,
                    "time"    => date('H:i:s A', strtotime($open_attendance->time_in)),
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
            $this->crud->update_nosession('attandances', ["id" => $closed_attendance->id], $data, "", $created_by);
            $target = $closed_attendance;

        } elseif ($open_attendance) {
            $checkin_ts = strtotime($open_attendance->date_in . ' ' . $open_attendance->time_in);
            $durasi_detik = $current_ts - $checkin_ts;

            // Toleransi max stay 15 jam (54.000 detik) untuk menangani shift 12 jam (Security) + lembur
            if ($durasi_detik > 54000) {
                goto force_checkin;
            } else {
                $data = [
                    'date_out'   => $date_now, 
                    'time_out'   => $time_now,
                    'updated_by' => $created_by
                ];
                if ($saved_filename) {
                    $data['foto_out'] = $saved_filename;
                }
                $this->crud->update_nosession('attandances', ["id" => $open_attendance->id], $data, "", $created_by);
                $target = $open_attendance;
            }

        } else {
            force_checkin:
            $selected_shift = null;

            // Matching window shift (+/- 2 Jam)
            foreach ($all_shifts as $s) {
                $is_sabtu_detail = (stripos($s->shift_detail, 'SABTU') !== false);
                
                // Filter: Jika hari ini Sabtu, utamakan detail Sabtu. Jika bukan Sabtu, abaikan detail Sabtu.
                if ($is_saturday && !$is_sabtu_detail && count($all_shifts) > 1) {
                    // Skip non-sabtu jika ada opsi lain pada hari Sabtu
                    continue; 
                }
                if (!$is_saturday && $is_sabtu_detail) {
                    // Skip detail Sabtu jika hari ini Senin-Jumat/Minggu
                    continue; 
                }

                $start_ts = strtotime($s->start);
                $w_start  = date('H:i:s', strtotime('-2 hours', $start_ts));
                $w_end    = date('H:i:s', strtotime('+2 hours', $start_ts));

                if ($w_start > $w_end) { 
                    if ($time_now >= $w_start || $time_now <= $w_end) { $selected_shift = $s; break; }
                } else {
                    if ($time_now >= $w_start && $time_now <= $w_end) { $selected_shift = $s; break; }
                }
            }

            // Fallback 1: Jika tidak cocok dalam window (misal terlambat parah), cari yang paling dekat non-sabtu/sabtu
            if (!$selected_shift) {
                foreach ($all_shifts as $s) {
                    $is_sabtu_detail = (stripos($s->shift_detail, 'SABTU') !== false);
                    if ($is_saturday == $is_sabtu_detail) {
                        $selected_shift = $s;
                        break;
                    }
                }
            }

            // Fallback 2: Jika masih null, ambil shift pertama
            if (!$selected_shift) {
                $selected_shift = $all_shifts[0];
            }

            $data = [
                'number'          => $number,
                'date_in'         => $date_now,
                'time_in'         => $time_now,
                'shift_start'     => $selected_shift->start,
                'shift_end'       => $selected_shift->end,
                'shift_name'      => $selected_shift->shift_name,
                'shift_detail'    => $selected_shift->shift_detail,
                'created_by'      => $created_by,
                'attandance_type' => 'QR Code'
            ];
            if ($saved_filename) {
                $data['foto_in'] = $saved_filename;
            }
            
            if ($this->crud->create_nosession('attandances', $data, "", $created_by)) {
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
                "shift"    => ($target->shift_name ?? '-') . " (" . ($target->shift_detail ?? '-') . ")",
                "foto_out" => $saved_filename
            ]
        ]));
    }
}