@extends('layouts.vertical', ['title' => 'Add Staff'])
@section('css')
<!-- Plugins css -->
<link href="{{asset('assets/libs/ladda/ladda.min.css')}}" rel="stylesheet" type="text/css" />
@endsection
@section('content')
<!-- Start Content-->
<div class="container-fluid">
   <!-- start page title -->
   <div class="row">
      <div class="col-12">
         <div class="page-title-box">
            <div class="page-title-right">
               <ol class="breadcrumb m-0">
                  <li class="breadcrumb-item"><a href="javascript: void(0);">Home</a></li>
                  <li class="breadcrumb-item"><a href="javascript: void(0);">Settings</a></li>
               </ol>
            </div>
            <h4 class="page-title">Settings</h4>
         </div>
      </div>
   </div>
   <!-- end page title -->
   @if ($message = Session::get('success'))  
   <div class="alert alert-success alert-block">  
      <button type="button" class="close" data-dismiss="alert">X</button>   
      <strong>{{ $message }}</strong>  
   </div>
   @endif 
   @if ($message = Session::get('warning'))  
   <div class="alert alert-danger alert-block">  
      <button type="button" class="close" data-dismiss="alert">X</button>   
      <strong>{{ $message }}</strong>  
   </div>
   @endif 
   <div class="row">
      <div class="col-md-4">
         <div class="card">
            <div class="card-body">
               <h4 class="header-title">Company details </h4>
               <form action="{{route('update.company')}}" method="post" id="bs6s" enctype='multipart/form-data'>
                  @csrf
                  <div class="form-group">
                     <label>Name</label>
                     <input type="text" class="form-control" name="name" value="{{@$companyDetails->name}}">
                  </div>
                  <p>Country <span style="float:right">Australia</span></p>
                  <div class="form-group">
                     <label>Logo</label>
                     <input type="file" class="form-control" name="file">
                     <img src="{{url('/images')}}/{{@$companyDetails->logo}}" onerror="this.src='https://t3.ftcdn.net/jpg/03/45/05/92/240_F_345059232_CPieT8RIWOUk4JqBkkWkIETYAkmz2b75.jpg'" style="width: 100px;height:100px">
                  </div>
                  <input type="submit" class="btn btn-success" >
               </form>
            </div>
            <!-- end card-body-->
         </div>
         <!-- end card-->
      </div>
      <!-- end col -->
      <div class="col-md-4">
         <div class="card">
            <div class="card-body">
               <h4 class="header-title">Notes Permissions </h4>
               <form action="{{route('update.notePermission')}}" method="post" id="bs6">
                  @csrf
                  <div class="form-group">
                     <label>Allow note edit</label>
                     <select name="note_edit" class="form-control np1">
                        <option value="">Select</optopn>
                        <option value="yes" <?php if(@$nP->note_edit == 'yes'){ echo 'selected'; } ?>>Yes</option>
                        <option value="no" <?php if(@$nP->note_edit == 'no'){ echo 'selected'; } ?>>No</option>
                     </select>
                  </div>
                  <div class="form-group">
                     <label>Hide client notes and documents to staff unscheduled for</label>
                     <input type="text" class="form-control expire_access1" name="expire_access" value="{{@$nP->expire_access}}">
                  </div>

                  <input type="submit" class="btn btn-success" >
               </form>
            </div>
            <!-- end card-body-->
         </div>
         <!-- end card-->
      </div>
      <!-- end col -->
      <div class="col-md-4">
         <div class="card">
            <div class="card-body">
               <h4 class="header-title">Time and Attendence </h4>
               <form action="{{route('update.time.attendence')}}" method="post" id="bs5">
                  @csrf
                  <div class="form-group">
                     <label>Enable unavailability</label>
                     <select name="enable_unavailability" class="form-control eu1">
                        <option value="">Select</optopn>
                        <option value="yes" <?php if(@$tA->enable_unavailability == 'yes'){ echo 'selected'; } ?>>Yes</option>
                        <option value="no" <?php if(@$tA->enable_unavailability == 'no'){ echo 'selected'; } ?>>No</option>
                     </select>
                  </div>
                  <p>Unavailability notice period <span style="float:right">{{@$tA->notice_preiod}}</span></p>
                  <div class="form-group">
                     <label>Clockin location check</label>
                     <select name="location_check" class="form-control eu1">
                        <option value="">Select</optopn>
                        <option value="yes" <?php if(@$tA->location_check == 'yes'){ echo 'selected'; } ?>>Yes</option>
                        <option value="no" <?php if(@$tA->location_check == 'no'){ echo 'selected'; } ?>>No</option>
                     </select>
                  </div>
                  <p>Attendance threshold in minutes <span style="float:right">{{@$tA->attendance_threshold}}</span></p>
                  <div class="form-group">
                     <label>Auto approve shift if clockin/out were successful</label>
                     <select name="auto_approve_shift" class="form-control eu1">
                        <option value="">Select</optopn>
                        <option value="yes" <?php if(@$tA->auto_approve_shift == 'yes'){ echo 'selected'; } ?>>Yes</option>
                        <option value="no" <?php if(@$tA->auto_approve_shift == 'no'){ echo 'selected'; } ?>>No</option>
                     </select>
                  </div>
                  <p>Timesheet precision <span style="float:right">{{@$tA->timesheet_precision}}</span></p>
                  <p>Pay rate is based on <span style="float:right">{{@$tA->pay_rate}}</span></p>
                  <div class="form-group">
                     <label>Clockin alert</label>
                     <select name="clockin_alert" class="form-control eu1">
                        <option value="">Select</optopn>
                        <option value="yes" <?php if(@$tA->clockin_alert == 'yes'){ echo 'selected'; } ?>>Yes</option>
                        <option value="no" <?php if(@$tA->clockin_alert == 'no'){ echo 'selected'; } ?>>No</option>
                     </select>
                  </div>
                  <p>Clockin alert message <span style="float:right">{{@$tA->pay_rate}}</span></p>
                  <input type="submit" class="btn btn-success" >
               </form>
            </div>
            <!-- end card-body-->
         </div>
         <!-- end card-->
      </div>
      <!-- end col -->
      <div class="col-md-4" id="schedule">
         <div class="card">
            <div class="card-body">
               <h4 class="header-title">Schedular </h4>
               <h4 class="header-title">Client Types <a href="javascript:;" data-toggle="modal" data-target="#clientType"  style="float: right;">+Add</a></h4>
               @foreach($cleintTypes as $sub)
               <a class="btn btn-outline-primary waves-effect waves-light"   href="javascript:;" role="button" aria-controls="offcanvasExample">
               {{$sub->name}} <span table-name='{{$sub->getTable()}}' rel="{{$sub->id}}" class="btn-label-right deleteCategory"><i class="mdi mdi-close-circle-outline"></i></span>
               </a>
               @endforeach
               <form action="{{route('updateSettings')}}" method="post" id="bs4">
                  @csrf
                  <input type="hidden" name="redirect" value="schedule" >
                  <div class="form-group">
                     <label>Timezone</label>
                     <select name="timezone" class="form-control timezone">
                        <option value="">Select</optopn>
                           @foreach($timezones as $time)
                        <option value="{{$time->timezone}}" <?php if(@$settings->timezone == $time->timezone){ echo 'selected'; } ?>>{{$time->timezone}}</option>
                        @endforeach
                     </select>
                  </div>
                  <div class="form-group">
                     <label>Minute Interval</label>
                     <select name="minute_interval" class="form-control timezone">
                        <option value="">Select</optopn>
                        <option value="1" <?php if(@$settings->minute_interval == 1){ echo 'selected'; } ?>>1</option>
                        <option value="5" <?php if(@$settings->minute_interval == 5){ echo 'selected'; } ?>>5</option>
                        <option value="15" <?php if(@$settings->minute_interval == 15){ echo 'selected'; } ?>>15</option>
                     </select>
                  </div>
                  <div class="form-group">
                     <label>Pay Run</label>
                     <select name="pay_run" class="form-control timezone">
                        <option value="">Select</optopn>
                        <option value="weekly" <?php if(@$settings->pay_run == 'weekly'){ echo 'selected'; } ?>>Weekly</option>
                        <option value="fortnightly" <?php if(@$settings->pay_run == 'fortnightly'){ echo 'selected'; } ?>>Fortnightly</option>
                     </select>
                  </div>
                  <!-- Manage Shift -->
                  <div class="form-group">
                     <label>First day of week</label>
                     <input type="text" name="first_day_fortnight" class="form-control" id="datepicker" value="{{date('Y-m-d', strtotime(@$settings->first_day_fortnight))}}">
                  </div>
                  <!-- Manage Shift -->
                  <div class="form-group">
                     <label>Carer can manage shifts</label>
                     <select name="manage_shift" class="form-control timezone">
                        <option value="">Select</optopn>
                        <option value="yes" <?php if(@$settings->pay_run == 'yes'){ echo 'selected'; } ?>>Yes</option>
                        <option value="no" <?php if(@$settings->pay_run == 'no'){ echo 'selected'; } ?>>No</option>
                     </select>
                  </div>
               </form>
            </div>
            <!-- end card-body-->
         </div>
         <!-- end card-->
      </div>
      <!-- end col -->
      <div class="col-md-4">
         <div class="card">
            <div class="card-body">
               <h4 class="header-title">Shift types <a href="{{route('shift-type.index')}}" style="float: right;">+Add</a></h4>
               @foreach($shiftTypes as $sub)
               <style>
                  .dot1 {
                  height: 9px;
                  width: 10px;
                  border-radius: 50%;
                  display: inline-block;
                  margin-left: -7px;
                  }
               </style>
               <a class="btn btn-outline-primary waves-effect waves-light"   href="javascript:;" role="button" aria-controls="offcanvasExample">
               <span style="background-color: <?=$sub->color?>" class="dot1"></span>{{$sub->name}}
               </a>
               @endforeach
            </div>
            <!-- end card-body-->
         </div>
         <!-- end card-->
      </div>
      <!-- end col -->
      <div class="col-md-4">
         <div class="card">
            <div class="card-body">
               <h4 class="header-title">Client public information headings</h4>
               <h5>Need to know information <a href="{{route('Need to know information')}}"   style="float: right;display:none1">Manage</a></h5>
               @foreach($needInfo as $sub)
               <a class="btn btn-outline-primary waves-effect waves-light"   href="javascript:;" role="button" aria-controls="offcanvasExample">
               {{$sub->heading}}
               </a>
               @endforeach
               <h5>Useful information <a href="{{route('Useful information')}}"   style="float: right;display:none1">Manage</a></h5>
               @foreach($useInfo as $sub)
               <a class="btn btn-outline-primary waves-effect waves-light"   href="javascript:;" role="button" aria-controls="offcanvasExample">
               {{$sub->heading}}
               </a>
               @endforeach
            </div>
            <!-- end card-body-->
         </div>
         <!-- end card-->
      </div>
      <!-- end col -->
      <div class="col-md-4">
         <div class="card">
            <div class="card-body">
               <h4 class="header-title">Notes headings <a href="javascript:;" data-toggle="modal" data-target="#holiday" style="float: right;display:none">+Add</a></h4>
               <h5>Progress Notes <a href="{{route('Progress Notes')}}"   style="float: right;display:none1">Manage</a></h5>
               @foreach($pNotes as $sub)
               <a class="btn btn-outline-primary waves-effect waves-light"   href="javascript:;" role="button" aria-controls="offcanvasExample">
               {{$sub->heading}}
               </a>
               @endforeach
               <h5>Feedback <a href="{{route('Feedback')}}"   style="float: right;display:none1">Manage</a></h5>
               @foreach($fNotes as $sub)
               <a class="btn btn-outline-primary waves-effect waves-light"   href="javascript:;" role="button" aria-controls="offcanvasExample">
               {{$sub->heading}}
               </a>
               @endforeach
               <h5>Incident <a href="{{route('Incident')}}"   style="float: right;display:none1">Manage</a></h5>
               @foreach($inc as $sub)
               <a class="btn btn-outline-primary waves-effect waves-light"   href="javascript:;" role="button" aria-controls="offcanvasExample">
               {{$sub->heading}}
               </a>
               @endforeach
               <h5>Enquiry <a href="{{route('Enquiry')}}"   style="float: right;display:none1">Manage</a></h5>
               @foreach($enq as $sub)
               <a class="btn btn-outline-primary waves-effect waves-light"   href="javascript:;" role="button" aria-controls="offcanvasExample">
               {{$sub->heading}}
               </a>
               @endforeach
            </div>
            <!-- end card-body-->
         </div>
         <!-- end card-->
      </div>
      <!-- end col -->
      <div class="col-md-4" id="cd">
         <div class="card">
            <div class="card-body">
               <h4 class="header-title">Client Document Categories <a href="javascript:;" data-toggle="modal" data-target="#centermodal" style="float: right;">+Add</a></h4>
               @foreach($docCategories as $cat) 
               <?php
                  //dd($cat->getTable());
                  ?>
               <a class="btn btn-outline-primary waves-effect waves-light"   href="javascript:;" role="button" aria-controls="offcanvasExample">
               {{$cat->category_name}} <span table-name='{{$cat->getTable()}}' rel="{{$cat->id}}" class="btn-label-right deleteCategory"><i class="mdi mdi-close-circle-outline"></i></span>
               </a>
               @endforeach
            </div>
            <!-- end card-body-->
         </div>
         <!-- end card-->
      </div>
      <!-- end col -->
      <div class="col-md-4" id="qc">
         <div class="card">
            <div class="card-body">
               <h4 class="header-title">Carer competency & qualification categories <a href="javascript:;" data-toggle="modal" data-target="#qualification" style="float: right;">+Add</a></h4>
               @foreach($qualificationCategory as $cat) 
               <a class="btn btn-outline-primary waves-effect waves-light"   href="javascript:;" role="button" aria-controls="offcanvasExample">
               {{$cat->category_name}} <span table-name='{{$cat->getTable()}}' rel="{{$cat->id}}" class="btn-label-right deleteCategory"><i class="mdi mdi-close-circle-outline"></i></span>
               </a>
               @endforeach
            </div>
            <!-- end card-body-->
         </div>
         <!-- end card-->
      </div>
      <!-- end col -->
      <div class="col-md-4" id="rh">
         <div class="card">
            <div class="card-body">
               <h4 class="header-title">Report Headings <a href="javascript:;" data-toggle="modal" data-target="#reportheading" style="float: right;">+Add</a></h4>
               @foreach($reportHeadingCategory as $cat) 
               <?php 
                  //echo  '<pre>';print_r($cat->catHeadings);
                  
                  ?>
               <a class=""   href="javascript:;" role="button" aria-controls="offcanvasExample">
               {{$cat->category_name}}  <a data="{{$cat->id}}" href="javascript:;" data-toggle="modal" data-target="#reportheadingN" style="float: right;" class="cadd">+Add</a>
               </a><br>
               @foreach($cat->catHeadings as $sub)
               <a class="btn btn-outline-primary waves-effect waves-light"   href="javascript:;" role="button" aria-controls="offcanvasExample">
               {{$sub->name}} <span table-name='{{$sub->getTable()}}' rel="{{$sub->id}}" class="btn-label-right deleteCategory"><i class="mdi mdi-close-circle-outline"></i></span>
               </a>
               @endforeach
               @endforeach
            </div>
            <!-- end card-body-->
         </div>
         <!-- end card-->
      </div>
      <!-- end col -->
      <div class="col-md-4" id="ph">
         <div class="card">
            <div class="card-body">
               <h4 class="header-title">Public Holiday <a href="javascript:;" data-toggle="modal" data-target="#holiday" style="float: right;">+Add</a></h4>
               @foreach($holiday as $sub)
               <a class="btn btn-outline-primary waves-effect waves-light"   href="javascript:;" role="button" aria-controls="offcanvasExample">
               {{date('d-m-Y', strtotime($sub->date))}} <span table-name='{{$sub->getTable()}}' rel="{{$sub->id}}" class="btn-label-right deleteCategory"><i class="mdi mdi-close-circle-outline"></i></span>
               </a>
               @endforeach
            </div>
            <!-- end card-body-->
         </div>
         <!-- end card-->
      </div>
      <!-- end col -->
   </div>
