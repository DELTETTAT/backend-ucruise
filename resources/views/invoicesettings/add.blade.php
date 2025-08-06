@extends('layouts.vertical', ['title' => 'Invoice Settings'])
@section('content')
<?php error_reporting(0); ?>
<!-- Start Content-->
<div class="container-fluid">
<!-- start page title -->
<div class="row">
   <div class="col-12">
      <!-- <div class="page-title-box">
         <div class="page-title-right">
            <ol class="breadcrumb m-0">
               <li class="breadcrumb-item"><a href="javascript: void(0);">Home</a></li>
               <li class="breadcrumb-item"><a href="javascript: void(0);">Invoice Settings</a></li>
            </ol>
         </div>
         <h4 class="page-title">Invoice Settings</h4>
      </div> -->
      @if ($message = Session::get('success'))  
      <div class="alert alert-success alert-block">  
         <button type="button" class="close" data-dismiss="alert">X</button>   
         <strong>{{ $message }}</strong>  
      </div>
      @endif 
      @if ($message = Session::get('warning'))  
      <div class="alert alert-danger alert-block">  
         <button type="button" class="close" data-dismiss="alert">X</button>   
         <strong>{{ $message }}</strong>  
      </div>
      @endif
   </div>
</div>
<!-- end page title --> 
<div class="row mt-3">
   <div class="col-lg-6 card">
      @if ($errors->any())
      <div class="alert alert-danger">
         <ul>
            @foreach ($errors->all() as $error)
            <li>{{ $error }}</li>
            @endforeach
         </ul>
      </div>
      @endif
   </div>
