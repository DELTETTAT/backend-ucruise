@extends('layouts.vertical', ['title' => 'Add Client'])
@section('content')
<?php error_reporting(0); ?>
<style>
   img.pimage {
      float: right;
      border-radius: 64px;
   }

   svg.feather.feather-check.check1 {
      color: green;
   }

   svg.feather.feather-x.close1 {
      color: red;
   }

   img.Aimage {

      border-radius: 64px;
   }
</style>
<!-- Start Content-->
<div class="container-fluid">
   <!-- start page title -->
   <!-- <div class="row">
      <div class="col-12">
         <div class="page-title-box">
            <div class="page-title-right">
               <ol class="breadcrumb m-0">
                  <li class="breadcrumb-item"><a href="javascript: void(0);">Home</a></li>
                  <li class="breadcrumb-item"><a href="javascript: void(0);">Clients</a></li>
                  <li class="breadcrumb-item"><a href="javascript: void(0);">View</a></li>
               </ol>
            </div>
            <h4 class="page-title"><img class="Aimage" src="{{url('/images')}}/{{@$show->profile_image}}" onerror="this.src='https://t3.ftcdn.net/jpg/03/45/05/92/240_F_345059232_CPieT8RIWOUk4JqBkkWkIETYAkmz2b75.jpg'" style="width: 50px;height:50px">{{$show->first_name.' '.$show->last_name}} <span style="font-size:12px">Details</span></h4>
         </div>
      </div>
   </div> -->
   <!-- end page title -->
   <div class="row mt-3">
      <div class="col-2">
         <ul class="nav_list" id="mainNav">
            <li class="activeli">
               <a href="#Demographic"><span>Demographic Detail</span></a>
            </li>
            <li>
               <a href="#Settings"><span>Settings</span></a>
            </li>
            <li>
               <a href="#Documents"><span>Documents</span></a>
            </li>
            <li>
               <a href="#Additional"><span>Additional Information</span></a>
            </li>
         </ul>
      </div>
      <div class="col-lg-10 card">
         @if ($errors->any())
         <div class="alert alert-danger">
            <ul>
               @foreach ($errors->all() as $error)
               <li>{{ $error }}</li>
               @endforeach
            </ul>
         </div>
         @endif
         <table class="table table-design-default settingTable" id="Demographic">
            <tr>
               <th>Demographic details</th>
               <th class="text-right editIcon"> <a href="{{route('clients.edit',[$show->id])}}">Edit</a></th>
            </tr>
            <tr>
               <td width="200">
                  <img class="pimage" src="{{url('/images')}}/{{@$show->profile_image}}" onerror="this.src='https://t3.ftcdn.net/jpg/03/45/05/92/240_F_345059232_CPieT8RIWOUk4JqBkkWkIETYAkmz2b75.jpg'" style="width: 200px;">
               </td>
               <td>
                  <p class="mt-3">First Name :<b> {{$show->salutation.' '.$show->first_name}} {{$show->middle_name}} {{$show->last_name}} </b></p>
                  <p class="mt-3">Email : <b>{{$show->email}}</b></p>
                  <p class="mt-3">Mobile :<b> {{$show->mobile}}</b> Phone :</b> {{$show->phone}}</p>
                  <p class="mt-3">Address :<b> {{$show->address}}</b></p>
                  <p class="mt-3">Gender :<b> {{$show->gender}}</b> DOB :<b> {{$show->dob}}</b> </p>
                  <p class="mt-3">Role :<b> Client</b></p>
               </td>
            </tr>
            <tr><th>Vehicle Information</th>
         <th></th></tr>
            <tr>  <td width="200">
                  <img class="pimage" src="{{url('/images/vehicles')}}/{{@$show->vehicle->image}}" onerror="this.src='https://t3.ftcdn.net/jpg/03/45/05/92/240_F_345059232_CPieT8RIWOUk4JqBkkWkIETYAkmz2b75.jpg'" style="width: 200px;">
               </td> 
               <td>
                  <p class="mt-3">Model:<b> {{$show->vehicle->name}} </b></p>
                  <p class="mt-3">Color : <b>{{$show->vehicle->color}}</b></p>
                  <p class="mt-3">Chasis No :<b> {{$show->vehicle->chasis_no}}</b></p>
                  <p class="mt-3">Vehicle No :<b> {{$show->vehicle->vehicle_no}}</b></p>
                  <p class="mt-3">Registration No :<b> {{$show->vehicle->registration_no}}</b></p>
                  <p class="mt-3">Seats :<b> {{$show->vehicle->seats}}</b></p>
                   
               </td></tr>
         </table>       
         <table class="table table-design-default tdbdnone settingTable" id="Settings">
            <tr>
               <th>Settings</th>
               <th class="text-right editIcon"><a href="javascript:" onClick="settingform()"> Edit </a></th>
            </tr>
            <tr>
               <td colspan="2">
                  <table class="col-12" id="settingView">
                     <tr>
                        <td width="300">
                           <p>NDIS Number :</p>
                        </td>
                        <td>
                        <b>{{@$csetting->NDIS_number}}</b>
                        </td>
                     </tr>
                     <tr>
                        <td>Aged Care Recipient ID : </td>
                        <td><b>{{@$csetting->reference_number}}</b></td>
                     </tr>
                     <tr>
                        <td>Reference Number :</td>
                        <td><b>{{@$csetting->recipient_id}}</b></td>
                     </tr>
                     <tr>
                        <td>Custom Field :</td>
                        <td><b>{{@$csetting->custom_field}}</b></td>
                     </tr>
                     <tr>
                        <td>PO. Number :</td>
                        <td><b>{{@$csetting->po_number}}</b></td>
                     </tr>
                     <tr>
                        <td>Client Type : </td>
                        <td><b>{{@$csetting->client_type}}</b></td>
                     </tr>
                     <tr>
                        <td>Default Price Book :</td>
                        <td><b>{{@$csetting->price_book}}</b></td>
                     </tr>
                     <tr>
                        <td>Teams : </td>
                        <td><b></b></td>
                     </tr>
                     <tr>
                        <td>Share Progress Notes : </td>
                        <td><b>{{@$csetting->progress_note}}</b></td>
                     </tr>
                     <tr>
                        <td>Enable SMS Reminders : </td>
                        <td><b>{{@$csetting->enable_sms_reminder}}</b></td>
                     </tr>
                     <tr>
                        <td>Invoice travel : </td>
                        <td><b>{{@$csetting->invoice_travel}}</b></td>
                     </tr>
                  </table>
               </td>
            </tr>
            <tr>
               <td colspan="2">
               <form action="{{route('clientSettingStore',[$show->id])}}" method="post" style="display:none" id="settingform">
                        @csrf
                        <div class="row">
                        <div class="form-group col-3">
                           <label>NDIS Number</label>
                           <input type="text" name="NDIS_number" placeholder="NDIS Number" class="form-control" value="{{$csetting->NDIS_number}}">
                        </div>
                        <div class="form-group col-3">
                           <label>Aged Care Recipient ID</label>
                           <input type="text" name="recipient_id" placeholder="Aged Care Recipient ID" class="form-control" value="{{$csetting->recipient_id}}">
                        </div>
                        <div class="form-group col-3">
                           <label>Reference Number</label>
                           <input type="text" name="reference_number" placeholder="Reference Number" class="form-control" value="{{$csetting->reference_number}}">
                        </div>
                        <div class="form-group col-3">
                           <label>Custom Field</label>
                           <input type="text" name="custom_field" placeholder="Custom Field" class="form-control" value="{{$csetting->custom_field}}">
                        </div>
                        <div class="form-group col-3">
                           <label>PO. Number</label>
                           <input type="text" name="po_number" placeholder="PO. Number" class="form-control" value="{{$csetting->po_number}}">
                        </div>
                        <div class="form-group col-3">
                           <label>Client Type</label>
                           <select name="client_type" class="form-control">
                              @foreach($clienttype as $cType)
                              <option value="{{$cType->id}}" <?php if ($csetting->client_type == $cType->id) {
                                                                  echo 'selected';
                                                               } ?>>{{$cType->plan_name}}</option>
                              @endforeach
                           </select>
                        </div>
                        <div class="form-group col-3">
                           <label>Default Price Book</label>
                           <select name="price_book" class="form-control">
                              @foreach($priceBook as $pbook)
                              <option value="{{$pbook->id}}" <?php if ($csetting->price_book == $pbook->id) {
                                                                  echo 'selected';
                                                               } ?>>{{$pbook->name}}</option>
                              @endforeach
                           </select>
                        </div>
                        <div class="form-group col-3">
                           <label>Teams</label>
                           <select name="team[]" class="form-control">
                              @foreach($teams as $tm)
                              <option value="{{$tm->id}}">{{$tm->name}}</option>
                              @endforeach
                           </select>
                        </div>
                        <div class="form-group col-3">
                           <label>Share Progress Notes</label>
                           <input type="checkbox" name="progress_note" placeholder="Share Progress Notes" class="progress_note" value="<?php if ($csetting->progress_note == 1) {
                                                                                                                                          echo 1;
                                                                                                                                       } else {
                                                                                                                                          echo 0;
                                                                                                                                       } ?>" <?php if ($csetting->progress_note == 1) {
                                                                                                                                                echo 'checked';
                                                                                                                                             } ?>>
                        </div>
                        <div class="form-group col-3">
                           <label>Enable SMS Reminders</label>
                           <input type="checkbox" name="enable_sms_reminder" placeholder="Enable SMS Reminders" class="enable_sms_reminder" value="<?php if ($csetting->enable_sms_reminder == 1) {
                                                                                                                                                      echo 1;
                                                                                                                                                   } else {
                                                                                                                                                      echo 0;
                                                                                                                                                   } ?>" <?php if ($csetting->enable_sms_reminder == 1) {
                                                                                                                                                            echo 'checked';
                                                                                                                                                         } ?>>
                        </div>
                        <div class="form-group col-3">
                           <label>Invoice travel</label>
                           <input type="checkbox" name="invoice_travel" placeholder="Invoice travel" class="invoice_travel" value="<?php if ($csetting->invoice_travel == 1) {
                                                                                                                                       echo 1;
                                                                                                                                    } else {
                                                                                                                                       echo 0;
                                                                                                                                    } ?>" <?php if ($csetting->invoice_travel == 1) {
                                                                                                                                             echo 'checked';
                                                                                                                                          } ?>>
                        </div>
                        </div>
                        <button type="submit" class="btn btn-success" value="1" name="exit">Save</button>
                        <button type="reset" class="btn btn-defult" onClick="settingformCancel()">Cancel</button>

                     </form>
               </td>
            </tr>
         </table>
         <table class="table table-design-default settingTable" id="Documents">
            <tr>
               <th>Documents </th>
               <th class="text-right editIcon"><a href="{{route('clientDocuments',[$show->id])}}">Manage All</a></th>
            </tr>
            <tr>
               <td colspan="2">
                  <table class="table table-design-default normal_Font">
                     <thead>
                        <tr>
                           <th>Name</th>
                           <th>Category</th>
                           <th>Staff Visibility</th>
                           <th>Expire At</th>
                           <th>No Expireation</th>
                           <th>Last Update</th>
                        </tr>
                     </thead>
                     <tbody>
                        @foreach($docoments as $key1=>$data)
                        <?php
                        // echo '<pre>';print_r($data->roles[0]->name);
                        ?>
                        <tr>
                           <td>{{@$data->name}}</td>
                           <td>
                              {{$data->category}}
                           </td>


                           <td style="text-align:center">
                              @if($data->staff_visibleity== '' || $data->staff_visibleity == 'No')
                              <i data-feather="x" class="close1"></i>
                              @else
                              <i data-feather="check" class="check1"></i>
                              @endif

                           </td>


                           <td>
                              @if($data->no_expireation == 0)
                              {{$data->expire}}

                              @else
                              {{'.....'}}
                              @endif
                           </td>
                           <td style="text-align:center">
                              @if($data->no_expireation== '' || $data->no_expireation == 0)
                              <i data-feather="x" class="close1"></i>
                              @else
                              <i data-feather="check" class="check1"></i>
                              @endif

                           </td>
                           <td>{{date('d-m-Y',strtotime($data->updated_at))}}</td>
               </td>
            </tr>
            @endforeach
            </tbody>
         </table>
         </td>
         </tr>
         </table>
         <table class="table table-design-default tdbdnone settingTable" id="Additional">
            <tr>
               <th>Additional Information</th>
               <th class="text-right editIcon"><a href="javascript:" onClick="Additionalform()"> Edit </a></th>
            </tr>
            <tr>
               <td colspan="2">
                  <table class="col-12" id="AdditionalView">
                     <tr>
                        <td width="300">
                           <p>NDIS Number :</p>
                        </td>
                        <td>
                        <b>{{$adf->private_info}}</b>
                        </td>
                     </tr>
                     <tr>
                        <td>Review date</td>
                        <td>{{$adf->review_date}}</td>
                     </tr>
                  </table>
               </td>
            </tr>
            <tr>
               <td colspan="2">
                  <form action="{{route('clientAdditionalInfo',[$show->id])}}" method="post" style="display:none" id="Additionalform">
                     @csrf
                     <div class="form-group">
                        <label>NDIS Number</label>
                        <textarea name="private_info" placeholder="Enter Private information" class="form-control">{{$adf->private_info}}</textarea>
                     </div>
                     <div class="form-group">
                        <label>Review date</label>
                        <input type="text" name="review_date" placeholder="Review Date" class="form-control" value="{{$adf->review_date}}" id="info">
                     </div>
                     <button type="submit" class="btn btn-success" value="1" name="exit">Save</button>
                     <button type="reset" class="btn btn-defult" onClick="AdditionalformCancel()">Cancel</button>

                  </form>
               </td>
            </tr>
         </table>

         <div class="row" style="display: none;">
            <div class="col-xl-8 col-lg-7">
               <!-- project card -->

               <div class="card d-block">
                  <a href="{{route('clients.edit',[$show->id])}}" style="float:right;margin-right:50px">Edit</a>
                  <div class="card-body">
                     <img class="pimage" src="{{url('/images')}}/{{@$show->profile_image}}" onerror="this.src='https://t3.ftcdn.net/jpg/03/45/05/92/240_F_345059232_CPieT8RIWOUk4JqBkkWkIETYAkmz2b75.jpg'" style="width: 100px;height:100px">
                     <p class="mt-3"><b>First Name :</b> {{$show->salutation.' '.$show->first_name}}</p>
                     <p class="mt-3"><b>Middle Name :</b> {{$show->middle_name}}</p>
                     <p class="mt-3"><b>Last Name :</b> {{$show->last_name}}</p>
                     <p class="mt-3"><b>Email :</b> {{$show->email}}</p>
                     <p class="mt-3"><b>Mobile :</b> {{$show->mobile}}</p>
                     <p class="mt-3"><b>Phone :</b> {{$show->phone}}</p>
                     <p class="mt-3"><b>Address :</b> {{$show->address}}</p>
                     <p class="mt-3"><b>DOB :</b> {{$show->dob}}</p>
                     <p class="mt-3"><b>Gender :</b> {{$show->gender}}</p>
                     <p class="mt-3"><b>Role :</b> Client</p>
                     <!-- end sub tasks/checklists -->
                  </div>
                  <!-- end card-body-->
               </div>
               <!-- end card-->
               <!-- end card-->
               <div class="card">
                  <div class="card-body" style="overflow: scroll;">
                     <h4>Documents</h4>
                     <a href="{{route('clientDocuments',[$show->id])}}" style="float:right">View All</a>
                     <table id="basic-datatable" class="table dt-responsive table-hover table-bordered nowrap w-100" style="white-space: nowrap;">
                        <thead>
                           <tr>
                              <th>Name</th>
                              <th>Category</th>
                              <th>Staff Visibility</th>
                              <th>Expire At</th>
                              <th>No Expireation</th>
                              <th>Last Update</th>
                           </tr>
                        </thead>
                        <tbody>
                           @foreach($docoments as $key1=>$data)
                           <?php
                           // echo '<pre>';print_r($data->roles[0]->name);
                           ?>
                           <tr>
                              <td>{{@$data->name}}</td>
                              <td>
                                 {{$data->category}}
                              </td>


                              <td style="text-align:center">
                                 @if($data->staff_visibleity== '' || $data->staff_visibleity == 'No')
                                 <i data-feather="x" class="close1"></i>
                                 @else
                                 <i data-feather="check" class="check1"></i>
                                 @endif

                              </td>


                              <td>
                                 @if($data->no_expireation == 0)
                                 {{$data->expire}}

                                 @else
                                 {{'.....'}}
                                 @endif
                              </td>
                              <td style="text-align:center">
                                 @if($data->no_expireation== '' || $data->no_expireation == 0)
                                 <i data-feather="x" class="close1"></i>
                                 @else
                                 <i data-feather="check" class="check1"></i>
                                 @endif

                              </td>
                              <td>{{date('d-m-Y',strtotime($data->updated_at))}}</td>
                              </td>
                           </tr>
                           @endforeach
                        </tbody>
                     </table>
                  </div>
                  <!-- end card body-->
               </div>
               <!-- end card -->
            </div>
            <!-- end col -->
            <div class="col-xl-4 col-lg-5">
               <div class="card">
                  <div class="card-body">
                     <h5 class="card-title font-16 mb-3">Settings</h5>
                     <form action="{{route('clientSettingStore',[$show->id])}}" method="post">
                        @csrf
                        <div class="form-group">
                           <label>NDIS Number</label>
                           <input type="text" name="NDIS_number" placeholder="NDIS Number" class="form-control" value="{{$csetting->NDIS_number}}">
                        </div>
                        <div class="form-group">
                           <label>Aged Care Recipient ID</label>
                           <input type="text" name="recipient_id" placeholder="Aged Care Recipient ID" class="form-control" value="{{$csetting->recipient_id}}">
                        </div>
                        <div class="form-group">
                           <label>Reference Number</label>
                           <input type="text" name="reference_number" placeholder="Reference Number" class="form-control" value="{{$csetting->reference_number}}">
                        </div>
                        <div class="form-group">
                           <label>Custom Field</label>
                           <input type="text" name="custom_field" placeholder="Custom Field" class="form-control" value="{{$csetting->custom_field}}">
                        </div>
                        <div class="form-group">
                           <label>PO. Number</label>
                           <input type="text" name="po_number" placeholder="PO. Number" class="form-control" value="{{$csetting->po_number}}">
                        </div>
                        <div class="form-group">
                           <label>Client Type</label>
                           <select name="client_type" class="form-control">
                              @foreach($clienttype as $cType)
                              <option value="{{$cType->id}}" <?php if ($csetting->client_type == $cType->id) {
                                                                  echo 'selected';
                                                               } ?>>{{$cType->plan_name}}</option>
                              @endforeach
                           </select>
                        </div>
                        <div class="form-group">
                           <label>Default Price Book</label>
                           <select name="price_book" class="form-control">
                              @foreach($priceBook as $pbook)
                              <option value="{{$pbook->id}}" <?php if ($csetting->price_book == $pbook->id) {
                                                                  echo 'selected';
                                                               } ?>>{{$pbook->name}}</option>
                              @endforeach
                           </select>
                        </div>
                        <div class="form-group">
                           <label>Teams</label>
                           <select name="team[]" class="form-control">
                              @foreach($teams as $tm)
                              <option value="{{$tm->id}}">{{$tm->name}}</option>
                              @endforeach
                           </select>
                        </div>
                        <div class="form-group">
                           <label>Share Progress Notes</label>
                           <input type="checkbox" name="progress_note" placeholder="Share Progress Notes" class="progress_note" value="<?php if ($csetting->progress_note == 1) {
                                                                                                                                          echo 1;
                                                                                                                                       } else {
                                                                                                                                          echo 0;
                                                                                                                                       } ?>" <?php if ($csetting->progress_note == 1) {
                                                                                                                                                echo 'checked';
                                                                                                                                             } ?>>
                        </div>
                        <div class="form-group">
                           <label>Enable SMS Reminders</label>
                           <input type="checkbox" name="enable_sms_reminder" placeholder="Enable SMS Reminders" class="enable_sms_reminder" value="<?php if ($csetting->enable_sms_reminder == 1) {
                                                                                                                                                      echo 1;
                                                                                                                                                   } else {
                                                                                                                                                      echo 0;
                                                                                                                                                   } ?>" <?php if ($csetting->enable_sms_reminder == 1) {
                                                                                                                                                            echo 'checked';
                                                                                                                                                         } ?>>
                        </div>
                        <div class="form-group">
                           <label>Invoice travel</label>
                           <input type="checkbox" name="invoice_travel" placeholder="Invoice travel" class="invoice_travel" value="<?php if ($csetting->invoice_travel == 1) {
                                                                                                                                       echo 1;
                                                                                                                                    } else {
                                                                                                                                       echo 0;
                                                                                                                                    } ?>" <?php if ($csetting->invoice_travel == 1) {
                                                                                                                                             echo 'checked';
                                                                                                                                          } ?>>
                        </div>
                        <button type="submit" class="btn btn-primary" value="1" name="exit">Save</button>
                     </form>
                  </div>
               </div>
               <!-- New -->
               <div class="card">
                  <div class="card-body">
                     <h5 class="card-title font-16 mb-3">Additional Information</h5>
                     <form action="{{route('clientAdditionalInfo',[$show->id])}}" method="post">
                        @csrf
                        <div class="form-group">
                           <label>NDIS Number</label>
                           <textarea name="private_info" placeholder="Enter Private information" class="form-control">{{$adf->private_info}}</textarea>
                        </div>
                        <div class="form-group">
                           <label>Review date</label>
                           <input type="text" name="review_date" placeholder="Review Date" class="form-control" value="{{$adf->review_date}}" id="info">
                        </div>
                        <button type="submit" class="btn btn-primary" value="1" name="exit">Save</button>
                     </form>
                  </div>
               </div>
            </div>
         </div>
         <!-- end row -->
         <div class="card-footer">
            <div class="row">
               <div class="col-md-12" style="margin-left: 12px; margin-right:12px;">
                  <h4>Archive Client</h4>
                  <div class="row">
                     <div class="col-md-12">
                        <div class="box box-solid">
                           <div class="box-header with-border"><i style="color:red">This will archive the client and you will not able to see client in your list. If you do wish to access the client, please go to Archive sub-menu.</i></div>
                           <div class="box-body"><a href="{{route('clientArchiveAccount',[$show->id])}}" class="btn btn-danger btn-md btn-flat" onclick="return confirm('Are you sure?')">Archive Client</a></div>
                        </div>
                     </div>
                  </div>
               </div>
            </div>
            <!-- <button type="submit" class="btn btn-primary" value="1" name="exit">Save and Exit</button> -->
            <!-- <a href="javascript:;" class="btn btn-danger" onclick="history.back()" >Back</a> -->
         </div>
      </div>
      <!-- end col -->
   </div>
   <!-- end row -->
