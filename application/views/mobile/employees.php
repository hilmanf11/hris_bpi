<div class="p-4">
    <h1>Employees</h1>

    <div class="card mt-4">
        <div class="card-body">
            <h5><span class="badge bg-success">Basic Information</span></h5>
            <table class="table tabl-sm" style="font-size: 12px;">
                <tr>
                    <th>Employee ID</th>
                    <td style="text-align: right;"><?= $employee->number ?></td>
                </tr>
                <tr>
                    <th>Fullname</th>
                    <td style="text-align: right;"><?= $employee->name ?></td>
                </tr>
                <tr>
                    <th>Sign Date</th>
                    <td style="text-align: right;"><?= date("d F Y", strtotime($employee->date_sign)) ?></td>
                </tr>
                <tr>
                    <th>Fit For Services</th>
                    <td style="text-align: right;"><?= $service ?></td>
                </tr>
                <tr>
                    <th>Contract Expired</th>
                    <td style="text-align: right;"><?= $employee->contract_name ?></td>
                </tr>
                <tr>
                    <th>Division</th>
                    <td style="text-align: right;"><?= $employee->division_name ?></td>
                </tr>
                <tr>
                    <th>Departement</th>
                    <td style="text-align: right;"><?= $employee->departement_name ?></td>
                </tr>
                <tr>
                    <th>Sub Departement</th>
                    <td style="text-align: right;"><?= $employee->departement_sub_name ?></td>
                </tr>
                <tr>
                    <th>Position</th>
                    <td style="text-align: right;"><?= $employee->position_name ?></td>
                </tr>
                <tr>
                    <th>Group</th>
                    <td style="text-align: right;"><?= $employee->group_name ?></td>
                </tr>
                <tr>
                    <th>Source</th>
                    <td style="text-align: right;"><?= @$employee->source_name ?></td>
                </tr>
            </table>
        </div>
    </div>
    <div class="card mt-2">
        <div class="card-body">
            <h5><span class="badge bg-success">Personal Data</span></h5>
            <table class="table tabl-sm" style="font-size: 12px;">
                <tr>
                    <th>Address</th>
                    <td style="text-align: right;"><?= @$employee->address ?></td>
                </tr>
                <tr>
                    <th>Place Of Birth</th>
                    <td style="text-align: right;"><?= date("d F Y", strtotime($employee->birthday)) ?></td>
                </tr>
                <tr>
                    <th>Gender</th>
                    <td style="text-align: right;"><?= @$employee->gender ?></td>
                </tr>
                <tr>
                    <th>Blood Type</th>
                    <td style="text-align: right;"><?= @$employee->blood ?></td>
                </tr>
                <tr>
                    <th>Religion</th>
                    <td style="text-align: right;"><?= @$employee->religion_name ?></td>
                </tr>
                <tr>
                    <th>Marital</th>
                    <td style="text-align: right;"><?= @$employee->marital_name ?></td>
                </tr>
                <tr>
                    <th>National ID</th>
                    <td style="text-align: right;"><?= @$employee->national_id ?></td>
                </tr>
                <tr>
                    <th>Tax No</th>
                    <td style="text-align: right;"><?= @$employee->tax_id ?></td>
                </tr>
                <tr>
                    <th>Jamsostek</th>
                    <td style="text-align: right;"><?= @$employee->jamsostek ?></td>
                </tr>
                <tr>
                    <th>JKN</th>
                    <td style="text-align: right;"><?= @$employee->jkn ?></td>
                </tr>
            </table>
        </div>
    </div>

    <div class="card mt-2">
        <div class="card-body">
            <h5><span class="badge bg-success">Contact Information</span></h5>
            <table class="table tabl-sm" style="font-size: 12px;">
                <tr>
                    <th>Telp No</th>
                    <td style="text-align: right;"><?= @$employee->telphone ?></td>
                </tr>
                <tr>
                    <th>Phone No</th>
                    <td style="text-align: right;"><?= @$employee->mobile_phone ?></td>
                </tr>
                <tr>
                    <th>Emergency No</th>
                    <td style="text-align: right;"><?= @$employee->emergency_no ?></td>
                </tr>
                <tr>
                    <th>Email</th>
                    <td style="text-align: right;"><?= @$employee->email ?></td>
                </tr>
                <tr>
                    <th>Driving Lisence</th>
                    <td style="text-align: right;"><?= @$employee->driving_no ?></td>
                </tr>
                <tr>
                    <th>STNK No</th>
                    <td style="text-align: right;"><?= @$employee->stnk_no ?></td>
                </tr>
                <tr>
                    <th>Google Maps</th>
                    <td style="text-align: right;"><?= @$employee->maps ?></td>
                </tr>
            </table>
        </div>
    </div>

    <div class="card mt-2">
        <div class="card-body">
            <h5><span class="badge bg-success">Bank Account</span></h5>
            <table class="table tabl-sm" style="font-size: 12px;">
                <tr>
                    <th>Bank No</th>
                    <td style="text-align: right;"><?= @$employee->bank_no ?></td>
                </tr>
                <tr>
                    <th>Bank Name</th>
                    <td style="text-align: right;"><?= @$employee->bank_name ?></td>
                </tr>
                <tr>
                    <th>Bank Branch</th>
                    <td style="text-align: right;"><?= @$employee->bank_branch ?></td>
                </tr>
            </table>
        </div>
    </div>
</div>