<div class="p-4" style="margin-bottom: 50px;">
    <h1>Attandance</h1>

    <div class="alert alert-primary mt-4" role="alert">
        <center>
            <p class="m-0"><b>Lists your work schedule</b></p>
            <hr>
            <?php
            foreach ($shifts as $shift) {
                echo '  <p class="m-0">' . $shift->shift_name . '</p>
                        <b class="display-6">' . $shift->start . ' </b>To<b class="display-6"> ' . $shift->end . '</b>
                        <hr>';
            }
            ?>
        </center>
    </div>

    <a class="btn btn-lg btn-success w-100 mb-5" id="check_in" href="<?= base_url('mobile/attandances/absence/' . $token) ?>"><i class="fa fa-clock-o"></i> Check In/Out</a>

    <h4>Attandance Lists</h4>
    <div class="alert alert-info mt-4" role="alert">
        Max 31 Days
    </div>
    <form method="post">
        <div class="input-group mb-3" hidden>
            <span class="input-group-text" id="f-maps-1"><i class="fa fa-user"></i></span>
            <input type="text" id="number" readonly class="form-control" value="<?= $number ?>" placeholder="Employee No" aria-describedby="f-maps-1">
        </div>
        <div class="input-group mb-3" hidden>
            <span class="input-group-text" id="f-maps-1"><i class="fa fa-user"></i></span>
            <input type="text" id="token" readonly class="form-control" value="<?= $token ?>" placeholder="Employee No" aria-describedby="f-maps-1">
        </div>
        <div class="input-group mb-3" hidden>
            <span class="input-group-text" id="f-maps-1"><i class="fa fa-maps"></i></span>
            <input type="text" id="latitude" readonly class="form-control" placeholder="Latitude" aria-describedby="f-maps-1">
        </div>
        <div class="input-group mb-3" hidden>
            <span class="input-group-text" id="f-maps-1"><i class="fa fa-maps"></i></span>
            <input type="text" id="longitude" readonly class="form-control" placeholder="Longitude" aria-describedby="f-maps-1">
        </div>
        <div class="input-group mb-3">
            <span class="input-group-text" id="f-calendar-1"><i class="fa fa-calendar"></i></span>
            <input type="date" id="date_from" class="form-control" value="<?= date("Y-m-d") ?>" placeholder="Date From" aria-describedby="f-calendar-1">
        </div>
        <div class="input-group mb-3">
            <span class="input-group-text" id="f-calendar-2"><i class="fa fa-calendar"></i></span>
            <input type="date" id="date_to" class="form-control" value="<?= date("Y-m-d") ?>" aria-describedby="f-calendar-2">
        </div>
        <a class="btn btn-sm btn-primary w-100 mb-3" onclick="filter_data()"><i class="fa fa-eye"></i> View Attandance</a>
    </form>

    <div id="results">

    </div>
</div>

<script>
    function filter_data() {
        var number = $("#number").val();
        var date_from = $("#date_from").val();
        var date_to = $("#date_to").val();

        Swal.fire({
            title: 'Please Wait',
            showConfirmButton: false,
            allowOutsideClick: false,
            allowEscapeKey: false,
            didOpen: () => {
                Swal.showLoading();
            },
        });

        $.ajax({
            type: "post",
            url: "<?= base_url('mobile/attandances/datatables') ?>",
            data: "number=" + number + "&date_from=" + date_from + "&date_to=" + date_to,
            dataType: "html",
            success: function(response) {
                Swal.close();
                $("#results").html(response);
            }
        });
    }

    $(document).ready(function() {
        $("#check_in").addClass('disabled');
        navigator.geolocation.getCurrentPosition(function(position) {
            $("#latitude").val(position.coords.latitude);
            $("#longitude").val(position.coords.longitude);
            $("#check_in").removeClass('disabled');
        }, function(e) {
            Swal.fire({
                text: "Please activate your gps",
                icon: "warning",
                allowOutsideClick: false,
            });
        }, {
            enableHighAccuracy: true
        });
    });
</script>