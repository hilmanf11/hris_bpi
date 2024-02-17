<?php
date_default_timezone_set("Asia/Bangkok");
defined('BASEPATH') or exit('No direct script access allowed');

class Notifications extends CI_Controller
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

    public function read($token = "")
    {
        if ($token != "") {
            $users_m = $this->crud->read("users_m", [], ["token" => $token]);
            if (!empty($users_m->number)) {
                $notifications = $this->crud->reads("notifications_m", [], ["number" => $users_m->number, "status" => "0"]);
                die(json_encode(array(
                    "code" => "200",
                    "results" => ["total" => count($notifications)],
                    "status" => "success",
                )));
            } else {
                show_error("Cannot Process your request");
            }
        } else {
            show_error("Cannot Process your request");
        }
    }

    public function firebase_send(){
        if($this->input->post()){
            $device_id = $this->input->post("device_id");
            $title = $this->input->post("title");  
            $messages = $this->input->post("messages");

            $url = "https://fcm.googleapis.com/fcm/send";

            $notification = [
                'title' => $title,
                'body' => $messages
            ];

            $extraNotificationData = ["message" => $messages];
 
            $fields = array(
                "registration_ids" => array($device_id),
                "notification" => $notification
            );

            $headers = array(
                "Authorization:key=AAAAC5GK2cw:APA91bE9l5P4KdBtJ0fq-DhM_Oc24LSRWDwzztIOufK0QcZPl0VTNQ06rsM7ViEZh3kH_NQ19Wj4yBCBYxN1tDiQxhwseyfwifm7BgakQhy5jSEVKzhdoAvhViNMcqE_suzTBmtUx-dt",
                "Content-Type: application/json"
            );

            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($fields));
            $result = curl_exec($ch);
            if($result === FALSE){
                die('CURL FAILED' . curl_error($ch));
            }else{
                die(json_encode(array("code" => 200, "message" => "Send Firebase Success", "theme" => "success", "results" => json_decode($result))));
            }
            curl_close($ch);
        }else{
            show_error("Cannot Process your request");
        }
    }
}
