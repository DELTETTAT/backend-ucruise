@extends('layouts.vertical', ['title' => 'List Staff Documents'])
@section('css')
<!-- Plugins css -->
<link href="{{asset('assets/libs/datatables/datatables.min.css')}}" rel="stylesheet" type="text/css" />
@endsection
@section('content')
<?php error_reporting(0); ?>
<!-- Start Content-->
<div class="container-fluid">
   <!-- start page title -->
   <div class="row">
      <div class="col-12">
         <div class="page-title-box">
            <div class="page-title-right">
               <ol class="breadcrumb m-0">
                  <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Home</a></li>
                  <li class="breadcrumb-item active">List Staff Documents </li>
               </ol>
            </div>
            <h4 class="page-title"><a href="{{route('staffDetails',[$cId])}}"><i data-feather="arrow-left"></i></a>List Staff Documents</h4>
         </div>
      </div>
   </div>
   <!-- end page title -->
   <div class="row">
      <div class="col-12">
         @if(Session::has('message'))
         <p class="alert alert-success">{{ Session::get('message') }}</p>
         @endif
         @if ($errors->any())
         <div class="alert alert-danger">
            <ul>
               @foreach ($errors->all() as $error)
               <li>{{ $error }}</li>
               @endforeach
            </ul>
         </div>
         @endif
         @if ($message = Session::get('warning'))
         <div class="alert alert-danger alert-block">
            <button type="button" class="close" data-dismiss="alert">X</button>
            <strong>{{ $message }}</strong>
         </div>
         @endif
         <style>
            .ui-datepicker {
               z-index: 9999 !important;
            }

            .modal {
               z-index: 9000 !important;
            }
         </style>
         <div class="card">
            <div class="card-body">
               <?php
               // <a href="javascript:;" data-toggle="modal" data-target="#sendEmail" class="btn btn-info" style="float: left;">Send Bulk Email</a>
               // <a href="{{route('listEmails')}}" class="btn btn-info dd" style="float: left;">List Emails</a>

               // <a href="javascript:;" data-toggle="modal" data-target="#sendSMS" class="btn btn-info sms" style="float: left;">Send SMS</a>
               // <a href="{{route('listSMS')}}" class="btn btn-info dd" style="float: left;">List SMS</a>
               // <a href="{{ url('users/add-staff') }}" class="btn btn-info" style="float: left;">Send Bulk SMS</a>
               // <a href="{{ route('exportStaff') }}" class="btn btn-info" style="float: right;">Export Data</a>
               // <a href="javascript:;" class="btn btn-success adstaff" style="float: right;" data-toggle="modal" data-target="#doc">Add Document</a>
               ?>
               <br>
               <br>
               <br>
               <table id="basic-datatable" class="table dt-responsive table-hover table-bordered nowrap w-100">
                  <thead>
                     <tr>
                        <th>#</th>
                        <th>Type</th>
                        <th>Name</th>
                        <th>Category</th>
                        <th>Staff Visibility</th>
                        <th>Expires</th>
                        <th>No Expiration</th>
                        <th>Last Update</th>
                        <th>Action</th>
                     </tr>
                  </thead>
                  <tbody>
                     @foreach($docoments as $key1=>$data)
                     <?php
                     // echo '<pre>';print_r($data->roles[0]->name);
                     ?>
                     <tr>
                        <td>{{$key1+1}}</td>
                        <td>
                           @if($data->type == 'png' || $data->type == 'jpg' || $data->type == 'jpeg')
                           <i data-feather="image" aria-hidden="true"></i>
                           @else
                           <i data-feather="file-text" aria-hidden="true"></i>
                           @endif
                        </td>
                        <td>{{@$data->name}}</td>
                        <td>
                           <a href="javascript:;" data-toggle="modal" data-target="#category{{$key1}}">
                              @if($data->category == "")
                              {{'........'}}
                              @else
                              {{$data->category}}
                              @endif
                        </td>
                        <td style="text-align:center">
                           <a href="javascript:;" data-toggle="modal" data-target="#staff{{$key1}}">
                              @if($data->staff_visibleity == "")
                              {{'........'}}
                              @else
                              {{$data->staff_visibleity}}
                              @endif
                        </td>
                        <td>
                           <a href="javascript:;" data-toggle="modal" data-target="#expiree{{$key1}}">
                              @if($data->expire == "")
                              {{'........'}}
                              @elseif($data->no_expireation == 1)
                              @else
                              {{date('d-m-Y',strtotime($data->expire))}}
                              @endif
                        </td>
                        <td style="text-align:center">
                           <form action="{{route('updateStaffNoExpireation',[$data->id])}}" method='post' enctype="multipart/form-data" class="check{{$key1}}">
                              @csrf
                              <input class="checkbox" type="checkbox" data="{{$key1}}" name="no_expireation" value="<?php if ($data->no_expireation == 1) {
                                                                                                                        echo 1;
                                                                                                                     } else {
                                                                                                                        echo 0;
                                                                                                                     } ?>" <?php if ($data->no_expireation == 1) {
                                                                                                                                                                                          echo 'checked';
                                                                                                                                                                                       } ?>>
                           </form>
                        </td>
                        <td>{{date('d-m-Y',strtotime($data->updated_at))}}</td>
                        </td>
                        <td style="display:none1">
                           <ul style="padding: initial;">
                              <li title="Edit" style="display:inline;"><a href="javascript:;" class=""><i class="fa fa-edit" style="cursor: pointer;" data-toggle="modal" data-target="#edit{{$key1}}"></i></a></li>
                              <li title="Delete" style="display:inline-block;"><a href="<?php echo url('/users/deleteCategory'); ?>/{{$data->id}}/{{$data->getTable()}}" class="" onclick="return confirm('Are you sure you want to delete this?')"><i class="fas fa-trash" style="cursor: pointer;color:red"></i></a>
                              </li>

                              <li title="Download" style="display:inline-block;"><a target="_blank" href="{{url('/images')}}/{{@$data->name}}"><i class="fas fa-download" style="cursor: pointer;color:blue"></i></a>
                              </li>

                           </ul>
                        </td>
                     </tr>
                     <!-- For doc category -->
                     <div class="modal fade" id="category{{$key1}}" tabindex="-1" role="dialog" aria-hidden="true">
                        <div class="modal-dialog modal-dialog-centered">
                           <div class="modal-content">
                              <div class="modal-header">
                                 <h4 class="modal-title" id="myCenterModalLabel">Update Document Category</h4>
                                 <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                              </div>
                              <div class="modal-body">
                                 <form action="{{route('updateStaffDocCategory',[$data->id])}}" method='post' enctype="multipart/form-data">
                                    @csrf
                                    <input type="hidden" name="redirect" value="qc">
                                    <div class="form-group">
                                       <label>Select Category</label>
                                       <select name="category" class="form-control">
                                          @foreach($reportHeadingCategory as $mainCat)
                                          <optgroup label="{{$mainCat->category_name}}">
                                             @foreach($mainCat->catHeadings as $sub)
                                             <option value="{{$sub->name}}" <?php if ($data->category == $sub->name) {
                                                                                 echo 'selected';
                                                                              } ?>>{{$sub->name}}</option>
                                             @endforeach
                                          </optgroup>
                                          @endforeach
                                       </select>
                                    </div>
                                    <div class="form-group">
                                       <button type="submit" class="ladda-button  btn btn-primary" dir="ltr" data-style="slide-left">Update
                                       </button>
                                       <button type="submit" class="ladda-button  btn btn-danger" class="close" data-dismiss="modal" aria-hidden="true">cancel
                                       </button>
                                    </div>
                                 </form>
                              </div>
                           </div>
                           <!-- /.modal-content -->
                        </div>
                        <!-- /.modal-dialog -->
                     </div>
                     <!-- For doc category -->
                     <!-- For Staff Visibility -->
                     <div class="modal fade" id="staff{{$key1}}" tabindex="-1" role="dialog" aria-hidden="true">
                        <div class="modal-dialog modal-dialog-centered">
                           <div class="modal-content">
                              <div class="modal-header">
                                 <h4 class="modal-title" id="myCenterModalLabel">Update Staff Visibility</h4>
                                 <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                              </div>
                              <div class="modal-body">
                                 <form action="{{route('updateStaffDocCategory',[$data->id])}}" method='post' enctype="multipart/form-data">
                                    @csrf
                                    <input type="hidden" name="redirect" value="qc">
                                    <div class="form-group">
                                       <label>Staff Visibility <code>*</code></label>
                                       <select name="staff_visibleity" class="form-control">
                                          <option value="">Select</option>
                                          <option value="Yes" <?php if ($data->staff_visibleity == 'Yes') {
                                                                  echo 'selected';
                                                               } ?>>Yes</option>
                                          <option value="No" <?php if ($data->staff_visibleity == 'No') {
                                                                  echo 'selected';
                                                               } ?>>No</option>
                                       </select>
                                    </div>
                                    <div class="form-group">
                                       <button type="submit" class="ladda-button  btn btn-primary" dir="ltr" data-style="slide-left">Update
                                       </button>
                                       <button type="submit" class="ladda-button  btn btn-danger" class="close" data-dismiss="modal" aria-hidden="true">cancel
                                       </button>
                                    </div>
                                 </form>
                              </div>
                           </div>
                           <!-- /.modal-content -->
                        </div>
                        <!-- /.modal-dialog -->
                     </div>
                     <!-- For doc category -->
                     <!-- Set expire date -->
                     <div class="modal fade" id="expiree{{$key1}}" tabindex="-1" role="dialog" aria-hidden="true">
                        <div class="modal-dialog modal-dialog-centered">
                           <div class="modal-content">
                              <div class="modal-header">
                                 <h4 class="modal-title" id="myCenterModalLabel">Update Expire Date</h4>
                                 <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                              </div>
                              <div class="modal-body">
                                 <form action="{{route('updateStaffDocCategory',[$data->id])}}" method='post' enctype="multipart/form-data">
                                    @csrf
                                    <input type="hidden" name="redirect" value="qc">
                                    <div class="form-group">
                                       <label>Date <code>*</code></label>
                                       <input type="date" class="form-control datepicker1" name="expire" id="expire1" required>
                                    </div>
                                    <div class="form-group">
                                       <button type="submit" class="ladda-button  btn btn-primary" dir="ltr" data-style="slide-left">Update
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
                     <!-- Set expire date -->
                     <!-- Edit Model -->
                     <div class="modal fade" id="edit{{$key1}}" tabindex="-1" role="dialog" aria-hidden="true">
                        <div class="modal-dialog modal-dialog-centered">
                           <div class="modal-content">
                              <div class="modal-header">
                                 <h4 class="modal-title" id="myCenterModalLabel">Upload Document File</h4>
                                 <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                              </div>
                              <div class="modal-body">
                                 <form action="{{route('updateStaffDocCategory',[$data->id])}}" method='post' enctype="multipart/form-data">
                                    @csrf
                                    <input type="hidden" name="redirect" value="qc">
                                    <div class="form-group">
                                       <label>File <code>*</code></label>
                                       <input type="file" class="form-control" name="file" required>
                                    </div>
                                    <div class="form-group">
                                       <button type="submit" class="ladda-button  btn btn-primary" dir="ltr" data-style="slide-left">Upload
                                       </button>
                                    </div>
                                 </form>
                              </div>
                           </div>
                           <!-- /.modal-content -->
                        </div>
                        <!-- /.modal-dialog -->
                     </div>
                     <!-- Edit Model -->
                     @endforeach
                  </tbody>
               </table>
            </div>
            <!-- end card body-->
         </div>
         <!-- end card -->
      </div>
      <!-- end col-->
   </div>
   <!-- end row-->
