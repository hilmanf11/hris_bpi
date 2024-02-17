<!-- TABLE DATAGRID -->
<table id="dg" class="easyui-datagrid" style="width:100%;" toolbar="#toolbar">
    <thead>
        <tr>
            <th rowspan="2" field="ck" checkbox="true"></th>
            <th rowspan="2" data-options="field:'request_id',width:120,halign:'center'">Request ID</th>
            <th rowspan="2" data-options="field:'request_by',width:150,halign:'center'">Request Name</th>
            <th rowspan="2" data-options="field:'name',width:200,halign:'center'">Candidate Name</th>
            <th rowspan="2" data-options="field:'division_name',width:150,halign:'center'">Division</th>
            <th rowspan="2" data-options="field:'departement_name',width:150,halign:'center'">Departement</th>
            <th rowspan="2" data-options="field:'departement_sub_name',width:150,halign:'center'">Departement Sub</th>
            <th rowspan="2" data-options="field:'psycological_total',width:100,halign:'center',align:'center', formatter:FormatterPsychological">Psychology</th>
            <th rowspan="2" data-options="field:'interview_total',width:100,halign:'center',align:'center', formatter:FormatterInterview">Interview</th>
            <th rowspan="2" data-options="field:'medical_total',width:100,halign:'center',align:'center', formatter:FormatterMedical">Medical</th>
            <th rowspan="2" data-options="field:'skill_total',width:100,halign:'center',align:'center', formatter:FormatterSkill">Skill</th>
            <th rowspan="2" data-options="field:'status',width:100,halign:'center',align:'center', formatter:FormatterStatus">Status</th>
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
<div id="toolbar">
    <div class="easyui-accordion" style="width:100%; margin-bottom:5px;">
        <div title="Click this to hide the filter" data-options="onCollapse: function(){ $('#dg').datagrid('reload'); }" style="padding:10px; background:#F4F4F4;">
            <fieldset style="width: 50%; border:2px solid #d0d0d0; border-radius:4px;">
                <legend><b>Form Filter Data</b></legend>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Request ID</span>
                    <input style="width:60%;" id="filter_request" class="easyui-combogrid">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Candidate Name</span>
                    <input style="width:60%;" id="filter_candidate" class="easyui-textbox">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;"></span>
                    <a href="javascript:;" class="easyui-linkbutton" onclick="filter()"><i class="fa fa-search"></i> Filter Data</a>
                    <a href="javascript:;" class="easyui-linkbutton" onclick="closing()"><i class="fa fa-check-circle"></i> Closing Request</a>
                </div>
            </fieldset>
        </div>
    </div>
    <?= $button ?>
    <a href="javascript:;" class="easyui-linkbutton" data-options="plain:true"><i class="fa fa-whatsapp"></i> <span id="wa">Send Whatsapp</span></a>
    <a href="javascript:;" class="easyui-linkbutton" data-options="plain:true"><i class="fa fa-envelope"></i> <span id="mail">Send Email</span></a>
</div>

