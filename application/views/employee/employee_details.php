<style>
    .button-gray {
        display: inline-block;
        padding: 5px;
        cursor: pointer;
        text-align: center;
        text-decoration: none;
        outline: none;
        color: #fff;
        background-color: #C2C2C2;
        border: none;
        border-radius: 5px;
    }

    .button-gray:hover {
        background-color: #B9B9B9;
    }
</style>

<?php
if ($employee->status == 0) {
    $status = '<span class="button-green" style="width: 100%; font-size: 20px !important;">ACTIVE</span>';
} else {
    $status = '<span class="button-red" style="width: 100%; font-size: 20px !important;">NOT ACTIVE</span>';
}

if(!empty($resignation->employee_id)){
    if($resignation->status_resign == "ON PROCEDURE"){
        $status_resign = '<span class="button-green" style="width: 100%; font-size: 20px !important;">'.$resignation->status_resign.'</span>';
    } else {
        $status_resign = '<span class="button-red" style="width: 100%; font-size: 20px !important;">'.$resignation->status_resign.'</span>';
    }
}else{
    $status_resign = '<span class="button-gray" style="width: 100%; font-size: 20px !important;">NO DATA RESIGN</span>';
}

if ($employee->image_profile == "" || $employee->image_profile == null) {
    $image = "<img src='" . base_url('assets/image/users/default.png') . "' style='width: 100%;'>";
} else {
    $image = "<img src='" . $employee->image_profile . "' style='width: 100%;'>";
}
?>

