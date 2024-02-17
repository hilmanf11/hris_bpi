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
    public function index($token = "")
    {
        if ($token == "") {
            $this->load->view('template/header_mobile');
            $this->load->view('mobile/404');
        } else {
            $users_m = $this->crud->read("users_m", [], ["token" => $token]);
            $employee = $this->crud->read("employees", [], ["number" => $users_m->number]);
            $users = $this->crud->read("users", [], ["number" => $users_m->number]);
            if (empty($this->session->username)) {
                $this->session->set_userdata(["username" => $users->username]);
            }

            $this->db->select("d.start, d.end, d.days, d.working, d.tolerance, d.name as shift_name");
            $this->db->from('shift_employees b');
            $this->db->join('shifts c', 'c.id = b.shift_id');
            $this->db->join('shift_details d', 'd.shift_id = c.id');
            $this->db->where('b.employee_id', $employee->id);
            $this->db->order_by('d.start', 'asc');
            $shifts = $this->db->get()->result();

            $data['number'] = $users_m->number;
            $data['shifts'] = $shifts;
            $data['token'] = $token;

            $this->load->view('template/header_mobile');
            $this->load->view('mobile/attandances', $data);
        }
    }

    public function absence($token = ""){
        if ($token == "") {
            $this->load->view('template/header_mobile');
            $this->load->view('mobile/404');
        } else {
            $users_m = $this->crud->read("users_m", [], ["token" => $token]);
            $employee = $this->crud->read("employees", [], ["number" => $users_m->number]);
            $users = $this->crud->read("users", [], ["number" => $users_m->number]);
            if (empty($this->session->username)) {
                $this->session->set_userdata(["username" => $users->username]);
            }

            $data['number'] = $users_m->number;

            $this->load->view('template/header_mobile');
            $this->load->view('mobile/attandances_absense', $data);
        }
    }

    public function getMaps($destination_lat, $destination_long)
    {
        //Config
        $this->db->select('*');
        $this->db->from('config');
        $config = $this->db->get()->row();

        $api = file_get_contents("https://maps.googleapis.com/maps/api/distancematrix/json?key=AIzaSyDgB4BT24dZow_JdOKJrO1YZIPTkq9jIeY&origins=" . $config->latitude . "," . $config->longitude . "&destinations=" . $destination_lat . "," . $destination_long . "&language=en-EN&sensor=false");
        $json = json_decode($api, true);
        $rows = $json['rows'][0];
        $elements = $rows['elements'][0];
        $distance = $elements['distance'];

        //die($api);
        //die(json_encode($distance['value']));

        return $distance['value'];
    }

    public function checkin()
    {
        if ($this->input->post()) {
            $token_in = $this->input->post("token_in");
            $number = $this->input->post("number");
            $latitude = $this->input->post("latitude");
            $longitude = $this->input->post("longitude");

            $distance = $this->getMaps($latitude, $longitude);

            $this->db->select('*');
            $this->db->from('config');
            $config = $this->db->get()->row();

            if ($token_in != $config->token) {
                die(json_encode(array("message" => "QR Code not found, you are cannot check in using this code", "theme" => "error")));
            } elseif ($distance > 500) {
                die(json_encode(array("message" => "Your location is far from " . $config->name, "theme" => "error")));
            } else {
                $date_in = date("Y-m-d");
                $time_in = date("H:i:s");
                $attandance_in = $this->crud->read("attandances", [], ["number" => $number, "date_in" => $date_in]);
                //Jika tanggal sekarang belum ada absen maka
                if (empty($attandance_in->number)) {
                    //Tanggal terakhir
                    $attandance_last = $this->crud->read("attandances", [], ["number" => $number], "1", "date_in", "desc");
                    //Jika tanggal terakhir dan checkout nya kosong maka
                    if ($attandance_last->time_out == null) {
                        //Update absen pulang
                        $this->crud->update("attandances", ["number" => $number, "id" => $attandance_last->id], ["date_out" => $date_in, "time_out" => $time_in]);
                        die(json_encode(array("message" => "Check Out Success", "theme" => "success")));
                    } else {
                        $this->crud->create("attandances", ["number" => $number, "date_in" => $date_in, "time_in" => $time_in]);
                        die(json_encode(array("message" => "Check In Success", "theme" => "success")));
                    }
                } elseif ($attandance_in->time_out == null) {
                    $this->crud->update("attandances", ["number" => $number, "id" => $attandance_in->id], ["date_out" => $date_in, "time_out" => $time_in]);
                    die(json_encode(array("message" => "Check Out Success", "theme" => "success")));
                } else {
                    die(json_encode(array("message" => "On this date you have Checked In", "theme" => "error")));
                }
            }
        }
    }

    function datatables()
    {
        if ($this->input->post()) {
            $number = $this->input->post("number");
            $date_from = $this->input->post("date_from");
            $date_to = $this->input->post("date_to");

            $records = $this->crud->query("SELECT * FROM attandances WHERE `number` = '$number' and date_in BETWEEN '$date_from' and '$date_to' ORDER BY date_in ASC limit 31");
            if (count($records) > 0) {
                $html = '<ul class="list-group mb-4">';
                foreach ($records as $record) {
                    $html .= '  <li class="list-group-item">
                                    <div class="float-start">
                                        <b>' . date("d F Y", strtotime($record->date_in)) . '</b>
                                    </div>
                                    <div class="float-end">
                                        <span class="badge bg-success">' . $record->time_in . '</span> <span class="badge bg-danger">' . $record->time_out . '</span>
                                    </div>
                                </li>';
                }

                $html .= '</ul>';
            } else {
                $html = '   <div class="alert alert-warning mt-4" role="alert">
                                Attandance Not Found
                            </div>';
            }

            die($html);
        }
    }
}
