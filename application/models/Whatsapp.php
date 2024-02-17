<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Whatsapp extends CI_Model
{
    function __construct()
    {
        parent::__construct();
    }

    function index()
    {
        show_404();
    }

    function checking($phone)
    {
        $first = substr($phone, 0, 1);
        $second = substr($phone, 0, 2);

        if ($first == "8") {
            return "62" . $phone;
        } elseif ($second == "62") {
            return $phone;
        } else {
            return false;
        }
    }

    function send_message($phone = "", $message = "")
    {
        if ($phone != "") {
            $token = "3NiutgwC21rdKFWf1ejnHqnkkPuDr3TBWLdHib1YKL1UacJ27K";
            $phone = $phone; //untuk group pakai groupid contoh: 62812xxxxxx-xxxxx
            $message = "";

            $curl = curl_init();
            curl_setopt_array($curl, array(
                CURLOPT_URL => 'https://app.ruangwa.id/api/send_message',
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => '',
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 0,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => 'POST',
                CURLOPT_POSTFIELDS => 'token=' . $token . '&number=' . $phone . '&message=' . $message,
            ));
            $response = curl_exec($curl);
            curl_close($curl);
            echo $response;
        } else {
            echo json_encode(array("result" => "false", "message" => "Kirim pesan Gagal", "status" => "failed"));
        }
    }
}
