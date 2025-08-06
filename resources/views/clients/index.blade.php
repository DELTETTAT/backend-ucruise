@extends('layouts.vertical', ['title' => 'All Clients'])

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
                        <li class="breadcrumb-item active">List Clients</li>
                    </ol>
                </div>
                <h4 class="page-title">List Clients</h4>
            </div>
        </div>
    </div> -->
    <!-- end page title -->

    <div class="row mt-3">
        <div class="col-2">
            <ul class="nav_list">
                <li class="activeli">
                    <a href="{{route('clients.index')}}"><span>List Drivers</span></a>
                </li>
                <li>
                    <a href="{{route('arcchiveClients')}}"><span>Archived Drivers</span></a>
                </li>
                <li>
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
                </li>
                --}}
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

            <div class="card">
                <div class="card-body">

                    <a href="javascript:;" data-toggle="modal" data-target="#sendEmail" class="btn btn-defult" style="float: left;">Send Bulk Email</a>
                    <a href="{{route('listEmails',2)}}" class="btn btn-defult dd" style="float: left;">List Emails</a>


                    <a href="javascript:;" data-toggle="modal" data-target="#sendSMS" class="btn btn-defult sms" style="float: left;">Send SMS</a>
                    <a href="{{route('listSMS',2)}}" class="btn btn-defult dd" style="float: left;">List SMS</a>
                    <a href="{{route('clients.create')}}" class="btn btn-defult dd" style="float: right;">New</a>
                    <table class="table table-design-default">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Name</th>
                                <th>Gender</th>
                                <th>Age</th>
                                <th>Mobile</th>
                                <th>Phone</th>
                                <th>Email</th>
                                <th>Address</th>
                                <th>Role</th>
                                <th></th>


                            </tr>
                        </thead>


                        <tbody>

                            @if(count($drivers) > 0 )
                            @foreach($drivers as $key1=>$data)
                            <?php
                            // echo '<pre>';print_r($data->roles[0]->name);
                            ?>
                            <tr>
                                <td>{{$key1+1}}</td>
                                <td><a href="{{route('clients.show',[$data->id])}}" class="action">{{@$data->salutation.' '.$data->first_name.' '.$data->middle_name.' '.$data->last_name}} </a></td>
                                <td>{{@$data->gender}}</td>
                                <td>{{(date('Y') - date('Y',strtotime($data->dob)))}}</td>
                                <td>{{$data->mobile}}</td>
                                <td>{{$data->phone}}</td>
                                <td>{{@$data->email}}</td>
                                <td>{{@$data->address}}</td>

                                <td>{{@ucfirst($data->roles[0]->name)}}</td>

                                </td>
                                 <td>
                                    <a href="{{url('/users/delete-driver',[$data->id])}}" onclick="return confirm('Are you sure you want to delete this?')"><i class="fas fa-trash text-danger" style="cursor: pointer;"></i></a>

                                </td>

                            </tr>
                            @endforeach
                            @else
                            <tr>
                                <td colspan="9" class="text-center">No data found</td>
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

<!-- Send Bulk Email -->
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
                        <select name="to[]" class="form-control js-example-basic-single" multiple="multiple" id="my-select" required>
                            @foreach($clients as $admin)
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
                        <textarea name="message" class="form-control" placeholder="Message" required></textarea>
                    </div>
                    <div class="form-group">
                        <input type="hidden" name="type" value="2">
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


<!-- Send Bulk SMS -->
<div class="modal fade" id="sendSMS" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title" id="myCenterModalLabel">Send Bulk SMS</h4>
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
            </div>
            <div class="modal-body">
                <form action="{{route('senBulkSMS')}}" method='post'>
                    @csrf
                    <input type="hidden" name="redirect" value="qc">
                    <div class="form-group">
                        <label>TO <code>*</code></label>
                        <select name="to[]" class="form-control js-example-basic-single" multiple="multiple" id="my-select1" required>
                            @foreach($clients as $admin)
                            <option value="{{$admin->email}}">{{$admin->first_name.' '.$admin->last_name}}</option>
                            @endforeach
                        </select>
                        <input type="checkbox" id="select-all-checkbox1"> Select All
                    </div>

                    <div class="form-group">
                        <label>Message <code>*</code></label>
                        <textarea name="message" class="form-control" placeholder="Message" required></textarea>
                    </div>
                    <div class="form-group">
                        <input type="hidden" name="type" value="2">
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


        $('#select-all-checkbox1').on('change', function() {
            var selectAll = $(this).prop('checked');

            // Select or deselect all options based on the "Select All" checkbox state
            $('#my-select1').find('option').prop('selected', selectAll);
            $('#my-select1').trigger('change');
        });

    });
</script>
@endsection