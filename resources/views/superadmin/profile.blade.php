@extends('layouts.vertical', ['title' => 'Profile'])
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
                  <li class="breadcrumb-item"><a href="javascript: void(0);">Profile</a></li>
               
               </ol>
            </div>
            <h4 class="page-title">Profile</h4>
         </div>
      </div>
   </div>
   <!-- end page title --> 
   <div class="row">
      <div class="col-lg-12 card">
            @if ($message = Session::get('success'))  
            <div class="alert alert-success alert-block">  
            <button type="button" class="close" data-dismiss="alert">X</button>   
            <strong>{{ $message }}</strong>  
            </div>  
            @endif 
         
         <form method="POST" action="{{ url('admin/updateProfile') }}" enctype="multipart/form-data" name="gift_store">
            {{ csrf_field() }}
            <div class="card-body">
               <div class="form-group">
                  <div class="row">

                      <div class="col-sm-6 mt-2">
                        <label for="exampleInputName1">First Name <code>*</code></label>
                         <input type="text" name="first_name" value="{{@$user->first_name}}" class="form-control"   required="" placeholder="First Name">
                     </div>

                     <div class="col-sm-6 mt-2">
                        <label for="exampleInputName1">Last Name <code>*</code></label>
                         <input type="text" name="last_name" value="{{@$user->last_name}}" class="form-control"   required="" placeholder="Last Name">
                     </div>

                     <div class="col-sm-6 mt-2">
                        <label for="exampleInputName1">Email <code>*</code></label>
                         <input type="text" name="email" value="{{@$user->email}}" class="form-control"   required="" placeholder="Email" readonly>
                     </div>

                     <div class="col-sm-6 mt-2">
                        <label for="exampleInputName1">Phone <code>*</code></label>
                         <input type="text" name="phone" value="{{@$user->phone}}" class="form-control"   required="" placeholder="Phone">
                     </div>

                     

                      

                      

                     
                     
                     
                  </div>
               </div>
            </div>
      </div>
      <div class="card-footer">
      <button type="submit" class="btn btn-primary" value="1" name="exit">Save</button>
       
      <a href="javascript:;" class="btn btn-danger" onclick="history.back()" >Back</a>
      </div>
      </form>
   </div>
   <!-- end col -->
</div>
<!-- end row -->
</div> <!-- container -->
<link href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.2-rc.1/css/select2.min.css" rel="stylesheet" />
<script src="https://code.jquery.com/jquery-2.1.1.min.js" type="text/javascript"></script>

<script type="text/javascript">
  $(document).ready(function() {
    $('#users').select2({
    placeholder: 'Select an option'
  });
    
    $("#chkall").click(function(){
        if($("#chkall").is(':checked')){
            $("#users > option").prop("selected", true);
            $("#users").trigger("change");
        } else {
            $("#users > option").prop("selected", false);
            $("#users").trigger("change");
        }
    });
});
</script>
@endsection
 