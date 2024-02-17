<body>
    <div class="greeting" style="height: 100px; background-position: center; background-repeat: no-repeat; background-size: cover; background-image: url(<?= $background ?>) !important;">
        <div style="float: left;">
            <p style="font-size: 25px !important; margin:0;"><?= $day ?>, <b style="font-size: 25px !important;"><?= $session_name ?></b>!</p>
            <span>It's <?= date("D, d F Y") ?>, do you want to know the features in this dashboard? <a href="#" onclick="guidlyBegin()">Click here</a></span>
        </div>
        <div style="float: right; padding-right: 200px;">
            <b style="font-size: 40px !important; font-family: Orbitron;" class="jam">23:00:54</b>
        </div>
    </div>
    <div class="row m-0">
        <div class="col-lg-9">
            <div class="row">
                <div class="col-lg-3" id="check_in">
                    <div class="alert-success" style="height: 70px;">
                        <div style="float: left; width: 60px;">
                            <i class="fa fa-calendar-check-o" style="font-size: 50px !important;"></i>
                        </div>
                        <div style="float: left;">
                            <small>Working</small>
                            <h1 style="margin: 0; font-size:25px !important;"><?= $attandance['total_working'] ?></h1>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3" id="absent">
                    <div class="alert-danger" style="height: 70px;">
                        <div style="float: left; width: 60px;">
                            <i class="fa fa-calendar-times-o" style="font-size: 50px !important;"></i>
                        </div>
                        <div style="float: left;">
                            <small>Absence</small>
                            <h1 style="margin: 0; font-size:25px !important;"><?= $attandance['total_absence'] ?></h1>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3" id="permit">

                    <div class="alert-info" style="height: 70px;">
                        <div style="float: left; width: 60px;">
                            <i class="fa fa-calendar-minus-o" style="font-size: 50px !important;"></i>
                        </div>
                        <div style="float: left;">
                            <small>Permit</small>
                            <h1 style="margin: 0; font-size:25px !important;"><?= $attandance['total_permit'] ?></h1>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3" id="late">

                    <div class="alert-warning" style="height: 70px;">
                        <div style="float: left; width: 60px;">
                            <i class="fa fa-calendar-o" style="font-size: 50px !important;"></i>
                        </div>
                        <div style="float: left;">
                            <small>Late</small>
                            <h1 style="margin: 0; font-size:25px !important;"><?= $attandance['total_late'] ?></h1>
                        </div>
                    </div>
                </div>
            </div>

            <div id="calendar" style="width: 100%;">

            </div>
        </div>
        <div class="col-lg-3">
            <div class="row">
                <div class="col-lg-12 mt-3" id="permission">
                    <div class="card">
                        <div class="card-header">
                            Permission Today
                        </div>
                        <div class="card-body" style="height: 250px; overflow: auto;">
                            <?= $permittoday ?>
                        </div>
                    </div>
                </div>
                <div class="col-lg-12 mt-3" id="contract">
                    <div class="card">
                        <div class="card-header">
                            Contract & Probation
                        </div>
                        <div class="card-body" style="height: 250px; overflow: auto;">
                            <?= $contracts ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-12">
            <div class="row">
                <div class="col-lg-3 mt-3" id="announcement">
                    <div class="card">
                        <div class="card-header">
                            Announcement
                        </div>
                        <div class="card-body" style="height: 250px; overflow: auto;">
                            <div class="alert alert-danger" role="alert">
                                No Announcement
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 mt-3" id="tasklist">
                    <div class="card">
                        <div class="card-header">
                            Task List
                        </div>
                        <div class="card-body" style="height: 250px; overflow: auto;">
                            <div class="alert alert-danger" role="alert">
                                No Information Task
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-3 mt-3" id="chart_service">
                    <div class="card">
                        <div class="card-header">
                            Chart Service
                        </div>
                        <div class="card-body p-0" style="height: 250px;">
                            <div id="chartServices"></div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-3 mt-3" id="chart_employee">
                    <div class="card">
                        <div class="card-header">
                            Chart Employee Status
                        </div>
                        <div class="card-body p-0" style="height: 250px;">
                            <div id="chartEmployee"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
