@extends('layouts.vertical', ['title' => 'Vehicles'])

@section('css')
<!-- Plugins css -->
<link href="{{asset('assets/libs/datatables/datatables.min.css')}}" rel="stylesheet" type="text/css" />
<link rel="stylesheet"
    href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.9.0/css/bootstrap-datepicker.min.css">


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
                    <a href="{{route('clients.index')}}"><span>List Drivers</span></a>
                 </li>
                 <li>
                    <a href="{{route('arcchiveClients')}}"><span>Archived Drivers</span></a>
                 </li>
                 <li>
                    <a href="{{route('expireClientDocuments')}}"><span>Expired Documents</span></a>
                 </li>
                 {{-- <li >
                    <a href="{{route('clients.create')}}"><span>New</span></a>
                 </li>  --}}
                <li class="activeli">
                    <a href="{{url('users/vehicles/show')}}"><span>All Vehicles</span></a>
                </li>
                {{-- <li >
                    <a href="{{url('users/vehicles/add')}}"><span>Add Vehicles</span></a>
                </li> --}}
                 
               


            </ul>
        </div>

        <div class="col-10">

            <div class="card">
        <form method="POST" action="{{url('users/vehicles/update/'.$edit->id)}}" enctype="multipart/form-data" name="edit_vehicle">
                {{ csrf_field() }}
                <div class="card-body">
                    <div class="form-group">
                        <div class="row">

                            <div class="col-sm-6 mt-2">
                                <label for="name">Vehicle Name <code>*</code></label>
                                <input type="text" name="name" class="form-control" required=""
                                     value="{{$edit->name}}">
                            </div>

                            <div class="col-sm-6 mt-2">
                                <label for="description">Description <code>*</code></label>
                                <input type="text" name="description" class="form-control" required=""
                                   value="{{$edit->description}}">
                            </div>
                            <div class="col-sm-3 mt-2">
                                <label for="seats">Seats <code>*</code></label>
                                <input type="text" name="seats" class="form-control" required=""
                                   value="{{$edit->seats}}">
                            </div>

                            <div class="col-sm-3 mt-2">
                                <label for="seats">Fare <code>*</code></label>
                                <input type="text" name="fare" class="form-control" required=""
                                   value="{{$edit->fare}}">
                            </div>






                        </div> <!-- end card body-->
                    </div> <!-- end card -->
                </div><!-- end col-->
        </div>
        <!-- end row-->

        <div class="card-footer">
            <button type="submit" class="btn btn-success" value="1" name="exit">Update</button>

            <a href="javascript:;" class="btn btn-danger" onclick="history.back()">Cancel</a>
         </div>
        </div>
    </div> <!-- container -->
    <style>
        li.select2-selection__choice {
            color: black !important;
        }
    </style>



    @endsection

    @section('script')
    <!-- Plugins js-->
    <script src="{{asset('assets/libs/datatables/datatables.min.js')}}"></script>
    <script src="{{asset('assets/libs/pdfmake/pdfmake.min.js')}}"></script>

    <!-- Page js-->
    <script src="{{asset('assets/js/pages/datatables.init.js')}}"></script>
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.9.0/js/bootstrap-datepicker.min.js">
    </script>
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