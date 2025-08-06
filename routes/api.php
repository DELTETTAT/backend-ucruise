<?php

use App\Http\Controllers\Api\StaffController;
use App\Http\Controllers\Api\AccountSetupController;
use App\Http\Controllers\Api\ActivityController;
use App\Http\Controllers\Api\RescheduleController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CompanyCalender\CompanyCalenderController;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\DriverController;
use App\Http\Controllers\Api\LeaveController;
use App\Http\Controllers\Api\ScheduleController;
use App\Http\Controllers\Api\HomeController;
use App\Http\Controllers\Api\HolidayController;
use App\Http\Controllers\Api\UploadCsvController;
use App\Http\Controllers\Api\HiringTemplate\HiringTemplateController;
use App\Http\Controllers\Api\Designation\DesignationController;
use App\Http\Controllers\Api\Hrms\Quiz\HiringQuiz\HiringquizController;
use App\Http\Controllers\Api\Hrms\Quiz\QuestionType\QuestiontypeController;
use App\Http\Controllers\Api\Hrms\Quiz\QuizLevel\QuizLevelController;
use App\Http\Controllers\Api\Hrms\Quiz\Reason\HrmsReasonController;
use App\Http\Controllers\Api\Hrms\Team\HrmsTeamController;
use App\Http\Controllers\Api\Hrms\Team\TeamManagerController;
use App\Http\Controllers\Api\NewApplicant\NewApplicantController;
use App\Http\Controllers\Api\NewApplicant\OfferacceptdeclineController;
use App\Http\Controllers\Api\NewApplicant\JobRequirementController;
use App\Http\Controllers\Api\UserAnswer\UserAnswerController;
use App\Http\Controllers\Api\Hrms\BasicSetting\UploadDocumentsController;
use App\Http\Controllers\Api\Hrms\BasicSetting\ReminderController;
use App\Http\Controllers\Api\Hrms\EmployeeDocuments\EmployeeDocumentsController;
use App\Http\Controllers\Api\Hrms\Employee\EmployeeAttendanceController;
use App\Http\Controllers\Api\Hrms\Employee\EmployeeCalenderAttendanceController;
use App\Http\Controllers\Api\Hrms\Employee\EmployeeController;
use App\Http\Controllers\Api\Performances\PerformanceController;
use App\Http\Controllers\Api\Timesheet\TimesheetController;
use App\Http\Controllers\Api\Hrms\Payroll\HrmsPayrollController;
use App\Http\Controllers\Api\Hrms\RolePermissions\RoleandpermissionController;
use App\Http\Controllers\Api\Hrms\ReportSettings\ReportSettingController;
use App\Http\Controllers\Api\Hrms\Announcement\AnnouncementController;
use App\Http\Controllers\Api\Hrms\Dashboard\HrmsDashboardController;
use App\Http\Controllers\Api\Hrms\Dashboard\AdminDashboardController;
use App\Http\Controllers\Api\Hrms\Resume\ResumeuploadController;
use App\Http\Controllers\Api\EmployeeSalary\SalaySlabController;
use App\Http\Controllers\Api\EmployeeSalary\EployeeSalaryCalculation;
use App\Http\Controllers\Api\SalarySetting\TaxInformationController;
use App\Http\Controllers\Api\SalarySetting\PFAndLeaveSettingController;
use App\Http\Controllers\Api\SalarySetting\TDSSettingController;
use App\Http\Controllers\Api\SalarySetting\SalarySettingController;
use App\Http\Controllers\Api\Projects\ProjectController;
use App\Http\Controllers\Api\Projects\ProjectTaskController;
use App\Http\Controllers\Api\Projects\ProjectSubTaskController;
use App\Http\Controllers\Api\Projects\SubProjectController;
use App\Http\Controllers\Api\Projects\MinProjectController;
use App\Http\Controllers\Api\Hrms\BasicSetting\BasicSettingController;
use App\Http\Controllers\Api\Hrms\EmployeeActionsController;
use App\Http\Controllers\Api\Hrms\Report\DailyWorkReportController;
use App\Http\Controllers\Api\Hrms\AddEmployeeFromExcel;
use App\Http\Controllers\Api\LeaveacceptdeclineController;

use App\Http\Controllers\Api\RouteAutomation\RouteAutomationController;
use App\Http\Controllers\Api\CronJobs\CronjobsController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::controller(AuthController::class)->group(function () {
    Route::post('login', 'login');
    Route::get('logout', 'logout');
    Route::any('offeracceptandDecline/{applicantId}/{acceptType}/{database}/{uniqueID}', [OfferacceptdeclineController::class, 'offeracceptandDecline']);
    Route::any('offeredRejected', [OfferacceptdeclineController::class, 'offeredRejected']);
    Route::any('handleLeaveAction/{employeeId}/{action}/{database}/{uniqueID}', [LeaveacceptdeclineController::class, 'handleLeaveAction']);
    Route::post('leaveRejected', [LeaveacceptdeclineController::class, 'leaveRejected']);
    // Resume upload
    Route::any('applicantResume/store/{id}', [ResumeuploadController::class, 'store']);
    //  Route::any('applicantResume/storePublic/{db}/{id}', [ResumeuploadController::class, 'storePublic']);


    Route::middleware('validate.token')->group(function () {
        Route::get('publicdata', [ResumeuploadController::class, 'publicData']);
        Route::post('new_applicant/pubDesignations/{id}', [NewApplicantController::class, 'pubDesignations']);
        Route::post('new_applicant/storePublicdate/{id}', [UserAnswerController::class, 'storePublicdate']);
    });
    // Token generate for public url access
    Route::get('generatePublicToken', [ResumeuploadController::class, 'generatePublicToken']);

    // New aplicant can submit form publically
    Route::post('new_applicant/newApplicantForm/{id}', [NewApplicantController::class, 'newApplicantForm']);
    // Route::post('new_applicant/pubDesignations/{id}', [NewApplicantController::class, 'pubDesignations']);
    Route::post('new_applicant/publicquiz/{id}', [UserAnswerController::class, 'publicquiz']);
    Route::post('new_applicant/storePublicdate/{id}', [UserAnswerController::class, 'storePublicdate']);

    //Resume upload publically
    Route::post('resume/createPublic/{db}', [ResumeuploadController::class, 'createPublic']);
    Route::post('resume/updatePublic/{db}/{id}', [ResumeuploadController::class, 'updatePublic']);
    Route::post('resume/showPublic/{db}/{id}', [ResumeuploadController::class, 'showPublic']);


    // ***** Send OTP For Forgot Pasword   ***** \\
    Route::post('sendOtpForgotPassword', [EmployeeController::class, 'sendOtpForgotPassword']);
    Route::post('varifyForgotPasswordOTP', [EmployeeController::class, 'varifyForgotPasswordOTP']);
    Route::post('change_password', [EmployeeController::class, 'changePassword']);
});


