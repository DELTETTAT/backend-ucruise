@extends('layouts.vertical', ['title' => 'Group Users'])

@section('css')
<!-- Plugins css -->
<link href="{{asset('assets/libs/datatables/datatables.min.css')}}" rel="stylesheet" type="text/css" />
<style>
    .img-thumbnail {
        max-width: 100px;
        height: auto;
    }
</style>
@endsection

@section('content')
<!-- Start Content-->
<div class="container-fluid">

    <!-- start page title -->
    <div class="row">
        <div class="col-12">
            <div class="page-title-box">
                <div class="page-title-right">
                    <ol class="breadcrumb m-0">
                        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Home</a></li>
                        <li class="breadcrumb-item active">List Images</li>
                    </ol>
                </div>
                <h4 class="page-title">List Images</h4>
            </div>
        </div>
    </div>
    <!-- end page title -->

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <a href="{{url('admin/create-image')}}" class="btn btn-primary mb-3" style="float: right;">Add Images</a>
                    <table id="imagesTable" class="table table-hover table-bordered table-striped">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Image</th>
                                <th>Category</th>
                                <th>Position</th>
                                <th>Title</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <!-- Data will be loaded via AJAX -->
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('script')
<!-- Plugins js-->
<script src="{{asset('assets/libs/datatables/datatables.min.js')}}"></script>
<script src="{{asset('assets/libs/pdfmake/pdfmake.min.js')}}"></script>

<script>
$(document).ready(function() {
    // Initialize DataTable
    var table = $('#imagesTable').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: '/uc/admin/imageindex',
            dataSrc: 'data'
        },
        columns: [
            { data: 'id' },
            { 
                data: 'file_path',
                render: function(data, type, row) {
                    return `<img src="${data}" class="img-thumbnail" alt="${row.title}">`;
                }
            },
            { data: 'category' },
            { data: 'position' },
            { data: 'title' },
            {
                data: 'id',
                render: function(data, type, row) {
                    return `
                        <div class="btn-group">
                            
                            <button class="btn btn-sm btn-danger delete-btn" data-id="${data}">Delete</button>
                        </div>
                    `;
                }
            }
        ]
    });

    // Delete functionality
    $('#imagesTable').on('click', '.delete-btn', function() {
        var id = $(this).data('id');
        if (confirm('Are you sure you want to delete this image?')) {
            $.ajax({
                url: `/uc/admin/imagedestroy/${id}`,
                
                
        
                success: function(response) {
                    if (response.success) {
                        table.ajax.reload();
                        toastr.success(response.message);
                    } else {
                        toastr.error(response.message);
                    }
                },
                error: function(xhr) {
                    toastr.error('Error deleting image');
                }
            });
        }
    });
});
</script>
@endsection