<script>
    function guidlyBegin() {
        guidely.add({
            attachTo: '#check_in',
            anchor: 'top-left',
            title: 'Check In',
            text: 'This is the number of information you have Check In'
        });

        guidely.add({
            attachTo: '#absent',
            anchor: 'top-left',
            title: 'Absent',
            text: 'This is the number of information you have Absent from work'
        });

        guidely.add({
            attachTo: '#permit',
            anchor: 'top-left',
            title: 'Permit',
            text: 'This is the number of information you have Absent from work due to permission'
        });

        guidely.add({
            attachTo: '#late',
            anchor: 'top-left',
            title: 'Late',
            text: 'This is the number of information you have Late to Work'
        });

        guidely.add({
            attachTo: '#calendar',
            anchor: 'top-left',
            title: 'Calendar',
            text: 'This is the Information about your check in and check out'
        });

        guidely.add({
            attachTo: '#permission',
            anchor: 'top-left',
            title: 'Permission',
            text: 'This is the list of Employee in Permission today'
        });

        guidely.add({
            attachTo: '#contract',
            anchor: 'top-left',
            title: 'Contract & Probation',
            text: 'This is the list of Employee in Contract & Probation today'
        });

        guidely.add({
            attachTo: '#announcement',
            anchor: 'top-left',
            title: 'Announcement',
            text: 'This is the list of Announcement in created with Employees'
        });

        guidely.add({
            attachTo: '#tasklist',
            anchor: 'top-left',
            title: 'Task List',
            text: 'This is the list of Tasks in created with your Departement Head'
        });


        guidely.init({
            welcome: true,
            startTrigger: false
        });
    }

    $(function() {
        Highcharts.chart('chartServices', {
            chart: {
                plotBackgroundColor: null,
                plotBorderWidth: null,
                plotShadow: true,
                type: 'pie',
                height: 250
            },
            title: {
                text: ''
            },
            tooltip: {
                pointFormat: '{series.name}: <b>{point.y:.1f}</b>'
            },
            accessibility: {
                point: {
                    valueSuffix: ''
                }
            },
            plotOptions: {
                pie: {
                    allowPointSelect: true,
                    cursor: 'pointer',
                    dataLabels: {
                        enabled: true,
                        format: '<b>{point.name}</b>'
                    }
                }
            },
            series: [{
                name: 'Total',
                colorByPoint: true,
                data: [{
                    name: '< 1 Year',
                    y: <?= $chartServices[0]['y'] ?>,
                }, {
                    name: '1 - 2 Year',
                    y: <?= $chartServices[1]['y'] ?>
                }, {
                    name: '2 - 5 Year',
                    y: <?= $chartServices[2]['y'] ?>
                }, {
                    name: '5 - 8 Year',
                    y: <?= $chartServices[3]['y'] ?>
                }, {
                    name: '8 - 10 Year',
                    y: <?= $chartServices[4]['y'] ?>
                }, {
                    name: '> 10 Year',
                    y: <?= $chartServices[5]['y'] ?>
                }]
            }]
        });

        Highcharts.chart('chartEmployee', {
            chart: {
                plotBackgroundColor: null,
                plotBorderWidth: null,
                plotShadow: true,
                type: 'pie',
                height: 250
            },
            title: {
                text: ''
            },
            tooltip: {
                pointFormat: '{series.name}: <b>{point.y:.1f}</b>'
            },
            accessibility: {
                point: {
                    valueSuffix: ''
                }
            },
            plotOptions: {
                pie: {
                    allowPointSelect: true,
                    cursor: 'pointer',
                    dataLabels: {
                        enabled: true,
                        format: '<b>{point.name}</b>'
                    }
                }
            },
            series: [{
                name: 'Total',
                colorByPoint: true,
                data: [{
                    name: '<?= $chartEmployee[0]->name ?>',
                    y: <?= $chartEmployee[0]->employee ?>,
                }, {
                    name: '<?= $chartEmployee[1]->name ?>',
                    y: <?= $chartEmployee[1]->employee ?>,
                }, {
                    name: '<?= $chartEmployee[2]->name ?>',
                    y: <?= $chartEmployee[2]->employee ?>,
                }, {
                    name: '<?= $chartEmployee[3]->name ?>',
                    y: <?= $chartEmployee[3]->employee ?>,
                }, {
                    name: '<?= $chartEmployee[4]->name ?>',
                    y: <?= $chartEmployee[4]->employee ?>,
                }, {
                    name: '<?= $chartEmployee[5]->name ?>',
                    y: <?= $chartEmployee[5]->employee ?>
                }]
            }]
        });

        calendars();
        // setTimeout(function() {
        //     window.location.href = window.location;
        // }, 30000);

        setInterval(jam, 1000);
    });

    function calendars() {
        $.ajax({
            type: "post",
            url: "<?= base_url('dashboard/dashboard/mycalendar') ?>",
            dataType: "html",
            success: function(response) {
                $("#calendar").html(response);
            }
        });
    }

    function jam() {
        var time = new Date(),
            hours = time.getHours(),
            minutes = time.getMinutes(),
            seconds = time.getSeconds();
        document.querySelectorAll('.jam')[0].innerHTML = harold(hours) + ":" + harold(minutes) + ":" + harold(seconds);

        function harold(standIn) {
            if (standIn < 10) {
                standIn = '0' + standIn
            }
            return standIn;
        }
    }
</script>