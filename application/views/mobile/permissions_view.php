<div class="p-4">
    <h1>Permission Lists</h1>

    <div class="alert alert-info mt-4" role="alert">
        The data only shows up for this year only
    </div>

    <?php
    if (count($permits)) {
        echo '<div class="accordion mt-4" id="accordionCashCarry">';
        foreach ($permits as $permit) {
            if ($permit['status_notification'] != "") {
                $status = '<span class="badge bg-warning">Pending</span>';
            } else {
                $status = '<span class="badge bg-success">Approved</span>';
            }

            echo '  <div class="accordion-item">
                        <h2 class="accordion-header" id="' . $permit['id'] . '">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapse' . $permit['id'] . '" aria-expanded="true" aria-controls="collapse' . $permit['id'] . '">
                                ' . $status . ' &nbsp; &nbsp; ' . date("d F Y", strtotime($permit['permit_date'])) . '
                            </button>
                        </h2>
                        <div id="collapse' . $permit['id'] . '" class="accordion-collapse collapse" aria-labelledby="' . $permit['id'] . '" data-bs-parent="#accordionCashCarry">
                            <div class="accordion-body">
                                <table class="table table-sm" style="font-size: 12px;">
                                    <tr>
                                        <th width="100">Request Name</th>
                                        <td style="text-align: right;">' . $permit['request_name'] . '</td>
                                    </tr>
                                    <tr>
                                        <th width="100">Permit Type</th>
                                        <td style="text-align: right;">' . $permit['permit_type_name'] . '</td>
                                    </tr>
                                    <tr>
                                        <th width="100">Permit Reason</th>
                                        <td style="text-align: right;">' . $permit['reason_name'] . '</td>
                                    </tr>
                                    <tr>
                                        <th width="100">Note</th>
                                        <td style="text-align: right;">' . $permit['note'] . '</td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                    </div>';
        }
        echo '</div>';
    } else {
        echo '  <div class="alert alert-warning mt-4" role="alert">
                    Data Not Found
                </div>';
    }
    ?>

</div>