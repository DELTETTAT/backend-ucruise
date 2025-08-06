@extends('layouts.vertical', ['title' => 'Edit Reminder'])
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
                  <li class="breadcrumb-item"><a href="javascript: void(0);">Reminders</a></li>
                  
               </ol>
            </div>
            <h4 class="page-title">Edit Reminder</h4>
         </div>
      </div>
   </div>
   <!-- end page title --> 
   <div class="row">
      <div class="col-lg-12 card">
         @if ($errors->any())
         <div class="alert alert-danger">
            <ul>
               @foreach ($errors->all() as $error)
               <li>{{ $error }}</li>
               @endforeach
            </ul>
         </div>
         @endif
         
         <form method="POST" action="{{ route('reminder.update',[$edit->id])}}" enctype="multipart/form-data" name="gift_store">
            {{ csrf_field() }}
            <div class="card-body">
               <div class="form-group">
                  <div class="row">

                     

                      <div class="col-sm-6 mt-2">
                        <label for="exampleInputName1">Target <code>*</code></label>
                           <select data-toggle="tooltip" data-placement="top" data-title="Choose Reminder target" class="form-control" name="target" id="reminder_target" data-original-title="" title="">
                           <option selected="selected" value="driver" {{$edit->target == 'driver' ?  'selected' : ''}} >Driver</option>
                           <option value="staff" {{$edit->target == 'staff' ?  'selected' : ''}} >Staff</option>
                           </select>
                     </div>

                     <div class="col-sm-6 mt-2">
                     <label for="exampleInputName1">Date <code>*</code></label>
                     <input type="tex" name="date" class="form-control"   required="" placeholder="days" value="{{$edit->date}}"> 
                         
                     </div>

                     <div class="col-sm-6 mt-2">
                     <label for="exampleInputName1">Title <code>*</code>&nbsp;<a href="javascript:;" data-toggle="modal" data-target="#details"  style="float: right;"><i class="fe-info"></i></a></label>
                      <textarea class="form-control" name="content" required="">{{$edit->content}}</textarea>
                         
                     </div>
                     <div class="col-sm-6 mt-2">
                     <label for="exampleInputName1">description <code>*</code></label>
                      <textarea class="form-control" name="description" required="" >{{$edit->description}}</textarea>
                         
                     </div>

                      
                  </div>
               </div>
            </div>
      </div>
      <div class="card-footer">
      <button type="submit" class="btn btn-primary" value="1" name="exit">Save and Exit</button>
       
      <a href="javascript:;" class="btn btn-danger" onclick="history.back()" >Back</a>
      </div>
      </form>
   </div>
   <!-- end col -->
</div>
<!-- end row -->
</div> <!-- container -->

<div class="modal fade" id="details" tabindex="-1" role="dialog"
   aria-labelledby="scrollableModalTitle" aria-hidden="true">
   <div class="modal-dialog modal-dialog-scrollable" role="document">
      <div class="modal-content">
         <div class="modal-header">
            <h5 class="modal-title" id="scrollableModalTitle">Description tags</h5>
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
            </button>
         </div>
         <div class="modal-body">
            <div class="popover-content">
            <p>Following tags can be used in content template</p>
            <b>{client_name}</b>   - The client's Display Name<br> <b>{client_date}</b>   - Date the client is scheduled to start the shift<br> <b>{client_time}</b>   - The start and end time the client is scheduled on the shift<br> <b>{shift_address}</b> - Location of shift<br> <b>{shift_date}</b>    - Date of shift<br> <b>{shift_time}</b>    - Time of shift<br> <b>{shift_start_time}</b>    - Start time of shift<br> <b>{shift_end_time}</b>    - End time of shift<br> <b>{shift_type}</b>    - Type of shift<br> <b>{staff_name}</b>    - Name of staff<br>
            </div>
         </div>
      </div>
      <!-- /.modal-content -->
   </div>
   <!-- /.modal-dialog -->
</div>
<!-- /.modal -->
@endsection
@section('script')

<link rel="stylesheet" href="//code.jquery.com/ui/1.13.2/themes/base/jquery-ui.css">
 


<script type="text/javascript">
  $(document).ready(function(){
   $( "#datepicker" ).datepicker({  maxDate: 0,dateFormat: 'dd-mm-yy' });
         $('.phone').keyup(function(e){
            if (/\D/g.test(this.value))
            {
               // Filter non-digits from input value.
               this.value = this.value.replace(/\D/g, '');
            }
      });

      $("input[type='checkbox']").click(function(){
         if ($(this).is(':checked')) {
         
            $('.salu').prop('disabled', false);
         } else {
              
            $('.salu').prop('disabled', true);
         }
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