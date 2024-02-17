<?php
date_default_timezone_set("Asia/Bangkok");
defined('BASEPATH') or exit('No direct script access allowed');

class Candidates extends CI_Controller
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
        $this->form_validation->set_rules('request_id', 'Request ID', 'required|min_length[1]|max_length[20]|is_unique[employee_candidates.request_id]');
    }

    //HALAMAN UTAMA
    public function index()
    {
        if (empty($this->session->username)) {
            redirect('error_session');
        } elseif ($this->checkuserAccess($this->id_menu()) > 0) {
            $data['button'] = $this->getbutton($this->id_menu());
            $data['psychologicals'] = $this->crud->reads('requirements', [], ["category" => "PSYCHOLOGICAL"]);
            $data['interviews'] = $this->crud->reads('requirements', [], ["category" => "INTERVIEW"]);
            $data['medicals'] = $this->crud->reads('requirements', [], ["category" => "MEDICAL"]);
            $data['skills'] = $this->crud->reads('requirements', [], ["category" => "SKILL"]);
            $this->load->view('template/header', $data);
            $this->load->view('requitment/candidates');
        } else {
            redirect('error_access');
        }
    }

    //GET DATA
    public function reads()
    {
        $post = isset($_POST['q']) ? $_POST['q'] : "";
        $get = $this->input->get();
        $send = $this->crud->reads('employee_candidates', ["name" => $post, "status" => 1], $get);
        echo json_encode($send);
    }

    //GET DATATABLES
    public function datatables()
    {
        if ($this->input->post()) {
            $filter_request = $this->input->get('filter_request');
            $filter_candidate = $this->input->get('filter_candidate');
            $aprvDepartement = $this->checkApprovalAccess('employee_candidates');

            $page = $this->input->post('page');
            $rows = $this->input->post('rows');
            //Pagination 1-10
            $page   = isset($page) ? intval($page) : 1;
            $rows   = isset($rows) ? intval($rows) : 10;
            $offset = ($page - 1) * $rows;
            $result = array();
            //Select Query
            $this->db->select('a.*, 
                b.request_by,
                i.users_id_from as status_check,
                i.users_id_to as status_notification, 
                c.name as division_name, 
                d.name as departement_name,
                e.name as departement_sub_name');
            $this->db->from('employee_candidates a');
            $this->db->join('employee_requests b', 'a.request_id = b.id');
            $this->db->join('divisions c', 'a.division_id = c.id');
            $this->db->join('departements d', 'a.departement_id = d.id');
            $this->db->join('departement_subs e', 'a.departement_sub_id = e.id');
            $this->db->join('notifications i', "a.id = i.table_id and i.table_name = 'employee_candidates'", 'left');
            $this->db->where('a.deleted', 0);
            $this->db->like('a.departement_id', $aprvDepartement);
            $this->db->like('a.request_id', $filter_request);
            $this->db->like('a.name', $filter_candidate);
            $this->db->order_by('a.request_id', 'DESC');
            $this->db->order_by('a.name', 'ASC');
            //Total Data
            $totalRows = $this->db->count_all_results('', false);
            //Limit 1 - 10
            $this->db->limit($rows, $offset);
            //Get Data Array
            $records = $this->db->get()->result_array();
            //Mapping Data
            $result['total'] = $totalRows;
            $result = array_merge($result, ['rows' => $records]);
            echo json_encode($result);
        }
    }

    //CREATE DATA
    public function create()
    {
        if ($this->input->post()) {
            if ($this->form_validation->run() == TRUE) {
                $post   = $this->input->post();
                $resume = $this->crud->upload('resume', ['pdf'], 'assets/document/reqruitment/resume/');
                $certificate = $this->crud->upload('certificate', ['pdf'], 'assets/document/reqruitment/certificate/');
                $health = $this->crud->upload('health', ['pdf'], 'assets/document/reqruitment/health/');
                $image_id = $this->crud->upload('image_id', ['jpg', 'png', 'jpeg'], 'assets/image/employee/id/');
                $image_profile = $this->crud->upload('image_profile', ['jpg', 'png', 'jpeg'], 'assets/image/employee/profile/');
                $send   = $this->crud->create('employee_candidates', array_merge($post, ["resume" => $resume, "certificate" => $certificate, "health" => $health, "image_id" => $image_id, "image_profile" => $image_profile]));
                echo $send;
            } else {
                show_error(validation_errors());
            }
        } else {
            show_error("Cannot Process your request");
        }
    }

    public function createPsychologicals()
    {
        if ($this->input->post()) {
            $post   = $this->input->post();
            $send   = $this->crud->update('employee_candidates', ["id" => $post['id']], [
                "psycological" => json_encode(array(
                    "requirement_id" => $post['requirement_id'],
                    "standard_score" => $post['standard_score'],
                    "final_score" => $post['final_score'],
                    "remarks" => $post['remarks']
                )),
                "psycological_date" => $post['trans_date'],
                "psycological_total" => array_sum($post['final_score']),
                "psycological_judgement" => $post['psycological_judgement'],
            ]);
            $this->updateStatus($post['id']);
            echo $send;
        } else {
            show_error("Cannot Process your request");
        }
    }

    public function createInterviews()
    {
        if ($this->input->post()) {
            $post   = $this->input->post();
            $send   = $this->crud->update('employee_candidates', ["id" => $post['id']], [
                "interview" => json_encode(array(
                    "requirement_id" => $post['requirement_id'],
                    "standard_score" => $post['standard_score'],
                    "final_score" => $post['final_score'],
                    "remarks" => $post['remarks']
                )),
                "interview_date" => $post['trans_date'],
                "interview_total" => array_sum($post['final_score']),
                "interview_judgement" => $post['interview_judgement'],
            ]);
            $this->updateStatus($post['id']);
            echo $send;
        } else {
            show_error("Cannot Process your request");
        }
    }

    public function createMedicals()
    {
        if ($this->input->post()) {
            $post   = $this->input->post();
            $send   = $this->crud->update('employee_candidates', ["id" => $post['id']], [
                "medical" => json_encode(array(
                    "requirement_id" => $post['requirement_id'],
                    "standard_score" => $post['standard_score'],
                    "final_score" => $post['final_score'],
                    "remarks" => $post['remarks']
                )),
                "medical_date" => $post['trans_date'],
                "medical_total" => array_sum($post['final_score']),
                "medical_judgement" => $post['medical_judgement'],
            ]);
            $this->updateStatus($post['id']);
            echo $send;
        } else {
            show_error("Cannot Process your request");
        }
    }

    public function createSkills()
    {
        if ($this->input->post()) {
            $post   = $this->input->post();
            $send   = $this->crud->update('employee_candidates', ["id" => $post['id']], [
                "skill" => json_encode(array(
                    "requirement_id" => $post['requirement_id'],
                    "standard_score" => $post['standard_score'],
                    "final_score" => $post['final_score'],
                    "remarks" => $post['remarks']
                )),
                "skill_date" => $post['trans_date'],
                "skill_total" => array_sum($post['final_score']),
                "skill_judgement" => $post['skill_judgement'],
            ]);
            $this->updateStatus($post['id']);
            echo $send;
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
            $resume = $this->crud->upload('resume', ['pdf'], 'assets/document/reqruitment/resume/');
            $certificate = $this->crud->upload('certificate', ['pdf'], 'assets/document/reqruitment/certificate/');
            $health = $this->crud->upload('health', ['pdf'], 'assets/document/reqruitment/health/');
            $image_id = $this->crud->upload('image_id', ['jpg', 'png', 'jpeg'], 'assets/image/employee/id/');
            $image_profile = $this->crud->upload('image_profile', ['jpg', 'png', 'jpeg'], 'assets/image/employee/profile/');
            $send = $this->crud->update('employee_candidates', ["id" => $id], array_merge($post, ["resume" => $resume, "certificate" => $certificate, "health" => $health, "image_id" => $image_id, "image_profile" => $image_profile]));
            echo $send;
        } else {
            show_error("Cannot Process your request");
        }
    }

    public function updateStatus($id)
    {
        $this->db->select("*");
        $this->db->from('employee_candidates');
        $this->db->where('id', $id);
        $this->db->where("psycological != '' and interview != '' and medical != '' and skill != ''");
        $records = $this->db->get()->result_array();

        if ($records) {
            $this->crud->update("employee_candidates", ["id" => $id], ["status" => 1]);
        }

        return true;
    }

    //DELETE DATA
    public function delete()
    {
        $data = $this->input->post();
        $send = $this->crud->delete('employee_candidates', $data);
        echo $send;
    }

    public function closing()
    {
        if ($this->input->post()) {
            $id   = $this->input->post('id');
            $send = $this->crud->update('employee_requests', ["id" => $id], ["status" => 1]);
            echo $send;
        } else {
            show_error("Cannot Process your request");
        }
    }

    //PRINT & EXCEL DATA
    public function print($option = "")
    {
        if ($option == "excel") {
            $format  = date("Ymd");
            header("Content-type: application/vnd-ms-excel");
            header("Content-Disposition: attachment; filename=employee_candidates_$format.xls");
        }

        $filter_request = $this->input->get('filter_request');
        $filter_candidate = $this->input->get('filter_candidate');
        $aprvDepartement = $this->checkApprovalAccess('employee_candidates');

        //Config
        $this->db->select('*');
        $this->db->from('config');
        $config = $this->db->get()->row();

        $this->db->select('a.*, 
                i.users_id_from as status_check,
                i.users_id_to as status_notification, 
                c.name as division_name, 
                d.name as departement_name,
                e.name as departement_sub_name');
        $this->db->from('employee_candidates a');
        $this->db->join('employee_requests b', 'a.request_id = b.id');
        $this->db->join('divisions c', 'a.division_id = c.id');
        $this->db->join('departements d', 'a.departement_id = d.id');
        $this->db->join('departement_subs e', 'a.departement_sub_id = e.id');
        $this->db->join('notifications i', "a.id = i.table_id and i.table_name = 'employee_candidates'", 'left');
        $this->db->where('a.deleted', 0);
        $this->db->like('a.departement_id', $aprvDepartement);
        $this->db->like('a.request_id', $filter_request);
        $this->db->like('a.name', $filter_candidate);
        $this->db->order_by('a.request_id', 'DESC');
        $this->db->order_by('a.name', 'ASC');
        $records = $this->db->get()->result_array();

        $html = '<html><head><title>Print Data</title></head><style>body {font-family: Arial, Helvetica, sans-serif;}#customers {border-collapse: collapse;width: 100%;font-size: 12px;}#customers td, #customers th {border: 1px solid #ddd;padding: 2px;}#customers tr:nth-child(even){background-color: #f2f2f2;}#customers tr:hover {background-color: #ddd;}#customers th {padding-top: 2px;padding-bottom: 2px;text-align: left;color: black;}</style><body>
        <center>
            <div style="float: left; font-size: 12px; text-align: left;">
                <table style="width: 100%;">
                    <tr>
                        <td width="50" style="font-size: 12px; vertical-align: top; text-align: center; vertical-align:jus margin-right:10px;">
                            <img src="' . $config->favicon . '" width="30">
                        </td>
                        <td style="font-size: 14px; text-align: left; margin:2px;">
                            <b>' . $config->name . '</b><br>
                            <small>EMPLOYEE CANDIDATE</small>
                        </td>
                    </tr>
                </table>
            </div>
            <div style="float: right; font-size: 12px; text-align: right;">
                Print Date ' . date("d M Y H:m:s") . ' <br>
                Print By ' . $this->session->username . '  
            </div>
        </center>
        <br><br><br>
        
        <table id="customers" border="1">
            <tr>
                <th width="20">No</th>
                <th>Name</th>
                <th>Division</th>
                <th>Departement</th>
                <th>Departement Sub</th>
                <th>Email</th>
                <th>Mobile Phone</th>
                <th>Place Birth</th>
                <th>Birthday</th>
                <th>Gender</th>
                <th>Psychology</th>
                <th>Interview</th>
                <th>Medical</th>
                <th>Skill</th>
            </tr>';
        $no = 1;
        foreach ($records as $data) {
            $html .= '<tr>
                    <td>' . $no . '</td>
                    <td>' . $data['name'] . '</td>
                    <td>' . $data['division_name'] . '</td>
                    <td>' . $data['departement_name'] . '</td>
                    <td>' . $data['departement_sub_name'] . '</td>
                    <td>' . $data['email'] . '</td>
                    <td>' . $data['mobile_phone'] . '</td>
                    <td>' . $data['place_birth'] . '</td>
                    <td>' . $data['birthday'] . '</td>
                    <td>' . $data['gender'] . '</td>
                    <td>' . $data['psycological_total'] . '</td>
                    <td>' . $data['interview_total'] . '</td>
                    <td>' . $data['medical_total'] . '</td>
                    <td>' . $data['skill_total'] . '</td>';
            $no++;
        }

        $html .= '</table></body></html>';
        echo $html;
    }
}
