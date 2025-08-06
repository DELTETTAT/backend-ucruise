@extends('layouts.vertical', ['title' => 'Allowances'])

@section('css')
    <!-- Plugins css -->
    <link href="{{asset('assets/libs/datatables/datatables.min.css')}}" rel="stylesheet" type="text/css" />

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
            <li class="activeli">
               <a href="{{route('allowance.index')}}"><span>Allowances</span></a>
            </li>

            <li>
               <a href="{{url('users/reminders')}}"><span>Reminders</span></a>
            </li>

            <li>
               <a href="{{url('users/subscription')}}"><span>Subscription</span> </a>
            </li>
            <li>
                <a href="{{url('users/subscription')}}"><span>Billing</span> </a>
             </li>
             <li>
                <a href="{{route('activity.index')}}"><span>Activity</span> </a>
            </li>
         </ul>
      </div>
            <div class="col-10">
             


                <div class="card">
                    <div class="card-body">
                     
                    <!-- <a href="javascript:;" data-toggle="modal" data-target="#sendEmail" class="btn btn-info" style="float: left;">Send Bulk Email</a> -->
                    <!-- <a href="{{ url('users/add-staff') }}" class="btn btn-info" style="float: left;">Send Bulk SMS</a> -->
                    
                    <!-- <a href="{{ route('exportStaff') }}" class="btn btn-info" style="float: right;">Export Data</a> -->
                    <a href="{{ route('allowance.create') }}" class="btn btn-defult" style="float: right;">Add Allowance</a>
                     
                    <br>
                    <br>
                    <br>
                        <table class="table table-design-default">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Name</th>
                                    <th>Type</th>
                                    <th>Value</th>
                                    <th>Xero Pay Item</th>
                                     
                                    <th>Action</th>
                                     
                                </tr>
                            </thead>
                        
                        
                            <tbody>
                            @if(count($allowance) > 0 )
                            @foreach($allowance as $key1=>$data)
                                <tr>
                                    <td>{{$key1+1}}</td>
                                    <td>{{$data->name}}</td>
                                    <td>{{$data->type}}</td>
                                    <td>{{$data->value}}</td>
                                    <td>{{$data->xero_pay_item}}</td>
                                    
                                     
                                     
                                    

                                    </td>
                                    <td style="display:nones">
                                        <a href="{{route('allowance.edit',[$data->id])}}" class="btn btn-sm btn-info"><i class="fa fa-edit" style="cursor: pointer;"></i></a><a href="<?php echo url('/users/deleteCategory');?>/{{$data->id}}/{{$data->getTable()}}" class="btn btn-sm btn-danger"  onclick="return confirm('Are you sure you want to delete this?')"><i class="fas fa-trash" style="cursor: pointer;"></i></a>
                                             
                                    </td>
                                     
                                </tr>
                            @endforeach 
                            @else
                            <tr>
                                <td colspan="6" class="text-center">No data found</td>
                            </tr>
                            @endif  
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

    <!-- Add Qualification categories -->
<div class="modal fade" id="sendEmail" tabindex="-1" role="dialog" aria-hidden="true">
   <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content">
         <div class="modal-header">
            <h4 class="modal-title" id="myCenterModalLabel">Send Bulk Email</h4> 
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
         </div>
         <div class="modal-body">
            <form action="{{route('senBulkEmail')}}" method='post'>
               @csrf
               <input type="hidden" name="redirect" value="qc">
               <div class="form-group">
                  <label>TO <code>*</code></label>
                  <select name="to[]" class="form-control js-example-basic-single" multiple="multiple" id="my-select">
                    @foreach($admins as $admin)
                        <option value="{{$admin->email}}">{{$admin->first_name.' '.$admin->last_name}}</option>
                    @endforeach
                  </select>
                  <input type="checkbox" id="select-all-checkbox"> Select All
               </div>

               <div class="form-group">
                  <label>Subject <code>*</code></label>
                  <input type="text" name="subject" class="form-control" placeholder="Subject" required>
               </div>

               <div class="form-group">
                  <label>Message <code>*</code></label>
                  <textarea   name="message" class="form-control" placeholder="Message" required></textarea>
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
    <!-- Plugins js-->
    <script src="{{asset('assets/libs/datatables/datatables.min.js')}}"></script>
    <script src="{{asset('assets/libs/pdfmake/pdfmake.min.js')}}"></script>

    <!-- Page js-->
    <script src="{{asset('assets/js/pages/datatables.init.js')}}"></script>
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

    <script>
        $(document).ready(function() {
            $('.js-example-basic-single').select2();

            $('#select-all-checkbox').on('change', function() {
            var selectAll = $(this).prop('checked');

            // Select or deselect all options based on the "Select All" checkbox state
            $('#my-select').find('option').prop('selected', selectAll);
            $('#my-select').trigger('change');
        });

        });
    </script>
@endsection