</div>
<!-- container -->
@endsection
@section('script')
<link rel="stylesheet" href="//code.jquery.com/ui/1.13.2/themes/base/jquery-ui.css">
<script type="text/javascript">
   function Additionalform() {
      document.getElementById("Additionalform").style.display = "block";
      document.getElementById("AdditionalView").style.display = "none";

   }
   function AdditionalformCancel() {
      document.getElementById("Additionalform").style.display = "none";
      document.getElementById("AdditionalView").style.display = "block";

   }
   function settingform() {
      document.getElementById("settingform").style.display = "block";
      document.getElementById("settingView").style.display = "none";

   }
   
   function settingformCancel() {
      document.getElementById("settingform").style.display = "none";
      document.getElementById("settingView").style.display = "grid";

   }
   var lastId,
 topMenu = $("#mainNav"),
 topMenuHeight = 100,
 // All list items
 menuItems = topMenu.find("a"),
 // Anchors corresponding to menu items
 scrollItems = menuItems.map(function(){
   var item = $($(this).attr("href"));
    if (item.length) { return item; }
 });
// Bind click handler to menu items
// so we can get a fancy scroll animation
menuItems.click(function(e){
  var href = $(this).attr("href"),
      offsetTop = href === "#" ? 0 : $(href).offset().top-topMenuHeight+1;
  $('html, body').stop().animate({ 
      scrollTop: offsetTop
  }, 100);
  e.preventDefault();
});


