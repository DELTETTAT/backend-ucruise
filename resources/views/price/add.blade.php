@extends('layouts.vertical', ['title' => 'Add Staff'])
@section('content')
<!-- Start Content-->
<div class="container-fluid">
   <!-- start page title -->
   <div class="row">
      <div class="col-12">
         <div class="page-title-box">
            <div class="page-title-right">
               <ol class="breadcrumb m-0">
                  <li class="breadcrumb-item"><a href="javascript: void(0);">Home</a></li>
                  <li class="breadcrumb-item"><a href="javascript: void(0);">Users</a></li>
                  <li class="breadcrumb-item"><a href="javascript: void(0);">Add Staff</a></li>
               </ol>
            </div>
            <h4 class="page-title">Add Staff</h4>
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
         
         <form method="POST" action="{{ route('staff.store') }}" enctype="multipart/form-data" name="gift_store">
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
                        <label for="exampleInputName1">Name <code>*</code></label>
                         <input type="text" name="first_name" class="form-control"   required="" placeholder="Name">
                     </div>

                     
                     <div class="col-sm-6 mt-2">
                        <label for="exampleInputName1">Email <code>*</code></label>
                         <input type="text" name="email" class="form-control"   required="" placeholder="Email">
                     </div>

                     <div class="col-sm-6 mt-2">
                        <label for="exampleInputName1">Mobile</label>
                         <input type="text" name="mobile" class="form-control phone"   required="" placeholder="Mobile">
                     </div>

                     <div class="col-sm-6 mt-2">
                        <label for="exampleInputName1">Phone <code>*</code></label>
                         <input type="text" name="phone" class="form-control phone"   required="" placeholder="Phone">
                     </div>

                     <div class="col-sm-6 mt-2">
                        <label for="exampleInputName1">Type <code>*</code></label>
                        <select class="form-control type" name="type" required="">
                             <option disabled="">Select</option>
                             <option value="carer">Carer</option>
                             <option value="Office User">Office User</option>
                             
                         </select> 
                     </div>


                     <div class="col-sm-6 mt-2 role" style="display:none">
                        <label for="exampleInputName1">Role <code>*</code></label>
                        <select class="form-control" name="role" required="">
                             <option disabled="">Select</option>
                              @foreach($roles as $role)
                             <option value="{{$role->name}}">{{ucfirst($role->name)}}</option>
                             @endforeach
                         </select> 
                     </div>

                     <div class="col-sm-6 mt-2">
                        <label for="exampleInputName1">Gender <code>*</code></label>
                         <select class="form-control" name="gender" required="">
                             <option disabled="">Select</option>
                             <option value="Male">Male</option>
                             <option value="Female">Female</option>
                             <option value="Intersex">Intersex</option>
                             <option value="Non-binary">Non-binary</option>
                             <option value="Unspecified">Unspecified</option>
                             <option value="Perfer not to say">Perfer not to say</option>
                         </select>
                     </div>

                     <div class="col-sm-6 mt-2">
                        <label for="exampleInputName1">Date Of Birth <code>*</code></label>
                         <input type="text" name="dob" class="form-control"  id="datepicker"  required="" placeholder="Date Of Birth">
                     </div>

                     
                     
                     <div class="col-sm-6 mt-2">
                        <label for="exampleInputName1">Employement Type <code>*</code></label>
                         <select class="form-control" name="employement_type" required="">
                             <option disabled="">Select</option>
                             <option value="Employee">Employee</option>
                             <option value="Contractor">Contractor</option>
                         </select>
                     </div>

                     <div class="col-sm-6 mt-2">
                        <label for="exampleInputName1">Address </label>
                         <input type="text" name="address" class="form-control"   required="" placeholder="Address">
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