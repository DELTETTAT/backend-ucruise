<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\CustomAuthController;
use App\Http\Controllers\{LeavemanagementController, ShiftChangeRequestController, VehicleController};
use App\Http\Controllers\Admin\{AccountController, ReasonController, RescheduleController, StaffController, TeamController, SchedularController};
use App\Http\Controllers\Superadmin\{ActivityController, UsersController, SubscriptionController, ShiftType, InvoiceSettingsController, ClientController, ComplainceController, PricebookController, AllowanceController, BillingController, PaygroupController};

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great !
|
*/

Route::get('/', function () {
    return view('welcome');
});


Route::get('signin', [CustomAuthController::class, 'index'])->name('signin');

/**
 * This Route is use for privacy policy
 */
Route::get('privacy-policy', [VehicleController::class, 'privacyPolicy'])->name('privacyPolicy');

Route::get('logout', [CustomAuthController::class, 'signOut'])->name('logout');
Route::get('admin/login', [CustomAuthController::class, 'index'])->name('login');
Route::post('admin/custom-login', [CustomAuthController::class, 'customLogin'])->name('login.custom');


// For staff login
Route::any('staff/login', [CustomAuthController::class, 'staff'])->name('staff');


// Forgot password
Route::get('/forgot-password', function () {
    return view('auth.passwords.email');
});
Route::get('/admin/success', function () {
    return view('auth.passwords.confirm');
});

Route::any('admin/otp', [CustomAuthController::class, 'otp']);
Route::post('admin/sendOtp', [CustomAuthController::class, 'sendOtp']);
Route::post('admin/savePassword', [CustomAuthController::class, 'savePassword']);


Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');



Route::group(array('prefix' => 'admin', 'middleware' => 'admin'), function () {

    // ================================================================//
    // ==========================SUPER ADMIN ROUTES ==================//

    Route::get('dashboard', [CustomAuthController::class, 'dashboard'])->name('admin.dashboard');
    // List all Admin
    Route::get('allAdmin', [UsersController::class, 'index']);
    Route::get('add-admin', [UsersController::class, 'create']);
    Route::any('my-account', [UsersController::class, 'myAccount']);
    Route::any('/edit-admin/{id}', [UsersController::class, 'edit']);
    Route::any('/delete-admin/{id}', [UsersController::class, 'destroy']);
    Route::any('updateProfile', [UsersController::class, 'updateProfile']);
    Route::any('emptyDatabase/{id}', [UsersController::class, 'emptyDatabase']);
    Route::any('deleteAllDrivers/{id}', [UsersController::class, 'deleteAllDrivers']);

    Route::resource('admin', UsersController::class);
    // Create subscription by super admin
    Route::resource('subscription', SubscriptionController::class);


    // Send bulk Accouncement
    Route::any('send-announcement', [UsersController::class, 'sendAnnouncement']);

    // Create Group users
    Route::any('list-announcement', [UsersController::class, 'listAnnouncement']);
    Route::any('create-group', [UsersController::class, 'createGroup']);
    Route::any('create-image', [UsersController::class, 'createimage']);
    Route::any('search-sub-users', [UsersController::class, 'searchByEmail']);


    Route::get('get-all-sub-users', [UsersController::class, 'getAllSubUsers']);
    Route::post('save-selected-users', [UsersController::class, 'saveSelectedUsers']);
    Route::post('storeimage', [UsersController::class, 'storeimage']);
    Route::get('imageindex', [UsersController::class, 'imageindex']);
    Route::any('imagedestroy/{id}', [UsersController::class, 'imagedestroy']);
    //Route::any('scheduler', [UsersController::class, 'scheduler']);

    Route::any('storeAnnouncement', [UsersController::class, 'storeAnnouncement']);
    Route::any('notificationSeen/{id}', [UsersController::class, 'notificationSeen']);
    Route::any('delete-announcement/{id}', [UsersController::class, 'deleteAnnouncement']);


    // Inactive Users inactive-userss
    Route::any('inactive-users', [UsersController::class, 'inactiveUsers']);
    Route::any('group-login-users', [UsersController::class, 'GroupLoginUsers']);
    Route::any('images-template', [UsersController::class, 'images']);

    // Schedular
    Route::resource('scheduler', SchedularController::class);
    Route::any('daily', [SchedularController::class, 'Daily']);
    Route::get('schedule/create', [SchedularController::class, 'addSchedule'])->name('addSchedule');
    Route::post('schedule/store', [SchedularController::class, 'storeSchedule'])->name('storeSchedule');
    Route::get('schedule/edit/{id}/{date}', [SchedularController::class, 'editSchedule'])->name('editSchedule');
    Route::post('schedule/update/{id}', [SchedularController::class, 'updateSchedule'])->name('updateSchedule');
    Route::post('schedule/getweeklyScheduleInfo', [SchedularController::class, 'getweeklyScheduleInfo'])->name('getweeklyScheduleInfo');
});

