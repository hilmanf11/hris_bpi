<?php
date_default_timezone_set("Asia/Bangkok");
defined('BASEPATH') or exit('No direct script access allowed');

class Test extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->helper('url');
        $this->load->helper(array('form', 'url'));
        $this->load->library('form_validation');
        $this->load->library('session');
        $this->load->library('email');
        $this->load->model('crud');
    }

    public function index(){
        $this->otp("hilmanfadillah11@gmail.com", "542344");
    }

    public function otp($email, $otp)
    {
        $config = array(
            'protocol' => 'smtp', // 'mail', 'sendmail', or 'smtp'
            'smtp_host' => 'aeconsys.com',
            'smtp_port' => 465,
            'smtp_user' => 'bpi@aeconsys.com',
            'smtp_pass' => 'plastic@123#',
            'smtp_crypto' => 'ssl', //can be 'ssl' or 'tls' for example
            'mailtype' => 'html', //plaintext 'text' mails or 'html'
            'smtp_timeout' => '4', //in seconds
            'charset' => 'iso-8859-1',
            'wordwrap' => TRUE
        );

        // $config = array(
        //     'protocol' => 'smtp', // 'mail', 'sendmail', or 'smtp'
        //     'smtp_host' => 'astech-ind.com',
        //     'smtp_port' => 465,
        //     'smtp_user' => 'info@astech-ind.com',
        //     'smtp_pass' => '76Ow7oM9s2pyIsFV',
        //     'smtp_crypto' => 'ssl', //can be 'ssl' or 'tls' for example
        //     'mailtype' => 'html', //plaintext 'text' mails or 'html'
        //     'smtp_timeout' => '4', //in seconds
        //     'charset' => 'iso-8859-1',
        //     'wordwrap' => TRUE
        // );

        $this->email->initialize($config);
        $this->email->from('bpi@aeconsys.com', 'Finstech. (Asahi Base Best Indonesia)');
        $this->email->to($email);
        $this->email->subject('Login OTP Code ' . $otp);
        $this->email->message('<!doctype html>
        <html>
        
        <head>
            <meta name="viewport" content="width=device-width, initial-scale=1.0" />
            <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
            <title>Simple Transactional Email</title>
            <style>
                body {
                    background: #EFEFEF;
                    font-family: Montserrat, Helvetica, Arial, serif;
                    font-size: 12px;
                }
            </style>
        </head>
        
        <body>
            <div style="background: #EFEFEF; width: 100%;">
                <div style="width: 100%; padding-top:100px; padding-bottom:100px;">
                    <center>
                        <div style="width: 60%; background: white; border-radius: 20px; padding:50px; text-align: left;">
                            <h3>Login to Finstech</h3>
                            <div style="border: 1px solid #eeeeee;
                                background-color: #f4f4f4;
                                font-family: monospace;
                                margin-bottom: 24px;
                                border-radius: 8px;
                                text-align: center;
                                font-size: 22px;
                                letter-spacing: 20px;
                                padding: 8px;">
                                <b>'.$otp.'</b>
                            </div>
                            <p>If you are having login problems, please contact nina@astech-ind.com</p>
                            <br><br><br>
                            <br><br><br><br><br><br>
            
                            <p>Regards,</p>
                            <p><b>Askara Techno Indonesia</b></p>
                        </div>
                    </center>
                </div>
            </div>
        </body>
        
        </html>');
        $this->email->send();
    }
}
