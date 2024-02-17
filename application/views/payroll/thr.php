<!-- TABLE DATAGRID -->
<table id="dg" class="easyui-datagrid" style="width:100%;" toolbar="#toolbar">
    <thead frozen="true">
        <tr>
            <th data-options="field:'employee_number',width:150,halign:'center'">Employee ID</th>
            <th data-options="field:'employee_name',width:250,halign:'center'">Employee Name</th>
        </tr>
    </thead>
    <thead>
        <tr>
            <th data-options="field:'departement_name',width:200,halign:'center'">Departement</th>
            <th data-options="field:'departement_sub_name',width:200,halign:'center'">Departement Sub</th>
            <th data-options="field:'date_sign',width:100,halign:'center'">Join Date</th>
            <th data-options="field:'services',width:100,halign:'center'">Fit for Service</th>
            <th data-options="field:'position_name',width:100,halign:'center'">Position</th>
            <th data-options="field:'marital',width:100,halign:'center'">Marital</th>
            <th data-options="field:'tax_id',width:100,halign:'center'">Tax Id</th>
            <th data-options="field:'salary',width:100,align:'center', formatter:numberformat">Salary</th>
            <th data-options="field:'allowence',width:100,align:'center', formatter:numberformat">Allowence</th>
            <th data-options="field:'total',width:100,align:'center', formatter:numberformat">Total</th>
            <th data-options="field:'thr',width:100,align:'center', formatter:numberformat">THR</th>
            <th data-options="field:'pph',width:100,align:'center', formatter:numberformat">PPH21</th>
            <th data-options="field:'total_thr',width:100,align:'center', formatter:numberformat">Total THR</th>
        </tr>
    </thead>
</table>

<!-- TOOLBAR DATAGRID -->
<div id="toolbar">
    <div class="easyui-accordion" style="width:100%; margin-bottom:5px;">
        <div title="Click this to hide the filter" data-options="onCollapse: function(){ $('#dg').datagrid('reload'); }" style="padding:10px; background:#F4F4F4;">
            <fieldset style="width: 99%; border:2px solid #d0d0d0; border-radius:4px;">
                <legend><b>Form Filter Data</b></legend>
                <div style="width: 50%; float: left;">
                    <div class="fitem">
                        <span style="width:30%; display:inline-block;">Period</span>
                        <input style="width:28%;" name="filter_year" id="filter_year" value="<?= date("Y") ?>" panelHeight="auto" class="easyui-combobox">
                    </div>
                    <div class="fitem">
                        <span style="width:30%; display:inline-block;">Division</span>
                        <input style="width:60%;" id="filter_division" class="easyui-combobox">
                    </div>
                    <div class="fitem">
                        <span style="width:30%; display:inline-block;">Departement</span>
                        <input style="width:60%;" id="filter_departement" class="easyui-combobox">
                    </div>
                    <div class="fitem">
                        <span style="width:30%; display:inline-block;">Departement Sub</span>
                        <input style="width:60%;" id="filter_departement_sub" class="easyui-combobox">
                    </div>
                    <div class="fitem">
                        <span style="width:30%; display:inline-block;"></span>
                        <a href="javascript:;" class="easyui-linkbutton" onclick="filter()"><i class="fa fa-search"></i> Filter Data</a>
                    </div>
                </div>
                <div style="width: 50%; float: left;">
                    <div class="fitem">
                        <span style="width:30%; display:inline-block;">Employee</span>
                        <input style="width:60%;" id="filter_employee" class="easyui-combogrid">
                    </div>
                    <div class="fitem">
                        <span style="width:30%; display:inline-block;">Employee Type</span>
                        <input style="width:60%;" id="filter_employee_type" name="filter_employee_type" class="easyui-combobox">
                    </div>
                    <div class="fitem">
                        <span style="width:30%; display:inline-block;">Group</span>
                        <input style="width:60%;" id="filter_group" name="filter_group" class="easyui-combobox">
                    </div>
                </div>
            </fieldset>
        </div>
    </div>
    <?= $button ?>