<!-- DIALOG SAVE AND UPDATE -->
<div id="dlg_insert" class="easyui-dialog" title="Add New" data-options="closed: true,modal:true" style="width: 800px; padding:10px; top: 20px;">
    <form id="frm_insert" method="post" enctype="multipart/form-data" novalidate>
        <div style="float: left; width: 50%; margin-right:5px;">
            <fieldset style="width:100%; border:1px solid #d0d0d0; margin-bottom: 10px; border-radius:4px; float: left;">
                <legend>Form Personal</legend>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Request ID</span>
                    <input style="width:60%;" name="request_id" id="request_id" required class="easyui-combogrid">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Candidate Name</span>
                    <input style="width:60%;" name="name" id="name" required class="easyui-textbox">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Email</span>
                    <input style="width:60%;" name="email" id="email" required class="easyui-textbox">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Mobile Phone</span>
                    <input style="width:60%;" name="mobile_phone" id="mobile_phone" data-options="buttonText:'+62', buttonAlign:'left'" required class="easyui-numberbox">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Address</span>
                    <input style="width:60%; height: 50px;" name="address" multiline="true" id="address" class="easyui-textbox">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Place of Birth</span>
                    <input style="width:60%;" name="place_birth" id="place_birth" class="easyui-textbox">
                </div>
                <div class="fitem" style="margin-bottom: 5px;">
                    <span style="width:35%; display:inline-block;">Birth Date</span>
                    <input style="width:60%;" name="birthday" id="birthday" data-options="formatter:myformatter,parser:myparser, editable: false" class="easyui-datebox">
                </div>
                <div class="fitem" style="margin-bottom: 5px;">
                    <span style="width:35%; display:inline-block;">Gender</span>
                    <input class="easyui-radiobutton" name="gender" checked="true" value="MALE"> Male &nbsp;
                    <input class="easyui-radiobutton" name="gender" value="FEMALE"> Female
                </div>
                <div class="fitem" style="margin-bottom: 5px;">
                    <span style="width:35%; display:inline-block;">Blood Type</span>
                    <input class="easyui-radiobutton" name="blood" checked="true" value="-"> - &nbsp;
                    <input class="easyui-radiobutton" name="blood" value="A"> A &nbsp;
                    <input class="easyui-radiobutton" name="blood" value="B"> B &nbsp;
                    <input class="easyui-radiobutton" name="blood" value="AB"> AB &nbsp;
                    <input class="easyui-radiobutton" name="blood" value="O"> O &nbsp;
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Religion</span>
                    <input style="width:60%;" name="religion_id" panelHeight="auto" id="religion_id" class="easyui-combobox">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Marital Status</span>
                    <input style="width:60%;" name="marital_id" id="marital_id" class="easyui-combogrid">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">National ID No</span>
                    <input style="width:60%;" name="national_id" id="national_id" class="easyui-numberbox">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Tax No (NPWP)</span>
                    <input style="width:60%;" name="tax_id" id="tax_id" class="easyui-numberbox">
                </div>
            </fieldset>
        </div>
        <div style="float: left; width: 49%;">
            <fieldset style="width:100%; border:1px solid #d0d0d0; margin-bottom: 10px; border-radius:4px; float: left;">
                <legend>Form Placement</legend>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Division</span>
                    <input style="width:60%;" name="division_id" required="" id="division_id" data-options="prompt: 'Choose Division', editable: false" class="easyui-combobox">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Departement</span>
                    <input style="width:60%;" name="departement_id" required="" id="departement_id" data-options="prompt: 'Choose Departement', editable: false" class="easyui-combobox">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Sub Departement</span>
                    <input style="width:60%;" name="departement_sub_id" required="" id="departement_sub_id" data-options="prompt: 'Choose Sub Departement', editable: false" class="easyui-combobox">
                </div>
            </fieldset>
            <fieldset style="width:100%; border:1px solid #d0d0d0; margin-bottom: 10px; border-radius:4px; float: left;">
                <legend>Attachment Upload</legend>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">CV/Resume</span>
                    <input style="width:60%;" name="resume" id="resume" accept=".pdf" data-options="prompt: 'File PDF'" class="easyui-filebox">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Certificate</span>
                    <input style="width:60%;" name="certificate" id="certificate" accept=".pdf" data-options="prompt: 'File PDF'" class="easyui-filebox">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Health</span>
                    <input style="width:60%;" name="health" id="health" accept=".pdf" data-options="prompt: 'File PDF'" class="easyui-filebox">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Foto ID</span>
                    <input style="width:60%;" name="image_id" accept=".jpg, .png, .jpeg" id="image_id" class="easyui-filebox">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Foto Profile</span>
                    <input style="width:60%;" name="image_profile" accept=".jpg, .png, .jpeg" id="image_profile" class="easyui-filebox">
                </div>
            </fieldset>
        </div>
    </form>
</div>

