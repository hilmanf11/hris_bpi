<div class="easyui-accordion" style="width:100%; margin-bottom:5px;">
    <div title="Click this to hide the filter" data-options="selected:true" style="padding:10px; background:#F4F4F4;">
        <fieldset style="width: 99%; border:2px solid #d0d0d0; margin-bottom: 5px; border-radius:4px;">
            <legend><b>Form Filter Data</b></legend>
            <div style="width: 50%; float:left;">
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Trans Date</span>
                    <input style="width:28%;" id="filter_from" class="easyui-datebox" value="<?= date("Y-m-01") ?>" data-options="formatter:myformatter,parser:myparser, editable:false"> To
                    <input style="width:28%;" id="filter_to" class="easyui-datebox" value="<?= date("Y-m-t") ?>" data-options="formatter:myformatter,parser:myparser, editable:false">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Division</span>
                    <input style="width:60%;" id="filter_division" class="easyui-combobox">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Department</span>
                    <input style="width:60%;" id="filter_departement" class="easyui-combobox">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;"></span>
                    <a href="javascript:;" class="easyui-linkbutton" onclick="filter()"><i class="fa fa-search"></i> Filter Data</a>
                </div>
            </div>
            <div style="width: 49%; float:left;">
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Department Sub</span>
                    <input style="width:60%;" id="filter_departement_sub" class="easyui-combobox">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Employee</span>
                    <input style="width:60%;" id="filter_employee" class="easyui-combogrid">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Group</span>
                    <input style="width:60%;" id="filter_group" name="filter_group" class="easyui-combobox">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Bank Name</span>
                    <select style="width:60%;" name="filter_bank" id="filter_bank" class="easyui-combobox">
                        <option value="">All</option>
                        <option value="-">Cash</option>
                        <option value="Bank Mandiri">Bank Mandiri</option>
                        <option value="Bank Syariah Indonesia">Bank Syariah Indonesia</option>
                        <option value="Bank Central Asia">Bank Central Asia</option>
                    </select>
                </div>
            </div>
        </fieldset>
    </div>
</div>

<?= $button ?>

<iframe id="printout_recap" src="" style="width: 100%; height:500px; border: 0;" hidden></iframe>

<div class="easyui-panel" title="Print Preview" style="width:100%; padding:10px;" data-options="fit:true">
    <iframe id="printout" src="" style="width: 100%; height: 50%; border: 0;"></iframe>
</div>

