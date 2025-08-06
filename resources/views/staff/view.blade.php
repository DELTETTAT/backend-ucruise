@extends('layouts.vertical', ['title' => 'Staff Details'])
@section('content')
<?php error_reporting(0); ?>
<style>
   img.pimage {
      float: right;
      border-radius: 64px;
   }

   svg.feather.feather-check.check1 {
      color: green;
   }

   svg.feather.feather-x.close1 {
      color: red;
   }

   img.Aimage {

      border-radius: 64px;
   }
</style>
<!-- Start Content-->
<div class="container-fluid">
   <!-- start page title -->
   <!-- <div class="row">
      <div class="col-12">
         <div class="page-title-box">
            <div class="page-title-right">
               <ol class="breadcrumb m-0">
                  <li class="breadcrumb-item"><a href="javascript: void(0);">Home</a></li>
                  <li class="breadcrumb-item"><a href="javascript: void(0);">Staff</a></li>
                  <li class="breadcrumb-item"><a href="javascript: void(0);">Details</a></li>
               </ol>
            </div>
            <h4 class="page-title"><img class="Aimage" src="{{url('/images')}}/{{@$show->profile_image}}" onerror="this.src='https://t3.ftcdn.net/jpg/03/45/05/92/240_F_345059232_CPieT8RIWOUk4JqBkkWkIETYAkmz2b75.jpg'" style="width: 50px;height:50px">{{$show->first_name.' '.$show->last_name}} <span style="font-size:12px">Details</span></h4>
         </div>
      </div>
   </div> -->
   <!-- end page title -->
   <div class="row mt-3">
      <div class="col-2">
         <ul class="nav_list" id="mainNav">
            <li class="activeli">
               <a href="#Demographic"><span>Demographic Detail</span></a>
            </li>
            <li>
               <a href="#Settings"><span>Settings</span></a>
            </li>
            <li>
               <a href="#Compliance"><span>Compliance</span></a>
            </li>
            <li>
               <a href="#Payroll"><span>Payroll Setting</span></a>
            </li>

            <li>
               <a href="#Note"><span>Note</span></a>
            </li>

            <li>
               <a href="#LeaveRequests"><span>Leave Requests</span></a>
            </li>
            <li>
               <a href="#TempLocation"><span>Temp Location Requests</span></a>
            </li>
            <li>
               <a href="#Reschedule"><span>Reschedule</span></a>
            </li>
         </ul>
      </div>
      <div class="col-lg-10 card">
         @if ($errors->any())
         <div class="alert alert-danger">
            <ul>
               @foreach ($errors->all() as $error)
               <li>{{ $error }}</li>
               @endforeach
            </ul>
         </div>
         @endif
         <table class="table table-design-default settingTable" id="Demographic">
            <tr>
               <th>Demographic details</th>
               <th class="text-right editIcon"> <a href="{{route('editStaff',[$show->id])}}">Edit</a></th>
            </tr>
            <tr>
               <td width="200">
                  <img class="pimage" src="{{url('/images')}}/{{@$show->profile_image}}" onerror="this.src='https://t3.ftcdn.net/jpg/03/45/05/92/240_F_345059232_CPieT8RIWOUk4JqBkkWkIETYAkmz2b75.jpg'" style="width: 200px;">
               </td>
               <td>
                  <p class="mt-3">First Name :<b> {{$show->salutation.' '.$show->first_name}}</b></p>
                  <!-- <p class="mt-3"><b>Middle Name :</b> {{$show->middle_name}}</p>
                     <p class="mt-3"><b>Last Name :</b> {{$show->last_name}}</p> -->
                  <p class="mt-3">Email : <b>{{$show->email}}</b></p>
                  <p class="mt-3">Mobile :<b> {{$show->mobile}}</b></p>
                  <p class="mt-3">Phone :<b> {{$show->phone}}</b></p>
                  <p class="mt-3">Address :<b> {{$show->address}}</b></p>
                  <p class="mt-3">DOB :<b> {{$show->dob}}</b></p>
                  <p class="mt-3">Gender :<b> {{$show->gender}}</b> Employment Type :<b> {{$show->employement_type}}</b></p>
                  <p class="mt-3">Language Spoken :<b> {{$show->staff_language}}</b></p>
                  <p class="mt-3">Role :<b> <span class="badge bg-soft-success text-success">{{ucfirst($show->roles[0]->name)}}</span></b></p>
               </td>
            </tr>
         </table>
         <table class="table table-design-default tdbdnone settingTable" id="Settings">
            <tr>
               <th width="300">Settings</th>
               <th class="text-right editIcon"><a href="javascript:" onClick="settingform()"> Edit </a></th>
            </tr>
            <tr>
               <td colspan="2">
                  <table class="col-12" id="settingView">
                     <tr>
                        <td width="300">
                           <p>Role :</p>
                        </td>
                        <td>
                           <button class="btn btn-light">{{ucfirst($show->roles[0]->name)}}</button>
                        </td>
                     </tr>
                     <tr>
                        <td>
                           <p>Teams :</p>
                        </td>
                        <td>
                           <?php

                           $teamArray =    explode(',', $csetting->teams);
                           ?>
                           @foreach($teams as $tm)
                           @if(in_array($tm->id, $teamArray))

                           <button class="btn btn-light">{{$tm->name}}</button>
                           @endif
                           @endforeach



                        </td>
                     </tr>
                     <tr>
                        <td>
                           <p>Notify Timesheet Approval :</p>
                        </td>
                        <td>
                           <b>{{$csetting->notify_timesheet_approval}}</b>
                        </td>
                     </tr>
                     <tr>
                        <td>
                           <p>Available For Rostering :</p>
                        </td>
                        <td>
                           <b>{{$csetting->available_for_rostering}}</b>
                        </td>
                     </tr>
                     <tr>
                        <td>
                           <p>Read and write private notes :</p>
                        </td>
                        <td>
                           <b>{{$csetting->private_notes}}</b>
                        </td>
                     </tr>
                     <tr>
                        <td>
                           <p>No Access :</p>
                        </td>
                        <td>
                           <b>
                              @if($csetting->no_access == 1)
                              yes
                              @else
                              No
                              @endif
                           </b>
                        </td>
                     </tr>
                     <tr>
                        <td>
                           <p>Account Owner :</p>
                        </td>
                        <td>
                           <b>
                              @if($csetting->account_owner == 1)
                              yes
                              @else
                              No
                              @endif
                           </b>
                        </td>
                     </tr>
                  </table>
               </td>
            </tr>
            <tr>
               <td colspan="2">
                  <form action="{{url('users/settingsUpdate',[$show->id])}}" method="post" id="settingform" style="display:none">
                     @csrf
                     <div class="row">
                        <div class="form-group col-3">
                           <label>Roles</label>
                           <select name="role_id" class="form-control">
                              <option value="">Select</option>
                              @foreach($roles as $tm)
                              <option value="{{$tm->id}}" <?php if ($show->roles[0]->id == $tm->id) {
                                                               echo 'selected';
                                                            } ?>>{{$tm->name}}</option>
                              @endforeach
                           </select>
                        </div>
                        <div class="form-group col-3">
                           <?php

                           $teamArray =    explode(',', $csetting->teams);
                           ?>

                           <label>Teams</label>
                           <select name="team[]" class="form-control" id="mySelect">
                              <option value="">Select</option>
                              @foreach($teams as $tm)
                              <option value="{{$tm->id}}" <?php if (in_array($tm->id, $teamArray)) {
                                                               echo 'selected';
                                                            } ?>>{{$tm->name}}</option>
                              @endforeach
                           </select>
                        </div>
                        <div class="form-group col-3">
                           <label>Notify Timesheet Approval</label>
                           <input type="text" name="notify_timesheet_approval" placeholder="Notify Timesheet Approval " class="form-control" value="{{$csetting->notify_timesheet_approval}}">
                        </div>
                        <div class="form-group col-3">
                           <label>Available For Rostering</label>
                           <input type="text" name="available_for_rostering" placeholder="Available For Rostering" class="form-control" value="{{$csetting->available_for_rostering}}">
                        </div>
                        <div class="form-group col-3">
                           <label>Read and write private notes</label>
                           <input type="text" name="private_notes" placeholder="Read and write private notes" class="form-control" value="{{$csetting->private_notes}}">
                        </div>

                        <div class="form-group col-3 mt-3">
                           <label>No Access</label>
                           <input type="checkbox" name="no_access" placeholder="Share Progress Notes" class="no_access" value="<?php if ($csetting->no_access == 1) {
                                                                                                                                    echo 1;
                                                                                                                                 } else {
                                                                                                                                    echo 0;
                                                                                                                                 } ?>" <?php if ($csetting->no_access == 1) {
                                                                                                                                          echo 'checked';
                                                                                                                                       } ?>>
                        </div>
                        <div class="form-group col-3 mt-3">
                           <label>Account Owner </label>
                           <input type="checkbox" name="account_owner" placeholder="Enable SMS Reminders" class="account_owner" value="<?php if ($csetting->account_owner == 1) {
                                                                                                                                          echo 1;
                                                                                                                                       } else {
                                                                                                                                          echo 0;
                                                                                                                                       } ?>" <?php if ($csetting->account_owner == 1) {
                                                                                                                                                echo 'checked';
                                                                                                                                             } ?>>
                        </div>
                     </div>

                     <button type="submit" class="btn btn-success" value="1" name="exit">Save</button>
                     <button type="reset" class="btn btn-defult" onClick="settingformCancel()">Cancel</button>
                  </form>
               </td>
            </tr>
         </table>
         <table class="table table-design-default settingTable" id="Compliance">
            <tr>
               <th>Compliance </th>
               <th class="text-right editIcon"><a href="{{route('staffDocuments',[$show->id])}}">Manage All</a></th>
            </tr>
            <tr>
               <td colspan="2">
                  <table class="table table-design-default normal_Font">
                     <thead>
                        <tr>

                           <th>Category</th>
                           <th>Expire At</th>
                           <th>Last Update</th>
                           <th>Status</th>
                        </tr>
                     </thead>
                     <tbody>
                        @foreach($docoments as $key1=>$data)
                        <tr>

                           <td>
                              {{$data->category}}
                           </td>
                           <td>
                              @if($data->no_expireation == 0)
                              {{date('d-m-Y',strtotime($data->expire))}}

                              @else
                              {{'.....'}}
                              @endif
                           </td>

                           <td>{{date('d-m-Y',strtotime($data->updated_at))}}</td>

                           <td>
                              @if($data->no_expireation == 1)

                              <span class="badge bg-soft-success text-success">Active</span>

                              @elseif(date('Y-m-d') >= $data->expire)
                              <span class="badge bg-soft-danger text-danger">Expired</span>
                              @else
                              <span class="badge bg-soft-success text-success">Active</span>
                              @endif
                           </td>

                        </tr>
                        @endforeach
                     </tbody>
                  </table>
               </td>
            </tr>
         </table>
         <table class="table table-design-default tdbdnone  settingTable" id="Payroll">
            <tr>
               <th width="300">Payroll Settings</th>
               <th class="text-right editIcon"><a href="javascript:" onClick="Payrollform()"> Edit </a></th>
            </tr>
            <tr>
               <td colspan="2">
                  <table class="col-12" id="PayrollView">
                     <tr>
                        <td width="300">
                           <p>Pay group :</p>
                        </td>
                        <td>
                           <button class="btn btn-light">{{@$adf->pay_group}}</button>
                        </td>
                     </tr>
                     <tr>
                        <td>
                           <p>Daily hours :</p>
                        </td>
                        <td>
                           <b>{{@$adf->daily_hours}}</b>
                        </td>
                     </tr>
                     <tr>
                        <td>
                           <p>Weekly hours :</p>
                        </td>
                        <td>
                           <b>{{@$adf->weekly_hours}}</b>
                        </td>
                     </tr>
                     <tr>
                        <td>
                           <p>External System Identifier :</p>
                        </td>
                        <td>
                           <b>{{@$adf->external_system_identifier}}</b>
                        </td>
                     </tr>
                  </table>
               </td>
            </tr>
            <tr>
               <td colspan="2">
                  <form action="{{route('staffPayrollSettings',[$show->id])}}" method="post" id="Payrollform" style="display: none;">
                     @csrf
                     <div class="row">
                        <div class="form-group col-3">
                           <label>Pay group</label>
                           <select name="pay_group" class="form-control">

                              <option value="Casual" <?php if ($adf->pay_group == 'Casual') {
                                                         echo 'selected';
                                                      } ?>>Casual</option>

                              <option value="Permanent Part Time" <?php if ($adf->pay_group == 'Permanent Part Time') {
                                                                     echo 'selected';
                                                                  } ?>>Permanent Part Time</option>

                           </select>
                        </div>
                        <div class="form-group col-3">
                           <label>Daily hours</label>
                           <input type="number" name="daily_hours" placeholder="Daily hours" class="form-control" value="{{$adf->daily_hours}}">
                        </div>

                        <div class="form-group col-3">
                           <label>Weekly hours</label>
                           <input type="number" name="weekly_hours" placeholder="Weekly hours" class="form-control" value="{{$adf->weekly_hours}}">
                        </div>

                        <div class="form-group col-3">
                           <label>External System Identifier</label>
                           <input type="text" name="external_system_identifier" placeholder="MYOB Card ID or HR Employee ID" class="form-control" value="{{$adf->external_system_identifier}}">
                        </div>
                     </div>

                     <button type="submit" class="btn btn-success" value="1" name="exit">Save</button>
                     <button type="reset" class="btn btn-defult" onClick="PayrollformCancel()">Cancel</button>

                  </form>
               </td>
            </tr>
         </table>
         <table class="table table-design-default settingTable" id="Note">
            <tr>
               <th>Notes</th>
               <th class="text-right editIcon"><a href="javascript:" onClick="noteform()"> Edit </a></th>
            </tr>
            <tr>
               <td colspan="2">
                  <form action="{{route('updateStaffNote',[$show->id])}}" method="post" id="noteform" style="display:none">
                     @csrf

                     <div class="form-group">
                        <label>Private Info</label>
                        <textarea name="private_info" placeholder="Enter Private Info" class="form-control">{{$stf->private_info}}</textarea>

                     </div>

                     <button type="submit" class="btn btn-success" value="1" name="exit">Save</button>
                     <button type="reset" class="btn btn-defult" onClick="noteformCancel()">Cancel</button>
                  </form>
                  <button class="btn btn-light" id="noteView">{{$stf->private_info}}</button>
               </td>
            </tr>
         </table>

         <table class="table table-design-default settingTable" id="LeaveRequests">
            <tr>
               <th>Leave Requests</th>
               <!-- <th class="text-right editIcon"><a href="{{route('staffDocuments',[$show->id])}}">Manage All</a></th> -->
            </tr>
            <tr>
               <td colspan="2">
                  <table class="table table-design-default normal_Font">
                     <thead>
                        <tr>
                           <th>Employee id</th>
                           <th>Employee name</th>
                           <th>Start Date</th>
                           <th>End Date</th>
                           <th>Leave type</th>
                           <th>Schedule type</th>
                           <th>Reason</th>
                           <th>Status</th>
                           <th>Action</th>
                        </tr>
                     </thead>
                     <tbody>
                        @foreach($leaveRequests as $leaveRequest)
                        <tr>
                           <td>
                              {{$show->unique_id}}
                           </td>
                           <td>
                              {{$show->first_name}}
                           </td>


                           <td>
                              {{$leaveRequest->start_date}}
                           </td>
                           <td>
                              {{$leaveRequest->end_date}}
                           </td>
                           <td>
                              @if($leaveRequest->type == 1)
                              Full day
                              @elseif($leaveRequest->type == 2)
                              Morning Half
                              @elseif($leaveRequest->type == 3)
                              Evening Half
                              @endif
                           </td>
                           <td>
                              @if($leaveRequest->type == 1)
                              <span class="badge bg-soft-primary text-primary"> Pick and drop</span>
                              @elseif($leaveRequest->type == 2)
                              <span class="badge bg-soft-success text-success">Pick</span>
                              @elseif($leaveRequest->type == 3)
                              <span class="badge bg-soft-danger text-danger">Drop</span>
                              @endif
                           </td>

                           <td>{{$leaveRequest->reason->message}}</td>

                           <td>
                              @if($leaveRequest->status == 0)

                              <span class="badge bg-soft-primary text-primary">submitted</span>

                              @elseif($leaveRequest->status == 1)
                              <span class="badge bg-soft-success text-success">Approved</span>
                              @elseif($leaveRequest->status == 2)
                              <span class="badge bg-soft-danger text-danger">Rejected</span>
                              @endif
                           </td>

                           <td>
                              @if($leaveRequest->status == 0)

                              <button style="border: none"><a href="{{ route('approve-leave', ['id' => $leaveRequest->id]) }}"><i class="mdi mdi-check-circle"></i>Accept</a></button>
                              <button style="border: none"> <a href="{{ route('reject-leave', ['id' => $leaveRequest->id]) }}" style="color:red"><i class="mdi mdi-alpha-x-circle"></i>Reject</a></button>
                              @endif
                           </td>


                        </tr>
                        @endforeach
                     </tbody>
                  </table>
               </td>
            </tr>
         </table>
         <table class="table table-design-default settingTable" id="TempLocation">
            <tr>
               <th>Temp Location change Requests</th>

            </tr>
            <tr>
               <td colspan="2">
                  <table class="table table-design-default normal_Font">
                     <thead>
                        <tr>
                           <th>Employee ID</th>
                           <th>Employee name</th>
                           <th>Reason</th>
                           <th>Requested date</th>
                           <th>Date</th>
                           <th>Requested location</th>
                           <th>Schedule type</th>
                           <th>Status</th>
                           <th>Action</th>
                        </tr>


                     </thead>
                     <tbody>
                        @foreach($scheduleCarerRelocations as $scheduleCarerRelocation)
                        <tr>
                           <td> {{$show->unique_id}}</td>
                           <td> {{$show->first_name}}</td>
                           <td>{{$scheduleCarerRelocation->reason->message}}</td>
                           <td>{{$scheduleCarerRelocation->date}}</td>
                           <td>{{ $scheduleCarerRelocation->created_at->format('Y-m-d') }}</td>
                           <td>{{$scheduleCarerRelocation->temp_address}}</td>
                           <td>
                              @if($scheduleCarerRelocation->shift_type == 1)
                              <span class="badge bg-soft-primary text-primary">Pick and drop</span>
                              @elseif($scheduleCarerRelocation->shift_type == 2)
                              <span class="badge bg-soft-success text-success">Pick</span>
                              @elseif($scheduleCarerRelocation->shift_type == 3)
                              <span class="badge bg-soft-danger text-danger">Drop</span>
                              @endif
                           </td>
                           <td>
                              @if($scheduleCarerRelocation->status == 0)
                              <span class="badge bg-soft-primary text-primary">submitted</span>
                              @elseif($scheduleCarerRelocation->status == 1)
                              <span class="badge bg-soft-success text-success">Accepted</span>
                              @elseif($scheduleCarerRelocation->status == 2)
                              <span class="badge bg-soft-danger text-danger">Rejected</span>
                              @endif
                           </td>
                           <td>
                              @if($scheduleCarerRelocation->status == 0)
                              <button style="border: none"><a href="{{ route('approve-shiftchange', ['id' => $scheduleCarerRelocation->id]) }}"><i class="mdi mdi-check-circle"></i>Accept</a></button>
                              <button style="border: none"><a href="{{ route('reject-shiftchange', ['id' => $scheduleCarerRelocation->id]) }}" style="color:red"><i class="mdi mdi-alpha-x-circle"></i>Reject</a></button>

                              @endif
                           </td>

                        </tr>
                        @endforeach
                     </tbody>
                  </table>
               </td>
            </tr>
         </table>

         <table class="table table-design-default settingTable" id="Reschedule">
            <tr>
               <th>Reschedule requests</th>

            </tr>
            <tr>
               <td colspan="2">
                  <table class="table table-design-default normal_Font">
                     <thead>
                        <tr>

                           <th>Employee ID</th>
                           <th>Employee name</th>
                           <th>Reason</th>
                           <th>Request date</th>
                           <th>Effective date</th>
                           <th>Requested Location</th>
                           <th>Status</th>
                           <th>Action</th>

                        </tr>
                     </thead>
                     <tbody>
                        @foreach($reschedules as $reschedule)
                        <tr>
                           <td>{{$show->unique_id}}</td>
                           <td>{{$show->first_name}}</td>
                           <td>{{$reschedule->reason->message}}</td>
                           <td>{{ $reschedule->created_at->format('Y-m-d') }}</td>
                           <td>{{ $reschedule->date }}</td>
                           <td>{{ $reschedule->address }}</td>

                           <td>
                              @if($reschedule->status == 0)

                              <span class="badge bg-soft-primary text-primary">Submitted</span>

                              @elseif($reschedule->status == 1)
                              <span class="badge bg-soft-success text-success">Accepted</span>
                              @elseif($reschedule->status == 2)
                              <span class="badge bg-soft-danger text-danger">Rejected</span>
                              @endif
                           </td>
                           <td>
                              @if($reschedule->status == 0)
                              <button style="border: none"><a href="{{ route('similarRoutes', ['id' => $reschedule->id]) }}"><i class="mdi mdi-check-circle"></i>Accept</a></button>&nbsp;&nbsp;&nbsp;
                              <button style="border: none"> <a href="{{ route('rejectReschedule', ['id' => $reschedule->id]) }}" style="color:red"><i class="mdi mdi-alpha-x-circle"></i>Reject</a></button>
                              @endif
                           </td>

                        </tr>
                        @endforeach
                     </tbody>
                  </table>
               </td>
            </tr>
         </table>

         <div class="row">
            <div class="col-xl-8 col-lg-7">
               <!-- project card -->

               <!-- <div class="card d-block">
                  <a href="{{route('editStaff',[$show->id])}}" style="float:right;margin-right:50px">Edit</a>
                  <div class="card-body">
                     <img class="pimage" src="{{url('/images')}}/{{@$show->profile_image}}" onerror="this.src='https://t3.ftcdn.net/jpg/03/45/05/92/240_F_345059232_CPieT8RIWOUk4JqBkkWkIETYAkmz2b75.jpg'" style="width: 100px;height:100px">
                     <p class="mt-3"><b>First Name :</b> {{$show->first_name}}</p>
                     <p class="mt-3"><b>Email :</b> {{$show->email}}</p>
                     <p class="mt-3"><b>Mobile :</b> {{$show->mobile}}</p>
                     <p class="mt-3"><b>Phone :</b> {{$show->phone}}</p>
                     <p class="mt-3"><b>Address :</b> {{$show->address}}</p>
                     <p class="mt-3"><b>DOB :</b> {{$show->dob}}</p>
                     <p class="mt-3"><b>Gender :</b> {{$show->gender}}</p>
                     <p class="mt-3"><b>Employment Type :</b> {{$show->employement_type}}</p>
                     <p class="mt-3"><b>Language Spoken :</b> {{$show->staff_language}}</p>
                     <p class="mt-3"><b>Role :</b> <span class="badge bg-soft-success text-success">{{ucfirst($show->roles[0]->name)}}</span></p>
                  </div>
               </div> -->
               <!-- end card-->
               <!-- end card-->
               <div class="card" style="display: none;">
                  <div class="card-body">
                     <h4>Compliance</h4>
                     <a href="{{route('staffDocuments',[$show->id])}}" style="float:right">Manage All</a>
                     <table class="table table-design-default">
                        <thead>
                           <tr>

                              <th>Category</th>
                              <th>Expire At</th>
                              <!-- <th>No Expireation</th> -->
                              <th>Last Update</th>
                              <th>Status</th>
                           </tr>
                        </thead>
                        <tbody>
                           @foreach($docoments as $key1=>$data)
                           <?php
                           // echo '<pre>';print_r($data->roles[0]->name);
                           ?>
                           <tr>

                              <td>
                                 {{$data->category}}
                              </td>




                              <td>
                                 @if($data->no_expireation == 0)
                                 {{date('d-m-Y',strtotime($data->expire))}}

                                 @else
                                 {{'.....'}}
                                 @endif
                              </td>

                              <td>{{date('d-m-Y',strtotime($data->updated_at))}}</td>

                              <td>
                                 @if($data->no_expireation == 1)

                                 <span class="badge bg-soft-success text-success">Active</span>

                                 @elseif(date('Y-m-d') >= $data->expire)
                                 <span class="badge bg-soft-danger text-danger">Expired</span>
                                 @else
                                 <span class="badge bg-soft-success text-success">Active</span>
                                 @endif
                              </td>

                           </tr>
                           @endforeach
                        </tbody>
                     </table>
                  </div>
                  <!-- end card body-->
               </div>
               <!-- end card -->
            </div>
            <!-- end col -->
            <div class="col-xl-4 col-lg-5" style="display: none;">
               <div class="card">
                  <div class="card-body">
                     <h5 class="card-title font-16 mb-3">Settings</h5>
                     <form action="{{url('users/settingsUpdate',[$show->id])}}" method="post">
                        @csrf
                        <div class="form-group">
                           <label>Roles</label>
                           <select name="role_id" class="form-control">
                              <option value="">Select</option>
                              @foreach($roles as $tm)
                              <option value="{{$tm->id}}" <?php if ($show->roles[0]->id == $tm->id) {
                                                               echo 'selected';
                                                            } ?>>{{$tm->name}}</option>
                              @endforeach
                           </select>
                        </div>
                        <div class="form-group">
                           <?php

                           $teamArray =    explode(',', $csetting->teams);
                           ?>

                           <label>Teams</label>
                           <select name="team[]" class="form-control" id="mySelect">
                              <option value="">Select</option>
                              @foreach($teams as $tm)
                              <option value="{{$tm->id}}" <?php if (in_array($tm->id, $teamArray)) {
                                                               echo 'selected';
                                                            } ?>>{{$tm->name}}</option>
                              @endforeach
                           </select>
                        </div>
                        <div class="form-group">
                           <label>Notify Timesheet Approval</label>
                           <input type="text" name="notify_timesheet_approval" placeholder="Notify Timesheet Approval " class="form-control" value="{{$csetting->notify_timesheet_approval}}">
                        </div>
                        <div class="form-group">
                           <label>Available For Rostering</label>
                           <input type="text" name="available_for_rostering" placeholder="Available For Rostering" class="form-control" value="{{$csetting->available_for_rostering}}">
                        </div>
                        <div class="form-group">
                           <label>Read and write private notes</label>
                           <input type="text" name="private_notes" placeholder="Read and write private notes" class="form-control" value="{{$csetting->private_notes}}">
                        </div>
                        <!-- <div class="form-group">
                           <label>Client Type</label>
                           <select name="client_type" class="form-control">
                              @foreach($clienttype as $cType)
                              <option value="{{$cType->id}}" <?php if ($csetting->client_type == $cType->id) {
                                                                  echo 'selected';
                                                               } ?>>{{$cType->plan_name}}</option>
                              @endforeach
                           </select>
                        </div>
                        <div class="form-group">
                           <label>Default Price Book</label>
                           <select name="price_book" class="form-control">
                              @foreach($priceBook as $pbook)
                              <option value="{{$pbook->id}}" <?php if ($csetting->price_book == $pbook->id) {
                                                                  echo 'selected';
                                                               } ?>>{{$pbook->name}}</option>
                              @endforeach
                           </select>
                        </div>
                        <div class="form-group">
                           <label>Teams</label>
                           <select name="team[]" class="form-control">
                              @foreach($teams as $tm)
                              <option value="{{$tm->id}}" >{{$tm->name}}</option>
                              @endforeach
                           </select>
                        </div> -->
                        <div class="form-group">
                           <label>No Access</label>
                           <input type="checkbox" name="no_access" placeholder="Share Progress Notes" class="no_access" value="<?php if ($csetting->no_access == 1) {
                                                                                                                                    echo 1;
                                                                                                                                 } else {
                                                                                                                                    echo 0;
                                                                                                                                 } ?>" <?php if ($csetting->no_access == 1) {
                                                                                                                                          echo 'checked';
                                                                                                                                       } ?>>
                        </div>
                        <div class="form-group">
                           <label>Account Owner </label>
                           <input type="checkbox" name="account_owner" placeholder="Enable SMS Reminders" class="account_owner" value="<?php if ($csetting->account_owner == 1) {
                                                                                                                                          echo 1;
                                                                                                                                       } else {
                                                                                                                                          echo 0;
                                                                                                                                       } ?>" <?php if ($csetting->account_owner == 1) {
                                                                                                                                                echo 'checked';
                                                                                                                                             } ?>>
                        </div>

                        <button type="submit" class="btn btn-primary" value="1" name="exit">Save</button>


                     </form>
                  </div>
               </div>
               <!-- New -->

               <!--  -->
               <div class="card" style="display: none;">
                  <div class="card-body">
                     <h5 class="card-title font-16 mb-3">Next of Kin</h5>
                     <form action="{{route('updateStaffKin',[$show->id])}}" method="post">
                        @csrf

                        <div class="form-group">
                           <label>Name</label>
                           <input type="text" name="name" placeholder="Enter Kin Name" class="form-control" value="{{$kin->name}}">

                        </div>

                        <div class="form-group">
                           <label>Relation</label>
                           <input type="text" name="relation" placeholder="Enter Kin Relation" class="form-control" value="{{$kin->relation}}">

                        </div>

                        <div class="form-group">
                           <label>Contact</label>
                           <input type="text" name="contact" placeholder="Enter Kin Contact" class="form-control" value="{{$kin->contact}}">

                        </div>

                        <div class="form-group">
                           <label>Email</label>
                           <input type="text" name="email" placeholder="Enter Kin Email" class="form-control" value="{{$kin->email}}">

                        </div>



                        <button type="submit" class="btn btn-primary" value="1" name="exit">Save</button>
                     </form>
                  </div>
               </div>
               <!--  -->


               <div class="card" style="display: none;">
                  <div class="card-body">
                     <h5 class="card-title font-16 mb-3">Payroll Settings</h5>
                     <form action="{{route('staffPayrollSettings',[$show->id])}}" method="post">
                        @csrf
                        <div class="form-group">
                           <label>Pay group</label>
                           <select name="pay_group" class="form-control">

                              <option value="Casual" <?php if ($adf->pay_group == 'Casual') {
                                                         echo 'selected';
                                                      } ?>>Casual</option>

                              <option value="Permanent Part Time" <?php if ($adf->pay_group == 'Permanent Part Time') {
                                                                     echo 'selected';
                                                                  } ?>>Permanent Part Time</option>

                           </select>
                        </div>
                        <!-- <div class="form-group">
                           <label>Allowances</label>
                           <input type="text" name="review_date" placeholder="Allowances" class="form-control" value="{{$adf->review_date}}" >
                        </div> -->

                        <div class="form-group">
                           <label>Daily hours</label>
                           <input type="number" name="daily_hours" placeholder="Daily hours" class="form-control" value="{{$adf->daily_hours}}">
                        </div>

                        <div class="form-group">
                           <label>Weekly hours</label>
                           <input type="number" name="weekly_hours" placeholder="Weekly hours" class="form-control" value="{{$adf->weekly_hours}}">
                        </div>

                        <div class="form-group">
                           <label>External System Identifier</label>
                           <input type="text" name="external_system_identifier" placeholder="MYOB Card ID or HR Employee ID" class="form-control" value="{{$adf->external_system_identifier}}">
                        </div>

                        <button type="submit" class="btn btn-primary" value="1" name="exit">Save</button>
                     </form>
                  </div>
               </div>

               <!--  -->
               <div class="card" style="display: none;">
                  <div class="card-body">
                     <h5 class="card-title font-16 mb-3">Notes</h5>
                     <form action="{{route('updateStaffNote',[$show->id])}}" method="post">
                        @csrf

                        <div class="form-group">
                           <label>Private Info</label>
                           <textarea name="private_info" placeholder="Enter Private Info" class="form-control">{{$stf->private_info}}</textarea>

                        </div>

                        <button type="submit" class="btn btn-primary" value="1" name="exit">Save</button>
                     </form>
                  </div>
               </div>
               <!--  -->

            </div>
         </div>
         <!-- end row -->



         <div class="card-footer">
            <div class="row">
               <div class="col-md-12" style="margin-left: 12px; margin-right:12px;">
                  <h4>Archive Staff</h4>
                  <div class="row">
                     <div class="col-md-12">
                        <div class="box box-solid">
                           <div class="box-header with-border"><i style="color:red">This will archive the Staff and you will not able to see Staff in your list. If you do wish to access the Staff, please go to Archive sub-menu.</i></div>
                           <div class="box-body"><a href="{{route('staffArchiveAccount',[$show->id])}}" class="btn btn-danger btn-md btn-flat" onclick="return confirm('Are you sure?')">Archive Staff</a></div>
                        </div>
                     </div>
                  </div>
               </div>
            </div>
            <!-- <button type="submit" class="btn btn-primary" value="1" name="exit">Save and Exit</button> -->
            <!-- <a href="javascript:;" class="btn btn-danger" onclick="history.back()" >Back</a> -->
         </div>
      </div>
      <!-- end col -->
   </div>
   <!-- end row -->
