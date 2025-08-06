@extends('layouts.vertical', ['title' => 'Scheduler'])

@section('css')
<!-- Plugins css -->
<link href="{{asset('assets/libs/datatables/datatables.min.css')}}" rel="stylesheet" type="text/css" />

@endsection

@section('content')
<?php error_reporting(0);
@$flag = $_GET['flag']; ?>
<!-- Start Content-->
<div class="container-fluid" id="shedular">


    <div class="row">
        <div class="col-12 d-flex">
            <select class="btn btn-defult h30">
                <option>Client</option>
            </select>
            <select class="btn btn-defult h30">
                <option>All Status</option>
            </select>
            <select class="btn btn-defult h30">
                <option>All Types</option>
            </select>
            <select class="btn btn-defult h30">
                <option>View</option>
            </select>
            <h3 class="text-center m-auto">{{date("F", strtotime($providedDate))}} 2023</h3>
            <button type="button" class="btn btn-defult h30 cal" onclick="tsDatePickerClick()"> <i data-feather="calendar"></i></button>
            <input type="text" id="cl" style="display:none" value="{{date('Y-m-d')}}">
            <select class="btn btn-defult h30" onchange="calDay()" id="calDay">
                {{-- <option value="2" @if (@$flag=='2' ) selected @endif>Fortnighty</option> --}}
                <option class="dd" value="1" @if (@$flag=='1' ) selected @endif>This Week</option>
                <option selected>Daily</option>

            </select>
            <button type="button" class="btn plusbtn"> <img src="{{asset('assets/images/plus.png')}}" alt="plus"></button>
        </div><!-- end col-->
    </div>
    <!-- end row-->

    <!-- start thisWeek calender-->
    <div class="row" id="daily">
        <div class="col-12">
            <table class="table schedularTable">
                <tr>
                    <th class="text-center" id="firstth"></th>
                    <th class="text-center">
                        <h2>12</h2>AM<br />|
                    </th>
                    <th class="text-center">
                        <h2>1</h2>AM<br />|
                    </th>
                    <th class="text-center">
                        <h2>2</h2>AM<br />|
                    </th>
                    <th class="text-center">
                        <h2>3</h2>AM<br />|
                    </th>
                    <th class="text-center">
                        <h2>4</h2>AM<br />|
                    </th>
                    <th class="text-center">
                        <h2>5</h2>AM<br />|
                    </th>
                    <th class="text-center">
                        <h2>6</h2>AM<br />|
                    </th>
                    <th class="text-center">
                        <h2>7</h2>AM<br />|
                    </th>
                    <th class="text-center">
                        <h2>8</h2>AM<br />|
                    </th>
                    <th class="text-center">
                        <h2>9</h2>AM<br />|
                    </th>
                    <th class="text-center">
                        <h2>10</h2>AM<br />|
                    </th>
                    <th class="text-center">
                        <h2>11</h2>AM<br />|
                    </th>
                    <th class="text-center">
                        <h2>12</h2>PM<br />|
                    </th>
                    <th class="text-center">
                        <h2>1</h2>PM<br />|
                    </th>
                    <th class="text-center">
                        <h2>2</h2>PM<br />|
                    </th>
                    <th class="text-center">
                        <h2>3</h2>PM<br />|
                    </th>
                    <th class="text-center">
                        <h2>4</h2>PM<br />|
                    </th>
                    <th class="text-center">
                        <h2>5</h2>PM<br />|
                    </th>
                    <th class="text-center">
                        <h2>6</h2>PM<br />|
                    </th>
                    <th class="text-center">
                        <h2>7</h2>PM<br />|
                    </th>
                    <th class="text-center">
                        <h2>8</h2>PM<br />|
                    </th>
                    <th class="text-center">
                        <h2>9</h2>PM<br />|
                    </th>
                    <th class="text-center">
                        <h2>10</h2>PM<br />|
                    </th>
                    <th class="text-center">
                        <h2>11</h2>PM<br />|
                    </th>
                </tr>
                @foreach($users_new as $user)

                <input type="hidden" name="user_ids[]" value="{{$user->id}}" id="user_ids">
                <tr id="{{$user->id}}">
                    <td class="user-detail">
                        <img src="{{asset('assets/images/user.png')}}" alt="user-image" class="rounded-circle">
                        <div class="btn-group float-right more">
                            <button type="button" class="btn dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                <img src="{{asset('assets/images/more.png')}}" alt="menu">
                            </button>
                            <!-- <div class="dropdown-menu">
                                <a class="dropdown-item" href="#"></a>
                            </div> -->
                        </div>
                        <h5>{{$user->first_name.' '.$user->last_name}}</h5>
                        <p>180 Hours</p>

                    </td>
                    <!-- <td class="w1" colspan="2">
                        <div class="card-cal lightgreen">
                            <h5>Aaran Johnsan</h5>
                            <p>11:00 Pm - 7:00 Am </p>
                            <hr class="line" />
                            <p class="bold">Soical support</p>
                            <div class="soical-icon mt-2">
                                <img src="{{asset('assets/images/Group.svg')}}" alt="Group" class="icon">
                                <img src="{{asset('assets/images/money1.svg')}}" alt="money1" class="icon">
                                <img src="{{asset('assets/images/money2.svg')}}" alt="money2" class="icon">
                                <img src="{{asset('assets/images/money-recive.svg')}}" alt="money-recive" class="float-right soical-recive">
                            </div>
                        </div>
                    </td>
                    <td class="w1"></td>
                    <td class="w1" colspan="2">
                        <div class="card-cal lightblue">
                            <h5>Aaran Johnsan</h5>
                            <p>11:00 Pm - 7:00 Am </p>
                            <hr class="line" />
                            <p class="bold">Soical support</p>
                            <div class="soical-icon mt-2">
                                <img src="{{asset('assets/images/Group.svg')}}" alt="Group" class="icon">
                                <img src="{{asset('assets/images/money1.svg')}}" alt="money1" class="icon">
                                <img src="{{asset('assets/images/money2.svg')}}" alt="money2" class="icon">
                                <img src="{{asset('assets/images/money-recive.svg')}}" alt="money-recive" class="float-right soical-recive">
                            </div>
                        </div>
                    </td>
                    <td class="w1"></td>
                    <td class="w1" colspan="3">
                        <div class="card-cal lightyellow">
                            <h5>Aaran Johnsan</h5>
                            <p>11:00 Pm - 7:00 Am </p>
                            <hr class="line" />
                            <p class="bold">Soical support</p>
                            <div class="soical-icon mt-2">
                                <img src="{{asset('assets/images/Group.svg')}}" alt="Group" class="icon">
                                <img src="{{asset('assets/images/money1.svg')}}" alt="money1" class="icon">
                                <img src="{{asset('assets/images/money2.svg')}}" alt="money2" class="icon">
                                <img src="{{asset('assets/images/money-recive.svg')}}" alt="money-recive" class="float-right soical-recive">
                            </div>
                        </div>
                    </td>
                    <td class="w1"></td>
                    <td class="w1" colspan="4">
                        <div class="card-cal lightred">
                            <h5>Aaran Johnsan</h5>
                            <p>11:00 Pm - 7:00 Am </p>
                            <hr class="line" />
                            <p class="bold">Soical support</p>
                            <div class="soical-icon mt-2">
                                <img src="{{asset('assets/images/Group.svg')}}" alt="Group" class="icon">
                                <img src="{{asset('assets/images/money1.svg')}}" alt="money1" class="icon">
                                <img src="{{asset('assets/images/money2.svg')}}" alt="money2" class="icon">
                                <img src="{{asset('assets/images/money-recive.svg')}}" alt="money-recive" class="float-right soical-recive">
                            </div>
                        </div>
                    </td>
                    <td class="w1"></td>
                    <td class="w1"></td>
                    <td class="w1"></td>
                    <td class="w1"></td>
                    <td class="w1"></td>
                    <td class="w1" colspan="5">
                        <div class="card-cal lightorange">
                            <h5>Aaran Johnsan</h5>
                            <p>11:00 Pm - 7:00 Am </p>
                            <hr class="line" />
                            <p class="bold">Soical support</p>
                            <div class="soical-icon mt-2">
                                <img src="{{asset('assets/images/Group.svg')}}" alt="Group" class="icon">
                                <img src="{{asset('assets/images/money1.svg')}}" alt="money1" class="icon">
                                <img src="{{asset('assets/images/money2.svg')}}" alt="money2" class="icon">
                                <img src="{{asset('assets/images/money-recive.svg')}}" alt="money-recive" class="float-right soical-recive">
                            </div>
                        </div>
                    </td> -->
                </tr>
                @endforeach
            </table>
        </div>
    </div>
    <!-- end thisWeek calender-->


