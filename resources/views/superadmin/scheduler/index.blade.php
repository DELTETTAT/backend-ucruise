<?php error_reporting(0);
@$flag = @$flag;
//  echo $flag;die;
?>
<style>
    .lightred {
        background-color: #f2dbdb !important;
    }
</style>
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
            <select class="btn btn-defult h30">
                <option>Client</option>
                <option>Staff</option>
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
            <select class="btn btn-defult h30">
                <option>All Types</option>
                @foreach($shiftTypes as $shiftType)
                <option>{{$shiftType->name}}</option>
                @endforeach
            </select>
            <select class="btn btn-defult h30">
                <option>View</option>
            </select>
            <h3 class="text-center m-auto">{{$currentMonth}}</h3>
            <button type="button" class="btn btn-defult h30 cal" onclick="tsDatePickerClick()"> <i data-feather="calendar"></i></button>
            <input type="text" id="cl" style="display:none" value="{{date('Y-m-d')}}">
            <select class="btn btn-defult h30" onchange="calDay()" id="calDay">
                <option value="2" @if (@$flag=='2' ) selected @endif>Fortnighty</option>
                <option class="dd" value="1" @if (@$flag=='1' ) selected @endif>This Week</option>
                <option value="3">Daily</option>

            </select>
            <button type="button" onclick="window.location.href='{{route('addSchedule')}}'" class="btn plusbtn"> <img src="{{asset('assets/images/plus.png')}}" alt="plus"></button>
        </div><!-- end col-->
    </div>
    <!-- end row-->

    <!-- start thisWeek calender-->
    <div class="row @if (@$flag == '1' || $flag == '2') displayblock @endif" id="thisWeek" style="display: none">
        <div class="col-12">
            <table class="table schedularTable">
                <tr>
                    <th class="text-center"></th>
                    <?php

                    foreach ($days as $dData) {
                    ?>

                        <th class="text-center">
                            <span class="activeday">
                                <h2>{{date('d',strtotime($dData['date']))}}</h2>
                            </span>{{strtoupper($dData['day'])}}<br />|

                        </th>

                    <?php }  ?>

                </tr>
                <?php
                // $users = ['Rahul','sonu'];

                foreach ($users_new as $user) {
                    
                  //  echo '<pre>';print_r($user);

                    // $cdate = date('Y-m-d',strtotime("today"));
                    // $currentDate = strtotime(date("Y-m-d", strtotime($cdate . " -" . (date("N", strtotime($cdate)) - 1) . " days")));
                    $currentDate = strtotime($days[0]['date']);
                ?>

                    <tr>
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
                            <h5>{{@$user->first_name.' '.@$user->last_name}} </h5>
                            <p>180 Hours</p>

                        </td>

                        <?php
                        //echo '<pre>';print_r($user->schedule_date);
                       
                        $i= 0;
                        while ($currentDate <= $endDate) { 
                           $userDates = $user->schedule_date;

                           $cDate2 =   $currentDate;

                            $cDate =  date('Y-m-d', $cDate2);

                            $getData = scheduleData($cDate,$user->id);
                            
                            ?>
                            @if($getData){
                                @if($getDatais_repeat == 1)
                                <td class="w13">
                                    <div class="card-cal lightgreen"> 
                                        <h5>Carer Name R{{$getData->id}}</h5>
                                        <p>{{date('H:i A',strtotime(@$clients->schedule->start_time))}} - {{date('H:i A',strtotime(@$clients->schedule->end_time))}}</p>

                                        <hr class="line" />
                                        <p class="bold">Social support  </p>
                                        <div class="soical-icon mt-2">
                                            <img src="{{asset('assets/images/Group.svg')}}" alt="Group" class="icon">
                                            <img src="{{asset('assets/images/money1.svg')}}" alt="money1" class="icon">
                                            <img src="{{asset('assets/images/money2.svg')}}" alt="money2" class="icon">
                                            <img src="{{asset('assets/images/money-recive.svg')}}" alt="money-recive" class="float-right soical-recive">
                                        </div>
                                    </div>
                                </td>
                                <?php $cDate2 = strtotime("+ day", $cDate2); ?>
                            @elseif($getData->is_repeat == 0)
                            <td class="w13">
                                    <div class="card-cal lightgreen"> 
                                        <h5>Carer Name {{$getData->id}}</h5>
                                        <p>{{date('H:i A',strtotime(@$clients->schedule->start_time))}} - {{date('H:i A',strtotime(@$clients->schedule->end_time))}}</p>

                                        <hr class="line" />
                                        <p class="bold">Social support  </p>
                                        <div class="soical-icon mt-2">
                                            <img src="{{asset('assets/images/Group.svg')}}" alt="Group" class="icon">
                                            <img src="{{asset('assets/images/money1.svg')}}" alt="money1" class="icon">
                                            <img src="{{asset('assets/images/money2.svg')}}" alt="money2" class="icon">
                                            <img src="{{asset('assets/images/money-recive.svg')}}" alt="money-recive" class="float-right soical-recive">
                                        </div>
                                    </div>
                                </td>
                            @endif
                        @else
                            <td class="w13">
                                <div class="card-cal lightred">
                                    No data available!
                                </div>
                            </td>
                        @endif
                            
                        <?php
                            $currentDate = strtotime("+1 day", $currentDate);
                            $i++;
                        } ?>

                    </tr>
                <?php } ?>

            </table>
            <div class="pagination">
                {{ $users->links() }}
            </div>
        </div>
    </div>
    <!-- end thisWeek calender-->

     

</div> <!-- container -->

<!-- Plugins js-->
<script src="{{asset('assets/libs/datatables/datatables.min.js')}}"></script>
<script src="{{asset('assets/libs/pdfmake/pdfmake.min.js')}}"></script>

<!-- Page js-->
