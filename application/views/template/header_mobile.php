<?php
date_default_timezone_set("Asia/Bangkok");

//Config
$this->db->select('*');
$this->db->from('config');
$config = $this->db->get()->row();
?>

<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <meta http-equiv='cache-control' content='no-cache'>
    <meta http-equiv='expires' content='0'>
    <meta http-equiv='pragma' content='no-cache'>
    <meta name="viewport" content="width=device-width, user-scalable=no">
    <title><?= $config->name ?> | <?= $config->description ?></title>

    <link rel="icon" href="<?= $config->favicon ?>" type="image/png" sizes="16x16">
    <link href='https://fonts.googleapis.com/css?family=Poppins' rel='stylesheet'>

    <script type="text/javascript" src="<?= base_url('assets/jquery.min.js') ?>"></script>

    <!-- Fontawesome -->
    <link rel="stylesheet" type="text/css" href="<?= base_url('assets/icons/fontawesome/css/font-awesome.min.css') ?>">
    <script src="https://kit.fontawesome.com/c568386327.js" crossorigin="anonymous"></script>

    <!-- Vendors -->
    <link rel="stylesheet" type="text/css" href="<?= base_url('assets/vendors/toastr.min.css') ?>">
    <link rel="stylesheet" type="text/css" href="<?= base_url('assets/vendors/sweetalert2.min.css') ?>">
    <link rel="stylesheet" type="text/css" href="<?= base_url('assets/vendors/guidely.css') ?>">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-GLhlTQ8iRABdZLl6O3oVMWSktQOp6b7In1Zl3/Jr59b6EGGoI1aFkw7cmDA6j6gD" crossorigin="anonymous">

    <script type="text/javascript" src="<?= base_url('assets/vendors/toastr.min.js') ?>"></script>
    <script type="text/javascript" src="<?= base_url('assets/vendors/sweetalert2.all.min.js') ?>"></script>
    <script type="text/javascript" src="<?= base_url('assets/vendors/guidely.min.js') ?>"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js" integrity="sha384-w76AqPfDkMBDXo30jS1Sgez6pr3x5MlQ1ZAGC+nuZB+EYdgRZgiwxhTBTkF7CXvN" crossorigin="anonymous"></script>
    <script type="text/javascript" src="<?= base_url('assets/vendors/html5-qrcode.min.js?1') ?>"></script>

    <!-- Charts -->
    <script src="<?= base_url('assets/vendors/charts/code/highcharts.js') ?>"></script>

    <style type="text/css">
        .highcharts-credits {
            display: none;
        }

        .preloader {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: 9999;
            background-color: #fff;
        }

        .preloader .loading {
            position: absolute;
            left: 50%;
            top: 50%;
            transform: translate(-50%, -50%);
            font: 14px arial;
        }
    </style>

    <script>
        $(document).ready(function() {
            $(".preloader").fadeOut();
        })
    </script>
</head>

<body style="margin: 0;">
    <div class="preloader">
        <div class="loading">
            <img src="<?= $config->logo ?>" width="150">
        </div>
    </div>
</body>