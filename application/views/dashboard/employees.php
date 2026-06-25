<style>
    .customers {
        border-collapse: collapse;
        width: 100%;
    }

    .customers td, .customers th {
        border: 1px solid #ddd;
        padding: 5px;
    }

    .customers tr:nth-child(even){background-color: #f2f2f2;}

    .customers tr:hover {background-color: #ddd;}

    .customers th {
        padding-top: 5px;
        padding-bottom: 5px;
        text-align: left;
        background-color: #04AA6D;
        color: white;
    }
</style>
<body>
    <div class="row m-0">
        <div class="col-lg-12">
            <div class="row">
                <div class="col-lg-4 mt-3" id="chart_group">
                    <div class="card">
                        <div class="card-header">
                            Chart Departements
                        </div>
                        <div class="card-body p-0">
                            <div id="chartDepartement"></div>
                            <table id="tableDepartement" class="customers">
                                <thead>
                                    <tr>
                                        <th>Departement Name</th>
                                        <th>Total Employee</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <div class="col-lg-4 mt-3" id="chart_group">
                    <div class="card">
                        <div class="card-header">
                            Chart Groups
                        </div>
                        <div class="card-body p-0">
                            <div id="chartGroup"></div>
                            <table id="tableGroup" class="customers">
                                <thead>
                                    <tr>
                                        <th>Group Name</th>
                                        <th>Total Employee</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <div class="col-lg-4 mt-3" id="chart_service">
                    <div class="card">
                        <div class="card-header">
                            Chart Ages
                        </div>
                        <div class="card-body p-0">
                            <div id="chartService"></div>
                            <table id="tableService" class="customers">
                                <thead>
                                    <tr>
                                        <th>Age Rate</th>
                                        <th>Total Employee</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <div class="col-lg-3 mt-3" id="chart_gender">
                    <div class="card">
                        <div class="card-header">
                            Chart Genders
                        </div>
                        <div class="card-body p-0">
                            <div id="chartGender"></div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-3 mt-3" id="chart_marital">
                    <div class="card">
                        <div class="card-header">
                            Chart Marital Status
                        </div>
                        <div class="card-body p-0">
                            <div id="chartMarital"></div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-3 mt-3" id="chart_domicile">
                    <div class="card">
                        <div class="card-header">
                            Chart Domicile
                        </div>
                        <div class="card-body p-0">
                            <div id="chartDomicile"></div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-3 mt-3" id="chart_education">
                    <div class="card">
                        <div class="card-header">
                            Chart Education
                        </div>
                        <div class="card-body p-0">
                            <div id="chartEducation"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
<script>
    $(function() {
        $.ajax({
            type: "post",
            url: "<?= base_url('dashboard/employees/chartDepartement') ?>",
            dataType: "json",
            success: function (json) {
                $('#tableDepartement tbody').html(json.html);

                Highcharts.chart('chartDepartement', {
                    chart: {
                        type: 'column',
                        height: 250
                    },
                    title: {
                        text: '',
                    },
                    xAxis: {
                        categories: json.name,
                        crosshair: true,
                        accessibility: {
                            description: 'Departement'
                        }
                    },
                    yAxis: {
                        min: 0,
                        title: {
                            text: 'Total Employee'
                        }
                    },
                    tooltip: {
                        valueSuffix: ''
                    },
                    plotOptions: {
                        column: {
                            pointPadding: 0.2,
                            borderWidth: 0
                        }
                    },
                    series: [
                        {
                            name: 'Total Employee',
                            data: json.number
                        }
                    ]
                });     
            }
        });

        $.ajax({
            type: "post",
            url: "<?= base_url('dashboard/employees/chartGroups') ?>",
            dataType: "json",
            success: function (json) {
                $('#tableGroup tbody').html(json.html);

                Highcharts.chart('chartGroup', {
                    chart: {
                        type: 'column',
                        height: 250
                    },
                    title: {
                        text: '',
                    },
                    xAxis: {
                        categories: json.name,
                        crosshair: true,
                        accessibility: {
                            description: 'Groups'
                        }
                    },
                    yAxis: {
                        min: 0,
                        title: {
                            text: 'Total Employee'
                        }
                    },
                    tooltip: {
                        valueSuffix: ''
                    },
                    plotOptions: {
                        column: {
                            pointPadding: 0.2,
                            borderWidth: 0
                        }
                    },
                    series: [
                        {
                            name: 'Total Employee',
                            data: json.number
                        }
                    ]
                });     
            }
        });

        $.ajax({
            type: "post",
            url: "<?= base_url("dashboard/employees/chartService") ?>",
            dataType: "json",
            success: function (json) {
                $('#tableService tbody').html(json.html);

                Highcharts.chart('chartService', {
                    chart: {
                        type: 'column',
                        height: 250
                    },
                    title: {
                        text: '',
                    },
                    xAxis: {
                        categories: json.name,
                        crosshair: true,
                        accessibility: {
                            description: 'Groups'
                        }
                    },
                    yAxis: {
                        min: 0,
                        title: {
                            text: 'Total Age'
                        }
                    },
                    tooltip: {
                        valueSuffix: ''
                    },
                    plotOptions: {
                        column: {
                            pointPadding: 0.2,
                            borderWidth: 0
                        }
                    },
                    series: [
                        {
                            name: 'Total Employee',
                            data: json.number
                        }
                    ]
                });     
            }
        });

        $.ajax({
            type: "post",
            url: "<?= base_url("dashboard/employees/chartGender") ?>",
            dataType: "json",
            success: function (json) {
                Highcharts.chart('chartGender', {
                    chart: {
                        type: 'pie',
                        height: 240
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
                        data: json
                    }]
                });     
            }
        });

        $.ajax({
            type: "post",
            url: "<?= base_url("dashboard/employees/chartMarital") ?>",
            dataType: "json",
            success: function (json) {
                Highcharts.chart('chartMarital', {
                    chart: {
                        type: 'pie',
                        height: 240
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
                        data: json
                    }]
                });     
            }
        });

        $.ajax({
            type: "post",
            url: "<?= base_url("dashboard/employees/chartDomicile") ?>",
            dataType: "json",
            success: function (json) {
                Highcharts.chart('chartDomicile', {
                    chart: {
                        type: 'pie',
                        height: 240
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
                        data: json
                    }]
                });     
            }
        });

        $.ajax({
            type: "post",
            url: "<?= base_url("dashboard/employees/chartEducation") ?>",
            dataType: "json",
            success: function (json) {
                Highcharts.chart('chartEducation', {
                    chart: {
                        type: 'pie',
                        height: 240
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
                        data: json
                    }]
                });     
            }
        });
    });
</script>