<div id="dlg_insert_psikologi" class="easyui-dialog" title="Add Psychology" data-options="closed: true,modal:true" style="width: 800px; padding:10px; top: 20px;">
    <form id="frm_insert_psikologi" method="post" enctype="multipart/form-data" novalidate>
        <fieldset style="width:100%; border:1px solid #d0d0d0; margin-bottom: 10px; border-radius:4px; float: left;">
            <legend>Form Input Data</legend>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Candidate ID</span>
                <input style="width:60%;" name="id" id="psy_id" readonly class="easyui-textbox">
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Candidate Name</span>
                <input style="width:60%;" name="name" id="psy_name" readonly class="easyui-textbox">
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Trans Date</span>
                <input style="width:60%;" name="trans_date" id="psy_date" required="" data-options="formatter:myformatter,parser:myparser,editable: false" class="easyui-datebox">
            </div>
        </fieldset>
        <fieldset style="width:100%; border:1px solid #d0d0d0; margin-bottom: 10px; border-radius:4px; float: left;">
            <legend>Form Psycological</legend>
            <table style="width: 100%;">
                <?php
                echo "  <tr>
                            <th width='150'></th>
                            <th width='20'>Pasing Grade</th>
                            <th width='20'>Result</th>
                            <th width='200'>Remarks</th>
                        </tr>";
                foreach ($psychologicals as $psychological) {
                    echo "  <tr>
                                <th>" . $psychological->name . "</th>
                                <th hidden><input class='easyui-textbox' value='" . $psychological->id . "' name='requirement_id[]'></th>
                                <th width='20'><input class='easyui-textbox' readonly value='" . $psychological->grade . "' name='standard_score[]'></th>
                                <th width='20'><input class='easyui-numberbox' value='0' name='final_score[]'></th>
                                <th width='200'><input class='easyui-textbox' name='remarks[]'></th>
                            </tr>";
                }
                ?>

            </table>

        </fieldset>
        <fieldset style="width:100%; border:1px solid #d0d0d0; margin-bottom: 10px; border-radius:4px; float: left;">
            <legend>Form Final Result</legend>
            <div class="fitem" style="margin-bottom: 5px;">
                <span style="width:35%; display:inline-block;">Judgement</span>
                <select style="width:60%;" name="psycological_judgement" class="easyui-combobox" panelHeight="auto">
                    <option value="PASS">PASS</option>
                    <option value="NO">NO</option>
                </select>
            </div>
        </fieldset>
    </form>
</div>

<div id="dlg_insert_interview" class="easyui-dialog" title="Add Interview" data-options="closed: true,modal:true" style="width: 800px; padding:10px; top: 20px;">
    <form id="frm_insert_interview" method="post" enctype="multipart/form-data" novalidate>
        <fieldset style="width:100%; border:1px solid #d0d0d0; margin-bottom: 10px; border-radius:4px; float: left;">
            <legend>Form Input Data</legend>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Candidate ID</span>
                <input style="width:60%;" name="id" id="int_id" readonly class="easyui-textbox">
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Candidate Name</span>
                <input style="width:60%;" name="name" id="int_name" readonly class="easyui-textbox">
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Trans Date</span>
                <input style="width:60%;" name="trans_date" id="int_date" required="" data-options="formatter:myformatter,parser:myparser,editable: false" class="easyui-datebox">
            </div>
        </fieldset>
        <fieldset style="width:100%; border:1px solid #d0d0d0; margin-bottom: 10px; border-radius:4px; float: left;">
            <legend>Form Interview</legend>
            <table style="width: 100%;">
                <?php
                echo "  <tr>
                            <th width='150'></th>
                            <th width='20'>Pasing Grade</th>
                            <th width='20'>Result</th>
                            <th width='200'>Remarks</th>
                        </tr>";
                foreach ($interviews as $interview) {
                    echo "  <tr>
                                <th>" . $interview->name . "</th>
                                <th hidden><input class='easyui-textbox' value='" . $interview->id . "' name='requirement_id[]'></th>
                                <th width='20'><input class='easyui-textbox' readonly value='" . $interview->grade . "' name='standard_score[]'></th>
                                <th width='20'><input class='easyui-numberbox' value='0' name='final_score[]'></th>
                                <th width='200'><input class='easyui-textbox' name='remarks[]'></th>
                            </tr>";
                }
                ?>

            </table>

        </fieldset>
        <fieldset style="width:100%; border:1px solid #d0d0d0; margin-bottom: 10px; border-radius:4px; float: left;">
            <legend>Form Final Result</legend>
            <div class="fitem" style="margin-bottom: 5px;">
                <span style="width:35%; display:inline-block;">Judgement</span>
                <select style="width:60%;" name="interview_judgement" class="easyui-combobox" panelHeight="auto">
                    <option value="PASS">PASS</option>
                    <option value="NO">NO</option>
                </select>
            </div>
        </fieldset>
    </form>
