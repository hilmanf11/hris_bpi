<?php
date_default_timezone_set("Asia/Bangkok");
defined('BASEPATH') or exit('No direct script access allowed');

class Privilege_groups extends CI_Controller
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
        if (empty($this->session->username)) {
            redirect('error_session');
        } elseif ($this->checkuserAccess($this->id_menu()) > 0) {
            $this->load->view('template/header');
            $this->load->view('admin/privilege_groups');
        } else {
            redirect('error_access');
        }
    }

    //GET DATA
    public function reads()
    {
        $post = isset($_POST['q']) ? $_POST['q'] : "";
        $username = $this->session->username;
        $send = $this->crud->query("SELECT b.id, b.name FROM privilege_groups a JOIN groups b ON a.group_id = b.id WHERE a.username = '$username' and a.status = '1'");
        echo json_encode($send);
    }

    public function getUsers()
    {
        $post = isset($_POST['q']) ? array("name" => $_POST['q']) : $this->input->get();
        $send = $this->crud->reads('users', $post, ["deleted" => 0, "actived" => 0]);
        echo json_encode($send);
    }

    public function datatables()
    {
        if ($this->input->get('username')) {
            $username = $this->input->get('username');

            $this->db->select('a.*, b.name');
            $this->db->from('privilege_groups a');
            $this->db->join('groups b', 'a.group_id = b.id');
            $this->db->like('a.username', $username);
            $this->db->group_by('a.group_id');
            $this->db->order_by('b.name', 'ASC');
            $records = $this->db->get()->result_array();
            echo json_encode($records);
        }
    }

    public function create()
    {
        if (!empty($this->session->username)) {
            if ($this->input->post()) {
                $post = $this->input->post();
                $sql = $this->db->query("SELECT * from groups order by `name` asc");
                foreach ($sql->result_array() as $data) {

                    //Ambil data yang belum terdaftar
                    $id = $data['id'];
                    $sql_cek2 = $this->db->query("SELECT * FROM privilege_groups WHERE group_id = '$id' and username = '$post[username]'");
                    $row_cek2 = $sql_cek2->num_rows();

                    if ($row_cek2 > 0) {
                        //jika data sudah ada maka tidak ada proses
                    } else {
                        //Jika data belum ada maka terjadi insert data
                        $value  = array(
                            'username'       => $post['username'],
                            'group_id'       => $id
                        );

                        $this->crud->create('privilege_groups', $value);
                    }
                }
            } else {
                show_error("Cannot Process your request");
            }
        } else {
            show_error("Your Session has been Expired");
        }
    }

    public function update()
    {
        if ($this->input->post()) {
            $id     = base64_decode($this->input->get('id'));
            $post   = $this->input->post();
            $send   = $this->crud->update('privilege_groups', ["id" => $id], $post);
            echo $send;
        } else {
            show_error("Cannot Process your request");
        }
    }
}
