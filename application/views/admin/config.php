<!-- UPDATE DATA -->
<div id="p" class="easyui-panel" title="Configuration" style="width:100%; height:600px; padding:10px; background:#fafafa;" data-options="collapsible:true, maximizable:true">
    <form id="frm_insert" method="post" enctype="multipart/form-data" novalidate>
        <div style="width: 100%; float: left;">
            <fieldset style="width:35%; border:1px solid #d0d0d0; margin-bottom: 10px; border-radius:4px; float: left;">
                <legend><b>Form Data</b></legend>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">ID</span>
                    <input style="width:60%;" name="number" value="<?= $config->number ?>" readonly class="easyui-textbox">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Name</span>
                    <input style="width:60%;" name="name" value="<?= $config->name ?>" class="easyui-textbox">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Description</span>
                    <input style="width:60%;" name="description" value="<?= $config->description ?>" class="easyui-textbox">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Address</span>
                    <input style="width:60%;" name="address" value="<?= $config->address ?>" class="easyui-textbox">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">NPWP</span>
                    <input style="width:60%;" name="npwp" value="<?= $config->npwp ?>" class="easyui-textbox">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Latitude</span>
                    <input style="width:60%;" name="latitude" value="<?= $config->latitude ?>" class="easyui-textbox">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Longitude</span>
                    <input style="width:60%;" name="longitude" value="<?= $config->longitude ?>" class="easyui-textbox">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Logo</span>
                    <input style="width:60%;" name="logo" class="easyui-filebox">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Favicon</span>
                    <input style="width:60%;" name="favicon" class="easyui-filebox">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Login</span>
                    <input style="width:60%;" name="image" class="easyui-filebox">
                </div>
                <br>
                <center>
                    <a class="easyui-linkbutton c6" onclick="saved()" data-options="iconCls:'icon-ok'">Save Changes</a>
                </center>
                <br>
            </fieldset>
        </div>
    </form>
</div>

<script>
    //Add Data
    function saved() {
        $('#frm_insert').form('submit', {
            url: '<?= base_url('admin/config/update') ?>',
            method: 'POST',
            onSubmit: function() {
                return $(this).form('validate');
            },
            success: function(result) {
                var result = eval('(' + result + ')');
                if (result.theme == "success") {
                    toastr.success(result.message, result.title);
                } else {
                    toastr.error(result.message, result.title);
                }
            }
        });
    }

    //Generate QR Code
    function generateQrCode() {
        $.ajax({
            type: "post",
            url: "<?= base_url('admin/config/generateQrCode') ?>",
            dataType: "json",
            success: function(response) {
                Swal.fire({
                    title: response.message,
                    icon: response.theme,
                    confirmButtonText: 'Ok',
                    allowOutsideClick: false,
                }).then((result) => {
                    if (result.isConfirmed) {
                        window.location.reload();
                    }
                });
            }
        });
    }
</script>