</div>
<!-- container -->
@endsection
@section('script')

<script type="text/javascript">
   var lastId,
      topMenu = $("#mainNav"),
      topMenuHeight = 100,
      // All list items
      menuItems = topMenu.find("a"),
      // Anchors corresponding to menu items
      scrollItems = menuItems.map(function() {
         var item = $($(this).attr("href"));
         if (item.length) {
            return item;
         }
      });
   // Bind click handler to menu items
   // so we can get a fancy scroll animation
   menuItems.click(function(e) {
      var href = $(this).attr("href"),
         offsetTop = href === "#" ? 0 : $(href).offset().top - topMenuHeight + 1;
      $('html, body').stop().animate({
         scrollTop: offsetTop
      }, 100);
      e.preventDefault();
   });


   // Bind to scroll
   $(window).scroll(function() {
      // Get container scroll position
      var fromTop = $(this).scrollTop() + topMenuHeight;

      // Get id of current scroll item
      var cur = scrollItems.map(function() {
         if ($(this).offset().top < fromTop)
            return this;
      });
      // Get the id of the current element
      cur = cur[cur.length - 1];
      var id = cur && cur.length ? cur[0].id : "";
      if (lastId !== id) {
         lastId = id;
         // Set/remove active class
         menuItems
            .parent().removeClass("activeli")
            .end().filter("a[href='#" + id + "']").parent().addClass("activeli");
      }
   });
   $(document).ready(function() {



      $('.no_access').click(function() {
         if ($('.no_access').prop('checked')) {
            $(this).val(1);
         } else {
            $(this).val(0);
         }
      });


      $('.account_owner').click(function() {
         if ($('.account_owner').prop('checked')) {
            $(this).val(1);
         } else {
            $(this).val(0);
         }
      });


      $('.invoice_travel').click(function() {
         if ($('.invoice_travel').prop('checked')) {
            $(this).val(1);
         } else {
            $(this).val(0);
         }
      });

      $("#info").datepicker();
      $("#datepicker").datepicker({
         maxDate: 0,
         dateFormat: 'dd-mm-yy'
      });
      $('.phone').keyup(function(e) {
         if (/\D/g.test(this.value)) {
            // Filter non-digits from input value.
            this.value = this.value.replace(/\D/g, '');
         }
      });

      $("input[type='checkbox']").click(function() {
         if ($(this).is(':checked')) {

            $('.salu').prop('disabled', false);
         } else {

            $('.salu').prop('disabled', true);
         }
      });


      // on change type ans show roles
      $('.type').change(function() {
         var curretValue = $(this).val();
         if (curretValue == 'Office User') {
            $('.role').show();
         } else {
            $('.role').hide();
         }
      });

      // when clickbox
      // $("input[type='checkbox']").click(function() { 
      // $('.type').change(function(){

      // });​

   });

   function noteform() {
      document.getElementById("noteform").style.display = "block";
      document.getElementById("noteView").style.display = "none";

   }

   function noteformCancel() {
      document.getElementById("noteform").style.display = "none";
      document.getElementById("noteView").style.display = "block";

   }

   function Payrollform() {
      document.getElementById("Payrollform").style.display = "block";
      document.getElementById("PayrollView").style.display = "none";

   }

   function PayrollformCancel() {
      document.getElementById("Payrollform").style.display = "none";
      document.getElementById("PayrollView").style.display = "grid";

   }

   function settingform() {
      document.getElementById("settingform").style.display = "block";
      document.getElementById("settingView").style.display = "none";

   }

   function settingformCancel() {
      document.getElementById("settingform").style.display = "none";
      document.getElementById("settingView").style.display = "grid";

   }
</script>
@endsection