@extends('layouts.vertical', ['title' => 'All Users'])

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
                        <li class="breadcrumb-item active">List Users</li>
                    </ol>
                </div>
                <h4 class="page-title">List Users</h4>
            </div>
        </div>
    </div> -->
    <!-- end page title -->

    <div class="row mt-3">
        <div class="col-2">
            <ul class="nav_list">
                <li class="activeli">
                    <a href="{{url('users/staff')}}"><span>List Staff</span></a>
                </li>
                <li>
                    <a href="{{route('arcchiveStaff')}}"><span>Archived Staff</span></a>
                </li>
                <li>
                    <a href="{{route('expireStaffDocuments')}}"><span>Expired Documents</span></a>
                </li>
                <li>
                    <a href="{{route('teams')}}"><span>List Teams</span></a>
                </li>
                <li>
                    <a href="{{url('users/add-staff')}}"><span>New</span></a>
                </li>
            </ul>
        </div>
        <div class="col-10">
            <div class="card">
                <div class="card-body">

                    <a href="javascript:;" data-toggle="modal" data-target="#sendEmail" class="btn btn-defult" style="float: left;">Send Bulk Email</a>

                    <a href="{{route('listEmails',1)}}" class="btn btn-defult dd" style="float: left;">List Emails</a>

                    <a href="javascript:;" data-toggle="modal" data-target="#sendSMS" class="btn btn-defult sms" style="float: left;">Send SMS</a>

                    <a href="{{route('listSMS',1)}}" class="btn btn-defult dd" style="float: left;">List SMS</a>
                    <!-- <a href="{{ url('users/add-staff') }}" class="btn btn-info" style="float: left;">Send Bulk SMS</a> -->


                    <!-- <a href="{{ url('users/add-staff') }}" class="btn btn-defult adstaff" style="float: right;">Add Staff</a> -->
                    <a href="{{ route('exportStaff') }}" class="btn btn-defult" style="float: right;">Export Data</a>

                    <br>
                    <br>
                    <br>
                    <table class="table table-design-default">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Phone</th>
                                <th>Gender</th>
                                <th>Role</th>
                                <th>Address</th>
                                <th>Employement Type</th>
                                <th>Last Login</th>

                            </tr>
                        </thead>


                        <tbody>
                            @if(count($admins) > 0 )
                            @foreach($admins as $key1=>$data)
                            <?php
                            // echo '<pre>';print_r($data->roles[0]->name);
                            ?>
                            <tr>
                                <td>{{$key1+1}}</td>
                                <td><a href="{{route('staffDetails',[$data->id])}}" class="action">{{$data->salutation.' '.$data->first_name.' '.$data->middle_name.' '.$data->last_name}} </a></td>
                                <td>{{$data->email}}</td>
                                <td>{{$data->phone}}</td>
                                <td>{{@$data->gender}}</td>
                                <td><span class="badge bg-soft-success text-success">{{@ucfirst($data->roles[0]->name)}}</span></td>

                                <td>{{@$data->address}}</td>
                                <td>{{@$data->employement_type}}</td>
                                <td>-</td>



                                </td>
                                <td style="display:block">
                                    <!-- <a href="{{url('/admin/edit-admin',[$data->id])}}" class="mr-2"><i class="fa fa-edit text-warning" style="cursor: pointer;"></i></a> -->

                                    <a href="{{url('/users/delete-staff',[$data->id])}}" onclick="return confirm('Are you sure you want to delete this?')"><i class="fas fa-trash text-danger" style="cursor: pointer;"></i></a>

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
                        <textarea name="message" class="form-control" placeholder="Message" required></textarea>
                    </div>
                    <div class="form-group">
                        <input type="hidden" name="type" value="1">
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
                            @foreach($admins as $admin)
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
                        <input type="hidden" name="type" value="1">
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