<script>
    function reload() {
        window.location.reload();
    }

    function pdf() {
        var printIframe = document.getElementById("printout");
        if (printIframe && printIframe.contentWindow) {
            printIframe.contentWindow.print();
        }
    }

    function filter() {
        var filter_from = $("#filter_from").datebox('getValue');
        var filter_to = $("#filter_to").datebox('getValue');
        var filter_division = $("#filter_division").combobox('getValue');
        var filter_departement = $("#filter_departement").combobox('getValue');
        var filter_departement_sub = $("#filter_departement_sub").combobox('getValue');
        var filter_employee = $("#filter_employee").combogrid('getValue');
        var filter_group = $("#filter_group").combobox('getValue');
        var filter_bank = $("#filter_bank").combobox('getValue');

        if (filter_from == "" || filter_to == "") {
            toastr.warning("Please Choose Filter Date Range");
        } else {
            var url = "?filter_division=" + encodeURIComponent(filter_division) +
                "&filter_departement=" + encodeURIComponent(filter_departement) +
                "&filter_departement_sub=" + encodeURIComponent(filter_departement_sub) +
                '&filter_from=' + encodeURIComponent(filter_from) +
                '&filter_to=' + encodeURIComponent(filter_to) +
                '&filter_employee=' + encodeURIComponent(filter_employee) +
                '&filter_bank=' + encodeURIComponent(filter_bank) +
                '&filter_group=' + encodeURIComponent(filter_group);

            $("#printout").contents().find('html').html("<center><br><br><br><b style='font-size:18px;'>Loading Cash & Carry Report...</b></center>");
            $("#printout").attr('src', '<?= base_url('report/cash_carry/print') ?>' + url);
        }
    }

    function pdf_view(filter_employee) {
        var filter_from = $("#filter_from").datebox('getValue');
        var filter_to = $("#filter_to").datebox('getValue');

        if (filter_from == "" || filter_to == "") {
            toastr.warning("Please Choose Filter Date");
        } else {
            $("#dlg_print").window('open');
            var url = '?filter_from=' + encodeURIComponent(filter_from) +
                '&filter_to=' + encodeURIComponent(filter_to) +
                '&filter_employee=' + encodeURIComponent(filter_employee);

            $("#printout").contents().find('html').html("<center><br><br><br><b style='font-size:18px;'>Loading...</b></center>");
            $("#printout").attr('src', '<?= base_url('report/cash_carry/print') ?>' + url);
        }
    }

    function excel() {
        var filter_from = $("#filter_from").datebox('getValue');
        var filter_to = $("#filter_to").datebox('getValue');
        var filter_division = $("#filter_division").combobox('getValue');
        var filter_departement = $("#filter_departement").combobox('getValue');
        var filter_departement_sub = $("#filter_departement_sub").combobox('getValue');
        var filter_employee = $("#filter_employee").combogrid('getValue');
        var filter_group = $("#filter_group").combobox('getValue');
        var filter_bank = $("#filter_bank").combobox('getValue');

        if (filter_from == "" || filter_to == "") {
            toastr.warning("Please Choose Filter Date Range");
        } else {
            var url = "?filter_division=" + encodeURIComponent(filter_division) +
                "&filter_departement=" + encodeURIComponent(filter_departement) +
                "&filter_departement_sub=" + encodeURIComponent(filter_departement_sub) +
                '&filter_from=' + encodeURIComponent(filter_from) +
                '&filter_to=' + encodeURIComponent(filter_to) +
                '&filter_employee=' + encodeURIComponent(filter_employee) +
                '&filter_bank=' + encodeURIComponent(filter_bank) +
                '&filter_group=' + encodeURIComponent(filter_group);

            window.location.assign('<?= base_url('report/cash_carry/print/excel') ?>' + url);
        }
    }

    $(function() {
        // Init Division Combobox
        $('#filter_division').combobox({
            url: '<?php echo base_url('employee/divisions/reads'); ?>',
            valueField: 'id',
            textField: 'name',
            prompt: 'Choose All',
            icons: [{
                iconCls: 'icon-clear',
                handler: function(e) {
                    $(e.data.target).combobox('clear').combobox('textbox').focus();
                    $('#filter_departement').combobox('clear').combobox('reload', '<?php echo base_url('employee/departements/reads'); ?>');
                    $('#filter_departement_sub').combobox('clear');
                }
            }],
            onSelect: function(division) {
                $('#filter_departement').combobox('clear');
                $('#filter_departement_sub').combobox('clear');
                $('#filter_departement').combobox('reload', '<?php echo base_url('employee/departements/reads'); ?>?division_id=' + division.id);
            }
        });

        // Init Department Combobox
        $('#filter_departement').combobox({
            url: '<?php echo base_url('employee/departements/reads'); ?>',
            valueField: 'id',
            textField: 'name',
            prompt: 'Choose All',
            icons: [{
                iconCls: 'icon-clear',
                handler: function(e) {
                    $(e.data.target).combobox('clear').combobox('textbox').focus();
                    $('#filter_departement_sub').combobox('clear');
                }
            }],
            onSelect: function(departement) {
                var division_id = $('#filter_division').combobox('getValue');
                $('#filter_departement_sub').combobox('clear');
                $('#filter_departement_sub').combobox('reload', '<?php echo base_url('employee/departement_subs/reads'); ?>?division_id=' + division_id + '&departement_id=' + departement.id);
            }
        });

        // Init Sub Department Combobox
        $('#filter_departement_sub').combobox({
            url: '<?php echo base_url('employee/departement_subs/reads'); ?>',
            valueField: 'id',
            textField: 'name',
            prompt: 'Choose All',
            icons: [{
                iconCls: 'icon-clear',
                handler: function(e) {
                    $(e.data.target).combobox('clear').combobox('textbox').focus();
                }
            }]
        });

        // Init Employee Combogrid
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

        // Init Group Combobox
        $("#filter_group").combobox({
            url: '<?= base_url('employee/groups/reads') ?>',
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

    // Datepicker Formatters
    function myformatter(date) {
        var y = date.getFullYear();
        var m = date.getMonth() + 1;
        var d = date.getDate();
        return y + '-' + (m < 10 ? ('0' + m) : m) + '-' + (d < 10 ? ('0' + d) : d);
    }

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

    function FormatterFile(value, row) {
        var linkPrint = "pdf_view('" + row.employee_id + "')";
        return '<a href="#" onclick="' + linkPrint + '" class="btn btn-primary btn-sm" style="pointer-events: auto; opacity:1; width:100%;"><i class="fa fa-eye"></i> View</a>';
    }

    function FormatterExcel(value, row) {
        var linkPrint = "excel_detail('" + row.employee_id + "')";
        return '<a href="#" onclick="' + linkPrint + '" class="btn btn-success btn-sm" style="pointer-events: auto; opacity:1; width:100%;"><i class="fa fa-file"></i> Download</a>';
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