<!-- TABLE DATAGRID -->
<table id="dg" class="easyui-datagrid" style="width:99.5%;" toolbar="#toolbar">
    <thead>
        <tr>
            <th field="ck" checkbox="true"></th>
            <th data-options="field:'number',width:150">Number ID</th>
            <th data-options="field:'name',width:200">Name</th>
            <th data-options="field:'username',width:150">Username</th>
            <th data-options="field:'email',width:200">Email</th>
            <th data-options="field:'phone',width:150">Phone</th>
            <th data-options="field:'position',width:200">Position</th>
            <th data-options="field:'avatar',width:200">File Foto</th>
            <th data-options="field:'theme',width:100">Theme</th>
            <th data-options="field:'actived',width:150, styler:cellStyler, formatter:cellFormatter">Status</th>
            <th data-options="field:'access',width:150, styler:cellStyler2, formatter:cellFormatter2">Access</th>
        </tr>
    </thead>
</table>

<!-- TOOLBAR DATAGRID -->
<div id="toolbar" style="height: 35px;">
    <?= $button ?>
</div>

<!-- DIALOG SAVE AND UPDATE -->
<div id="dlg_insert" class="easyui-dialog" title="Add New" data-options="closed: true" style="width: 400px; padding:10px; top: 20px;">
    <form id="frm_insert" method="post" enctype="multipart/form-data" novalidate>
        <fieldset style="width:100%; border:1px solid #d0d0d0; margin-bottom: 10px; border-radius:4px; float: left;">
            <legend><b>Form Data</b></legend>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Employee ID</span>
                <input style="width:60%;" name="number" id="number" required="" class="easyui-combogrid">
            </div>
            <div class="fitem" hidden>
                <span style="width:35%; display:inline-block;">Departement</span>
                <input style="width:60%;" name="departement_id" id="departement_id" class="easyui-textbox">
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Fullname</span>
                <input style="width:60%;" name="name" id="name" required="" class="easyui-textbox">
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Username</span>
                <input style="width:60%;" name="username" id="username" required="" class="easyui-textbox">
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Password</span>
                <input style="width:60%;" name="password" id="password" required="" class="easyui-passwordbox">
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Position</span>
                <input style="width:60%;" name="position" id="position" required="true" data-options="prompt:'Staff Production'" class="easyui-textbox">
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Email</span>
                <input style="width:60%;" name="email" id="email" required="true" class="easyui-textbox">
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Phone</span>
                <input style="width:60%;" name="phone" id="phone" data-options="buttonText:'+62',buttonAlign:'left'" class="easyui-numberbox">
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Foto Profile</span>
                <input style="width:60%;" name="avatar" id="avatar" class="easyui-filebox" accept=".jpg, .png, .jpeg">
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Theme App</span>
                <select style="width:60%;" name="theme" id="theme" class="easyui-combobox">
                    <option value="default">Default</option>
                    <option value="cupertino">Cupertino</option>
                    <option value="black">Black</option>
                    <option value="bootstrap">Bootstrap</option>
                    <option value="gray">Gray</option>
                    <option value="pepper-grinder">Pepper Grinder</option>
                    <option value="material">Material</option>
                    <option value="material-blue">Material Blue</option>
                    <option value="material-teal">Material Teal</option>
                    <option value="metro">Metro</option>
                    <option value="metro-blue">Metro Blue</option>
                    <option value="metro-gray">Metro Gray</option>
                    <option value="metro-green">Metro Green</option>
                    <option value="metro-orange">Metro Orange</option>
                    <option value="metro-red">Metro Red</option>
                    <option value="sunny">Sunny</option>
                </select>
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Status</span>
                <select style="width:60%;" name="actived" required="" panelHeight="auto" class="easyui-combobox">
                    <option value="0">Active</option>
                    <option value="1">Not Active</option>
                </select>
            </div>
            <div class="alert alert-warning" role="alert">
                If you select full access then all departments will appear, if limited then only appear
                according to department
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Access</span>
                <select style="width:60%;" name="access" required="" panelHeight="auto" class="easyui-combobox">
                    <option value="0">Full Access</option>
                    <option value="1">Limited</option>
                </select>
            </div>
        </fieldset>
    </form>
</div>

<!-- PRINT PDF -->
<iframe id="printout" src="<?= base_url('admin/users/print') ?>" style="width: 100%;" hidden></iframe>
<script>
    //ADD DATA
    function add() {
        $('#dlg_insert').dialog('open');
        $('#username').textbox('enable');
        $('#password').textbox('enable');
        url_save = '<?= base_url('admin/users/create') ?>';
        $('#frm_insert').form('clear');
    }
    //EDIT DATA
    function update() {
        var row = $('#dg').datagrid('getSelected');
        if (row) {
            $('#dlg_insert').dialog('open');
            $('#frm_insert').form('load', row);
            $('#username').textbox('disable');
            $('#password').textbox('disable');
            url_save = '<?= base_url('admin/users/update') ?>?id=' + btoa(row.id);
        } else {
            toastr.info("Please select one of the data in the table first");
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
                            url: '<?= base_url('admin/users/delete') ?>',
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
            toastr.info("Please select one of the data in the table first");
        }
    }
    //PRINT PDF
    function pdf() {
        $("#printout").get(0).contentWindow.print();
    }
    //EXPORT EXCEL
    function excel() {
        window.location.assign('<?= base_url('admin/users/print/excel') ?>');
    }
    //RELOAD
    function reload() {
        window.location.reload();
    }

    $(function() {
        //SETTING DATAGRID EASYUI
        $('#dg').datagrid({
            url: '<?= base_url('admin/users/datatables') ?>',
            pagination: true,
            clientPaging: false,
            remoteFilter: true,
            rownumbers: true,
            fit: true,
            pageList: [20, 50, 100, 500, 1000],
            pageSize: 20,
        }).datagrid('enableFilter');

        //SAVE DATA
        $('#dlg_insert').dialog({
            buttons: [{
                text: 'Save',
                iconCls: 'icon-ok',
                handler: function() {
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
            }]
        });

        $('#number').combogrid({
            url: '<?= base_url('employee/employees/readFulls') ?>',
            panelWidth: 450,
            idField: 'number',
            textField: 'number',
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
            onSelect: function(val, row) {
                $("#departement_id").textbox('setValue', row.departement_id);
                $("#name").textbox('setValue', row.name);
                $("#phone").textbox('setValue', row.mobile_phone);
                $("#email").textbox('setValue', row.email);
                $("#position").textbox('setValue', row.position_name);
            }
        });
    });

    //CELLSTYLE STATUS
    function cellStyler(value, row, index) {
        if (value == 0) {
            return 'background: #53D636; color:white;';
        } else {
            return 'background: #FF5F5F; color:white;';
        }
    }

    function cellStyler2(value, row, index) {
        if (value == 0) {
            return 'background: #53D636; color:white;';
        } else {
            return 'background: #FAD277; color:white;';
        }
    }
    //FORMATTER STATUS
    function cellFormatter(value) {
        if (value == 0) {
            return 'Active';
        } else {
            return 'Not Active';
        }
    };

    function cellFormatter2(value) {
        if (value == 0) {
            return 'Full Access';
        } else {
            return 'Limited';
        }
    };
</script>