</div>
<!-- container -->
</div> <!-- content -->
<!-- Add Client document categories -->
<div class="modal fade" id="centermodal" tabindex="-1" role="dialog" aria-hidden="true">
   <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content">
         <div class="modal-header">
            <h4 class="modal-title" id="myCenterModalLabel">Client Document Categories</h4>
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
         </div>
         <div class="modal-body">
            <form action="{{route('uploadDocCategoty')}}" method='post'>
               @csrf
               <input type="hidden" name="redirect" value="cd">
               <div class="form-group">
                  <label>Document Category Name <code>*</code></label>
                  <input type="text" name="category_name" class="form-control" placeholder="Document Category Name" required>
               </div>
               <div class="form-group">
                  <button type="submit" class="ladda-button  btn btn-primary" dir="ltr" data-style="slide-left">Submit
                  </button>
               </div>
            </form>
         </div>
      </div>
      <!-- /.modal-content -->
   </div>
   <!-- /.modal-dialog -->
</div>
<!-- /.modal -->
<!-- Add Qualification categories -->
<div class="modal fade" id="qualification" tabindex="-1" role="dialog" aria-hidden="true">
   <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content">
         <div class="modal-header">
            <h4 class="modal-title" id="myCenterModalLabel">Qualification Categories</h4>
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
         </div>
         <div class="modal-body">
            <form action="{{route('uploadQualificationCategoty')}}" method='post'>
               @csrf
               <input type="hidden" name="redirect" value="qc">
               <div class="form-group">
                  <label>Qualification Category Name <code>*</code></label>
                  <input type="text" name="category_name" class="form-control" placeholder="Qualification Category Name" required>
               </div>
               <div class="form-group">
                  <button type="submit" class="ladda-button  btn btn-primary" dir="ltr" data-style="slide-left">Submit
                  </button>
               </div>
            </form>
         </div>
      </div>
      <!-- /.modal-content -->
   </div>
   <!-- /.modal-dialog -->
