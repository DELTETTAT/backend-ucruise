@extends('layouts.vertical', ['title' => 'Send Bulk Announcement'])
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
                  <li class="breadcrumb-item"><a href="javascript: void(0);">Send Bulk Announcement</a></li>
               
               </ol>
            </div>
            <h4 class="page-title">Send Bulk Announcement</h4>
         </div>
      </div>
   </div>
   <!-- end page title --> 
   <div class="row">
      <div class="col-lg-12 card">
            
         
         <form method="POST" action="{{ url('admin/storeAnnouncement') }}" enctype="multipart/form-data" name="gift_store">
            {{ csrf_field() }}
            <div class="card-body">
               <div class="form-group">
                  <div class="row">

                      <div class="col-sm-6 mt-2">
                        <label for="exampleInputName1">Title <code>*</code></label>
                         <input type="text" name="title" class="form-control"   required="" placeholder="Title">
                     </div>

                     <div class="col-sm-6 mt-2">
                        <label for="exampleInputName1">Image </label>
                         <input type="file" name="file" class="form-control" >
                     </div>

                     <!-- <div class="col-sm-6 mt-2">
                        <label for="exampleInputName1">Users <code>*</code></label>
                         <select class="form-control" name="userid[]" required="" multiple id="users" >
                              

                             @foreach($allUsers as $data)
                             <option value="{{$data->id}}">{{$data->first_name}}</option>
                              @endforeach
                         </select>
                         <input id="chkall" type="checkbox" >Select All
                     </div> -->

                     <div class="col-sm-6 mt-2">
                        <label for="exampleInputName1">Description <code>*</code></label>
                         <textarea   name="description" class="form-control"   required="" ></textarea>
                     </div>

                     

                      

                     
                     
                     
                  </div>
               </div>
            </div>
      </div>
      <div class="card-footer">
      <button type="submit" class="btn btn-primary" value="1" name="exit">Send</button>
       
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
 