Route::group(array('prefix' => 'users', 'middleware' => 'admin'), function () {

    //Staff Routes
    Route::get('staff', [StaffController::class, 'index'])->name('staff.list');;
    Route::get('add-staff', [StaffController::class, 'create'])->name('addStaff');
    Route::post('staff-store', [StaffController::class, 'store'])->name('staff.store');
    Route::any('delete-staff/{id}', [StaffController::class, 'deleteStaff'])->name('staff.delete');

    //Account Management Routes
    Route::any('accounts', [AccountController::class, 'index'])->name('account');
    Route::any('upload-document-categoty', [AccountController::class, 'uploadDocCategory'])->name('uploadDocCategoty');
    Route::any('upload-report-heading', [AccountController::class, 'uploadReportHeading'])->name('uploadReportHeading');
    Route::any('upload-public-holiday', [AccountController::class, 'uploadPublicHoliday'])->name('uploadPublicHoliday');
    Route::any('upload-report-headings', [AccountController::class, 'uploadReportHeadings'])->name('uploadReportHeadings');
    Route::any('upload-qualification-categoty', [AccountController::class, 'uploadQualificationCategory'])->name('uploadQualificationCategoty');

    Route::any('storeHoliday', [AccountController::class, 'storeHoliday'])->name('holiday.store');
    Route::any('shiftTypeStore', [AccountController::class, 'shiftTypeStore'])->name('shiftType.store');
    Route::any('storeDocCategory', [AccountController::class, 'storeDocCategory'])->name('docCategory.store');
    Route::any('storeLeaveReason', [ReasonController::class, 'storeLeaveReason'])->name('leaveReason.store');
    Route::any('storeratingReason', [ReasonController::class, 'storeRatingReason'])->name('ratingReason.store');
    Route::any('storetempReason', [ReasonController::class, 'storeTempReason'])->name('tempReason.store');
    Route::any('storecancelRideReason', [ReasonController::class, 'storeCancelRideReason'])->name('cancelRideReason.store');
    Route::any('storeComplaintReason', [ReasonController::class, 'storeComplaintReason'])->name('complaintReason.store');
    Route::any('storeShiftChangeReason', [ReasonController::class, 'storeShiftChangeReason'])->name('shiftChangeReason.store');
    Route::any('storeReportHeading', [AccountController::class, 'storeReportHeading'])->name('reportHeading.store');
    Route::any('storeFaq', [AccountController::class, 'storeFaq'])->name('faq.store');

    Route::any('deleteNote/{id}', [AccountController::class, 'deleteNote'])->name('note.delete');
    Route::any('deleteFaq/{id}', [AccountController::class, 'deleteFaq'])->name('faq.delete');
    Route::any('deleteHoliday/{id}', [AccountController::class, 'deleteHoliday'])->name('holiday.delete');
    Route::any('deleteShiftType/{id}', [AccountController::class, 'deleteShiftType'])->name('shiftType.delete');
    Route::any('deleteClientType/{id}', [AccountController::class, 'deleteClientType'])->name('clientType.delete');
    Route::any('deleteDocCategory/{id}', [AccountController::class, 'deleteDocCategory'])->name('docCategory.delete');
    Route::any('deleteReason/{id}', [ReasonController::class, 'deleteReason'])->name('reason.delete');
    Route::any('deleteCancelRideReason/{id}', [ReasonController::class, 'deleteCancelRideReason'])->name('cancelRideReason.delete');
    Route::any('deleteReportHeading/{id}', [AccountController::class, 'deleteReportHeading'])->name('reportHeading.delete');

    Route::any('deleteCategory/{id}/{table}', [UsersController::class, 'deleteCategory'])->name('deleteCategory');
    Route::any('add-notes', [UsersController::class, 'addNotes'])->name('Add Notes');
    Route::any('noteStore', [UsersController::class, 'noteStore'])->name('note.store');


    // Manage notes progress.manage
    Route::any('roles', [UsersController::class, 'roles'])->name('roles');
    Route::any('progress-note-list', [UsersController::class, 'progressList'])->name('Progress Notes');

    Route::any('billing', [BillingController::class, 'index'])->name('billing.index');
    Route::post('getBillingInformation', [BillingController::class, 'getBillingInformation'])->name('getBillingInformation');

    // Activity routes
    Route::any('activity', [ActivityController::class, 'index'])->name('activity.index');
    Route::post('getActivityInformation', [ActivityController::class, 'getActivityInformation'])->name('getActivityInformation');

    // Module access
    Route::any('moduleAccess/{id}', [UsersController::class, 'moduleAccess'])->name('moduleAccess');

    Route::any('edit-note/{id}', [UsersController::class, 'editNote']);
    Route::any('note-update/{id}', [UsersController::class, 'noteUpdate'])->name('note.update');

    Route::any('enquiry', [UsersController::class, 'progressList'])->name('Enquiry');
    Route::any('feedback', [UsersController::class, 'progressList'])->name('Feedback');
    Route::any('incident', [UsersController::class, 'progressList'])->name('Incident');
    Route::any('usefull-information', [UsersController::class, 'progressList'])->name('Useful information');
    Route::any('need-to-know-information', [UsersController::class, 'progressList'])->name('Need to know information');

    // Shift type

    Route::resource('shift-type', ShiftType::class);
    // For client types
    Route::any('company-map', [UsersController::class, 'companyMap'])->name('company-map');
    Route::post('change-company-location', [UsersController::class, 'updateCompanyLoction'])->name('update.location');
    Route::any('update-company', [UsersController::class, 'updateCompany'])->name('update.company');
    Route::any('updateSettings', [UsersController::class, 'updateSettings'])->name('updateSettings');
    Route::any('client-store', [UsersController::class, 'clientTypeStore'])->name('clientType.store');
    Route::any('update-notePermission', [UsersController::class, 'notePermission'])->name('update.notePermission');
    Route::any('client/updateSettings', [UsersController::class, 'clientUpdateSettings'])->name('clientUpdateSettings');
    Route::any('update-time-attendence', [UsersController::class, 'updateTimeAttendence'])->name('update.time.attendence');
    Route::any('update-ride-setting', [UsersController::class, 'updateRideSetting'])->name('update.ride.setting');
    Route::any('store/template', [UsersController::class, 'storeTemplate'])->name('template.store');

    //Leave
    Route::any('leave', [LeavemanagementController::class, 'getLeaveRequests'])->name('leave');
    Route::get('/reject-leave/{id}', [LeavemanagementController::class, 'rejectLeave'])->name('reject-leave');
    Route::get('/approve-leave/{id}', [LeavemanagementController::class, 'approveLeave'])->name('approve-leave');
    Route::get('/leave-requests', [LeavemanagementController::class, 'getLeaveRequestsAjax'])->name('leave-requests');

    Route::any('reschedules', [RescheduleController::class, 'index'])->name('reschedule.index');
    Route::any('acceptReschedule/{schedule_id}/{reschedule_id}', [RescheduleController::class, 'acceptReschedule'])->name('acceptReschedule');
    Route::any('rejectReschedule/{id}', [RescheduleController::class, 'rejectReschedule'])->name('rejectReschedule');
    Route::any('findSimilarRoutes/{id}', [RescheduleController::class, 'similarRoutes'])->name('similarRoutes');

    // Shift change Request status
    Route::get('/approve-shifchange/{id}', [ShiftChangeRequestController::class, 'approveShiftChangeRequest'])->name('approve-shiftchange');
    Route::get('/reject-shiftchange/{id}', [ShiftChangeRequestController::class, 'rejectShiftChangeRequest'])->name('reject-shiftchange');

    //Vehicles
    Route::any('vehicles', [VehicleController::class, 'index'])->name('vehicles');
    Route::any('vehicles/add', [VehicleController::class, 'add'])->name('vehicles.add');
    Route::any('vehicles/store', [VehicleController::class, 'store'])->name('vehicles.store');
    Route::any('vehicles/show', [VehicleController::class, 'show'])->name('vehicles.show');
    Route::any('vehicles/edit/{id}', [VehicleController::class, 'edit'])->name('vehicles.edit');
    Route::any('vehicles/update/{id}', [VehicleController::class, 'update'])->name('vehicles.update');
    Route::any('vehicles/delete/{id}', [VehicleController::class, 'destroy'])->name('vehicles.delete');


    // Invoice settings
    Route::any('storeTax', [InvoiceSettingsController::class, 'storeTax'])->name('storeTax');
    Route::any('invoice_settings', [InvoiceSettingsController::class, 'index'])->name('invoice_settings');
    Route::any('invoice-update', [InvoiceSettingsController::class, 'invoiceUpdate'])->name('invoice.settings');

    // REMINDERS
    Route::any('reminders', [InvoiceSettingsController::class, 'reminders'])->name('reminders');
    Route::any('add-reminder', [InvoiceSettingsController::class, 'addReminder'])->name('add.reminder');
    Route::any('reminder-store', [InvoiceSettingsController::class, 'reminderStore'])->name('reminder.store');
    Route::any('edit-reminder/{id}', [InvoiceSettingsController::class, 'editReminder'])->name('edit.reminder');
    Route::any('reminder-update/{id}', [InvoiceSettingsController::class, 'reminderUpdate'])->name('reminder.update');

    // Subscription

    Route::any('subscription', [InvoiceSettingsController::class, 'subscription']);
    Route::any('exportStaff', [StaffController::class, 'exportStaff'])->name('exportStaff');
    Route::any('closeAccount', [UsersController::class, 'closeAccount'])->name('closeAccount');
    Route::any('senBulkEmail', [UsersController::class, 'senBulkEmail'])->name('senBulkEmail');
    Route::any('activateAccount/{id}', [UsersController::class, 'activateAccount'])->name('activateAccount');


    //Team Routes
    Route::any('add-team', [TeamController::class, 'create']);
    Route::any('edit-team/{id}', [TeamController::class, 'edit']);
    Route::any('teams', [TeamController::class, 'index'])->name('teams');
    Route::any('store-team', [TeamController::class, 'store'])->name('store.team');
    Route::any('updateTeam/{id}', [TeamController::class, 'update'])->name('updateTeam');


    Route::any('listSMS/{type}', [UsersController::class, 'listSMS'])->name('listSMS');
    Route::any('senBulkSMS', [UsersController::class, 'senBulkSMS'])->name('senBulkSMS');
    Route::any('listEmails/{type}', [UsersController::class, 'listEmails'])->name('listEmails');
    // Route::any('clients', [UsersController::class, 'clients'])->name('clients');

    Route::resource('clients', ClientController::class);
    Route::any('clientSettingStore/{id}', [ClientController::class, 'clientSettingStore'])->name('clientSettingStore');
    Route::any('clientAdditionalInfo/{id}', [ClientController::class, 'clientAdditionalInfo'])->name('clientAdditionalInfo');
    Route::any('delete-driver/{id}', [ClientController::class, 'deleteDriver'])->name('driver.delete');
    // Archive account
    Route::any('arcchiveClients', [ClientController::class, 'arcchiveClients'])->name('arcchiveClients');
    Route::any('unurchiveClient/{id}', [ClientController::class, 'unurchiveClient'])->name('unurchiveClient');
    Route::any('clientArchiveAccount/{id}', [ClientController::class, 'clientArchiveAccount'])->name('clientArchiveAccount');

    // Clients Documents
    Route::any('newClient', [ClientController::class, 'newClient'])->name('newClient');
    Route::any('clientDocuments/{id}', [ClientController::class, 'clientDocuments'])->name('clientDocuments');
    Route::any('expireClientDocuments', [ClientController::class, 'expireClientDocuments'])->name('expireClientDocuments');
    Route::any('uploadClientDocument/{id}', [ClientController::class, 'uploadClientDocument'])->name('uploadClientDocument');
    Route::any('updateClientDocCategory/{id}', [ClientController::class, 'updateClientDocCategory'])->name('updateClientDocCategory');
    Route::any('updateClientNoExpireation/{id}', [ClientController::class, 'updateClientNoExpireation'])->name('updateClientNoExpireation');


    Route::any('settingsUpdate/{id}', [UsersController::class, 'settingsUpdate']);
    Route::any('edit-staff/{id}', [StaffController::class, 'edit'])->name('editStaff');
    Route::any('updateStaff/{id}', [StaffController::class, 'update'])->name('updateStaff');
    Route::any('staff-details/{id}', [StaffController::class, 'show'])->name('staffDetails');
    Route::any('arcchiveStaff', [StaffController::class, 'arcchiveStaff'])->name('arcchiveStaff');
    Route::any('staffDocuments/{id}', [StaffController::class, 'staffDocuments'])->name('staffDocuments');
    Route::any('updateStaffKin/{id}', [StaffController::class, 'updateStaffKin'])->name('updateStaffKin');
    Route::any('unurchiveStaff/{id}', [StaffController::class, 'unurchiveStaff'])->name('unurchiveStaff');
    Route::any('updateStaffNote/{id}', [StaffController::class, 'updateStaffNote'])->name('updateStaffNote');
    Route::any('expireStaffDocuments', [StaffController::class, 'expireStaffDocuments'])->name('expireStaffDocuments');
    Route::any('staffPayrollSetting/{id}', [StaffController::class, 'staffPayrollSetting'])->name('staffPayrollSettings');
    Route::any('uploadStaffDocument/{id}', [StaffController::class, 'uploadStaffDocument'])->name('uploadStaffDocument');
    Route::any('updateStaffDocCategory/{id}', [StaffController::class, 'updateStaffDocCategory'])->name('updateStaffDocCategory');
    Route::any('updateStaffNoExpireation/{id}', [StaffController::class, 'updateStaffNoExpireation'])->name('updateStaffNoExpireation');


    Route::any('staffArchiveAccount/{id}', [StaffController::class, 'staffArchiveAccount'])->name('staffArchiveAccount');
    Route::any('updateModulePermission/{roleId}', [UsersController::class, 'updateModulePermission'])->name('updateModulePermission');

    // Coomplainace compliance
    Route::resource('compliance', ComplainceController::class);

    // For Prices Table
    Route::resource('prices', PricebookController::class);
    Route::any('pricebookStore', [PricebookController::class, 'pricebookStore'])->name('pricebookStore');
    Route::any('pricebookEdit/{id}', [PricebookController::class, 'pricebookEdit'])->name('pricebookEdit');
    Route::any('priceBookUpdate', [PricebookController::class, 'priceBookUpdate'])->name('priceBookUpdate');
    // For Pay Group Table
    Route::resource('award_group', PaygroupController::class);
    Route::any('payGroupStore', [PaygroupController::class, 'payGroupStore'])->name('payGroupStore');
    Route::any('payGroupUpdate/{id}', [PaygroupController::class, 'payGroupUpdate'])->name('payGroupUpdate');


    // Allowwnce allowance
    Route::resource('allowance', AllowanceController::class);
    Route::any('managePermissions', [UsersController::class, 'managePermissions'])->name('managePermissions');
    Route::any('managePermissions/store', [UsersController::class, 'storePermissions'])->name('managePermissions.store');
});
