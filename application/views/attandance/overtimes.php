<!-- TABLE DATAGRID -->
<table id="dg" class="easyui-datagrid" style="width:100%;" toolbar="#toolbar">
    <thead>
        <tr>
            <th rowspan="2" field="ck" checkbox="true"></th>
            <th rowspan="2" data-options="field:'trans_date',width:100,halign:'center'">Request Date</th>
            <th rowspan="2" data-options="field:'request_code',width:120,halign:'center'">Request No</th>
            <th rowspan="2" data-options="field:'fullname',width:150,halign:'center'">Request Name</th>
            <th rowspan="2" data-options="field:'employee_id',width:100,halign:'center'">Employee ID</th>
            <th rowspan="2" data-options="field:'employee_name',width:250,halign:'center'">Employee Name</th>
            <th rowspan="2" data-options="field:'start',width:80,halign:'center'">Start</th>
            <th rowspan="2" data-options="field:'end',width:80,halign:'center'">End</th>
            <th rowspan="2" data-options="field:'type',width:100,halign:'center'">Type</th>
            <th rowspan="2" data-options="field:'duration_hour',width:120,halign:'center'">Duration</th>
            <th rowspan="2" data-options="field:'duration_convert',width:80,halign:'center'">Convert</th>
            <th rowspan="2" data-options="field:'amount',width:80,halign:'center', formatter:numberformat">Amount</th>
            <th rowspan="2" data-options="field:'remarks',width:200,halign:'center'">Remarks</th>
            <th colspan="3" data-options="field:'',width:100,halign:'center'"> Approval</th>
            <th colspan="2" data-options="field:'',width:100,halign:'center'"> Created</th>
            <th colspan="2" data-options="field:'',width:100,halign:'center'"> Updated</th>
        </tr>
        <tr>
            <th data-options="field:'approved',width:100,align:'center',styler:statusStyler, formatter:statusFormatter"> Status</th>
            <th data-options="field:'approved_by',width:120,align:'center'"> By</th>
            <th data-options="field:'approved_date',width:150,align:'center'"> Date</th>
            <th data-options="field:'created_by',width:100,align:'center'"> By</th>
            <th data-options="field:'created_date',width:150,align:'center'"> Date</th>
            <th data-options="field:'updated_by',width:100,align:'center'"> By</th>
            <th data-options="field:'updated_date',width:150,align:'center'"> Date</th>
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
                        <span style="width:30%; display:inline-block;">Period Date</span>
                        <input style="width:28%;" name="filter_from" id="filter_from" value="<?= date("Y-m-01") ?>" data-options="formatter:myformatter,parser:myparser, editable: false" class="easyui-datebox"> To
                        <input style="width:28%;" name="filter_to" id="filter_to" value="<?= date("Y-m-t") ?>" data-options="formatter:myformatter,parser:myparser, editable: false" class="easyui-datebox">
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
                        <span style="width:30%; display:inline-block;">Request Code</span>
                        <input style="width:60%;" id="filter_request_code" class="easyui-combobox">
                    </div>
                    <div class="fitem">
                        <span style="width:30%; display:inline-block;">Request By</span>
                        <input style="width:60%;" id="filter_request" class="easyui-combobox">
                    </div>
                    <div class="fitem">
                        <span style="width:30%; display:inline-block;">Approval</span>
                        <select style="width:60%;" id="filter_approval" class="easyui-combobox" data-options="panelHeight:'auto'">
                            <option value="">Choose All</option>
                            <option value="0">Approve</option>
                            <option value="1">Checked</option>
                        </select>
                    </div>
                </div>
            </fieldset>
        </div>
    </div>
    <?= $button ?>
</div>

