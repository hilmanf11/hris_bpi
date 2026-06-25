<!-- TABLE DATAGRID -->
<table id="dg" class="easyui-datagrid" style="width:99.5%;" toolbar="#toolbar">
    <thead>
        <tr>
            <th rowspan="2" field="ck" checkbox="true"></th>
            <th rowspan="2" data-options="field:'name',width:400,halign:'center'">Title</th>
            <th rowspan="2" data-options="field:'division_name',width:200,halign:'center'">Division</th>
            <th rowspan="2" data-options="field:'departement_name',width:200,halign:'center'">Departement</th>
            <th rowspan="2" data-options="field:'start_date',width:100,halign:'center'">Start Date</th>
            <th rowspan="2" data-options="field:'finish_date',width:100,halign:'center'">Finish Date</th>
            <th rowspan="2" data-options="field:'attachment',width:80,halign:'center',formatter:attachmentButton">Attachment</th>
            <th colspan="2" data-options="field:'',width:100,halign:'center'"> Created</th>
            <th colspan="2" data-options="field:'',width:100,halign:'center'"> Updated</th>
        </tr>
        <tr>
            <th data-options="field:'created_by',width:100,align:'center'"> By</th>
            <th data-options="field:'created_date',width:150,align:'center'"> Date</th>
            <th data-options="field:'updated_by',width:100,align:'center'"> By</th>
            <th data-options="field:'updated_date',width:150,align:'center'"> Date</th>
        </tr>
    </thead>
</table>

<!-- TOOLBAR DATAGRID -->
<div id="toolbar" style="height: 35px;">
    <?= $button ?>
</div>

<!-- DIALOG SAVE AND UPDATE -->
<div id="dlg_insert" class="easyui-dialog" title="Add New" data-options="closed: true, modal:true" style="width: 800px; height: 550px; padding:10px; top: 20px;">
    <form id="frm_insert" method="post" enctype="multipart/form-data" novalidate>
        <fieldset style="width:100%; border:1px solid #d0d0d0; margin-bottom: 10px; border-radius:4px; float: left;">
            <legend><b>Form Data</b></legend>
            <div class="fitem">
                <span style="width:25%; display:inline-block;">Division</span>
                <input style="width:50%;" name="division_id" id="division_id" required="" class="easyui-combobox">
            </div>
            <div class="fitem">
                <span style="width:25%; display:inline-block;">Departement</span>
                <input style="width:50%;" name="departement_id[]" id="departement_id" required="" class="easyui-combobox">
            </div>
            <div class="fitem">
                <span style="width:25%; display:inline-block;">Title</span>
                <input style="width:50%;" name="name" required="" class="easyui-textbox">
            </div>
            <div class="fitem">
                <span style="width:25%; display:inline-block;">Display Date</span>
                <input style="width:25%;" name="start_date" required="" data-options="formatter:myformatter,parser:myparser, editable: false, prompt:'Start Date'" class="easyui-datebox">
                <input style="width:25%;" name="finish_date" required="" data-options="formatter:myformatter,parser:myparser, editable: false, prompt:'Finish Date'" class="easyui-datebox">
            </div>
            <div class="fitem">
                <span style="width:25%; display:inline-block;">Attachment</span>
                <input style="width:50%;" name="attachment" class="easyui-filebox" data-options="prompt:'File Max 1mb, .jpg, .png & .pdf'">
            </div>
        </fieldset>
        <div style="width:100%; float: left;">
            <textarea class="summernote" name="description" id="description"></textarea>
        </div>
    </form>
</div>

<!-- PDF -->
<iframe id="printout" src="<?= base_url('announcement/news/print') ?>" style="width: 100%;" hidden></iframe>

<script>
    //ADD DATA
    function add() {
        $('#dlg_insert').dialog('open');
        url_save = '<?= base_url('announcement/news/create') ?>';
        $('#frm_insert').form('clear');
    }

    //EDIT DATA
    function update() {
        var row = $('#dg').datagrid('getSelected');
        if (row) {
            $('#dlg_insert').dialog('open');
            $('#frm_insert').form('load', row);
            $('#departement_id').combobox('setValue', row.departement_id);
            $("#description").summernote("code", row.description);

            url_save = '<?= base_url('announcement/news/update') ?>?id=' + btoa(row.id);
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
                            url: '<?= base_url('announcement/news/delete') ?>',
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
    //PRINT PDF
    function pdf() {
        $("#printout").get(0).contentWindow.print();
    }
    //PRINT EXCEL
    function excel() {
        window.location.assign('<?= base_url('announcement/news/print/excel') ?>');
    }
    //RELOAD
    function reload() {
        window.location.reload();
    }

    $(function() {
        $('.summernote').summernote({
            height: 300,
            hintMode: 'words',
            hintSelect: 'next',
            toolbar: [
                // [groupName, [list of button]]
                ['style', ['bold', 'italic', 'underline', 'clear']],
                ['font', ['strikethrough', 'superscript', 'subscript']],
                ['fontsize', ['fontsize']],
                ['para', ['ul', 'ol', 'paragraph']],
                ['height', ['height']],
                ['table', ['table']]
            ]
        });

        //SETTING DATAGRID EASYUI
        $('#dg').datagrid({
            url: '<?= base_url('announcement/news/datatables') ?>',
            pagination: true,
            clientPaging: false,
            remoteFilter: true,
            rownumbers: true,
            fit: true,
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

        $('#division_id').combobox({
            url: '<?php echo base_url('employee/divisions/reads'); ?>',
            valueField: 'id',
            textField: 'name',
            prompt: 'Choose Division',
            onSelect: function(div) {
                $('#departement_id').combobox({
                    url: '<?php echo base_url('employee/departements/reads'); ?>?division_id=' + div.id,
                    valueField: 'id',
                    textField: 'name',
                    multiple: true,
                    prompt: 'Choose Departement',
                });
            }
        });
    });

    function attachmentButton(val, row){
        if(val != ""){
            return '<a class="button-green" target="_blank" style="width:100%;" href="<?= base_url('assets/document/news/') ?>' + val + '"><i class="fa fa-eye"></i> View</a>';
        }
    }

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
</script>