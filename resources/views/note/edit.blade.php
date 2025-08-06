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
         
         <form method="POST" action="{{ route('note.update',[$edit->id])}}" enctype="multipart/form-data" name="gift_store">
            <input type="hidden" name="_method" value="PUT">
            {{ csrf_field() }}
            <div class="card-body">
               <div class="form-group">
               <div class="row">

                     

                  <div class="col-sm-6 mt-2">
                  <label for="exampleInputName1">Heading Name <code>*</code></label>
                     <input type="text" name="heading" class="form-control"  value="{{$edit->heading}}"  required="" placeholder="Heading Name">
                  </div>

                  <div class="col-sm-6 mt-2">
                  <label for="exampleInputName1">Mandatory <code>*</code></label>
                  <select class="form-control salu" name="mandatory">
                        
                        <option value="1" <?php if($edit->mandatory ==1){ echo 'selected'; } ?>>Yes</option>
                        <option value="0" <?php if($edit->mandatory ==0){ echo 'selected'; } ?>>No</option>
                           
                            
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
<script type="text/javascript">
  $(document).ready(function(){
    $('.gift').change(function(){
      let selectedVal= $(this).val();
      if(selectedVal=='Animated Gift'){
        $('.gif').show();
      }else{
        $('.gif').hide();
      }
    });
  });
</script>
@endsection