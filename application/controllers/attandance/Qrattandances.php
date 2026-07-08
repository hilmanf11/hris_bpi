<?php
date_default_timezone_set("Asia/Bangkok");
defined('BASEPATH') or exit('No direct script access allowed');

class Qrattandances extends CI_Controller
{
    private $secret_key = "BANS-HU-PLASTIC-2026"; // Kunci rahasia (simpan baik-baik)
    public function __construct()
    {
        parent::__construct();
        // $this->check_access();

        
        $this->load->helper('url');
        $this->load->helper(array('form', 'url'));
        $this->load->library('form_validation');
        $this->load->model('crud');
    }

    private function check_access() {
        $token_dari_url = $this->input->get('token');

        // Jika tidak ada token sama sekali
        if (!$token_dari_url) {
            show_error('Akses Ditolak: Anda tidak memiliki akses.', 403);
            exit;
        }

        // Dekripsi token
        $decrypted = openssl_decrypt($token_dari_url, "AES-128-ECB", $this->secret_key);

        // Jika dekripsi gagal atau isinya bukan "TERMINAL-BPI-2026"
        if ($decrypted !== "TERMINAL-BPI-2026") {
            show_error('Akses Ditolak: Kunci perangkat tidak valid.', 403);
            exit;
        }
    }

    //HALAMAN UTAMA
    public function index()
    {

        $this->load->view('attandance/qrattandances');

    }

    public function createData()
    {
        $post = $this->input->post();
        $number = isset($post['qrcode']) ? $post['qrcode'] : null;

        // $number = '01040011210132';

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
            die(json_encode(["title" => "Error", "message" => "Karyawan tidak ditemukan", "theme" => "error"]));
        }

        $date_now = date('Y-m-d');
        $time_now = date('H:i:s');

        $formatted_date = date('d/m/Y', strtotime($date_now));
        $formatted_time = date('H:i:s A', strtotime($time_now));
        
        $emp_name = isset($employee->name) ? $employee->name : '-';
        $emp_dept = isset($employee->department_name) ? $employee->department_name : '-'; 

        // Definisikan value default admin di sini agar mudah diganti-ganti jika perlu
        $created_by = isset($employee->username) ? $employee->username : '-'; 

        // =========================================================================
        // 1. AMBIL SEMUA JADWAL SHIFT KARYAWAN DARI DATABASE
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

        foreach ($all_shifts as $s) {
            if ($s->start == '24:00:00') $s->start = '00:00:00';
            if ($s->end == '24:00:00') $s->end = '00:00:00';
        }

        // =========================================================================
        // 2. CEK STATUS RIWAYAT ABSENSI DI DATABASE (STATE CHECK)
        // =========================================================================
        $this->db->where(['number' => $number, 'date_out' => null]);
        $this->db->order_by('id', 'DESC');
        $open_attendance = $this->db->get('attandances')->row();

        $this->db->where(['number' => $number, 'date_out' => $date_now]);
        $this->db->order_by('id', 'DESC');
        $closed_attendance = $this->db->get('attandances')->row();

        $is_multi_in  = false;
        $is_multi_out = false;

        if ($open_attendance && $open_attendance->date_in == $date_now) {
            $shift_start_ts = strtotime($open_attendance->shift_start);
            $w_start        = date('H:i:s', strtotime('-2 hours', $shift_start_ts));
            $w_end          = date('H:i:s', strtotime('+2 hours', $shift_start_ts));

            if ($w_start > $w_end) {
                if ($time_now >= $w_start || $time_now <= $w_end) { $is_multi_in = true; }
            } else {
                if ($time_now >= $w_start && $time_now <= $w_end) { $is_multi_in = true; }
            }
        }

        if (!$is_multi_in && $closed_attendance) {
            $last_out_ts = strtotime($closed_attendance->date_out . ' ' . $closed_attendance->time_out);
            $current_ts  = strtotime("$date_now $time_now");
            if (($current_ts - $last_out_ts) >= 0 && ($current_ts - $last_out_ts) < 3600) {
                $is_multi_out = true;
            }
        }

        // =========================================================================
        // 3. EKSEKUSI LOGIKA UTAMA ABSENSI
        // =========================================================================

