@extends('layouts.vertical', ['title' => 'Staff Details'])
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
   <div class="row">
      <div class="col-12">
         <div class="page-title-box">
            <div class="page-title-right">
               <ol class="breadcrumb m-0">
                  <li class="breadcrumb-item"><a href="javascript: void(0);">Home</a></li>
                  <li class="breadcrumb-item"><a href="javascript: void(0);">Staff</a></li>
                  <li class="breadcrumb-item"><a href="javascript: void(0);">Details</a></li>
               </ol>
            </div>
            <h4 class="page-title"> Module Access</h4>
         </div>
      </div>
   </div>
   <!-- end page title --> 
   <div class="row">
      <div class="col-lg-12 card">
         @if ($errors->any())
         <div class="alert alert-danger">
            <ul>
               @foreach ($errors->all() as $error)
               <li>{{ $error }}</li>
               @endforeach
            </ul>
         </div>
         @endif
         <?php error_reporting(0); $userKey = []; ?>
         <form action="{{route('updateModulePermission',[$roleId])}}" method="post">
            @csrf
         <div class="row">
            <div class="col-xl-12 col-lg-12">
               <!-- project card -->
               <div class="card">
                  <div class="card-body" style="overflow: scroll;">
                     <h4>Modules</h4>
                     
                     <table class="table table-striped table-bordered bootstrap-datatable" 
                        style="width:100%;">
                        <tr>
                           <th width="19%">Module</th>
                           <th width="16%">List</th>
                           <th width="10%">Add</th>
                           <th width="16%">Edit</th>
                           <th width="10%">Delete</th>
                           
                        </tr>

                        <tr>
                     
                      <th class="aaa">Staff</th>

                      <td>

                        <input type="checkbox" class="i-checks" id="admin_user_list" name="keyname[]" value="staff.list" <?php if(in_array('staff.list',$module)){ echo 'checked'; } ?>>

                      </td>

                      <td>

                        <input type="checkbox" class="i-checks" id="admin_user_list" name="keyname[]" value="addStaff" <?php if(in_array('addStaff',$module)){ echo 'checked'; } ?>>

                      </td>

                      <td>

                        <input type="checkbox" class="i-checks" id="admin_user_list" name="keyname[]" value="staffDetails" <?php if(in_array('staffDetails',$module)){ echo 'checked'; } ?>>

                      </td>

                      <td>

                        <input type="checkbox" class="i-checks" id="admin_user_list" name="keyname[]" value="staffArchiveAccount" <?php if(in_array('staffArchiveAccount',$module)){ echo 'checked'; } ?>>

                      </td>

                      

                    </tr>
                         
                        
                       
                       
                        
                     </table>
                  </div>
                  <!-- end card body-->
               </div>
               <!-- end card -->
               <div class="card-footer">
               <button type="submit" class="btn btn-primary" value="1" name="exit">Save and Exit</button>

               <a href="javascript:;" class="btn btn-danger" onclick="history.back()">Back</a>
            </div>
            </div>
         </div>
      </form>
      </div>
   </div>
   <!-- end col -->
</div>
<!-- end row -->
</div>
<!-- container -->
@endsection
@section('script')
<script type="text/javascript">
   $(document).ready(function(){
   
    
   
    $('.no_access').click(function(){
       if ($('.no_access').prop('checked')) {
          $(this).val(1);
       } else {
          $(this).val(0);
       }
    });
   
   
    $('.account_owner').click(function(){
       if ($('.account_owner').prop('checked')) {
          $(this).val(1);
       } else {
          $(this).val(0);
       }
    });
   
   
    $('.invoice_travel').click(function(){
       if ($('.invoice_travel').prop('checked')) {
          $(this).val(1);
       } else {
          $(this).val(0);
       }
    });
   
    $( "#info" ).datepicker();
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