</div> <!-- container -->
@endsection

@section('script')
<!-- Plugins js-->
<script src="{{asset('assets/libs/datatables/datatables.min.js')}}"></script>
<script src="{{asset('assets/libs/pdfmake/pdfmake.min.js')}}"></script>

<!-- Page js-->
<script src="{{asset('assets/js/pages/datatables.init.js')}}"></script>
<script>
    function calDay() {
        var x = document.getElementById("calDay").value;
        if (x == '1') {
            window.location.assign("/ShiftCare/admin/scheduler?flag=1");
            //   document.getElementById("thisWeek").style.display = "block";
            //   document.getElementById("Fortnighty").style.display = "none";
        } else {
            window.location.assign("/ShiftCare/admin/scheduler?flag=2");
            // document.getElementById("thisWeek").style.display = "none";
            // document.getElementById("Fortnighty").style.display = "block";

        }
    }

    
    function renderUserData(days, dates, months, years, user_ids){
        
        var formData = {
            _token : "{{ csrf_token() }}",
            days : days,
            dates : dates,
            months : months,
            years : years,
            user_ids : user_ids,
        };

        var type = "POST";
        var ajaxurl = "{{route('getweeklyScheduleInfo')}}";

        $.ajax({
            type: type,
            url: ajaxurl,
            data: formData,
            dataType: 'json',
            success: function (data) {
                $.each(data.schedule, function(k, v) {
                    $.each(v.clients, function(k1, v1) {
                        var p1 = '<td class="w1" colspan="3">';
                        var p2 = '<div class="card-cal lightorange">';
                        var p3 = '<h5>'+v1.user.first_name+'</h5>';
                        var p4 = '<p>11:00 Pm - 7:00 Am </p>';
                        var p5 = '<hr class="line" />';
                        var p6 = '<p class="bold">Social support</p>';
                        var p7 = '<div class="soical-icon mt-2">';
                        var p8 = '<img src="{{asset("assets/images/Group.svg")}}" alt="Group" class="icon">';
                        var p9 = '<img src="{{asset("assets/images/money1.svg")}}" alt="money1" class="icon">';
                        var p10 = '<img src="{{asset("assets/images/money2.svg")}}" alt="money2" class="icon">';
                        var p11 = '<img src="{{asset("assets/images/money-recive.svg")}}" alt="money-recive" class="float-right soical-recive">';
                        var p12 = '</div>';
                        var p13 = '</div>';
                        var p14 = '</td>';
                        $("#"+v1.client_id).append(p1+p2+p3+p4+p5+p6+p7+p8+p9+p10+p11+p12+p13+p14);
                    });
                });
                initFeatherIcons();
                datep();
            },
            error: function (data) {
                console.log(data);
            }
        });
        
    }

    $(document).ready(function() {

           var days = {!! json_encode($days) !!};
           var dates = {!! json_encode($dates) !!};
           var months = {!! json_encode($months) !!};
           var years = {!! json_encode($years) !!};
           var user_ids = $("input[name='user_ids[]']").map(function(){return $(this).val();}).get();

           renderUserData(days, dates, months, years, user_ids);
    });

    function initFeatherIcons() {
        feather.replace(); // This will replace all elements with "data-feather" attributes with SVG icons
    }

    function datep() {
        $("#cl").datepicker({
            onSelect: function(selectedDate) {
                // The selectedDate parameter contains the selected date
                console.log(selectedDate);

                var data = {
                    calendarDate: selectedDate
                };
                console.log('>>>', data);
                // loadPaginatedData(1, data);

            }
        });
    }

    function tsDatePickerClick() {
        $("#cl").datepicker('show');
    }
</script>
@endsection