        if ($is_multi_in) {
            die(json_encode([
                "title" => "Success", "theme" => "success", "type" => "CHECK IN",
                "results" => [
                    "id"    => $number, "name" => $emp_name, "dept" => $emp_dept,
                    "time"  => date('H:i:s A', strtotime($open_attendance->time_in)),
                    "date"  => date('d/m/Y', strtotime($open_attendance->date_in)),
                    "shift" => ($open_attendance->shift_name ?? '-') . " (" . ($open_attendance->shift_detail ?? '-') . ")"
                ]
            ]));

        } elseif ($is_multi_out) {
            $data = [
                'date_out'   => $date_now, 
                'time_out'   => $time_now,
                'updated_by' => $created_by // Tambahan default input by admin saat check out
            ];
            $this->crud->update_nosession('attandances', ["id" => $closed_attendance->id], $data, "", $created_by);
            $target = $closed_attendance;

        } elseif ($open_attendance) {
            $current_ts = strtotime("$date_now $time_now");
            $shift_start_ts = strtotime($open_attendance->date_in . ' ' . $open_attendance->shift_start);
            
            if (date('H', strtotime($open_attendance->time_in)) >= 20 && date('H', $shift_start_ts) <= 4) {
                $shift_start_ts = strtotime($open_attendance->date_in . ' ' . $open_attendance->shift_start . ' +1 day');
            }

            $durasi_detik = $current_ts - $shift_start_ts;
            $max_stay_duration = 50400; 

            if ($durasi_detik > $max_stay_duration) {
                goto force_checkin;
            } else {
                $data = [
                    'date_out'   => $date_now, 
                    'time_out'   => $time_now,
                    'updated_by' => $created_by // Tambahan default input by admin saat check out
                ];
                $this->crud->update_nosession('attandances', ["id" => $open_attendance->id], $data, "", $created_by);
                $target = $open_attendance;
            }

        } else {
            force_checkin:
            $selected_shift = null;

            foreach ($all_shifts as $s) {
                $start_ts = strtotime($s->start);
                $w_start  = date('H:i:s', strtotime('-2 hours', $start_ts));
                $w_end    = date('H:i:s', strtotime('+2 hours', $start_ts));

                if ($w_start > $w_end) {
                    if ($time_now >= $w_start || $time_now <= $w_end) { $selected_shift = $s; break; }
                } else {
                    if ($time_now >= $w_start && $time_now <= $w_end) { $selected_shift = $s; break; }
                }
            }

            if (!$selected_shift) {
                $closest_diff = null;
                foreach ($all_shifts as $s) {
                    $diff = abs(strtotime($time_now) - strtotime($s->start));
                    if ($closest_diff === null || $diff < $closest_diff) {
                        $closest_diff = $diff;
                        $selected_shift = $s;
                    }
                }
            }

            $data = [
                'number'       => $number,
                'date_in'      => $date_now,
                'time_in'      => $time_now,
                'shift_start'  => $selected_shift->start,
                'shift_end'    => $selected_shift->end,
                'shift_name'   => $selected_shift->shift_name,
                'shift_detail' => $selected_shift->shift_detail,
                'created_by'   => $created_by, // Tambahan default input by admin saat check in baru
                'attandance_type' => 'QR Code'
            ];
            
            if ($this->crud->create_nosession('attandances', $data, "", $created_by)) {// "" untuk insert ke log
                die(json_encode([
                    "title" => "Success", "theme" => "success", "type" => "CHECK IN",
                    "results" => [
                        "id" => $number, "name" => $emp_name, "dept" => $emp_dept,
                        "time" => $formatted_time, "date" => $formatted_date,
                        "shift" => ($selected_shift->shift_name ?? '-') . " (" . ($selected_shift->shift_detail ?? '-') . ")"
                    ]
                ]));
            }
        }

        die(json_encode([
            "title" => "Success",
            "theme" => "success",
            "type" => "CHECK OUT",
            "results" => [
                "id"    => $number,
                "name"  => $emp_name,
                "dept"  => $emp_dept,
                "time"  => $formatted_time, 
                "date"  => $formatted_date,
                "shift" => ($target->shift_name ?? '-') . " (" . ($target->shift_detail ?? '-') . ")"
            ]
        ]));
    }

   

   
}