</div>
<div class="row">
<div class="col-2">
         <ul class="nav_list">
            <li class="activeli">
               <a href="{{url('users/invoice_settings')}}"><span>Invoice Settings</span></a>
            </li>
            <li>
               <a href="{{route('prices.index')}}"><span>Prices</span></a>
            </li>
            <li>
               <a href="{{route('award_group.index')}}"><span>Pay Groups</span></a>
            </li>
            <li>
               <a href="{{route('allowance.index')}}"><span>Allowances</span></a>
            </li>

            <li>
               <a href="{{url('users/reminders')}}"><span>Reminders</span></a>
            </li>

            <li>
               <a href="{{url('users/subscription')}}"><span>Subscription</span> </a>
            </li>
            <li>
               <a href="{{route('billing.index')}}"><span>Billing</span> </a>
            </li>
            <li>
               <a href="{{route('activity.index')}}"><span>Activity</span> </a>
            </li>
         </ul>
      </div>
   <div class="col-md-5">
      <div class="card">
         <div class="card-body">
            <form method="POST" action="{{ route('invoice.settings') }}" enctype="multipart/form-data" name="gift_store">
               {{ csrf_field() }}
               <div class="form-group">
                  <label for="exampleInputName1">ABN</label>
                  <input type="text" value="{{$edit->abn}}" name="abn" class="form-control"    placeholder="ABN">  
               </div>
               <div class="form-group">
                  <label for="exampleInputName1">Address</label>
                  <textarea class="form-control" placeholder="Address..." name="address">{{$edit->address}}</textarea>  
               </div>
               <div class="form-group">
                  <label for="exampleInputName1">Phone</label>
                  <input type="text" name="phone" value="{{$edit->phone}}" class="form-control"  placeholder="Phone">  
               </div>
               <div class="form-group">
                  <label for="exampleInputName1">Payment terms</label>
                  <textarea class="form-control" placeholder="Payment terms..." name="payment_return">{{$edit->payment_return}}</textarea>  
               </div>
               <div class="form-group">
                  <label for="exampleInputName1">Contact email</label>
                  <input type="text" value="{{$edit->contact_email}}" name="contact_email" class="form-control"  placeholder="Contact email">  
               </div>
               <div class="form-group">
                  <label for="exampleInputName1">Email Message</label>
                  <textarea class="form-control" id="editor" placeholder="Email Message" name="email_message">{{$edit->email_message}}</textarea>  
               </div>
               <div class="form-group">
                  <label for="exampleInputName1">Payment rounding</label>
                  <input type="number" value="{{$edit->payment_rounding}}" name="payment_rounding" class="form-control"  placeholder="Payment rounding"> 
                  <span><i style="color:red">Decimal</i></span> 
               </div>
               <div class="form-group">
                  <label for="exampleInputName1">NDIA provider number</label>
                  <input type="text" value="{{$edit->provider_number}}" name="provider_number" class="form-control"  placeholder="NDIA provider number">  
               </div>
               <div class="form-group">
                  <label for="exampleInputName1">Cost calculation is based on</label>
                  <input type="text" value="{{$edit->cost_calcculation}}"  name="cost_calcculation" class="form-control"  placeholder="NDIA provider number">  
               </div>
               <div class="form-group">
                  <label for="exampleInputName1">Cancelled by client label </label>
                  <select class="form-control" name="cancelled_by_client">
                     <option value="yes" <?php if($edit->cancelled_by_client == 'yes'){ echo 'selected'; } ?>>Yes</option>
                     <option value="no" <?php if($edit->cancelled_by_client == 'no'){ echo 'selected'; } ?>>No</option>
                  </select>
               </div>
               <div class="form-group">
                  <label for="exampleInputName1">Cancel message</label>
                  <input type="text" value="{{$edit->client_message}}" name="client_message" class="form-control"  placeholder="Cancel message">  
               </div>
               <div class="form-group">
                  <label for="exampleInputName1">Invoice item default format &nbsp;<a href="javascript:;" data-toggle="modal" data-target="#details"  style="float: right;"><i class="fe-info"></i></a></label>
                  <textarea class="form-control" placeholder="{client} @ {shift_date} {shift_start_time} - {shift_end_time} [{price_book}] [{price_ref_no}]" name="invoice_item_default_format">{{$edit->invoice_item_default_format}}</textarea>  
               </div>
         </div>
         <div class="card-footer">
         <button type="submit" class="btn btn-primary" value="1" name="exit">Save</button>
         <a href="javascript:;" class="btn btn-danger" onclick="history.back()" >Cancel</a>
         </div>
         </form>
         <!-- end card-body-->
      </div>
      <!-- end card-->
   </div>
   <div class="col-md-5">
      <div class="card">
         <div class="card-body">
            <h4 class="header-title">Taxes 
            @if(!@$tax->name)
            <a style="float:right" href="javascript:;" data-toggle="modal" data-target="#tax">Add Tax</a>
            @endif
         </h4>
            <table class="table table-design-default">
               <tbody>
                  <tr>
                     <td><b>Name</b></td>
                     <td><b>Rate</b></td>
                     <td></td>
                      
                  </tr>
                  @if(@$tax->name)
                  <tr>
                     <td>{{@$tax->name}}</td>
                     <td>{{@$tax->tax}}%</td>
                     <td><a href="javascript:;" data-toggle="modal" data-target="#tax">Edit</a></td>
                  </tr>
                  @endif
                   
               </tbody>
            </table>
         </div>
         <!-- end card-body-->
      </div>
      <!-- end card-->
   </div>
</div>

<div class="modal fade" id="tax" tabindex="-1" role="dialog"
   aria-labelledby="scrollableModalTitle" aria-hidden="true">
   <div class="modal-dialog modal-dialog-scrollable" role="document">
      <div class="modal-content">
         <div class="modal-header">
            <h5 class="modal-title" id="scrollableModalTitle">Add Tax Information</h5>
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
            </button>
         </div>
         <div class="modal-body">
         <form action="{{route('storeTax')}}" method="post" id="bs4">
                  @csrf
                  <input type="hidden" name="redirect" value="schedule" >
                     <div class="form-group">
                        <label>Name<code>*</code></label>
                        <input type="text" name="name" placeholder="GST" class="form-control" required value="{{@$tax->name}}">
                        
                     </div>

                     <div class="form-group">
                        <label>Rate<code>*</code></label>
                        <input type="text" name="tax" placeholder="Rate" class="form-control" required value="{{@$tax->tax}}">
                        
                     </div>

                     <input type="submit"  class="btn btn-success">
                   
               </form>
         </div>
      </div>
      <!-- /.modal-content -->
   </div>
   <!-- /.modal-dialog -->
