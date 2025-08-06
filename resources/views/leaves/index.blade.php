@extends('layouts.vertical', ['title' => 'Leaves'])

@section('css')
<!-- Plugins css -->
<link href="{{asset('assets/libs/datatables/datatables.min.css')}}" rel="stylesheet" type="text/css" />

<link href="{{asset('assets/libs/flatpickr/flatpickr.min.css')}}" rel="stylesheet" type="text/css" />


@endsection

@section('content')
<?php error_reporting(0); ?>
<!-- Start Content-->
<div class="container-fluid">
    <div class="row mt-3">
        <div class="col-9"></div>
        <div class="col-3">
            <input type="text" id="range-datepicker" name="daterange" class="form-control flatpickr-input active" placeholder="2018-10-03 to 2018-10-10">
        </div>

    </div>


    <div class="row mt-3">


        <div class="col-12">



            <div class="card">
                <div class="card-body">



                    <table class="table table-design-default">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Name</th>
                                <th>Leave type</th>
                                <th>Date</th>
                                <th>Status</th>


                            </tr>
                        </thead>
                        <tbody id="ajax_container">


                        </tbody>


                    </table>

                </div> <!-- end card body-->
            </div> <!-- end card -->
        </div><!-- end col-->
    </div>
    <!-- end row-->



</div> <!-- container -->
<style>
    li.select2-selection__choice {
        color: black !important;
    }
</style>



@endsection

@section('script')
<!-- Plugins js-->
<script src="{{asset('assets/libs/datatables/datatables.min.js')}}"></script>
<script src="{{asset('assets/libs/flatpickr/flatpickr.min.js')}}"></script>
<script src="{{asset('assets/libs/pdfmake/pdfmake.min.js')}}"></script>
<script src="{{asset('assets/js/pages/form-pickers.init.js')}}"></script>

<!-- Page js-->
<script src="{{asset('assets/js/pages/datatables.init.js')}}"></script>
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>


<script>
    $(document).ready(function() {
        const fp = document.querySelector("#range-datepicker")._flatpickr;
        const today = new Date();

        // ✅ Get the first day of the current week (Sunday)
        const firstDay = new Date(
            today.setDate(today.getDate() - today.getDay() + 1),
        );

        // ✅ Get the last day of the current week (Saturday)
        const lastDay = new Date(
            today.setDate(today.getDate() - today.getDay() + 7),
        );

        fp.setDate([fp.formatDate(firstDay, "Y-m-d"), fp.formatDate(lastDay, "Y-m-d")]);
        renderLeaveData(fp.formatDate(fp.selectedDates[0], "Y-m-d"), fp.formatDate(fp.selectedDates[1], "Y-m-d"));

    });

    function renderLeaveData(startDate, endDate) {
        // Make an AJAX request to your Laravel route (adjust the URL as needed)
        $.ajax({
            url: "{{ route('leave-requests') }}",
            type: "GET",
            data: {
                startDate: startDate,
                endDate: endDate
            },
            success: function(data) {
                // Update the content of the leave data container
                if (data.leaveRequests.length === 0) {
                    // Handle the case when no data is found
                    $('#ajax_container').html('<tr><td colspan="5" style="text-align:center">No leave requests found</td></tr>');
                } else {
                    $('#ajax_container').html('');
                    var sno = 1;
                    $.each(data.leaveRequests, function(key, val) {
                        // console.log(i);
                        var type;

                        type = 'Full leave';
                        if (val.type === 1) {} else if (val.type === 2) {
                            type = 'Morning half';
                        } else {
                            type = 'Evening half';
                        }

                        var row = '<tr>' + '<td>' + sno + '</td>' + '<td>' + val.staff.first_name + ' ' + val.staff.last_name + '</td>' + '<td>' + type + '</td>' + '<td>' + val.date + '</td>' + '<td>' + val.status +
                            '</td>' + '</tr>';
                        //(val.status === 'submitted' ? '&nbsp;&nbsp;<a href="http://127.0.0.1:8000/admin/schedule/edit/' + val.schedule_id + '/' + (Date.parse(val.date) / 1000)+'">/*<i class="mdi mdi-eye"></i></a>'*/ : '') +
                        $('#ajax_container').append(row);
                        sno++;

                    });
                }



            },
            error: function() {
                console.error('Failed to fetch leave data.');
            }
        });
    }

    $('#range-datepicker').on('change', function() {
        const fp = document.querySelector("#range-datepicker")._flatpickr;
        if (fp.selectedDates.length == 2) {
            fp.set("minDate", false);
            fp.set("maxDate", false);
            renderLeaveData(fp.formatDate(fp.selectedDates[0], "Y-m-d"), fp.formatDate(fp.selectedDates[1], "Y-m-d"));
        } else {
            fp.set("minDate", fp.selectedDates[0].fp_incr(-7));
            fp.set("maxDate", fp.selectedDates[0].fp_incr(+7));
        }
    });
</script>


@endsection