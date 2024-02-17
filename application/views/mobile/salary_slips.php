<div class="p-4">
    <h1>Salary Slip</h1>

    <form method="post" class="mt-4">
        <div class="mb-3 form-floating" hidden>
            <input type="text" id="employee_id" class="form-control" value="<?= $employee->id ?>">
            <label for="employee_id">Employee ID</label>
        </div>
        <div class="input-group mb-3">
            <span class="input-group-text" id="f-calendar-1"><i class="fa fa-calendar"></i></span>
            <select onchange="periodend(this.value)" class="form-control" aria-describedby="f-calendar-1">
                <option value="">Select Cut Off</option>
                <?php
                foreach ($periods as $period) {
                    echo '<option value="' . $period['start'] . '/' . $period['finish'] . '">' . date("F Y", strtotime($period['finish'])) . '</option>';
                }
                ?>
            </select>
        </div>
    </form>

    <div id="results">

    </div>
</div>

<script>
    function periodend(period) {
        if (period == "") {
            $("#results").html("");
        } else {
            var employee_id = $("#employee_id").val();
            $.ajax({
                type: "post",
                url: "<?= base_url("mobile/salary_slips/datatables") ?>",
                data: "employee_id=" + employee_id +
                    "&period_end=" + window.btoa(period),
                dataType: "html",
                success: function(response) {
                    $("#results").html(response);
                }
            });
        }
    }
</script>