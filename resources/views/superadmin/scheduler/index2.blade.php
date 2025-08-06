@extends('layouts.vertical', ['title' => 'Scheduler'])

@section('css')
<!-- Plugins css -->
<link href="{{asset('assets/libs/datatables/datatables.min.css')}}" rel="stylesheet" type="text/css" />

@endsection

@section('content')
<?php error_reporting(0); @$flag = $_GET['flag'];?>
<!-- Start Content-->
<div class="container-fluid">

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
            <select class="btn btn-defult">
                <option>Client</option>
            </select>
            <select class="btn btn-defult">
                <option>All Status</option>
            </select>
            <select class="btn btn-defult">
                <option>All Types</option>
            </select>
            <select class="btn btn-defult">
                <option>View</option>
            </select>
            <h2 class="text-center m-auto">March 2023</h2>
            <button type="button" class="btn btn-defult"> <i data-feather="calendar"></i></button>
            <select class="btn btn-defult" onchange="calDay()" id="calDay">
                <option class="dd" value="1" @if (@$flag == '1') selected @endif>This Week</option>
                <option value="2" @if (@$flag == '2') selected @endif>Fortnighty</option>
            </select>
            <button type="button" class="btn plusbtn"> <img src="{{asset('assets/images/plus.png')}}" alt="plus"></button>
        </div><!-- end col-->
    </div>
    <!-- end row-->

    <!-- start thisWeek calender-->
    <div class="row @if (@$flag != '2') displayblock @endif" id="thisWeek" style="display: none">
        <div class="col-12">
            <table class="table schedularTable">
                <tr>
                    <th class="text-center"></th>
                    <th class="text-center">
                        <h2>07</h2>MON<br />|
                    </th>
                    <th class="text-center">
                        <h2>08</h2>TUE<br />|
                    </th>
                    <th class="text-center">
                        <h2>09</h2>WED<br />|
                    </th>
                    <th class="text-center">
                        <h2>10</h2>THU<br />|
                    </th>
                    <th class="text-center">
                        <h2>11</h2>FRI<br />|
                    </th>
                    <th class="text-center">
                        <h2>12</h2>SAT<br />|
                    </th>
                    <th class="text-center">
                        <h2>13</h2>SUN<br />|
                    </th>
                </tr>
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
                        <h5>Jone Cooper</h5>
                        <p>180 Hours</p>

                    </td>
                    <td class="w13">
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
                    <td class="w13"></td>
                    <td class="w13">
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
                    <td class="w13"></td>
                    <td class="w13">
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
                    <td class="w13"></td>
                    <td class="w13">
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
                </tr>
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
                        <h5>Jone Cooper</h5>
                        <p>180 Hours</p>

                    </td>
                    <td class="w13"></td>
                    <td class="w13">
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
                    <td class="w13"></td>
                    <td>
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
                    </td>
                    <td class="w13"></td>
                    <td class="w13">
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
                    <td class="w13"></td>
                </tr>
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
                        <h5>Jone Cooper</h5>
                        <p>180 Hours</p>

                    </td>
                    <td class="w13">
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
                    <td class="w13">
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
                    <td class="w13">
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
                    <td class="w13">
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
                    <td class="w13">
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
                    <td class="w13">
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
                    <td class="w13">
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
                </tr>
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
                        <h5>Jone Cooper</h5>
                        <p>180 Hours</p>

                    </td>
                    <td class="w13"></td>
                    <td class="w13">
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
                    <td class="w13"></td>
                    <td>
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
                    </td>
                    <td class="w13"></td>
                    <td class="w13">
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
                    <td class="w13"></td>
                </tr>
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
                        <h5>Jone Cooper</h5>
                        <p>180 Hours</p>

                    </td>
                    <td class="w13">
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
                    <td class="w13"></td>
                    <td class="w13">
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
                    <td class="w13"></td>
                    <td class="w13">
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
                    <td class="w13"></td>
                    <td class="w13">
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
                </tr>
            </table>
        </div>
    </div>
    <!-- end thisWeek calender-->

     <!-- start Fortnighty calender-->
     <div class="row @if (@$flag == '2') displayblock @endif" id="Fortnighty"  style="display: none"> 
        <div class="col-12">
            <table class="table schedularTable">
                <tr>
                    <th class="text-center"></th>
                    <th class="text-center">
                        <h2>07</h2>MON<br />|
                    </th>
                    <th class="text-center">
                        <h2>08</h2>TUE<br />|
                    </th>
                    <th class="text-center">
                        <h2>09</h2>WED<br />|
                    </th>
                    <th class="text-center">
                        <h2>10</h2>THU<br />|
                    </th>
                    <th class="text-center">
                        <h2>11</h2>FRI<br />|
                    </th>
                    <th class="text-center">
                        <h2>12</h2>SAT<br />|
                    </th>
                    <th class="text-center">
                        <h2>13</h2>SUN<br />|
                    </th>
                    <th class="text-center">
                        <h2>14</h2>MON<br />|
                    </th>
                    <th class="text-center">
                        <h2>15</h2>TUE<br />|
                    </th>
                    <th class="text-center">
                        <h2>16</h2>WED<br />|
                    </th>
                    <th class="text-center">
                        <h2>17</h2>THU<br />|
                    </th>
                    <th class="text-center">
                        <h2>18</h2>FRI<br />|
                    </th>
                    <th class="text-center">
                        <h2>19</h2>MON<br />|
                    </th>
                    <th class="text-center">
                        <h2>20</h2>SAT<br />|
                    </th>
                </tr>
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
                        <h5>Jone Cooper</h5>
                        <p>180 Hours</p>

                    </td>
                    <td class="w10">
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
                    <td class="w10"></td>
                    <td class="w10">
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
                    <td class="w10"></td>
                    <td class="w10">
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
                    <td class="w10"></td>
                    <td class="w10">
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
                    </td>
                    <td class="w10">
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
                    <td class="w10"></td>
                    <td class="w10">
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
                    </td>
                    <td class="w10">
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
                    <td class="w10"></td>
                    <td class="w10"></td>
                    <td class="w10">
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
                </tr>
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
                        <h5>Jone Cooper</h5>
                        <p>180 Hours</p>

                    </td>
                    <td class="w10"></td>
                    <td class="w10">
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
                    <td></td>
                    <td>
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
                    </td>
                    <td class="w10"></td>
                    <td class="w10">
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
                    </td>
                    <td class="w10"></td>
                    <td class="w10"></td>
                    <td class="w10">
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
                    </td>
                    <td class="w10">
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
                    <td class="w10"></td>
                    <td class="w10">
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
                    </td>
                    <td class="w10">
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
                    <td class="w10">
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
                </tr>
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
                        <h5>Jone Cooper</h5>
                        <p>180 Hours</p>

                    </td>
                    <td class="w10">
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
                    <td class="w10">
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
                    <td class="w10">
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
                    <td class="w10">
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
                    <td class="w10">
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
                    <td class="w10">
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
                    <td class="w10">
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
                    </td>
                    <td class="w10">
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
                    <td class="w10">
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
                    <td class="w10">
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
                    </td>
                    <td class="w10">
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
                    <td class="w10">
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
                    <td class="w10">
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
                    <td class="w10">
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
                </tr>
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
                        <h5>Jone Cooper</h5>
                        <p>180 Hours</p>

                    </td>
                    <td class="w10">
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
                    <td class="w10">
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
                    <td>
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
                    <td>
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
                    </td>
                    <td class="w10">
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
                    <td class="w10">
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
                    </td>
                    <td class="w10">
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
                    <td class="w10">
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
                    <td class="w10">
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
                    </td>
                    <td class="w10">
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
                    <td class="w10">
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
                    <td class="w10">
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
                    </td>
                    <td class="w10">
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
                    <td class="w10">
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
                </tr>
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
                        <h5>Jone Cooper</h5>
                        <p>180 Hours</p>

                    </td>
                    <td class="w10">
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
                    <td class="w10"></td>
                    <td class="w10">
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
                    <td class="w10"></td>
                    <td class="w10">
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
                    <td class="w10"></td>
                    <td class="w10">
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
                    </td>
                    <td class="w10">
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
                    <td class="w10"></td>
                    <td class="w10">
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
                    </td>
                    <td class="w10">
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
                    <td class="w10"></td>
                    <td class="w10"></td>
                    <td class="w10">
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
                </tr>
                
            </table>
        </div>
    </div>
    <!-- end Fortnighty calender-->

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
  if(x == '1'){
    window.location.assign("/ShiftCare/admin/scheduler");
//   document.getElementById("thisWeek").style.display = "block";
//   document.getElementById("Fortnighty").style.display = "none";
  }
  else{
    window.location.assign("/ShiftCare/admin/scheduler?flag=2");
    // document.getElementById("thisWeek").style.display = "none";
    // document.getElementById("Fortnighty").style.display = "block";

  }
}
</script>
@endsection