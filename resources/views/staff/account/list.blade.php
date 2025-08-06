@extends('layouts.vertical', ['title' => 'Add Staff'])
@section('css')
<!-- Plugins css -->
<link href="{{asset('assets/libs/ladda/ladda.min.css')}}" rel="stylesheet" type="text/css" />
@endsection
@section('content')
<!-- Start Content-->
<div class="container-fluid">
   <!-- start page title -->
   <!-- <div class="row">
      <div class="col-12">
         <div class="page-title-box">
            <div class="page-title-right">
               <ol class="breadcrumb m-0">
                  <li class="breadcrumb-item"><a href="javascript: void(0);">Home</a></li>
                  <li class="breadcrumb-item"><a href="javascript: void(0);">Settings</a></li>
               </ol>
            </div>
            <h4 class="page-title">Settings</h4>
         </div>
      </div>
   </div> -->
   <!-- end page title -->
   @if ($message = Session::get('success'))
   <div class="alert alert-success alert-block">
      <button type="button" class="close" data-dismiss="alert">X</button>
      <strong>{{ $message }}</strong>
   </div>
   @endif
   @if ($message = Session::get('warning'))
   <div class="alert alert-danger alert-block">
      <button type="button" class="close" data-dismiss="alert">X</button>
      <strong>{{ $message }}</strong>
   </div>
   @endif
   <div class="row mt-3">
      <div class="col-2">
         <ul class="nav_list" id="mainNav">
            <li class="activeli">
               <a href="#Company_Details"><span>Company Details</span></a>
            </li>
            <li>
               <a href="#public_information"><span>Client public information headings</span></a>
            </li>
            <li>
               <a href="#Note_permission"><span>Note permission</span></a>
            </li>
            <li>
               <a href="#Notes_headings"><span>Notes headings</span></a>
            </li>
            <li>
               <a href="#Leave_types"><span>Leave types</span></a>
            </li>
            <li>
               <a href="#Leave_reasons"><span>Leave Reasons</span></a>
            </li>
            <li>
               <a href="#Complaint_reasons"><span>Complaint Reasons</span></a>
            </li>
            <li>
               <a href="#Rating_reasons"><span>Rating Reasons</span></a>
            </li>
            <li>
               <a href="#Cancellation_reasons"><span>Cancel ride Reasons</span></a>
            </li>

            <li>
               <a href="#Shift_change_reasons"><span>Shift Change Reasons</span></a>
            </li>
            <li>
               <a href="#Temp_change_reasons"><span>Temp location change Reasons</span></a>
            </li>
            <li>
               <a href="#Shift_types"><span>Shift types</span></a>
            </li>
            <li>
               <a href="#Client_document"><span>Client document categories</span></a>
            </li>
            <li>
               <a href="#Scheduler"><span>Scheduler</span></a>
            </li>
            <li>
               <a href="#Report_heading"><span>Report heading</span></a>
            </li>
            <li>
               <a href="#attendance"><span>Time and attendance</span></a>
            </li>
            <li>
               <a href="#ridesetting"><span>Ride Settings</span></a>
            </li>
            <li>
               <a href="#Public_holidays"><span>Public holidays</span></a>
            </li>
            <li>
               <a href="#faq"><span>FAQ</span></a>
            </li>
            <li>
               <a href="#schedule_template"><span>Schedule template</span></a>
            </li>
         </ul>
      </div>

      <div class="col-md-10">
         <table class="table table-design-default settingTable" id="Company_Details">
            <tr>
               <th>Company details</th>

               <th class="text-right editIcon" onClick="CompanyForm()">Edit</th>

            </tr>
            <tr>
               <td colspan="2">
                  <div id="CompanyLogo">
                     <h4>

                        <img src="{{url('/images')}}/{{@$companyDetails->logo}}" onerror="this.src='https://t3.ftcdn.net/jpg/03/45/05/92/240_F_345059232_CPieT8RIWOUk4JqBkkWkIETYAkmz2b75.jpg'" style="width: 100px;height:100px">
                        {{@@$companyDetails->name}}

                     </h4>
                     <div> {{@@$companyDetails->email}}</div>
                     <div> {{@@$companyDetails->phone}}</div>
                     <div> {{@@$companyDetails->address}} <span style="float:right;" class="editIcon"> <a href="<?php echo url('users/company-map'); ?>"><i class="mdi mdi-map-marker"></i></a></span></div>
                     <div>{{@@$companyDetails->country}}</div>
                  </div>
                  <form action="{{route('update.company')}}" method="POST" enctype="multipart/form-data" id="CompanyForm" style="display: none;">
                     @csrf
                     <div colspan="3" style="padding-top:0px;">
                        <span>Company Logo</span><br />
                        <input type="file" class="form-control col-3" name="file" id="file">
                        <span>Company Name</span><br />
                        <input type="text" class="form-control col-3" name="name" value="{{@@$companyDetails->name}}" id="name">
                        <!-- <span>Company Address</span><br />
                        <input type="text" class="form-control col-3" name="address" value="{{@@$companyDetails->address}}" id="companyaddress"> -->
                        <span>Company Email</span><br />
                        <input type="email" class="form-control col-3" name="email" value="{{@@$companyDetails->email}}" id="companyemail" readonly>
                        <!-- <input type="hidden" id="latitude" name="latitude" value="{{@@$companyDetails->latitude}}">
                        <input type="hidden" id="longitude" name="longitude" value="{{@@$companyDetails->longitude}}">
                        <input type="hidden" id="country" name="country" value="{{@@$companyDetails->country}}"> -->
                        <span>Company Phone</span><br />
                        <input type="phone" class="form-control col-3" name="phone" value="{{@@$companyDetails->phone}}" id="companyphone">
                        <div class="col-3 float-right text-right">
                           <input type="submit" class="btn btn-success">
                           <button type="reset" class="btn btn-danger" onClick="CompanyFormCancel()">Cancel</button>
                        </div>
                     </div>
                  </form>
               </td>
            </tr>
         </table>
         <!-- <div class="card">
            <div class="card-body">
               <h4 class="header-title">Company details </h4>
               <form action="{{route('update.company')}}" method="post" id="bs6s" enctype='multipart/form-data'>
                  @csrf
                  <div class="form-group">
                     <label>Name</label>
                     <input type="text" class="form-control" name="name" value="{{@$companyDetails->name}}">
                  </div>
                  <p>Country <span style="float:right">Australia</span></p>
                  <div class="form-group">
                     <label>Logo</label>
                     <input type="file" class="form-control" name="file">
                     <img src="{{url('/images')}}/{{@$companyDetails->logo}}" onerror="this.src='https://t3.ftcdn.net/jpg/03/45/05/92/240_F_345059232_CPieT8RIWOUk4JqBkkWkIETYAkmz2b75.jpg'" style="width: 100px;height:100px">
                  </div>
                  <input type="submit" class="btn btn-success">
               </form>
            </div>
         </div> -->
         <!-- end card-->
         <!-- end col -->

         <table class="table table-design-default tdbdnone settingTable" id="public_information">
            <tr>
               <th>Client public information headings</th>
               <th class="text-right editIcon"><a href="javascript:" onClick="publicInformationform()"> Edit </a></th>
            </tr>
            <tr id="publicInformationView">
               <td colspan="2">
                  <table class="col-12">
                     <tr>
                        <td colspan="2">
                           <span>Heading</span><br />
                           @foreach($useInfo as $useInf)
                           <button class="btn lightorange br10">{{$useInf->heading}} </button>
                           <!-- <button class="btn lightgreen br10" type="button">Risk</button> -->
                           @endforeach
                        </td>
                     </tr>
                  </table>
               </td>
            </tr>
            <tr>
               <td colspan="2">
                  <div style="display:none" id="publicInformationform">
                     <div class="row">
                        <div class="col-12">
                           <span>Heading</span><br />
                           @foreach($useInfo as $useInf)
                           <a href="<?php echo url('/users/deleteNote'); ?>/{{$useInf->id}}" class="btn lightorange br10" onclick="return confirm('Are you sure you want to delete this?')">{{$useInf->heading}} <i data-feather="x" class="close2"></i></a>
                           <!-- <button class="btn lightgreen br10" type="button">Risk <i data-feather="x" class="close2"></i></button> -->
                           @endforeach
                        </div>
                     </div>
                     <form action="{{route('note.store')}}" method="POST" enctype="multipart/form-data" method="post">
                        @csrf
                        <div class="row">
                           <div class="form-group col-3">
                              <input type="text" class="form-control" placeholder="Add new heading" name="heading" required>
                              <input type="hidden" value="Useful information" name="category_name" required>
                           </div>
                           <div>
                              <!-- <button type="submit" class="btn btn-success" style="padding:10px!Important">+ Add</button> -->
                           </div>
                        </div>
                        <div class="text-right">
                           <input type="submit" class="btn btn-success mt-2" value="Submit">
                           <button type="reset" class="btn btn-danger mt-2" onClick="publicInformationCancel()">Cancel</button>
                        </div>
                     </form>
                  </div>
               </td>
            </tr>
         </table>
         <table class="table table-design-default tdbdnone settingTable" id="Note_permission">
            <tr>
               <th colspan="2">Note permission</th>
               <th class="text-right editIcon"><a href="javascript:" onClick="notePermissionForm()"> Edit </a></th>
            </tr>
            <tr>
               <td colspan="3">
                  <table class="col-12" id="notePermissionView">
                     <tr>
                        <td class="d-flex">
                           <p> Allow not edit</p>
                        </td>
                        <td>
                           <div class="custom-control custom-switch text-center">
                              <input type="checkbox" class="custom-control-input" id="customSwitche2" <?php if (@$nP->note_edit == 'on') {
                                                                                                         echo 'checked';
                                                                                                      } ?> disabled>
                              <label class="custom-control-label" for="customSwitche2">Allow not edit</label>
                           </div>
                        </td>
                        <td class="text-right">{{@@$nP->expire_access}} Days</td>
                     </tr>
                  </table>
               </td>
            </tr>
            <tr>
               <td colspan="3">

                  <form action="{{route('update.notePermission')}}" method="post" style="display:none" id="notePermissionForm">
                     @csrf
                     <div class="row">
                        <div class="col-5">
                           <p> Allow not edit {{@$nP->note_edit}}</p>
                        </div>
                        <div class="col-4">
                           <div class="custom-control custom-switch text-center">
                              <input type="checkbox" name="note_edit" class="custom-control-input" id="customSwitches" <?php if (@$nP->note_edit == 'on') {
                                                                                                                           echo 'checked';
                                                                                                                        } ?>>
                              <label class="custom-control-label" for="customSwitches">Allow not edit</label>
                           </div>
                        </div>
                        <div class="col-3">
                           <input type="number" class="form-control" name="expire_access" value="{{@$nP->expire_access}}">
                           <input type="submit" class="btn btn-success mt-2">
                           <button type="reset" class="btn btn-danger mt-2" onClick="notePermissionFormCancel()">Cancel</button>
                        </div>
                     </div>
                  </form>
               </td>
            </tr>

         </table>
         <table class="table table-design-default settingTable tdbdnone" id="Notes_headings">
            <tr>
               <th>Notes headings</th>
               <th class="text-right editIcon"></th>
            </tr>
            <tr>
               <td>
                  <span>Progress Notes</span><br />
                  <div id="noteProgessView">
                     @foreach($pNotes as $pNote)
                     <button class="btn lightorange br10">{{$pNote->heading}} </button>
                     <!-- <button class="btn lightgreen br10">Behaviour </button>
                     <button class="btn lightorange br10">Presentation </button>
                     <button class="btn lightyellow br10">Activity </button>
                     <button class="btn lightgreen br10">Support provided on shift </button> -->
                     @endforeach
                  </div>
                  <div style="display:none" id="noteProgessForm">
                     <div class="row">
                        <div class="col-12">
                           @foreach($pNotes as $pNote)
                           <a href="<?php echo url('/users/deleteNote'); ?>/{{$pNote->id}}" class="btn lightorange br10" onclick="return confirm('Are you sure you want to delete this?')">{{$pNote->heading}} <i data-feather="x" class="close2"></i></a>
                           <!-- <button class="btn lightgreen br10" type="button">Behaviour <i data-feather="x" class="close2"></i></button>
                           <button class="btn lightorange br10" type="button">Presentation <i data-feather="x" class="close2"></i></button>
                           <button class="btn lightyellow br10" type="button">Activity <i data-feather="x" class="close2"></i></button>
                           <button class="btn lightgreen br10" type="button">Support provided on shift<i data-feather="x" class="close2"></i></button> -->
                           @endforeach
                        </div>
                     </div>
                     <form action="{{route('note.store')}}" method="POST" enctype="multipart/form-data" method="post">
                        @csrf
                        <div class="row">
                           <div class="form-group col-3">
                              <input type="text" class="form-control" placeholder="Add new heading" name="heading" required>
                              <input type="hidden" value="Progress Notes" name="category_name" required>
                           </div>
                           <!-- <div>
                              <button type="button" class="btn btn-success" style="padding:10px!Important">+ Add</button>
                           </div> -->
                        </div>
                        <div class="text-right">
                           <input type="submit" class="btn btn-success mt-2" value="Submit">
                           <button type="reset" class="btn btn-danger mt-2" onClick="noteProgessCancel()">Cancel</button>
                        </div>
                     </form>
                  </div>
               </td>
               <td class="text-right editIcon"><a href="javascript:" onClick="noteProgessForm()"> Edit </a></td>
            </tr>
            <tr>
               <td>
                  <span>Feedback</span><br />
                  <div id="FeedbackView">
                     @foreach($fNotes as $fNote)
                     <button class="btn lightgreen br10">{{$fNote->heading}}</button>
                     <!-- <button class="btn lightorange br10">Text name </button> -->
                     @endforeach
                  </div>
                  <div style="display:none" id="FeedbackForm">
                     <div class="row">
                        <div class="col-12">
                           @foreach($fNotes as $fNote)
                           <a href="<?php echo url('/users/deleteNote'); ?>/{{$fNote->id}}" onclick="return confirm('Are you sure you want to delete this?')" class="btn lightgreen br10">{{$fNote->heading}}<i data-feather="x" class="close2"></i></a>
                           <!-- <button class="btn lightorange br10" type="button">Text name <i data-feather="x" class="close2"></i></button> -->
                           @endforeach
                        </div>
                     </div>
                     <form action="{{route('note.store')}}" method="POST" enctype="multipart/form-data" method="post">
                        @csrf
                        <div class="row">
                           <div class="form-group col-3">
                              <input type="text" class="form-control" placeholder="Add new heading" name="heading" required>
                              <input type="hidden" value="Feedback" name="category_name" required>
                           </div>
                           <!-- <div>
                           <button type="button" class="btn btn-success" style="padding:10px!Important">+ Add</button>
                        </div> -->
                        </div>
                        <div class="text-right">
                           <input type="submit" class="btn btn-success mt-2" value="Submit">
                           <button type="reset" class="btn btn-danger mt-2" onClick="FeedbackCancel()">Cancel</button>
                        </div>
                     </form>
                  </div>
               </td>
               <td class="text-right editIcon"><a href="javascript:" onClick="FeedbackForm()"> Edit </a></td>
            </tr>
            <tr>
               <td>
                  <span>Incident</span><br />
                  <div id="IncidentView">
                     @foreach($inc as $in)
                     <button class="btn lightgreen br10">{{$in->heading}} </button>
                     <!-- <button class="btn lightorange br10">Text name </button> -->
                     @endforeach
                  </div>
                  <div style="display:none" id="IncidentForm">
                     <div class="row">
                        <div class="col-12">
                           @foreach($inc as $in)
                           <a href="<?php echo url('/users/deleteNote'); ?>/{{$in->id}}" onclick="return confirm('Are you sure you want to delete this?')" class="btn lightgreen br10">{{$in->heading}} <i data-feather="x" class="close2"></i></a>
                           <!-- <button class="btn lightorange br10" type="button">Text name <i data-feather="x" class="close2"></i></button> -->
                           @endforeach
                        </div>
                     </div>

                     <form action="{{route('note.store')}}" method="POST" enctype="multipart/form-data" method="post">
                        @csrf
                        <div class="row">
                           <div class="form-group col-3">
                              <input type="text" class="form-control" placeholder="Add new heading" name="heading" required>
                              <input type="hidden" value="Incident" name="category_name" required>
                           </div>
                           <!-- <div>
                              <button type="button" class="btn btn-success" style="padding:10px!Important">+ Add</button>
                           </div> -->
                        </div>
                        <div class="text-right">
                           <input type="submit" class="btn btn-success mt-2" value="Submit">
                           <button type="reset" class="btn btn-danger mt-2" onClick="IncidentCancel()">Cancel</button>
                        </div>
                     </form>
                  </div>
               </td>
               <td class="text-right editIcon"><a href="javascript:" onClick="IncidentForm()"> Edit </a></td>
            </tr>
            <tr>
               <td>
                  <span>Enquiry</span><br />
                  <div id="EnquiryView">
                     @foreach($enq as $en)
                     <button class="btn lightgreen br10">{{$en->heading}} </button>
                     <!-- <button class="btn lightorange br10">Text Name </button> -->
                     @endforeach
                  </div>
                  <div style="display:none" id="EnquiryForm">
                     <div class="row">
                        <div class="col-12">
                           @foreach($enq as $en)
                           <a href="<?php echo url('/users/deleteNote'); ?>/{{$en->id}}" onclick="return confirm('Are you sure you want to delete this?')" class="btn lightgreen br10">{{$en->heading}} <i data-feather="x" class="close2"></i></a>
                           <!-- <button class="btn lightorange br10" type="button">Text name <i data-feather="x" class="close2"></i></button> -->
                           @endforeach

                        </div>
                     </div>
                     <form action="{{route('note.store')}}" method="POST" enctype="multipart/form-data" method="post">
                        @csrf
                        <div class="row">
                           <div class="form-group col-3">
                              <input type="text" class="form-control" placeholder="Add new heading" name="heading" required>
                              <input type="hidden" value="Enquiry" name="category_name" required>
                           </div>
                           <!-- <div>
                           <button type="button" class="btn btn-success" style="padding:10px!Important">+ Add</button>
                        </div> -->
                        </div>
                        <div class="text-right">
                           <input type="submit" class="btn btn-success mt-2" value="Submit">
                           <button type="reset" class="btn btn-danger mt-2" onClick="EnquiryCancel()">Cancel</button>
                        </div>
                     </form>
                  </div>
               </td>
               <td class="text-right editIcon"><a href="javascript:" onClick="EnquiryForm()"> Edit </a></td>
            </tr>
         </table>
         <table class="table table-design-default tdbdnone settingTable" id="Leave_types">
            <tr>
               <th>Leave types</th>
               {{-- <th class="text-right editIcon"><a href="javascript:" onClick="shiftform()"> Edit </a></th> --}}
            </tr>
            <tr id="shiftView">
               <td colspan="2">
                  <table class="col-12">
                     <tr>
                        <td colspan="2">
                           <button class="btn lightgreen br10"><span class="smallbox btn-success"></span>Full leave</button>
                           <button class="btn lightorange br10"><span class="smallbox btn-warning"></span>Morning half</button>
                           <button class="btn lightyellow br10"><span class="smallbox btn-info"></span>Evening half</button>

                     </tr>
                  </table>
               </td>
            </tr>

         </table>
         <table class="table table-design-default settingTable tdbdnone" id="Leave_reasons">
            <tr>
               <th>Leave Reasons</th>
               <th class="text-right editIcon"><a href="javascript:" onClick="LeaveReasonsForm()"> Edit </a></th>
            </tr>
            <tr id="LeaveReasonsView">
               <td colspan="2">
                  <table class="col-12">
                     <tr>
                        <td colspan="2">
                           @foreach($leaveReasons as $leaveReason)
                           <button class="btn btn-light br10 m-1">{{$leaveReason->message}} </button>
                           @endforeach
                        </td>
                     </tr>
                  </table>
               </td>
            </tr>
            <tr>
               <td colspan="2">
                  <div style="display:none" id="LeaveReasonsForm">
                     <div class="row">
                        <div class="col-12">
                           @foreach($leaveReasons as $leaveReason)
                           <a href="<?php echo url('/users/deleteReason'); ?>/{{$leaveReason->id}}" onclick="return confirm('Are you sure you want to delete this?')" class="btn btn-light br10 lightgreen m-1">{{$leaveReason->message}} <i data-feather="x" class="close2"></i></a>
                           @endforeach
                        </div>
                     </div>
                     <form action="{{route('leaveReason.store')}}" method="POST" enctype="multipart/form-data" method="post">
                        @csrf
                        <div class="row">
                           <div class="form-group col-3">
                              <input type="text" class="form-control" placeholder="Add new reason" name="message" required>
                           </div>
                           <!-- <div>
                              <button type="button" class="btn btn-success" style="padding:10px!Important">+ Add</button>
                           </div> -->
                        </div>
                        <div class="text-right">
                           <input type="submit" class="btn btn-success mt-2" value="Submit">
                           <button type="reset" class="btn btn-danger mt-2" onClick="LeaveReasonsCancel()">Cancel</button>
                        </div>
                     </form>
                  </div>
               </td>
            </tr>
         </table>
         <table class="table table-design-default settingTable tdbdnone" id="Complaint_reasons">
            <tr>
               <th>Complaint Reasons</th>
               <th class="text-right editIcon"><a href="javascript:" onClick="ComplaintReasonsForm()"> Edit </a></th>
            </tr>
            <tr id="ComplaintReasonsView">
               <td colspan="2">
                  <table class="col-12">
                     <tr>
                        <td colspan="2">
                           @foreach($complaintReasons as $complaintReason)
                           <button class="btn btn-light br10 m-1">{{$complaintReason->message}} </button>
                           @endforeach
                        </td>
                     </tr>
                  </table>
               </td>
            </tr>
            <tr>
               <td colspan="2">
                  <div style="display:none" id="ComplaintReasonsForm">
                     <div class="row">
                        <div class="col-12">
                           @foreach($complaintReasons as $complaintReason)
                           <a href="<?php echo url('/users/deleteReason'); ?>/{{$complaintReason->id}}" onclick="return confirm('Are you sure you want to delete this?')" class="btn btn-light br10 lightgreen m-1">{{$complaintReason->message}} <i data-feather="x" class="close2"></i></a>
                           @endforeach
                        </div>
                     </div>
                     <form action="{{route('complaintReason.store')}}" method="POST" enctype="multipart/form-data" method="post">
                        @csrf
                        <div class="row">
                           <div class="form-group col-3">
                              <input type="text" class="form-control" placeholder="Add new reason" name="message" required>
                           </div>
                           <!-- <div>
                              <button type="button" class="btn btn-success" style="padding:10px!Important">+ Add</button>
                           </div> -->
                        </div>
                        <div class="text-right">
                           <input type="submit" class="btn btn-success mt-2" value="Submit">
                           <button type="reset" class="btn btn-danger mt-2" onClick="ComplaintReasonsCancel()">Cancel</button>
                        </div>
                     </form>
                  </div>
               </td>
            </tr>
         </table>

         <table class="table table-design-default settingTable tdbdnone" id="Rating_reasons">
            <tr>
               <th>Rating Reasons</th>
               <th class="text-right editIcon"><a href="javascript:" onClick="RatingReasonsForm()"> Edit </a></th>
            </tr>
            <tr id="RatingReasonsView">
               <td colspan="2">
                  <table class="col-12">
                     <tr>
                        <td colspan="2">
                           @foreach($ratingReasons as $ratingReason)
                           <button class="btn btn-light br10 m-1">{{$ratingReason->message}} </button>
                           @endforeach
                        </td>
                     </tr>
                  </table>
               </td>
            </tr>
            <tr>
               <td colspan="2">
                  <div style="display:none" id="RatingReasonsForm">
                     <div class="row">
                        <div class="col-12">
                           @foreach($ratingReasons as $ratingReason)
                           <a href="<?php echo url('/users/deleteReason'); ?>/{{$ratingReason->id}}" onclick="return confirm('Are you sure you want to delete this?')" class="btn btn-light br10 lightgreen m-1">{{$ratingReason->message}} <i data-feather="x" class="close2"></i></a>
                           @endforeach
                        </div>
                     </div>
                     <form action="{{route('ratingReason.store')}}" method="POST" enctype="multipart/form-data" method="post">
                        @csrf
                        <div class="row">
                           <div class="form-group col-3">
                              <input type="text" class="form-control" placeholder="Add new reason" name="message" required>
                           </div>
                           <!-- <div>
                              <button type="button" class="btn btn-success" style="padding:10px!Important">+ Add</button>
                           </div> -->
                        </div>
                        <div class="text-right">
                           <input type="submit" class="btn btn-success mt-2" value="Submit">
                           <button type="reset" class="btn btn-danger mt-2" onClick="ComplaintReasonsCancel()">Cancel</button>
                        </div>
                     </form>
                  </div>
               </td>
            </tr>
         </table>

         <table class="table table-design-default settingTable tdbdnone" id="Cancellation_reasons">
            <tr>
               <th>Cancel Ride Reasons</th>
               <th class="text-right editIcon"><a href="javascript:" onClick="CancelReasonsForm()"> Edit </a></th>
            </tr>
            <tr id="CancelReasonsView">
               <td colspan="2">
                  <table class="col-12">
                     <tr>
                        <td colspan="2">
                           @foreach($cancelRideReasons as $cancelRideReason)
                           <button class="btn btn-light br10 m-1">{{$cancelRideReason->message}} </button>
                           @endforeach
                        </td>
                     </tr>
                  </table>
               </td>
            </tr>
            <tr>
               <td colspan="2">
                  <div style="display:none" id="CancelReasonsForm">
                     <div class="row">
                        <div class="col-12">

                           @foreach($cancelRideReasons as $cancelRideReason)
                           <a href="<?php echo url('/users/deleteCancelRideReason'); ?>/{{$cancelRideReason->id}}" onclick="return confirm('Are you sure you want to delete this?')" class="btn btn-light br10 lightgreen m-1">{{$cancelRideReason->message}} <i data-feather="x" class="close2"></i></a>
                           @endforeach
                        </div>
                     </div>
                     <form action="{{route('cancelRideReason.store')}}" method="POST" enctype="multipart/form-data" method="post">
                        @csrf
                        <div class="row">
                           <div class="form-group col-3">
                              <input type="text" class="form-control" placeholder="Add new reason" name="message" required>
                           </div>
                           <!-- <div>
                              <button type="button" class="btn btn-success" style="padding:10px!Important">+ Add</button>
                           </div> -->
                        </div>
                        <div class="text-right">
                           <input type="submit" class="btn btn-success mt-2" value="Submit">
                           <button type="reset" class="btn btn-danger mt-2" onClick="RideCancel()">Cancel</button>
                        </div>
                     </form>

                  </div>
               </td>
            </tr>
         </table>

         <table class="table table-design-default settingTable tdbdnone" id="Shift_change_reasons">
            <tr>
               <th>Shift Change Reasons</th>
               <th class="text-right editIcon"><a href="javascript:" onClick="ShiftChangeReasonsForm()"> Edit </a></th>
            </tr>
            <tr id="ShiftChangeReasonsView">
               <td colspan="2">
                  <table class="col-12">
                     <tr>
                        <td colspan="2">
                           @foreach($shiftChangeReasons as $shiftChangeReason)
                           <button class="btn btn-light br10 m-1">{{$shiftChangeReason->message}} </button>
                           @endforeach
                        </td>
                     </tr>
                  </table>
               </td>
            </tr>
            <tr>
               <td colspan="2">
                  <div style="display:none" id="ShiftChangeReasonsForm">
                     <div class="row">
                        <div class="col-12">
                           @foreach($shiftChangeReasons as $shiftChangeReason)
                           <a href="<?php echo url('/users/deleteReason'); ?>/{{$shiftChangeReason->id}}" onclick="return confirm('Are you sure you want to delete this?')" class="btn btn-light br10 lightgreen m-1">{{$shiftChangeReason->message}} <i data-feather="x" class="close2"></i></a>
                           @endforeach
                        </div>
                     </div>
                     <form action="{{route('shiftChangeReason.store')}}" method="POST" enctype="multipart/form-data" method="post">
                        @csrf
                        <div class="row">
                           <div class="form-group col-3">
                              <input type="text" class="form-control" placeholder="Add new reason" name="message" required>
                           </div>
                           <!-- <div>
                              <button type="button" class="btn btn-success" style="padding:10px!Important">+ Add</button>
                           </div> -->
                        </div>
                        <div class="text-right">
                           <input type="submit" class="btn btn-success mt-2" value="Submit">
                           <button type="reset" class="btn btn-danger mt-2" onClick="ShiftChangeReasonsCancel()">Cancel</button>
                        </div>
                     </form>
                  </div>
               </td>
            </tr>
         </table>

         <table class="table table-design-default settingTable tdbdnone" id="Temp_change_reasons">
            <tr>
               <th>Temp Location Change Reasons</th>
               <th class="text-right editIcon"><a href="javascript:" onClick="TempChangeReasonsForm()"> Edit </a></th>
            </tr>
            <tr id="ShiftChangeReasonsView">
               <td colspan="2">
                  <table class="col-12">
                     <tr>
                        <td colspan="2">
                           @foreach($tempChangeReasons as $tempChangeReason)
                           <button class="btn btn-light br10 m-1">{{$tempChangeReason->message}} </button>
                           @endforeach
                        </td>
                     </tr>
                  </table>
               </td>
            </tr>
            <tr>
               <td colspan="2">
                  <div style="display:none" id="TempChangeReasonsForm">
                     <div class="row">
                        <div class="col-12">
                           @foreach($tempChangeReasons as $tempChangeReason)
                           <a href="<?php echo url('/users/deleteReason'); ?>/{{$tempChangeReason->id}}" onclick="return confirm('Are you sure you want to delete this?')" class="btn btn-light br10 lightgreen m-1">{{$tempChangeReason->message}} <i data-feather="x" class="close2"></i></a>
                           @endforeach
                        </div>
                     </div>
                     <form action="{{route('tempReason.store')}}" method="POST" enctype="multipart/form-data" method="post">
                        @csrf
                        <div class="row">
                           <div class="form-group col-3">
                              <input type="text" class="form-control" placeholder="Add new reason" name="message" required>
                           </div>
                           <!-- <div>
                              <button type="button" class="btn btn-success" style="padding:10px!Important">+ Add</button>
                           </div> -->
                        </div>
                        <div class="text-right">
                           <input type="submit" class="btn btn-success mt-2" value="Submit">
                           <button type="reset" class="btn btn-danger mt-2" onClick="ShiftChangeReasonsCancel()">Cancel</button>
                        </div>
                     </form>
                  </div>
               </td>
            </tr>
         </table>



         <table class="table table-design-default tdbdnone settingTable" id="Shift_types">
            <tr>
               <th>Shift types</th>
               {{-- <th class="text-right editIcon"><a href="javascript:" onClick="shiftform()"> Edit </a></th> --}}
            </tr>
            <tr id="shiftView">
               <td colspan="2">
                  <table class="col-12">
                     <tr>
                        <td colspan="2">
                           @foreach($shiftTypes as $shiftType)
                           <button class="btn btn-light m-1 br10"><span class="smallbox btn-{{$shiftType->color}}"></span> {{$shiftType->name}}</button>
                           @endforeach
                        </td>
                     </tr>
                  </table>
               </td>
            </tr>
            {{-- <tr>
               <td colspan="2">
                  <div style="display:none" id="shiftform">
                     <div class="row">
                        <div class="col-12">
                           @foreach($shiftTypes as $shiftType)
                           <a href="<?php echo url('/users/deleteShiftType'); ?>/{{$shiftType->id}}"
            onclick="return confirm('Are you sure you want to delete this?')"
            class="btn btn-light m-1 br10"><span class="smallbox btn-{{$shiftType->color}}"></span>{{$shiftType->name}} <i data-feather="x" class="close2"></i></a>
            @endforeach
      </div>
   </div>
   <form action="{{route('shiftType.store')}}" method="POST" enctype="multipart/form-data" method="post">
      @csrf
      <div class="row">
         <div class="form-group col-3">
            <input type="text" class="form-control" placeholder="Add new Shift Type" name="name" required>
         </div>
         <div class="form-group col-2">
            <select class="form-control" name="color">
               <option>Select Color</option>
               <option value="success" class="btn-success">Success</option>
               <option value="danger" class="btn-danger">Danger</option>
               <option value="info" class="btn-info">Info</option>
               <option value="warning" class="btn-warning">Warning</option>
               <option value="primary" class="btn-primary">Primary</option>
            </select>
         </div>
      </div>
      <div class="text-right">
         <input type="submit" class="btn btn-success" value="Submit">
         <button type="reset" class="btn btn-danger" onClick="shiftCancel()">Cancel</button>
      </div>
   </form>
</div>
</td>
</tr> --}}
</table>