</div>

<div id="dlg_insert_medical" class="easyui-dialog" title="Add Medical" data-options="closed: true,modal:true" style="width: 800px; padding:10px; top: 20px;">
    <form id="frm_insert_medical" method="post" enctype="multipart/form-data" novalidate>
        <fieldset style="width:100%; border:1px solid #d0d0d0; margin-bottom: 10px; border-radius:4px; float: left;">
            <legend>Form Input Data</legend>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Candidate ID</span>
                <input style="width:60%;" name="id" id="med_id" readonly class="easyui-textbox">
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Candidate Name</span>
                <input style="width:60%;" name="name" id="med_name" readonly class="easyui-textbox">
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Trans Date</span>
                <input style="width:60%;" name="trans_date" id="med_date" required="" data-options="formatter:myformatter,parser:myparser,editable: false" class="easyui-datebox">
            </div>
        </fieldset>
        <fieldset style="width:100%; border:1px solid #d0d0d0; margin-bottom: 10px; border-radius:4px; float: left;">
            <legend>Form Medical</legend>
            <table style="width: 100%;">
                <?php
                echo "  <tr>
                            <th width='150'></th>
                            <th width='20'>Pasing Grade</th>
                            <th width='20'>Result</th>
                            <th width='200'>Remarks</th>
                        </tr>";
                foreach ($medicals as $medical) {
                    echo "  <tr>
                                <th>" . $medical->name . "</th>
                                <th hidden><input class='easyui-textbox' value='" . $medical->id . "' name='requirement_id[]'></th>
                                <th width='20'><input class='easyui-textbox' readonly value='" . $medical->grade . "' name='standard_score[]'></th>
                                <th width='20'><input class='easyui-numberbox' value='0' name='final_score[]'></th>
                                <th width='200'><input class='easyui-textbox' name='remarks[]'></th>
                            </tr>";
                }
                ?>

            </table>

        </fieldset>
        <fieldset style="width:100%; border:1px solid #d0d0d0; margin-bottom: 10px; border-radius:4px; float: left;">
            <legend>Form Final Result</legend>
            <div class="fitem" style="margin-bottom: 5px;">
                <span style="width:35%; display:inline-block;">Judgement</span>
                <select style="width:60%;" name="medical_judgement" class="easyui-combobox" panelHeight="auto">
                    <option value="PASS">PASS</option>
                    <option value="NO">NO</option>
                </select>
            </div>
        </fieldset>
    </form>
</div>

