<!-- TABLE DATAGRID -->
<table id="dg" class="easyui-datagrid" style="width:100%;" data-options="rownumbers:true,singleSelect:true,fit:true" toolbar="#toolbar"></table>

<!-- TOOLBAR DATAGRID -->
<div id="toolbar" style="height: 75px;">
    <fieldset style="margin-bottom: 10px;">
        <legend>Choose User</legend>
        <form id="frm_search" method="post" enctype="multipart/form-data" novalidate>
            <div class="row">
                <div class="col-lg-5">
                    <div class="fitem">
                        <span style="width:150px; display:inline-block;">Name</span>
                        <input style="width:300px;" id="username" class="easyui-textbox">
                    </div>
                </div>
            </div>
        </form>
    </fieldset>
</div>

<br>

<script>
    $(function() {
        $('#username').combogrid({
            onSelect: function(value, row) {
                var username = row.username;
                $.ajax({
                    url: '<?= base_url('admin/privilege_groups/create') ?>',
                    type: 'post',
                    data: 'username=' + username,
                    success: function(msg) {
                        $('#dg').treegrid({
                            url: '<?= base_url('admin/privilege_groups/datatables') ?>?username=' + username,
                            rownumbers: true,
                            singleSelect: true,
                            idField: 'id',
                            treeField: 'name',
                            fit: true,
                            onBeforeLoad: function(row, param) {
                                if (!row) {
                                    param.id = 0;
                                }
                            },
                            columns: [
                                [{
                                    field: 'name',
                                    title: 'Group Name',
                                    width: 250,
                                    align: 'left'
                                }, {
                                    field: 'status',
                                    title: 'Status',
                                    width: 80,
                                    align: 'center',
                                    formatter: function(hasil, row) {
                                        var action = "save_data('" + row.id + "')";
                                        if (row.status == "1") {
                                            return '<input type="checkbox" id="status' + row.id + '" value="status" onclick="' + action + '" checked>';
                                        } else {
                                            return '<input type="checkbox" id="status' + row.id + '" value="status" onclick="' + action + '">';
                                        }
                                    }
                                }]
                            ],
                            onCheck: function(index, row) {
                                //$(this).datagrid('refreshRow', index);
                            },
                            onUncheck: function(index, row) {
                                //$(this).datagrid('refreshRow', index);
                            }
                        });
                    }
                });
            }
        });

        //Get Username
        $('#username').combogrid({
            url: '<?= base_url('admin/privilege_groups/getUsers') ?>',
            panelWidth: 400,
            idField: 'username',
            textField: 'name',
            mode: 'remote',
            fitColumns: true,
            columns: [
                [{
                    field: 'name',
                    title: 'Name',
                    width: 200
                }, {
                    field: 'username',
                    title: 'Username',
                    width: 200
                }, ]
            ]
        });
    });

    function save_data(data) {
        var username = $("#username").val();
        var id = data;

        if ($("#status" + data).is(':checked')) {
            var h_status = "1";
        } else {
            var h_status = "0";
        }

        $.ajax({
            url: '<?= base_url('admin/privilege_groups/update/') ?>?id=' + window.btoa(id),
            type: 'post',
            data: '&status=' + h_status,
            success: function(msg) {
                var result = eval('(' + msg + ')');
                if (result.theme == "success") {
                    toastr.success(result.message, result.title);
                } else {
                    toastr.error(result.message, result.title);
                }
            },
            error: function(jqXHR, textStatus, errorThrown) {
                toastr.error(jqXHR.statusText);
            }
        });
    }
</script>