Route::controller(AuthController::class)->group(function () {
    Route::post('getMultipleSubUsers', 'getMultipleSubUsers');
});

Route::middleware(['admin:sanctum'])->group(function () {
    Route::post('changePassword', [HomeController::class, 'changePassword']);
    Route::get('home', [HomeController::class, 'home']);
    Route::get('dailyhome', [HomeController::class, 'dailyhome']);
    Route::any('scheduleDetails', [HomeController::class, 'scheduleDetails']);
    Route::any('absents', [HomeController::class, 'absents']);
    Route::get('profile', [HomeController::class, 'profile']);
    Route::any('startRide', [ScheduleController::class, 'startRide']);
    Route::any('pickStaff', [ScheduleController::class, 'pickStaff']);
    Route::any('dropStaff', [ScheduleController::class, 'dropStaff']);
    Route::any('schedules', [ScheduleController::class, 'schedulesAll']);
    Route::any('cancelRide', [ScheduleController::class, 'cancelRide']);
    Route::any('announcements', [HomeController::class, 'announcements']);
    Route::post('leave/apply', [LeaveController::class, 'applyForLeave']);
    Route::get('leavetypes', [LeaveController::class, 'leaveTypes']);
    Route::any('employeeDriverInfo', [LeaveController::class, 'employeeDriverInfo']);
    Route::get('company-profile', [HomeController::class, 'companyProfile']);
    Route::get('upcoming-holidays', [HolidayController::class, 'upcomingHolidays']);

    Route::any('shift-relocation-request', [ScheduleController::class, 'shiftRelocationRequest']);
    Route::any('shiftChange-requests', [ScheduleController::class, 'shiftChangeRequests']);
    Route::get('leave-requests', [LeaveController::class, 'previousLeaveRequest']);
    Route::post('store-driver-documents', [DriverController::class, 'uploadDocuments']);
    Route::get('show-documents', [DriverController::class, 'showDocuments']);
    Route::any('update-profile', [DriverController::class, 'updateProfile']);
    Route::post('reschedule', [RescheduleController::class, 'store']);
    Route::post('caprequest', [RescheduleController::class, 'caprequest']);
    Route::post('caprequestUpdate', [RescheduleController::class, 'caprequestUpdate']);
    Route::any('cablistRequest', [RescheduleController::class, 'cablistRequest']);
    Route::any('help', [HomeController::class, 'help']);
    Route::any('carer-grouping', [HomeController::class, 'findCarers']);
    Route::any('driver/schedules', [DriverController::class, 'schedules']);
    Route::any('driver/schedules', [DriverController::class, 'schedules']);
    Route::any('completeRide', [ScheduleController::class, 'completeRide']);
    Route::any('getScheduleById', [HomeController::class, 'getScheduleById']);
    Route::any('team', [ScheduleController::class, 'teams']);
    Route::any('resetRide', [ScheduleController::class, 'resetRide']);
    Route::any('driverInvoice', [DriverController::class, 'getTotalInvoiceForDriverInRange']);
    Route::any('getDriverMonthlyRideCount', [StaffController::class, 'getDriverMonthlyRideStats']);
    Route::any('singleDriverBilling', [StaffController::class, 'allDriverBilling']);
    Route::any('allDriverBilling', [DriverController::class, 'allDriverBilling']);
    Route::post('allDriversPayrollHistory', [DriverController::class, 'allDriversPayrollHistory']);
    Route::any('noShow', [ScheduleController::class, 'noShow']);
    Route::any('rate-ride', [ScheduleController::class, 'rideRating']);
    Route::any('store-complaints', [RescheduleController::class, 'storeComplaint']);
    Route::any('changePaymentStatus', [DriverController::class, 'changePaymentStatus']);
    Route::any('includeToInvoice', [DriverController::class, 'includeToInvoice']);
    Route::any('payToDrivers', [DriverController::class, 'payToDrivers']);
    Route::any('rejectDocument', [DriverController::class, 'rejectDocument']);
    Route::any('acceptDocument', [DriverController::class, 'acceptDocument']);
    Route::any('show-complaints', [RescheduleController::class, 'showComplaint']);
    Route::any('closeComplaint', [RescheduleController::class, 'closeComplaint']);
    Route::any('reason', [HomeController::class, 'reasons']);
    Route::any('employeeRideInfo', [ScheduleController::class, 'employeeRideInfo']);
    Route::any('employeeRideInfoDaily', [ScheduleController::class, 'employeeRideInfoDaily']);

    //AccountSetup api routes

    Route::any('updateCompany', [AccountSetupController::class, 'updateCompanyInfo']);
    Route::any('subscriptionPlan', [AccountSetupController::class, 'subscriptionPlans']);
    Route::any('upgradePlan', [AccountSetupController::class, 'upgradePlans']);
    Route::any('groupingPassword', [AccountSetupController::class, 'groupingPassword']);
    Route::any('updateCompanyLocation', [AccountSetupController::class, 'updateCompanyLoction']);
    Route::any('getReason', [AccountSetupController::class, 'reason']);
    Route::any('deleteReason', [AccountSetupController::class, 'deleteReason']);
    Route::any('addReason', [AccountSetupController::class, 'addReason']);
    Route::any('updateReason', [AccountSetupController::class, 'updateReason']);
    Route::any('updateFaq', [AccountSetupController::class, 'updateFaq']);
    Route::any('addFaq', [AccountSetupController::class, 'addFaq']);
    Route::any('deleteFaq', [AccountSetupController::class, 'deleteFaq']);
    Route::any('addHoliday', [AccountSetupController::class, 'addHoliday']);
    Route::any('listFaq', [AccountSetupController::class, 'listFaq']);
    Route::any('updateHoliday', [AccountSetupController::class, 'updateHoliday']);
    Route::any('addPrice', [AccountSetupController::class, 'addPrice']);
    Route::any('editPrice', [AccountSetupController::class, 'editPrice']);
    Route::any('deletePrice', [AccountSetupController::class, 'deletePrice']);
    Route::any('addPriceBook', [AccountSetupController::class, 'addPriceBook']);
    Route::any('updatePriceBook', [AccountSetupController::class, 'updatePriceBook']);
    Route::any('deletePriceBook', [AccountSetupController::class, 'deletePriceBook']);
    Route::any('listPriceAndPriceBookdata', [AccountSetupController::class, 'listPriceAndPriceBookdata']);
    Route::any('listDrivers', [AccountSetupController::class, 'listDriver']);
    Route::any('alldriversDelete', [AccountSetupController::class, 'alldriversDelete']);
    Route::any('allschedulesDelete', [AccountSetupController::class, 'allschedulesDelete']);
    Route::any('listStaffs', [AccountSetupController::class, 'listStaff']);
    Route::any('hrmslistStaffs', [AccountSetupController::class, 'hrmslistStaffs']);
    Route::any('downloadSampleExcelfile', [AccountSetupController::class, 'downloadSampleExcelfile']);
    Route::any('addDriver', [StaffController::class, 'addDriver']);
    Route::any('addStaff', [StaffController::class, 'addStaff']);
    Route::any('deleteDriver', [StaffController::class, 'deleteDriver']);
    Route::any('deleteStaff', [StaffController::class, 'deleteStaff']);
    Route::any('updateDriver', [StaffController::class, 'updateDriver']);
    Route::any('driverDetails', [StaffController::class, 'driverDetail']);
    Route::any('driverTripDetailsOfMonth', [StaffController::class, 'driverTripDetailsOfMonth']);
    Route::any('careTripDetailsOfMonth', [StaffController::class, 'careTripDetailsOfMonth']);
    Route::any('carebookingSummary', [StaffController::class, 'carebookingSummary']);

    Route::any('unshinedDriver', [StaffController::class, 'unshinedDriver']);
    Route::any('testDriver', [StaffController::class, 'testDriver']);
    Route::any('routeAutomation', [StaffController::class, 'routeAutomation']);
    Route::any('routeAutomationList', [StaffController::class, 'routeAutomationList']);
    Route::any('routeAutomationAddNewEmployee', [StaffController::class, 'routeAutomationAddNewEmployee']);
    Route::any('unsignDriverEmployee', [StaffController::class, 'unsignDriverEmployee']);
    Route::any('addEmployeeIngroup', [StaffController::class, 'addEmployeeIngroup']);
    Route::get('getDesignations', [StaffController::class, 'getDesignations']);


    Route::any('employeeDetails', [StaffController::class, 'employeeDetail']);
    Route::any('updateStaff', [StaffController::class, 'updateStaff']);
    Route::any('uploadDriverCsv', [UploadCsvController::class, 'driverCsv']);
    Route::any('scheduleTypes', [AccountSetupController::class, 'shiftTypes']);
    Route::any('documentType', [AccountSetupController::class, 'documentType']);
    Route::any('addDocumentType', [AccountSetupController::class, 'addDocumentType']);
    Route::any('deleteDocumentType', [AccountSetupController::class, 'deleteDocumentType']);
    Route::any('rideSetting', [AccountSetupController::class, 'rideSettings']);
    Route::any('addTemplate', [AccountSetupController::class, 'addTemplate']);
    Route::any('deleteTemplate', [AccountSetupController::class, 'deleteTemplate']);
    Route::any('updateTemplate', [AccountSetupController::class, 'updateTemplate']);
    Route::any('scheduleTemplate', [AccountSetupController::class, 'listScheduleTemplate']);
    Route::any('send/notification', [AccountSetupController::class, 'sendNotification']);
    Route::any('updateAnnouncement', [AccountSetupController::class, 'updateAnnouncement']);
    Route::any('deleteAnnouncement', [AccountSetupController::class, 'deleteAnnouncement']);
    Route::any('addAnnouncement', [AccountSetupController::class, 'addAnnouncement']);
    Route::any('accountInfo', [AccountSetupController::class, 'accountInfo']);
    Route::any('hrmsAccountInfo', [AccountSetupController::class, 'hrmsAccountInfo']);
    Route::any('scannerInfo', [AccountSetupController::class, 'scannerInfo']);
    Route::any('deleteHoliday', [AccountSetupController::class, 'deleteHoliday']);
    Route::any('timeandShift', [AccountSetupController::class, 'timeandShift']);
    Route::post('timeandShiftEdit/{id}', [AccountSetupController::class, 'timeandShiftEdit']);
    Route::any('timeandShiftlist', [AccountSetupController::class, 'timeandShiftlist']);
    Route::delete('timeandShiftdelete/{id}', [AccountSetupController::class, 'timeandShiftdelete']);
    Route::post('SystemSetupHistory', [AccountSetupController::class, 'SystemSetupHistory']);

    //Schedule routes
    Route::any('addSchedule', [ScheduleController::class, 'addSchedule']);
    Route::any('updateSchedule', [ScheduleController::class, 'updateSchedule']);
    Route::any('updatefutureSchedule', [ScheduleController::class, 'updatefutureSchedule']);
    Route::any('deleteparticularSchedule', [ScheduleController::class, 'deleteparticularSchedule']);
    Route::any('deletefutureSchedule', [ScheduleController::class, 'deletefutureSchedule']);
    Route::any('extendSchedule', [ScheduleController::class, 'extendSchedule']);
    Route::any('deleteSchedule', [ScheduleController::class, 'deleteSchedule']);
    Route::any('scheduleData', [ScheduleController::class, 'scheduleData']);
    Route::any('scheduleData', [ScheduleController::class, 'scheduleData']);
    Route::any('listAllSchedules', [ScheduleController::class, 'listAllSchedules']);
    Route::any('dailylistAllSchedules', [ScheduleController::class, 'dailylistAllSchedules']);
    Route::any('editScheduleData', [ScheduleController::class, 'editScheduleData']);
    Route::any('dailyeditScheduleData', [ScheduleController::class, 'dailyeditScheduleData']);
    Route::any('schedulePositionStatus', [ScheduleController::class, 'schedulePositionStatus']);
    Route::any('carerPositionchange', [ScheduleController::class, 'carerPositionchange']);
    Route::any('listusers', [ScheduleController::class, 'listusers']);
    Route::any('serach_driver', [ScheduleController::class, 'serachDriver']);
    Route::any('serach_employee', [ScheduleController::class, 'serachEmployee']);

    // add multiple schedules
    Route::any('addMultipleSchedule', [ScheduleController::class, 'addMultipleSchedule']);

    //DashBoard
    Route::any('listDashboardData', [DashboardController::class, 'listDashboardData']);
    Route::any('allEmployeeActivity', [ActivityController::class, 'allEmployeeActivity']);
    Route::any('allDriverActivity', [ActivityController::class, 'allDriverActivity']);
    Route::any('dashboardTempLocation', [DashboardController::class, 'dashboardTempLocation']);
    Route::any('dashboardReschedule', [DashboardController::class, 'dashboardReschedule']);
    Route::any('dashboardLeave', [DashboardController::class, 'dashboardLeave']);
    Route::any('dashBoardComplaints', [DashboardController::class, 'dashBoardComplaints']);
    Route::any('acceptLeaveRequest', [DashboardController::class, 'acceptLeaveRequest']);
    Route::any('rejectLeaveRequest', [DashboardController::class, 'rejectLeaveRequest']);
    Route::any('rejectTempRequest', [DashboardController::class, 'rejectTempRequest']);
    Route::any('acceptTempRequest', [DashboardController::class, 'acceptTempRequest']);
    Route::any('rejectReschedule', [DashboardController::class, 'rejectReschedule']);
    Route::any('similarRoutes', [DashboardController::class, 'similarRoutes']);
    Route::any('manageRoute', [ActivityController::class, 'manageRoute']);
    Route::any('acceptReschedule', [DashboardController::class, 'acceptReschedule']);
    Route::any('scheduleHistory', [DashboardController::class, 'scheduleHistory']);
    Route::any('dragAndDrop', [DashboardController::class, 'dragAndDrop']);
    Route::any('tempRoute', [DashboardController::class, 'tempRoute']);
    Route::any('archiveEmployee', [StaffController::class, 'archiveEmployee']);
    Route::any('unArchiveEmployee', [StaffController::class, 'unArchiveEmployee']);
    Route::any('listArchiveEmployees', [StaffController::class, 'listArchiveEmployees']);
    Route::any('listArchiveDrivers', [StaffController::class, 'listArchiveDrivers']);
    Route::any('archiveDriver', [StaffController::class, 'archiveDriver']);
    Route::any('unArchiveDriver', [StaffController::class, 'unArchiveDriver']);
    Route::any('sendMail', [StaffController::class, 'sendMail']);
    Route::any('dashboardAlertbreakdown', [DashboardController::class, 'dashboardAlertbreakdown']);
    Route::any('usersComplances', [DashboardController::class, 'usersComplances']);
    Route::any('billingSummarywithfilter', [DashboardController::class, 'billingSummarywithfilter']);
    Route::any('mapAnalysis', [DashboardController::class, 'mapAnalysis']);
    Route::any('driverTripsDetailsOfMinth', [DashboardController::class, 'driverTripsDetailsOfMinth']);
    Route::any('driverActivity', [DashboardController::class, 'driverActivity']);
    Route::any('employeeActivityList', [DashboardController::class, 'employeeActivityList']);
    Route::any('driverActivityList', [DashboardController::class, 'driverActivityList']);
    /**
     * this route is for store hiring templates
     */

    Route::group(['prefix' => 'hiringtemplate'], function () {
        Route::post('index', [HiringTemplateController::class, 'index']);
        Route::post('store', [HiringTemplateController::class, 'store']);
        Route::post('edit/{id}', [HiringTemplateController::class, 'edit']);
        Route::post('update/{id}', [HiringTemplateController::class, 'update']);
        Route::delete('destroy/{id}', [HiringTemplateController::class, 'destroy']);
        Route::get('template_list', [HiringTemplateController::class, 'templateList']);
        Route::get('templateVariableNameList', [HiringTemplateController::class, 'templateVariableNameList']);
    });


    /**
     * This route is for designation
     */
    Route::group(['prefix' => 'designation'], function () {
        Route::get('index', [DesignationController::class, 'index']);
        Route::post('store', [DesignationController::class, 'store']);
        Route::post('update/{id}', [DesignationController::class, 'update']);
        Route::get('edit/{id}', [DesignationController::class, 'edit']);
        Route::delete('delete/{id}', [DesignationController::class, 'delete']);
        Route::get('designationList', [DesignationController::class, 'designationList']);
    });

    /**
     * This route is for Quiz Module
     */
    Route::group(['prefix' => 'quiz'], function () {

        /**
         * This Block is use for question type listing
         */
        Route::group(['prefix' => 'question_type'], function () {
            Route::get('index', [QuestiontypeController::class, 'index']);
            Route::post('store', [QuestiontypeController::class, 'store']);
            Route::get('edit/{id}', [QuestiontypeController::class, 'edit']);
            Route::post('update/{id}', [QuestiontypeController::class, 'update']);
            Route::delete('destroy/{id}', [QuestiontypeController::class, 'destroy']);
            Route::get('listAllQuestionType', [QuestiontypeController::class, 'listAllQuestionType']);
        });

        /**
         * This Block is use for quiz level listing
         */
        Route::group(['prefix' => 'question_level'], function () {
            Route::get('index', [QuizLevelController::class, 'index']);
            Route::post('store', [QuizLevelController::class, 'store']);
            Route::get('edit/{id}', [QuizLevelController::class, 'edit']);
            Route::post('update/{id}', [QuizLevelController::class, 'update']);
            Route::delete('destroy/{id}', [QuizLevelController::class, 'destroy']);
            Route::get('allquizlevels', [QuizLevelController::class, 'allquizlevels']);
        });

        /**
         * This Block is use for create a new quiz
         */
        Route::group(['prefix' => 'hiring_quiz'], function () {
            Route::get('index', [HiringquizController::class, 'index']);
            Route::post('store', [HiringquizController::class, 'store']);
            Route::get('edit/{id}', [HiringquizController::class, 'edit']);
            Route::post('update/{id}', [HiringquizController::class, 'update']);
            Route::delete('destroy/{id}', [HiringquizController::class, 'destroy']);
            Route::delete('destroyQuizQuestion/{id}', [HiringquizController::class, 'destroyQuizQuestion']);
        });
    });
    /**
     * This route is for new applicant Module
     */
    Route::group(['prefix' => 'new_applicant'], function () {

        /**
         * This block is use for candidates listing
         */
        Route::group(['prefix' => 'candidates'], function () {
            Route::any('index', [NewApplicantController::class, 'index']);
            Route::post('store', [NewApplicantController::class, 'store']);
            Route::get('edit/{id}', [NewApplicantController::class, 'edit']);
            Route::any('update/{id}', [NewApplicantController::class, 'update']);
            Route::any('delete/{id}', [NewApplicantController::class, 'destroy']);
            Route::any('sendEmailToNewApplicant', [NewApplicantController::class, 'sendEmailToNewApplicant']);
            Route::post('makeParmanentEmployee', [NewApplicantController::class, 'makeParmanentEmployee']);
            Route::any('makeParmanentEmployeeList/{id}', [NewApplicantController::class, 'makeParmanentEmployeeList']);
            Route::post('makeParmanentUser', [NewApplicantController::class, 'makeParmanentUser']);
            Route::any('emailList', [NewApplicantController::class, 'applicantEmaillist']);
            Route::post('applicantScheduledInterviewList', [NewApplicantController::class, 'applicantScheduledInterviewList']);
            Route::post('applicantNextRound/{id}', [NewApplicantController::class, 'applicantNextRound']);
            Route::post('reschedule', [NewApplicantController::class, 'applicantReSchedule']);
            Route::get('scheduledDashboardList', [NewApplicantController::class, 'scheduledDashboardList']);
            Route::post('assignManager', [NewApplicantController::class, 'assignManager']);
            Route::get('referralApplicantsList', [NewApplicantController::class, 'referralApplicantsList']);
            Route::post('reOffered', [NewApplicantController::class, 'reOffered']);

            Route::post('updateHistoryList', [NewApplicantController::class, 'updateHistoryList']);

        });

        Route::group(['prefix' => 'quiz'], function () {
            Route::any('index', [UserAnswerController::class, 'index']);
            Route::post('store', [UserAnswerController::class, 'store']);
            Route::post('applicantAnswerQuiz', [UserAnswerController::class, 'applicantAnswerQuiz']);
        });

        /**
         * This block is use for job requirement
         */

        Route::group(['prefix' => 'job_requirement'], function () {
            Route::get('index', [JobRequirementController::class, 'index']);
            Route::post('store', [JobRequirementController::class, 'store']);
            Route::get('edit/{id}', [JobRequirementController::class, 'edit']);
            Route::any('update/{id}', [JobRequirementController::class, 'update']);
            Route::any('delete/{id}', [JobRequirementController::class, 'destroy']);
            Route::any('jobPostStatus/{id}', [JobRequirementController::class, 'jobPostStatus']);
            Route::get('joblistforDashboard', [JobRequirementController::class, 'joblistforDashboard']);
        });
    });

    Route::group(['prefix' => 'hrms_team'], function () {
        Route::get('index', [HrmsTeamController::class, 'index']);
        Route::post('store', [HrmsTeamController::class, 'store']);
        Route::get('edit/{id}', [HrmsTeamController::class, 'edit']);
        Route::post('update/{id}', [HrmsTeamController::class, 'update']);
        Route::delete('destroy/{id}', [HrmsTeamController::class, 'destroy']);
        Route::get('all_teams', [HrmsTeamController::class, 'all_teams']);
        Route::post('staffFilter', [HrmsTeamController::class, 'staffFilter']);
        Route::get('managerList', [HrmsTeamController::class, 'managerList']);
        Route::get('teamLeaderList', [HrmsTeamController::class, 'teamLeaderList']);
        Route::get('teamMemberList', [HrmsTeamController::class, 'teamMemberList']);
        Route::post('team_list_accourding_manager', [HrmsTeamController::class, 'teamListAccourdingManager']);
        Route::get('managerUnderEmployee', [HrmsTeamController::class, 'managerUnderEmployee']);
        Route::post('add_manager_member', [HrmsTeamController::class, 'addManagerMember']);


        /**
         * This Block is use for Team Manager
         */
        Route::group(['prefix' => 'team_manager'], function () {
            Route::get('index', [TeamManagerController::class, 'index']);
            Route::post('store', [TeamManagerController::class, 'store']);
            Route::get('edit/{id}', [TeamManagerController::class, 'edit']);
            Route::post('update/{id}', [TeamManagerController::class, 'update']);
            Route::delete('destroy/{id}', [TeamManagerController::class, 'destroy']);
        });
    });



    /**
     * This Block is use for reason list
     */
    Route::group(['prefix' => 'reason'], function () {
        Route::get('index', [HrmsReasonController::class, 'index']);
        Route::post('store', [HrmsReasonController::class, 'store']);
        Route::get('edit/{id}', [HrmsReasonController::class, 'edit']);
        Route::post('update/{id}', [HrmsReasonController::class, 'update']);
        Route::delete('destroy/{id}', [HrmsReasonController::class, 'destroy']);
        Route::delete('subCategoryDelete/{id}', [HrmsReasonController::class, 'subCategoryDelete']);
    });

    /**
     * This Block is use for Upload documents list
     */
    Route::group(['prefix' => 'documents'], function () {
        Route::get('index', [UploadDocumentsController::class, 'index']);
        Route::post('store', [UploadDocumentsController::class, 'store']);
        Route::get('edit/{id}', [UploadDocumentsController::class, 'edit']);
        Route::post('update/{id}', [UploadDocumentsController::class, 'update']);
        Route::delete('destroy/{id}', [UploadDocumentsController::class, 'destroy']);
        Route::delete('deleteDocumentCategory/{id}', [UploadDocumentsController::class, 'deleteDocumentCategory']);
    });

    /**
     * This Block is use for Reminders list
     */
    Route::group(['prefix' => 'reminders'], function () {
        Route::get('index', [ReminderController::class, 'index']);
        Route::post('store', [ReminderController::class, 'store']);
        Route::get('edit/{id}', [ReminderController::class, 'edit']);
        Route::post('update/{id}', [ReminderController::class, 'update']);
        Route::delete('destroy/{id}', [ReminderController::class, 'destroy']);
        Route::delete('deleteDocumentCategory/{id}', [ReminderController::class, 'deleteDocumentCategory']);
    });


    /**
     * This route is for upload employee documents
     */
    Route::group(['prefix' => 'employee_documents'], function () {
        Route::post('index', [EmployeeDocumentsController::class, 'index']);
        Route::post('store', [EmployeeDocumentsController::class, 'store']);
        Route::any('delete/{id}', [EmployeeDocumentsController::class, 'delete']);
    });

    Route::group(['prefix' => 'company_calender'], function () {
        Route::get('index', [CompanyCalenderController::class, 'index']);
        Route::post('store', [CompanyCalenderController::class, 'store']);
        Route::get('edit/{id}', [CompanyCalenderController::class, 'edit']);
        Route::post('update/{id}', [CompanyCalenderController::class, 'update']);
        Route::delete('destroy/{id}', [CompanyCalenderController::class, 'destroy']);
    });

    /**
     * This route is for employee attendance
     */
    Route::group(['prefix' => 'employee_attendace'], function () {
        Route::post('index', [EmployeeAttendanceController::class, 'index']);
        Route::post('store', [EmployeeAttendanceController::class, 'store']);
        Route::post('todayActivity', [EmployeeAttendanceController::class, 'todayActivity']);
        Route::post('attendancePerformance', [EmployeeAttendanceController::class, 'attendancePerformance']);
        Route::post('attendanceTimesheet', [EmployeeAttendanceController::class, 'attendanceTimesheet']);
        Route::post('trackingEmployeeAttendance', [EmployeeAttendanceController::class, 'trackingEmployeeAttendance']);
    });

    /**
     * This route is for employee attendance calender
     */
    Route::group(['prefix' => 'employee_attendace'], function () {
        Route::post('calender', [EmployeeCalenderAttendanceController::class, 'index']);
        Route::post('calender/update', [EmployeeCalenderAttendanceController::class, 'update']);
        Route::get('calender_update_list', [EmployeeCalenderAttendanceController::class, 'calenderUpdateList']);
    });

    /**
     * This route is for employee payrolls
     */
    Route::group(['prefix' => 'payrolls'], function () {
        Route::post('index', [HrmsPayrollController::class, 'index']);
        Route::post('approvedStatus', [HrmsPayrollController::class, 'approvedStatus']);
    });

    /**
     * This route is for Performances
     */

    Route::group(['prefix' => 'performances'], function () {
        Route::post('index', [PerformanceController::class, 'index']);
        Route::post('store', [PerformanceController::class, 'store']);
        Route::delete('destroy/{id}', [PerformanceController::class, 'destroy']);
        Route::get('edit/{id}', [PerformanceController::class, 'edit']);
        Route::post('update/{id}', [PerformanceController::class, 'update']);

        // Project

        Route::post('projectCreate', [PerformanceController::class, 'projectCreate']);
        Route::delete('projectDestroy/{id}', [PerformanceController::class, 'projectDestroy']);
        Route::get('editProject/{id}', [PerformanceController::class, 'editProject']);
        Route::post('projectUpdate/{id}', [PerformanceController::class, 'projectUpdate']);
        Route::get('projectList', [PerformanceController::class, 'projectList']);
        Route::any('projectCalanderList', [PerformanceController::class, 'projectCalanderList']);
        Route::any('teamProjects/{id}', [PerformanceController::class, 'teamProjects']);
    });


    /**
     * This route is for Timesheet
     */

    Route::group(['prefix' => 'timesheet'], function () {

        Route::get('index', [TimesheetController::class, 'index']);
        Route::post('timesheet', [TimesheetController::class, 'timesheet']);
        Route::post('authTimesheet', [TimesheetController::class, 'authTimesheet']);
        Route::post('timeSheetReport', [TimesheetController::class, 'timeSheetReport']);
    });

    /**
     * This route is for employee roles  permissions
     */
    Route::group(['prefix' => 'roles_permission'], function () {
        Route::post('index', [RoleandpermissionController::class, 'index']);
        Route::post('update', [RoleandpermissionController::class, 'update']);
        Route::post('addNewRole', [RoleandpermissionController::class, 'addNewRole']);
        Route::post('roleUpdate/{id}', [RoleandpermissionController::class, 'roleUpdate']);
        Route::delete('destroy/{id}', [RoleandpermissionController::class, 'destroy']);
        Route::get('authRolepermissions', [RoleandpermissionController::class, 'authRolepermissions']);
        Route::get('mainNewRoles', [RoleandpermissionController::class, 'mainNewRoles']);
        // Route::post('updateNewRoles', [RoleandpermissionController::class, 'updateNewRoles']);
        //  Route::get('getRoleandPermissions', [RoleandpermissionController::class, 'getRoleandPermissions']);
    });


    /**
     * This route is for reports settings
     */
    Route::group(['prefix' => 'reports'], function () {
        Route::post('index', [ReportSettingController::class, 'index']);
        Route::post('store', [ReportSettingController::class, 'store']);
        Route::post('create', [ReportSettingController::class, 'create']);
        Route::get('edit/{id}', [ReportSettingController::class, 'edit']);
        Route::post('update/{id}', [ReportSettingController::class, 'update']);
        Route::get('titleEdit/{id}', [ReportSettingController::class, 'titleEdit']);
        Route::post('titleUpdate/{id}', [ReportSettingController::class, 'titleUpdate']);
        Route::delete('titleDestroy/{id}', [ReportSettingController::class, 'titleDestroy']);
        Route::delete('destroy/{id}', [ReportSettingController::class, 'destroy']);
    });

    /**
     * This route is for reports settings
     */
    Route::group(['prefix' => 'announcement'], function () {
        Route::get('index', [AnnouncementController::class, 'index']);
        Route::post('store', [AnnouncementController::class, 'store']);
        Route::delete('destroy/{id}', [AnnouncementController::class, 'destroy']);
    });


    /**
     * This route is for reports settings
     */

    Route::group(['prefix' => 'employee'], function () {
        Route::get('index', [EmployeeController::class, 'index'])->middleware('role.permission:Employee.view');
        Route::post('update/{id}', [EmployeeController::class, 'update']);

        Route::get('getRoles', [EmployeeController::class, 'getRoles']);
        Route::post('leaves', [EmployeeController::class, 'employeeLeaves']);
        Route::get('employeeList', [EmployeeController::class, 'employeeList']);
        Route::post('updateStatus', [EmployeeController::class, 'updateStatus']);
        Route::post('user_info_update', [EmployeeController::class, 'userInfoUpdate']);
        Route::get('get_profile_data', [EmployeeController::class, 'getProfileData']);
        Route::post('update_profile', [EmployeeController::class, 'updateProfile']);
        Route::post('updateOfficeStatus', [EmployeeController::class, 'updateOfficeStatus']);
        Route::post('generateReferralCode', [EmployeeController::class, 'generateReferralCode']);
        Route::post('cabFacility', [EmployeeController::class, 'cabFacility']);
        Route::get('sendBirthDayEmail', [EmployeeController::class, 'sendBirthDayEmail']);
        Route::post('EmployeeHistoryList', [EmployeeController::class, 'EmployeeHistoryList']);
        Route::post('newEmployeeAssignPC', [EmployeeController::class, 'newEmployeeAssignPC']);
        Route::post('uploadedEmployeelatLong', [EmployeeController::class, 'uploadedEmployeelatLong']);
        Route::post('updateDriverStatus', [EmployeeController::class, 'updateDriverStatus']);

    });

    /**
     * This route is for resume upload
     */
    Route::group(['prefix' => 'resume'], function () {
        Route::post('create', [ResumeuploadController::class, 'create']);
        Route::post('update/{id}', [ResumeuploadController::class, 'update']);
        Route::post('show/{id}', [ResumeuploadController::class, 'show']);
        // Route::post('store/{id}', [ResumeuploadController::class, 'store']);

    });


    /**
     * This route is for Dashboard
     */

    Route::group(['prefix' => 'dashboard'], function () {
        Route::post('hiringStatusForGraph', [HrmsDashboardController::class, 'hiringStatusForGraph']);
    });


    /**
     * This route is for Employee Salary Slabs
     */

    Route::group(['prefix' => 'salary_slab'], function () {
        Route::post('store', [SalaySlabController::class, 'store']);
        Route::get('index', [SalaySlabController::class, 'index']);
    });

    /**
     * This route is for Employee Salary Calculation
     */

    Route::group(['prefix' => 'salary_calculation'], function () {
        Route::post('calculate_salary', [EployeeSalaryCalculation::class, 'calculate']);
        Route::post('fetchUser_salary', [EployeeSalaryCalculation::class, 'fetchUserSalary']);
        Route::post('add_user_increment', [EployeeSalaryCalculation::class, 'addUserIncrement']);
        Route::post('increment_salaries_list', [EployeeSalaryCalculation::class, 'incrementSalariesList']);
        Route::POST('get_salaryslip_data', [EployeeSalaryCalculation::class, 'getSalarySlip']);
        Route::post('payroll_summary', [EployeeSalaryCalculation::class, 'payrollSummary']);
        Route::post('increment_salaries_history_Breakdowns', [EployeeSalaryCalculation::class, 'incrementSalariesHistoryWithBreakdowns']);
        Route::post('payroll_history_chart', [EployeeSalaryCalculation::class, 'payrollHistoryChart']);
        Route::post('salary_slip_years', [EployeeSalaryCalculation::class, 'salarySlipYears']);
        Route::any('excecute_salary_saving_process', [EployeeSalaryCalculation::class, 'getFromImportEmployeeSalaryFromExcelsStoreThenDelete']);
        Route::post('download-salary-slip', [EployeeSalaryCalculation::class, 'downloadSalarySlipPdf']);
        Route::post('delete-salary-record', [EployeeSalaryCalculation::class, 'deleteSalaryRecord']);
        Route::post('getsalary-slip-for-month', [EployeeSalaryCalculation::class, 'getSalarySlipForMonth']);
        Route::post('download_salary_slip_pdf_year', [EployeeSalaryCalculation::class, 'downloadSalarySlipPdfYear']);
        Route::post('ongoing_payrun_summary', [EployeeSalaryCalculation::class, 'OngoingPayrunSummary']);

    });

     Route::group(['prefix' => 'Manager_salary_calculation'], function () {
        Route::any('get_all_mangers_with_employee', [EployeeSalaryCalculation::class, 'getAllMangersWithEmployee']);
        Route::any('get_comapny_payroll_history_chart', [EployeeSalaryCalculation::class, 'getComapnyPayrollHistoryChart']);
        Route::post('pay_run_history_so_far', [EployeeSalaryCalculation::class, 'payrunHistorySoFar']);
         Route::post('employee_of_a_manager_and_there_cost', [EployeeSalaryCalculation::class, 'employeeOfAManagerAndThereCost']);

    });




    /**
     * This route is for Salary Setting
     */

    Route::group(['prefix' => 'salary_setting'], function () {

        /**
         * This route is for Tax Information
         */
        Route::group(['prefix' => 'tax_infomation'], function () {
            Route::post('store', [TaxInformationController::class, 'store']);
            Route::get('index', [TaxInformationController::class, 'index']);
        });

        /**
         * This route is for PF Setting
         */
        Route::group(['prefix' => 'pf_setting'], function () {
            Route::post('store', [PFAndLeaveSettingController::class, 'store']);
            Route::get('index', [PFAndLeaveSettingController::class, 'index']);
            Route::post('PayrollScheduleSetting', [PFAndLeaveSettingController::class, 'PayrollScheduleSetting']);
            Route::get('getPayrollScheduleSetting', [PFAndLeaveSettingController::class, 'getPayrollScheduleSetting']);
            Route::get('leavetpye', [PFAndLeaveSettingController::class, 'leavetpye']);
        });


        /**
         * This route is for TDS Setting
         */
        Route::group(['prefix' => 'tds_setting'], function () {
            Route::post('store', [TDSSettingController::class, 'store']);
            Route::get('index', [TDSSettingController::class, 'index']);
            Route::get('edit/{id}', [TDSSettingController::class, 'edit']);
            Route::post('update/{id}', [TDSSettingController::class, 'update']);
            Route::delete('destroy/{id}', [TDSSettingController::class, 'destroy']);
        });

        /**
         * This route is for Salary Setting
         */

         Route::group(['prefix' => 'salary_setting'], function () {
            Route::post('store', [SalarySettingController::class, 'store']);
            Route::get('show', [SalarySettingController::class, 'show']);
        });
    });


    /**
     * This route is for Employee Dashboard
     */

    Route::group(['prefix' => 'employee_dashboard'], function () {
        Route::post('today_attendance', [HrmsDashboardController::class, 'todayAttendance']);
        Route::get('authEmployeeDetail', [StaffController::class, 'authEmployeeDetail']);
        Route::get('authleavetype', [HrmsDashboardController::class, 'authleavetype']);
        Route::post('authTodayAttendance', [HrmsDashboardController::class, 'authTodayAttendance']);
        Route::get('authRequest', [HrmsDashboardController::class, 'authRequest']);
    });


    /**
     * This route is for Admin Dashboard
     */

    Route::group(['prefix' => 'admin_dashboard'], function () {
        Route::get('attendanceStatus', [AdminDashboardController::class, 'attendanceStatus']);
        Route::get('team_leaves', [AdminDashboardController::class, 'teamLeaves']);
        Route::get('event_list', [AdminDashboardController::class, 'eventList']);
        Route::get('all_attendance', [AdminDashboardController::class, 'allAttendance']);
        Route::get('all_leave_requests', [AdminDashboardController::class, 'allLeaveRequests']);
        Route::get('job_opening_status', [AdminDashboardController::class, 'jobOpeningStatus']);
        Route::get('today_present_employees', [AdminDashboardController::class, 'todayPresentEmployees']);
        Route::get('today_absent_employees', [AdminDashboardController::class, 'todayAbsentEmployees']);
        Route::get('today_onLeave_employees', [AdminDashboardController::class, 'todayOnLeaveEmployees']);
        Route::get('authDetails', [AdminDashboardController::class, 'authDetails']);
        Route::get('idCardReceivedStats', [AdminDashboardController::class, 'idCardReceivedStats']);
        Route::post('idCardIndex', [AdminDashboardController::class, 'idCardIndex']);
    });


    Route::group(['prefix' => 'routeautomation'], function () {
        Route::get('dailyAutomation', [RouteAutomationController::class, 'dailyAutomation']);
        Route::get('dailyAutomationDelete', [RouteAutomationController::class, 'dailyAutomationDelete']);
        Route::post('dailyUnshinedEmployee', [RouteAutomationController::class, 'dailyUnshinedEmployee']);
        Route::post('dailyUnshinedEmployeedateWise', [RouteAutomationController::class, 'dailyUnshinedEmployeedateWise']);
        Route::get('dailyAutomationCluster/{db}/{shifttype?}/{employeeshift?}', [RouteAutomationController::class, 'dailyAutomationCluster']);
        Route::post('dailyAutomationClusterschedule', [RouteAutomationController::class, 'dailyAutomationClusterschedule']);
        Route::post('dailyschedulewithsingledatabase', [RouteAutomationController::class, 'dailyschedulewithsingledatabase']);
        Route::post('dailyschedulewithsingledatabasepickandDrop', [RouteAutomationController::class, 'dailyschedulewithsingledatabasepickandDrop']);
    });


    Route::group(['prefix' => 'cronjobs'], function () {
        Route::post('accountCloseDeactive', [CronjobsController::class, 'accountCloseDeactive']);
    });


    /**
     * This route is for Projects
     */
    Route::group(['prefix' => 'project'], function () {
        Route::post('store', [ProjectController::class, 'store']);
        Route::get('index', [ProjectController::class, 'index']);
        Route::get('edit/{id}', [ProjectController::class, 'edit']);
        Route::delete('delete/{id}', [ProjectController::class, 'destroy']);
        Route::post('update/{id}', [ProjectController::class, 'update']);
    });


    /**
     * This route is for Sub Projects
     */
    Route::group(['prefix' => 'sub_project'], function () {
        Route::post('store', [SubProjectController::class, 'store']);
        Route::post('index', [SubProjectController::class, 'index']);
        Route::get('edit/{id}', [SubProjectController::class, 'edit']);
        Route::post('update/{id}', [SubProjectController::class, 'update']);
        Route::delete('delete/{id}', [SubProjectController::class, 'destroy']);
    });

    /**
     * This route is for Min Projects
     */
    Route::group(['prefix' => 'min_project'], function () {
        Route::post('store', [MinProjectController::class, 'store']);
        Route::get('edit/{id}', [MinProjectController::class, 'edit']);
        Route::post('update/{id}', [MinProjectController::class, 'update']);
        Route::delete('delete/{id}', [MinProjectController::class, 'destroy']);
    });

    /**
     * This route is for Projects Task
     */
    Route::group(['prefix' => 'project_task'], function () {
        Route::post('store', [ProjectTaskController::class, 'store']);
        Route::post('index', [ProjectTaskController::class, 'index']);
        Route::delete('delete/{id}', [ProjectTaskController::class, 'destroy']);
        Route::get('edit/{id}', [ProjectTaskController::class, 'edit']);
        Route::post('update/{id}', [ProjectTaskController::class, 'update']);
        Route::post('update_status', [ProjectTaskController::class, 'updateStatus']);
        Route::post('assignee_employee_list', [ProjectTaskController::class, 'assigneeEmployeesList']);
        Route::post('employeeProjectCount', [ProjectTaskController::class, 'employeeProjectCount']);
        Route::get('adminDashboardProjectCount', [ProjectTaskController::class, 'adminDashboardProjectCount']);
    });

    /**
     * This route is for Basic Setting in Account Setup
     */
    Route::group(['prefix' => 'basic_setting'], function () {
        Route::post('store', [BasicSettingController::class, 'store']);
        Route::get('index', [BasicSettingController::class, 'index']);
    });


    // employee Actions (resignation)
    Route::group(['prefix' => 'employee'], function () {
        Route::post('resignation', [EmployeeActionsController::class, 'resignation']);
        Route::get('listResignations', [EmployeeActionsController::class, 'listResignations']);
        Route::post('handleResignation', [EmployeeActionsController::class, 'handleResignation']);
    });

    //*** Employee Daily Work Report *****/
    Route::group(['prefix' => 'report'], function () {
        Route::post('store', [DailyWorkReportController::class, 'store']);
        Route::get('index', [DailyWorkReportController::class, 'index']);
    });


    Route::post('validateUsersExcel', [AddEmployeeFromExcel::class, 'validateUsersExcel']);
    Route::post('addUsersFromExcel', [AddEmployeeFromExcel::class, 'addUsersFromExcel']);
    Route::post('addUsersFromExcelNewMethod', [AddEmployeeFromExcel::class, 'addUsersFromExcelNewMethod']);
    Route::post('addDriversFromExcel', [AddEmployeeFromExcel::class, 'addDriversFromExcel']);
    Route::post('getUploadExcelResponse', [AddEmployeeFromExcel::class, 'getUploadExcelResponse']);
    Route::post('getWrongAddressNotification', [AddEmployeeFromExcel::class, 'getWrongAddressNotification']);





});
    Route::post('checkReferralCodes', [NewApplicantController::class, 'checkReferralCode']);


Route::get('/test', function () {
    dd('hello');
});

