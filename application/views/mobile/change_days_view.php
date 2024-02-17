<div class="p-4">
    <h1>Change Days</h1>

    <div class="alert alert-info mt-4" role="alert">
        The data only shows up for this year only
    </div>

    <?php
    if (count($change_days)) {
        echo '<div class="accordion mt-4" id="accordionCashCarry">';
        foreach ($change_days as $change_day) {
            if ($change_day['status_notification'] != "") {
                $status = '<span class="badge bg-warning">Pending</span>';
            } else {
                $status = '<span class="badge bg-success">Approved</span>';
            }

            echo '  <div class="accordion-item">
                        <h2 class="accordion-header" id="' . $change_day['id'] . '">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapse' . $change_day['id'] . '" aria-expanded="true" aria-controls="collapse' . $change_day['id'] . '">
                                ' . $status . ' &nbsp; &nbsp; ' . $change_day['request_code'] . '
                            </button>
                        </h2>
                        <div id="collapse' . $change_day['id'] . '" class="accordion-collapse collapse" aria-labelledby="' . $change_day['id'] . '" data-bs-parent="#accordionCashCarry">
                            <div class="accordion-body">
                                <table class="table table-sm" style="font-size: 12px;">
                                    <tr>
                                        <th width="100">Date From</th>
                                        <td style="text-align: right;">' . date("d F Y", strtotime($change_day['start'])) . '</td>
                                    </tr>
                                    <tr>
                                        <th width="100">Date To</th>
                                        <td style="text-align: right;">' . date("d F Y", strtotime($change_day['end'])) . '</td>
                                    </tr>
                                    <tr>
                                        <th width="100">Remarks</th>
                                        <td style="text-align: right;">' . $change_day['remarks'] . '</td>
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