<div id="dlg_insert_skill" class="easyui-dialog" title="Add Skill" data-options="closed: true,modal:true" style="width: 800px; padding:10px; top: 20px;">
    <form id="frm_insert_skill" method="post" enctype="multipart/form-data" novalidate>
        <fieldset style="width:100%; border:1px solid #d0d0d0; margin-bottom: 10px; border-radius:4px; float: left;">
            <legend>Form Input Data</legend>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Candidate ID</span>
                <input style="width:60%;" name="id" id="skill_id" readonly class="easyui-textbox">
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Candidate Name</span>
                <input style="width:60%;" name="name" id="skill_name" readonly class="easyui-textbox">
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Trans Date</span>
                <input style="width:60%;" name="trans_date" id="skill_date" required="" data-options="formatter:myformatter,parser:myparser,editable: false" class="easyui-datebox">
            </div>
        </fieldset>
        <fieldset style="width:100%; border:1px solid #d0d0d0; margin-bottom: 10px; border-radius:4px; float: left;">
            <legend>Form Skill</legend>
            <table style="width: 100%;">
                <?php
                echo "  <tr>
                            <th width='150'></th>
                            <th width='20'>Pasing Grade</th>
                            <th width='20'>Result</th>
                            <th width='200'>Remarks</th>
                        </tr>";
                foreach ($skills as $skill) {
                    echo "  <tr>
                                <th>" . $skill->name . "</th>
                                <th hidden><input class='easyui-textbox' value='" . $skill->id . "' name='requirement_id[]'></th>
                                <th width='20'><input class='easyui-textbox' readonly value='" . $skill->grade . "' name='standard_score[]'></th>
                                <th width='20'><input class='easyui-numberbox' value='0' name='final_score[]'></th>
                                <th width='200'><input class='easyui-textbox' name='remarks[]'></th>
                            </tr>";
                }
                ?>

            </table>

        </fieldset>
        <fieldset style="width:100%; border:1px solid #d0d0d0; margin-bottom: 10px; border-radius:4px; float: left;">
            <legend>Form Final Result</legend>
            <div class="fitem" style="margin-bottom: 5px;">
                <span style="width:35%; display:inline-block;">Judgement</span>
                <select style="width:60%;" name="skill_judgement" class="easyui-combobox" panelHeight="auto">
                    <option value="PASS">PASS</option>
                    <option value="NO">NO</option>
                </select>
            </div>
        </fieldset>
    </form>
</div>

<!-- PDF -->
<iframe id="printout" src="<?= base_url('requitment/candidates/print') ?>" style="width: 100%;" hidden></iframe>

