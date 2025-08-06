@extends('layouts.vertical', ['title' => 'List Expired Documents'])
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
                  <li class="breadcrumb-item active">List Expired Documents </li>
               </ol>
            </div>
            <h4 class="page-title">List Expired Documents</h4>
         </div>
      </div>
   </div> -->
   <!-- end page title -->
   <div class="row mt-3">
      <div class="col-2">
         <ul class="nav_list">
            <li>
               <a href="{{route('clients.index')}}"><span>List Drivers</span></a>
            </li>
            <li>
               <a href="{{route('arcchiveClients')}}"><span>Archived Clients</span></a>
            </li>
            <li class="activeli">
               <a href="{{route('expireClientDocuments')}}"><span>Expired Documents</span></a>
            </li>
            {{-- <li>
               <a href="{{route('clients.create')}}"><span>New</span></a>
            </li> --}}
            <li>
               <a href="{{url('users/vehicles/show')}}"><span>All Vehicles</span></a>
           </li>
           {{-- <li>
               <a href="{{url('users/vehicles/add')}}"><span>Add Vehicles</span></a>
           </li> --}}
         </ul>
      </div>
      <div class="col-10">
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
               <table class="table table-design-default">
                  <thead>
                     <tr>
                        <th>#</th>
                        <th>Driver</th>
                        <th>Document Category</th>
                        <th>Document Name</th>
                        <th>Expired</th>

                     </tr>
                  </thead>
                  <tbody>
                  
                     @if(count($expireDoc) > 0 )
                     @foreach($expireDoc as $key1=>$data)
                  
                     <?php
                     // echo '<pre>';print_r($data->clients->first_name);
                     ?>
                     <tr>
                        <td>{{$key1+1}}</td>
                         <td><a href="{{url('clientDocuments/',[$data->clients->id])}}" class="action">{{$data->clients->first_name.' '.$data->clients->last_name}}</a></td>
                        <td>{{$data->category}}</td>
                        <td>{{$data->name}}</td>
                        <td>
                           <?php
                           if ($data->expire  != "") {
                              $date1 = date('Y-m-d');
                              $date2 = $data->expire;

                              $diff = abs(strtotime($date2) - strtotime($date1));

                              $years = floor($diff / (365 * 60 * 60 * 24));
                              $months = floor(($diff - $years * 365 * 60 * 60 * 24) / (30 * 60 * 60 * 24));
                              $days = floor(($diff - $years * 365 * 60 * 60 * 24 - $months * 30 * 60 * 60 * 24) / (60 * 60 * 24));

                              printf("%d years ago, %d months ago, %d days ago\n", $years, $months, $days);
                           } else {
                              echo 'Not Set';
                           }
                           ?>


                        </td>

                     </tr>

                     @endforeach
                     @else
                     <tr>
                        <td colspan="5" class="text-center">No data found</td>
                     </tr>
                     @endif
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