<!-- DIALOG SAVE AND UPDATE -->
<div id="dlg_insert" class="easyui-dialog" title="Add New" data-options="closed: true,modal:true" style="width: 400px; padding:10px; top: 20px;">
    <form id="frm_insert" method="post" novalidate>
        <fieldset style="width:100%; border:1px solid #d0d0d0; margin-bottom: 10px; border-radius:4px; float: left;">
            <legend><b>Form Data</b></legend>
            <div class="fitem">
                <span style="width:30%; display:inline-block;">Trans Date</span>
                <input style="width:60%;" name="trans_date" id="trans_date" required class="easyui-datebox" data-options="formatter:myformatter,parser:myparser,editable:false" value="<?= date("Y-m-d") ?>">
            </div>
            <div class="fitem" id="edit_employee">
                <span style="width:30%; display:inline-block;">Employee</span>
                <input style="width:60%;" id="employee_id" name="employee_id" required class="easyui-combogrid">
            </div>
            <div class="fitem">
                <span style="width:30%; display:inline-block;">Request Code</span>
                <input style="width:60%;" name="request_code" readonly data-options="prompt:'Automatic from Employee'" id="request_code" class="easyui-textbox" required>
            </div>
            <div class="fitem">
                <span style="width:30%; display:inline-block;">Type</span>
                <select style="width:60%;" id="type" panelHeight="auto" data-options="prompt:'Choose Type Overtime'" required name="type" class="easyui-combobox">
                    <option value="REGULAR">REGULAR</option>
                    <option value="CORRECTION">CORRECTION</option>
                </select>
            </div>
            <div class="fitem">
                <span style="width:30%; display:inline-block;">Start <small style="color:red;">(time)</small></span>
                <input style="width:30%;" name="start" mask="99:99:99" required id="start" class="easyui-maskedbox">
            </div>
            <div class="fitem">
                <span style="width:30%; display:inline-block;">End <small style="color:red;">(time)</small></span>
                <input style="width:30%;" name="end" mask="99:99:99" required id="end" class="easyui-maskedbox">
            </div>
            <div class="fitem">
                <span style="width:30%; display:inline-block;">Remarks</span>
                <input style="width:60%; height:50px;" multiline="true" name="remarks" id="remarks" class="easyui-textbox">
            </div>
        </fieldset>
    </form>
</div>

<div id="dlg_upload" class="easyui-dialog" title="Upload Data" data-options="closed: true,modal:true" style="width: 500px; padding:10px; top: 20px;">
    <form id="frm_upload" method="post" enctype="multipart/form-data" novalidate>
        <fieldset style="width:100%; border:1px solid #d0d0d0; margin-bottom: 10px; border-radius:4px; float: left;">
            <legend><b>Form Data</b></legend>
            <div class="fitem">
                <span style="width:30%; display:inline-block;">File Upload</span>
                <input name="file_upload" style="width: 60%;" required="" accept=".xls" id="file_excel" class="easyui-filebox">
            </div>
        </fieldset>
    </form>
    <span style="float: left; color:green;">SUCCESS : <b id="p_success">0</b></span><span style="float: right; color:red;"> FAILED : <b id="p_failed">0</b></span>
    <div id="p_upload" class="easyui-progressbar" style="width:100%; margin-top: 10px;"></div>
    <center><b id="p_start">0</b> Of <b id="p_finish">0</b></center>
    <div id="p_remarks" title="History Upload" class="easyui-panel" style="width:100%; height:200px; padding:10px; margin-top: 10px;">
        <ul id="remarks">

        </ul>
    </div>
</div>

<!-- PDF -->
<iframe id="printout" src="" style="width: 100%;" hidden></iframe>

