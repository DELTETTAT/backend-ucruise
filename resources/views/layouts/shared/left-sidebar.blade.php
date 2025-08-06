========== Left Sidebar Start ========== -->
<?php $sCount = App\Models\User::where('id', Auth::Id())->whereHas('roles', function ($q) {
    $q->where('name', 'superadmin');
})->count();
?>

<div class="left-side-menu">

    <div class="h-100" data-simplebar>

        <!-- User box -->
        <div class="user-box text-center">
            <img src="{{asset('assets/images/users/user-1.jpg')}}" alt="user-img" title="Mat Helme" class="rounded-circle avatar-md">
            <div class="dropdown">
                <a href="javascript: void(0);" class="text-dark dropdown-toggle h5 mt-2 mb-1 d-block" data-toggle="dropdown">Geneva Kennedy</a>
                <div class="dropdown-menu user-pro-dropdown">

                    <!-- item-->
                    <a href="javascript:void(0);" class="dropdown-item notify-item">
                        <i class="fe-user mr-1"></i>
                        <span>My Account</span>
                    </a>

                    <!-- item-->
                    <a href="javascript:void(0);" class="dropdown-item notify-item">
                        <i class="fe-settings mr-1"></i>
                        <span>Settings</span>
                    </a>

                    <!-- item-->
                    <a href="javascript:void(0);" class="dropdown-item notify-item">
                        <i class="fe-lock mr-1"></i>
                        <span>Lock Screen</span>
                    </a>

                    <!-- item-->
                    <a href="javascript:void(0);" class="dropdown-item notify-item">
                        <i class="fe-log-out mr-1"></i>
                        <span>Logout</span>
                    </a>

                </div>
            </div>
            <p class="text-muted">Admin Head</p>
        </div>

        <!--- Sidemenu -->
        <div id="sidebar-menu">

            <ul id="side-menu">

                <li class="menu-title">Navigation</li>

                <li>
                    <!-- <a href="#sidebarDashboards" data-toggle="collapse">
                        <i data-feather="airplay"></i>
                        <span class="badge badge-success badge-pill float-right">4</span>
                        <span> Admin </span>
                    </a> -->
                    <!-- <div class="collapse" id="sidebarDashboards">
                        <ul class="nav-second-level">
                            <li>
                                <a href="{{route('admin.dashboard')}}">Dashboard</a>
                            </li>
                        </ul>
                    </div> -->
                </li>

                <!-- <li class="menu-title mt-2">Apps</li> -->
                @if($sCount == 0)
                <li>
                    <a href="{{url('admin/scheduler')}}">
                        <i data-feather="calendar"></i>
                        <span> Scheduler </span>
                    </a>
                </li>
                @endif
                @if($sCount != 0)
                <li>
                    <a href="{{route('admin.dashboard')}}">
                        <i data-feather="home"></i>
                        <span> Dashboard </span>
                    </a>
                </li>
                @endif



                @if($sCount > 0)
                <li>
                    <a href="{{url('/admin/allAdmin')}}">
                        <i data-feather="users"></i>
                        <span> List Users </span>
                    </a>
                </li>

                <li>
                    <a href="{{route('subscription.index')}}">
                        <i data-feather="clipboard"></i>
                        <span> Subscription </span>
                    </a>
                </li>


                <!-- <li>
                    <a href="{{url('admin/send-announcement')}}">
                        <i data-feather="mic"></i>
                        <span> Bulk Announcement </span>
                    </a>
                </li> -->

                <li>
                    <a href="{{url('admin/list-announcement')}}">
                        <i data-feather="mic"></i>
                        <span> Announcement </span>
                    </a>
                </li>

                <li>
                    <a href="{{url('admin/inactive-users')}}">
                        <i data-feather="users"></i>
                        <span> Close Account </span>
                    </a>
                </li>

                <li>
                    <a href="{{url('admin/group-login-users')}}">
                        <i data-feather="users"></i>
                        <span> Group Users</span>
                    </a>
                </li>
                   <li>
                    <a href="{{url('admin/images-template')}}">
                        <i data-feather="users"></i>
                        <span> Images</span>
                    </a>
                </li>


                @endif
            
            
        
                 {{-- <li>
                    <a href="{{url('admin/category')}}">
                        <i data-feather="clipboard"></i>
                        <span>Category</span>
                    </a>
                </li>  --}}




                @if($sCount == 0)

                 {{-- <li style="display:none1">
                    <a href="{{route('roles')}}">
                        <i data-feather="users"></i>
                        <span> Roles </span>
                        <span class="menu-arrow"></span>
                    </a>
                </li>  --}}

                <li style="display:none1">
                    <a href="{{url('users/staff')}}">
                        <i data-feather="user-plus"></i>
                        <span> Staff </span>
                        <span class="menu-arrow"></span>
                    </a>
                    <div>
                        <ul class="nav-second-level">
                            
                            <li>
                                <a href="{{url('users/staff')}}">
                                    <!--                                 <i data-feather="clipboard"></i> -->
                                    <span>List Staff</span>
                                </a>
                            </li>
                            <li>
                                <a href="{{route('arcchiveStaff')}}">
                                    <!--                                 <i data-feather="clipboard"></i> -->
                                    <span>Archived Staff</span>
                                </a>
                            </li>

                            <li>
                                <a href="{{route('expireStaffDocuments')}}">
                                    <!--                                 <i data-feather="clipboard"></i> -->
                                    <span>Expired Documents</span>
                                </a>
                            </li>

                            <li>
                                <a href="{{route('teams')}}">
                                    <!--                                 <i data-feather="clipboard"></i> -->
                                    <span>List Teams</span>
                                </a>
                            </li>
                            <li>
                                <a href="{{url('users/add-staff')}}">
                                    <!--                                 <i data-feather="clipboard"></i> -->
                                    <span>New</span>
                                </a>
                            </li>


                        </ul>
                    </div>
                </li>


                <!-- For client -->
                <li style="display:none1">
                    <a href="{{route('clients.index')}}">
                        <i data-feather="users"></i>
                        <span> Clients </span>
                        <span class="menu-arrow"></span>
                    </a>
                    <div>
                        <ul class="nav-second-level">


                            <li>
                                <a href="{{route('clients.index')}}">
                                    <!--                                 <i data-feather="clipboard"></i> -->
                                    <span>List Clients</span>
                                </a>
                            </li>

                            <li>
                                <a href="{{route('arcchiveClients')}}">
                                    <!--                                 <i data-feather="clipboard"></i> -->
                                    <span>Archived Clients</span>
                                </a>
                            </li>

                            <li>
                                <a href="{{route('expireClientDocuments')}}">
                                    <!--                                 <i data-feather="clipboard"></i> -->
                                    <span>Expired Documents</span>
                                </a>
                            </li>
                            <li>
                                <a href="{{route('newClient')}}">
                                    <span>New</span>
                                </a>
                            </li>
                            <li>
                                <a href="{{route('clients.create')}}">
                                    <!--                                 <i data-feather="clipboard"></i> -->
                                    <span>New</span>
                                </a>
                            </li>
                            <li>
                                <a href="{{url('users/vehicles/add')}}">
                                    <!--                                 <i data-feather="clipboard"></i> -->
                                    <span>Add vehicles</span>
                                </a>
                            </li>
                            
                            <li>
                                <a href="{{url('users/vehicles/show')}}">
                                    <!--                                 <i data-feather="clipboard"></i> -->
                                    <span>All vehicles</span>
                                </a>
                            </li>
                            

                        </ul>
                    </div>
                </li>







                
                <!-- <li style="display:none1">
                    <a href="{{route('compliance.index')}}">
                        <i data-feather="pie-chart"></i>
                        <span> Report </span>
                        <span class="menu-arrow"></span>
                    </a>
                    <div class="collapse" id="report">
                        <ul class="nav-second-level">
                            <li>
                                <a href="{{route('compliance.index')}}">
                                    <span>Compliance</span>
                                </a>
                            </li>
                        </ul>
                    </div>  
                </li> -->


                <li>
                    <a href="{{url('users/accounts')}}">
                        <i data-feather="tool"></i>
                        <span> Account </span>

                    </a>
                </li>

                    {{-- Vehicles --}}
                <li style="display:none1">
                    <a href="{{route('leave')}}">
                        <i data-feather="minus-square"></i>
                        <span>Leaves</span>
                    </a> 
                </li>

                <!-- <li style="display:none1">
                    <a href="{{route('reschedule.index')}}">
                        <i data-feather="minus-square"></i>
                        <span>Reschedule Requests</span>
                    </a> 
                </li> -->

                <!-- Account -->
                <li style="display:none1">
                    <a href="{{url('users/invoice_settings')}}">
                        <i data-feather="settings"></i>
                        <span> Setting </span>
                        <span class="menu-arrow"></span>
                    </a>
                    <!-- <div class="collapse" id="account"> -->
                    <div>
                        <ul class="nav-second-level">
                            <li>
                                <a href="{{url('users/account')}}">
                                    <!--                                 <i data-feather="clipboard"></i> -->
                                    <span>Settings</span>
                                </a>
                            </li>
                            <li>
                                <a href="{{url('users/invoice_settings')}}">
                                    <!--                                 <i data-feather="clipboard"></i> -->
                                    <span>Invoice Settings</span>
                                </a>
                            </li>

                            <li>
                                <a href="{{route('prices.index')}}">
                                    <!--                                 <i data-feather="clipboard"></i> -->
                                    <span>Prices</span>
                                </a>
                            </li>

                            <li>
                                <a href="{{route('award_group.index')}}">
                                    <!--                                 <i data-feather="clipboard"></i> -->
                                    <span>Pay Groups</span>
                                </a>
                            </li>

                            <li>
                                <a href="{{route('allowance.index')}}">
                                    <!--                                 <i data-feather="clipboard"></i> -->
                                    <span>Allowances</span>
                                </a>
                            </li>

                            <li>
                                <a href="{{url('users/reminders')}}">
                                    <!--                                 <i data-feather="clipboard"></i> -->
                                    <span>Reminders</span>
                                </a>
                            </li>

                            <li>
                                <a href="{{url('users/subscription')}}">
                                    <!--                                 <i data-feather="clipboard"></i> -->
                                    <span>Subscription</span>
                                </a>
                            </li>

                        </ul>
                    </div>
                </li>


               


                @endif




                



            </ul>

        </div>
        <!-- End Sidebar -->

        <div class="clearfix"></div>

    </div>
    <!-- Sidebar -left -->
    <li class="settingbottom">
        <a href="javascript:void(0);" class="waves-effect">
            <i class="fe-settings noti-icon"></i>
        </a>
    </li>
</div>
<!-- Left Sidebar End