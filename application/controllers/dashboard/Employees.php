<?php
date_default_timezone_set("Asia/Bangkok");
defined('BASEPATH') or exit('No direct script access allowed');

class Employees extends CI_Controller
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

    public function index()
    {
        if ($this->session->username != "") {
            $data['config'] = $this->crud->read('config');
            $data['session_name'] = $this->session->name;

            $this->load->view('template/header');
            $this->load->view('dashboard/employees', $data);
        } else {
            redirect('error_session');
        }
    }

    public function chartDepartement()
    {
        $querys = $this->crud->query("SELECT b.name, COUNT(a.number) as total FROM departements b LEFT JOIN employees a ON a.departement_id = b.id and a.status = 0 GROUP BY b.name");
        $name = array();
        $number = array();

        $html = "";
        foreach ($querys as $data) {
            $name[] = $data->name;
            $number[] = (int) $data->total;
            $html .= "<tr><td>".$data->name."</td><td>".$data->total."</td></tr>";
        }

        echo json_encode(array("name" => $name, "number" => $number, "html" => $html));
    }

    public function chartGroups()
    {
        $querys = $this->crud->query("SELECT b.name, COUNT(a.number) as total FROM groups b LEFT JOIN employees a ON a.group_id = b.id and a.status = 0 GROUP BY b.name");
        $name = array();
        $number = array();

        $html = "";
        foreach ($querys as $data) {
            $name[] = $data->name;
            $number[] = (int) $data->total;
            $html .= "<tr><td>".$data->name."</td><td>".$data->total."</td></tr>";
        }

        echo json_encode(array("name" => $name, "number" => $number, "html" => $html));
    }

    public function chartService()
    {
        $query = $this->crud->query("SELECT 
            SUM(CASE WHEN (a.usia >= 18) and (a.usia <= 25) THEN 1 ELSE 0 END) as satu,
            SUM(CASE WHEN (a.usia >= 26) and (a.usia <= 30) THEN 1 ELSE 0 END) as dua,
            SUM(CASE WHEN (a.usia >= 31) and (a.usia <= 35) THEN 1 ELSE 0 END) as tiga,
            SUM(CASE WHEN (a.usia >= 36) and (a.usia <= 40) THEN 1 ELSE 0 END) as empat,
            SUM(CASE WHEN (a.usia >= 41) and (a.usia <= 49) THEN 1 ELSE 0 END) as lima,
            SUM(CASE WHEN (a.usia >= 50) and (a.usia <= 55) THEN 1 ELSE 0 END) as enam,
            SUM(CASE WHEN (a.usia >= 56) THEN 1 ELSE 0 END) as tujuh
        FROM (SELECT TIMESTAMPDIFF(YEAR, birthday, CURDATE()) AS usia FROM employees WHERE status = 0) a");
        
        $name = array();
        $number = array();
        $html = "";
        foreach ($query as $row) {
            $name = array("18 to 25", "26 to 30", "31 to 35", "36 to 40", "41 to 49", "50 to 55", "> 56");
            $number = array((int) $row->satu, (int) $row->dua, (int) $row->tiga, (int) $row->empat, (int) $row->lima, (int) $row->enam, (int) $row->tujuh);
            $html .= "  <tr><td>18 to 25</td><td>".$row->satu."</td></tr>
                        <tr><td>26 to 30</td><td>".$row->dua."</td></tr>
                        <tr><td>31 to 35</td><td>".$row->tiga."</td></tr>
                        <tr><td>36 to 40</td><td>".$row->empat."</td></tr>
                        <tr><td>41 to 49</td><td>".$row->lima."</td></tr>
                        <tr><td>50 to 55</td><td>".$row->enam."</td></tr>
                        <tr><td> > 56<td>".$row->tujuh."</td></tr>";
        }

        echo json_encode(array("name" => $name, "number" => $number, "html" => $html));
    }

    public function chartGender()
    {
        $querys = $this->crud->query("SELECT (CASE WHEN gender = '' THEN 'EMPTY' ELSE gender END) as gender, COUNT(number) as total FROM employees WHERE status = 0 GROUP BY gender");
        $name = array();

        foreach ($querys as $data) {
            $name[] = array("name" => $data->gender, "y" => (int) $data->total);
        }

        echo json_encode($name);
    }

    public function chartMarital()
    {
        $querys = $this->crud->query("SELECT 
            SUM(CASE WHEN b.number = 'TK/0' THEN 1 ELSE 0 END) as single, 
            SUM(CASE WHEN b.number != 'TK/0' THEN 1 ELSE 0 END) as maried 
        FROM employees a JOIN maritals b ON a.marital_id = b.id WHERE a.status = 0");
        
        $name = array();
        $number = array();
        foreach ($querys as $data) {
            $name[] = array("name" => "Single", "y" => (int) $data->single);
            $name[] = array("name" => "Married", "y" => (int) $data->maried);
        }
 
        echo json_encode($name);
    }

    public function chartDomicile()
    {
        $querys = $this->crud->query("SELECT 
            SUM(CASE WHEN a.address LIKE '%purwakarta%' THEN 1 ELSE 0 END) as purwakarta, 
            SUM(CASE WHEN a.address NOT LIKE '%purwakarta%' THEN 1 ELSE 0 END) as not_purwakarta 
        FROM employees a WHERE a.status = 0");
        
        $name = array();
        $number = array();
        foreach ($querys as $data) {
            $name[] = array("name" => "Purwakarta", "y" => (int) $data->purwakarta);
            $name[] = array("name" => "Outside", "y" => (int) $data->not_purwakarta);
        }
 
        echo json_encode($name);
    }

    public function chartEducation()
    {
        $querys = $this->crud->query("SELECT b.level, COUNT(a.number) as number FROM employees a JOIN (select number, level from employee_educations GROUP by number) b ON a.number = b.number WHERE a.status = 0 GROUP BY b.level");
        
        $name = array();
        $number = array();
        foreach ($querys as $data) {
            $name[] = array("name" => $data->level, "y" => (int) $data->number);
        }
 
        echo json_encode($name);
    }
}