<script>
    //ADD DATA
    function add() {
        $('#dlg_insert').dialog('open');
        url_save = '<?= base_url('requitment/candidates/create') ?>';
        $('#frm_insert').form('clear');
    }

    function addPsycological(id) {
        $.ajax({
            type: "post",
            url: '<?= base_url('requitment/candidates/reads') ?>',
            data: "id=" + id,
            dataType: "json",
            success: function(json) {
                $('#dlg_insert_psikologi').dialog('open');
                $("#psy_id").textbox('setValue', json[0].id);
                $("#psy_name").textbox('setValue', json[0].name);
                $("#psy_date").datebox('setValue', '<?= date("Y-m-d") ?>');
            }
        });
    }

    function addInterview(id) {
        $.ajax({
            type: "post",
            url: '<?= base_url('requitment/candidates/reads') ?>',
            data: "id=" + id,
            dataType: "json",
            success: function(json) {
                $('#dlg_insert_interview').dialog('open');
                $("#int_id").textbox('setValue', json[0].id);
                $("#int_name").textbox('setValue', json[0].name);
                $("#int_date").datebox('setValue', '<?= date("Y-m-d") ?>');
            }
        });
    }

    function addMedical(id) {
        $.ajax({
            type: "post",
            url: '<?= base_url('requitment/candidates/reads') ?>',
            data: "id=" + id,
            dataType: "json",
            success: function(json) {
                $('#dlg_insert_medical').dialog('open');
                $("#med_id").textbox('setValue', json[0].id);
                $("#med_name").textbox('setValue', json[0].name);
                $("#med_date").datebox('setValue', '<?= date("Y-m-d") ?>');
            }
        });
    }

    function addSkill(id) {
        $.ajax({
            type: "post",
            url: '<?= base_url('requitment/candidates/reads') ?>',
            data: "id=" + id,
            dataType: "json",
            success: function(json) {
                $('#dlg_insert_skill').dialog('open');
                $("#skill_id").textbox('setValue', json[0].id);
                $("#skill_name").textbox('setValue', json[0].name);
                $("#skill_date").datebox('setValue', '<?= date("Y-m-d") ?>');
            }
        });
    }

    //EDIT DATA
    function update() {
        var row = $('#dg').datagrid('getSelected');
        if (row) {
            $('#dlg_insert').dialog('open');
            $('#frm_insert').form('load', row);
            url_save = '<?= base_url('requitment/candidates/update') ?>?id=' + btoa(row.id);
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
                            url: '<?= base_url('requitment/candidates/delete') ?>',
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

    function closing() {
        var filter_request = $("#filter_request").combogrid('getValue');

        if (filter_request != '') {
            $.messager.confirm('Warning', 'Are you sure you want to closing this Request?', function(r) {
                if (r) {
                    $.ajax({
                        method: 'post',
                        url: '<?= base_url('requitment/candidates/closing') ?>',
                        data: {
                            id: filter_request
                        },
                        success: function(result) {
                            var result = eval('(' + result + ')');
                            if (result.theme == "success") {
                                toastr.success(result.message, result.title);
                                $('#dlg_insert').dialog('close');
                            } else {
                                toastr.error(result.message, result.title);
                            }
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
            });
        } else {
            toastr.warning("Please select request id!");
        }
    }

    //FILTER DATA
    function filter() {
        var filter_request = $("#filter_request").combogrid('getValue');
        var filter_candidate = $("#filter_candidate").textbox('getValue');

        var url = "?filter_request=" + filter_request +
            "&filter_candidate=" + filter_candidate;

        $('#dg').datagrid({
            url: '<?= base_url('requitment/candidates/datatables') ?>' + url
        });

        $("#printout").contents().find('html').html("<center><br><br><br><b style='font-size:20px;'>Please Wait...</b></center>");
        $("#printout").attr('src', '<?= base_url('requitment/candidates/print') ?>' + url);
    }

    //PRINT PDF
    function pdf() {
        $("#printout").get(0).contentWindow.print();
    }
    //PRINT EXCEL
    function excel() {
        var filter_request = $("#filter_request").combogrid('getValue');
        var filter_candidate = $("#filter_candidate").textbox('getValue');

        var url = "?filter_request=" + filter_request +
            "&filter_candidate=" + filter_candidate;

        window.location.assign('<?= base_url('requitment/candidates/print/excel') ?>' + url);
    }
    //RELOAD
    function reload() {
        window.location.reload();
    }

    $(function() {
        //SETTING DATAGRID EASYUI
        $('#dg').datagrid({
            url: '<?= base_url('requitment/candidates/datatables') ?>',
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
                                $('#dlg_insert').dialog('close');
                            } else {
                                toastr.error(result.message, result.title);
                            }

                            $('#dg').datagrid('reload');
                        }
                    });
                }
            }]
        });

        $('#dlg_insert_psikologi').dialog({
            buttons: [{
                text: 'Save',
                iconCls: 'icon-ok',
                handler: function() {
                    $('#frm_insert_psikologi').form('submit', {
                        url: '<?= base_url('requitment/candidates/createPsychologicals') ?>',
                        onSubmit: function() {
                            return $(this).form('validate');
                        },
                        success: function(result) {
                            var result = eval('(' + result + ')');

                            if (result.theme == "success") {
                                toastr.success(result.message, result.title);
                                $('#dlg_insert_psikologi').dialog('close');
                            } else {
                                toastr.error(result.message, result.title);
                            }

                            $('#dg').datagrid('reload');
                        }
                    });
                }
            }]
        });

        $('#dlg_insert_interview').dialog({
            buttons: [{
                text: 'Save',
                iconCls: 'icon-ok',
                handler: function() {
                    $('#frm_insert_interview').form('submit', {
                        url: '<?= base_url('requitment/candidates/createInterviews') ?>',
                        onSubmit: function() {
                            return $(this).form('validate');
                        },
                        success: function(result) {
                            var result = eval('(' + result + ')');

                            if (result.theme == "success") {
                                toastr.success(result.message, result.title);
                                $('#dlg_insert_interview').dialog('close');
                            } else {
                                toastr.error(result.message, result.title);
                            }

                            $('#dg').datagrid('reload');
                        }
                    });
                }
            }]
        });

        $('#dlg_insert_medical').dialog({
            buttons: [{
                text: 'Save',
                iconCls: 'icon-ok',
                handler: function() {
                    $('#frm_insert_medical').form('submit', {
                        url: '<?= base_url('requitment/candidates/createMedicals') ?>',
                        onSubmit: function() {
                            return $(this).form('validate');
                        },
                        success: function(result) {
                            var result = eval('(' + result + ')');

                            if (result.theme == "success") {
                                toastr.success(result.message, result.title);
                                $('#dlg_insert_medical').dialog('close');
                            } else {
                                toastr.error(result.message, result.title);
                            }

                            $('#dg').datagrid('reload');
                        }
                    });
                }
            }]
        });

        $('#dlg_insert_skill').dialog({
            buttons: [{
                text: 'Save',
                iconCls: 'icon-ok',
                handler: function() {
                    $('#frm_insert_skill').form('submit', {
                        url: '<?= base_url('requitment/candidates/createSkills') ?>',
                        onSubmit: function() {
                            return $(this).form('validate');
                        },
                        success: function(result) {
                            var result = eval('(' + result + ')');

                            if (result.theme == "success") {
                                toastr.success(result.message, result.title);
                                $('#dlg_insert_skill').dialog('close');
                            } else {
                                toastr.error(result.message, result.title);
                            }

                            $('#dg').datagrid('reload');
                        }
                    });
                }
            }]
        });

        $('#filter_request').combogrid({
            url: '<?= base_url('requitment/requests/readRequests') ?>',
            panelWidth: 700,
            idField: 'id',
            textField: 'id',
            mode: 'remote',
            fitColumns: true,
            prompt: 'Choose All',
            columns: [
                [{
                    field: 'id',
                    title: 'Request ID',
                    width: 100
                }, {
                    field: 'request_by',
                    title: 'Request Name',
                    width: 150
                }, {
                    field: 'request_date',
                    title: 'Request Date',
                    width: 100
                }, {
                    field: 'departement_name',
                    title: 'Departement',
                    width: 150
                }, {
                    field: 'qty',
                    title: 'Qty',
                    width: 80
                }]
            ],
        });

        $('#request_id').combogrid({
            url: '<?= base_url('requitment/requests/readRequests') ?>',
            panelWidth: 700,
            idField: 'id',
            textField: 'id',
            mode: 'remote',
            fitColumns: true,
            prompt: 'Choose Request ID',
            columns: [
                [{
                    field: 'id',
                    title: 'Request ID',
                    width: 100
                }, {
                    field: 'request_by',
                    title: 'Request Name',
                    width: 150
                }, {
                    field: 'request_date',
                    title: 'Request Date',
                    width: 100
                }, {
                    field: 'departement_name',
                    title: 'Departement',
                    width: 150
                }, {
                    field: 'qty',
                    title: 'Qty',
                    width: 80
                }]
            ],
            onSelect: function(val, row) {
                $('#division_id').combobox({
                    url: '<?php echo base_url('employee/divisions/reads'); ?>',
                    valueField: 'id',
                    textField: 'name',
                    prompt: 'Choose Division',
                    onLoadSuccess: function() {
                        $("#division_id").combobox('setValue', row.division_id);
                    },
                    onSelect: function(division) {
                        $('#departement_id').combobox({
                            url: '<?php echo base_url('employee/departements/reads'); ?>?division_id=' + division.id,
                            valueField: 'id',
                            textField: 'name',
                            prompt: 'Choose Departement',
                            onLoadSuccess: function() {
                                $("#departement_id").combobox('setValue', row.departement_id);
                            },
                            onSelect: function(departement) {
                                $('#departement_sub_id').combobox({
                                    url: '<?php echo base_url('employee/departement_subs/reads'); ?>?division_id=' + division.id + '&departement_id=' + departement.id,
                                    valueField: 'id',
                                    textField: 'name',
                                    prompt: 'Choose Departement Sub',
                                    onLoadSuccess: function() {
                                        $("#departement_sub_id").combobox('setValue', row.departement_sub_id);
                                    },
                                });
                            }
                        });
                    }
                });
            }
        });

        $('#marital_id').combogrid({
            url: '<?= base_url('employee/maritals/reads') ?>',
            panelWidth: 300,
            idField: 'id',
            textField: 'name',
            mode: 'remote',
            fitColumns: true,
            prompt: 'Choose Marital Status',
            columns: [
                [{
                    field: 'number',
                    title: 'Code',
                    width: 80
                }, {
                    field: 'name',
                    title: 'Marital Name',
                    width: 200
                }]
            ],
        });

        $('#religion_id').combobox({
            url: '<?php echo base_url('employee/religions/reads'); ?>',
            valueField: 'id',
            textField: 'name',
            prompt: 'Choose Religion'
        });
    });

    function FormatterPsychological(val, row) {
        var id = "addPsycological('" + row.id + "')";
        if (val == "0") {
            return '<a class="button-red" href="#" onclick="' + id + '" style="text-decoration: none; width:100%;"><i class="fa fa-plus"></i> Start</a>';
        } else {
            if (row.psycological_judgement == "NO") {
                return '<b style="color:red;">' + row.psycological_judgement + ' : ' + val + '</b>';
            } else {
                return '<b style="color:#53D636;">' + row.psycological_judgement + ' : ' + val + '</b>';
            }
        }
    }

    function FormatterInterview(val, row) {
        var id = "addInterview('" + row.id + "')";
        if (val == "0") {
            return '<a class="button-red" href="#" onclick="' + id + '" style="text-decoration: none; width:100%;"><i class="fa fa-plus"></i> Start</a>';
        } else {
            if (row.interview_judgement == "NO") {
                return '<b style="color:red;">' + row.interview_judgement + ' : ' + val + '</b>';
            } else {
                return '<b style="color:#53D636;">' + row.interview_judgement + ' : ' + val + '</b>';
            }
        }
    }

    function FormatterMedical(val, row) {
        var id = "addMedical('" + row.id + "')";
        if (val == "0") {
            return '<a class="button-red" href="#" onclick="' + id + '" style="text-decoration: none; width:100%;"><i class="fa fa-plus"></i> Start</a>';
        } else {
            if (row.medical_judgement == "NO") {
                return '<b style="color:red;">' + row.medical_judgement + ' : ' + val + '</b>';
            } else {
                return '<b style="color:#53D636;">' + row.medical_judgement + ' : ' + val + '</b>';
            }
        }
    }

    function FormatterSkill(val, row) {
        var id = "addSkill('" + row.id + "')";
        if (val == "0") {
            return '<a class="button-red" href="#" onclick="' + id + '" style="text-decoration: none; width:100%;"><i class="fa fa-plus"></i> Start</a>';
        } else {
            if (row.skill_judgement == "NO") {
                return '<b style="color:red;">' + row.skill_judgement + ' : ' + val + '</b>';
            } else {
                return '<b style="color:#53D636;">' + row.skill_judgement + ' : ' + val + '</b>';
            }
        }
    }

    function FormatterStatus(val) {
        if (val == 0) {
            return '<b style="color:orange;"><i class="fa fa-rotate-left"></i> PROCESS</b>';
        } else if (val == 1) {
            return '<b style="color:#53D636;"><i class="fa fa-check"></i> DONE</b>';
        } else {
            return '<b style="color:red;"><i class="fa fa-times"></i> FAIL</b>';
        }
    };

    //CELLSTYLE STATUS
    function statusStyler(value, row, index) {
        if (value == "" || value == null) {
            return 'background: #53D636; color:white;';
        } else {
            return 'background: #FF5F5F; color:white;';
        }
    }

    //FORMATTER STATUS
    function statusFormatter(value) {
        if (value == "" || value == null) {
            return 'Approved';
        } else {
            return 'Checked';
        }
    };

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