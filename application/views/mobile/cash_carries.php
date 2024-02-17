<div class="p-4">
    <h1>Cash Carries</h1>

    <div class="alert alert-info mt-4" role="alert">
        The data only shows up the last 31 days
    </div>

    <?php
    if (count($cash_carries)) {
        echo '<div class="accordion mt-4" id="accordionCashCarry">';
        foreach ($cash_carries as $cash_carry) {
            $this->db->select('c.days');
            $this->db->from('shift_employees a');
            $this->db->join('shifts b', 'a.shift_id = b.id', 'left');
            $this->db->join('shift_details c', 'b.id = c.shift_id', 'left');
            $this->db->where('a.employee_id', $cash_carry['employee_id']);
            $shift_employee = $this->db->get()->row();

            $this->db->select('trans_date');
            $this->db->from('calendars');
            $this->db->where('trans_date', $cash_carry['trans_date']);
            $calendars = $this->db->get()->result_array();

            $allowance_cash_carry = $this->crud->read("allowance_cash_carries", [], ["contract_id" => $cash_carry['contract_id']]);

            $start = strtotime($cash_carry['trans_date']);
            $att_time_begin = strtotime(@$cash_carry['trans_date'] . " " . @$cash_carry['time_in']);
            $att_time_end = strtotime(@$cash_carry['trans_date'] . " " . @$cash_carry['time_out']);
            $att_diff = $att_time_end - $att_time_begin;
            $att_hour = floor($att_diff / (60 * 60));

            $cc_hour = $cash_carry['duration_hour'];

            //Validasi Jam
            if ($att_hour > $cc_hour) {
                $hour = $cc_hour;
            } else {
                $hour = $att_hour;
            }

            //Validasi Uang makan
            if ($cash_carry['meal'] == 0 or $cash_carry['time_in'] == "") {
                $meal = 0;
            } else {
                $meal = @$allowance_cash_carry->meal;
            }

            if (@$shift_employee->days == "5") {
                if (date('w', $start) !== '0' && date('w', $start) !== '6') {

                    //Kalo ada tanggal Merah
                    if (count($calendars) > 0) {
                        $total = ((@$allowance_cash_carry->holiday * $hour) + $meal);
                    } else {
                        $total = ((@$allowance_cash_carry->weekday * $hour) + $meal);
                    }
                } else {
                    $total = ((@$allowance_cash_carry->weekend * $hour) + $meal);
                }
            } else {
                if (date('w', $start) !== '0') {

                    //Kalo ada tanggal Merah
                    if (count($calendars) > 0) {
                        $total = ((@$allowance_cash_carry->holiday * $hour) + $meal);
                    } else {
                        $total = ((@$allowance_cash_carry->weekday * $hour) + $meal);
                    }
                } else {
                    $total = ((@$allowance_cash_carry->weekend * $hour) + $meal);
                }
            }

            if ($cash_carry['users_id_to'] != "") {
                $status = '<span class="badge bg-warning">Pending</span>';
            } else {
                $status = '<span class="badge bg-success">Approved</span>';
            }

            echo '  <div class="accordion-item">
                        <h2 class="accordion-header" id="' . $cash_carry['id'] . '">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapse' . $cash_carry['id'] . '" aria-expanded="true" aria-controls="collapse' . $cash_carry['id'] . '">
                                ' . $status . ' &nbsp; &nbsp; ' . date("d F Y", strtotime($cash_carry['trans_date'])) . '
                            </button>
                        </h2>
                        <div id="collapse' . $cash_carry['id'] . '" class="accordion-collapse collapse" aria-labelledby="' . $cash_carry['id'] . '" data-bs-parent="#accordionCashCarry">
                            <div class="accordion-body">
                                <table class="table table-sm" style="font-size: 12px;">
                                    <tr>
                                        <th width="100">Request No</th>
                                        <td style="text-align: right;">' . $cash_carry['request_code'] . '</td>
                                    </tr>
                                    <tr>
                                        <th width="100">Request Name</th>
                                        <td style="text-align: right;">' . $cash_carry['created_by'] . '</td>
                                    </tr>
                                    <tr>
                                        <th width="100">Start</th>
                                        <td style="text-align: right;"><span class="badge bg-success">' . $cash_carry['start'] . '</span></td>
                                    </tr>
                                    <tr>
                                        <th width="100">End</th>
                                        <td style="text-align: right;"><span class="badge bg-danger">' . $cash_carry['end'] . '</span></td>
                                    </tr>
                                    <tr>
                                        <th width="100">Check In</th>
                                        <td style="text-align: right;"><span class="badge bg-success">' . $cash_carry['time_in'] . '</span></td>
                                    </tr>
                                    <tr>
                                        <th width="100">Check Out</th>
                                        <td style="text-align: right;"><span class="badge bg-danger">' . $cash_carry['time_out'] . '</span></td>
                                    </tr>
                                    <tr>
                                        <th width="100">Duration</th>
                                        <td style="text-align: right;">' . number_format($att_hour, 2) . ' Hour</td>
                                    </tr>
                                    <tr>
                                        <th width="100">Meal</th>
                                        <td style="text-align: right;">Rp. ' . number_format($meal) . '</td>
                                    </tr>
                                    <tr>
                                        <th width="100">Amount</th>
                                        <td style="text-align: right;">Rp. ' . number_format($total) . '</td>
                                    </tr>
                                    <tr>
                                        <th width="100">Remarks</th>
                                        <td style="text-align: right;">' . $cash_carry['remarks'] . '</td>
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