</div>
<!-- container -->
<style>
   li.select2-selection__choice {
      color: black !important;
   }
</style>
<div class="modal fade" id="doc" tabindex="-1" role="dialog" aria-hidden="true">
   <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content">
         <div class="modal-header">
            <h4 class="modal-title" id="myCenterModalLabel">Upload Document File</h4>
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
         </div>
         <div class="modal-body">
            <form action="{{route('uploadStaffDocument',[$cId])}}" method='post' enctype="multipart/form-data">
               @csrf
               <input type="hidden" name="redirect" value="qc">

               <div class="form-group">
                  <label>Select Category</label>
                  <select name="category" class="form-control">
                     @foreach($reportHeadingCategory as $mainCat)
                     <optgroup label="{{$mainCat->category_name}}">
                        @foreach($mainCat->catHeadings as $sub)
                        <option value="{{$sub->name}}">{{$sub->name}}</option>
                        @endforeach
                     </optgroup>
                     @endforeach
                  </select>
               </div>


               <div class="form-group">
                  <label>File <code>*</code></label>
                  <input type="file" class="form-control" name="file" required>
               </div>
               <div class="form-group">
                  <button type="submit" class="ladda-button  btn btn-primary" dir="ltr" data-style="slide-left">Upload
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
<link rel="stylesheet" href="//code.jquery.com/ui/1.13.2/themes/base/jquery-ui.css">
<script src="https://code.jquery.com/jquery-3.6.0.js"></script>
<script src="https://code.jquery.com/ui/1.13.2/jquery-ui.js"></script>
<script>
   $(function() {
      //$( ".datepicker" ).datepicker();
      $('.datepickers').datepicker({
         format: "dd-mm-YYYY"
      }).on('changeDate', function(ev) {
         $(this).datepicker('hide');
      });
   });
</script>
@endsection
@section('script')
<!-- Plugins js-->
<link rel="stylesheet" href="//code.jquery.com/ui/1.13.2/themes/base/jquery-ui.css">
<script>
   $(document).ready(function() {



      $('.checkbox').on('click', function() {
         var fId = $(this).attr('data');
         if ($(this).is(':checked')) {
            $(this).val('1');
         } else {
            $(this).val('0');
         }
         setTimeout(function() {
            $('.check' + fId).submit();
         }, 1000);
      });



      $("#expire").datepicker({
         dateFormat: 'dd-mm-yy'
      });


      $('.js-example-basic-single').select2();

      $('#select-all-checkbox').on('change', function() {
         var selectAll = $(this).prop('checked');

         // Select or deselect all options based on the "Select All" checkbox state
         $('#my-select').find('option').prop('selected', selectAll);
         $('#my-select').trigger('change');
      });


      $('#select-all-checkbox1').on('change', function() {
         var selectAll = $(this).prop('checked');

         // Select or deselect all options based on the "Select All" checkbox state
         $('#my-select1').find('option').prop('selected', selectAll);
         $('#my-select1').trigger('change');
      });

   });
</script>
@endsection