<script>
    //ADD DATA
    function add() {
        $('#dlg_insert').dialog('open');
        url_save = '<?= base_url('attandance/overtimes/create') ?>';
        $('#frm_insert').form('clear');
        $('#trans_date').datebox('setValue', '<?= date("Y-m-d") ?>');
        $('#start').maskedbox('setValue', '08:00:00');
        $('#end').maskedbox('setValue', '17:00:00');
        $('#employee_id').combogrid('enable');
        $('#request_code').textbox('enable');
    }

    //EDIT DATA
    function update() {
        var row = $('#dg').datagrid('getSelected');
        if (row) {
            $('#dlg_insert').dialog('open');
            $('#frm_insert').form('load', row);
            $('#employee_id').combogrid('disable');
            $('#request_code').textbox('disable');
            url_save = '<?= base_url('attandance/overtimes/update') ?>?id=' + window.btoa(row.id);
        } else {
            toastr.warning("Please select one of the data in the table first!", "Information");
        }
    }

    //DELETE DATA
    function deleted() {
        var rows = $('#dg').datagrid('getSelections');
        if (rows.length > 0) {
            $.messager.confirm('Warning', 'Are you sure you want to delete this data?', function(r) {
                if (r) {
                    for (var i = 0; i < rows.length; i++) {
                        var row = rows[i];
                        $.ajax({
                            method: 'post',
                            url: '<?= base_url('attandance/overtimes/delete') ?>',
                            data: {
                                id: row.id
                            },
                            success: function(result) {
                                var result = eval('(' + result + ')');
                            },
                            error: function(jqXHR, textStatus, errorThrown) {
                                toastr.error(jqXHR.statusText);
                                $.messager.alert("Error", jqXHR.statusText, 'error');
                            },
                            complete: function(data) {
                                $('#dg').datagrid('reload');
                            }
                        });
                    }
                }
            });
        } else {
            toastr.warning("Please select one of the data in the table first!", "Information");
        }
    }

    //UPLOAD DATA
    function upload() {
        $('#dlg_upload').dialog('open');
    }

    function download_excel() {
        window.location.assign('<?= base_url('template/tmp_overtimes.xls') ?>');
    }

    function filter() {
        var filter_from = $("#filter_from").datebox('getValue');
        var filter_to = $("#filter_to").datebox('getValue');
        var filter_division = $("#filter_division").combobox('getValue');
        var filter_departement = $("#filter_departement").combobox('getValue');
        var filter_departement_sub = $("#filter_departement_sub").combobox('getValue');
        var filter_employee = $("#filter_employee").combobox('getValue');
        var filter_request = $("#filter_request").combobox('getValue');
        var filter_approval = $("#filter_approval").combobox('getValue');
        var url = "?filter_from=" + filter_from +
            "&filter_to=" + filter_to +
            "&filter_division=" + filter_division +
            "&filter_departement=" + filter_departement +
            "&filter_departement_sub=" + filter_departement_sub +
            "&filter_employee=" + filter_employee +
            "&filter_request=" + filter_request +
            "&filter_approval=" + filter_approval;

        $('#dg').datagrid({
            url: '<?= base_url('attandance/overtimes/datatables') ?>' + url
        });

        $("#printout").contents().find('html').html("<center><br><br><br><b style='font-size:20px;'>Please Wait...</b></center>");
        $("#printout").attr('src', '<?= base_url('attandance/overtimes/print') ?>' + url);
    }

    //PRINT PDF
    function pdf() {
        $("#printout").get(0).contentWindow.print();
    }
    //PRINT EXCEL
    function excel() {
        var filter_from = $("#filter_from").datebox('getValue');
        var filter_to = $("#filter_to").datebox('getValue');
        var filter_division = $("#filter_division").combobox('getValue');
        var filter_departement = $("#filter_departement").combobox('getValue');
        var filter_departement_sub = $("#filter_departement_sub").combobox('getValue');
        var filter_employee = $("#filter_employee").combobox('getValue');
        var filter_request = $("#filter_request").combobox('getValue');
        var filter_approval = $("#filter_approval").combobox('getValue');
        var url = "?filter_from=" + filter_from +
            "&filter_to=" + filter_to +
            "&filter_division=" + filter_division +
            "&filter_departement=" + filter_departement +
            "&filter_departement_sub=" + filter_departement_sub +
            "&filter_employee=" + filter_employee +
            "&filter_request=" + filter_request +
            "&filter_approval=" + filter_approval;

        window.location.assign('<?= base_url('attandance/overtimes/print/excel') ?>' + url);
    }
    //RELOAD
    function reload() {
        window.location.reload();
    }

    $(function() {
        $('#dg').datagrid({
            url: '<?= base_url('attandance/overtimes/datatables') ?>',
            pagination: true,
            rownumbers: true,
            fit: true,
            pageList: [20, 50, 100, 500, 1000],
            pageSize: 20,
        });

        //SAVE DATA
        $('#dlg_insert').dialog({
            buttons: [{
                text: 'Save',
                iconCls: 'icon-ok',
                handler: function() {
                    var employee_id = $("#employee_id").combogrid('getValue');
                    var trans_date = $("#trans_date").datebox('getValue');
                    var start = $("#start").maskedbox('getValue');
                    var end = $("#end").maskedbox('getValue');

                    $.ajax({
                        type: "post",
                        url: "<?= base_url('attandance/overtimes/readOvertimeSumDept') ?>?employee_id=" + btoa(employee_id) + "&trans_date=" + btoa(trans_date) + "&start=" + btoa(start) + "&end=" + btoa(end),
                        data: "data",
                        dataType: "json",
                        success: function(response) {
                            if (response.theme == "success") {
                                $.ajax({
                                    type: "post",
                                    url: "<?= base_url('attandance/overtimes/readOvertimeSumEmp') ?>?employee_id=" + btoa(employee_id) + "&trans_date=" + btoa(trans_date) + "&start=" + btoa(start) + "&end=" + btoa(end),
                                    data: "data",
                                    dataType: "json",
                                    success: function(response_over) {
                                        if (response_over.theme == "success") {
                                            $('#frm_insert').form('submit', {
                                                url: url_save,
                                                onSubmit: function() {
                                                    return $(this).form('validate');
                                                },
                                                success: function(result) {
                                                    var result = eval('(' + result + ')');

                                                    if (result.theme == "success") {
                                                        toastr.success(result.message, result.title);
                                                    } else {
                                                        toastr.error(result.message, result.title);
                                                    }

                                                    $('#dlg_insert').dialog('close');
                                                    $('#dg').datagrid('reload');
                                                }
                                            });
                                        } else if (response_over.theme == "warning") {
                                            $.messager.confirm('Warning', response_over.message, function(r) {
                                                if (r) {
                                                    $('#frm_insert').form('submit', {
                                                        url: url_save,
                                                        onSubmit: function() {
                                                            return $(this).form('validate');
                                                        },
                                                        success: function(result) {
                                                            var result = eval('(' + result + ')');

                                                            if (result.theme == "success") {
                                                                toastr.success(result.message, result.title);
                                                            } else {
                                                                toastr.error(result.message, result.title);
                                                            }

                                                            $('#dlg_insert').dialog('close');
                                                            $('#dg').datagrid('reload');
                                                        }
                                                    });
                                                }
                                            });
                                        } else {
                                            toastr.error(response_over.message, response_over.title);
                                        }
                                    }
                                });
                            } else if (response.theme == "warning") {
                                $.messager.confirm('Warning', response.message, function(r) {
                                    $('#frm_insert').form('submit', {
                                        url: url_save,
                                        onSubmit: function() {
                                            return $(this).form('validate');
                                        },
                                        success: function(result) {
                                            var result = eval('(' + result + ')');

                                            if (result.theme == "success") {
                                                toastr.success(result.message, result.title);
                                            } else {
                                                toastr.error(result.message, result.title);
                                            }

                                            $('#dlg_insert').dialog('close');
                                            $('#dg').datagrid('reload');
                                        }
                                    });
                                });
                            } else {
                                toastr.error(response.message);
                            }
                        }
                    });
                }
            }]
        });

        //UPLOAD DATA
        $('#dlg_upload').dialog({
            buttons: [{
                text: 'List Failed',
                handler: function() {
                    window.open('<?= base_url('attandance/overtimes/uploadDownloadFailed') ?>', '_blank');
                }
            }, {
                text: 'Upload',
                iconCls: 'icon-ok',
                handler: function() {
                    $('#frm_upload').form('submit', {
                        url: '<?= base_url('attandance/overtimes/upload') ?>',
                        onSubmit: function() {
                            if ($(this).form('validate') == false) {
                                return $(this).form('validate');
                            } else {
                                $.messager.progress({
                                    title: 'Please Wait',
                                    msg: 'Importing Excel to Database'
                                });
                            }
                        },
                        success: function(result) {
                            $.messager.progress('close');

                            //Clear File
                            $.ajax({
                                url: "<?= base_url('attandance/overtimes/uploadclearFailed') ?>"
                            });

                            var json = eval('(' + result + ')');
                            requestData(json.total, json);

                            function requestData(total, json, number = 1, value = 0, success = 1, failed = 1) {
                                if (value < 100) {
                                    value = Math.floor((number / total) * 100);
                                    $('#p_upload').progressbar('setValue', value);
                                    $('#p_start').html(number);
                                    $('#p_finish').html(total);

                                    $.ajax({
                                        type: "POST",
                                        async: true,
                                        url: "<?= base_url('attandance/overtimes/uploadCreate') ?>",
                                        data: {
                                            "data": json[number - 1]
                                        },
                                        cache: false,
                                        dataType: "json",
                                        success: function(result) {
                                            if (result.theme == "success") {
                                                $('#p_success').html(success);
                                                var title = "<b style='color: green;'>" + result.title + "</b> | " + result.message;
                                                requestData(total, json, number + 1, value, success + 1, failed + 0);
                                            } else {
                                                $('#p_failed').html(failed);
                                                var title = "<b style='color: red;'>" + result.title + "</b> | " + result.message;

                                                //Json Failed
                                                $.ajax({
                                                    type: "POST",
                                                    async: true,
                                                    url: "<?= base_url('attandance/overtimes/uploadcreateFailed') ?>",
                                                    data: {
                                                        data: json[number - 1],
                                                        message: result.message
                                                    },
                                                    cache: false
                                                });

                                                requestData(total, json, number + 1, value, success + 0, failed + 1);
                                            }

                                            $("#p_remarks").append(title + "<br>");
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
                                                    requestData(total, json, number, value, success + 0, failed + 0);
                                                }, 5000);
                                            }
                                        }
                                    });
                                }
                            }
                        }
                    });
                }
            }]
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

        $('#employee_id').combogrid({
            url: '<?= base_url('employee/employees/reads') ?>',
            panelWidth: 450,
            idField: 'id',
            textField: 'name',
            mode: 'remote',
            fitColumns: true,
            prompt: 'Choose Employee',
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
            onSelect: function(emp) {
                $.ajax({
                    url: '<?= base_url('attandance/overtimes/requestCode') ?>',
                    type: 'post',
                    data: 'departement_id=' + emp.departement_id,
                    success: function(requestCode) {
                        $("#request_code").textbox('setValue', requestCode);
                    }
                });
            }
        });

        $('#filter_request_code').combobox({
            url: '<?= base_url('attandance/overtimes/readRequestCode') ?>',
            valueField: 'request_code',
            textField: 'request_code',
            prompt: "Choose All",
            icons: [{
                iconCls: 'icon-clear',
                handler: function(e) {
                    $(e.data.target).combobox('clear').combobox('textbox').focus();
                }
            }]
        });

        $('#filter_request').combobox({
            url: '<?= base_url('attandance/overtimes/readRequest') ?>',
            valueField: 'created_by',
            textField: 'created_by',
            prompt: "Choose All",
            icons: [{
                iconCls: 'icon-clear',
                handler: function(e) {
                    $(e.data.target).combobox('clear').combobox('textbox').focus();
                }
            }]
        });
    });

    //CELLSTYLE STATUS
    function statusStyler(value, row, index) {
        if (row.approved_to == "" || row.approved_to == null) {
            return 'background: #53D636; color:white;';
        } else {
            return 'background: #FF5F5F; color:white;';
        }
    }
    //FORMATTER STATUS
    function statusFormatter(value, row) {
        if (row.approved_to == "" || row.approved_to == null) {
            return 'Approved';
        } else {
            return 'Checked';
        }
    };

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

    function numberformat(value, row) {
        const formatter = new Intl.NumberFormat('id-ID', {
            style: 'currency',
            currency: 'IDR',
            minimumFractionDigits: 0
        });

        return "<b>" + formatter.format(value) + "</b>";
    }
</script>