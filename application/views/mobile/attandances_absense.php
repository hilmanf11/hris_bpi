<div class="p-4" style="margin-bottom: 50px;">
    <h1>Attandance</h1>

    <form method="post">
        <div class="input-group mb-3" hidden>
            <span class="input-group-text" id="f-maps-1"><i class="fa fa-user"></i></span>
            <input type="text" id="number" readonly class="form-control" value="<?= $number ?>" placeholder="Employee No" aria-describedby="f-maps-1">
        </div>
        <div class="input-group mb-3" hidden>
            <span class="input-group-text" id="f-maps-1"><i class="fa fa-maps"></i></span>
            <input type="text" id="latitude" readonly class="form-control" placeholder="Latitude" aria-describedby="f-maps-1">
        </div>
        <div class="input-group mb-3" hidden>
            <span class="input-group-text" id="f-maps-1"><i class="fa fa-maps"></i></span>
            <input type="text" id="longitude" readonly class="form-control" placeholder="Longitude" aria-describedby="f-maps-1">
        </div>
    </form>

    <p id="readerScanIn">
        
    </p>

    <div class="alert alert-info mt-4" role="alert">
        Scan the QR code for Check In or Check Out absences
    </div>
</div>

<script>
    Swal.fire({
        title: 'Please Wait',
        showConfirmButton: false,
        allowOutsideClick: false,
        allowEscapeKey: false,
        didOpen: () => {
            Swal.showLoading();
        },
    });

    const html5QrCode = new Html5Qrcode("readerScanIn");

    let config = {
        fps: 10,
        qrbox: 250,
        rememberLastUsedCamera: true,
        supportedScanTypes: [Html5QrcodeScanType.SCAN_TYPE_CAMERA]
    };
    Html5Qrcode.getCameras().then(devices => {
        if (devices && devices.length) {
            html5QrCode.start({
                facingMode: "environment"
            }, config, onScanSuccess);
            Swal.close();
        }
    }).catch(err => {
        Swal.close();
    });

    //Check In
    function onScanSuccess(decodedText, decodedResult) {
        html5QrCode.stop().then((ignore) => {
            var number = $("#number").val();
            var latitude = $("#latitude").val();
            var longitude = $("#longitude").val();

            if (latitude == "") {
                Swal.fire({
                    text: "Your location not found",
                    icon: "error",
                    allowOutsideClick: false,
                });
            } else {
                $.ajax({
                    type: "post",
                    url: "<?= base_url('mobile/attandances/checkin') ?>",
                    data: "token_in=" + decodedText + "&latitude=" + latitude + "&longitude=" + longitude + "&number=" + number,
                    dataType: "json",
                    success: function(response) {
                        Swal.fire({
                            text: response.message,
                            icon: response.theme,
                            allowOutsideClick: false,
                        }).then((result) => {
                            if (result.isConfirmed) {
                                history.back()
                            }
                        });
                    }
                });
            }
        }).catch((err) => {
            // Stop failed, handle it.
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