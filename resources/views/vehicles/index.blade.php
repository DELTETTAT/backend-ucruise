@extends('layouts.vertical', ['title' => 'Vehicles'])

@section('css')
<!-- Plugins css -->
<link href="{{asset('assets/libs/datatables/datatables.min.css')}}" rel="stylesheet" type="text/css" />
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.9.0/css/bootstrap-datepicker.min.css">


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
                
                <li class="activeli">
                    <a href="{{url('users/vehicles/show')}}"><span>All Vehicles</span></a>
                </li>
                {{-- <li>
                    <a href="{{url('users/vehicles/add')}}"><span>Add Vehicles</span></a>
                </li> --}}
                
                
              
                   
            </ul>
        </div>

        <div class="col-10">



            <div class="card">
                <div class="card-body">

                    <!-- <a href="javascript:;" data-toggle="modal" data-target="#sendEmail" class="btn btn-info" style="float: left;">Send Bulk Email</a> -->
                    <!-- <a href="{{ url('users/add-staff') }}" class="btn btn-info" style="float: left;">Send Bulk SMS</a> -->

                    <!-- <a href="{{ route('exportStaff') }}" class="btn btn-info" style="float: right;">Export Data</a> -->
                    {{-- <a href="{{ route('allowance.create') }}" class="btn btn-defult" style="float: right;">Add
                        Allowance</a> --}}
                     
                    <table class="table table-design-default">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Name</th>
                                <th>Description</th>
                                <th>Seats</th>


                            </tr>
                        </thead>



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



@endsection

@section('script')
<!-- Plugins js-->
<script src="{{asset('assets/libs/datatables/datatables.min.js')}}"></script>
<script src="{{asset('assets/libs/pdfmake/pdfmake.min.js')}}"></script>

<!-- Page js-->
<script src="{{asset('assets/js/pages/datatables.init.js')}}"></script>
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.9.0/js/bootstrap-datepicker.min.js"></script>
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