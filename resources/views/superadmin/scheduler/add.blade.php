@extends('layouts.vertical', ['title' => 'Scheduler'])

@section('css')
<!-- Plugins css -->
<link href="{{asset('assets/libs/datatables/datatables.min.css')}}" rel="stylesheet" type="text/css" />

@endsection

@section('content')

<?php error_reporting(0);
@$flag = $_GET['flag']; ?>
<!-- Start Content-->
<!-- Start Content-->
<div class="container-fluid" id="shedular">



    <!-- start page title -->
    <!-- <div class="row">
            <div class="col-12">
                <div class="page-title-box">
                    <div class="page-title-right">
                        <ol class="breadcrumb m-0">
                            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Home</a></li>
                            <li class="breadcrumb-item active">Scheduler</li>
                        </ol>
                    </div>
                    <h4 class="page-title">Scheduler</h4>
                </div>
            </div>
        </div>      -->
    <!-- end page title -->

    <div class="row">
        <div class="col-12 d-flex">
            <select class="btn btn-defult h30" id="client_staff">
                <option value="1">Driver</option>
                <option value="2">Staff</option>
            </select>
            <select class="btn btn-defult h30">
                <option>All Status</option>
                <option>Job Board</option>
                <option>Pending</option>
                <option>Cancelled</option>
                <option>Booked</option>
                <option>Approved</option>
                <option>Invoiced</option>
            </select>
            <select class="btn btn-defult h30" id="shiftType">
                <option value="all">All Types</option>
                @foreach($shiftTypes as $shiftType)
                <option value="{{$shiftType->id}}">{{$shiftType->name}}</option>
                @endforeach
            </select>
            <select class="btn btn-defult h30">
                <option>View</option>
            </select>
            <div class="spinner-border spinner-border-sm mt-2 ml-2 text-primary d-none" role="status" id="loader">
                <span class="sr-only">Loading...</span>
            </div>
            <!-- <div class="text-center m-auto">Loading...</div> -->
            <h3 class="text-center m-auto" id="current_month"></h3>
            <button type="button" class="btn btn-defult h30 cal" onclick="tsDatePickerClick()"> <i data-feather="calendar"></i></button>
            <input type="text" id="cl" style="display:none" value="{{date('Y-m-d')}}">
            <select class="btn btn-defult h30" onchange="calDay()" id="calDay">
                {{-- <option value="2" @if (@$flag=='2' ) selected @endif>Fortnighty</option> --}}
                <option class="dd" value="1" @if (@$flag=='1' ) selected @endif>This Week</option>
                <option value="3">Daily</option>

            </select>
            <button type="button" onclick="window.location.href='{{route('addSchedule')}}'" class="btn plusbtn"> <img src="{{asset('assets/images/plus.png')}}" alt="plus"></button>
        </div><!-- end col-->
    </div>
    <!-- end row-->
    <div class="" id="ajax-container">



    </div> <!-- container -->
    @endsection

    @section('script')
    <!-- Plugins js-->
    <script src="{{asset('assets/libs/datatables/datatables.min.js')}}"></script>
    <script src="{{asset('assets/libs/pdfmake/pdfmake.min.js')}}"></script>
    <link rel="stylesheet" href="//code.jquery.com/ui/1.13.2/themes/base/jquery-ui.css">
    <!-- Page js-->
    <script src="{{asset('assets/js/pages/datatables.init.js')}}"></script>


    <script>
        // var flag = "{{($flag == "") ? 1 :  $flag }}";


        $(document).ready(function() {
            datep();
            calDay();

        });

        function tsDatePickerClick() {
            $("#cl").datepicker('show');
        }

        function datep() {
            $("#cl").datepicker({
                onSelect: function(selectedDate) {
                    // The selectedDate parameter contains the selected date
                    console.log(selectedDate);

                    data['calendarDate'] = selectedDate;
                    // var data = {
                    //     calendarDate: selectedDate
                    // };
                    // console.log('>>>', data);
                    loadPaginatedData(1, data);

                }
            });
        }

        var data = {};

        $(document).on('click', '.page-link', function(e) {
            e.preventDefault();
            var page = $(this).attr('href').split('page=')[1];
            console.log('>>>', page);
            loadPaginatedData(page, data);
        });


        function initFeatherIcons() {
            feather.replace(); // This will replace all elements with "data-feather" attributes with SVG icons

        }

        // Get calendar data 
        function loadPaginatedData(page, data) {
            $.ajax({
                url: "{{ route('scheduler.create') }}?page=" + page,
                data: data,
                method: "GET",
                success: function(response) {
                    $('#ajax-container').html(response.html);
                    var user_ids = $("input[name='user_ids[]']").map(function() {
                        return $(this).val();
                    }).get();
                    initFeatherIcons();
                    datep();

                    $("#current_month").html(response.current_month);
                    renderUserData(response.dates, response.days, user_ids, response.shift_type_id, response.client_staff);

                },
                error: function() {
                    console.error('Failed to fetch content.');
                }
            });

        }

        function formatAMPM(date) {
            var hours = date.getHours();
            var minutes = date.getMinutes();
            var ampm = hours >= 12 ? 'pm' : 'am';
            hours = hours % 12;
            hours = hours ? hours : 12; // the hour '0' should be '12'
            minutes = minutes < 10 ? '0' + minutes : minutes;
            var strTime = hours + ':' + minutes + ' ' + ampm;
            return strTime;
        }

        function renderUserData(dates, days, user_ids, shift_type_id, client_staff) {
            var formData = {
                _token: "{{ csrf_token() }}",
                days: days,
                dates: dates,
                user_ids: user_ids,
                shift_type_id: shift_type_id,
                client_staff: client_staff,
            };

            var type = "POST";
            var ajaxurl = "{{route('getweeklyScheduleInfo')}}";

            $.ajax({
                type: type,
                url: ajaxurl,
                data: formData,
                dataType: 'json',
                beforeSend: function() {
                    $("#loader").removeClass("d-none");
                },
                success: function(data) {
                    var x = document.getElementById("calDay").value;
                    var currentDate = new Date();
                    currentDate.setHours(0, 0, 0, 0);
                    $.each(data.schedule, function(k, v) {
                        var isPastDate = new Date(v.date) < currentDate;

                        var client_staff_id = document.getElementById("client_staff").value;
                        if (client_staff_id == 1) {

                            var day = new Date(v.date).getDay();
                            var ans = (day === 6 || day === 0);

                            if (v.shift_type_id == 2) {
                                var p2 = '<div class="card-cal lightblue"';
                                if (ans) {
                                    p2 += 'style="opacity:0.3;"';
                                }
                                p2 += '>';
                            } else if (v.shift_type_id == 3) {
                                var p2 = '<div class="card-cal lightred"';
                                if (ans) {
                                    p2 += 'style="opacity:0.3;"';
                                }
                                p2 += '>';
                            } else {
                                var p2 = '<div class="card-cal lightgreen"';
                                if (ans) {
                                    p2 += 'style="opacity:0.3;"';
                                }
                                p2 += '>';
                            }

                            var count = $("#" + v.driver_id + "_" + (Date.parse(v.date) / 1000)).data("count");
                            var carer_name = (v.carers[0]) ? v.carers[0].user.first_name : 'Job Board';
                            var p3 = '<h5>' + v.city + '</h5>';
                            var p4 = '<p>';

                            if (v.shift_type.name === 'pick and drop') {
                                p4 += formatAMPM(new Date(v.start_time)) + '-' + formatAMPM(new Date(v.end_time));
                            } else if (v.shift_type.name === 'pick') {
                                p4 += formatAMPM(new Date(v.start_time));
                            } else {
                                p4 += formatAMPM(new Date(v.end_time));
                            }
                            p4 += '</p>';

                            var p5 = '<hr class="line" />';
                            var p6 = '<p class="bold">' + v.shift_type.name.charAt(0).toUpperCase() + v.shift_type.name.slice(1); + '</p>';
                            var p7 = '<div class="soical-icon mt-2">';
                            p8 = '';
                            p9 = '';
                            p10 = '';

                            if (v.is_repeat == 1) {
                                var p10 = '<img title="Repeat" src="{{asset("assets/images/money2.svg")}}" alt="money2" class="icon">';
                            }
                            if (v.is_splitted == 1) {
                                var p9 = '<img title="Splitted" src="{{asset("assets/images/Group.svg")}}" alt="Group" class="icon">';
                            }

                            var p11 = '<img src="{{asset("assets/images/money-recive.svg")}}" alt="money-recive" class="float-right soical-recive">';
                            var p12 = '</div>';
                            var p13 = '</div>';
                            var p14 = '</td>';
                            if (!isPastDate) {
                                var editUrl = 'schedule/edit/' + v.id + '/' + (Date.parse(v.date) / 1000);
                                console.log(v.date);
                                var p_a1 = '<a href=" ' + editUrl + ' ">';
                                var p_a2 = '</a>';
                            } else {
                                p_a1 = '';
                                p_a2 = '';
                            }
                            if (x == 1) {
                                if (count > 1) {
                                    if (count > 2) {
                                        count += 1;
                                        $("#" + v.driver_id + "_" + (Date.parse(v.date) / 1000) + '_more').html("+" + (count - 2) + " more");
                                    } else {
                                        var p1 = '<div class="card-cal lightred" id=' + v.driver_id + '_' + (Date.parse(v.date) / 1000) + '_more' + '>';
                                        var p2 = '</div>';
                                        count += 1;
                                        $("#" + v.driver_id + "_" + (Date.parse(v.date) / 1000)).append(p1 + "+" + (count - 2) + " more" + p2);
                                        $("#" + v.driver_id + "_" + (Date.parse(v.date) / 1000)).data("count", count);
                                    }
                                } else {
                                    var p1 = '<td class="w13">';

                                    if (jQuery.inArray(v.date, data.holidays) == -1) {
                                        count += 1;
                                        $("#" + v.driver_id + "_" + (Date.parse(v.date) / 1000)).append(p_a1 + p2 + p3 + p4 + p5 + p6 + p7 + p8 + p9 + p10 + p11 + p12 + p13 + p_a2);
                                        $("#" + v.driver_id + "_" + (Date.parse(v.date) / 1000)).data("count", count);
                                        if (v.shift_finishes_next_day == 1) {
                                            var next_day_count = $("#" + v.driver_id + "_" + (Date.parse(addOneDay(new Date(v.date))) / 1000)).data("count");
                                            $("#" + v.driver_id + "_" + (Date.parse(addOneDay(new Date(v.date))) / 1000)).append(p_a1 + p2 + p3 + p4 + p5 + p6 + p7 + p8 + p9 + p10 + p11 + p12 + p13 + p_a2);
                                            $("#" + v.driver_id + "_" + (Date.parse(addOneDay(new Date(v.date))) / 1000)).data("count", next_day_count + 1);
                                        }

                                    } else {
                                        $.each(v.carers, function(k1, v1) {
                                            if (v1.working_days != null) {
                                                if (jQuery.inArray(v.date, jQuery.parseJSON(v1.working_days)) !== -1) {
                                                    count += 1;
                                                    $("#" + v.driver_id + "_" + (Date.parse(v.date) / 1000)).append(p_a1 + p2 + p3 + p4 + p5 + p6 + p7 + p8 + p9 + p10 + p11 + p12 + p13 + p_a2);
                                                    $("#" + v.driver_id + "_" + (Date.parse(v.date) / 1000)).data("count", count);
                                                    if (v.shift_finishes_next_day == 1) {
                                                        var next_day_count = $("#" + v.driver_id + "_" + (Date.parse(addOneDay(new Date(v.date))) / 1000)).data("count");
                                                        $("#" + v.driver_id + "_" + (Date.parse(addOneDay(new Date(v.date))) / 1000)).append(p_a1 + p2 + p3 + p4 + p5 + p6 + p7 + p8 + p9 + p10 + p11 + p12 + p13 + p_a2);
                                                        $("#" + v.driver_id + "_" + (Date.parse(addOneDay(new Date(v.date))) / 1000)).data("count", next_day_count + 1);
                                                    }
                                                }
                                            }
                                        });
                                    }
                                }
                            } else if (x == 3) {

                                var p1 = '<td class="w1" colspan="2">';

                                if (jQuery.inArray(v.date, data.holidays) == -1) {
                                    $("#" + v.driver_id + "_" + (Date.parse(v.date) / 1000)).append(p_a1 + p2 + p3 + p4 + p5 + p6 + p7 + p8 + p9 + p10 + p11 + p12 + p13 + p_a2);
                                    if (v.shift_finishes_next_day == 1) {
                                        $("#" + v.driver_id + "_" + (Date.parse(addOneDay(new Date(v.date))) / 1000)).append(p_a1 + p2 + p3 + p4 + p5 + p6 + p7 + p8 + p9 + p10 + p11 + p12 + p13 + p_a2);
                                    }
                                } else {
                                    $.each(v.carers, function(k1, v1) {
                                        if (v1.working_days != null) {
                                            if (jQuery.inArray(v.date, jQuery.parseJSON(v1.working_days)) !== -1) {
                                                $("#" + v.driver_id + "_" + (Date.parse(v.date) / 1000)).append(p_a1 + p2 + p3 + p4 + p5 + p6 + p7 + p8 + p9 + p10 + p11 + p12 + p13 + p_a2);
                                                if (v.shift_finishes_next_day == 1) {
                                                    $("#" + v.driver_id + "_" + (Date.parse(addOneDay(new Date(v.date))) / 1000)).append(p_a1 + p2 + p3 + p4 + p5 + p6 + p7 + p8 + p9 + p10 + p11 + p12 + p13 + p_a2);
                                                }
                                            }
                                        }
                                    });
                                }

                            }
                        } else if (client_staff_id == 2) {
                            $.each(v.carers, function(k1, v1) {
                                var isPastDate = new Date(v.date) < currentDate;

                                var day = new Date(v.date).getDay();
                                var ans = (day === 6 || day === 0);

                                if (v.shift_type_id == 2) {
                                    var p2 = '<div class="card-cal lightblue"';
                                    if (ans) {
                                        p2 += 'style="opacity:0.3;"';
                                    }
                                    p2 += '>';
                                } else if (v.shift_type_id == 3) {
                                    var p2 = '<div class="card-cal lightred"';
                                    if (ans) {
                                        p2 += 'style="opacity:0.3;"';
                                    }
                                    p2 += '>';
                                } else {
                                    var p2 = '<div class="card-cal lightgreen"';
                                    if (ans) {
                                        p2 += 'style="opacity:0.3;"';
                                    }
                                    p2 += '>';
                                }

                                var count = $("#" + v1.carer_id + "_" + (Date.parse(v.date) / 1000)).data("count");
                                var driver_name = (v.driver) ? v.driver.first_name + ' ' + v.driver.last_name : 'Job Board';
                                var p3 = '<h5>' + driver_name + '</h5>';
                                var p4 = '<p>';

                                if (v.shift_type.name === 'pick and drop') {
                                    p4 += formatAMPM(new Date(v.start_time)) + '-' + formatAMPM(new Date(v.end_time));
                                } else if (v.shift_type.name === 'pick') {
                                    p4 += formatAMPM(new Date(v.start_time));
                                } else {
                                    p4 += formatAMPM(new Date(v.end_time));
                                }
                                p4 += '</p>';
                                var p5 = '<hr class="line" />';
                                var p6 = '<p class="bold">' + v.shift_type.name.charAt(0).toUpperCase() + v.shift_type.name.slice(1); + '</p>';
                                var p7 = '<div class="soical-icon mt-2">';
                                // var p8 = '<img src="{{asset("assets/images/Group.svg")}}" alt="Group" class="icon">';
                                // var p9 = '<img src="{{asset("assets/images/money1.svg")}}" alt="money1" class="icon">';
                                // var p10 = '<img src="{{asset("assets/images/money2.svg")}}" alt="money2" class="icon">';
                                p8 = '';
                                p9 = '';
                                p10 = '';

                                if (v.is_repeat == 1) {
                                    var p10 = '<img title="Repeat" src="{{asset("assets/images/money2.svg")}}" alt="money2" class="icon">';
                                }
                                if (v.is_splitted == 1) {
                                    var p9 = '<img title="Splitted" src="{{asset("assets/images/Group.svg")}}" alt="Group" class="icon">';
                                }

                                var p11 = '<i mg src="{{asset("assets/images/money-recive.svg")}}" alt="money-recive" class="float-right soical-recive">';
                                var p12 = '</div>';
                                var p13 = '</div>';
                                var p14 = '</td>';
                                if (!isPastDate) {
                                    var editUrl = 'schedule/edit/' + v.id + '/' + (Date.parse(v.date) / 1000);

                                    var p_a1 = '<a href=" ' + editUrl + ' ">';
                                    var p_a2 = '</a>';
                                } else {
                                    p_a1 = '';
                                    p_a2 = '';
                                }
                                var day = new Date(v.date).getDay();
                                var ans = (day === 6 || day === 0);

                                if (x == 1) {

                                    if (count > 1) {
                                        if (count > 2) {
                                            count += 1;
                                            $("#" + v1.carer_id + "_" + (Date.parse(v.date) / 1000) + '_more').html("+" + (count - 2) + " more");
                                        } else {
                                            var p1 = '<div class="card-cal lightred" id=' + v1.carer_id + '_' + (Date.parse(v.date) / 1000) + '_more' + '>';
                                            var p2 = '</div>';
                                            count += 1;
                                            $("#" + v1.carer_id + "_" + (Date.parse(v.date) / 1000)).append(p1 + "+" + (count - 2) + " more" + p2);
                                            $("#" + v1.carer_id + "_" + (Date.parse(v.date) / 1000)).data("count", count);
                                        }
                                    } else {
                                        var p1 = '<td class="w13">';

                                        if (jQuery.inArray(v.date, data.holidays) !== -1) {

                                            if (v1.working_days != null) {
                                                if (jQuery.inArray(v.date, jQuery.parseJSON(v1.working_days)) == -1) {
                                                    var p2 = '<div class="card-cal lightorange">';
                                                    var p6 = '<p class="bold">Public Holiday</p>';
                                                }
                                            } else {
                                                var p2 = '<div class="card-cal lightorange">';
                                                var p6 = '<p class="bold">Public Holiday</p>';
                                            }
                                        } else if (data.leaves.hasOwnProperty(v1.carer_id) && data.leaves[v1.carer_id] == v.date) {
                                            var p2 = '<div class="card-cal lightyellow">';
                                            var p6 = '<p class="bold">On Leave</p>';
                                            var p4 = '<p> </p>';
                                        }
                                        count += 1;
                                        $("#" + v1.carer_id + "_" + (Date.parse(v.date) / 1000)).append(p_a1 + p2 + p3 + p4 + p5 + p6 + p7 + p8 + p9 + p10 + p11 + p12 + p13 + p_a2);
                                        $("#" + v1.carer_id + "_" + (Date.parse(v.date) / 1000)).data("count", count);
                                        if (v.shift_finishes_next_day == 1) {
                                            $("#" + v1.carer_id + "_" + (Date.parse(addOneDay(new Date(v.date))) / 1000)).append(p_a1 + p2 + p3 + p4 + p5 + p6 + p7 + p8 + p9 + p10 + p11 + p12 + p13 + p_a2);
                                        }
                                    }

                                } else if (x == 3) {

                                    if (jQuery.inArray(v.date, data.holidays) !== -1) {

                                        if (v1.working_days != null) {
                                            if (jQuery.inArray(v.date, jQuery.parseJSON(v1.working_days)) == -1) {
                                                var p2 = '<div class="card-cal lightorange">';
                                                var p6 = '<p class="bold">Public Holiday</p>';
                                            }
                                        } else {
                                            var p2 = '<div class="card-cal lightorange">';
                                            var p6 = '<p class="bold">Public Holiday</p>';
                                        }
                                    } else if (data.leaves.hasOwnProperty(v1.carer_id) && data.leaves[v1.carer_id] == v.date) {
                                        var p2 = '<div class="card-cal lightyellow">';
                                        var p6 = '<p class="bold">On Leave</p>';
                                        var p4 = '<p> </p>';
                                    }
                                    $("#" + v1.carer_id + "_" + (Date.parse(v.date) / 1000)).append(p_a1 + p2 + p3 + p4 + p5 + p6 + p7 + p8 + p9 + p10 + p11 + p12 + p13 + p_a2);
                                    if (v.shift_finishes_next_day == 1) {
                                        $("#" + v1.carer_id + "_" + (Date.parse(addOneDay(new Date(v.date))) / 1000)).append(p_a1 + p2 + p3 + p4 + p5 + p6 + p7 + p8 + p9 + p10 + p11 + p12 + p13 + p_a2);
                                    }
                                }
                            });
                        }

                    });
                },
                error: function(data) {
                    console.log(data);
                },
                complete: function() {
                    $("#loader").addClass("d-none");
                },
            });

        }

        function addOneDay(date = new Date()) {
            date.setDate(date.getDate() + 1);

            return date;
        }
    </script>
    <script src="{{asset('assets/js/pages/datatables.init.js')}}"></script>
    <script>
        var url = "<?= url('/'); ?>";

        function calDay() {
            var x = document.getElementById("calDay").value;
            if (x == '1') {
                //window.location.assign(url + "/admin/scheduler?flag=1");
                data['flag'] = 1;
                loadPaginatedData(1, data);

            }
            if (x == '2') {
                data['flag'] = 2;
                loadPaginatedData(1, data);

            }
            if (x == '3') {
                data['flag'] = 3;
                loadPaginatedData(1, data);

            }
        }

        $('#shiftType').on('change', function() {
            data['shift_type_id'] = this.value;
            calDay();
        });

        $('#client_staff').on('change', function() {
            data['client_staff'] = this.value;
            calDay();
        });
    </script>
    @endsection