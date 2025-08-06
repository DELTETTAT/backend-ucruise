@extends('layouts.vertical', ['title' => 'Group create'])
@section('content')
    <style>
        #userList {
            list-style: none;
            padding: 0;
            margin-top: 5px;
        }

        #userList li {
            padding: 6px;
            background: #f2f2f2;
            margin-bottom: 4px;
            border-radius: 4px;
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
                            <li class="breadcrumb-item"><a href="javascript: void(0);">Group create</a></li>

                        </ol>
                    </div>
                    <h4 class="page-title">Group create</h4>
                </div>
            </div>
        </div>
        <!-- end page title -->
        <div class="row">
            <div class="col-lg-6 card">
                <input type="text" id="emailSearch" class="form-control" placeholder="Search email..."
                    autocomplete="off">
                <ul id="userList"></ul>
                <div id="selectUsersWrapper" style="display: none;">
                    <button class="btn btn-primary mt-2" id="saveSelectedUsers">Select Users</button>
                </div>

            </div>
            <!-- end col -->
        </div>
        <!-- end row -->
    </div> <!-- container -->


    <link href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.2-rc.1/css/select2.min.css" rel="stylesheet" />
    <script src="https://code.jquery.com/jquery-2.1.1.min.js" type="text/javascript"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>


    <script>
        let selectedUserIds = [];
        $('#emailSearch').on('keyup', function() {
            let email = $(this).val();

            if (email.length > 1) {
                $.ajax({
                    url: '/uc/admin/search-sub-users',
                    type: 'GET',
                    data: {
                        email: email
                    },
                    success: function(response) {
                        let users = [];
                        if (Array.isArray(response.users)) {
                            users = response.users;
                        } else if (Array.isArray(response)) {
                            users = response;
                        }

                        $('#userList').empty();

                        if (users.length > 0) {
                            users.forEach(function(user) {
                                let checked = selectedUserIds.includes(user.id.toString()) ?
                                    'checked' : '';
                                $('#userList').append(`
                                <li>
                                    <input type="checkbox" class="user-checkbox" value="${user.id}" ${checked}>
                                    ${user.email}
                                </li>
                            `);
                            });

                            if (users.length >= 2) {
                                $('#selectUsersWrapper').show();
                            } else {
                                $('#selectUsersWrapper').hide();
                            }

                            // Rebind checkbox change after list refresh
                            $('.user-checkbox').on('change', function() {
                                let userId = $(this).val();
                                if ($(this).is(':checked')) {
                                    if (!selectedUserIds.includes(userId)) {
                                        selectedUserIds.push(userId);
                                    }
                                } else {
                                    selectedUserIds = selectedUserIds.filter(id => id !==
                                        userId);
                                }
                            });

                        } else {
                            $('#userList').append('<li>No users found</li>');
                            $('#selectUsersWrapper').hide();
                        }
                    },
                    error: function() {
                        $('#userList').empty().append('<li>Error fetching users</li>');
                        $('#selectUsersWrapper').hide();
                    }
                });
            } else {
                $('#userList').empty();
                $('#selectUsersWrapper').hide();
            }
        });

        // Save selected users to DB
        $('#saveSelectedUsers').on('click', function() {

            if (selectedUserIds.length === 0) {
                alert('Please select user!');
                return;
            }

            if (selectedUserIds.length === 1) {
                alert('Please select at least two users');
                return;
            }

            // var pwd = $('#Grouppassword').val();
            //  if (!pwd) {
            //     alert('Please add group password.');
            //     return;
            // }

            var selectedEmails = [];
            $('#userList input.user-checkbox:checked').each(function() {
                selectedEmails.push($(this).closest('li').text().trim());
            });

            $.ajax({
                url: '/uc/admin/save-selected-users',
                type: 'POST',
                data: {
                    users: selectedUserIds,
                    emails: selectedEmails,
                    _token: '{{ csrf_token() }}'
                },
                success: function(res) {
                    alert('Users saved successfully');
                    selectedUserIds = [];
                    $('#userList').empty();
                    $('#emailSearch').val('');
                    // $("#userModal").hide();
                    // $('.modal-backdrop').remove();
                    // $('body').removeClass('modal-open');
                    // $('body').css('padding-right', '');
                },
                error: function() {
                    alert('Error saving users');
                }
            });
        });
    </script>
@endsection
