<?php
error_reporting(0);
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

        //VALIDASI FORM
        $this->form_validation->set_rules('number', 'Employee', 'required|min_length[1]|max_length[30]');
    }

    //HALAMAN UTAMA
    public function index()
    {
        if (empty($this->session->username)) {
            redirect('error_session');
        } elseif ($this->checkuserAccess($this->id_menu()) > 0) {
            $data['button'] = $this->getbutton($this->id_menu());

            $this->load->view('template/header', $data);
            $this->load->view('attandance/attandances');
        } else {
            redirect('error_access');
        }
    }

    //GET DATA
    public function reads()
    {
        $post = isset($_POST['q']) ? $_POST['q'] : "";
        $send = $this->crud->reads('attandances', ["name" => $post]);
        echo json_encode($send);
    }

    //GET DATATABLES
    public function datatables()
    {
        
        if ($this->input->post()) {
            $filter_from = $this->input->get('filter_from');
            $filter_to = $this->input->get('filter_to');
            $filter_division = $this->input->get('filter_division');
            $filter_departement = $this->input->get('filter_departement');
            $filter_departement_sub = $this->input->get('filter_departement_sub');
            $filter_employee = $this->input->get('filter_employee');
            $filter_status = $this->input->get('filter_status');
            $filter_attandance_type = $this->input->get('filter_attandance_type');
            $result = array();

            //Employee
            $this->db->select("a.id, b.id as attandance_id, a.number, a.name, a.division_id, a.departement_id, a.departement_sub_id, (CASE WHEN b.attandance_type IS NULL OR TRIM(b.attandance_type) = '' THEN 'Upload HRIS' ELSE b.attandance_type END) as attandance_type, b.foto_in,
                DAYNAME(b.date_in) as dayname,
                b.date_in,
                b.date_out,
                b.time_in,
                b.time_out,
                c.name as division_name, 
                d.name as departement_name, 
                e.name as departement_sub_name, 
                g.name as contract_name");
            $this->db->from('employees a');
            $this->db->join('attandances b', 'a.number = b.number');
            $this->db->join('divisions c', 'a.division_id = c.id');
            $this->db->join('departements d', 'a.departement_id = d.id');
            $this->db->join('departement_subs e', 'a.departement_sub_id = e.id');
            $this->db->join('contracts g', 'g.id = a.contract_id', 'left');
            if ($filter_from != "" && $filter_to != "") {
                $this->db->where("(b.date_in >= '$filter_from' and b.date_in <= '$filter_to' or b.date_out >= '$filter_from' and b.date_out <= '$filter_to')");
            }
            $this->db->like('a.division_id', $filter_division);
            $this->db->like('a.departement_id', $filter_departement);
            $this->db->like('a.departement_sub_id', $filter_departement_sub);
            $this->db->like('a.id', $filter_employee);
            if ($filter_attandance_type != "") {
                if ($filter_attandance_type == "Upload HRIS") {
                    $this->db->where("(b.attandance_type = 'Upload HRIS' OR b.attandance_type IS NULL OR TRIM(b.attandance_type) = '')");
                } else {
                    $this->db->where('b.attandance_type', $filter_attandance_type);
                }
            }
            $this->db->group_by("a.number");
            $this->db->group_by("b.date_in");
            $this->db->order_by("d.name", "asc");
            $this->db->order_by("a.name", "asc");
            $this->db->order_by("b.date_in", "asc");
            $employees = $this->db->get()->result_array();
            // echo 'test';
        // die();

            // die(json_encode($employees));

            $datas['total'] = count($employees);
            $datas['rows'] = $employees;
            echo json_encode($datas);
        }
    }

    //CREATE DATA
    public function create()
    {
        if ($this->input->post()) {
            if ($this->form_validation->run() == TRUE) {
                $post   = $this->input->post();
                $post['attandance_type'] = "Input HRIS";
                $attandance = $this->crud->read("attandances", [], ["number" => $post['number'], "date_in" => $post['date_in']]);
                if ($attandance) {
                    echo json_encode(array("title" => "Available", "message" => "The attandance for this employee and date in has been created", "theme" => "error"));
                } else {
                    $send   = $this->crud->create('attandances', $post);
                    echo $send;
                }
            } else {
                show_error(validation_errors());
            }
        } else {
            show_error("Cannot Process your request");
        }
    }

    //UPDATE DATA
    public function update()
    {
        if ($this->input->post()) {
            $id   = base64_decode($this->input->get('id'));
            $post = $this->input->post();
            $send = $this->crud->update('attandances', ["id" => $id], $post);
            echo $send;
        } else {
            show_error("Cannot Process your request");
        }
    }

    //DELETE DATA
    public function delete()
    {
        $data = $this->input->post();
        $delete = $this->crud->delete("attandances", ['number' => $data['number'], 'date_in' => $data['date_in']]);
        echo $delete;
    }

    function formatTanggal($date)
    {
        if (empty($date)) return NULL;

        // Jika mengandung strip/slash, ganti slash ke strip agar strtotime membaca sebagai d-m-Y
        $clean_date = str_replace('/', '-', trim($date));
        
        $timestamp = strtotime($clean_date);
        
        // Jika berhasil di-parse oleh strtotime
        if ($timestamp !== false) {
            return date('Y-m-d', $timestamp);
        }

        // Fallback menggunakan DateTime jika strtotime gagal
        $datetime = DateTime::createFromFormat('d/m/Y', $date);
        return $datetime ? $datetime->format('Y-m-d') : NULL;
    }

    public function create_temp($number, $date, $time)
    {
        $this->db->select('*');
        $this->db->from('attandances_temp');
        $this->db->where("number", $number);
        $this->db->where("attandance_date", $date . " " . $time);
        $records = $this->db->get()->result_array();

        $datas = array(
            'number' => $number,
            'attandance_date' => $date . " " . $time
        );

        if (count($records) > 0) {
            return json_encode(array("title" => "Duplicated", "message" => "Data absence duplicate", "theme" => "error"));
        } else {
            $send = $this->crud->create('attandances_temp', $datas);
            return $send;
        }
    }

    //UPLOAD DATA
    public function upload()
    {
        error_reporting(0);

        if ($this->input->post('file_type') == "text") {
            // ... (Bagian teks sudah aman karena menggunakan attandance_temp dan di-order_by)
            $target = basename($_FILES['file_upload']['name']);
            move_uploaded_file($_FILES['file_upload']['tmp_name'], $target);
            $open = fopen($target, 'r');

            while (!feof($open)) {
                $getTextLine = fgets($open);
                $explodeLine = explode(":", $getTextLine);

                list($code, $date, $time, $id, $number) = $explodeLine;

                $this->create_temp(trim($number), trim(date('Y-m-d', strtotime($date))), trim(date('H:i:s', strtotime($time))));
            }

            $this->db->select('*');
            $this->db->from('attandance_temp');
            $this->db->order_by('number', 'asc');
            $this->db->order_by('attandance_date', 'asc');
            $attandance = $this->db->get()->result_object();

            foreach ($attandance as $data) {
                $datas[] = array(
                    'number' => $data->number,
                    'date_in' => date('d/m/Y H:i:s', strtotime($data->attandance_date)),
                    'date_out' => date('d/m/Y H:i:s', strtotime($data->attandance_date))
                );
            }

            $datas['total'] = count($datas);
            echo json_encode($datas);
            unlink($_FILES['file_upload']['name']);
            $this->db->truncate('attandance_temp');

        } elseif ($this->input->post('file_type') == "excel") {
            require_once 'assets/vendors/excel_reader2.php';
            $target = basename($_FILES['file_upload']['name']);
            move_uploaded_file($_FILES['file_upload']['tmp_name'], $target);
            chmod($_FILES['file_upload']['name'], 0777);
            $file = $_FILES['file_upload']['name'];
            $data = new Spreadsheet_Excel_Reader($file, false);
            $total_row = $data->rowcount($sheet_index = 0);

            // Penampung temporary agar data terurut berdasarkan Waktu & PIN
            for ($i = 2; $i <= $total_row; $i++) {
                $datein = explode(";", $data->val($i, 4));
                $clean_date = $this->formatTanggal($datein[0]); // Format ke Y-m-d
                $clean_time = trim($datein[1]);                 // Format H:i:s
                
                $raw_lokasi = trim((string)$data->val($i, 5));
                $lokasi = 1;
                if ($raw_lokasi === '2' || strtolower($raw_lokasi) === 'out' || strtolower($raw_lokasi) === 'c/out') {
                    $lokasi = 2;
                } elseif ($raw_lokasi === '1' || strtolower($raw_lokasi) === 'in' || strtolower($raw_lokasi) === 'c/in') {
                    $lokasi = 1;
                } else {
                    $lokasi = ((int)$raw_lokasi === 2) ? 2 : 1;
                }

                $sort_helper[] = array(
                    'lokasi'   => $lokasi,
                    'number'   => trim((string)$data->val($i, 6)),
                    'datetime' => $clean_date . " " . $clean_time,
                    'date_in'  => trim($datein[0]) . " " . $clean_time
                );
            }

            // Urutkan array berdasarkan PIN (number), Waktu (datetime) Ascending, dan jika waktu sama: IN dulu baru OUT
            usort($sort_helper, function($a, $b) {
                if ($a['number'] == $b['number']) {
                    if ($a['datetime'] == $b['datetime']) {
                        return ($a['lokasi'] == 1) ? -1 : 1;
                    }
                    return strcmp($a['datetime'], $b['datetime']);
                }
                return strcmp($a['number'], $b['number']);
            });

            // Pindahkan hasil urutan ke variabel $datas yang siap di-loop oleh Ajax
            foreach ($sort_helper as $row) {
                $datas[] = array(
                    'lokasi'   => $row['lokasi'],
                    'number'   => $row['number'],
                    'date_in'  => $row['date_in'], 
                    'date_out' => $row['date_in']
                );
            }

            $datas['total'] = count($datas);
            echo json_encode($datas);

            unlink($_FILES['file_upload']['name']);
        } else {
            echo json_encode("Format File Error");
        }
    }

    public function uploadclearFailed()
    {
        @unlink('failed/attandances.txt');
    }

    public function uploadcreateFailed()
    {
        if ($this->input->post()) {
            $message = $this->input->post('message');
            $textFailed = fopen('failed/attandances.txt', 'a');
            fwrite($textFailed, $message . "\n");
            fclose($textFailed);
        }
    }

    public function uploadDownloadFailed()
    {
        $file = "failed/attandances.txt";

        header('Content-Description: File Failed');
        header('Content-Disposition: attachment; filename=' . basename($file));
        header('Expires: 0');
        header('Cache-Control: must-revalidate');
        header('Pragma: public');
        header('Content-Length: ' . @filesize($file));
        header("Content-Type: text/plain");
        @readfile($file);
    }

    public function uploadcreate()
    {
        if ($this->input->post()) {
            $data    = $this->input->post('data');
            $explode = explode(" ", $data['date_in']);
            $date_in = $this->formatTanggal($explode[0]); // Tanggal log mesin (Y-m-d)
            $time_in = trim($explode[1]);                 // Jam log mesin (H:i:s)
            $lokasi  = isset($data['lokasi']) ? (int) trim((string)$data['lokasi']) : 1;

            // 1. Ambil Data Employee
            $this->db->select('*');
            $this->db->from('employees');
            $this->db->where('number', $data['number']);
            $employee = $this->db->get()->row();

            if (!empty($employee->number)) {
                
                // ==========================================
                // KONDISI 1: JIKA LOG ADALAH CHECK IN (lokasi == 1)
                // ==========================================
                if ($lokasi == 1) {

                    $target_date_in = $date_in;
                    $shift = $this->getMatchingShift($employee->id, $target_date_in, $time_in, true);
                    $tomorrow = date("Y-m-d", strtotime('+1 days', strtotime($target_date_in)));

                    // Tentukan date_out berdasarkan shift & jam masuk
                    // Jika shift lintas hari (misal shift malam 23:35->08:00 atau shift siang 16:00->00:00)
                    // dan karyawan masuk di siang/sore/malam (>= 12:00:00), date_out adalah BESOK (tomorrow)
                    $is_overnight = $this->isOvernightShift($shift);
                    if ($is_overnight && strtotime("1970-01-01 " . $time_in) >= strtotime("1970-01-01 12:00:00")) {
                        $date_out = $tomorrow;
                    } else {
                        $date_out = $target_date_in;
                    }

                    // Cari apakah sudah ada record absensi di tanggal masuk ini (1 karyawan = max 1 record per date_in)
                    $this->db->select('*');
                    $this->db->from('attandances');
                    $this->db->where('number', $data['number']);
                    $this->db->where('date_in', $target_date_in);
                    $this->db->order_by('id', 'ASC');
                    $attandance_in = $this->db->get()->row();

                    if (!empty($attandance_in->number)) {
                        $existing_time_in = (!empty($attandance_in->time_in) && $attandance_in->time_in !== '00:00:00') ? $attandance_in->time_in : null;

                        // Perbarui jika time_in sebelumnya masih kosong (misal record dibuat oleh checkout duluan) atau jika time_in baru lebih awal
                        if (empty($existing_time_in) || $time_in < $existing_time_in) {
                            $data_attandance = array(
                                'time_in'         => $time_in,
                                'date_out'        => !empty($attandance_in->date_out) ? $attandance_in->date_out : $date_out,
                                'shift_start'     => isset($shift->start) ? $shift->start : NULL,
                                'shift_name'      => isset($shift->name) ? $shift->name : NULL,
                                'shift_detail'    => isset($shift->shift_name) ? $shift->shift_name : NULL,
                                'shift_days'      => isset($shift->days) ? $shift->days : NULL,
                                'attandance_type' => 'Upload HRIS'
                            );

                            $this->crud->update('attandances', ["id" => $attandance_in->id], $data_attandance, "no_log");
                            echo json_encode(array("title" => "Updated", "message" => $employee->name . " | " . $target_date_in . " " . $time_in . " Update Check In", "theme" => "success"));
                        } else {
                            // time_in sudah ada & lebih awal dari log ini -> abaikan (tap ganda / upload ulang)
                            echo json_encode(array("title" => "Ignored", "message" => $employee->name . " | " . $target_date_in . " " . $time_in . " Check In Diabaikan (Sudah Ada)", "theme" => "success"));
                        }

                    } else {
                        // Belum ada record sama sekali di tanggal ini -> buat record baru
                        $data_attandance = array(
                            'number'          => $data['number'],
                            'date_in'         => $target_date_in,
                            'time_in'         => $time_in,
                            'date_out'        => $date_out,
                            'shift_start'     => isset($shift->start) ? $shift->start : NULL,
                            'shift_name'      => isset($shift->name) ? $shift->name : NULL,
                            'shift_detail'    => isset($shift->shift_name) ? $shift->shift_name : NULL,
                            'shift_days'      => isset($shift->days) ? $shift->days : NULL,
                            'attandance_type' => 'Upload HRIS'
                        );
                        $this->crud->create('attandances', $data_attandance, "no_log");
                        echo json_encode(array("title" => "Created", "message" => $employee->name . " | " . $target_date_in . " " . $time_in . " Create Check In", "theme" => "success"));
                    }

                // ==========================================
                // KONDISI 2: JIKA LOG ADALAH CHECK OUT (lokasi == 2)
                // ==========================================
                } else {
                    $yesterday = date("Y-m-d", strtotime('-1 days', strtotime($date_in)));
                    $hour_out  = (int) date('H', strtotime($time_in));
                    $shift_out = $this->getMatchingShift($employee->id, $date_in, $time_in, false);
                    $is_shift_overnight = $this->isOvernightShift($shift_out);

                    // 1. Tentukan target_date_in:
                    // Jika jam checkout adalah pagi/subuh/dini hari (<= 11:00), prioritaskan mencocokkan ke record kemarin jika ada
                    $target_date_in = $date_in;

                    if ($hour_out <= 11) {
                        // Cek apakah ada record kemarin
                        $this->db->select('*');
                        $this->db->from('attandances');
                        $this->db->where('number', $data['number']);
                        $this->db->where('date_in', $yesterday);
                        $this->db->order_by('id', 'ASC');
                        $att_yesterday = $this->db->get()->row();

                        // Cek apakah ada record hari ini
                        $this->db->select('*');
                        $this->db->from('attandances');
                        $this->db->where('number', $data['number']);
                        $this->db->where('date_in', $date_in);
                        $this->db->order_by('id', 'ASC');
                        $att_today = $this->db->get()->row();

                        if (!empty($att_yesterday->number) && empty($att_today->number)) {
                            // Ada record kemarin dan belum ada record hari ini -> pasangkan ke kemarin
                            $target_date_in = $yesterday;
                        } elseif (!empty($att_yesterday->number) && !empty($att_today->number)) {
                            // Jika record kemarin time_out-nya masih kosong -> pasangkan ke kemarin
                            if (empty($att_yesterday->time_out) || $att_yesterday->time_out == '00:00:00') {
                                $target_date_in = $yesterday;
                            } else {
                                $target_date_in = $date_in;
                            }
                        } elseif ($is_shift_overnight) {
                            // Belum ada record sama sekali kemarin maupun hari ini (Lupa In shift malam)
                            $target_date_in = $yesterday;
                        }
                    }

                    // 2. Cari record yang sudah ada di target_date_in (1 karyawan = max 1 record per date_in)
                    $this->db->select('*');
                    $this->db->from('attandances');
                    $this->db->where('number', $data['number']);
                    $this->db->where('date_in', $target_date_in);
                    $this->db->order_by('id', 'ASC');
                    $existing = $this->db->get()->row();

                    if (!empty($existing->number)) {
                        // Karyawan SUDAH MEMILIKI RECORD di target_date_in ini!
                        // PASTI UPDATE RECORD INI, JANGAN PERNAH INSERT BARU!
                        $existing_out = (!empty($existing->time_out) && $existing->time_out !== '00:00:00') ? $existing->time_out : null;

                        // Jika time_out baru sama persis atau sudah ada time_out yang lebih lambat di tanggal out yang sama -> abaikan
                        if ($existing_out && $existing->date_out == $date_in && $time_in <= $existing_out) {
                            echo json_encode(array("title" => "Ignored", "message" => $employee->name . " | In: " . $target_date_in . " - Out: " . $date_in . " " . $time_in . " Check Out Diabaikan (Sudah Ada yang Lebih Terlambat)", "theme" => "success"));
                        } else {
                            $data_attandance = array(
                                'date_out'     => $date_in,
                                'time_out'     => $time_in,
                                'shift_start'  => !empty($existing->shift_start) ? $existing->shift_start : (isset($shift_out->start) ? $shift_out->start : NULL),
                                'shift_name'   => !empty($existing->shift_name) ? $existing->shift_name : (isset($shift_out->name) ? $shift_out->name : NULL),
                                'shift_detail' => !empty($existing->shift_detail) ? $existing->shift_detail : (isset($shift_out->shift_name) ? $shift_out->shift_name : NULL),
                                'shift_days'   => !empty($existing->shift_days) ? $existing->shift_days : (isset($shift_out->days) ? $shift_out->days : NULL)
                            );
                            $this->crud->update('attandances', ["id" => $existing->id], $data_attandance, "no_log");
                            echo json_encode(array("title" => "Updated", "message" => $employee->name . " | In: " . $target_date_in . " - Out: " . $date_in . " " . $time_in . " Update Check Out", "theme" => "success"));
                        }

                    } else {
                        // Karyawan BENAR-BENAR BELUM MEMILIKI RECORD di target_date_in ini (Lupa Check In)
                        $data_attandance = array(
                            'number'          => $data['number'],
                            'date_in'         => $target_date_in,
                            'time_in'         => NULL,
                            'date_out'        => $date_in,
                            'time_out'        => $time_in,
                            'shift_start'     => isset($shift_out->start) ? $shift_out->start : NULL,
                            'shift_name'      => isset($shift_out->name) ? $shift_out->name : NULL,
                            'shift_detail'    => isset($shift_out->shift_name) ? $shift_out->shift_name : NULL,
                            'shift_days'      => isset($shift_out->days) ? $shift_out->days : NULL,
                            'attandance_type' => 'Upload HRIS'
                        );
                        $this->crud->create('attandances', $data_attandance, "no_log");
                        echo json_encode(array("title" => "Created", "message" => $employee->name . " | In: " . $target_date_in . " - Out: " . $date_in . " " . $time_in . " Create Check Out (Tanpa Check In)", "theme" => "success"));
                    }
                }
            } else {
                echo json_encode(array("title" => "Warning", "message" => $data['number'] . " Employee ID Unregistered", "theme" => "error"));
            }
        }
    }

    private function isOvernightShift($shift)
    {
        if (empty($shift)) return false;

        $start = !empty($shift->start) ? $shift->start : '00:00:00';
        $end   = !empty($shift->end) ? $shift->end : '00:00:00';

        if ($start == '24:00:00') $start = '00:00:00';
        if ($end == '24:00:00') $end = '00:00:00';

        $st_ts = strtotime("1970-01-01 " . $start);
        $en_ts = strtotime("1970-01-01 " . $end);

        // 1. Jika end time < start time (misal 16:00 -> 01:00, 19:00 -> 07:00)
        if ($en_ts < $st_ts) {
            return true;
        }

        // 2. Jika end time = 00:00:00 dan start time >= 12:00:00 (misal Shift Siang: 16:00 -> 00:00)
        if ($end == '00:00:00' && $st_ts >= strtotime("1970-01-01 12:00:00")) {
            return true;
        }

        // 3. Jika start time = 00:00:00 dan end time <= 12:00:00 (Shift Malam: 00:00 -> 08:00 / 09:00)
        if ($start == '00:00:00' && $en_ts <= strtotime("1970-01-01 12:00:00") && $en_ts > 0) {
            return true;
        }

        // 4. Cek nama shift mengandung kata MALAM
        $name = (isset($shift->name) ? $shift->name : '') . ' ' . (isset($shift->shift_name) ? $shift->shift_name : '');
        if (stripos($name, 'MALAM') !== false) {
            return true;
        }

        return false;
    }

    private function getMatchingShift($employee_id, $target_date, $target_time, $is_check_in = true)
    {
        $this->db->select("d.start, d.end, d.days, d.working, d.tolerance, c.name, d.name as shift_name");
        $this->db->from('shift_employees b');
        $this->db->join('shifts c', 'c.id = b.shift_id');
        $this->db->join('shift_details d', 'd.shift_id = c.id');
        $this->db->where('b.employee_id', $employee_id);
        $all_shifts = $this->db->get()->result();

        if (empty($all_shifts)) {
            return null;
        }

        // Normalisasi jam 24:00:00 -> 00:00:00
        foreach ($all_shifts as $s) {
            if ($s->start == '24:00:00') $s->start = '00:00:00';
            if ($s->end == '24:00:00') $s->end = '00:00:00';
        }

        $is_saturday = (date('N', strtotime($target_date)) == 6);
        
        // Filter kandidat shift berdasarkan hari (Sabtu vs Hari Kerja Biasa)
        $candidates = array();
        foreach ($all_shifts as $s) {
            $is_sabtu_detail = (stripos($s->shift_name, 'SABTU') !== false || strtoupper($s->working) === 'WEEKEND');
            if ($is_saturday && $is_sabtu_detail) {
                $candidates[] = $s;
            } elseif (!$is_saturday && !$is_sabtu_detail) {
                $candidates[] = $s;
            }
        }

        // Jika tidak ada yang cocok dengan filter hari, gunakan semua shift
        if (empty($candidates)) {
            $candidates = $all_shifts;
        }

        // Cari detail shift dengan selisih waktu terkecil (circular 24 jam)
        $best_shift = null;
        $min_diff   = 999999;

        foreach ($candidates as $s) {
            $ref_time = $is_check_in ? $s->start : $s->end;
            if (empty($ref_time)) continue;

            // Jika check-in dan ada lembur awal di cash_carries, gunakan jam mulai lembur sebagai patokan IN
            if ($is_check_in && !empty($employee_id)) {
                $ot_early = $this->get_early_overtime($employee_id, $target_date, $s->start);
                if ($ot_early && !empty($ot_early->start)) {
                    $ref_time = ($ot_early->start == '24:00:00') ? '00:00:00' : $ot_early->start;
                }
            }

            $diff = $this->getTimeDiffMinutes($target_time, $ref_time);
            if ($diff < $min_diff) {
                $min_diff   = $diff;
                $best_shift = $s;
            }
        }

        return $best_shift ? $best_shift : $all_shifts[0];
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

    private function getTimeDiffMinutes($time1, $time2)
    {
        $ts1 = strtotime("1970-01-01 " . $time1);
        $ts2 = strtotime("1970-01-01 " . $time2);
        if ($ts1 === false || $ts2 === false) return 999999;

        $diff = abs($ts1 - $ts2) / 60;
        if ($diff > 720) {
            $diff = 1440 - $diff;
        }
        return $diff;
    }

    // public function uploadcreate()
    // {
    //     if ($this->input->post()) {
    //         $data = $this->input->post('data');
    //         $explode = explode(" ", $data['date_in']);
    //         $date_in = $this->formatTanggal($explode[0]);
    //         $time_in = $explode[1];

    //         //Attandance
    //         $this->db->select('*');
    //         $this->db->from('attandances');
    //         $this->db->where('number', $data['number']);
    //         $this->db->where('date_in', $date_in);
    //         $attandance = $this->db->get()->row();

    //         //Employee
    //         $this->db->select('*');
    //         $this->db->from('employees');
    //         $this->db->where('number', $data['number']);
    //         $employee = $this->db->get()->row();

    //         //Jika Terdaftar di master employee
    //         if (!empty($employee->number)) {
    //             //Jika sudah absen jam masuk
    //             if (!empty($attandance->date_in)) {
    //                 //Shift 2 check
    //                 if ((substr($attandance->time_in, 0, 2) == "00" or substr($attandance->time_in, 0, 2) == "01") and (substr($time_in, 0, 2) == "15" or substr($time_in, 0, 2) == "16")) {
    //                     $data_attandance = array(
    //                         'number' => $data['number'],
    //                         'date_out' => $attandance->date_in,
    //                         'time_out' => $attandance->time_in,
    //                         'date_in' => $date_in,
    //                         'time_in' => $time_in,
    //                     );
    //                     //Shift 3 check
    //                 } elseif ((substr($attandance->time_in, 0, 2) == "08" or substr($attandance->time_in, 0, 2) == "07") and (substr($time_in, 0, 2) == "23" or substr($time_in, 0, 2) == "00")) {
    //                     $data_attandance = array(
    //                         'number' => $data['number'],
    //                         'date_out' => $attandance->date_in,
    //                         'time_out' => $attandance->time_in,
    //                         'date_in' => $date_in,
    //                         'time_in' => $time_in,
    //                     );
    //                 } else {
    //                     $data_attandance = array(
    //                         'number' => $data['number'],
    //                         'date_out' => $date_in,
    //                         'time_out' => $time_in,
    //                     );
    //                 }

    //                 $this->crud->update('attandances', ["id" => $attandance->id], $data_attandance);
    //                 echo json_encode(array("title" => "Updated", "message" => $employee->name . " | " . $date_in . " " . $time_in . " Update data Check Out", "theme" => "success"));
    //             } else {
    //                 //Cek tanggal kemarin
    //                 $yesterday = date("Y-m-d", strtotime('-1 days', strtotime($date_in)));
    //                 $this->db->select("*");
    //                 $this->db->from('attandances');
    //                 $this->db->where('number', $data['number']);
    //                 $this->db->where('date_in', $yesterday);
    //                 $attandance_2 = $this->db->get()->row();

    //                 //jika tanggal kemarin ada jam masuk
    //                 if (!empty($attandance_2->date_in)) {
    //                     //jika jam keluar nya kosong
    //                     if (@$attandance_2->time_out == "" or @$attandance_2->time_out == null) {

    //                         $attandance_plus = date_create($attandance_2->date_in . " " . $attandance_2->time_in);
    //                         $date_plus = date_create($date_in . " " . $time_in);

    //                         //Jika jam masuk kemarin sama dengan jam masuk hari ini
    //                         if (substr($attandance_2->time_in, 0, 2) == substr($time_in, 0, 2)) {
    //                             $data_attandance = array(
    //                                 'number' => $data['number'],
    //                                 'date_in' => $date_in,
    //                                 'time_in' => $time_in,
    //                             );

    //                             $this->crud->create('attandances', $data_attandance);
    //                             echo json_encode(array("title" => "Saved", "message" => $employee->name . " | " . $date_in . " " . $time_in . " Create Check in", "theme" => "success"));

    //                             //Jika jam masuk kemarin kurang dari jam masuk sekarang
    //                         } elseif ($attandance_plus < $date_plus) {
    //                             $data_attandance = array(
    //                                 'number' => $data['number'],
    //                                 'date_in' => $date_in,
    //                                 'time_in' => $time_in,
    //                             );

    //                             $this->crud->create('attandances', $data_attandance);
    //                             echo json_encode(array("title" => "Saved", "message" => $employee->name . " | " . $date_in . " " . $time_in . " Create Check in", "theme" => "success"));
    //                         } else {
    //                             $data_attandance = array(
    //                                 'number' => $data['number'],
    //                                 'date_out' => $date_in,
    //                                 'time_out' => $time_in,
    //                             );

    //                             $this->crud->update('attandances', ["id" => @$attandance_2->id], $data_attandance);
    //                             echo json_encode(array("title" => "Updated", "message" => $employee->name . " Update data time out yesterday", "theme" => "success"));
    //                         }
    //                     } else {
    //                         $data_attandance = array(
    //                             'number' => $data['number'],
    //                             'date_in' => $date_in,
    //                             'time_in' => $time_in,
    //                         );

    //                         $this->crud->create('attandances', $data_attandance);
    //                         echo json_encode(array("title" => "Saved", "message" => $employee->name . " | " . $date_in . " " . $time_in . " Create Check in", "theme" => "success"));
    //                     }
    //                 } else {
    //                     $data_attandance = array(
    //                         'number' => $data['number'],
    //                         'date_in' => $date_in,
    //                         'time_in' => $time_in,
    //                     );

    //                     $this->crud->create('attandances', $data_attandance);
    //                     echo json_encode(array("title" => "Saved", "message" => $employee->name . " | " . $date_in . " " . $time_in . " Create Check in", "theme" => "success"));
    //                 }
    //             }
    //         } else {
    //             echo json_encode(array("title" => "Warning", "message" => $data['number'] . " Empoyee ID Un Registered", "theme" => "error"));
    //         }
    //     }
    // }

    //PRINT & EXCEL DATA
    public function print($option = "")
    {
        if ($option == "excel") {
            $format  = date("Ymd");
            header("Content-type: application/vnd-ms-excel");
            header("Content-Disposition: attachment; filename=attandances_$format.xls");
        }

        $filter_from = $this->input->get('filter_from');
        $filter_to = $this->input->get('filter_to');
        $filter_division = $this->input->get('filter_division');
        $filter_departement = $this->input->get('filter_departement');
        $filter_departement_sub = $this->input->get('filter_departement_sub');
        $filter_employee = $this->input->get('filter_employee');
        $filter_attandance_type = $this->input->get('filter_attandance_type');

        $this->db->select("a.id, b.id as attandance_id, a.number, a.name, a.division_id, a.departement_id, a.departement_sub_id, (CASE WHEN b.attandance_type IS NULL OR TRIM(b.attandance_type) = '' THEN 'Upload HRIS' ELSE b.attandance_type END) as attandance_type, b.foto_in,
            DAYNAME(b.date_in) as dayname,
            b.date_in,
            b.date_out,
            b.time_in,
            b.time_out,
            c.name as division_name, 
            d.name as departement_name, 
            e.name as departement_sub_name, 
            g.name as contract_name");
        $this->db->from('employees a');
        $this->db->join('attandances b', 'a.number = b.number');
        $this->db->join('divisions c', 'a.division_id = c.id');
        $this->db->join('departements d', 'a.departement_id = d.id');
        $this->db->join('departement_subs e', 'a.departement_sub_id = e.id');
        $this->db->join('contracts g', 'g.id = a.contract_id', 'left');
        if ($filter_from != "" && $filter_to != "") {
            $this->db->where("(b.date_in >= '$filter_from' and b.date_in <= '$filter_to' or b.date_out >= '$filter_from' and b.date_out <= '$filter_to')");
        }
        $this->db->like('a.division_id', $filter_division);
        $this->db->like('a.departement_id', $filter_departement);
        $this->db->like('a.departement_sub_id', $filter_departement_sub);
        $this->db->like('a.id', $filter_employee);
        if ($filter_attandance_type != "") {
            if ($filter_attandance_type == "Upload HRIS") {
                $this->db->where("(b.attandance_type = 'Upload HRIS' OR b.attandance_type IS NULL OR TRIM(b.attandance_type) = '')");
            } else {
                $this->db->where('b.attandance_type', $filter_attandance_type);
            }
        }
        $this->db->group_by("a.number");
        $this->db->group_by("b.date_in");
        $this->db->order_by("d.name", "asc");
        $this->db->order_by("a.name", "asc");
        $this->db->order_by("b.date_in", "asc");
        $records = $this->db->get()->result_array();

        $html = '<html><head><title>Print Data Attandance</title></head><style>body {font-family: Arial, Helvetica, sans-serif;}#customers {border-collapse: collapse;width: 100%;font-size: 12px;}#customers td, #customers th {border: 1px solid #ddd;padding: 4px;}#customers tr:nth-child(even){background-color: #f2f2f2;}#customers tr:hover {background-color: #ddd;}#customers th {padding-top: 6px;padding-bottom: 6px;text-align: left;color: black;background-color: #e5e7eb;}</style><body>
        <style> .str{ mso-number-format:\@; } </style>
        <center>
            <div style="float: left; font-size: 12px; text-align: left;">
                Human Resource Information System
            </div>
            <div style="float: right; font-size: 12px; text-align: right;">
                Print Date ' . date("d M Y H:i:s") . ' <br>
                Print By ' . $this->session->username . '  
            </div>
        </center><br><br>
        <center>
            <h3>Data Attendance</h3>
        </center>
        
        <table id="customers" border="1">
            <tr>
                <th width="20">No</th>
                <th>Employee ID</th>
                <th>Fullname</th>
                <th>Division</th>
                <th>Department</th>
                <th>Department Sub</th>
                <th>Day</th>
                <th>Date In</th>
                <th>Time In</th>
                <th>Date Out</th>
                <th>Time Out</th>
                <th>Attendance Type</th>
            </tr>';
        $no = 1;
        foreach ($records as $data) {
            $html .= '<tr>
                    <td>' . $no . '</td>
                    <td class="str">' . $data['number'] . '</td>
                    <td>' . $data['name'] . '</td>
                    <td>' . $data['division_name'] . '</td>
                    <td>' . $data['departement_name'] . '</td>
                    <td>' . $data['departement_sub_name'] . '</td>
                    <td>' . $data['dayname'] . '</td>
                    <td>' . $data['date_in'] . '</td>
                    <td>' . $data['time_in'] . '</td>
                    <td>' . $data['date_out'] . '</td>
                    <td>' . $data['time_out'] . '</td>
                    <td>' . $data['attandance_type'] . '</td>
                    </tr>';
            $no++;
        }

        $html .= '</table></body></html>';
        echo $html;
    }
}