<table class="table table-design-default settingTable tdbdnone" id="Client_document">
   <tr>
      <th>Client document categories</th>
      <th class="text-right editIcon"><a href="javascript:" onClick="ClientDocumentForm()"> Edit </a></th>
   </tr>
   <tr id="ClientDocumentView">
      <td colspan="2">
         <table class="col-12">
            <tr>
               <td colspan="2">
                  @foreach($docCategories as $docCategory)
                  <button class="btn btn-light br10 m-1">{{$docCategory->category_name}} </button>
                  @endforeach
               </td>
            </tr>
         </table>
      </td>
   </tr>
   <tr>
      <td colspan="2">
         <div style="display:none" id="ClientDocumentForm">
            <div class="row">
               <div class="col-12">
                  @foreach($docCategories as $docCategory)
                  <a href="<?php echo url('/users/deleteDocCategory'); ?>/{{$docCategory->id}}" onclick="return confirm('Are you sure you want to delete this?')" class="btn btn-light br10 lightgreen m-1">{{$docCategory->category_name}} <i data-feather="x" class="close2"></i></a>
                  @endforeach
               </div>
            </div>
            <form action="{{route('docCategory.store')}}" method="POST" enctype="multipart/form-data" method="post">
               @csrf
               <div class="row">
                  <div class="form-group col-3">
                     <input type="text" class="form-control" placeholder="Add new heading" name="category_name" required>
                  </div>
                  <!-- <div>
                              <button type="button" class="btn btn-success" style="padding:10px!Important">+ Add</button>
                           </div> -->
               </div>
               <div class="text-right">
                  <input type="submit" class="btn btn-success mt-2" value="Submit">
                  <button type="reset" class="btn btn-danger mt-2" onClick="ClientDocumentCancel()">Cancel</button>
               </div>
            </form>
         </div>
      </td>
   </tr>