</div>
<!-- /.modal -->
<!-- Add Report heading -->
<div class="modal fade" id="reportheading" tabindex="-1" role="dialog" aria-hidden="true">
   <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content">
         <div class="modal-header">
            <h4 class="modal-title" id="myCenterModalLabel">Report headings</h4>
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
         </div>
         <div class="modal-body">
            <form action="{{route('uploadReportHeading')}}" method='post'>
               @csrf
               <input type="hidden" name="redirect" value="rh">
               <div class="form-group">
                  <label>Report Heading Name<code>*</code></label>
                  <input type="text" name="category_name" class="form-control" placeholder="Report Heading Name" required>
               </div>
               <div class="form-group">
                  <button type="submit" class="ladda-button  btn btn-primary" dir="ltr" data-style="slide-left">Submit
                  </button>
               </div>
            </form>
         </div>
      </div>
      <!-- /.modal-content -->
   </div>
   <!-- /.modal-dialog -->
</div>
<!-- /.modal -->
<!-- Add Report heading Category-->
<div class="modal fade" id="reportheadingN" tabindex="-1" role="dialog" aria-hidden="true">
   <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content">
         <div class="modal-header">
            <h4 class="modal-title" id="myCenterModalLabel">Add Report heading</h4>
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
         </div>
         <div class="modal-body">
            <form action="{{route('uploadReportHeadings')}}" method='post'>
               @csrf
               <input type="hidden" name="redirect" value="rh">
               <div class="form-group">
                  <label>Report Heading Name<code>*</code></label>
                  <input type="text" name="name" class="form-control" placeholder="Report Heading Name" required>
               </div>
               <input type="hidden" name="category_id"  class="cId">
               <div class="form-group">
                  <button type="submit" class="ladda-button  btn btn-primary" dir="ltr" data-style="slide-left">Submit
                  </button>
               </div>
            </form>
         </div>
      </div>
      <!-- /.modal-content -->
   </div>
   <!-- /.modal-dialog -->
