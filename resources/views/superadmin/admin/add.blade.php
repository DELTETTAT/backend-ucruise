@extends('layouts.vertical', ['title' => 'Add User'])
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
                  <li class="breadcrumb-item"><a href="javascript: void(0);">Add User</a></li>
               </ol>
            </div>
            <h4 class="page-title">Add User</h4>
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
         
         <form method="POST" action="{{ route('admin.store') }}" enctype="multipart/form-data" name="gift_store">
            {{ csrf_field() }}
            <div class="card-body">
               <div class="form-group">
                  <div class="row">

                      <div class="col-sm-6 mt-2">
                        <label for="exampleInputName1">First Name <code>*</code></label>
                         <input type="text" name="first_name" class="form-control"   required="" placeholder="First Name">
                     </div>

                     <div class="col-sm-6 mt-2">
                        <label for="exampleInputName1">Last Name <code>*</code></label>
                         <input type="text" name="last_name" class="form-control"   required="" placeholder="Last Name">
                     </div>

                     <div class="col-sm-6 mt-2">
                        <label for="exampleInputName1">Email <code>*</code></label>
                         <input type="text" name="email" class="form-control"   required="" placeholder="Email">
                     </div>

                     <div class="col-sm-6 mt-2">
                        <label for="exampleInputName1">Phone <code>*</code></label>
                         <input type="text" name="phone" class="form-control pn"  id="phone-number"  required="" placeholder="Phone">
                     </div>

                     <div class="col-sm-6 mt-2">
                        <label for="exampleInputName1">Company Name <code>*</code></label>
                         <input type="text" name="company_name" class="form-control"   required="" placeholder="Company Name">
                     </div>

                     
                     
                     <div class="col-sm-6 mt-2">
                        <label for="exampleInputName1">Status <code>*</code></label>
                         <select class="form-control" name="status" required="">
                             <option disabled="">Select</option>
                             <option value="1">Active</option>
                             <option value="0">In-Active</option>
                         </select>
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
 
@endsection