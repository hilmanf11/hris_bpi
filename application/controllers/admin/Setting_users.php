<?php
date_default_timezone_set("Asia/Bangkok");
defined('BASEPATH') or exit('No direct script access allowed');

class Setting_users extends CI_Controller
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
            $this->load->view('admin/setting_users');
        } else {
            redirect('error_access');
        }
    }

    public function getUsers()
    {
        $post = isset($_POST['q']) ? array("name" => $_POST['q']) : $this->input->get();
        $send = $this->crud->reads('users', $post, ["deleted" => 0, "actived" => 0]);
        echo json_encode($send);
    }

    function hasChild($id)
    {
        $menus = $this->crud->reads("menus", [], ["menus_id" => $id]);
        return count($menus) > 0 ? true : false;
    }

    public function datatables()
    {
        if ($this->input->get('users_id')) {
            $id = $this->input->post('id');
            $nama = $this->input->get('users_id');
            $result = array();

            if ($id === "0") {
                $rs = $this->db->query("SELECT count(*) as total from menus where menus_id=''");
                $row = $rs->row();
                $result['total'] = $row->total;

                $query  = $this->db->query("SELECT a.m_view,a.m_add,a.m_edit,a.m_delete,a.m_upload,a.m_download,a.m_print,a.m_excel,b.v_view, b.v_add,b.v_edit,b.v_delete,b.v_upload,b.v_download,b.v_print,b.v_excel, c.name, c.id, b.id as id_user_set 
                                        FROM setting_menus a 
                                        JOIN setting_users b on a.menus_id = b.menus_id 
                                        JOIN menus c on a.menus_id = c.id 
                                        WHERE b.users_id = '$nama' and c.menus_id='' ORDER BY c.sort asc");
                $items = array();
                foreach ($query->result_array() as $data) {
                    $data['state'] = $this->haschild($data['id']) ? 'closed' : 'open';
                    array_push($items, $data);
                }

                $result["rows"] = $items;
            } else {
                $query  = $this->db->query("SELECT a.m_view,a.m_add,a.m_edit,a.m_delete,a.m_upload,a.m_download,a.m_print,a.m_excel,b.v_view, b.v_add,b.v_edit,b.v_delete,b.v_upload,b.v_download,b.v_print,b.v_excel, c.name, c.id, b.id as id_user_set
                                        FROM setting_menus a 
                                        JOIN setting_users b on a.menus_id = b.menus_id 
                                        JOIN menus c on a.menus_id = c.id 
                                        WHERE b.users_id = '$nama' and c.menus_id='$id' ORDER BY c.sort asc");
                $items = array();
                foreach ($query->result_array() as $data) {
                    $data['state'] = $this->haschild($data['id']) ? 'closed' : 'open';
                    array_push($result, $data);
                }
            }
            echo json_encode($result);
        }
    }

    public function create()
    {
        if (!empty($this->session->username)) {
            if ($this->input->post()) {
                $post = $this->input->post();
                $sql = $this->db->query("SELECT * from setting_menus order by menus_id asc");
                foreach ($sql->result_array() as $data) {

                    //Ambil data yang belum terdaftar di setting user
                    $menus_id = $data['menus_id'];
                    $sql_cek2 = $this->db->query("SELECT * FROM setting_users WHERE menus_id = '$menus_id' and users_id = '$post[users_id]'");
                    $row_cek2 = $sql_cek2->num_rows();

                    if ($row_cek2 > 0) {
                        //jika data sudah ada maka tidak ada proses
                    } else {
                        //Jika data belum ada maka terjadi insert data di setting user
                        $value  = array(
                            'users_id'       => $post['users_id'],
                            'menus_id'       => $menus_id
                        );

                        $send   = $this->crud->create('setting_users', $value);
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
            $send   = $this->crud->update('setting_users', ["id" => $id], $post);
            echo $send;
        } else {
            show_error("Cannot Process your request");
        }
    }
}
