<div class="p-4">
    <h1>Change Days</h1>

    <form method="post" id="form-insert" class="mt-4">
        <div class="mb-3 form-floating" hidden>
            <input type="text" id="employee_id" class="form-control" value="<?= $employee->id ?>">
            <label for="employee_id">Employee ID</label>
        </div>
        <div class="mb-3 form-floating">
            <input type="date" id="start" class="form-control" value="<?= date("Y-m-d") ?>">
            <label for="start">Date From</label>
        </div>
        <div class="mb-3 form-floating">
            <input type="date" id="end" class="form-control" value="<?= date("Y-m-d") ?>">
            <label for="end">Date Replace</label>
        </div>
        <div class="mb-3 form-floating">
            <textarea type="text" id="remarks" class="form-control" rows="3"></textarea>
            <label for="note">Remarks</label>
        </div>
        <button type="button" onclick="save()" class="btn btn btn-primary w-100 mb-3"><i class="fa fa-check"></i> Submit Change Days </button>
        <a class="btn btn btn-warning w-100 mb-3" href="<?= base_url("mobile/change_days/lists/" . $api_key) ?>"><i class="fa fa-eye"></i> View Change Days </a>
    </form>
</div>

<script>
    function save() {
        var employee_id = $("#employee_id").val();
        var start = $("#start").val();
        var end = $("#end").val();
        var remarks = $("#remarks").val();

        if (start == "" || end == "" || remarks == "") {
            Swal.fire({
                text: "Please completed your data",
                icon: "error",
                confirmButtonText: 'Ok',
                allowOutsideClick: false,
            });
        } else {
            $.ajax({
                type: "post",
                url: "<?= base_url("mobile/change_days/create") ?>",
                data: "employee_id=" + employee_id +
                    "&start=" + start +
                    "&end=" + end +
                    "&remarks=" + remarks,
                dataType: "json",
                success: function(response) {
                    if (response.theme == "success") {
                        Swal.fire({
                            text: "Change Days request has been submitted",
                            icon: "success",
                            confirmButtonText: 'Ok',
                            allowOutsideClick: false,
                        }).then((result) => {
                            $('#form-insert').trigger('reset');
                        });
                    } else {
                        Swal.fire({
                            text: response.message,
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