</div>
<!-- /.modal --> 



<div class="modal fade" id="details" tabindex="-1" role="dialog"
   aria-labelledby="scrollableModalTitle" aria-hidden="true">
   <div class="modal-dialog modal-dialog-scrollable" role="document">
      <div class="modal-content">
         <div class="modal-header">
            <h5 class="modal-title" id="scrollableModalTitle">Description tags</h5>
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
            </button>
         </div>
         <div class="modal-body">
            <table class="table table-design-default">
               <tbody>
                  <tr>
                     <td><b>{client}</b></td>
                     <td>- Name of client</td>
                  </tr>
                  <tr>
                     <td><b>{client_first_name}</b></td>
                     <td>- First name of client</td>
                  </tr>
                  <tr>
                     <td><b>{client_last_name}</b></td>
                     <td>- Last name of client</td>
                  </tr>
                  <tr>
                     <td><b>{shift_date}</b></td>
                     <td>- Date of the shift</td>
                  </tr>
                  <tr>
                     <td><b>{shift_start_time}</b></td>
                     <td>- Start time</td>
                  </tr>
                  <tr>
                     <td><b>{shift_end_time}</b></td>
                     <td>- End time</td>
                  </tr>
                  <tr>
                     <td><b>{price_book}</b></td>
                     <td>- Price book name</td>
                  </tr>
                  <tr>
                     <td><b>{price_ref_no}</b></td>
                     <td>- Price reference number</td>
                  </tr>
                  <tr>
                     <td><b>{client_ndia_no}</b></td>
                     <td>- Client NDIA number</td>
                  </tr>
                  <tr>
                     <td><b>{client_ref_no}</b></td>
                     <td>- Client reference number</td>
                  </tr>
                  <tr>
                     <td><b>{client_type}</b></td>
                     <td>- Client type</td>
                  </tr>
                  <tr>
                     <td><b>{carers}</b></td>
                     <td>- List of carers</td>
                  </tr>
                  <tr>
                     <td><b>{shift_type}</b></td>
                     <td>- Shift type</td>
                  </tr>
                  <tr>
                     <td><b>{rate}</b></td>
                     <td>- Rate amount</td>
                  </tr>
                  <tr>
                     <td><b>{quantity}</b></td>
                     <td>- Quantity</td>
                  </tr>
                  <tr>
                     <td><b>{client_expense}</b></td>
                     <td>- Client Expense Description</td>
                  </tr>
               </tbody>
            </table>
         </div>
      </div>
      <!-- /.modal-content -->
   </div>
   <!-- /.modal-dialog -->
</div>
<!-- /.modal -->  
@endsection
@section('script')
<link rel="stylesheet" href="//code.jquery.com/ui/1.13.2/themes/base/jquery-ui.css">
<script src="https://cdn.ckeditor.com/4.13.0/standard/ckeditor.js"></script>
<script type="text/javascript">
   $(document).ready(function(){
   
      CKEDITOR.replace('editor');
   
   
    $( "#datepicker" ).datepicker({  maxDate: 0,dateFormat: 'dd-mm-yy' });
          $('.phone').keyup(function(e){
             if (/\D/g.test(this.value))
             {
                // Filter non-digits from input value.
                this.value = this.value.replace(/\D/g, '');
             }
       });
   
       $("input[type='checkbox']").click(function(){
          if ($(this).is(':checked')) {
          
             $('.salu').prop('disabled', false);
          } else {
               
             $('.salu').prop('disabled', true);
          }
       });
   
   
       // on change type ans show roles
       $('.type').change(function(){
          var curretValue =  $(this).val();
          if(curretValue == 'Office User'){
             $('.role').show();
          }else{
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