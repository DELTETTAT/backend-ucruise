@extends('layouts.vertical', ['title' => 'Vehicles'])

@section('css')
<!-- Plugins css -->
<link href="{{asset('assets/libs/datatables/datatables.min.css')}}" rel="stylesheet" type="text/css" />
<link rel="stylesheet"
    href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.9.0/css/bootstrap-datepicker.min.css">
<link rel="stylesheet" href="https://cdn.materialdesignicons.com/6.5.95/css/materialdesignicons.min.css">


@endsection

@section('content')
<?php error_reporting(0); ?>
<!-- Start Content-->
<div class="container-fluid">

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
                 
                <li class="activeli">
                    <a href="{{url('users/vehicles/show')}}"><span>All Vehicles</span></a>
                </li>
                
                 
                 



            </ul>
        </div>

        <div class="col-10">



            <div class="card">
                <div class="card-body">
                    <!-- <a href=" {{url('users/vehicles/add')}}" class="btn btn-defult dd" style="float: right;">Add Vehicle</a> -->
                    

                    

                    <table class="table table-design-default">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Name</th>
                                <th>Description</th>
                                <th>Seats</th>
                                <th>Fare</th>
                                <th>Action</th>


                            </tr>
                        </thead>

                        <tbody>

                            @foreach ($vehicles as $v)
                            <tr>
                                <td>{{$loop->index+1}}</td>
                                <td><a href="{{url('users/vehicles/edit/'.$v->id)}}">{{$v->name}}</a></td>
                                <td>{{$v->description}}</td>
                                <td>{{$v->seats}}</td>
                                <td>{{$v->fare}}</td>
                                <td><a href="{{url('users/vehicles/delete/'.$v->id)}}"
                                        onclick="return confirmDelete();"><i class="mdi mdi-delete"></i></a></td>
                            </tr>
                            @endforeach



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
    function confirmDelete() {
        if (confirm('Are you sure you want to delete this vehicle?')) {
            // If the user confirms, allow the default link behavior (deleting the vehicle).
            return true;
        }
        // If the user cancels, prevent the default link behavior (no deletion).
        return false;
    }
</script>

@endsection