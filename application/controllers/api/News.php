<?php
header('Content-Type: application/json; charset=utf-8');

date_default_timezone_set("Asia/Bangkok");
defined('BASEPATH') or exit('No direct script access allowed');

class News extends CI_Controller
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
    public function index()
    {
        show_error("Cannot Process your request");
    }

    public function reads($api_key = "", $limit = "10")
    {

        if ($api_key != "") {
            $user = $this->crud->read("users", [], ["api_key" => $api_key]);

            if ($user) {
                $departement_id = $user->departement_id;
                $date = date("Y-m-d");

                $announcements = $this->crud->query("SELECT * FROM news WHERE departement_id LIKE '%$departement_id%' and finish_date >= '$date' ORDER BY start_date DESC LIMIT $limit");
                $datas = array();

                foreach ($announcements as $announcement) {
                    $userFrom = $this->crud->read("users", [], ["username" => $announcement->created_by]);

                    if($userFrom->avatar == null || $userFrom->avatar == ""){
                        $avatar = "assets/image/users/default.png";
                    }else{
                        $avatar = "assets/image/users/".$userFrom->avatar;
                    }

                    if($announcement->attachment == null || $announcement->attachment == ""){
                        $attachment = "";
                    }else{
                        $attachment = "assets/document/news/".$announcement->attachment;
                    }

                    $datas[] = array(
                        "id" => $announcement->id,
                        "avatar" => $avatar,
                        "created_by" => $userFrom->name,
                        "created_date" => $announcement->created_date,
                        "start_date" => $announcement->start_date,
                        "finish_date" => $announcement->finish_date,
                        "name" => $announcement->name,
                        "description" => $announcement->description,
                        "attachment" => $attachment,
                    );
                }

                die(json_encode(array(
                    "title" => "Success",
                    "message" => "Data Found",
                    "theme" => "success",
                    "results" => $datas
                )));
            } else {
                show_error("API Key Invalid");
            }
        } else {
            show_error("Cannot Process your request");
        }
    }

    public function readDetails($id = "")
    {
        if ($id != "") {
            $news = $this->crud->read("news", [], ["id" => $id]);

            if ($news) {
                if($news->attachment == null || $news->attachment == ""){
                    $attachment = "";
                }else{
                    $attachment = "assets/document/news/".$news->attachment;
                }

                $datas = array(
                    "name" => $news->name,
                    "description" => $news->description,
                    "start_date" => $news->start_date,
                    "finish_date" => $news->finish_date,
                    "attachment" => $attachment,
                );

                die(json_encode(array(
                    "title" => "Success",
                    "message" => "Data Found",
                    "theme" => "success",
                    "results" => $datas
                )));
            } else {
                show_error("Announcement Not Found");
            }
        } else {
            show_error("Cannot Process your request");
        }
    }
}