</table>
<table class="table table-design-default settingTable tdbdnone" id="Scheduler">
   <tr>
      <th colspan="2">Scheduler</th>
      <th class="text-right editIcon"><a href="javascript:" onClick="SchedulerForm()"> Edit </a></th>
   </tr>
   <tr>
      <td><span>Client Types </span></td>
      <td></td>
      <!-- <th class="text-right editIcon"><a href="javascript:" onClick="ClientDocumentForm()"> Edit </a></th> -->
      <td class="text-right editIcon"><a href="javascript:" onClick="clientType()"> Manage </a></td>
   </tr>
   <tr id="clientTypeView">
      <td colspan="3">
         @foreach($cleintTypes as $cleintType)
         <button class="btn btn-light m-1 br10">{{$cleintType->name}}</button>
         @endforeach
      </td>
   </tr>
   <tr>
      <td colspan="3">
         <div style="display:none" id="clientTypeForm">
            <div class="row">
               <div class="col-12">
                  @foreach($cleintTypes as $cleintType)
                  <a href="<?php echo url('/users/deleteClientType'); ?>/{{$cleintType->id}}" onclick="return confirm('Are you sure you want to delete this?')" class="btn btn-light m-1 br10">{{$cleintType->name}} <i data-feather="x" class="close2"></i></a>
                  @endforeach
               </div>
            </div>
            <form action="{{route('clientType.store')}}" method="POST" enctype="multipart/form-data" method="post">
               @csrf
               <div class="row">
                  <div class="form-group col-3">
                     <input type="text" class="form-control" placeholder="Add new type" name="name" required>
                  </div>
                  <!-- <div>
                              <button type="button" class="btn btn-success" style="padding:10px!Important">+ Add</button>
                           </div> -->
               </div>
               <div class="text-right">
                  <input type="submit" class="btn btn-success mt-2" value="Submit">
                  <button type="reset" class="btn btn-danger mt-2" onClick="clientTypeCancel()">Cancel</button>
               </div>
            </form>
         </div>
      </td>
   </tr>
   <form action="{{route('clientUpdateSettings')}}" method="post">
      @csrf
      <tr>
         <td><span>Timezone </span> </td>
         <td>
            <div id="Timezone">
               <b>{{$settings ? $settings->timezone ? $settings->timezone : 365 : 365}}</b>
            </div>
            <div id="Timezone-form" style="display: none;">
               <input type="text" class="form-control col-3" name="timezone" placeholder="Timezone" value="{{$settings ? $settings->timezone : 365}}" required>
            </div>
         </td>
         <td></td>
      </tr>
      <tr>
         <td><span>Minute Interval </span> </td>
         <td>
            <div id="Interval">
               <b>{{$settings ? $settings->minute_interval ? $settings->minute_interval : 1 : 1}}</b>
            </div>
            <div id="Interval-form" style="display: none;">
               <input type="number" class="form-control col-3" name="minute_interval" placeholder="Minute Interval" value="{{$settings ? $settings->minute_interval : 1}}" required>
            </div>
         </td>
         <td></td>
      </tr>
      <tr>
         <td><span>Pay run </span> </td>
         <td>
            <div id="Pay">
               @php
               $date = date_create($settings ? $settings->first_day_fortnight : '');
               $day = date_format($date,"l");
               @endphp
               <b>{{$settings ? $settings->pay_run . ' starting ' . $day : 'Fortnightly starting
                           Wednesday'}}</b>
            </div>
            <div id="Pay-form" style="display: none;">

               <input type="radio" name="pay_run" value="Fornightly" {{$settings ? ($settings->pay_run ==
                        'Fornightly') ? 'checked="checked"' : '' : 'checked="checked"'}}>Fortnightly

               <input type="radio" name="pay_run" value="Weekly" {{$settings ? ($settings->pay_run == 'Weekly')
                        ? 'checked="checked"' : '' : ''}}> Weekly

            </div>
         </td>
         <td></td>
      </tr>
      <tr id="Communication">
         <td><span>Communication mode </span> </td>
         <td>
            <div>
               <b>email</b>
            </div>
         </td>
         </td>
         <td></td>
      </tr>

      <tr id="first_day_of_fornight" style="display: none;">
         <td id="first_day_of_fornight_heading"><span>First Day of Fornight </span> </td>
         <td>
            <div>
               <input type="date" name="first_day_fornight" class="form-control col-3" value="{{$settings ? $settings->first_day_fortnight : ''}}" required>
            </div>
         </td>
         </td>
         <td></td>
      </tr>

      <tr>
         <td><span>Carer can manage shifts </span> </td>
         <td>
            <div class="custom-control custom-switch">
               <input type="checkbox" class="custom-control-input" name="can_manage_shifts" id="customSwitche" {{$settings ? ($settings->manage_shift == 1) ? 'checked="checked"' : '' : ''}}>
               <label class="custom-control-label" for="customSwitche"></label>
            </div>
         </td>
         <td></td>
      </tr>
      <tr id="clientTypeFormCancel" style="display: none;">
         <td colspan="3" style="padding-top: 0;">
            <div class="col-3 float-right text-right">
               <input type="submit" class="btn btn-success">
               <button type="reset" class="btn btn-danger" onClick="clientTypeFormCancel()">Cancel</button>
            </div>
         </td>
      </tr>
   </form>
</table>
<table class="table table-design-default settingTable tdbdnone" id="Report_heading">
   <tr>
      <th>Report heading</th>
      <th class="text-right editIcon"></th>
   </tr>
   @foreach($reportHeadingCategory as $headings)
   <tr>
      <td>
         <span>{{$headings->category_name}}</span><br />
         <div id="ReportHeadingView{{$headings->id}}">
            @foreach($headings->catHeadings as $subHeading)
            <button class="btn lightorange m-1 br10">{{$subHeading->name}}</button>
            <!-- <button class="btn lightgreen m-1 br10">Manual Heading </button>
                     <button class="btn btn-light m-1 br10">NDIS Orientation </button>
                     <button class="btn lightgreen m-1 br10">CPR Training </button>
                     <button class="btn lightorange m-1 br10">COVID 19 - ICT </button> -->
            @endforeach
         </div>
         <div style="display:none" id="ReportHeadingForm{{$headings->id}}">
            <div class="row">
               <div class="col-12">
                  @foreach($headings->catHeadings as $subHeading)
                  <a href="<?php echo url('/users/deleteReportHeading'); ?>/{{$subHeading->id}}" onclick="return confirm('Are you sure you want to delete this?')" class="btn lightorange m-1 br10">{{$subHeading->name}} <i data-feather="x" class="close2"></i></a>
                  @endforeach
               </div>
            </div>
            <form action="{{route('reportHeading.store')}}" method="POST" enctype="multipart/form-data" method="post">
               @csrf
               <div class="row">
                  <div class="form-group col-3">
                     <input type="text" class="form-control" placeholder="Add new Report Heading" name="name" required>
                     <input type="hidden" value="{{$headings->id}}" name="heading_id">
                  </div>
                  <!-- <div>
                              <button type="button" class="btn btn-success" style="padding:10px!Important">+ Add</button>
                           </div> -->
               </div>
               <div class="text-right">
                  <input type="submit" class="btn btn-success mt-2" value="Submit">
                  <button type="reset" class="btn btn-danger mt-2 ReportHeadingCancel" data-id="{{$headings->id}}">Cancel</button>
               </div>
            </form>
         </div>

      </td>
      <td class="text-right editIcon"><a href="javascript:" class="ReportHeadingForm" data-id="{{$headings->id}}"> Edit </a></td>
   </tr>
   @endforeach
   <!-- <tr>
               <td>
                  <span>KPI</span><br />
                  <div id="KPIView">
                     <button class="btn lightgreen m-1 br10">Vehicle Insurance</button>
                     <button class="btn btn-light m-1 br10">100 Pt ID check-passport-Australian </button>
                     <button class="btn lightorange m-1 br10">100 Pt ID check- Driver's Licence </button>
                     <button class="btn lightgreen m-1 br10">100 Pt ID check-Student Id</button>
                  </div>
                  <form method="post" style="display:none" id="KPIForm">
                     <div class="row">
                        <div class="col-12">
                           <button class="btn lightgreen m-1 br10" type="button">Vehicle Insurance <i data-feather="x" class="close2"></i></button>
                           <button class="btn btn-light m-1 br10" type="button">100 Pt ID check-passport-Australian <i data-feather="x" class="close2"></i></button>
                           <button class="btn lightorange m-1 br10" type="button">100 Pt ID check- Driver's Licence <i data-feather="x" class="close2"></i></button>
                           <button class="btn lightgreen m-1 br10" type="button">100 Pt ID check-Student Id <i data-feather="x" class="close2"></i></button>
                        </div>
                     </div>
                     <div class="row">
                        <div class="form-group col-3">
                           <input type="text" class="form-control" placeholder="Add new heading">
                        </div>
                        <div>
                           <button type="button" class="btn btn-success" style="padding:10px!Important">+ Add</button>
                        </div>
                     </div>
                     <div class="text-right">
                        <input type="button" class="btn btn-success mt-2" value="Submit">
                        <button type="reset" class="btn btn-danger mt-2" onClick="KPICancel()">Cancel</button>
                     </div>
                  </form>
               </td>
               <td class="text-right editIcon"><a href="javascript:" onClick="KPIForm()"> Edit </a></td>
            </tr>
            <tr>
               <td>
                  <span>Other</span><br />
                  <div id="OtherView">
                     <button class="btn lightorange m-1 br10">Seizure management training</button>
                     <button class="btn lightgreen m-1 br10">Mental Health First Aid </button>
                     <button class="btn btn-light m-1 br10">Medication Support Training </button>
                     <button class="btn lightgreen m-1 br10">Induction Training </button>
                     <button class="btn lightorange m-1 br10">Training- other </button>
                  </div>
                  <form method="post" style="display:none" id="OtherForm">
                     <div class="row">
                        <div class="col-12">
                           <button class="btn lightorange m-1 br10" type="button">Seizure management training <i data-feather="x" class="close2"></i></button>
                           <button class="btn lightgreen m-1 br10" type="button">Mental Health First Aid <i data-feather="x" class="close2"></i></button>
                           <button class="btn btn-light m-1 br10" type="button">Medication Support Training <i data-feather="x" class="close2"></i></button>
                           <button class="btn lightgreen m-1 br10" type="button">Induction Training <i data-feather="x" class="close2"></i></button>
                           <button class="btn lightorange m-1 br10" type="button">Training- other <i data-feather="x" class="close2"></i></button>
                        </div>
                     </div>
                     <div class="row">
                        <div class="form-group col-3">
                           <input type="text" class="form-control" placeholder="Add new heading">
                        </div>
                        <div>
                           <button type="button" class="btn btn-success" style="padding:10px!Important">+ Add</button>
                        </div>
                     </div>
                     <div class="text-right">
                        <input type="button" class="btn btn-success mt-2" value="Submit">
                        <button type="reset" class="btn btn-danger mt-2" onClick="OtherCancel()">Cancel</button>
                     </div>
                  </form>
               </td>
               <td class="text-right editIcon"><a href="javascript:" onClick="OtherForm()"> Edit </a></td>
            </tr> -->
</table>
<table class="table table-design-default settingTable tdbdnone" id="attendance">
   <tr>
      <th colspan="2">Time and attendance</th>
      <th class="text-right editIcon"><a href="javascript:" onClick="attendanceForm()"> Edit </a></th>
   </tr>
   <form action="{{route('update.time.attendence')}}" method="post" id="bs5">
      @csrf
      <tr>
         <td><span>Enable unavailability </span> </td>
         <td>
            <div class="custom-control custom-switch">
               <input type="checkbox" name="enable_unavailability" class="custom-control-input" id="customSwitche11" <?php if (@$tA->enable_unavailability == 'on') {
                                                                                                                        echo 'checked';
                                                                                                                     } ?>>
               <label class="custom-control-label" for="customSwitche11">Unavailability notice period</label>
            </div>
         </td>
         <td class="text-right" id="notice_period"><b>{{@$tA->notice_preiod}}</b></td>
         <td class="text-right" id="notice_period_form" style="display: none;"><input type="text" class="form-control" name="notice_period" placeholder="Notice Period" value="{{@$tA->notice_preiod}}"></td>
      </tr>
      <tr>
         <td><span>Clockin location Check </span> </td>
         <td>
            <div class="custom-control custom-switch">
               <input type="checkbox" name="location_check" class="custom-control-input" id="customSwitche12" <?php if (@$tA->location_check == 'on') {
                                                                                                                  echo 'checked';
                                                                                                               } ?>>
               <label class="custom-control-label" for="customSwitche12">Attendance threshold in minutes
               </label>
            </div>
         </td>
         <td class="text-right" id="attendance_threshold"><b>{{@$tA->attendance_threshold}}</b></td>
         <td class="text-right" id="attendance_threshold_form" style="display: none;"><input type="text" class="form-control" name="attendance_threshold" placeholder="Attendance Threshold" value="{{@$tA->attendance_threshold}}"></td>
      </tr>
      <tr>
         <td><span>Auto approve shift if clockin/out were successful </span> </td>
         <td>
            <div class="custom-control custom-switch">
               <input type="checkbox" name="auto_approve_shift" class="custom-control-input" id="customSwitche13" <?php if (@$tA->auto_approve_shift == 'on') {
                                                                                                                     echo 'checked';
                                                                                                                  } ?>>
               <label class="custom-control-label" for="customSwitche13">Timesheet precision</label>
            </div>
         </td>
         <td class="text-right" id="time_precision"><b>{{@$tA->timesheet_precision}}</b></td>
         <td class="text-right" id="time_precision_form" style="display: none;"><input type="text" class="form-control" name="time_precision" placeholder="Time Precision" value="{{@$tA->timesheet_precision}}"></td>
      </tr>
      <tr>
         <td><span>Clockin alert</span> </td>
         <td>
            <div class="custom-control custom-switch">
               <input type="checkbox" name="clockin_alert" class="custom-control-input" id="customSwitche14" <?php if (@$tA->clockin_alert == 'on') {
                                                                                                                  echo 'checked';
                                                                                                               } ?>>
               <label class="custom-control-label" for="customSwitche14">Pay rate is based on </label>
            </div>
         </td>
         <td class="text-right" id="pay_rate"><b>{{@$tA->pay_rate}}</b></td>
         <td class="text-right" id="pay_rate_form" style="display: none;"><select name="pay_rate" id="pay_rate">
               <option value="End Time" {{$tA->pay_rate == 'End Time' ? 'selected' : ''}}>End Time</option>
               <option value="Start Time" {{$tA->pay_rate == 'Start Time' ? 'selected' : ''}}>Start Time
               </option>
            </select></td>
      </tr>
      <tr>
         <td><span>Clockin alert message </span> </td>
         <td class="text-right" id="clock_alert_message">
            <b>{{@$tA->clockin_alert_message}}</b>
         </td>
         <td class="text-right" id="clock_alert_message_form" style="display: none;">
            <input type="text" class="" name="clock_alert_message" value="{{$tA->clockin_alert_message}}">
         </td>
         <!-- <td class="text-right"><b>{{@$tA->pay_rate}}</b></td> -->
      </tr>
      <!-- <tr>
               <td><span>Payroll Software </span> </td>
               <td> </td>
               <td class="text-right">
                  <button class="btn btn-light m-1 br10">Xero</button><button class="btn btn-light m-1 br10"><span class="smallbox btn-primary"></span>Disconnect from Xero</button>
               </td>
            </tr> -->
      <tr>
         <td><span>Xero Organization </span> </td>
         <td> </td>
         <td class="text-right"><b>SA Metro Care</b></td>
      </tr>
      <tr>
         <td colspan="2"></td>
         <td>
            <div id="attendanceCancel" class="text-right" style="display: none;">
               <button type="submit" class="btn btn-success" value="1" name="exit">Save</button>
               <button type="reset" class="btn btn-danger" onClick="AdditionalformCancel()">Cancel</button>
            </div>
         </td>
      </tr>
   </form>
</table>
<table class="table table-design-default tdbdnone settingTable" id="Public_holidays">
   <tr>
      <th>Public holidays</th>
      <th class="text-right editIcon"><a href="javascript:" onClick="publicholidaysform()"> Edit </a></th>
   </tr>
   <tr id="publicholidaysview">
      <td colspan="2">
         <table class="col-12">
            <tr>
               <td colspan="2">
                  @foreach($holiday as $hol)
                  <button class="btn lightgreen m-1 br10">{{date('D - d F Y', strtotime($hol->date)) }}
                     <div style="font-size:10px;">{{$hol->name}}</div>
                  </button>
                  <!-- <button class="btn lightorange m-1 br10">SUN - 01 Jan 2023 </button>
                           <button class="btn btn-light m-1 br10">SUN - 01 Jan 2023 </button>
                           <button class="btn lightorange m-1 br10">SUN - 01 Jan 2023 </button>
                           <button class="btn lightgreen m-1 br10">SUN - 01 Jan 2023 </button> -->
                  @endforeach
               </td>
            </tr>
         </table>
      </td>
   </tr>
   <tr>
      <td colspan="2">
         <div style="display:none" id="publicholidaysform">
            <div class="row">
               <div class="col-12">
                  @foreach($holiday as $hol)
                  <a href="<?php echo url('/users/deleteHoliday'); ?>/{{$hol->id}}" onclick="return confirm('Are you sure you want to delete this?')" class="btn lightgreen m-1 br10">{{date('D - d F Y', strtotime($hol->date)) }} {{$hol->name}}<i data-feather="x" class="close2"></i></a>
                  @endforeach
               </div>
            </div>
            <form action="{{route('holiday.store')}}" method="POST" enctype="multipart/form-data" method="post">
               @csrf
               <div class="row">
                  <div class="form-group col-3">
                     <input type="date" class="form-control" placeholder="Add new holiday" name="date" required>
                  </div>
                  <div class="form-group col-3">
                     <input type="text" class="form-control" placeholder="Name" name="name" required>
                  </div>
                  <div class="form-group col-3">
                     <input type="text" class="form-control" placeholder="Description" name="description" required>
                  </div>
                  <!-- <div>
                              <button type="button" class="btn btn-success" style="padding:10px!Important">+ Add</button>
                           </div> -->
               </div>
               <div class="text-right">
                  <input type="submit" class="btn btn-success mt-2" value="Submit">
                  <button type="reset" class="btn btn-danger mt-2" onClick="publicholidaysCancel()">Cancel</button>
               </div>
            </form>
         </div>
      </td>
   </tr>
</table>

<table class="table table-design-default settingTable tdbdnone" id="ridesetting">
   <tr>
      <th colspan="2">Ride Settings</th>
      <th></th>
      <th class="text-right editIcon"><a href="javascript:" onClick="ridesettingForm()"> Edit </a></th>
   </tr>
   <form action="{{route('update.ride.setting')}}" method="post">
      @csrf
      <!-- Female Employee Safety Radio -->
      <tr>
         <td><span>Female Employee Safety</span> </td>
         <td class="text-right" id="female_employee_security">
            <!-- Bootstrap radio buttons -->
            <div class="form-check form-check-inline">
               <input class="form-check-input" type="radio" name="female_employee_security" id="female_security_yes" value=1 {{@$rideSettings->female_safety==1 ?'checked' : ''}}>
               <label class="form-check-label" for="female_security_yes">Yes</label>
            </div>
            <div class="form-check form-check-inline">
               <input class="form-check-input" type="radio" name="female_employee_security" id="female_security_no" value=0 {{@$rideSettings->female_safety==0 ?'checked':''}}>
               <label class="form-check-label" for="female_security_no">No</label>
            </div>
         </td>
      </tr>
      <!-- Noshow -->
      <tr>
         <td><span>Noshow</span> </td>
         <td class="text-right" id="noshowSelect_frequency" style="display: none;">
            <!-- Bootstrap select dropdown -->
            <select name="noshow_frequency" class="form-control">
               <option value="monthly" {{@$rideSettings->noshow_frequency=='monthly'?'selected':''}}>Monthly</option>
               <option value="weekly" {{@$rideSettings->noshow_frequency=='weekly'?'selected':''}}>Weekly</option>
               <option value="yearly" {{@$rideSettings->noshow_frequency=='yearly'?'selected':''}}>Yearly</option>
            </select>
         </td>
         <td class="text-right" id="noshowFrequency"><b>{{@$rideSettings->noshow_frequency}}</b></td>
         <td class="text-right" style="display: none;" id="noshowCounter_form">
            <!-- Counter input -->
            <div class="input-group">
               <button type="button" class="btn btn-outline-secondary" onclick="decrementCounter('noshow_count')">-</button>
               <input type="number" name="noshow_count" id="noshow_count" value='{{@$rideSettings->noshow_count}}' min="1" max="10" value="1" style="width:40px; text-align:center">
               <button type="button" class="btn btn-outline-secondary" onclick="incrementCounter('noshow_count')">+</button>
            </div>

         </td>
         <td class="text-right" id="noshowCounter"><b>{{@$rideSettings->noshow_count}} times</b></td>
      </tr>
      <!-- Leave Timer -->
      <tr>
         <td><span>Leave Timer</span> </td>
         <td class="text-right" style="display: none;" id="leaveCounter_form">
            <!-- Bootstrap timepicker -->
            <div class="input-group">
               <input type="time" name="leave_timer" id="leave_timer" class="form-control" placeholder="MM:SS" value="{{@$rideSettings->leave_timer}}">

            </div>
         </td>

         <td class="text-right" id="leaveCounter"><b>{{@$rideSettings->leave_timer}} </b></td>
         </td>
      </tr>
      <!-- No-show Timer -->
      <tr>

         <td><span>No-show Timer</span> </td>
         <td class="text-right">
            <!-- Bootstrap radio buttons for Yes/No -->
            <div class="form-check form-check-inline">
               <input class="form-check-input" type="radio" name="show_noshow_timer" id="show_noshow_timer_yes" value=1 onclick="toggleNoshowTimerField(this)" {{@$rideSettings->noshow==1 ?'checked':''}}>
               <label class="form-check-label" for="show_noshow_timer_yes">Yes</label>
            </div>
            <div class="form-check form-check-inline">
               <input class="form-check-input" type="radio" name="show_noshow_timer" id="show_noshow_timer_no" value=0 onclick="toggleNoshowTimerField(this)" {{@$rideSettings->noshow==0 ?'checked':''}}>
               <label class="form-check-label" for="show_noshow_timer_no">No</label>
            </div>
            <!-- Bootstrap timepicker for No-show Timer (initially hidden) -->
            <div id="noshow_timer_group" style="display: none;">
               <input type="time" name="noshow_timer" id="noshow_timer" class="form-control" value="{{@$rideSettings->noshow_timer}}">

            </div>
         </td>
         @if(@$rideSettings->noshow==1)
         <td class="text-right" id="ridenoshow"><b>{{@$rideSettings->noshow_timer}}</b></td>
         @endif

      </tr>
      <tr>
         <td colspan="3"></td>
         <td>
            <div id="ridesettingButtons" class="text-right" style="display: none;">
               <button type="submit" class="btn btn-success" value="1" name="exit">Save</button>
               <button type="reset" class="btn btn-danger" onClick="ridesettingCancel()">Cancel</button>
            </div>
         </td>
      </tr>
   </form>
</table>




<table class="table table-design-default tdbdnone settingTable" id="faq">
   <tr>
      <th>FAQ</th>
      <th class="text-right editIcon"><a href="javascript:" onClick="faqform()"> Edit </a></th>
   </tr>
   <tr id="faqview">
      <td colspan="2">
         <table class="col-12">
            <tr>
               <td colspan="2">

                  @foreach($faqs as $faq)
                  <button class="btn lightorange br10" style="text-align: left;">
                     <details>
                        <summary>{{$faq->question}}</summary>
                        <p>{{$faq->answer}}</p>
                     </details>
                  </button><br>
                  <!-- <button class="btn lightgreen br10" type="button">Risk</button> -->
                  @endforeach
               </td>
            </tr>
         </table>
      </td>
   </tr>
   <tr>
      <td colspan="2">
         <div style="display:none" id="faqform">
            <div class="row">
               <div class="col-12">

                  @foreach($faqs as $faq)
                  <a href="<?php echo url('/users/deleteFaq'); ?>/{{$faq->id}}" class="btn lightorange br10" onclick="return confirm('Are you sure you want to delete this?')">{{$faq->question}} <i data-feather="x" class="close2"></i></a>
                  <!-- <button class="btn lightgreen br10" type="button">Risk <i data-feather="x" class="close2"></i></button> -->
                  @endforeach
               </div>
            </div>
            <form action="{{route('faq.store')}}" method="POST" enctype="multipart/form-data" method="post">
               @csrf
               <div class="row">
                  <div class="form-group col-3">
                     <input type="text" class="form-control" placeholder="Question" name="question" required><br>
                     <input type="text" class="form-control" placeholder="Answer" name="answer" required>
                  </div>
                  <div>
                     <!-- <button type="submit" class="btn btn-success" style="padding:10px!Important">+ Add</button> -->
                  </div>
               </div>
               <div class="text-right">
                  <input type="submit" class="btn btn-success mt-2" value="Submit">
                  <button type="reset" class="btn btn-danger mt-2" onClick="faqCancel()">Cancel</button>
               </div>
            </form>
         </div>
      </td>
   </tr>
</table>



<table class="table table-design-default tdbdnone settingTable" id="schedule_template">
   <tr>
      <th>Schedule Template</th>
      <th class="text-right editIcon"><a href="javascript:" onClick="templateform()"> Edit </a></th>
   </tr>
   <tr id="faqview">
      <td colspan="2">
         <table class="col-12">
            <tr>
               <td colspan="2">

                  @foreach($scheduleTemplate as $template)
                  <div class="row">
                     <details>
                        <summary>{{$template->title}}</summary>
                        <table class="table table-design-default normal_Font">
                           <thead>
                              <tr>

                                 <th>Pick Time</th>
                                 <th>Drop time</th>
                                 <th>End next day</th>
                                 <th>PriceBook</th>
                                 <th>Repeat</th>

                              </tr>
                           </thead>
                           <tbody>
                              <tr>
                                 <td>{{@@$template->pick_time}}</td>
                                 <td>{{@@$template->drop_time}}</td>
                                 <td><input type="checkbox" {{($template->shift_finishes_next_day == 1) ? 'checked' : ''}}></td>
                                 <td>{{@@$template->pricebook->name}}</td>
                                 <td><input type="checkbox" {{($template->is_repeat == 1) ? 'checked' : ''}}></td>

                           </tbody>
                        </table>
                     </details>
                  </div>

                  <!-- <button class="btn lightgreen br10" type="button">Risk</button> -->
                  @endforeach
               </td>
            </tr>
         </table>
      </td>
   </tr>
   <tr>
      <td colspan="2">
         <div style="display:none" id="templateform">

            <form action="{{route('template.store')}}" method="POST" enctype="multipart/form-data" method="post">
               @csrf
               <div class="row">

                  <div id="pickupTime" class="col-sm-6">
                     <label for="exampleInputName1">PickUp time </label>
                     <input type="time" name="pick_time" class="form-control" placeholder="Start time">

                  </div>
                  <div id="dropTime" class="col-sm-6">
                     <label for="exampleInputName1">Drop time </label>
                     <input type="time" name="drop_time" class="form-control" placeholder="Drop time">
                  </div>


                  <!-- <button type="submit" class="btn btn-success" style="padding:10px!Important">+ Add</button> -->

               </div>

               <div class="row mt-4">
                  <div class="col-sm-6 ">
                     <label for="exampleInputName1">Price Book  </label>
                     <select class="form-control js-example-basic-single" name="pricebook" id="pricebook">
                        @foreach($pricebooks as $pricebook)
                        <option value="{{@@$pricebook->id}}">{{@@$pricebook->name}}</option>
                        @endforeach
                     </select>
                  </div>
                  <div class="col-sm-6 ">
                     <label for="exampleInputName1">Template title </label>
                     <input type="text" name="title" class="form-control" placeholder="title">
                  </div>
               </div>
               <div class="row mt-4">
                  <div id="shift_finishes_next_day" class="col-sm-6">
                     <label for="exampleInputName1">Shift Finishes Next Day  </label>
                     <input type="checkbox" name="shift_finishes_next_day" id="next_day">
                  </div>
                  <div id="repeat" class="col-sm-6">
                     <label for="exampleInputName1">Repeat </label>
                     <input type="checkbox" name="is_repeat" id="is_repeat">
                  </div>
               </div>

               <div class="row">
                  <div class="col-sm-6 mt-3 role repeated">
                     <label for="exampleInputName1">Reacurrance  </label>
                     <select class="form-control js-example-basic-single" name="reacurrance" id="reacurrance">
                        <option value="daily">Daily</option>
                        <option value="weekly">Weekly</option>
                        <option value="monthly">Monthly</option>
                     </select>
                  </div>

                  <div class="col-sm-6 mt-3 role Daily repeated">
                     <label for="exampleInputName1">Repeat every (Days) </label>
                     <select class="form-control js-example-basic-single" name="repeat_days">
                        <option value="1">1</option>
                        <option value="2">2</option>
                        <option value="3">3</option>
                        <option value="4">4</option>
                        <option value="5">5</option>
                        <option value="6">6</option>
                        <option value="7">7</option>
                        <option value="8">8</option>
                        <option value="9">9</option>
                        <option value="10">10</option>
                        <option value="11">11</option>
                        <option value="12">12</option>
                        <option value="13">13</option>
                        <option value="14">14</option>
                        <option value="15">15</option>
                     </select>
                  </div>
               </div>
               <div class="row">
                  <div class="col-sm-6 mt-3 role Weekly repeated">
                     <label for="exampleInputName1">Repeat every (Week) </label>
                     <select class="form-control js-example-basic-single" name="repeat_weeks">
                        <option value="1">1</option>
                        <option value="2">2</option>
                        <option value="3">3</option>
                        <option value="4">4</option>
                        <option value="5">5</option>
                        <option value="6">6</option>
                        <option value="7">7</option>
                        <option value="8">8</option>
                        <option value="9">9</option>
                        <option value="10">10</option>
                        <option value="11">11</option>
                        <option value="12">12</option>
                     </select>
                  </div>

                  <div class="col-sm-6 mt-3 role Monthly repeated">
                     <label for="exampleInputName1">Repeat every (Month) </label>
                     <select class="form-control js-example-basic-single" name="repeat_months">
                        <option value="1">1</option>
                        <option value="2">2</option>
                        <option value="3">3</option>
                     </select>
                  </div>
               </div>

               <div class="col-sm-12 mt-3 role Monthly repeated">
                  <label for="exampleInputName1">Repeat every (Day of the Month) </label>
                  <select class="form-control js-example-basic-single" name="repeat_day_of_month">
                     <option value="1">1</option>
                     <option value="2">2</option>
                     <option value="3">3</option>
                     <option value="4">4</option>
                     <option value="5">5</option>
                     <option value="6">6</option>
                     <option value="7">7</option>
                     <option value="8">8</option>
                     <option value="9">9</option>
                     <option value="10">10</option>
                     <option value="11">11</option>
                     <option value="12">12</option>
                     <option value="13">13</option>
                     <option value="14">14</option>
                     <option value="15">15</option>
                     <option value="16">16</option>
                     <option value="17">17</option>
                     <option value="18">18</option>
                     <option value="19">19</option>
                     <option value="20">20</option>
                     <option value="21">21</option>
                     <option value="22">22</option>
                     <option value="23">23</option>
                     <option value="24">24</option>
                     <option value="25">25</option>
                     <option value="26">26</option>
                     <option value="27">27</option>
                     <option value="28">28</option>
                     <option value="29">29</option>
                     <option value="30">30</option>
                     <option value="31">31</option>
                  </select>
               </div>

               <div class="col-sm-12 mt-3 role Weekly repeated">
                  <label for="exampleInputName1">Mon <code>*</code></label>
                  <input type="checkbox" name="mon" id="mon">

                  <label for="exampleInputName1" class="ml-2">Tue <code>*</code></label>
                  <input type="checkbox" name="tue" id="tue">

                  <label for="exampleInputName1" class="ml-2">Wed <code>*</code></label>
                  <input type="checkbox" name="wed" id="wed">

                  <label for="exampleInputName1" class="ml-2">Thu <code>*</code></label>
                  <input type="checkbox" name="thu" id="thu">

                  <label for="exampleInputName1" class="ml-2">Fri <code>*</code></label>
                  <input type="checkbox" name="fri" id="fri">

                  <label for="exampleInputName1" class="ml-2">Sat <code>*</code></label>
                  <input type="checkbox" name="sat" id="sat">

                  <label for="exampleInputName1" class="ml-2">Sun <code>*</code></label>
                  <input type="checkbox" name="sun" id="sun">
               </div>
               <div class="text-right">
                  <input type="submit" class="btn btn-success mt-2" value="Submit">
                  <button type="reset" class="btn btn-danger mt-2" onClick="templateCancel()">Cancel</button>
               </div>
            </form>
         </div>
      </td>
   </tr>
</table>

</div>

<div style="display: none;">
   <div class="col-md-3">
      <div class="card">
         <div class="card-body">
            <h4 class="header-title">Notes Permissions </h4>
            <form action="{{route('update.notePermission')}}" method="post" id="bs6">
               @csrf
               <div class="form-group">
                  <label>Allow note edit</label>
                  <select name="note_edit" class="form-control np1">
                     <option value="">Select</option>
                     <option value="yes" <?php if (@$nP->note_edit == 'yes') {
                                             echo 'selected';
                                          } ?>>Yes</option>
                     <option value="no" <?php if (@$nP->note_edit == 'no') {
                                             echo 'selected';
                                          } ?>>No</option>
                  </select>
               </div>
               <div class="form-group">
                  <label>Hide client notes and documents to staff unscheduled for</label>
                  <input type="text" class="form-control expire_access1" name="expire_access" value="{{@$nP->expire_access}}">
               </div>

               <input type="submit" class="btn btn-success">
            </form>
         </div>
         <!-- end card-body-->
      </div>
      <!-- end card-->
   </div>
   <!-- end col -->
   <div class="col-md-4">
      <div class="card">
         <div class="card-body">
            <h4 class="header-title">Time and Attendence </h4>
            <form action="{{route('update.time.attendence')}}" method="post" id="bs5">
               @csrf
               <div class="form-group">
                  <label>Enable unavailability</label>
                  <select name="enable_unavailability" class="form-control eu1">
                     <option value="">Select</option>
                     <option value="yes" <?php if (@$tA->enable_unavailability == 'yes') {
                                             echo 'selected';
                                          } ?>>Yes</option>
                     <option value="no" <?php if (@$tA->enable_unavailability == 'no') {
                                             echo 'selected';
                                          } ?>>No</option>
                  </select>
               </div>
               <p>Unavailability notice period <span style="float:right">{{@$tA->notice_preiod}}</span></p>
               <div class="form-group">
                  <label>Clockin location check</label>
                  <select name="location_check" class="form-control eu1">
                     <option value="">Select</option>
                     <option value="yes" <?php if (@$tA->location_check == 'yes') {
                                             echo 'selected';
                                          } ?>>Yes</option>
                     <option value="no" <?php if (@$tA->location_check == 'no') {
                                             echo 'selected';
                                          } ?>>No</option>
                  </select>
               </div>
               <p>Attendance threshold in minutes <span style="float:right">{{@$tA->attendance_threshold}}</span>
               </p>
               <div class="form-group">
                  <label>Auto approve shift if clockin/out were successful</label>
                  <select name="auto_approve_shift" class="form-control eu1">
                     <option value="">Select</option>
                     <option value="yes" <?php if (@$tA->auto_approve_shift == 'yes') {
                                             echo 'selected';
                                          } ?>>Yes</option>
                     <option value="no" <?php if (@$tA->auto_approve_shift == 'no') {
                                             echo 'selected';
                                          } ?>>No</option>
                  </select>
               </div>
               <p>Timesheet precision <span style="float:right">{{@$tA->timesheet_precision}}</span></p>
               <p>Pay rate is based on <span style="float:right">{{@$tA->pay_rate}}</span></p>
               <div class="form-group">
                  <label>Clockin alert</label>
                  <select name="clockin_alert" class="form-control eu1">
                     <option value="">Select</option>
                     <option value="yes" <?php if (@$tA->clockin_alert == 'yes') {
                                             echo 'selected';
                                          } ?>>Yes</option>
                     <option value="no" <?php if (@$tA->clockin_alert == 'no') {
                                             echo 'selected';
                                          } ?>>No</option>
                  </select>
               </div>
               <p>Clockin alert message <span style="float:right">{{@$tA->pay_rate}}</span></p>
               <input type="submit" class="btn btn-success">
            </form>
         </div>
         <!-- end card-body-->
      </div>
      <!-- end card-->
   </div>
   <!-- end col -->
   <div class="col-md-4" id="schedule">
      <div class="card">
         <div class="card-body">
            <h4 class="header-title">Schedular </h4>
            <h4 class="header-title">Client Types <a href="javascript:;" data-toggle="modal" data-target="#clientType" style="float: right;">+Add</a></h4>
            @foreach($cleintTypes as $sub)
            <a class="btn btn-outline-primary waves-effect waves-light" href="javascript:;" role="button" aria-controls="offcanvasExample">
               {{$sub->name}} <span table-name='{{$sub->getTable()}}' rel="{{$sub->id}}" class="btn-label-right deleteCategory"><i class="mdi mdi-close-circle-outline"></i></span>
            </a>
            @endforeach
            <form action="{{route('updateSettings')}}" method="post" id="bs4">
               @csrf
               <input type="hidden" name="redirect" value="schedule">
               <div class="form-group">
                  <label>Timezone</label>
                  <select name="timezone" class="form-control timezone">
                     <option value="">Select</option>
                     @foreach($timezones as $time)
                     <option value="{{$time->timezone}}" <?php if (@$settings->timezone == $time->timezone) {
                                                            echo 'selected';
                                                         } ?>>{{$time->timezone}}</option>
                     @endforeach
                  </select>
               </div>
               <div class="form-group">
                  <label>Minute Interval</label>
                  <select name="minute_interval" class="form-control timezone">
                     <option value="">Select</option>
                     <option value="1" <?php if (@$settings->minute_interval == 1) {
                                          echo 'selected';
                                       } ?>>1</option>
                     <option value="5" <?php if (@$settings->minute_interval == 5) {
                                          echo 'selected';
                                       } ?>>5</option>
                     <option value="15" <?php if (@$settings->minute_interval == 15) {
                                             echo 'selected';
                                          } ?>>15</option>
                  </select>
               </div>
               <div class="form-group">
                  <label>Pay Run</label>
                  <select name="pay_run" class="form-control timezone">
                     <option value="">Select</option>
                     <option value="weekly" <?php if (@$settings->pay_run == 'weekly') {
                                                echo 'selected';
                                             } ?>>Weekly</option>
                     <option value="fortnightly" <?php if (@$settings->pay_run == 'fortnightly') {
                                                      echo 'selected';
                                                   } ?>>Fortnightly</option>
                  </select>
               </div>
               <!-- Manage Shift -->
               <div class="form-group">
                  <label>First day of week</label>
                  <input type="text" name="first_day_fortnight" class="form-control" id="datepicker" value="{{date('Y-m-d', strtotime(@$settings->first_day_fortnight))}}">
               </div>
               <!-- Manage Shift -->
               <div class="form-group">
                  <label>Carer can manage shifts</label>
                  <select name="manage_shift" class="form-control timezone">
                     <option value="">Select</option>
                     <option value="yes" <?php if (@$settings->pay_run == 'yes') {
                                             echo 'selected';
                                          } ?>>Yes</option>
                     <option value="no" <?php if (@$settings->pay_run == 'no') {
                                             echo 'selected';
                                          } ?>>No</option>
                  </select>
               </div>
            </form>
         </div>
         <!-- end card-body-->
      </div>
      <!-- end card-->
   </div>
   <!-- end col -->
   <div class="col-md-4">
      <div class="card">
         <div class="card-body">
            <h4 class="header-title">Shift types <a href="{{route('shift-type.index')}}" style="float: right;">+Add</a></h4>
            @foreach($shiftTypes as $sub)
            <style>
               .dot1 {
                  height: 9px;
                  width: 10px;
                  border-radius: 50%;
                  display: inline-block;
                  margin-left: -7px;
               }
            </style>
            <a class="btn btn-outline-primary waves-effect waves-light" href="javascript:;" role="button" aria-controls="offcanvasExample">
               <span style="background-color: <?= $sub->color ?>" class="dot1"></span>{{$sub->name}}
            </a>
            @endforeach
         </div>
         <!-- end card-body-->
      </div>
      <!-- end card-->
   </div>
   <!-- end col -->
   <!-- <div class="col-md-4">
         <div class="card">
            <div class="card-body">
               <h4 class="header-title">Client public information headings</h4>
               <h5>Need to know information <a href="{{route('Need to know information')}}" style="float: right;display:none1">Manage</a></h5>
               @foreach($needInfo as $sub)
               <a class="btn btn-outline-primary waves-effect waves-light" href="javascript:;" role="button" aria-controls="offcanvasExample">
                  {{$sub->heading}}
               </a>
               @endforeach
               <h5>Useful information <a href="{{route('Useful information')}}" style="float: right;display:none1">Manage</a></h5>
               @foreach($useInfo as $sub)
               <a class="btn btn-outline-primary waves-effect waves-light" href="javascript:;" role="button" aria-controls="offcanvasExample">
                  {{$sub->heading}}
               </a>
               @endforeach
            </div>
           
         </div>
        
      </div> -->
   <!-- end col -->
   <div class="col-md-4">
      <div class="card">
         <div class="card-body">
            <h4 class="header-title">Notes headings <a href="javascript:;" data-toggle="modal" data-target="#holiday" style="float: right;display:none">+Add</a></h4>
            <h5>Progress Notes <a href="{{route('Progress Notes')}}" style="float: right;display:none1">Manage</a>
            </h5>
            @foreach($pNotes as $sub)
            <a class="btn btn-outline-primary waves-effect waves-light" href="javascript:;" role="button" aria-controls="offcanvasExample">
               {{$sub->heading}}
            </a>
            @endforeach
            <h5>Feedback <a href="{{route('Feedback')}}" style="float: right;display:none1">Manage</a></h5>
            @foreach($fNotes as $sub)
            <a class="btn btn-outline-primary waves-effect waves-light" href="javascript:;" role="button" aria-controls="offcanvasExample">
               {{$sub->heading}}
            </a>
            @endforeach
            <h5>Incident <a href="{{route('Incident')}}" style="float: right;display:none1">Manage</a></h5>
            @foreach($inc as $sub)
            <a class="btn btn-outline-primary waves-effect waves-light" href="javascript:;" role="button" aria-controls="offcanvasExample">
               {{$sub->heading}}
            </a>
            @endforeach
            <h5>Enquiry <a href="{{route('Enquiry')}}" style="float: right;display:none1">Manage</a></h5>
            @foreach($enq as $sub)
            <a class="btn btn-outline-primary waves-effect waves-light" href="javascript:;" role="button" aria-controls="offcanvasExample">
               {{$sub->heading}}
            </a>
            @endforeach
         </div>
         <!-- end card-body-->
      </div>
      <!-- end card-->
   </div>
   <!-- end col -->
   <div class="col-md-4" id="cd">
      <div class="card">
         <div class="card-body">
            <h4 class="header-title">Client Document Categories <a href="javascript:;" data-toggle="modal" data-target="#centermodal" style="float: right;">+Add</a></h4>
            @foreach($docCategories as $cat)
            <?php
            //dd($cat->getTable());
            ?>
            <a class="btn btn-outline-primary waves-effect waves-light" href="javascript:;" role="button" aria-controls="offcanvasExample">
               {{$cat->category_name}} <span table-name='{{$cat->getTable()}}' rel="{{$cat->id}}" class="btn-label-right deleteCategory"><i class="mdi mdi-close-circle-outline"></i></span>
            </a>
            @endforeach
         </div>
         <!-- end card-body-->
      </div>
      <!-- end card-->
   </div>
   <!-- end col -->
   <div class="col-md-4" id="qc">
      <div class="card">
         <div class="card-body">
            <h4 class="header-title">Carer competency & qualification categories <a href="javascript:;" data-toggle="modal" data-target="#qualification" style="float: right;">+Add</a></h4>
            @foreach($qualificationCategory as $cat)
            <a class="btn btn-outline-primary waves-effect waves-light" href="javascript:;" role="button" aria-controls="offcanvasExample">
               {{$cat->category_name}} <span table-name='{{$cat->getTable()}}' rel="{{$cat->id}}" class="btn-label-right deleteCategory"><i class="mdi mdi-close-circle-outline"></i></span>
            </a>
            @endforeach
         </div>
         <!-- end card-body-->
      </div>
      <!-- end card-->
   </div>
   <!-- end col -->
   <div class="col-md-4" id="rh">
      <div class="card">
         <div class="card-body">
            <h4 class="header-title">Report Headings <a href="javascript:;" data-toggle="modal" data-target="#reportheading" style="float: right;">+Add</a></h4>
            @foreach($reportHeadingCategory as $cat)
            <?php
            //echo  '<pre>';print_r($cat->catHeadings);

            ?>
            <a class="" href="javascript:;" role="button" aria-controls="offcanvasExample">
               {{$cat->category_name}} <a data="{{$cat->id}}" href="javascript:;" data-toggle="modal" data-target="#reportheadingN" style="float: right;" class="cadd">+Add</a>
            </a><br>
            @foreach($cat->catHeadings as $sub)
            <a class="btn btn-outline-primary waves-effect waves-light" href="javascript:;" role="button" aria-controls="offcanvasExample">
               {{$sub->name}} <span table-name='{{$sub->getTable()}}' rel="{{$sub->id}}" class="btn-label-right deleteCategory"><i class="mdi mdi-close-circle-outline"></i></span>
            </a>
            @endforeach
            @endforeach
         </div>
         <!-- end card-body-->
      </div>
      <!-- end card-->
   </div>
   <!-- end col -->
   <div class="col-md-4" id="ph">
      <div class="card">
         <div class="card-body">
            <h4 class="header-title">Public Holiday <a href="javascript:;" data-toggle="modal" data-target="#holiday" style="float: right;">+Add</a></h4>
            @foreach($holiday as $sub)
            <a class="btn btn-outline-primary waves-effect waves-light" href="javascript:;" role="button" aria-controls="offcanvasExample">
               {{date('d-m-Y', strtotime($sub->date))}} <span table-name='{{$sub->getTable()}}' rel="{{$sub->id}}" class="btn-label-right deleteCategory"><i class="mdi mdi-close-circle-outline"></i></span>
            </a>
            @endforeach
         </div>
         <!-- end card-body-->
      </div>
      <!-- end card-->
   </div>
</div>
<!-- end col -->
</div>
</div>
<!-- container -->
</div> <!-- content -->
<!-- Add Client document categories -->
<div class="modal fade" id="centermodal" tabindex="-1" role="dialog" aria-hidden="true">
   <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content">
         <div class="modal-header">
            <h4 class="modal-title" id="myCenterModalLabel">Client Document Categories</h4>
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
         </div>
         <div class="modal-body">
            <form action="{{route('uploadDocCategoty')}}" method='post'>
               @csrf
               <input type="hidden" name="redirect" value="cd">
               <div class="form-group">
                  <label>Document Category Name <code>*</code></label>
                  <input type="text" name="category_name" class="form-control" placeholder="Document Category Name" required>
               </div>
               <div class="form-group">
                  <button type="submit" class="ladda-button  btn btn-primary" dir="ltr" data-style="slide-left">Submit
                  </button>
               </div>
            </form>
         </div>
      </div>
      <!-- /.modal-content -->
   </div>
   <!-- /.modal-dialog -->
</div>
<!-- /.modal -->
<!-- Add Qualification categories -->
<div class="modal fade" id="qualification" tabindex="-1" role="dialog" aria-hidden="true">
   <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content">
         <div class="modal-header">
            <h4 class="modal-title" id="myCenterModalLabel">Qualification Categories</h4>
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
         </div>
         <div class="modal-body">
            <form action="{{route('uploadQualificationCategoty')}}" method='post'>
               @csrf
               <input type="hidden" name="redirect" value="qc">
               <div class="form-group">
                  <label>Qualification Category Name <code>*</code></label>
                  <input type="text" name="category_name" class="form-control" placeholder="Qualification Category Name" required>
               </div>
               <div class="form-group">
                  <button type="submit" class="ladda-button  btn btn-primary" dir="ltr" data-style="slide-left">Submit
                  </button>
               </div>
            </form>
         </div>
      </div>
      <!-- /.modal-content -->
   </div>
   <!-- /.modal-dialog -->
</div>
<!-- /.modal -->
<!-- Add Report heading -->
<div class="modal fade" id="reportheading" tabindex="-1" role="dialog" aria-hidden="true">
   <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content">
         <div class="modal-header">
            <h4 class="modal-title" id="myCenterModalLabel">Report headings</h4>
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
         </div>
         <div class="modal-body">
            <form action="{{route('uploadReportHeading')}}" method='post'>
               @csrf
               <input type="hidden" name="redirect" value="rh">
               <div class="form-group">
                  <label>Report Heading Name<code>*</code></label>
                  <input type="text" name="category_name" class="form-control" placeholder="Report Heading Name" required>
               </div>
               <div class="form-group">
                  <button type="submit" class="ladda-button  btn btn-primary" dir="ltr" data-style="slide-left">Submit
                  </button>
               </div>
            </form>
         </div>
      </div>
      <!-- /.modal-content -->
   </div>
   <!-- /.modal-dialog -->
</div>
<!-- /.modal -->
<!-- Add Report heading Category-->
<div class="modal fade" id="reportheadingN" tabindex="-1" role="dialog" aria-hidden="true">
   <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content">
         <div class="modal-header">
            <h4 class="modal-title" id="myCenterModalLabel">Add Report heading</h4>
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
         </div>
         <div class="modal-body">
            <form action="{{route('uploadReportHeadings')}}" method='post'>
               @csrf
               <input type="hidden" name="redirect" value="rh">
               <div class="form-group">
                  <label>Report Heading Name<code>*</code></label>
                  <input type="text" name="name" class="form-control" placeholder="Report Heading Name" required>
               </div>
               <input type="hidden" name="category_id" class="cId">
               <div class="form-group">
                  <button type="submit" class="ladda-button  btn btn-primary" dir="ltr" data-style="slide-left">Submit
                  </button>
               </div>
            </form>
         </div>
      </div>
      <!-- /.modal-content -->
   </div>
   <!-- /.modal-dialog -->
</div>
<!-- /.modal -->
<!-- Add Public Holiday-->
<div class="modal fade" id="holiday" tabindex="-1" role="dialog" aria-hidden="true">
   <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content">
         <div class="modal-header">
            <h4 class="modal-title" id="myCenterModalLabel">Add Public Holiday</h4>
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
         </div>
         <div class="modal-body">
            <form action="{{route('uploadPublicHoliday')}}" method='post'>
               @csrf
               <input type="hidden" name="redirect" value="ph">
               <div class="form-group">
                  <label>Public Holiday Date<code>*</code></label>
                  <input type="text" name="date" class="form-control datepicker1" id="d" required="" placeholder="dd-mm-YYYY">
               </div>
               <div class="form-group">
                  <button type="submit" class="ladda-button  btn btn-primary" dir="ltr" data-style="slide-left">Submit
                  </button>
               </div>
            </form>
         </div>
      </div>
      <!-- /.modal-content -->
   </div>
   <!-- /.modal-dialog -->
</div>
<!-- /.modal -->
<!-- Schedular -->
<div class="modal fade" id="clientType" tabindex="-1" role="dialog" aria-hidden="true">
   <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content">
         <div class="modal-header">
            <h4 class="modal-title" id="myCenterModalLabel">Add Client types</h4>
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
         </div>
         <div class="modal-body">
            <form action="{{route('clientType.store')}}" method='post'>
               @csrf
               <input type="hidden" name="redirect" value="schedule">
               <div class="form-group">
                  <label>Add Client types<code>*</code></label>
                  <input type="text" name="name" class="form-control" placeholder="Client types" required>
               </div>
               <div class="form-group">
                  <button type="submit" class="ladda-button  btn btn-primary" dir="ltr" data-style="slide-left">Submit
                  </button>
               </div>
            </form>
         </div>
      </div>
      <!-- /.modal-content -->
   </div>
   <!-- /.modal-dialog -->
</div>
<!-- /.modal -->
@endsection
@section('script')
<script src="{{asset('assets/libs/ladda/ladda.min.js')}}"></script>
<!-- Page js-->
<script src="{{asset('assets/js/pages/loading-btn.init.js')}}"></script>
<link rel="stylesheet" href="//code.jquery.com/ui/1.13.2/themes/base/jquery-ui.css">
<script type="text/javascript">
   // Cache selectors
   var lastId,
      topMenu = $("#mainNav"),
      topMenuHeight = 100,
      // All list items
      menuItems = topMenu.find("a"),
      // Anchors corresponding to menu items
      scrollItems = menuItems.map(function() {
         var item = $($(this).attr("href"));
         if (item.length) {
            return item;
         }
      });

   // Bind click handler to menu items
   // so we can get a fancy scroll animation
   menuItems.click(function(e) {
      var href = $(this).attr("href"),
         offsetTop = href === "#" ? 0 : $(href).offset().top - topMenuHeight + 1;
      $('html, body').stop().animate({
         scrollTop: offsetTop
      }, 100);
      e.preventDefault();
   });


   // Bind to scroll
   $(window).scroll(function() {
      // Get container scroll position
      var fromTop = $(this).scrollTop() + topMenuHeight;
      // Get id of current scroll item
      var cur = scrollItems.map(function() {
         if ($(this).offset().top < fromTop)
            return this;
      });
      // Get the id of the current element
      cur = cur[cur.length - 1];
      var id = cur && cur.length ? cur[0].id : "";
      if (lastId !== id) {
         lastId = id;
         // Set/remove active class
         menuItems
            .parent().removeClass("activeli")
            .end().filter("a[href='#" + id + "']").parent().addClass("activeli");
      }
   });

   function notePermissionForm() {
      document.getElementById("notePermissionForm").style.display = "block";
      document.getElementById("notePermissionView").style.display = "none";

   }

   function notePermissionFormCancel() {
      document.getElementById("notePermissionForm").style.display = "none";
      document.getElementById("notePermissionView").style.display = "inline-table";

   }

   function publicInformationform() {
      document.getElementById("publicInformationform").style.display = "block";
      document.getElementById("publicInformationView").style.display = "none";

   }

   function publicInformationCancel() {
      document.getElementById("publicInformationform").style.display = "none";
      document.getElementById("publicInformationView").style.display = "contents";

   }

   function faqform() {
      document.getElementById("faqform").style.display = "block";
      document.getElementById("faqView").style.display = "none";

   }

   function faqCancel() {
      document.getElementById("faqform").style.display = "none";
      document.getElementById("faqView").style.display = "contents";

   }

   function templateform() {
      document.getElementById("templateform").style.display = "block";
      $('.repeated').hide();

   }

   function templateCancel() {
      document.getElementById("templateform").style.display = "none";


   }

   function shiftform() {
      document.getElementById("shiftform").style.display = "block";
      document.getElementById("shiftView").style.display = "none";

   }

   function shiftCancel() {
      document.getElementById("shiftform").style.display = "none";
      document.getElementById("shiftView").style.display = "contents";

   }

   function ComplianceForm() {
      document.getElementById("ComplianceForm").style.display = "block";
      document.getElementById("ComplianceView").style.display = "none";

   }

   function ComplianceCancel() {
      document.getElementById("ComplianceForm").style.display = "none";
      document.getElementById("ComplianceView").style.display = "contents";
   }

   function SettingsFormForm() {
      document.getElementById("SettingsFormForm").style.display = "block";
      document.getElementById("SettingsFormView").style.display = "none";
   }

   function SettingsFormCancel() {
      document.getElementById("SettingsFormForm").style.display = "none";
      document.getElementById("SettingsFormView").style.display = "contents";

   }

   function KPIForm() {
      document.getElementById("KPIForm").style.display = "block";
      document.getElementById("KPIView").style.display = "none";

   }

   function KPICancel() {
      document.getElementById("KPIForm").style.display = "none";
      document.getElementById("KPIView").style.display = "contents";

   }

   function OtherForm() {
      document.getElementById("OtherForm").style.display = "block";
      document.getElementById("OtherView").style.display = "none";

   }

   function OtherCancel() {
      document.getElementById("OtherForm").style.display = "none";
      document.getElementById("OtherView").style.display = "contents";

   }

   function noteProgessForm() {
      document.getElementById("noteProgessForm").style.display = "block";
      document.getElementById("noteProgessView").style.display = "none";

   }

   function noteProgessCancel() {
      document.getElementById("noteProgessForm").style.display = "none";
      document.getElementById("noteProgessView").style.display = "contents";

   }

   function FeedbackForm() {
      document.getElementById("FeedbackForm").style.display = "block";
      document.getElementById("FeedbackView").style.display = "none";

   }

   function IncidentForm() {
      document.getElementById("IncidentForm").style.display = "block";
      document.getElementById("IncidentView").style.display = "none";

   }

   function IncidentCancel() {
      document.getElementById("IncidentForm").style.display = "none";
      document.getElementById("IncidentView").style.display = "contents";

   }


   function EnquiryForm() {
      document.getElementById("EnquiryForm").style.display = "block";
      document.getElementById("EnquiryView").style.display = "none";

   }

   function EnquiryCancel() {
      document.getElementById("EnquiryForm").style.display = "none";
      document.getElementById("EnquiryView").style.display = "contents";

   }

   function FeedbackCancel() {
      document.getElementById("FeedbackForm").style.display = "none";
      document.getElementById("FeedbackView").style.display = "contents";

   }

   function ClientDocumentForm() {
      document.getElementById("ClientDocumentForm").style.display = "block";
      document.getElementById("ClientDocumentView").style.display = "none";

   }

   function ClientDocumentCancel() {
      document.getElementById("ClientDocumentForm").style.display = "none";
      document.getElementById("ClientDocumentView").style.display = "contents";

   }

   function LeaveReasonsForm() {
      document.getElementById("LeaveReasonsForm").style.display = "block";
      document.getElementById("LeaveReasonsView").style.display = "none";

   }

   function CancelReasonsForm() {
      document.getElementById("CancelReasonsForm").style.display = "block";
      document.getElementById("CancelReasonsView").style.display = "none";

   }

   function RideCancel() {
      document.getElementById("CancelReasonsForm").style.display = "none";
      document.getElementById("CancelReasonsView").style.display = "contents";

   }

   function LeaveReasonsCancel() {
      document.getElementById("LeaveReasonsForm").style.display = "none";
      document.getElementById("LeaveReasonsView").style.display = "contents";

   }

   function ComplaintReasonsForm() {
      document.getElementById("ComplaintReasonsForm").style.display = "block";
      document.getElementById("ComplaintReasonsView").style.display = "none";

   }

   function ComplaintReasonsCancel() {
      document.getElementById("ComplaintReasonsForm").style.display = "none";
      document.getElementById("ComplaintReasonsView").style.display = "contents";

   }

   function ShiftChangeReasonsForm() {
      document.getElementById("ShiftChangeReasonsForm").style.display = "block";
      document.getElementById("ShiftChangeReasonsView").style.display = "none";

   }

   function ShiftChangeReasonsCancel() {
      document.getElementById("ShiftChangeReasonsForm").style.display = "none";
      document.getElementById("ShiftChangeReasonsView").style.display = "contents";

   }

   function TempChangeReasonsForm() {
      document.getElementById("TempChangeReasonsForm").style.display = "block";
      document.getElementById("TempChangeReasonsView").style.display = "none";

   }

   function ShiftChangeReasonsCancel() {
      document.getElementById("TempChangeReasonsForm").style.display = "none";
      document.getElementById("TempChangeReasonsView").style.display = "contents";

   }

   function RatingReasonsForm() {
      document.getElementById("RatingReasonsForm").style.display = "block";
      document.getElementById("RatingReasonsView").style.display = "none";

   }

   function RatingReasonsCancel() {
      document.getElementById("RatingReasonsForm").style.display = "none";
      document.getElementById("RatingReasonsView").style.display = "contents";

   }

   function CompanyForm() {
      document.getElementById("CompanyLogo").style.display = "none";
      document.getElementById("CompanyForm").style.display = "block";

   }

   function CompanyFormCancel() {
      document.getElementById("CompanyLogo").style.display = "block";
      document.getElementById("CompanyForm").style.display = "none";

   }

   function clientType() {
      document.getElementById("clientTypeForm").style.display = "block";
      document.getElementById("clientTypeView").style.display = "none";

   }

   function clientTypeCancel() {
      document.getElementById("clientTypeForm").style.display = "none";
      document.getElementById("clientTypeView").style.display = "contents";

   }

   function clientTypeFormCancel() {
      document.getElementById("clientTypeFormCancel").style.display = "none";
      document.getElementById("Interval-form").style.display = "none";
      document.getElementById("Timezone-form").style.display = "none";
      document.getElementById("Pay-form").style.display = "none";
      document.getElementById("Interval").style.display = "contents";
      document.getElementById("Timezone").style.display = "contents";
      document.getElementById("Communication").style.display = "contents";
      document.getElementById("Pay").style.display = "contents";
      document.getElementById("first_day_of_fornight").style.display = "none";
   }

   function SchedulerForm() {
      document.getElementById("clientTypeFormCancel").style.display = "contents";
      document.getElementById("Interval").style.display = "none";
      document.getElementById("Timezone").style.display = "none";
      document.getElementById("Communication").style.display = "none";
      document.getElementById("Pay").style.display = "none";
      document.getElementById("Interval-form").style.display = "contents";
      document.getElementById("Timezone-form").style.display = "contents";
      document.getElementById("Pay-form").style.display = "contents";
      document.getElementById("first_day_of_fornight").style.display = "contents";
   }

   function publicholidaysform() {
      document.getElementById("publicholidaysform").style.display = "block";
      document.getElementById("publicholidaysview").style.display = "none";

   }

   function publicholidaysCancel() {
      document.getElementById("publicholidaysform").style.display = "none";
      document.getElementById("publicholidaysview").style.display = "contents";

   }

   function attendanceForm() {
      document.getElementById("attendanceCancel").style.display = "block";
      document.getElementById("notice_period").style.display = "none";
      document.getElementById("notice_period_form").style.display = "block";
      document.getElementById("attendance_threshold").style.display = "none";
      document.getElementById("attendance_threshold_form").style.display = "block";
      document.getElementById("time_precision").style.display = "none";
      document.getElementById("time_precision_form").style.display = "block";
      document.getElementById("pay_rate").style.display = "none";
      document.getElementById("pay_rate_form").style.display = "block";
      document.getElementById("clock_alert_message").style.display = "none";
      document.getElementById("clock_alert_message_form").style.display = "block";

   }

   function ridesettingForm() {
      document.getElementById("ridesettingButtons").style.display = "block";

      document.getElementById("noshow_timer_group").style.display = "block";

      document.getElementById("noshowFrequency").style.display = "none";


      document.getElementById("noshowSelect_frequency").style.display = "block";
      document.getElementById("ridenoshow").style.display = "none";

      document.getElementById("noshowCounter").style.display = "none";
      document.getElementById("noshowCounter_form").style.display = "block";
      document.getElementById("leaveCounter_form").style.display = "block";
      document.getElementById("leaveCounter").style.display = "none";



   }

   function ridesettingCancel() {
      document.getElementById("ridesettingButtons").style.display = "none";

      document.getElementById("noshow_timer_group").style.display = "none";
      document.getElementById("noshowFrequency").style.display = "block";


      document.getElementById("noshowSelect_frequency").style.display = "none";
      document.getElementById("ridenoshow").style.display = "block";

      document.getElementById("noshowCounter").style.display = "block";
      document.getElementById("noshowCounter_form").style.display = "none";
      document.getElementById("leaveCounter_form").style.display = "none";
      document.getElementById("leaveCounter").style.display = "block";
   }


   function AdditionalformCancel() {
      document.getElementById("attendanceCancel").style.display = "none";
      document.getElementById("notice_period").style.display = "block";
      document.getElementById("notice_period_form").style.display = "none";
      document.getElementById("attendance_threshold").style.display = "block";
      document.getElementById("attendance_threshold_form").style.display = "none";
      document.getElementById("time_precision").style.display = "block";
      document.getElementById("time_precision_form").style.display = "none";
      document.getElementById("pay_rate").style.display = "block";
      document.getElementById("pay_rate_form").style.display = "none";
      document.getElementById("clock_alert_message").style.display = "block";
      document.getElementById("clock_alert_message_form").style.display = "none";
   }

   $(document).ready(function() {
      $(".ReportHeadingForm").click(function() {
         var id = $(this).data("id");

         document.getElementById("ReportHeadingForm" + id).style.display = "block";
         document.getElementById("ReportHeadingView" + id).style.display = "none";

      });

      $(".ReportHeadingCancel").click(function() {
         var id = $(this).data("id");

         document.getElementById("ReportHeadingForm" + id).style.display = "none";
         document.getElementById("ReportHeadingView" + id).style.display = "contents";

      });
   });

   $(document).ready(function() {

      $('input[type=radio][name=pay_run]').change(function() {
         if (this.value == 'Fornightly') {
            $("#first_day_of_fornight_heading").html("<span>First Day of Fornight</span>");
         } else if (this.value == 'Weekly') {
            $("#first_day_of_fornight_heading").html("<span>First Day of Week</span>");
         }
      });

      $('#holiday').on('shown.bs.modal', function() {
         //  alert();
         $('.datepicker1').datepicker();
      });

      $('.timezone, #datepicker').change(function() {
         $('#bs4').submit();
      });
      $('.eu').change(function() {
         $('#bs5').submit();
      });

      $('.np').change(function() {
         $('#bs6').submit();
      });
      $('.expire_access').keyup(function() {
         $('#bs6').submit();
      });


      $("#d").datepicker({
         dateFormat: 'dd-mm-yy'
      });

      $(".datepicker").datepicker({
         dateFormat: 'dd-mm-yy'
      });
      $("#datepicker").datepicker({
         dateFormat: 'dd-mm-yy'
      });

      $(".cadd").click(function() {
         var Id = $(this).attr('data');
         $('.cId').val(Id);
      });


      $("#datepicker").datepicker({
         maxDate: 0,
         dateFormat: 'dd-mm-yy'
      });
      $('.phone').keyup(function(e) {
         if (/\D/g.test(this.value)) {
            // Filter non-digits from input value.
            this.value = this.value.replace(/\D/g, '');
         }
      });

      $(".deleteCategory").click(function() {
         var attrv = $(this).attr('rel');
         var tblName = $(this).attr('table-name');
         //alert(tblName);
         if (!confirm('Are u sure you want to delete?')) {
            return false;
         }
         var url = "<?php echo url('/users/deleteCategory'); ?>/" + attrv + "/" + tblName;

         window.location.href = url;
      });


      // on change type ans show roles
      $('.type').change(function() {
         var curretValue = $(this).val();
         if (curretValue == 'Office User') {
            $('.role').show();
         } else {
            $('.role').hide();
         }
      });
      $('#is_repeat').change(function() {
         if ($(this).prop('checked')) {
            $('.repeated').show(); // Show repeated fields if checkbox is checked
         } else {
            $('.repeated').hide(); // Hide repeated fields if checkbox is unchecked
         }
      });

      // when clickbox
      // $("input[type='checkbox']").click(function() { 
      // $('.type').change(function(){

      // });​

   });
</script>
<script>
   function initAutocomplete() {
      var input = document.getElementById('companyaddress');
      var options = {
         types: ['geocode'], // This restricts results to addresses
      };

      var autocomplete = new google.maps.places.Autocomplete(input, options);

      // Add a listener to capture the selected place
      autocomplete.addListener('place_changed', function() {
         var place = autocomplete.getPlace();

         if (!place.geometry) {
            console.log('Place details not available');
            return;
         }

         // Get latitude and longitude
         var latitude = place.geometry.location.lat();
         var longitude = place.geometry.location.lng();


         // Extract postal code (if available)

         var country = '';
         for (var i = 0; i < place.address_components.length; i++) {
            for (var j = 0; j < place.address_components[i].types.length; j++) {
               if (place.address_components[i].types[j] === 'country') {
                  country = place.address_components[i].long_name;
                  break;
               }
            }
         }
         console.log(country);


         // Store the selected place data
         //  console.log(latitude);
         //  console.log(longitude);
         document.getElementById('latitude').value = latitude;
         document.getElementById('longitude').value = longitude;
         document.getElementById('country').value = country;

      });
   }
</script>

<script>
   // This is your API key from the Google Cloud Console
   const GOOGLE_API_KEY = '{{env("GOOGLE_API_KEY")}}';

   // Load the Google Places API and call the initAutocomplete() function
   function loadScript() {
      var script = document.createElement('script');
      script.type = 'text/javascript';
      script.src = `https://maps.googleapis.com/maps/api/js?key=` + GOOGLE_API_KEY + `&libraries=places&callback=initAutocomplete`;

      document.body.appendChild(script);
   }

   // Listen for the DOM content to be fully loaded, then load the script
   window.addEventListener('load', loadScript);
</script>
<script>
   function incrementCounter(inputId) {
      var input = document.getElementById(inputId);
      input.stepUp();
   }

   function decrementCounter(inputId) {
      var input = document.getElementById(inputId);
      input.stepDown();
   }

   function toggleNoshowTimerField(radio) {
      var noshowTimerGroup = document.getElementById("noshow_timer_group");
      if (radio.value === "Yes") {
         noshowTimerGroup.style.display = "block";
      } else {
         noshowTimerGroup.style.display = "none";
      }
   }

   function toggleNoshowTimerField(radio) {
      var noshowTimerGroup = document.getElementById("noshow_timer_group");
      if (radio.value === "1") {
         noshowTimerGroup.style.display = "block";
      } else {
         noshowTimerGroup.style.display = "none";
      }
   }
</script>
<script>
   $(document).ready(function() {

   });
</script>
@endsection