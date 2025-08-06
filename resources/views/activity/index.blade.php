@extends('layouts.vertical', ['title' => 'Activity'])

@section('css')
<!-- Plugins css -->
<link href="{{asset('assets/libs/datatables/datatables.min.css')}}" rel="stylesheet" type="text/css" />
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.9.0/css/bootstrap-datepicker.min.css">


@endsection

@section('content')
<?php error_reporting(0); ?>
<!-- Start Content-->
<div class="container-fluid">

    <!-- start page title -->
    <!-- <div class="row">
            <div class="col-12">
                <div class="page-title-box">
                    <div class="page-title-right">
                        <ol class="breadcrumb m-0">
                            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Home</a></li>
                            <li class="breadcrumb-item active">List Allowances</li>
                        </ol>
                    </div>
                    <h4 class="page-title">List Allowances</h4>
                </div>
            </div>
        </div>      -->
    <!-- end page title -->

    <div class="row mt-3">
        <div class="col-2">
            <ul class="nav_list">
                <li>
                    <a href="{{url('users/invoice_settings')}}"><span>Invoice Settings</span></a>
                </li>
                <li>
                    <a href="{{route('prices.index')}}"><span>Prices</span></a>
                </li>
                <li>
                    <a href="{{route('award_group.index')}}"><span>Pay Groups</span></a>
                </li>
                <li>
                    <a href="{{route('allowance.index')}}"><span>Allowances</span></a>
                </li>

                <li>
                    <a href="{{url('users/reminders')}}"><span>Reminders</span></a>
                </li>

                <li>
                    <a href="{{url('users/subscription')}}"><span>Subscription</span> </a>
                </li>
                <li>
                    <a href="{{route('billing.index')}}"><span>Billing</span> </a>
                </li>
                <li class="activeli">
                    <a href="{{route('activity.index')}}"><span>Activity</span> </a>
                </li>
            </ul>
        </div>

        <div class="col-10">



            <div class="card">
                <div class="card-body">

                    <div class="container">
                        <div class="row">
                            <div class="col-6"></div>
                            <div class="col-2">
                                <select class="btn btn-primary">
                                    <option value="">Select an option</option>
                                    <option value="option1">Option 1</option>
                                    <option value="option2">Option 2</option>
                                    <option value="option3">Option 3</option>
                                </select>
                            </div>
                            <div class="col-4">
                                <div class="input-daterange">
                                    <input type="text" id="start_date" name="start_date" placeholder="Start Date">&nbsp;<input type="text" id="end_date" name="end_date" placeholder="End Date">
                                </div>
                            </div>
                        </div>
                    </div>

                    <br>
                    <table class="table table-design-default">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Name</th>
                                <th>Total Shifts</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($drivers as $driver)
                            <input type="hidden" name="user_ids[]" value="{{$driver->id}}" id="user_ids">
                            <tr>
                                <td>{{$loop->index+1}}</td>
                                <td>{{$driver->first_name . " " . $driver->last_name}}</td>
                                <td id="{{$driver->id}}"></td>
                            </tr>
                            @endforeach
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
<script src="{{asset('assets/libs/pdfmake/pdfmake.min.js')}}"></script>

<!-- Page js-->
<script src="{{asset('assets/js/pages/datatables.init.js')}}"></script>
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.9.0/js/bootstrap-datepicker.min.js"></script>
<script>
    $(document).ready(function() {
        $('.js-example-basic-single').select2();

        $('#select-all-checkbox').on('change', function() {
            var selectAll = $(this).prop('checked');
            $('#my-select').find('option').prop('selected', selectAll);
            $('#my-select').trigger('change');
        });

    });


    $('.input-daterange').datepicker({
        format: 'yyyy-mm-dd', // You can adjust the date format
        todayBtn: 'linked',
        autoclose: true
    });

    $('.input-daterange').datepicker().on('change', function(ev) {
        var startDate = $("#start_date").val();
        var endDate = $("#end_date").val();
        var user_ids = $("input[name='user_ids[]']").map(function() {
            return $(this).val();
        }).get();
        renderUserData(user_ids, startDate, endDate);
    });

    $(document).ready(function() {
        var user_ids = $("input[name='user_ids[]']").map(function() {
            return $(this).val();
        }).get();
        renderUserData(user_ids);
    });

    function renderUserData(user_ids, startDate = null, endDate = null) {
        var formData = {
            _token: "{{ csrf_token() }}",
            startDate: startDate,
            endDate: endDate,
            user_ids: user_ids,
        };

        var type = "POST";
        var ajaxurl = "{{route('getActivityInformation')}}";

        $.ajax({
            type: type,
            url: ajaxurl,
            data: formData,
            dataType: 'json',
            success: function(data) {
                $.each(data.users, function(k, v) {
                    if (v in data.schedule) {
                        $("#" + v).html(data.schedule[v]);
                    } else {
                        $("#" + v).html(0);
                    }
                });
            },
            error: function(data) {
                console.log(data);
            },
        });

    }
</script>

@endsection