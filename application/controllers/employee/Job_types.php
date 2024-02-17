<?php
date_default_timezone_set("Asia/Bangkok");
defined('BASEPATH') or exit('No direct script access allowed');

class Job_types extends CI_Controller
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
        $this->form_validation->set_rules('employee_id', 'Employee ID', 'required|min_length[1]|max_length[30]');
    }

    //HALAMAN UTAMA
    public function index()
    {
        if (empty($this->session->username)) {
            redirect('error_session');
        } elseif ($this->checkuserAccess($this->id_menu()) > 0) {
            $data['button'] = $this->getbutton($this->id_menu());

            $this->load->view('template/header', $data);
            $this->load->view('employee/job_types');
        } else {
            redirect('error_access');
        }
    }

    //GET DATA
    public function reads()
    {
        $post = isset($_POST['q']) ? $_POST['q'] : "";
        $send = $this->crud->reads('job_types', ["employee_id" => $post]);
        echo json_encode($send);
    }

    //GET DATATABLES
    public function datatables()
    {
        $filter_divisions = $this->input->get('filter_divisions');
        $filter_departements = $this->input->get('filter_departements');
        $filter_departement_subs = $this->input->get('filter_departement_subs');
        $filter_employees = $this->input->get('filter_employees');
        $filter_position = $this->input->get('filter_position');
        $filter_contract = $this->input->get('filter_contract');
        $filter_job_type = $this->input->get('filter_job_type');

        $page = $this->input->post('page');
        $rows = $this->input->post('rows');

        //Pagination 1-10
        $page   = isset($page) ? intval($page) : 1;
        $rows   = isset($rows) ? intval($rows) : 10;
        $offset = ($page - 1) * $rows;
        $result = array();

        //Select Query
        $this->db->select('a.*,
            b.id as employee_id,
            b.number as employee_number,
            b.name as employee_name,
            c.name as departement_name,
            d.name as departement_sub_name,
            e.name as position_name,
            f.name as contract_name
        ');
        $this->db->from('employees b');
        $this->db->join('job_types a', 'a.employee_id = b.id', 'left');
        $this->db->join('departements c', 'b.departement_id = c.id', 'left');
        $this->db->join('departement_subs d', 'b.departement_sub_id = d.id', 'left');
        $this->db->join('positions e', 'b.position_id = e.id', 'left');
        $this->db->join('contracts f', 'b.contract_id = f.id', 'left');
        $this->db->where('b.status', 0);
        $this->db->like('b.division_id', $filter_divisions);
        $this->db->like('b.departement_id', $filter_departements);
        $this->db->like('b.departement_sub_id', $filter_departement_subs);
        $this->db->like('b.id', $filter_employees);
        $this->db->like('b.position_id', $filter_position);
        $this->db->like('b.contract_id', $filter_contract);
        if($filter_job_type == ""){
            $this->db->where('a.job_type', null);
        }elseif($filter_job_type != "-"){
            $this->db->where('a.job_type', $filter_job_type);
        }
        $this->db->order_by('b.name', 'ASC');

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

    //CREATE DATA
    public function create()
    {
        if ($this->input->post()) {
            if ($this->form_validation->run() == TRUE) {
                $post = $this->input->post();
                $job_types = $this->crud->reads("job_types", [], ["employee_id" => $post['employee_id']]);

                if ($job_types) {
                    $send = $this->crud->update('job_types', ["employee_id" => $post['employee_id']], ["job_type" => $post['job_type']]);
                } else {
                    $send   = $this->crud->create('job_types', [
                        "employee_id" => $post['employee_id'],
                        "job_type" => $post['job_type'],
                    ]);
                }

                echo $send;
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
            $approval = $this->crud->read('approvals', [], ["table_name" => "job_types"]);
            $attachment = $this->crud->upload('attachment', ['pdf', 'docx'], 'assets/document/agreement/');

            if ($approval) {
                $this->crud->update('job_types', ["number" => $post['number']], ["status" => 1]);
                $this->crud->delete('job_types', ["id" => $id]);
                $send = $this->crud->create('job_types', array_merge($post, array("attachment" => $attachment)));
            } else {
                $this->crud->update('job_types', ["number" => $post['number']], ["status" => 1]);
                $this->crud->delete('job_types', ["id" => $id]);
                $send = $this->crud->create('job_types', array_merge($post, array("attachment" => $attachment)));
                $postEmployee = array(
                    "position_id" => $post['position_id'],
                    "group_id" => $post['group_id'],
                    "contract_id" => $post['contract_id'],
                    "date_sign" => $post['date_sign'],
                    "date_expired" => $post['date_expired'],
                );
                $this->crud->update('employees', ["number" => $post['number']], $postEmployee);
            }
            echo $send;
        } else {
            show_error("Cannot Process your request");
        }
    }

    //DELETE DATA
    public function delete()
    {
        $data = $this->input->post();
        $send = $this->crud->delete('job_types', $data);
        echo $send;
    }

    //PRINT & EXCEL DATA
    public function print($option = "")
    {
        if ($option == "excel") {
            $format  = date("Ymd");
            header("Content-type: application/vnd-ms-excel");
            header("Content-Disposition: attachment; filename=job_types_$format.xls");
        }

        //Filter Data
        $filter_divisions = $this->input->get('filter_divisions');
        $filter_departements = $this->input->get('filter_departements');
        $filter_departement_subs = $this->input->get('filter_departement_subs');
        $filter_employees = $this->input->get('filter_employees');
        $filter_position = $this->input->get('filter_position');
        $filter_contract = $this->input->get('filter_contract');
        $filter_job_type = $this->input->get('filter_job_type');

        //Config
        $this->db->select('*');
        $this->db->from('config');
        $config = $this->db->get()->row();

        $this->db->select('a.*, 
            b.number as employee_number,
            b.name as employee_name,
            c.name as departement_name,
            d.name as departement_sub_name,
            e.name as position_name,
            f.name as contract_name
        ');
        $this->db->from('employees b');
        $this->db->join('job_types a', 'a.employee_id = b.id', 'left');
        $this->db->join('departements c', 'b.departement_id = c.id', 'left');
        $this->db->join('departement_subs d', 'b.departement_sub_id = d.id', 'left');
        $this->db->join('positions e', 'b.position_id = e.id', 'left');
        $this->db->join('contracts f', 'b.contract_id = f.id', 'left');
        $this->db->where('b.status', 0);
        $this->db->like('b.division_id', $filter_divisions);
        $this->db->like('b.departement_id', $filter_departements);
        $this->db->like('b.departement_sub_id', $filter_departement_subs);
        $this->db->like('b.id', $filter_employees);
        $this->db->like('b.position_id', $filter_position);
        $this->db->like('b.contract_id', $filter_contract);
        if($filter_job_type == ""){
            $this->db->where('a.job_type', null);
        }elseif($filter_job_type != "-"){
            $this->db->where('a.job_type', $filter_job_type);
        }
        $this->db->order_by('b.name', 'ASC');
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
                            <small>JOB TYPES</small>
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
                <th>Employee ID</th>
                <th>Employee Name</th>
                <th>Departement</th>
                <th>Departement Sub</th>
                <th>Position</th>
                <th>Employee Type</th>
                <th>Job Type</th>
            </tr>';
        $no = 1;
        foreach ($records as $data) {
            $html .= '  <tr>
                            <td>' . $no . '</td>
                            <td>' . $data['employee_number'] . '</td>
                            <td>' . $data['employee_name'] . '</td>
                            <td>' . $data['departement_name'] . '</td>
                            <td>' . $data['departement_sub_name'] . '</td>
                            <td>' . $data['position_name'] . '</td>
                            <td>' . $data['contract_name'] . '</td>
                            <td>' . $data['job_type'] . '</td>
                        </tr>';
            $no++;
        }

        $html .= '</table></body></html>';
        echo $html;
    }
}