</div>
<!-- /.modal -->
<!-- Add Public Holiday-->
<div class="modal fade" id="holiday" tabindex="-1" role="dialog" aria-hidden="true">
   <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content">
         <div class="modal-header">
            <h4 class="modal-title" id="myCenterModalLabel">Add Public Holiday</h4>
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
         </div>
         <div class="modal-body">
            <form action="{{route('uploadPublicHoliday')}}" method='post'>
               @csrf
               <input type="hidden" name="redirect" value="ph">
               <div class="form-group">
                  <label>Public Holiday Date<code>*</code></label>
                  <input type="text" name="date" class="form-control datepicker1"  id="d"  required="" placeholder="dd-mm-YYYY">
               </div>
               <div class="form-group">
                  <button type="submit" class="ladda-button  btn btn-primary" dir="ltr" data-style="slide-left">Submit
                  </button>
               </div>
            </form>
         </div>
      </div>
      <!-- /.modal-content -->
   </div>
   <!-- /.modal-dialog -->
</div>
<!-- /.modal -->
<!-- Schedular -->
<div class="modal fade" id="clientType" tabindex="-1" role="dialog" aria-hidden="true">
   <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content">
         <div class="modal-header">
            <h4 class="modal-title" id="myCenterModalLabel">Add Client types</h4>
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
         </div>
         <div class="modal-body">
            <form action="{{route('clientType.store')}}" method='post'>
               @csrf
               <input type="hidden" name="redirect" value="schedule">
               <div class="form-group">
                  <label>Add Client types<code>*</code></label>
                  <input type="text" name="name" class="form-control" placeholder="Client types" required>
               </div>
               <div class="form-group">
                  <button type="submit" class="ladda-button  btn btn-primary" dir="ltr" data-style="slide-left">Submit
                  </button>
               </div>
            </form>
         </div>
      </div>
      <!-- /.modal-content -->
   </div>
   <!-- /.modal-dialog -->
