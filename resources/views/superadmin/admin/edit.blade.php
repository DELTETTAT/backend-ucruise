@extends('layouts.vertical', ['title' => 'Edit User'])
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
                  <li class="breadcrumb-item"><a href="javascript: void(0);">User</a></li>
                  <li class="breadcrumb-item"><a href="javascript: void(0);">Edit User</a></li>
               </ol>
            </div>
            <h4 class="page-title">Edit User</h4>
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
         
         <form method="POST" action="{{ route('admin.update',[$edit->id])}}" enctype="multipart/form-data" name="gift_store">
            <input type="hidden" name="_method" value="PUT">
            {{ csrf_field() }}
            <div class="card-body">
               <div class="form-group">
                  <div class="row">

                      <div class="col-sm-6 mt-2">
                        <label for="exampleInputName1">First Name <code>*</code></label>
                         <input type="text" name="first_name" class="form-control"   required="" placeholder="First Name" value="{{$edit->first_name}}">
                     </div>

                     <div class="col-sm-6 mt-2">
                        <label for="exampleInputName1">Last Name <code>*</code></label>
                         <input type="text" name="last_name" class="form-control"   required="" placeholder="Last Name" value="{{$edit->last_name}}">
                     </div>

                     <div class="col-sm-6 mt-2">
                        <label for="exampleInputName1">Email <code>*</code></label>
                         <input type="text" name="email" class="form-control"   required="" placeholder="Email" value="{{$edit->email}}">
                     </div>

                     <div class="col-sm-6 mt-2">
                        <label for="exampleInputName1">Phone <code>*</code></label>
                         <input type="text" name="phone"   class="form-control pn"   required="" placeholder="Phone" value="{{$edit->phone}}">
                     </div>


                     <div class="col-sm-6 mt-2">
                        <label for="exampleInputName1">Company Name <code>*</code></label>
                         <input type="text" name="company_name" class="form-control"   required="" placeholder="Company Name" value="{{$edit->company_name}}" readonly>
                     </div>

                     
                     
                     <div class="col-sm-6 mt-2">
                        <label for="exampleInputName1">Status <code>*</code></label>
                         <select class="form-control" name="status" required="">
                             <option disabled="">Select</option>
                             <option value="1" <?php if($edit->status ==1){ echo 'selected'; } ?>>Active</option>
                             <option value="0" <?php if($edit->status ==0){ echo 'selected'; } ?>>In-Active</option>
                         </select>
                     </div>
                  </div>
               </div>
            </div>
      </div>
      <div class="card-footer">
      <button type="submit" class="btn btn-success" value="1" name="exit">Save</button>
       
      <a href="javascript:;" class="btn btn-defult" onclick="history.back()" >Cancel</a>
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