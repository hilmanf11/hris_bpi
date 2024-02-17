<?php
date_default_timezone_set("Asia/Bangkok");
defined('BASEPATH') or exit('No direct script access allowed');

class Requests extends CI_Controller
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
        $this->form_validation->set_rules('departement_sub_id', 'Departement', 'required|min_length[1]|max_length[30]');
    }

    //HALAMAN UTAMA
    public function index()
    {
        if (empty($this->session->username)) {
            redirect('error_session');
        } elseif ($this->checkuserAccess($this->id_menu()) > 0) {
            $data['button'] = $this->getbutton($this->id_menu());

            $this->load->view('template/header', $data);
            $this->load->view('requitment/requests');
        } else {
            redirect('error_access');
        }
    }

    //GET DATA
    public function readRequests()
    {
        $post = isset($_POST['q']) ? $_POST['q'] : "";
        $this->db->select('a.*, b.name as division_name, c.name as departement_name, d.name as departement_sub_name, i.users_id_from as status_check, i.users_id_to as status_notification');
        $this->db->from('employee_requests a');
        $this->db->join('divisions b', 'a.division_id = b.id');
        $this->db->join('departements c', 'a.departement_id = c.id');
        $this->db->join('departement_subs d', 'a.departement_sub_id = d.id');
        $this->db->join('notifications i', "a.id = i.table_id and i.table_name = 'employee_requests'", 'left');
        $this->db->like('a.request_by', $post);
        $this->db->where('a.status', 0);
        $this->db->where("(i.users_id_to = '' or i.users_id_to is null)");
        $this->db->group_by('a.request_by');
        $this->db->group_by('a.request_date');
        $this->db->order_by('a.request_date', 'desc');
        $records = $this->db->get()->result_array();
        echo json_encode($records);
    }

    public function readInformation()
    {
        $datenow = date("Y-m-d");
        $aprvDepartement = $this->checkApprovalAccess('employee_requests');
        $this->db->select('request_by, due_date');
        $this->db->from('employee_requests');
        $this->db->like('departement_id', $aprvDepartement);
        $this->db->where('due_date >=', $datenow);
        $this->db->order_by('due_date', 'asc');
        $records = $this->db->get()->result_array();

        foreach ($records as $data) {
            $date1 = new DateTime($datenow);
            $date2 = new DateTime($data['due_date']);
            $days = $date2->diff($date1)->days + 1;

            if ($days <= 2) {
                echo '<b style="margin-bottom:5px; color:red;">Timeout</b> | This Request By <b>' . $data['request_by'] . '</b> only ' . $days . ' days left <br>';
            } elseif ($days <= 10) {
                echo '<b style="margin-bottom:5px; color:orange;">Warning</b> | This Request By <b>' . $data['request_by'] . '</b> only ' . $days . ' days left <br>';
            } else {
                echo '<b style="margin-bottom:5px; color:green;">Progress</b> | This Request By <b>' . $data['request_by'] . '</b> only ' . $days . ' days left <br>';
            }
        }
    }

    //GET DATATABLES
    public function datatables()
    {
        if ($this->input->post()) {
            $filter_from = $this->input->get('filter_from');
            $filter_to = $this->input->get('filter_to');
            $filter_request = $this->input->get('filter_request');
            $filter_approval = $this->input->get('filter_approval');
            $aprvDepartement = $this->checkApprovalAccess('employee_requests');

            $page = $this->input->post('page');
            $rows = $this->input->post('rows');
            //Pagination 1-10
            $page   = isset($page) ? intval($page) : 1;
            $rows   = isset($rows) ? intval($rows) : 10;
            $offset = ($page - 1) * $rows;
            $result = array();
            //Select Query
            $this->db->select('a.*, 
                i.users_id_from as status_check,
                i.users_id_to as status_notification, 
                c.name as division_name, 
                d.name as departement_name,
                e.name as departement_sub_name');
            $this->db->from('employee_requests a');
            $this->db->join('divisions c', 'a.division_id = c.id');
            $this->db->join('departements d', 'a.departement_id = d.id');
            $this->db->join('departement_subs e', 'a.departement_sub_id = e.id');
            $this->db->join('notifications i', "a.id = i.table_id and i.table_name = 'employee_requests'", 'left');
            $this->db->where('a.deleted', 0);
            $this->db->like('a.departement_id', $aprvDepartement);
            $this->db->like('a.request_by', $filter_request);
            if ($filter_from != "" || $filter_to != "") {
                $this->db->where("a.request_date between '$filter_from' and '$filter_to'");
            }
            if ($filter_approval == "0") {
                $this->db->where("(i.users_id_to = '' or i.users_id_to is null)");
            } elseif ($filter_approval == "1") {
                $this->db->where("(i.users_id_to != '' or i.users_id_to is not null)");
            }
            $this->db->order_by('a.due_date', 'ASC');
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
                $send   = $this->crud->create('employee_requests', $post);
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
            $send = $this->crud->update('employee_requests', ["id" => $id], $post);
            echo $send;
        } else {
            show_error("Cannot Process your request");
        }
    }

    //DELETE DATA
    public function delete()
    {
        $data = $this->input->post();
        $send = $this->crud->delete('employee_requests', $data);
        echo $send;
    }

    //PRINT & EXCEL DATA
    public function print($option = "")
    {
        if ($option == "excel") {
            $format  = date("Ymd");
            header("Content-type: application/vnd-ms-excel");
            header("Content-Disposition: attachment; filename=employee_requests_$format.xls");
        }

        $filter_from = $this->input->get('filter_from');
        $filter_to = $this->input->get('filter_to');
        $filter_request = $this->input->get('filter_request');
        $filter_approval = $this->input->get('filter_approval');
        $aprvDepartement = $this->checkApprovalAccess('employee_requests');

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
        $this->db->from('employee_requests a');
        $this->db->join('divisions c', 'a.division_id = c.id');
        $this->db->join('departements d', 'a.departement_id = d.id');
        $this->db->join('departement_subs e', 'a.departement_sub_id = e.id');
        $this->db->join('notifications i', "a.id = i.table_id and i.table_name = 'employee_requests'", 'left');
        $this->db->where('a.deleted', 0);
        $this->db->like('a.departement_id', $aprvDepartement);
        $this->db->like('a.request_by', $filter_request);
        if ($filter_from != "" || $filter_to != "") {
            $this->db->where("a.request_date between '$filter_from' and '$filter_to'");
        }
        if ($filter_approval == "0") {
            $this->db->where("(i.users_id_to != '' or i.users_id_to != null)");
        } elseif ($filter_approval == "1") {
            $this->db->where("(i.users_id_to = '' or i.users_id_to = null)");
        }
        $this->db->order_by('a.due_date', 'ASC');
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
                            <small>EMPLOYEE REQUEST</small>
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
                <th>Request By</th>
                <th>Request Date</th>
                <th>Due Date</th>
                <th>Division</th>
                <th>Departement</th>
                <th>Departement Sub</th>
                <th>Qty</th>
                <th>Note</th>
            </tr>';
        $no = 1;
        foreach ($records as $data) {
            $html .= '<tr>
                    <td>' . $no . '</td>
                    <td>' . $data['request_by'] . '</td>
                    <td>' . $data['request_date'] . '</td>
                    <td>' . $data['due_date'] . '</td>
                    <td>' . $data['division_name'] . '</td>
                    <td>' . $data['departement_name'] . '</td>
                    <td>' . $data['departement_sub_name'] . '</td>
                    <td>' . $data['qty'] . '</td>
                    <td>' . $data['description'] . '</td>';
            $no++;
        }

        $html .= '</table></body></html>';
        echo $html;
    }
}
