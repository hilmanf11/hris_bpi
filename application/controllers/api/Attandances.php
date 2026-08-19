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

                            // test
                            // $time_now = $post['time_out'];
                            // $date_now = $post['date_out'];

                            $this->db->select('*');
                            $this->db->from('attandances');
                            $this->db->where('number', $user->number);
                            $this->db->where("date_in = '$date_in'");
                            $attandance_in = $this->db->get()->row();

                            $data = array(
                                'number' => trim($user->number),
                                'date_out' => $date_now,
                                'time_out' => $time_now,
                                'foto_out' => @$foto,
                                'status' => $post['source'] ?? 'MOBILE',
                                'attandance_type' => 'MOBILE HRIS',
                            );

                            if ($this->mobile->update('attandances', ["id" => @$attandance_in->id], $data, $user->username)) {

                                $dataLogs = array(
                                    'name' => $user->name,
                                    'number' => trim($user->number),
                                    'date_attendance' => $date_now,
                                    'time_attendance' => $time_now,
                                    'info_attendance' => "CHECK OUT",
                                    'attendance_type' => $post['source'] ?? 'MOBILE',
                                    "foto" => @$foto,
                                    "shift_detail" => @$attandance_in->shift_detail,
                                );

                                $this->mobile->create('attendances_logs', $dataLogs, $user->username);

                                die(json_encode(array(
                                    "title" => "Success",
                                    "message" => "Attendance Out Completed",
                                    "theme" => "success"
                                )));
                            } else {
                                show_error("Attendance Out UnComplete");
                            }
                        } else {
                            // $foto = $this->mobile->upload('foto', ['png', 'jpg', 'jpeg'], 'assets/document/attendance/', ["number" => $user->number], "attandances", "foto");
                            $foto = $this->mobile->upload('foto', ['png', 'jpg', 'jpeg'], 'assets/document/attendance/' . $folder . '/');

                            // $tolerance_hour_min = date("H:i:s", strtotime('-2 Hour', strtotime($post['time_in'])));
                            // $tolerance_hour_plus = date("H:i:s", strtotime('+2 Hour', strtotime($post['time_in'])));
                            // $this->db->select("d.start, d.end, d.days, d.working, d.tolerance, c.name, d.name as shift_name");
                            // $this->db->from('shift_employees b');
                            // $this->db->join('shifts c', 'c.id = b.shift_id');
                            // $this->db->join('shift_details d', 'd.shift_id = c.id');
                            // $this->db->where('b.employee_id', $employee->id);
                            // $this->db->where("TIME(d.start) >=  TIME('$tolerance_hour_min') && TIME(d.start) <= TIME('$tolerance_hour_plus')");
                            // $shift = $this->db->get()->row();


                            // test
                            // $time_now = $post['time_in'];
                            // $date_now = $post['date_in'];

                            $this->db->select("d.start, d.end, d.days, d.working, d.tolerance, c.name, d.name as shift_name");
                            $this->db->from('shift_employees b');
                            $this->db->join('shifts c', 'c.id = b.shift_id');
                            $this->db->join('shift_details d', 'd.shift_id = c.id');
                            $this->db->where('b.employee_id', $employee->id);

                            $shifts = $this->db->get()->result();

                            $shift = null;

                            $previousShift = null;
                            $previousDiff = PHP_INT_MAX;

                            $nextShift = null;
                            $nextDiff = PHP_INT_MAX;

                            $timeInSeconds = strtotime($time_now);

                            foreach ($shifts as $item) {

                                /*
                                * 24:00 dianggap 00:00
                                */
                                $startTime = $item->start == '24:00:00'
                                    ? '00:00:00'
                                    : $item->start;

                                $shiftStartSeconds = strtotime($startTime);

                                $diff = $timeInSeconds - $shiftStartSeconds;

                                /*
                                * Handle perbedaan hari / midnight
                                */
                                if ($diff > 43200) {
                                    $diff -= 86400;
                                } elseif ($diff < -43200) {
                                    $diff += 86400;
                                }

                                /*
                                * SHIFT SEBELUMNYA
                                */
                                if ($diff >= 0) {

                                    if ($diff < $previousDiff) {
                                        $previousDiff = $diff;
                                        $previousShift = $item;
                                    }

                                /*
                                * SHIFT BERIKUTNYA
                                */
                                } else {

                                    $distance = abs($diff);

                                    if ($distance < $nextDiff) {
                                        $nextDiff = $distance;
                                        $nextShift = $item;
                                    }
                                }
                            }

                            /*
                             Jika shift berikutnya maksimal 3 jam,
                             gunakan shift berikutnya.
                            
                             Contoh:
                             13:00 → 16:00 = 3 jam
                             → pilih 16:00
                            */
                            if ($nextShift && $nextDiff <= 10800) {

                                $shift = $nextShift;

                            }
                            /*
                             Kalau shift berikutnya lebih dari 3 jam,
                             tetap gunakan shift sebelumnya.
                            
                             Contoh:
                             12:00 → 16:00 = 4 jam
                             → tetap 08:00
                            */
                            elseif ($previousShift) {

                                $shift = $previousShift;

                            }
                            /*
                             Kalau tidak ada shift sebelumnya,
                             gunakan shift berikutnya.
                            */
                            elseif ($nextShift) {

                                $shift = $nextShift;
                            }


                            /*
                             Setelah shift ditemukan,
                             ubah 24:00 menjadi 00:00.
                            */
                            if ($shift && $shift->start == '24:00:00') {
                                $shift->start = '00:00:00';
                            }

                            $data = array(
                                'number' => trim($user->number),
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

                                $dataLogs = array(
                                    'name' => $user->name,
                                    'number' => trim($user->number),
                                    'date_attendance' => $date_now,
                                    'time_attendance' => $time_now,
                                    'info_attendance' => "CHECK IN",
                                    'attendance_type' => $post['source'] ?? 'MOBILE',
                                    'foto' => @$foto,
                                    'shift_detail' => @$shift->name,
                                );

                                $this->mobile->create('attendances_logs', $dataLogs, $user->username);

                                die(json_encode(array(
                                    "title" => "Success",
                                    "message" => "Attendance In Completed",
                                    "theme" => "success",
                                    "results" => $shift
                                )));
                            } else {
                                show_error("Attendance In Incomplete");
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

    // test untuk check shift
    public function checkShift() {
        // $time_now = date("H:i:s");

        $time_now = '21:05:00';

        $employee = $this->mobile->read("employees", [], ["number" => "01010201260001"]);

        $this->db->select("d.start, d.end, d.days, d.working, d.tolerance, c.name, d.name as shift_name");
        $this->db->from('shift_employees b');
        $this->db->join('shifts c', 'c.id = b.shift_id');
        $this->db->join('shift_details d', 'd.shift_id = c.id');
        $this->db->where('b.employee_id', $employee->id);

        $shifts = $this->db->get()->result();

        $shift = null;

        $previousShift = null;
        $previousDiff = PHP_INT_MAX;

        $nextShift = null;
        $nextDiff = PHP_INT_MAX;

        $timeInSeconds = strtotime($time_now);

        foreach ($shifts as $item) {

            /*
            * 24:00 dianggap 00:00
            */
            $startTime = $item->start == '24:00:00'
                ? '00:00:00'
                : $item->start;

            $shiftStartSeconds = strtotime($startTime);

            $diff = $timeInSeconds - $shiftStartSeconds;

            /*
            * Handle perbedaan hari / midnight
            */
            if ($diff > 43200) {
                $diff -= 86400;
            } elseif ($diff < -43200) {
                $diff += 86400;
            }

            /*
            * SHIFT SEBELUMNYA
            */
            if ($diff >= 0) {

                if ($diff < $previousDiff) {
                    $previousDiff = $diff;
                    $previousShift = $item;
                }

            /*
            * SHIFT BERIKUTNYA
            */
            } else {

                $distance = abs($diff);

                if ($distance < $nextDiff) {
                    $nextDiff = $distance;
                    $nextShift = $item;
                }
            }
        }

        /*
        * Jika shift berikutnya maksimal 3 jam,
        * gunakan shift berikutnya.
        *
        * Contoh:
        * 13:00 → 16:00 = 3 jam
        * → pilih 16:00
        */
        if ($nextShift && $nextDiff <= 10800) {

            $shift = $nextShift;

        }
        /*
        * Kalau shift berikutnya lebih dari 3 jam,
        * tetap gunakan shift sebelumnya.
        *
        * Contoh:
        * 12:00 → 16:00 = 4 jam
        * → tetap 08:00
        */
        elseif ($previousShift) {

            $shift = $previousShift;

        }
        /*
        * Kalau tidak ada shift sebelumnya,
        * gunakan shift berikutnya.
        */
        elseif ($nextShift) {

            $shift = $nextShift;
        }


        /*
        * Setelah shift ditemukan,
        * ubah 24:00 menjadi 00:00.
        */
        if ($shift && $shift->start == '24:00:00') {
            $shift->start = '00:00:00';
        }
        
        die(json_encode(array(
            "title" => "Success",
            "message" => "Data Found",
            "theme" => "success",
            "results" => $shift
        )));
    }
}
