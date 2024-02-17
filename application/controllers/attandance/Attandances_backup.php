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
            $result = array();

            //Employee
            $this->db->select("a.id, b.id as attandance_id, a.number, a.name, a.division_id, a.departement_id, a.departement_sub_id,
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
            $this->db->group_by("a.number");
            $this->db->group_by("b.date_in");
            $this->db->order_by("d.name", "asc");
            $this->db->order_by("a.name", "asc");
            $this->db->order_by("b.date_in", "asc");
            $employees = $this->db->get()->result_array();

            die(json_encode($employees));

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
        // menggunakan class Datetime
        $datetime = DateTime::createFromFormat('d/m/Y', $date);
        return @$datetime->format('Y-m-d');
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

            for ($i = 2; $i <= $total_row; $i++) {
                $datein = explode(";", $data->val($i, 4));
                $datas[] = array(
                    'lokasi' => $data->val($i, 5),
                    'number' => trim($data->val($i, 6)),
                    'date_in' => $datein[0] . " " . $datein[1],
                    'date_out' => $datein[0] . " " . $datein[1]
                );
                //$this->create_temp(trim($data->val($i, 6)), trim(date('Y-m-d', strtotime($data->val($i, 4)))), trim(date('H:i:s', strtotime($data->val($i, 4)))));
            }

            // $this->db->select('*');
            // $this->db->from('attandance_temp');
            // $this->db->order_by('number', 'asc');
            // $this->db->order_by('attandance_date', 'asc');
            // $attandance = $this->db->get()->result_object();

            // foreach ($attandance as $data) {
            //     $datas[] = array(
            //         'number' => $data->number,
            //         'date_in' => date('d/m/Y H:i:s', strtotime($data->attandance_date)),
            //         'date_out' => date('d/m/Y H:i:s', strtotime($data->attandance_date))
            //     );
            // }

            $datas['total'] = count($datas);
            echo json_encode($datas);

            unlink($_FILES['file_upload']['name']);
            //$this->db->truncate('attandance_temp');
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
            $data = $this->input->post('data');
            $explode = explode(" ", $data['date_in']);
            $date_in = $this->formatTanggal($explode[0]);
            $time_in = $explode[1];

            //Attandance
            $this->db->select('*');
            $this->db->from('attandances');
            $this->db->where('number', $data['number']);
            $this->db->where("date_in = '$date_in'");
            $attandance_in = $this->db->get()->row();

            //Attandance
            $this->db->select('*');
            $this->db->from('attandances');
            $this->db->where('number', $data['number']);
            $this->db->where("date_out = '$date_in'");
            $attandance_out = $this->db->get()->row();

            //Employee
            $this->db->select('*');
            $this->db->from('employees');
            $this->db->where('number', $data['number']);
            $employee = $this->db->get()->row();

            //Jika Terdaftar di master employee
            if (!empty($employee->number)) {
                if ($data['lokasi'] == 1) {
                    $yesterday = date("Y-m-d", strtotime('-1 days', strtotime($date_in)));

                    //Attandance
                    $this->db->select('*');
                    $this->db->from('attandances');
                    $this->db->where('number', $data['number']);
                    $this->db->where("date_out = '$yesterday'");
                    $attandance_yesterday_in = $this->db->get()->row();

                    if (!empty($attandance_in->number)) {
                        $data_attandance = array(
                            'number' => $data['number'],
                            'date_in' => $date_in,
                            'time_in' => $time_in,
                        );
                        $this->crud->update('attandances', ["id" => $attandance_in->id], $data_attandance);
                        echo json_encode(array("title" => "Updated", "message" => $employee->name . " | " . $date_in . " " . $time_in . " Update data Check In", "theme" => "success"));
                    } else if (!empty($attandance_yesterday_in->number)) {
                        if ($attandance_yesterday_in->date_in == "") {
                            $data_attandance = array(
                                'number' => $data['number'],
                                'date_in' => $date_in,
                                'time_in' => $time_in,
                            );
                            $this->crud->update('attandances', ["id" => $attandance_yesterday_in->id], $data_attandance);
                            echo json_encode(array("title" => "Updated", "message" => $employee->name . " | " . $date_in . " " . $time_in . " Update data Check In", "theme" => "success"));
                        } else {
                            $data_attandance = array(
                                'number' => $data['number'],
                                'date_in' => $date_in,
                                'time_in' => $time_in,
                            );
                            $this->crud->create('attandances', $data_attandance);
                            echo json_encode(array("title" => "Created", "message" => $employee->name . " | " . $date_in . " " . $time_in . " Create data Check In", "theme" => "success"));
                        }
                    } else {
                        $data_attandance = array(
                            'number' => $data['number'],
                            'date_in' => $date_in,
                            'time_in' => $time_in,
                        );
                        $this->crud->create('attandances', $data_attandance);
                        echo json_encode(array("title" => "Created", "message" => $employee->name . " | " . $date_in . " " . $time_in . " Create data Check In", "theme" => "success"));
                    }
                } else {
                    $yesterday = date("Y-m-d", strtotime('-1 days', strtotime($date_in)));

                    //Attandance
                    $this->db->select('*');
                    $this->db->from('attandances');
                    $this->db->where('number', $data['number']);
                    $this->db->where("date_in = '$yesterday'");
                    $attandance_yesterday_out = $this->db->get()->row();

                    if (!empty($attandance_out->number)) {
                        $data_attandance = array(
                            'number' => $data['number'],
                            'date_out' => $date_in,
                            'time_out' => $time_in,
                        );
                        $this->crud->update('attandances', ["id" => $attandance_out->id], $data_attandance);
                        echo json_encode(array("title" => "Updated", "message" => $employee->name . " | " . $date_in . " " . $time_in . " Update data Check Out", "theme" => "success"));
                    } else if (!empty($attandance_yesterday_out->number)) {
                        if ($attandance_yesterday_out->date_out == "") {
                            $data_attandance = array(
                                'number' => $data['number'],
                                'date_out' => $date_in,
                                'time_out' => $time_in,
                            );
                            $this->crud->update('attandances', ["id" => $attandance_yesterday_out->id], $data_attandance);
                            echo json_encode(array("title" => "Updated", "message" => $employee->name . " | " . $date_in . " " . $time_in . " Update data Check Out", "theme" => "success"));
                        } elseif (!empty($attandance_in->number)) {
                            $data_attandance = array(
                                'number' => $data['number'],
                                'date_out' => $date_in,
                                'time_out' => $time_in,
                            );
                            $this->crud->update('attandances', ["id" => $attandance_in->id], $data_attandance);
                            echo json_encode(array("title" => "Updated", "message" => $employee->name . " | " . $date_in . " " . $time_in . " Update data Check Out", "theme" => "success"));
                        } else {
                            $data_attandance = array(
                                'number' => $data['number'],
                                'date_in' => $date_in,
                                'time_in' => "00:00:00",
                                'date_out' => $date_in,
                                'time_out' => $time_in,
                            );
                            $this->crud->create('attandances', $data_attandance);
                            echo json_encode(array("title" => "Created", "message" => $employee->name . " | " . $date_in . " " . $time_in . " Create data Check Out", "theme" => "success"));
                        }
                    } else {
                        $data_attandance = array(
                            'number' => $data['number'],
                            'date_in' => $date_in,
                            'time_in' => "00:00:00",
                            'date_out' => $date_in,
                            'time_out' => $time_in,
                        );
                        $this->crud->create('attandances', $data_attandance);
                        echo json_encode(array("title" => "Created", "message" => $employee->name . " | " . $date_in . " " . $time_in . " Create data Check Out", "theme" => "success"));
                    }
                }
            } else {
                echo json_encode(array("title" => "Warning", "message" => $data['number'] . " Empoyee ID Un Registered", "theme" => "error"));
            }
        }
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
        echo "<center><h4>Not Available Report Attandance in this module</h4></center>";
    }
}
