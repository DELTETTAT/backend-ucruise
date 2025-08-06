@extends('layouts.vertical', ['title' => 'Edit Staff'])
@section('content')
<?php error_reporting(0); ?>
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
                  <li class="breadcrumb-item"><a href="javascript: void(0);">Edit Staff</a></li>
               </ol>
            </div>
            <h4 class="page-title">Edit Staff</h4>
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
         
         <form method="POST" action="{{ route('updateStaff',[$edit->id])}}" enctype="multipart/form-data" name="gift_store">
            <input type="hidden" name="_method" value="PUT">
            {{ csrf_field() }}
            <div class="card-body">
               <div class="form-group">
                  <div class="row">

                  <div class="col-sm-2 mt-2">
                        <label for="exampleInputName1">Use salutation <input type="checkbox" name="check" ></label>
                        <select class="form-control salu" name="salutation" disabled>
                              
                             <option value="Mr">Mr</option>
                             <option value="Mrs">Mrs</option>
                             <option value="Miss">Miss</option>
                             <option value="Ms">Ms</option>
                             <option value="Mx">Mx</option>
                             <option value="Dr">Doctor</option>
                              
                         </select>
                     </div>

                      <div class="col-sm-4 mt-2">
                        <label for="exampleInputName1">First Name <code>*</code></label>
                         <input type="text" name="first_name" class="form-control"  value="{{$edit->first_name}}"  required="" placeholder="First Name">
                     </div>

                     <div class="col-sm-6 mt-2">
                        <label for="exampleInputName1">Email <code>*</code></label>
                         <input type="text" name="email" class="form-control" value="{{$edit->email}}"   required="" placeholder="Email">
                     </div>

                     <div class="col-sm-6 mt-2">
                        <label for="exampleInputName1">Mobile</label>
                         <input type="text" name="mobile" class="form-control phone" value="{{$edit->mobile}}"   required="" placeholder="Mobile">
                     </div>

                     <div class="col-sm-6 mt-2">
                        <label for="exampleInputName1">Phone <code>*</code></label>
                         <input type="text" name="phone" class="form-control phone"  value="{{$edit->phone}}"  required="" placeholder="Phone">
                     </div>

                      

                     <div class="col-sm-6 mt-2">
                        <label for="exampleInputName1">Gender <code>*</code></label>
                         <select class="form-control" name="gender" required="">
                             <option disabled="">Select</option>
                             <option value="Male" <?php if($edit->gender == 'Male'){echo 'selected'; } ?>>Male</option>
                             <option value="Female" <?php if($edit->gender == 'Female'){echo 'selected'; } ?>>Female</option>
                             <option value="Intersex" <?php if($edit->gender == 'Intersex'){echo 'selected'; } ?>>Intersex</option>
                             <option value="Non-binary" <?php if($edit->gender == 'Non-binary'){echo 'selected'; } ?>>Non-binary</option>
                             <option value="Unspecified" <?php if($edit->gender == 'Unspecified'){echo 'selected'; } ?>>Unspecified</option>
                             <option value="Perfer not to say"<?php if($edit->gender == 'Perfer not to say'){echo 'selected'; } ?> >Perfer not to say</option>
                         </select>
                     </div>

                     <div class="col-sm-6 mt-2">
                        <label for="exampleInputName1">Date Of Birth <code>*</code></label>
                         <input type="text" name="dob" class="form-control" value="{{$edit->dob}}"  id="datepicker"  required="" placeholder="Date Of Birth">
                     </div>


                     <div class="col-sm-6 mt-2">
                        <label for="exampleInputName1">Employment Type <code>*</code></label>
                         <select class="form-control" name="employement_type" required="">
                             <option disabled="">Select</option>
                             <option value="Employee" <?php if($edit->employement_type == 'Employee'){echo 'selected'; } ?>>Employee</option>
                             <option value="Contractor" <?php if($edit->employement_type == 'Contractor'){echo 'selected'; } ?>>Contractor</option>
                              
                         </select>
                     </div>


                     <div class="col-sm-6 mt-2">
                        <label for="exampleInputName1">Address </label>
                         <input type="text" name="address" class="form-control"  value="{{$edit->address}}"  required="" placeholder="Address">
                     </div>


                      


                     <div class="col-sm-6 mt-2">
                        <label for="exampleInputName1">Language Spoken <code>*</code></label>
                           <?php
                           $ids = explode(',',$edit->language_spoken);
                          // dd( $ids);
                           ?>
                         <select class="form-control" name="language_spoken[]" required="">
                             <option disabled="">Select</option>\
                             @foreach($language as $lang)
                             <option value="{{$lang->code}}" <?php if(in_array($lang->code,$ids)){echo 'selected'; } ?>>{{$lang->language_name}}</option>
                             @endforeach
                         </select>
                     </div>

                     <div class="col-sm-6 mt-2">
                        <label for="exampleInputName1">Image <code>*</code></label>
                         <input type="file" name="file" class="form-control" >

                         <img class="Aimage" src="{{url('/images')}}/{{@$edit->profile_image}}" onerror="this.src='https://t3.ftcdn.net/jpg/03/45/05/92/240_F_345059232_CPieT8RIWOUk4JqBkkWkIETYAkmz2b75.jpg'" style="width: 50px;height:50px">
                     </div>

                     
 

                  </div>
               </div>
            </div>
      </div>
      <div class="card-footer">
      <button type="submit" class="btn btn-primary" value="1" name="exit">Save and Exit</button>
       
      <a href="javascript:;" class="btn btn-danger" onclick="history.back()" >Back</a>
      </div>
      </form>
   </div>
   <!-- end col -->
</div>
<!-- end row -->
</div> <!-- container -->
@endsection
@section('script')

<link rel="stylesheet" href="//code.jquery.com/ui/1.13.2/themes/base/jquery-ui.css">
 


<script type="text/javascript">
  $(document).ready(function(){
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