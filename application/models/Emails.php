<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Emails extends CI_Model
{
    function __construct()
    {
        parent::__construct();
        $this->load->library('email');
    }

    function index()
    {
        show_404();
    }

    public function emailRegistration($email, $name = "USER", $username, $password)
    {
        $config = array(
            'protocol' => 'smtp', // 'mail', 'sendmail', or 'smtp'
            'smtp_host' => 'aeconsys.com',
            'smtp_port' => 465,
            'smtp_user' => 'kinenta@aeconsys.com',
            'smtp_pass' => 'Hilman1196',
            'smtp_crypto' => 'ssl', //can be 'ssl' or 'tls' for example
            'mailtype' => 'html', //plaintext 'text' mails or 'html'
            'smtp_timeout' => '4', //in seconds
            'charset' => 'iso-8859-1',
            'wordwrap' => TRUE
        );

        $this->email->initialize($config);
        $this->email->from('kinenta@aeconsys.com', 'AECONSYS [Registration]');
        $this->email->to($email);
        $this->email->subject('Registration New User');
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
                        <h1>REGISTRATION</h1>
            
                        <div style="width: 60%; background: white; border-radius: 20px; padding:50px; text-align: left;">
                            <h3>Hi.<br><span style="color:#FF6060"> ' . $name . ' </span></h3>
                            <h3>Your email for signing up!</h3>
                            <p>You have been registered as a new user in the aplication, please use the following account to enter the application</p>
                            <table>
                                <tr>
                                    <td width="50">Username</td>
                                    <td width="10">:</td>
                                    <td width="100"><b>' . $username . '</b></td>
                                </tr>
                                <tr>
                                    <td width="50">Password</td>
                                    <td width="10">:</td>
                                    <td width="100"><b>' . $password . '</b></td>
                                </tr>
                            </table>
                            <p>If you are having username and password problems, please contact your IT Dept</p>
                            <br><br><br>
                            <br><br><br><br><br><br>
            
                            <p>Regards,</p>
                            <p><b>AECONSYS</b></p>
                        </div>
                    </center>
                </div>
            </div>
        </body>
        
        </html>');
        $this->email->send();
    }

    public function emailForgot($email)
    {
        $config = array(
            'protocol' => 'smtp', // 'mail', 'sendmail', or 'smtp'
            'smtp_host' => 'aeconsys.com',
            'smtp_port' => 465,
            'smtp_user' => 'kinenta@aeconsys.com',
            'smtp_pass' => 'Hilman1196',
            'smtp_crypto' => 'ssl', //can be 'ssl' or 'tls' for example
            'mailtype' => 'html', //plaintext 'text' mails or 'html'
            'smtp_timeout' => '4', //in seconds
            'charset' => 'iso-8859-1',
            'wordwrap' => TRUE
        );

        $this->email->initialize($config);
        $this->email->from('kinenta@aeconsys.com', 'AECONSYS [Reset Password]');
        $this->email->to($email);
        $this->email->subject('Reset Password');
        $this->email->message('<!doctype html>
        <html>
        
        <head>
            <meta name="viewport" content="width=device-width, initial-scale=1.0" />
            <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
            <title>RESET PASSWORD</title>
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
                        <h1>RESET PASSWORD!!</h1>
            
                        <div style="width: 60%; background: white; border-radius: 20px; padding:50px; text-align: left;">
                            <h3>Hi.<br><span style="color:#FF6060"> ' . $email . ' </span></h3>
                            <h3>Your email is registered in our system !</h3>
                            <p>If you want to reset password please click below</p>
                            <br><br><br><br><br>
                            <center><a target="_blank" style="padding: 10px; background-color: #65B451; color: white; border-radius: 1rem; margin: 10px; text-decoration: none;" href="' . base_url('login/reset/' . base64_encode($email)) . '">RESET PASSWORD</a></center>
                        </div>
                    </center>
                </div>
            </div>
        </body>
        
        </html>');
        $this->email->send();
    }

    public function emailForgotMobile($email, $password)
    {
        $config = array(
            'protocol' => 'smtp', // 'mail', 'sendmail', or 'smtp'
            'smtp_host' => 'aeconsys.com',
            'smtp_port' => 465,
            'smtp_user' => 'kinenta@aeconsys.com',
            'smtp_pass' => 'Hilman1196',
            'smtp_crypto' => 'ssl', //can be 'ssl' or 'tls' for example
            'mailtype' => 'html', //plaintext 'text' mails or 'html'
            'smtp_timeout' => '4', //in seconds
            'charset' => 'iso-8859-1',
            'wordwrap' => TRUE
        );

        $this->email->initialize($config);
        $this->email->from('kinenta@aeconsys.com', 'AECONSYS [Reset Password]');
        $this->email->to($email);
        $this->email->subject('Reset Password');
        $this->email->message('<!doctype html>
        <html>
        
        <head>
            <meta name="viewport" content="width=device-width, initial-scale=1.0" />
            <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
            <title>RESET PASSWORD</title>
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
                        <h1>RESET PASSWORD!!</h1>
            
                        <div style="width: 60%; background: white; border-radius: 20px; padding:50px; text-align: left;">
                            <h3>Hi.<br><span style="color:#FF6060"> ' . $email . ' </span></h3>
                            <h3>Your email is registered in our system !</h3>
                            <p>Your Password now <b>' . $password . '</b></p>
                            <br><br>
                        </div>
                    </center>
                </div>
            </div>
        </body>
        
        </html>');
        $this->email->send();
    }

    public function emailSalarySlip($email, $employee, $company, $html)
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

        $this->email->initialize($config);
        $this->email->from('bpi@aeconsys.com', $company . ' [Salary Slip]');
        $this->email->to($email);
        $this->email->subject('Salary Slip ' . $employee);
        $this->email->message(base64_decode($html));
        $email = $this->email->send();

        return $email;
    }

    public function emailThrSlip($email, $employee, $company, $html)
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

        $this->email->initialize($config);
        $this->email->from('bpi@aeconsys.com', $company . ' [THR Slip]');
        $this->email->to($email);
        $this->email->subject('THR Slip ' . $employee);
        $this->email->message(base64_decode($html));
        $email = $this->email->send();

        return $email;
    }
}