// Bind to scroll
$(window).scroll(function(){
   // Get container scroll position
   var fromTop = $(this).scrollTop()+topMenuHeight;
   
   // Get id of current scroll item
   var cur = scrollItems.map(function(){
     if ($(this).offset().top < fromTop)
       return this;
   });
   // Get the id of the current element
   cur = cur[cur.length-1];
   var id = cur && cur.length ? cur[0].id : "";
   if (lastId !== id) {
       lastId = id;
       // Set/remove active class
       menuItems
         .parent().removeClass("activeli")
         .end().filter("a[href='#"+id+"']").parent().addClass("activeli");
   }                   
});
   $(document).ready(function() {

      $('.progress_note').click(function() {
         if ($('.progress_note').prop('checked')) {
            $(this).val(1);
         } else {
            $(this).val(0);
         }
      });


      $('.enable_sms_reminder').click(function() {
         if ($('.enable_sms_reminder').prop('checked')) {
            $(this).val(1);
         } else {
            $(this).val(0);
         }
      });


      $('.invoice_travel').click(function() {
         if ($('.invoice_travel').prop('checked')) {
            $(this).val(1);
         } else {
            $(this).val(0);
         }
      });

      $("#info").datepicker();
      $("#datepicker").datepicker({
         maxDate: 0,
         dateFormat: 'dd-mm-yy'
      });
      $('.phone').keyup(function(e) {
         if (/\D/g.test(this.value)) {
            // Filter non-digits from input value.
            this.value = this.value.replace(/\D/g, '');
         }
      });

      $("input[type='checkbox']").click(function() {
         if ($(this).is(':checked')) {

            $('.salu').prop('disabled', false);
         } else {

            $('.salu').prop('disabled', true);
         }
      });


      // on change type ans show roles
      $('.type').change(function() {
         var curretValue = $(this).val();
         if (curretValue == 'Office User') {
            $('.role').show();
         } else {
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