<div class="easyui-panel" title="Employee Detail" style="width:99.5%;padding:10px;background:#fafafa;" data-options="collapsible:true">
    <div style="width: 100%; float: left;">
        <fieldset style="width:20%; border:1px solid #d0d0d0; margin-bottom: 10px; border-radius:4px; float: left;">
            <legend><b>Foto Profile</b></legend>
            <?= $image ?>
            <?= $status ?>
            <br>
            <?= $status_resign ?>
            <br>
            <a class="button-red" href="<?= base_url('employee/employees/index/' . $id_menu) ?>" style="width: 100%; font-size: 20px !important;">BACK</a>
        </fieldset>
        <fieldset style="width:75%; padding:11px; border:1px solid #d0d0d0; margin-bottom: 10px; border-radius:4px; float: left;">
            <legend><b>Personal Information</b></legend>
            <div style="width: 50%; float: left;">
                <h1 style="font-size: 40px !important; margin:0; padding:0;"><?= $employee->name ?></h1>
                <span style="width:60%; font-size: 20px !important;"><?= $employee->number ?></span>
                <br><br>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Candidate ID</span>
                    <b style="width:60%;"><?= $employee->candidate_id ?></b>
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Nickname</span>
                    <b style="width:60%;"><?= $employee->nickname ?></b>
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Email</span>
                    <b style="width:60%;"><?= $employee->email ?></b>
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Phone No</span>
                    <b style="width:60%;"><?= $employee->mobile_phone ?></b>
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Division</span>
                    <b style="width:60%;"><?= $division->name ?></b>
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Departement</span>
                    <b style="width:60%;"><?= $departement->name ?></b>
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Departement Sub</span>
                    <b style="width:60%;"><?= $departement_sub->name ?></b>
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Employee Type</span>
                    <b style="width:60%;"><?= @$contract->name ?></b>
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Position</span>
                    <b style="width:60%;"><?= @$position->name ?></b>
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Group</span>
                    <b style="width:60%;"><?= @$group->name ?></b>
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Sign In</span>
                    <b style="width:60%;"><?= $employee->date_sign ?></b>
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Fit of Service</span>
                    <b style="width:60%;"><?= $service ?></b>
                </div>
            </div>
            <div style="width: 50%; float: left;">
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Address</span>
                    <b style="width:60%;"><?= $employee->address ?></b>
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Place Of Birth</span>
                    <b style="width:60%;"><?= $employee->place_birth ?></b>
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Birthday</span>
                    <b style="width:60%;"><?= $employee->birthday ?></b>
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Gender</span>
                    <b style="width:60%;"><?= $employee->gender ?></b>
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Blood Type</span>
                    <b style="width:60%;"><?= $employee->blood ?></b>
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Religion</span>
                    <b style="width:60%;"><?= @$religion->name ?></b>
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Marital Status</span>
                    <b style="width:60%;"><?= @$marital->name ?></b>
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">National ID</span>
                    <b style="width:60%;"><?= $employee->national_id ?></b>
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Tax No</span>
                    <b style="width:60%;"><?= $employee->tax_id ?></b>
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">BPJS TK</span>
                    <b style="width:60%;"><?= $employee->jamsostek ?></b>
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">BPJS TK Date</span>
                    <b style="width:60%;"><?= $employee->jamsostek_date ?></b>
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">JKN</span>
                    <b style="width:60%;"><?= $employee->jkn ?></b>
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">JKN Date</span>
                    <b style="width:60%;"><?= $employee->jkn_date ?></b>
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Number of Family (JKN)</span>
                    <b style="width:60%;"><?= $employee->jkn_family ?></b>
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Emergency No</span>
                    <b style="width:60%;"><?= $employee->emergency_no ?></b>
                </div>

                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Driving Licence</span>
                    <b style="width:60%;"><?= $employee->driving_no ?></b>
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">STNK No</span>
                    <b style="width:60%;"><?= $employee->stnk_no ?></b>
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Google Maps</span>
                    <b style="width:60%;"><?= $employee->maps ?></b>
                </div>
            </div>
        </fieldset>
        <div class="easyui-tabs" style="width:100%; float: left;">
            <div title="Family">
                <table id="dgFamily" class="easyui-datagrid" style="width:100%;">
                    <thead>
                        <tr>
                            <th data-options="field:'name',width:150,halign:'center'">Family Name</th>
                            <th data-options="field:'place',width:100,halign:'center'">Place Birth</th>
                            <th data-options="field:'birthday',width:100,halign:'center'">Birthday</th>
                            <th data-options="field:'relation',width:100,halign:'center'">Relation</th>
                            <th data-options="field:'profesion',width:200,halign:'center'">Profession</th>
                            <th data-options="field:'contact',width:100,halign:'center'">Contact</th>
                        </tr>
                    </thead>
                </table>
            </div>

            <!-- Education Information -->
            <div title="Education">
                <table id="dgEducation" class="easyui-datagrid" style="width:100%;">
                    <thead>
                        <tr>
                            <th data-options="field:'level',width:150,halign:'center'">Education Level</th>
                            <th data-options="field:'degree',width:100,halign:'center'">Degree</th>
                            <th data-options="field:'school',width:250,halign:'center'">School/University</th>
                            <th data-options="field:'start',width:100,halign:'center'">Start</th>
                            <th data-options="field:'end',width:100,halign:'center'">End</th>
                            <th data-options="field:'qpa',width:100,halign:'center'">QPA</th>
                        </tr>
                    </thead>
                </table>
            </div>

            <!-- Working Experience -->
            <div title="Experience">
                <table id="dgExperience" class="easyui-datagrid" style="width:100%;">
                    <thead>
                        <tr>
                            <th data-options="field:'name',width:250,halign:'center'">Company Name</th>
                            <th data-options="field:'type',width:150,halign:'center'">Industries Type</th>
                            <th data-options="field:'start',width:100,halign:'center'">Start</th>
                            <th data-options="field:'end',width:100,halign:'center'">End</th>
                            <th data-options="field:'position',width:200,halign:'center'">Position</th>
                            <th data-options="field:'salary',width:100,halign:'center'">Salary</th>
                        </tr>
                    </thead>
                </table>
            </div>

            <!-- Training Experience -->
            <div title="Training">
                <table id="dgTraining" class="easyui-datagrid" style="width:100%;">
                    <thead>
                        <tr>
                            <th data-options="field:'name',width:200,halign:'center'">Training Name</th>
                            <th data-options="field:'description',width:300,halign:'center'">Description</th>
                            <th data-options="field:'start',width:100,halign:'center'">Date</th>
                            <th data-options="field:'profesion',width:150,halign:'center'">Profession</th>
                            <th data-options="field:'contact',width:100,halign:'center'">Contact</th>
                        </tr>
                    </thead>
                </table>
            </div>

            <!-- Carrer Information -->
            <div title="Carrer">
                <table id="dgCarrer" class="easyui-datagrid" style="width:100%;">
                    <thead>
                        <tr>
                            <th data-options="field:'name',width:200,halign:'center'">Training Name</th>
                            <th data-options="field:'description',width:300,halign:'center'">Description</th>
                            <th data-options="field:'start',width:100,halign:'center'">Date</th>
                            <th data-options="field:'profesion',width:150,halign:'center'">Profession</th>
                            <th data-options="field:'contact',width:100,halign:'center'">Contact</th>
                        </tr>
                    </thead>
                </table>
            </div>
        </div>
    </div>
</div>

<script>
    $(function() {
        $('#dgFamily').datagrid({
            url: '<?= base_url('employee/employees/readFamilys/' . $employee->number) ?>'
        });

        $('#dgEducation').datagrid({
            url: '<?= base_url('employee/employees/readEducation/' . $employee->number) ?>'
        });

        $('#dgExperience').datagrid({
            url: '<?= base_url('employee/employees/readExperience/' . $employee->number) ?>'
        });

        $('#dgTraining').datagrid({
            url: '<?= base_url('employee/employees/readTraining/' . $employee->number) ?>'
        });

        $('#dgCarrer').datagrid({
            url: '<?= base_url('employee/employees/readCarrer/' . $employee->number) ?>'
        });
    });
</script>