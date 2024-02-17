<div class="easyui-accordion" style="width:100%;">
    <div title="Click this to hide the filter" data-options="selected:true" style="padding:10px; background:#F4F4F4;">
        <!-- <div style="width: 100%; display: grid; grid-template-columns: auto auto auto; grid-gap: 5px; display: flex;"> -->
        <fieldset style="width: 99%; border:2px solid #d0d0d0; margin-bottom: 5px; margin-top: 5px; border-radius:4px;">
            <legend><b>Form Filter Data</b></legend>
            <div style="width: 50%; float:left;">
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Period Date</span>
                    <input style="width:28%;" name="filter_from" id="filter_from" class="easyui-combogrid"> To
                    <input style="width:28%;" name="filter_to" id="filter_to" data-options="prompt:'Date To'" readonly class="easyui-textbox">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Group</span>
                    <input style="width:60%;" id="filter_group" name="filter_group" class="easyui-combobox">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Source</span>
                    <input style="width:60%;" id="filter_source" name="filter_source" class="easyui-combobox">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Employee</span>
                    <input style="width:60%;" id="filter_employee" class="easyui-combogrid">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;"></span>
                    <a href="javascript:;" class="easyui-linkbutton" onclick="filter()"><i class="fa fa-search"></i> Filter Data</a>
                </div>
            </div>
        </fieldset>
        <?= $button ?>
    </div>
</div>

<div class="easyui-panel" title="Print Preview" style="width:100%;padding:10px;" data-options="fit:true">
    <iframe id="printout" src="" style="width: 100%; height:98%; border: 0;"></iframe>
</div>
<script>
    function reload() {
        window.location.reload();
    }

    function pdf() {
        $("#printout").get(0).contentWindow.print();
    }

    function filter() {
        var filter_from = $("#filter_from").combogrid('getValue');
        var filter_to = $("#filter_to").textbox('getValue');
        var filter_group = $("#filter_group").combobox('getValue');
        var filter_source = $("#filter_source").combobox('getValue');
        var filter_employee = $("#filter_employee").combogrid('getValue');

        if (filter_from == "" || filter_to == "" || filter_group == "") {
            toastr.warning("Please Choose Filter Date and Group");
        } else {
            var url = "?filter_from=" + filter_from +
                "&filter_to=" + filter_to +
                "&filter_group=" + filter_group +
                "&filter_source=" + filter_source +
                "&filter_employee=" + filter_employee;

            $("#printout").contents().find('html').html("<center><br><br><br><b style='font-size:20px;'>Please Wait...</b></center>");
            $("#printout").attr('src', '<?= base_url('report/student_payrolls/print') ?>' + url);
        }
    }

    function excel() {
        var filter_from = $("#filter_from").combogrid('getValue');
        var filter_to = $("#filter_to").textbox('getValue');
        var filter_group = $("#filter_group").combobox('getValue');
        var filter_source = $("#filter_source").combobox('getValue');
        var filter_employee = $("#filter_employee").combogrid('getValue');

        if (filter_from == "" || filter_to == "" || filter_group == "") {
            toastr.warning("Please Choose Filter Date and Group");
        } else {
            var url = "?filter_from=" + filter_from +
                "&filter_to=" + filter_to +
                "&filter_group=" + filter_group +
                "&filter_source=" + filter_source +
                "&filter_employee=" + filter_employee;

            $.messager.alert('Please Wait', 'Export Excel Data..!', 'info');
            window.location.assign('<?= base_url('report/student_payrolls/print/excel') ?>' + url);
        }
    }

    $(function() {
        //Filter Cutoff
        $('#filter_from').combogrid({
            url: '<?= base_url('payroll/cutoff/reads') ?>',
            panelWidth: 300,
            idField: 'start',
            textField: 'start',
            mode: 'remote',
            fitColumns: true,
            prompt: 'Date From',
            columns: [
                [{
                    field: 'start',
                    title: 'Date From',
                    width: 120
                }, {
                    field: 'finish',
                    title: 'Date To',
                    width: 120
                }]
            ],
            onSelect: function(val, row) {
                $("#filter_to").textbox('setValue', row.finish);
            }
        });

        $('#filter_group').combobox({
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
            onSelect: function(group) {
                $('#filter_source').combobox({
                    url: '<?= base_url('employee/sources/reads') ?>/' + group.id,
                    valueField: 'id',
                    textField: 'name',
                    prompt: "Choose All",
                    icons: [{
                        iconCls: 'icon-clear',
                        handler: function(e) {
                            $(e.data.target).combobox('clear').combobox('textbox').focus();
                        }
                    }],
                    onSelect: function(source) {
                        $('#filter_employee').combogrid({
                            url: '<?= base_url('employee/employees/reads?source_id=') ?>' + source.id,
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
</script>