<div class="p-4">
    <h1>Permission</h1>

    <form method="post" id="form-insert" class="mt-4">
        <div class="mb-3 form-floating" hidden>
            <input type="text" id="employee_id" class="form-control" value="<?= $employee->id ?>">
            <label for="permit_date">Employee ID</label>
        </div>
        <div class="mb-3 form-floating" hidden>
            <input type="date" id="trans_date" class="form-control" value="<?= date("Y-m-d") ?>">
            <label for="permit_date">Trans Date</label>
        </div>
        <div class="mb-3 form-floating">
            <select id="permit_type_id" onchange="permit_type(this.value)" class="form-control">
                <option value="">Choose Permit Type</option>
                <?php
                foreach ($permit_types as $permit_type) {
                    echo '<option value="' . $permit_type->id . '">' . $permit_type->name . '</option>';
                }
                ?>
            </select>
            <label for="type">Permit Type</label>
        </div>
        <div class="mb-3 form-floating">
            <select id="reason_id" class="form-control">

            </select>
            <label for="reason">Reason</label>
        </div>
        <div class="mb-3 form-floating">
            <input type="date" id="permit_date" class="form-control" value="<?= date("Y-m-d") ?>">
            <label for="permit_date">Permit Date</label>
        </div>
        <div class="mb-3 form-floating">
            <textarea type="text" id="note" class="form-control" rows="3"></textarea>
            <label for="note">Notes</label>
        </div>
        <div class="mb-3 form-floating" hidden>
            <input type="number" id="leave" class="form-control">
            <label for="leave">Leave</label>
        </div>
        <div class="mb-3 form-floating" hidden>
            <input type="number" id="duration" class="form-control" value="1">
            <label for="duration">Duration</label>
        </div>
        <div class="alert alert-info mt-4" id="alertLeave" role="alert">
            You have <b id="leavePermit"></b> days left Permit
        </div>
        <button type="button" onclick="save()" class="btn btn btn-primary w-100 mb-3"><i class="fa fa-check"></i> Submit Submission </button>
        <a class="btn btn btn-warning w-100 mb-3" href="<?= base_url("mobile/permissions/lists/" . $api_key) ?>"><i class="fa fa-eye"></i> View Permit </a>
    </form>
</div>

<script>
    $(function() {
        $("#alertLeave").hide();
    });

    function permit_type(val) {
        var employee_id = $("#employee_id").val();
        $.ajax({
            type: "post",
            url: "<?= base_url('mobile/permissions/reason/') ?>" + val,
            dataType: "html",
            success: function(reason) {
                $("#reason_id").html(reason);

                $.ajax({
                    type: "post",
                    url: "<?= base_url('attandance/permits/readLeave') ?>",
                    data: "employee_id=" + employee_id + "&permit_type_id=" + val,
                    dataType: "json",
                    success: function(leave) {
                        $("#leave").val(leave.total);
                        $("#leavePermit").html(leave.total);
                        $("#alertLeave").show();
                    }
                });
            }
        });
    }

    function save() {
        var employee_id = $("#employee_id").val();
        var trans_date = $("#trans_date").val();
        var permit_type_id = $("#permit_type_id").val();
        var reason_id = $("#reason_id").val();
        var permit_date = $("#permit_date").val();
        var duration = $("#duration").val();
        var leave = $("#leave").val();
        var note = $("#note").val();

        if (permit_type_id == "" || reason_id == "" || note == "") {
            Swal.fire({
                title: "Please complete your data",
                icon: "error",
                confirmButtonText: 'Ok',
                allowOutsideClick: false,
            });
        } else {
            $.ajax({
                type: "post",
                url: "<?= base_url("mobile/permissions/create") ?>",
                data: "employee_id=" + employee_id +
                    "&trans_date=" + trans_date +
                    "&permit_type_id=" + permit_type_id +
                    "&reason_id=" + reason_id +
                    "&date_from=" + permit_date +
                    "&date_to=" + permit_date +
                    "&duration=" + duration +
                    "&leave=" + leave +
                    "&attachment=" +
                    "&note=" + note,
                dataType: "json",
                success: function(response) {
                    if (response.theme == "success") {
                        Swal.fire({
                            title: "Permit request has been submitted",
                            icon: "success",
                            confirmButtonText: 'Ok',
                            allowOutsideClick: false,
                        }).then((result) => {
                            $('#form-insert').trigger('reset');
                            $("#alertLeave").hide();
                        });
                    } else {
                        Swal.fire({
                            title: "Permit Request failed",
                            icon: "error",
                            confirmButtonText: 'Ok',
                            allowOutsideClick: false,
                        });
                    }
                }
            });
        }
    }
</script>