</div>

<div id="dlg_generate" class="easyui-dialog" title="Generating Data" data-options="closed: true,modal:true,closable: false" style="width: 500px; padding:10px; top: 20px;">
    <div class="alert alert-warning" role="alert">
        Please wait until the generate process is complete
    </div>
    <div id="p_upload" class="easyui-progressbar" style="width:460px; margin-top: 10px;"></div>
    <center><b id="p_start">0</b> Of <b id="p_finish">0</b></center>
    <div id="p_remarks" class="easyui-panel" style="width:460px; height:200px; padding:10px; margin-top: 10px;">
        <p>History Generate</p>
        <ul id="remarks">

        </ul>
    </div>
</div>

<!-- PDF -->
<iframe id="printout" src="<?= base_url('payroll/thr/print') ?>" style="width: 100%;" hidden></iframe>

<script>
    function add() {
        var filter_year = $("#filter_year").combobox('getValue');
        var filter_division = $("#filter_division").combobox('getValue');
        var filter_departement = $("#filter_departement").combobox('getValue');
        var filter_departement_sub = $("#filter_departement_sub").combobox('getValue');
        var filter_employee = $("#filter_employee").combogrid('getValue');
        var filter_employee_type = $("#filter_employee_type").combobox('getValue');
        var filter_group = $("#filter_group").combobox('getValue');

        if (filter_year == "") {
            toastr.warning("Please Enter Period", "Filter Date");
        } else {
            $.ajax({
                url: "<?= base_url('payroll/thr/read') ?>",
                type: 'get',
                data: 'filter_year=' + filter_year,
                success: function(message) {
                    var message_read = eval('(' + message + ')');

                    if (message_read['status'] != "1") {
                        if (message_read['generate'] == "EXIST") {
                            $.messager.confirm('Warning', 'Are you sure you want to repeat generate data?', function(r) {
                                if (r) {
                                    $.ajax({
                                        method: 'post',
                                        url: '<?= base_url('payroll/thr/delete') ?>',
                                        data: {
                                            filter_year: filter_year,
                                            filter_division: filter_division,
                                            filter_departement: filter_departement,
                                            filter_departement_sub: filter_departement_sub,
                                            filter_employee: filter_employee,
                                            filter_employee_type: filter_employee_type,
                                            filter_group: filter_group
                                        },
                                        success: function(deleted) {
                                            Swal.fire({
                                                title: 'Please Wait for Generating Data',
                                                showConfirmButton: false,
                                                allowOutsideClick: false,
                                                allowEscapeKey: false,
                                                didOpen: () => {
                                                    Swal.showLoading();
                                                },
                                            });

                                            var url = "filter_division=" + filter_division +
                                                "&filter_departement=" + filter_departement +
                                                "&filter_departement_sub=" + filter_departement_sub +
                                                '&filter_year=' + filter_year +
                                                '&filter_employee_type=' + filter_employee_type +
                                                '&filter_group=' + filter_group +
                                                '&filter_employee=' + filter_employee;
                                            $.ajax({
                                                url: "<?= base_url('payroll/thr/generatePayroll') ?>",
                                                type: 'get',
                                                data: url,
                                                success: function(result) {
                                                    $('#dlg_generate').dialog('open');
                                                    Swal.close();
                                                    var json = eval('(' + result + ')');
                                                    requestData(json.total, json);

                                                    function requestData(total, json, number = 1, value = 0) {
                                                        if (value < 100) {
                                                            value = Math.floor((number / total) * 100);
                                                            $('#p_upload').progressbar('setValue', value);
                                                            $('#p_start').html(number);
                                                            $('#p_finish').html(total);

                                                            $.ajax({
                                                                url: "<?= base_url('payroll/thr/create') ?>?" + url,
                                                                type: 'post',
                                                                data: json['rows'][number - 1],
                                                                success: function(note) {
                                                                    var result = eval('(' + note + ')');
                                                                    if (result.theme == "success") {
                                                                        var title = "<b style='color: green;'>" + result.title + "</b> | " + result.message;
                                                                    } else {
                                                                        var title = "<b style='color: red;'>" + result.title + "</b> | " + result.message;
                                                                    }
                                                                    $("#p_remarks").append(title + "<br>");
                                                                    requestData(total, json, number + 1, value);

                                                                    if (number == total) {
                                                                        $('#dlg_generate').dialog('close');
                                                                        Swal.fire(
                                                                            'Generate Completed',
                                                                            'Generate payroll has been completed, please check the generated payroll data, if an error occurs, please generate it again before the data is approved',
                                                                            'success'
                                                                        );
                                                                    }
                                                                },
                                                                fail: function(jqXHR, textStatus) {
                                                                    if (textStatus == "error") {
                                                                        Swal.fire({
                                                                            title: 'Connection Time Out, Check Your Connection',
                                                                            showConfirmButton: false,
                                                                            allowOutsideClick: false,
                                                                            allowEscapeKey: false,
                                                                            didOpen: () => {
                                                                                Swal.showLoading();
                                                                            },
                                                                        });

                                                                        setTimeout(function() {
                                                                            requestData(total, json, number, value);
                                                                        }, 5000);
                                                                    }
                                                                }
                                                            });
                                                        }
                                                    }
                                                }
                                            });
                                        },
                                    });
                                }
                            });
                        } else {
                            Swal.fire({
                                title: 'Please Wait for Generating Data',
                                showConfirmButton: false,
                                allowOutsideClick: false,
                                allowEscapeKey: false,
                                didOpen: () => {
                                    Swal.showLoading();
                                },
                            });

                            var url = "filter_division=" + filter_division +
                                "&filter_departement=" + filter_departement +
                                "&filter_departement_sub=" + filter_departement_sub +
                                '&filter_year=' + filter_year +
                                '&filter_employee_type=' + filter_employee_type +
                                '&filter_group=' + filter_group +
                                '&filter_employee=' + filter_employee;
                            $.ajax({
                                url: "<?= base_url('payroll/thr/generatePayroll') ?>",
                                type: 'get',
                                data: url,
                                success: function(result) {
                                    $('#dlg_generate').dialog('open');
                                    Swal.close();
                                    var json = eval('(' + result + ')');
                                    requestData(json.total, json);

                                    function requestData(total, json, number = 1, value = 0) {
                                        if (value < 100) {
                                            value = Math.floor((number / total) * 100);
                                            $('#p_upload').progressbar('setValue', value);
                                            $('#p_start').html(number);
                                            $('#p_finish').html(total);

                                            $.ajax({
                                                url: "<?= base_url('payroll/thr/create') ?>?" + url,
                                                type: 'post',
                                                data: json['rows'][number - 1],
                                                success: function(note) {
                                                    var result = eval('(' + note + ')');
                                                    if (result.theme == "success") {
                                                        var title = "<b style='color: green;'>" + result.title + "</b> | " + result.message;
                                                    } else {
                                                        var title = "<b style='color: red;'>" + result.title + "</b> | " + result.message;
                                                    }
                                                    $("#p_remarks").append(title + "<br>");
                                                    requestData(total, json, number + 1, value);

                                                    if (number == total) {
                                                        $('#dlg_generate').dialog('close');
                                                        Swal.fire(
                                                            'Generate Completed',
                                                            'Generate payroll has been completed, please check the generated payroll data, if an error occurs, please generate it again before the data is approved',
                                                            'success'
                                                        );
                                                    }
                                                },
                                                fail: function(jqXHR, textStatus) {
                                                    if (textStatus == "error") {
                                                        Swal.fire({
                                                            title: 'Connection Time Out, Check Your Connection',
                                                            showConfirmButton: false,
                                                            allowOutsideClick: false,
                                                            allowEscapeKey: false,
                                                            didOpen: () => {
                                                                Swal.showLoading();
                                                            },
                                                        });

                                                        setTimeout(function() {
                                                            requestData(total, json, number, value);
                                                        }, 5000);
                                                    }
                                                }
                                            });
                                        }
                                    }
                                }
                            });
                        }
                    } else {
                        Swal.fire(
                            'Cannot Generate!',
                            'the payroll data that you entered has been approved, please contact the relevant admin if you want to repeat generate payroll again',
                            'warning'
                        );
                    }
                }
            });
        }
    }

    function filter() {
        var filter_year = $("#filter_year").combobox('getValue');
        var filter_division = $("#filter_division").combobox('getValue');
        var filter_departement = $("#filter_departement").combobox('getValue');
        var filter_departement_sub = $("#filter_departement_sub").combobox('getValue');
        var filter_employee = $("#filter_employee").combogrid('getValue');
        var filter_employee_type = $("#filter_employee_type").combobox('getValue');
        var filter_group = $("#filter_group").combobox('getValue');

        var url = "?filter_year=" + filter_year +
            "&filter_division=" + filter_division +
            "&filter_departement=" + filter_departement +
            "&filter_departement_sub=" + filter_departement_sub +
            "&filter_employee=" + filter_employee +
            "&filter_employee_type=" + filter_employee_type +
            "&filter_group=" + filter_group;

        if (filter_year == "") {
            toastr.warning("Please Enter Cut Off Period");
        } else {

            $('#dg').datagrid({
                url: '<?= base_url('payroll/thr/datatables') ?>' + url
            });

            $("#printout").contents().find('html').html("<center><br><br><br><b style='font-size:20px;'>Please Wait...</b></center>");
            $("#printout").attr('src', '<?= base_url('payroll/thr/print') ?>' + url);
        }
    }

    //PRINT PDF
    function pdf() {
        $("#printout").get(0).contentWindow.print();
    }
    //PRINT EXCEL
    function excel() {
        var filter_year = $("#filter_year").combobox('getValue');
        var filter_division = $("#filter_division").combobox('getValue');
        var filter_departement = $("#filter_departement").combobox('getValue');
        var filter_departement_sub = $("#filter_departement_sub").combobox('getValue');
        var filter_employee = $("#filter_employee").combogrid('getValue');
        var filter_employee_type = $("#filter_employee_type").combobox('getValue');
        var filter_group = $("#filter_group").combobox('getValue');

        var url = "?filter_year=" + filter_year +
            "&filter_division=" + filter_division +
            "&filter_departement=" + filter_departement +
            "&filter_departement_sub=" + filter_departement_sub +
            "&filter_employee=" + filter_employee +
            "&filter_employee_type=" + filter_employee_type +
            "&filter_group=" + filter_group;


        if (filter_year == "") {
            toastr.warning("Please Enter Cut Off Period");
        } else {
            window.location.assign('<?= base_url('payroll/thr/print/excel') ?>' + url);
        }
    }
    //RELOAD
    function reload() {
        window.location.reload();
    }

    $(function() {
        $("#add").html("Generate Payroll");
        $('#dg').datagrid({
            url: '<?= base_url('payroll/thr/datatables') ?>',
            pagination: true,
            rownumbers: true,
            fit: true,
            pageList: [20, 50, 100, 500, 1000],
            pageSize: 20,
        });

        //Filter Cutoff
        $('#filter_year').combobox({
            url: '<?php echo base_url('payroll/thr/readYears'); ?>',
            valueField: 'year',
            textField: 'year',
            prompt: 'Choose Period',
        });

        //Get Departement
        $('#filter_division').combobox({
            url: '<?php echo base_url('employee/divisions/reads'); ?>',
            valueField: 'id',
            textField: 'name',
            prompt: 'Choose All',
            icons: [{
                iconCls: 'icon-clear',
                handler: function(e) {
                    $(e.data.target).combobox('clear').combobox('textbox').focus();
                }
            }],
            onSelect: function(division) {
                $('#filter_departement').combobox({
                    url: '<?php echo base_url('employee/departements/reads'); ?>?division_id=' + division.id,
                    valueField: 'id',
                    textField: 'name',
                    prompt: 'Choose All',
                    icons: [{
                        iconCls: 'icon-clear',
                        handler: function(e) {
                            $(e.data.target).combobox('clear').combobox('textbox').focus();
                        }
                    }],
                    onSelect: function(departement) {
                        $('#filter_departement_sub').combobox({
                            url: '<?php echo base_url('employee/departement_subs/reads'); ?>?division_id=' + division.id + '&departement_id=' + departement.id,
                            valueField: 'id',
                            textField: 'name',
                            prompt: 'Choose All',
                            icons: [{
                                iconCls: 'icon-clear',
                                handler: function(e) {
                                    $(e.data.target).combobox('clear').combobox('textbox').focus();
                                }
                            }],
                            onSelect: function(departement_sub) {
                                $('#filter_employee').combogrid({
                                    url: '<?= base_url('employee/employees/reads?departement_sub_id=') ?>' + departement_sub.id,
                                    panelWidth: 450,
                                    idField: 'id',
                                    textField: 'name',
                                    mode: 'remote',
                                    fitColumns: true,
                                    prompt: 'Choose All',
                                    icons: [{
                                        iconCls: 'icon-clear',
                                        handler: function(e) {
                                            $(e.data.target).combogrid('clear').combogrid('textbox').focus();
                                        }
                                    }],
                                    columns: [
                                        [{
                                            field: 'number',
                                            title: 'Employee ID',
                                            width: 120
                                        }, {
                                            field: 'name',
                                            title: 'Employee Name',
                                            width: 200
                                        }]
                                    ],
                                });
                            }
                        });
                    }
                });
            }
        });

        $('#filter_employee').combogrid({
            url: '<?= base_url('employee/employees/reads') ?>',
            panelWidth: 450,
            idField: 'id',
            textField: 'name',
            mode: 'remote',
            fitColumns: true,
            prompt: 'Choose All',
            icons: [{
                iconCls: 'icon-clear',
                handler: function(e) {
                    $(e.data.target).combogrid('clear').combogrid('textbox').focus();
                }
            }],
            columns: [
                [{
                    field: 'number',
                    title: 'Employee ID',
                    width: 120
                }, {
                    field: 'name',
                    title: 'Employee Name',
                    width: 200
                }]
            ],
        });

        $("#filter_employee_type").combobox({
            url: '<?= base_url('employee/contracts/reads') ?>',
            valueField: 'id',
            textField: 'name',
            prompt: "Choose All",
            icons: [{
                iconCls: 'icon-clear',
                handler: function(e) {
                    $(e.data.target).combobox('clear').combobox('textbox').focus();
                }
            }],
        });

        $("#filter_group").combobox({
            url: '<?= base_url('admin/privilege_groups/reads') ?>',
            valueField: 'id',
            textField: 'name',
            prompt: "Choose All",
            icons: [{
                iconCls: 'icon-clear',
                handler: function(e) {
                    $(e.data.target).combobox('clear').combobox('textbox').focus();
                }
            }],
        });
    });

    //Format Datepicker
    function myformatter(date) {
        var y = date.getFullYear();
        var m = date.getMonth() + 1;
        var d = date.getDate();
        return y + '-' + (m < 10 ? ('0' + m) : m) + '-' + (d < 10 ? ('0' + d) : d);
    }

    //Format Datepicker
    function myparser(s) {
        if (!s) return new Date();
        var ss = (s.split('-'));
        var y = parseInt(ss[0], 10);
        var m = parseInt(ss[1], 10);
        var d = parseInt(ss[2], 10);
        if (!isNaN(y) && !isNaN(m) && !isNaN(d)) {
            return new Date(y, m - 1, d);
        } else {
            return new Date();
        }
    }

    function StylerAllowence(value, row, index) {
        return 'background: #C6FFB5;';
    }

    function StylerDeduction(value, row, index) {
        return 'background: #FFB5B5;';
    }

    function numberformat(value, row) {
        const formatter = new Intl.NumberFormat('id-ID');

        return "<b>" + formatter.format(value) + "</b>";
    }
</script>