</div>
<!-- /.modal -->
@endsection
@section('script')
<script src="{{asset('assets/libs/ladda/ladda.min.js')}}"></script>
<!-- Page js-->
<script src="{{asset('assets/js/pages/loading-btn.init.js')}}"></script>
<link rel="stylesheet" href="//code.jquery.com/ui/1.13.2/themes/base/jquery-ui.css">
<script type="text/javascript">
   $(document).ready(function(){
   
     $('.timezone, #datepicker').change(function(){
         $('#bs4').submit();
     });
     $('.eu').change(function(){
         $('#bs5').submit();
     });
     
     $('.np').change(function(){
         $('#bs6').submit();
     });
     $('.expire_access').keyup(function(){
         $('#bs6').submit();
     });
   
   
   $( ".datepicker" ).datepicker({dateFormat: 'dd-mm-yy' });
    $( "#datepicker" ).datepicker({dateFormat: 'dd-mm-yy' });
     
    $(".cadd").click(function(){
       var Id =  $(this).attr('data');
        $('.cId').val(Id);
    });
   
    
    $( "#datepicker" ).datepicker({  maxDate: 0,dateFormat: 'dd-mm-yy' });
          $('.phone').keyup(function(e){
             if (/\D/g.test(this.value))
             {
                // Filter non-digits from input value.
                this.value = this.value.replace(/\D/g, '');
             }
       });
   
       $(".deleteCategory").click(function(){
          var attrv = $(this).attr('rel');
          var tblName = $(this).attr('table-name');
          //alert(tblName);
          if (! confirm('Are u sure you want to delete?')) { return false; }
          var url = "<?php echo url('/users/deleteCategory');?>/"+attrv+"/"+tblName;
           
          window.location.href = url;
       });
   
   
       // on change type ans show roles
       $('.type').change(function(){
          var curretValue =  $(this).val();
          if(curretValue == 'Office User'){
             $('.role').show();
          }else{
             $('.role').hide();
          }
       });
   
       // when clickbox
      // $("input[type='checkbox']").click(function() { 
       // $('.type').change(function(){
      
       // });​
       
   });
</script>
@endsection