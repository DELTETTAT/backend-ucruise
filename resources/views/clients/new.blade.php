@extends('layouts.vertical', ['title' => 'New Client'])
@section('content')
<?php error_reporting(0); ?>
<!-- Start Content-->
<div class="container-fluid">
    <!-- start page title -->
    <div class="row">
        <div class="col-12">
            <div class="page-title-box">
                <div class="page-title-right">
                    <ol class="breadcrumb mt-20">
                        <a href="" class="btn btn-defult">Cancel</a>
                        <a href="" class="btn btn-defult">Create</a>
                    </ol>

                </div>
                <h4 class="page-title">Demographic Detail</h4>
            </div>
        </div>
    </div>
    <div class="row">
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
                {{-- <li class="activeli">
                    <a href="{{route('newClient')}}"><span>New</span></a>
                </li> --}}
                <li>
                    <a href="{{url('users/vehicles/show')}}"><span>All Vehicles</span></a>
                </li>
            </ul>
        </div>
        <div class="col-10">
            <table class="table table-design-default">
                <thead>
                    <tr>
                        <th>Basic Infomation</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>
                            <form>
                                <div class="form-group">
                                    <div class="row">
                                        <div class="col-sm-4 mt-2">
                                            <label class="label required">Enter First Name:</label>
                                            <input type="text" name="" class="form-control" placeholder="Enter First Name">
                                        </div>
                                        <div class="col-sm-4 mt-2">
                                            <label class="label required">Middle Name:</label>
                                            <input type="text" name="" class="form-control" placeholder="Middle Name">
                                        </div>
                                        <div class="col-sm-4 mt-2">
                                            <label class="label required">Last/ Family Name</label>
                                            <input type="text" name="" class="form-control" placeholder="Last/ Family Name">
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-sm-4 mt-2">
                                            <label class="label required">Display Name</label>
                                            <input type="text" name="" class="form-control" placeholder="Display Name">
                                        </div>
                                        <div class="col-sm-4 mt-2">
                                            <label class="label required">Gender</label>
                                            <select class="form-control">
                                                <option>Select</option>
                                                <option>Male</option>
                                                <option>Feale</option>
                                            </select>
                                        </div>
                                        <div class="col-sm-4 mt-2">
                                            <label class="label required">Date of Birth:</label>
                                            <input type="text" name="" class="form-control">
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-sm-4 mt-2">
                                            <label class="label required">Contact:</label>
                                            <input type="text" name="" class="form-control" placeholder="Contact">
                                        </div>
                                        <div class="col-sm-4 mt-2">
                                            <label class="label required">Email:</label>
                                            <input type="email" name="" class="form-control" placeholder="Email">
                                        </div>
                                        <div class="col-sm-4 mt-2">
                                            <label class="label required">Religion:</label>
                                            <input type="text" name="" class="form-control" placeholder="Religion">
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-sm-4 mt-2">
                                            <label class="label required">Marital Status:</label>
                                            <select class="form-control">
                                                <option>Select</option>
                                                <option>Married</option>
                                                <option>Unmarried</option>
                                            </select>
                                        </div>
                                        <div class="col-sm-4 mt-2">
                                            <label class="label required">Nationality:</label>
                                            <input type="text" name="" class="form-control" placeholder="Nationality">
                                        </div>
                                        <div class="col-sm-4 mt-2">
                                            <label class="label required">Language Spoken:</label>
                                            <input type="text" name="" class="form-control" placeholder="Language Spoken">
                                        </div>
                                    </div>
                            </form>
                        </td>
                    </tr>
                </tbody>
            </table>

            <table class="table table-design-default">
                <thead>
                    <tr>
                        <th>Address Details</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>
                            <form>
                                <div class="form-group">
                                    <div class="row">
                                        <div class="col-sm-12 mt-2">
                                            <label class="label required">Full Address:</label>
                                            <input type="text" name="" class="form-control" placeholder="Full Address">
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-sm-4 mt-2">
                                            <label class="label required">Unit/Aprtment Number:</label>
                                            <input type="text" name="" class="form-control" placeholder="Unit/Aprtment Number">
                                        </div>
                                        <div class="col-sm-4 mt-2">
                                            <label class="label required">State & City:</label>
                                            <input type="text" name="" class="form-control" placeholder="State & City">
                                        </div>
                                        <div class="col-sm-4 mt-2">
                                            <label class="label required">Language:</label>
                                            <input type="text" name="" class="form-control" placeholder="Language">
                                        </div>
                                    </div>

                                </div>
                            </form>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection