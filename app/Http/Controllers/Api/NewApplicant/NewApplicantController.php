<?php

namespace App\Http\Controllers\Api\NewApplicant;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\NewApplicant;
use App\Models\HiringQuiz;
use App\Models\SubUser;
use App\Models\HiringTemplate;
use App\Models\QuizQuestionDetail;
use App\Models\Role;
use App\Models\User;
use App\Models\SubUserAddresse;
use App\Models\NewApplicantCabAddress;
use App\Models\HrmsEmployeeEmail;
use App\Mail\NewApplicantNotification;
use App\Mail\HiringTemplateNotification;
use App\Http\Requests\NewApplicantRequest;
use App\Http\Requests\UpdateApplicantRequest;
use App\Http\Resources\NewApplicant\CandidatesCollection;
use App\Http\Resources\NewApplicant\CandidatesResource;
use App\Http\Requests\SendEmailApplicantRequest;
use App\Models\HrmsApplicantReminder;
use App\Models\HrmsResumeUpload;
use App\Models\QuizLevel;
use App\Models\Designation;
use App\Mail\SendMailToUser;
use App\Models\EmployeeTeamManager;
use App\Models\TeamManager;
use App\Models\HrmsRole;
use App\Models\HrmsTeam;
use App\Models\HrmsTimeAndShift;
use App\Models\HrmsTeamMember;
use App\Models\UserInfo;
use App\Models\HrmsEmployeeRole;
use App\Models\UpdateApplicantHistory;
use App\Models\ApplicantOfferedHistory;
use App\Models\EmployeeSalary;
use Mail;
use Str;
use Hash;
use Carbon\Carbon;
use App\Services\PdfWatermarkService;
use DateTime;
use Exception;
use Illuminate\Support\Facades\File;
use App\Http\Requests\MakePermanentEmployeeRequest;

use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use App\Jobs\SendEmailForNextRoundJob;
class NewApplicantController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */


    protected $databaseSelect = [
        '1' => 'UC_hrms',
        '2' => 'UC_shivam_uc',
        '3' => 'UC_unifysmartsolutions'
    ];

    // protected $databaseSelect =  ['1'=>'uc_sdna','2'=>'uc_new','3'=>'student'];


    /**
     * @OA\post(
     * path="/uc/api/new_applicant/candidates/index",
     * operationId="getCandidates",
     * tags={"New Applicant Templates"},
     * summary="Get Candidates Request",
     *   security={ {"Bearer": {} }},
     * description="Get Candidates Request",
     *     @OA\RequestBody(
     *         @OA\JsonContent(),
     *         @OA\MediaType(
     *            mediaType="multipart/form-data",
     *            @OA\Schema(
     *               type="object",
     *              @OA\Property(property="search", type="string"),
     *              @OA\Property(property="filter_name", type="string"),
     *              @OA\Property(property="filter_applied_date", type="string"),
     *              @OA\Property(property="position", type="string"),
     *              @OA\Property(property="stages", type="integer"),
     *              @OA\Property(property="is_rejected", type="integer"),
     *              @OA\Property(property="in_progress", type="integer"),
     *              @OA\Property(property="is_feature_reference", type="integer"),
     *              @OA\Property(property="is_offered", type="integer", description="1 for in offerd, 0 => out offered"),
     *              @OA\Property(property="manager_id", type="string"),
     *            ),
     *        ),
     *    ),
     *      @OA\Response(
     *          response=201,
     *          description="Candidates Get Successfully",
     *          @OA\JsonContent()
     *       ),
     *      @OA\Response(
     *          response=200,
     *          description="Candidates Get Successfully",
     *          @OA\JsonContent()
     *       ),
     *      @OA\Response(
     *          response=422,
     *          description="Unprocessable Entity",
     *          @OA\JsonContent()
     *       ),
     *      @OA\Response(response=400, description="Bad request"),
     *      @OA\Response(response=404, description="Resource Not Found"),
     * )
     */

    public function index(Request $request)
    {
        try {

            $search = $request->search ?? null;
            $filter_name = $request->filter_name ?? null;
            $managerid = $request->manager_id ?? null;
            $filter_position = $request->position ?? null;
            $filter_applied_date = $request->filter_applied_date ? Carbon::parse($request->filter_applied_date)->format('Y-m-d') : null;
            $stage = $request->stages ?? null;
            $in_progress = $request->in_progress ?? null;
            $isRejected = $request->is_rejected ?? null;
            $isReferece = $request->is_feature_reference ?? null;
            $isOfferd = $request->is_offered ?? null;

            $user_id = auth('sanctum')->user()->id;
            $admin = 0;
            $is_manager = 0;

             // if view role admin view
            $getTeamMembers = SubUser::with('hrmsroles.viewrole')->find($user_id);
            if ($getTeamMembers) {
                 $role_view = $getTeamMembers->hrmsroles[0]->viewrole->name;
                if ($role_view == 'Admin View') {
                    $admin = 1;
                }elseif ($role_view == 'Manager View') {   // manager view
                    $is_manager = 1;
                    $manager_id = $user_id;
                    $team_manager = EmployeeTeamManager::where('employee_id', $user_id)->first();
                }
            }


             ///  if Main Admin

            $auth_role = DB::table('role_user')->where('user_id',$user_id)->first();
            $employeeRole = DB::table('roles')->find($auth_role->role_id);
            if ($employeeRole->name == "admin") {
                 $admin = 1;
            }

            if ($admin == 0) {
                //  if ($is_manager == 1 ) {
                //      $team_manager = EmployeeTeamManager::where('employee_id', $user_id)->first();
                //      if ($team_manager) {
                //          $team_manager_id = $team_manager->team_manager_id;
                //      }
                //  }else {
                     $is_manager = 1;
                     $getManagerList = TeamManager::with(['employees','teams.teamLeader', 'teams.teamMembers.user'])->get();
                      //$team_manager_id = null;
                     foreach ($getManagerList as $key => $manager) {

                         foreach ($manager->teams as $key => $team) {

                             if (isset($team->team_leader) == $user_id) {
                                //  $team_manager_id =  $manager->id;
                                //  $team__id =   $team->id;

                                $manager_id = $manager->employees[0]->id;
                                 break 2;
                             }else {

                                 foreach ($team->teamMembers as $key => $member) {

                                       if ($member->user->id == $user_id) {
                                            //  $team_manager_id =  $manager->id;
                                            //  $team__id =   $team->id;
                                            $manager_id = $manager->employees[0]->id;
                                             break 2;
                                       }
                                 }
                             }


                         }
                     }
               //  }
                $team_manager = EmployeeTeamManager::where('employee_id', $user_id)->first();

            }
            if ($admin == 1) {
                $query = NewApplicant::with(['Designation','manager','OfferedHistory'])->where('is_employee', 0)->where('quiz_status',1);
            }elseif ($is_manager == 1) {
                $query = NewApplicant::with(['Designation','manager','OfferedHistory'])->where('is_employee', 0)->where('manager_id',$team_manager->team_manager_id)->where('quiz_status',1);
            }

            //////////////////////////////////////////////////////////////////////////////
           // $query = NewApplicant::with(['Designation','manager','OfferedHistory'])->where('is_employee', 0)->where('quiz_status',1);

           // $query = NewApplicant::with(['Designation','manager:id,first_name,last_name'])->where('is_employee', 0)->where('quiz_status',1);

            if (!is_null($isRejected)) {
                $query->where('is_rejected', $isRejected);
            }
            if (!is_null($isReferece)) {
                $query->where('is_feature_reference', $isReferece)->where('is_rejected', 0);
            }
            if (!is_null($stage)) {
                $query->where('stages', $stage)->where('is_rejected', 0)->where('is_feature_reference', 0)->whereNot('stages', 4);
            }
            // if (!is_null($isOfferd)) {
            //     $query->where(function ($q) {
            //          $q->where('stages', 4);
            //     })
            //     ->orWhere(function ($q) use ($isOfferd){
            //         $q->where('is_offered', $isOfferd);
            //     })->where('is_rejected', 0)->where('is_feature_reference', 0)->where('is_employee', 0);
            // }
            if (!is_null($in_progress)) {
                $query->whereNotIn('stages', [0,4])->where('is_rejected', 0)->where('is_feature_reference', 0)->where('is_offered', 0);
            }
            if (!is_null($search)) {
                $query->where(function ($q) use ($search) {
                    $q->where('first_name', 'like', '%' . $search . '%')
                      ->orWhere('last_name', 'like', '%' . $search . '%');
                });
            }
            if (!is_null($filter_name)) {
                $query->where('first_name', $filter_name);
            }
            if (!is_null($filter_applied_date)) {
                $query->whereDate('created_at', $filter_applied_date);
            }

            if (!is_null($filter_position)) {
                 $query->whereHas('Designation', function ($q) use ($filter_position){
                       $q->where('title', 'like', '%'.$filter_position.'%');
                 });
            }

            // if (!is_null($filter_name)) {
            //     $query->where('first_name', $filter_name);
            // }

            // if (!is_null($managerid)) {
            //     $query->where('manager_id', $managerid)
            //     ->where('manager_id', '!=', null)
            //     ->whereNotNull('manager_id');
            // }
            if (!is_null($managerid)) {
                $query->where(function ($q) use ($managerid, $isOfferd) {
                    $q->where('manager_id', $managerid)
                    ->whereNotNull('manager_id')
                    ->where('manager_id', '!=', null);

                    if (!is_null($isOfferd)) {
                        $q->where(function ($subQ) use ($isOfferd) {
                            $subQ->where('stages', 4)
                                ->orWhere('is_offered', $isOfferd);
                        })
                        ->where('is_rejected', 0)
                        ->where('is_feature_reference', 0)
                        ->where('is_employee', 0);
                    }
                });
            } else {
                if (!is_null($isOfferd)) {
                    $query->where(function ($q) use ($isOfferd) {
                        $q->where('stages', 4)
                        ->orWhere('is_offered', $isOfferd);
                    })
                    ->where('is_rejected', 0)
                    ->where('is_feature_reference', 0)
                    ->where('is_employee', 0);
                }
            }


            $perPage = NewApplicant::PAGINATE;
            $applicantCandidates = $query->orderBy('updated_at', 'desc')->paginate($perPage);

            // Convert paginated items into an array and add question count & correct answers count
            $modifiedCandidates = collect($applicantCandidates->items())->map(function ($candidate) {
                // Fetch the quiz ID for the candidate
                $quizId = DB::table('user_answer_detail')
                    ->where('new_applicant_id', $candidate->id)
                    ->value('quiz_id');

                // Count the total number of questions in the quiz
                $questionCount = 0;
                if ($quizId) {
                    $questionCount = DB::table('quiz_question_details')
                        ->where('hiring_quiz_id', $quizId)
                        ->count();
                }

                // Count the total correct answers for the candidate
                $correctAnswerCount = DB::table('user_answer_detail')
                    ->where('new_applicant_id', $candidate->id)
                    ->where('is_answer_correct', 1)
                    ->count();

                // Add data dynamically
                $candidate->question_count = $questionCount;
                $candidate->correct_answers = $correctAnswerCount;
                $candidate->interview_mode = $candidate->interview_mode;
                $candidate->sent_mail = $candidate->sent_mail ?? 0;
                $candidate->referral_code = $candidate->referral_code ?? null;

                return $candidate;
            });

            // Reconstruct the paginated response with updated candidates
            $updatedPagination = new LengthAwarePaginator(
                $modifiedCandidates,
                $applicantCandidates->total(),
                $perPage,
                $applicantCandidates->currentPage(),
                ['path' => $request->url(), 'query' => $request->query()]
            );

            // Return the response
            return $this->successResponse(
                new CandidatesCollection($updatedPagination),
                "Candidates List"
            );

        } catch (\Throwable $th) {
            return $this->errorResponse($th->getMessage());
        }
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }


    /**
     * @OA\post(
     * path="/uc/api/new_applicant/pubDesignations/{id}",
     * operationId="pubDesignations",
     * tags={"New Applicant Templates"},
     * summary="Get pubDesignations Request",
     *   security={ {"Bearer": {} }},
     * description="Get pubDesignations Request",
     *     @OA\Parameter(
     *     name="id",
     *     in="path",
     *     required=true,
     *     @OA\Schema(type="string")
     *    ),
     *      @OA\Response(
     *          response=201,
     *          description="Candidates Get Successfully",
     *          @OA\JsonContent()
     *       ),
     *      @OA\Response(
     *          response=200,
     *          description="Candidates Get Successfully",
     *          @OA\JsonContent()
     *       ),
     *      @OA\Response(
     *          response=422,
     *          description="Unprocessable Entity",
     *          @OA\JsonContent()
     *       ),
     *      @OA\Response(response=400, description="Bad request"),
     *      @OA\Response(response=404, description="Resource Not Found"),
     * )
     */

    public function pubDesignations(Request $request, $id){

        try {

            //$token = $request->query('token');

            // $token = request()->get('token');
            // if(empty($token)){
            //     return response()->json(['message' => 'Invalid database ID'], 400);
            // }
            // $databaseName = explode('@@', $token);
            // $decodedDatabase = base64_decode($databaseName[0]);
            // $this->connectDB($databaseName);

            $token = request()->get('token');

            if (empty($token)) {
                return response()->json(['message' => 'Invalid database ID'], 400);
            }

            $databaseName = explode('@@', $token);

            if (!isset($databaseName[0])) {
                return response()->json(['message' => 'Invalid token format'], 400);
            }

            $decodedDatabase = base64_decode($databaseName[0]);

            if ($decodedDatabase === false) {
                return response()->json(['message' => 'Invalid base64 encoding'], 400);
            }

            // Now, pass the decoded string instead of the array
            $this->connectDB($decodedDatabase);

           //return DB::connection()->getDatabaseName();

           $data =[];

           $data['designation'] = $designation = Designation::get();
           $data['role_level'] = $QuizLevel = QuizLevel::get();

            if($designation){
                    return $this->successResponse($data, 'Designation list found!');
            }else{
                return $this->errorResponse('Designation not found');
            }

        } catch (\Throwable $th) {
            return $this->errorResponse($th->getMessage());
        }
    }

     /**
     * @OA\Post(
     *     path="/uc/api/new_applicant/newApplicantForm/{id}",
     *     operationId="newApplicantForm",
     *     tags={"New Applicant Templates"},
     *     summary="Submit new applicant data public",
     *     security={{"Bearer": {}}},
     *     description="Endpoint to process new applicant data publically",
     *     @OA\Parameter(
     *     name="id",
     *     in="path",
     *     required=true,
     *     @OA\Schema(type="string")
     *    ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 type="object",
     *                 @OA\Property(property="first_name", type="string", description="First name of the person"),
     *                 @OA\Property(property="last_name", type="string", description="Last name of the person"),
     *                 @OA\Property(property="email", type="string", description="Email address"),
     *                 @OA\Property(property="phone", type="string", description="Phone number"),
     *                 @OA\Property(property="gender", type="string", description="Gender"),
     *                 @OA\Property(property="dob", type="string", format="date", description="Date of birth"),
     *                 @OA\Property(property="designation_id", type="integer", description="Position applied for"),
     *                 @OA\Property(property="role", type="integer", description="Roles associated with the applicant"),
     *                 @OA\Property(property="session_id", type="string", description="Session Id"),
     *                 @OA\Property(property="linkedin_url", type="string", format="url", description="LinkedIn profile URL"),
     *                 @OA\Property(property="upload_resume", type="string", format="binary", description="Upload resume"),
     *                 @OA\Property(property="cover_letter", type="string", description="Cover letter"),
     *                 @OA\Property(property="skills", type="string", description="Skills"),
     *                 @OA\Property(property="experience", type="string", description="Experience details"),
     *                 @OA\Property(property="typing_speed", type="integer", description="Typing speed"),
     *                 @OA\Property(property="notice_period", type="string", description="Notice period"),
     *                 @OA\Property(property="expected_date_of_join", type="string", format="date", description="Expected date of joining"),
     *                 @OA\Property(property="nightshift", type="integer", description="Nightshift preference (1 for yes, 0 for no)"),
     *                 @OA\Property(property="is_notice", type="integer", description="IsNotice preference (1 for yes, 0 for no)"),
     *                 @OA\Property(property="stages", type="integer", description="stages preference (1 to 5 stages)"),
     *                 @OA\Property(property="cab_facility", type="integer", description="Cab facility preference (1 for yes, 0 for no)"),
     *                 @OA\Property(property="address", type="text"),
     *                 @OA\Property(property="city", type="text"),
     *                 @OA\Property(property="state", type="text"),
     *                 @OA\Property(property="country", type="text"),
     *                 @OA\Property(property="blood_group", type="string"),
     *                 @OA\Property(property="profile_image", type="string", format="binary", description="Upload profile image"),
     *                 @OA\Property(property="marital_status", type="string"),
     *                 @OA\Property(property="interview_mode", type="integer",  description="0 => offline 1 => online"),
     *                 @OA\Property(property="referral_code", type="string"),
     *                 @OA\Property(
     *                     property="cab_address",
     *                     type="string",
     *                     description="Cab address (Required if cab_facility is 1)"
     *                 ),
     *                 @OA\Property(
     *                     property="longitude",
     *                     type="number",
     *                     format="float",
     *                     description="Longitude (Required if cab_facility is 1)"
     *                 ),
     *                 @OA\Property(
     *                     property="latitude",
     *                     type="number",
     *                     format="float",
     *                     description="Latitude (Required if cab_facility is 1)"
     *                 ),
     *                 @OA\Property(
     *                     property="locality",
     *                     type="string",
     *                     description="Locality (Required if cab_facility is 1)"
     *                 ),
     *                 @OA\Property(property="referral_name", type="string", description="Referral name"),
     *                 @OA\Property(property="employee_code", type="string", description="Employee code"),
     *                 @OA\Property(property="current_salary", type="number",  description="Current salary"),
     *                 @OA\Property(property="salary_expectation", type="number", description="Salary expectation"),
     *                 @OA\Property(property="why_do_you_want_to_join_unify", type="string", description="Reason for joining Unify"),
     *                 @OA\Property(property="how_do_you_come_to_know_about_unify", type="string", description="How did you learn about Unify"),
     *                 @OA\Property(property="weakness", type="string", description="Weaknesses"),
     *                 @OA\Property(property="strength", type="string", description="Strengths"),
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Template created successfully.",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="New Applicant created successfully."),
     *             @OA\Property(property="template", type="object", description="Details of the created New Applicant.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Unprocessable Entity",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="error", type="string", example="Validation error."),
     *             @OA\Property(property="details", type="object", description="Validation errors.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Bad Request",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="error", type="string", example="Invalid request.")
     *         )
     *     )
     * )
     */

    public function newApplicantForm(NewApplicantRequest $request, $id){

        try {

            info('New Applicant Data: for testing 1..........'. json_encode($request->all()));
            $token = request()->get('token');

            if (empty($token)) {
                return response()->json(['message' => 'Invalid database ID'], 400);
            }

            $databaseName = explode('@@', $token);

            // Manger id get
            $manager = explode('@@@manId', $token);
            $managerId  = $manager['1'];

            if (!isset($databaseName[0])) {
                return response()->json(['message' => 'Invalid token format'], 400);
            }

            $decodedDatabase = base64_decode($databaseName[0]);

            if ($decodedDatabase === false) {
                return response()->json(['message' => 'Invalid base64 encoding'], 400);
            }

            // Now, pass the decoded string instead of the array
            $this->connectDB($decodedDatabase);

            // ✅ Referral code validation
            $referralCode = $request->input('referral_code');

            if ($referralCode) {
                $referrer = SubUser::where('referral_code', $referralCode)->first();

                if (!$referrer) {
                    return response()->json(['message' => 'Invalid referral code.'], 400);
                }

                if ($referrer->expires_at && now()->greaterThan($referrer->expires_at)) {
                    return response()->json(['message' => 'Referral code has expired.'], 400);
                }
            }

           // $this->connectDB(base64_decode($id));

            $today = now()->format('Y-m-d');
            $validatedData = $request->validated();


            $existEmployee = SubUser::where('email', $request->email)->first();
            $existApplicant = NewApplicant::where('email', $request->email)->first();

            $exist_history = null;
            if ($existEmployee) {
                $exist_history = [
                    'user' => $existEmployee->id,
                    'name' => $existEmployee->first_name,
                ];

            }
            if ($existApplicant) {
                $exist_history = [
                    'applicant' => $existApplicant->id,
                    'stages' => $existApplicant->stages,
                    'name' => $existApplicant->first_name,
                    'rejected' => $existApplicant->is_rejected,
                    'future' => $existApplicant->is_feature_reference,
                ];
            }

            if ($request->hasFile('upload_resume')) {
                $imgName = time()  . $request->file('upload_resume')->getClientOriginalName();
                $request->file('upload_resume')->move(public_path('newApplicantResume'), $imgName);
                $validatedData['upload_resume'] = $imgName; // Assign to validated data
            } else {
                $validatedData['upload_resume'] = null; // Ensure field exists even if no file is uploaded
            }

            if ($request->hasFile('profile_image')) {
                $imgName = time()  . $request->file('profile_image')->getClientOriginalName();
                $request->file('profile_image')->move(public_path('images'), $imgName);
                $validatedData['profile_image'] = $imgName; // Assign to validated data
            } else {
                $validatedData['profile_image'] = null; // Ensure field exists even if no file is uploaded
            }

            // Convert Skills to JSON Array if provided
            if ($request->filled('skills')) {
                $validatedData['skills'] = array_map('trim', explode(',', $request->skills));
            }

            $reason_history = ['status' => ["Candidate Registered Successful"], 'date' => $today];
            $validatedData['reason_history'] = [$reason_history];
            $validatedData['exists_history'] = [$exist_history];
            $validatedData['manager_id'] = $managerId ?? null;
            info('New Applicant Data: for testing 2'. json_encode($validatedData));
            $newApplicant = NewApplicant::create($validatedData);

            $fullName = trim(($newApplicant->first_name ?? '') . ' ' . ($newApplicant->last_name ?? ''));
            $changed = $fullName . " filled a form Registered New Applicant";

            UpdateApplicantHistory::create([
                    'applicant_id' => $newApplicant->id,
                    'updated_by' => auth('sanctum')->id(),
                    'notes' =>  "Registered New Applicant",
                    'date' => $today,
                    'time' => now()->format('H:i:s'),
                    'changed' => $changed,
                ]);
            if ($request->cab_facility == 1) {

                $cabAddressData = [
                    'new_applicant_id' => $newApplicant->id,
                    'cab_address' => $request->input('cab_address'),
                    'locality' => $request->input('locality'),
                    'longitude' => $request->input('longitude'),
                    'latitude' => $request->input('latitude'),
                ];

                // Insert the data into the new_applicant_cab_address table

               NewApplicantCabAddress::create($cabAddressData);
            }

            $sessionId = $request->input('session_id');
            $resumeUpload = HrmsResumeUpload::where('session_id',  $sessionId)->first();
            if ($resumeUpload && !is_null($resumeUpload->resume_name)) {
                 $resumeUpload->is_accept = 1;
                 $resumeUpload->save();
            }
//
            $experienceLevel = 0;
            $experience = $newApplicant->experience;
            switch ($experience) {
                case 'freshers':
                case '1 Year':
                    $experienceLevel = 1;
                    break;

                case '2 Year':
                case '3 Year':
                case '4 Year':
                    $experienceLevel = 2;
                    break;

                case '5 Year':
                case '6 Year':
                    $experienceLevel = 3;
                    break;

                default:
                    $experienceLevel = 3;
                    break;
            }

            $hiringQuiz = HiringQuiz::with(['getDesignationDetails', 'getQuizLevel'])
            ->where('desgination_id', $newApplicant->designation_id)
            ->where('quiz_level_id', $experienceLevel)
            ->first();

            if (!$hiringQuiz) {
                $newApplicant->quiz_status = 1;
                $newApplicant->save();
                $existQuiz = 0;
            }
            if ($hiringQuiz) {
                $existQuiz = 1;
            }

            //****** Third Party Api For Tracking App  **********//
                $curl = curl_init();

                curl_setopt_array($curl, array(
                    CURLOPT_URL => 'https://tracker.unifygroup.in/api/hrms/add-notification',
                    CURLOPT_RETURNTRANSFER => true,
                    CURLOPT_ENCODING => '',
                    CURLOPT_MAXREDIRS => 10,
                    CURLOPT_TIMEOUT => 10,
                    CURLOPT_FOLLOWLOCATION => true,
                    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                    CURLOPT_CUSTOMREQUEST => 'POST',
                    CURLOPT_POSTFIELDS => json_encode([
                        'type' => 'notification',
                        'orgName' => $decodedDatabase,
                        'title' => 'Form Submitted',
                        'message' => "$fullName has submitted a new application form for the designation."
                    ]),
                    CURLOPT_HTTPHEADER => array(
                        'Content-Type: application/json'
                    ),
                ));

                $response = curl_exec($curl);
                $err = curl_error($curl);
                curl_close($curl);
           //****** Third Party Api For Tracking App End **********//

            return $this->successResponse(['applicant_id'  => $newApplicant->id, 'existQuiz' => $existQuiz, 'interview_mode' => $newApplicant->interview_mode, 'referral_code' => $newApplicant->referral_code], 'New Applicant Store');

            }catch (Exception $ex) {

                return $this->errorResponse($ex->getMessage());
            }


     }

   /**Function to store the New Applicant Form */

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */

     /**
     * @OA\Post(
     *     path="/uc/api/new_applicant/candidates/store",
     *     operationId="NewapplicantTemplates",
     *     tags={"New Applicant Templates"},
     *     summary="Submit new applicant data",
     *     security={{"Bearer": {}}},
     *     description="Endpoint to process new applicant data.",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 type="object",
     *                 @OA\Property(property="first_name", type="string", description="First name of the person"),
     *                 @OA\Property(property="last_name", type="string", description="Last name of the person"),
     *                 @OA\Property(property="email", type="string", description="Email address"),
     *                 @OA\Property(property="phone", type="string", description="Phone number"),
     *                 @OA\Property(property="gender", type="string", description="Gender"),
     *                 @OA\Property(property="dob", type="string", format="date", description="Date of birth"),
     *                 @OA\Property(property="designation_id", type="integer", description="Position applied for"),
     *                 @OA\Property(property="role", type="integer", description="Roles associated with the applicant"),
     *                 @OA\Property(property="session_id", type="string", description="Session Id"),
     *                 @OA\Property(property="linkedin_url", type="string", format="url", description="LinkedIn profile URL"),
     *                 @OA\Property(property="upload_resume", type="string", format="binary", description="Upload resume"),
     *                 @OA\Property(property="cover_letter", type="string", description="Cover letter"),
     *
     * @OA\Property(
     *     property="skills",
     *     type="array",
     *     description="List of skills",
     *     @OA\Items(type="string"),
     * ),
     *                 @OA\Property(property="experience", type="string", description="Experience details"),
     *                 @OA\Property(property="typing_speed", type="integer", description="Typing speed"),
     *                 @OA\Property(property="notice_period", type="string", description="Notice period"),
     *                 @OA\Property(property="expected_date_of_join", type="string", format="date", description="Expected date of joining"),
     *                 @OA\Property(property="nightshift", type="integer", description="Nightshift preference (1 for yes, 0 for no)"),
     *                 @OA\Property(property="is_notice", type="integer", description="IsNotice preference (1 for yes, 0 for no)"),
     *                 @OA\Property(property="stages", type="integer", description="stages preference (1 to 5 stages)"),
     *                 @OA\Property(property="cab_facility", type="integer", description="Cab facility preference (1 for yes, 0 for no)"),
     *                 @OA\Property(property="address", type="text"),
     *                 @OA\Property(property="city", type="text"),
     *                 @OA\Property(property="state", type="text"),
     *                 @OA\Property(property="country", type="text"),
     *                 @OA\Property(property="blood_group", type="string"),
     *                 @OA\Property(property="profile_image", type="string", format="binary", description="Upload profile image"),
     *                 @OA\Property(property="marital_status", type="string"),
     *                 @OA\Property(property="interview_mode", type="integer",  description="0 => offline 1 => online"),
     *                 @OA\Property(property="referral_code", type="string"),
     *                 @OA\Property(
     *                     property="cab_address",
     *                     type="string",
     *                     description="Cab address (Required if cab_facility is 1)"
     *                 ),
     *                 @OA\Property(
     *                     property="longitude",
     *                     type="number",
     *                     format="float",
     *                     description="Longitude (Required if cab_facility is 1)"
     *                 ),
     *                 @OA\Property(
     *                     property="latitude",
     *                     type="number",
     *                     format="float",
     *                     description="Latitude (Required if cab_facility is 1)"
     *                 ),
     *                 @OA\Property(
     *                     property="locality",
     *                     type="string",
     *                     description="Locality (Required if cab_facility is 1)"
     *                 ),
     *                 @OA\Property(property="referral_name", type="string", description="Referral name"),
     *                 @OA\Property(property="employee_code", type="string", description="Employee code"),
     *                 @OA\Property(property="current_salary", type="number",  description="Current salary"),
     *                 @OA\Property(property="salary_expectation", type="number", description="Salary expectation"),
     *                 @OA\Property(property="why_do_you_want_to_join_unify", type="string", description="Reason for joining Unify"),
     *                 @OA\Property(property="how_do_you_come_to_know_about_unify", type="string", description="How did you learn about Unify"),
     *                 @OA\Property(property="weakness", type="string", description="Weaknesses"),
     *                 @OA\Property(property="strength", type="string", description="Strengths")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Template created successfully.",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="New Applicant created successfully."),
     *             @OA\Property(property="template", type="object", description="Details of the created New Applicant.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Unprocessable Entity",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="error", type="string", example="Validation error."),
     *             @OA\Property(property="details", type="object", description="Validation errors.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Bad Request",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="error", type="string", example="Invalid request.")
     *         )
     *     )
     * )
     */
    public function store(NewApplicantRequest $request)
    {
        try {
            // ✅ Referral code validation
        $referralCode = $request->input('referral_code');
        $db_name = DB::connection()->getDatabaseName();

        if ($referralCode) {
            $referrer = SubUser::where('referral_code', $referralCode)->first();

            if (!$referrer) {
                return response()->json(['message' => 'Invalid referral code.'], 400);
            }

            if ($referrer->expires_at && now()->greaterThan($referrer->expires_at)) {
                return response()->json(['message' => 'Referral code has expired.'], 400);
            }
        }

        $today = now()->format('Y-m-d');

        $validatedData = $request->validated();
        $validatedData['interview_mode']  = $request->interview_mode ?? 0; // Default to 0 if not provided

        $existEmployee = SubUser::where('email', $request->email)->first();
        $existApplicant = NewApplicant::where('email', $request->email)->first();

        $exist_history = null;
        if ($existEmployee) {
            $exist_history = [
                'user' => $existEmployee->id,
                'name' => $existEmployee->first_name,
            ];

        }
        if ($existApplicant) {
            $exist_history = [
                'applicant' => $existApplicant->id,
                'name' => $existApplicant->first_name,
                'stages' => $existApplicant->stages,
                'rejected' => $existApplicant->is_rejected,
                'future' => $existApplicant->is_feature_reference,
            ];
        }


        if ($request->hasFile('upload_resume')) {
            $imgName = time()  . $request->file('upload_resume')->getClientOriginalName();
            $request->file('upload_resume')->move(public_path('newApplicantResume'), $imgName);
            $validatedData['upload_resume'] = $imgName; // Assign to validated data
        } else {
            $validatedData['upload_resume'] = null; // Ensure field exists even if no file is uploaded
        }

        if ($request->hasFile('profile_image')) {
            $imgName = time()  . $request->file('profile_image')->getClientOriginalName();
            $request->file('profile_image')->move(public_path('images'), $imgName);
            $validatedData['profile_image'] = $imgName; // Assign to validated data
        } else {
            $validatedData['profile_image'] = null; // Ensure field exists even if no file is uploaded
        }

        // Convert Skills to JSON Array if provided
        if ($request->filled('skills')) {
            $validatedData['skills'] = array_map('trim', explode(',', $request->skills));
        }

        $reason_history = ['status' => ["Candidate Registered Successful"], 'date' => $today];
        $validatedData['reason_history'] = [$reason_history];
        $validatedData['exists_history'] = [$exist_history];

        $newApplicant = NewApplicant::create($validatedData);



        $fullName = trim(($newApplicant->first_name ?? '') . ' ' . ($newApplicant->last_name ?? ''));
        $changed = $fullName . " filled a form Registered New Applicant";

        UpdateApplicantHistory::create([
                'applicant_id' => $newApplicant->id,
                'updated_by' => auth('sanctum')->id(),
                'notes' =>  "Registered New Applicant",
                'date' => $today,
                'time' => now()->format('H:i:s'),
                'changed' => $changed,
            ]);
        if ($request->cab_facility == 1) {

            $cabAddressData = [
                'new_applicant_id' => $newApplicant->id,
                'cab_address' => $request->input('cab_address'),
                'locality' => $request->input('locality'),
                'longitude' => $request->input('longitude'),
                'latitude' => $request->input('latitude'),
            ];

            // Insert the data into the new_applicant_cab_address table

           NewApplicantCabAddress::create($cabAddressData);
        }

        $sessionId = $request->input('session_id');
        $resumeUpload = HrmsResumeUpload::where('session_id',  $sessionId)->first();
        if ($resumeUpload && !is_null($resumeUpload->resume_name)) {
             $resumeUpload->is_accept = 1;
             $resumeUpload->save();
        }

        $experienceLevel = 0;
        $experience = $newApplicant->experience;
        switch ($experience) {
            case 'freshers':
            case '1 Year':
                $experienceLevel = 1;
                break;

            case '2 Year':
            case '3 Year':
            case '4 Year':
                $experienceLevel = 2;
                break;

            case '5 Year':
            case '6 Year':
                $experienceLevel = 3;
                break;

            default:
                $experienceLevel = 3;
                break;
        }

        $hiringQuiz = HiringQuiz::with(['getDesignationDetails', 'getQuizLevel'])
        ->where('desgination_id', $newApplicant->designation_id)
        ->where('quiz_level_id', $experienceLevel)
        ->first();

        if (!$hiringQuiz) {
            $newApplicant->quiz_status = 1;
            $newApplicant->save();
            $existQuiz = 0;
        }
        if ($hiringQuiz) {
            $existQuiz = 1;
        }


          //****** Third Party Api For Tracking App  **********//
            $curl = curl_init();

            curl_setopt_array($curl, array(
                CURLOPT_URL => 'https://tracker.unifygroup.in/api/hrms/add-notification',
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => '',
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 10,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => 'POST',
                CURLOPT_POSTFIELDS => json_encode([
                    'type' => 'notification',
                    'orgName' => $db_name,
                    'title' => 'Form Submitted',
                    'message' => "$fullName has submitted a new application form for the designation."
                ]),
                CURLOPT_HTTPHEADER => array(
                    'Content-Type: application/json'
                ),
            ));

            $response = curl_exec($curl);
            $err = curl_error($curl);
            curl_close($curl);

            // if ($err) {
            //     //return response()->json(['error' => $err], 500);
            //     info('errror...'.$err);
            // } else {
            //     //return $response;
            //     info('response...'.$response);
            // }

        //****** Third Party Api For Tracking App End **********//


        return $this->successResponse(['applicant_id'  => $newApplicant->id, 'existQuiz' => $existQuiz], 'New Applicant Store');

        }catch (Exception $ex) {
            return $this->errorResponse($ex->getMessage());
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */


    /**
     * @OA\get(
     * path="/uc/api/new_applicant/candidates/edit/{id}",
     * operationId="editCandidates",
     * tags={"New Applicant Templates"},
     * summary="Edit candidates Request",
     *   security={ {"Bearer": {} }},
     * description="Edit candidates Request",
     *    @OA\Parameter(
     *     name="id",
     *     in="path",
     *     required=true,
     *     @OA\Schema(type="integer")
     *    ),
     *      @OA\Response(
     *          response=201,
     *          description="candidates Edited Successfully",
     *          @OA\JsonContent()
     *       ),
     *      @OA\Response(
     *          response=200,
     *          description="candidates Edited Successfully",
     *          @OA\JsonContent()
     *       ),
     *      @OA\Response(
     *          response=422,
     *          description="Unprocessable Entity",
     *          @OA\JsonContent()
     *       ),
     *      @OA\Response(response=400, description="Bad request"),
     *      @OA\Response(response=404, description="Resource Not Found"),
     * )
     */

            public function edit(Request $request ,$NewApplicant)
        {
            try {

                // Retrieve the candidate details with the cab address
                $getCandidateDetails = NewApplicant::with('CandidateAddress')->find($NewApplicant);

                if (!$getCandidateDetails) {
                    return $this->validationErrorResponse('The given data is not found');
                }
                $quizId = DB::table('user_answer_detail')
                ->where('new_applicant_id', $NewApplicant)
                ->value('quiz_id');

            // Count the number of questions for this quiz_id in quiz_question_detail
            $questionCount = 0;
            if ($quizId) {
                $questionCount = DB::table('quiz_question_details')
                    ->where('hiring_quiz_id', $quizId)
                    ->count();
            }

            $correctAnswerCount = DB::table('user_answer_detail')
            ->where('new_applicant_id', $NewApplicant)
            ->where('is_answer_correct', 1)
            ->count();
                // Prepare cab address data
                $cabAddressData = null;
                if ($getCandidateDetails->cab_facility == 1 && $getCandidateDetails->CandidateAddress) {

                    $cabAddressData = [
                        'cab_address' => $getCandidateDetails->CandidateAddress->cab_address,
                        'locality' => $getCandidateDetails->CandidateAddress->locality,
                        'longitude' => $getCandidateDetails->CandidateAddress->longitude,
                        'latitude' => $getCandidateDetails->CandidateAddress->latitude,
                    ];
                }
                $candidateData = new CandidatesResource($getCandidateDetails);

                $candidateDataArray = $candidateData->toArray($request);
                $candidateDataArray['cab_address'] = $cabAddressData;
                $candidateDataArray['question_count'] = $questionCount;
                $candidateDataArray['correct_answers'] = $correctAnswerCount;
                // Return the success response with candidate data
                return $this->successResponse($candidateDataArray, 'Candidate Details');

            } catch (Exception $ex) {
                // Return the error response in case of an exception
                return $this->errorResponse($ex->getMessage());
            }
        }



    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */

    /**
     * @OA\post(
     * path="/uc/api/new_applicant/candidates/update/{id}",
     * operationId="updateCandidates",
     * tags={"New Applicant Templates"},
     * summary="Update Candidates Request",
     *   security={ {"Bearer": {} }},
     * description="Update Candidates Request",
     *    @OA\Parameter(
     *     name="id",
     *     in="path",
     *     required=true,
     *     @OA\Schema(type="integer")
     *    ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 type="object",
     *                 @OA\Property(property="first_name", type="string", description="First name of the person"),
     *                 @OA\Property(property="last_name", type="string", description="Last name of the person"),
     *                 @OA\Property(property="email", type="string", description="Email address"),
     *                 @OA\Property(property="phone", type="string", description="Phone number"),
     *                 @OA\Property(property="gender", type="string", description="Gender"),
     *                 @OA\Property(property="dob", type="string", format="date", description="Date of birth"),
     *                 @OA\Property(property="designation_id", type="integer", description="Position applied for"),
     *                 @OA\Property(property="role", type="integer", description="Roles associated with the applicant"),
     *                 @OA\Property(property="linkedin_url", type="string", format="url", description="LinkedIn profile URL"),
     *                 @OA\Property(property="upload_resume", type="string", format="binary", description="Upload resume"),
     *                 @OA\Property(property="cover_letter", type="string", description="Cover letter"),
     *                 @OA\Property(
     *                        property="skills",
     *                        type="array",
     *                        description="List of skills",
     *                 @OA\Items(type="string"),
     *                 ),
     *                 @OA\Property(property="experience", type="string", description="Experience details"),
     *                 @OA\Property(property="typing_speed", type="integer", description="Typing speed"),
     *                 @OA\Property(property="notice_period", type="string", description="Notice period"),
     *                 @OA\Property(property="expected_date_of_join", type="string", format="date", description="Expected date of joining"),
     *                 @OA\Property(property="working_nightshift", type="integer", description="working_nightshift preference (1 for yes, 0 for no)"),
     *                 @OA\Property(property="is_notice", type="integer", description="IsNotice preference (1 for yes, 0 for no)"),
     *                 @OA\Property(property="stages", type="integer", description="stages preference (1 to 5 stages)"),
     *                 @OA\Property(property="cab_facility", type="integer", description="Cab facility preference (1 for yes, 0 for no)"),
     *                 @OA\Property(property="address", type="text"),
     *                 @OA\Property(property="city", type="text"),
     *                 @OA\Property(property="state", type="text"),
     *                 @OA\Property(property="country", type="text"),
     *                 @OA\Property(property="blood_group", type="text"),
     *                 @OA\Property(property="profile_image",  type="string", format="binary", description="Upload profile image"),
     *                 @OA\Property(property="marital_status", type="text"),
     *                 @OA\Property(
     *                     property="cab_address",
     *                     type="string",
     *                     description="Cab address (Required if cab_facility is 1)"
     *                 ),
     *                 @OA\Property(
     *                     property="longitude",
     *                     type="number",
     *                     format="float",
     *                     description="Longitude (Required if cab_facility is 1)"
     *                 ),
     *                 @OA\Property(
     *                     property="latitude",
     *                     type="number",
     *                     format="float",
     *                     description="Latitude (Required if cab_facility is 1)"
     *                 ),
     *                 @OA\Property(
     *                     property="locality",
     *                     type="string",
     *                     description="Locality (Required if cab_facility is 1)"
     *                 ),
     *                 @OA\Property(property="referral_name", type="string", description="Referral name"),
     *                 @OA\Property(property="employee_code", type="string", description="Employee code"),
     *                 @OA\Property(property="current_salary", type="number",  description="Current salary"),
     *                 @OA\Property(property="salary_expectation", type="number", description="Salary expectation"),
     *                 @OA\Property(property="why_do_you_want_to_join_unify", type="string", description="Reason for joining Unify"),
     *                 @OA\Property(property="how_do_you_come_to_know_about_unify", type="string", description="How did you learn about Unify"),
     *                 @OA\Property(property="weakness", type="string", description="Weaknesses"),
     *                 @OA\Property(property="strength", type="string", description="Strengths"),
     *                 @OA\Property(property="notes", type="string", description="notes for the candidate update reason"),
     *             )
     *         )
     *     ),
     *      @OA\Response(
     *          response=201,
     *          description="Designation Updated Successfully",
     *          @OA\JsonContent()
     *       ),
     *      @OA\Response(
     *          response=200,
     *          description="Designation Updated Successfully",
     *          @OA\JsonContent()
     *       ),
     *      @OA\Response(
     *          response=422,
     *          description="Unprocessable Entity",
     *          @OA\JsonContent()
     *       ),
     *      @OA\Response(response=400, description="Bad request"),
     *      @OA\Response(response=404, description="Resource Not Found"),
     * )
     */
    // public function update(NewApplicantRequest $request, $id)
    // {
    //     try {

    //         $today = now()->format('Y-m-d');
    //         $reason = isset($request->reason) ? $request->reason : "";

    //         $findCandidateDetail = NewApplicant::find($id);
    //             if ($findCandidateDetail) {
    //                 $validatedData = $request->validated();

    //                 /**
    //                  * handle the file upload
    //                  */
    //                 if ($request->hasFile('upload_resume')) {
    //                     $resumeName = time() . "_" . $request->file('upload_resume')->getClientOriginalName();
    //                     $request->file('upload_resume')->move(public_path('newApplicantResume'), $resumeName);
    //                     $validatedData['upload_resume'] = $resumeName;
    //                 }

    //                 /**
    //                  * handle the profile file upload
    //                  */
    //                 if ($request->hasFile('profile_image')) {
    //                     $profileIMG = time() . "_" . $request->file('profile_image')->getClientOriginalName();
    //                     $request->file('profile_image')->move(public_path('images'), $profileIMG);
    //                 }

    //                 // Convert Skills to JSON Array if provided
    //                 if ($request->filled('skills')) {
    //                     $validatedData['skills'] = !empty($request->skills) ? array_map('trim', explode(',', $request->skills)) : $findCandidateDetail->skill;
    //                 }

    //                 $findCandidateDetail->update($validatedData);

    //                 if ($request->cab_facility == 1) {

    //                     $findCabAddressData = NewApplicantCabAddress::where('new_applicant_id', $id)->first();

    //                     if (isset($findCabAddressData)) {
    //                         $cabAddressData = [
    //                             'new_applicant_id' => $id,
    //                             'cab_address' => $requestData['cab_address']['cab_address'],
    //                             'locality' => $requestData['cab_address']['locality'],
    //                             'longitude' => $requestData['cab_address']['longitude'],
    //                             'latitude' => $requestData['cab_address']['latitude'],
    //                         ];
    //                         // update the data into the new_applicant_cab_address table
    //                         $findCabAddressData->update($cabAddressData);
    //                     }

    //                 }



    //                 return $this->successResponse(
    //                     new CandidatesResource($findCandidateDetail),
    //                     'Candidate Updated Successfully'
    //                 );
    //             }else {
    //                 return $this->validationErrorResponse("the given data is not found");
    //             }
    //     } catch (\Throwable $th) {
    //         return $this->errorResponse($th->getMessage());
    //     }
    // }






        public function update(UpdateApplicantRequest $request, $id)
        {

            try {
                        $today = now()->format('Y-m-d');
                        $reason = isset($request->reason) ? $request->reason : "";
                        $note = isset($request->note) ? $request->note : "Unknown note";

                        $findCandidateDetail = NewApplicant::find($id);
                        if ($findCandidateDetail) {
                            // Store original data before any changes
                            $originalData = $findCandidateDetail->getOriginal();
                            $validatedData = $request->validated();

                            // Handle resume upload and track change
                            if ($request->hasFile('upload_resume')) {
                                $oldResume = $findCandidateDetail->upload_resume;
                                $resumeName = time() . "_" . $request->file('upload_resume')->getClientOriginalName();
                                $request->file('upload_resume')->move(public_path('newApplicantResume'), $resumeName);
                                $validatedData['upload_resume'] = $resumeName;
                                $oldOriginalName = $oldResume ? substr($oldResume, strpos($oldResume, '_') + 1) : null;
                                $newOriginalName = $request->file('upload_resume')->getClientOriginalName();
                                if ($oldOriginalName != $newOriginalName) {
                                      $this->logFieldChange($findCandidateDetail->id, $note, 'upload_resume', $oldResume, $resumeName);
                                }

                            }

                            // Handle profile image upload and track change
                            if ($request->hasFile('profile_image')) {
                                $oldProfileImg = $findCandidateDetail->profile_image;
                                $profileIMG = time() . "_" . $request->file('profile_image')->getClientOriginalName();
                                $request->file('profile_image')->move(public_path('images'), $profileIMG);
                                $validatedData['profile_image'] = $profileIMG;

                                $oldprofileName = $oldProfileImg ? substr($oldProfileImg, strpos($oldProfileImg, '_') + 1) : null;
                                $newprofileName = $request->file('profile_image')->getClientOriginalName();
                                if ($oldprofileName != $newprofileName) {
                                     $this->logFieldChange($findCandidateDetail->id, $note, 'profile_image', $oldProfileImg, $profileIMG);
                                }

                            }



                            if ($request->filled('skills')) {
                                // Handle old skills (from DB)
                                $oldSkillsRaw = $findCandidateDetail->skills ?? '';
                                $oldSkills = is_array($oldSkillsRaw)
                                    ? array_map('trim', $oldSkillsRaw)
                                    : array_map('trim', explode(',', $oldSkillsRaw));

                                // Handle new skills (from request)
                                $newSkillsRaw = $request->skills;
                                $newSkills = is_array($newSkillsRaw)
                                    ? array_map('trim', $newSkillsRaw)
                                    : array_map('trim', explode(',', $newSkillsRaw));

                                // Sort both arrays for consistent comparison
                                sort($oldSkills);
                                sort($newSkills);

                                // Compare the arrays
                                if ($oldSkills != $newSkills) {
                                    $validatedData['skills'] = $newSkills;
                                    $this->logFieldChange($findCandidateDetail->id, $note, 'skills', implode(', ', $oldSkills), implode(', ', $newSkills));
                                }
                            }


                            // Track all other field changes before updating

                            $this->trackFieldChanges($findCandidateDetail, $note, $originalData, $validatedData);

                            // Update the candidate
                            $findCandidateDetail->update($validatedData);

                            // Send the email without attachment



                            if ($request->cab_facility == 1) {
                                $findCabAddressData = NewApplicantCabAddress::where('new_applicant_id', $id)->first();

                                if (isset($findCabAddressData)) {
                                    $cabAddressData = [
                                        'new_applicant_id' => $id,
                                        'cab_address' => $validatedData['cab_address'],
                                        'locality' => $validatedData['locality'],
                                        'longitude' => $validatedData['longitude'],
                                        'latitude' => $validatedData['latitude'],
                                    ];

                                    // Track cab address changes
                                    $this->trackCabAddressChanges($findCandidateDetail->id, $note, $findCabAddressData, $cabAddressData);

                                    $findCabAddressData->update($cabAddressData);
                                }
                            }

                            return $this->successResponse(
                                new CandidatesResource($findCandidateDetail),
                                'Candidate Updated Successfully'
                            );
                        } else {
                            return $this->validationErrorResponse("the given data is not found");
                        }
            } catch (\Throwable $th) {
                    //return $this->errorResponse($th->getMessage());
                     return $this->errorResponse(sprintf(
                    '%s in %s on line %d',
                    $th->getMessage(),
                    $th->getFile(),
                    $th->getLine()
                ));
            }
        }

        /**
        * Track changes between original data and new data
        */
        private function trackFieldChanges($applicant, $note, $originalData, $newData)
        {
            $ignoredFields = ['updated_at', 'created_at', 'reason_history'];

            foreach ($newData as $field => $newValue) {
                if (in_array($field, $ignoredFields)) {
                    continue;
                }

                $oldValue = $originalData[$field] ?? null;

                // Skip if values are equal
                if ($oldValue == $newValue) {
                    continue;
                }

                if ($field == 'cab_address' || $field == 'locality' || $field == 'longitude' ||
                 $field == 'latitude' || $field == 'upload_resume' || $field == 'profile_image' || $field == 'skills') {
                    continue;
                }

                $this->logFieldChange($applicant->id, $note, $field, $oldValue, $newValue);
            }
        }

        /**
        * Track cab address changes separately
        */
        private function trackCabAddressChanges($applicantId, $note, $originalData, $newData)
        {
            foreach ($newData as $field => $newValue) {
                $oldValue = $originalData->{$field};

                if ($oldValue != $newValue) {
                    $this->logFieldChange(
                        $applicantId,
                         $note,
                        'cab_'.$field,
                        $oldValue,
                        $newValue
                    );
                }
            }
        }

        /**
        * Log a single field change to the history table
        */
        private function logFieldChange($applicantId, $note, $field, $oldValue, $newValue)
        {
            // Convert field name to human-readable format
            $readableFieldName = str_replace('_', ' ', $field);

            // Format values for display
            $displayOldValue = $this->formatValueForDisplay($oldValue);
            $displayNewValue = $this->formatValueForDisplay($newValue);

            if ($field == 'gender') {
                  if ($oldValue == 0) {
                        $displayOldValue = 'Female';
                  }elseif ($oldValue == 1) {
                        $displayOldValue = 'Male';
                  }elseif ($oldValue == 2) {
                        $displayOldValue = 'Other';
                  }
                  if ($newValue == 0) {
                        $displayNewValue = 'Female';
                  }elseif ($newValue == 1) {
                        $displayNewValue = 'Male';
                  }elseif ($newValue == 2) {
                        $displayNewValue = 'Other';
                  }
            }

            if ($field == 'cab_facility') {
                if ($oldValue == 0) {
                    $displayOldValue = 'No';
                }elseif ($oldValue == 1) {
                    $displayOldValue = 'Yes';
                }
                if ($newValue == 0) {
                    $displayNewValue = 'No';
                }elseif ($newValue == 1) {
                    $displayNewValue = 'Yes';
                }
            }

            if ($field == 'interview_mode') {
                if ($oldValue == 0) {
                    $displayOldValue = 'On-site';
                }elseif ($oldValue == 1) {
                    $displayOldValue = 'Remote';
                }
                if ($newValue == 0) {
                    $displayNewValue = 'On-site';
                }elseif ($newValue == 1) {
                    $displayNewValue = 'Remote';
                }
            }
            if ($field == 'working_nightshift') {
                if ($oldValue == 0) {
                    $displayOldValue = 'No';
                }elseif ($oldValue == 1) {
                    $displayOldValue = 'Yes';
                }
                if ($newValue == 0) {
                    $displayNewValue = 'No';
                }elseif ($newValue == 1) {
                    $displayNewValue = 'Yes';
                }
            }

            // Create the note message
            $changedData = ucfirst($readableFieldName)." changed from {$displayOldValue} to {$displayNewValue}";
            if ($field != 'note') {
                 UpdateApplicantHistory::create([
                    'applicant_id' => $applicantId,
                    'updated_by' => auth('sanctum')->id(),
                    'notes' =>  $note,
                    'date' =>  now()->format('Y-m-d'),
                    'time' => now()->format('H:i:s'),
                    'changed' => $changedData,
               ]);
            }

        }

        /**
        * Format values for display in the notes
        */
        private function formatValueForDisplay($value)
        {
            if (is_null($value)) {
                return 'empty';
            }

            if ($value === '') {
                return 'empty string';
            }

            if (is_array($value)) {
                return json_encode($value);
            }

            if (is_bool($value)) {
                return $value ? 'true' : 'false';
            }

            return $value;
        }







    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */

    /**
     * @OA\delete(
     * path="/uc/api/new_applicant/candidates/delete/{id}",
     * operationId="deleteCandidate",
     * tags={"New Applicant Templates"},
     * summary="Delete Candidate Request",
     * security={ {"Bearer": {} }},
     * description="Delete Candidate Request",
     * @OA\Parameter(
     *     name="id",
     *     in="path",
     *     required=true,
     *     @OA\Schema(type="integer")
     * ),
     * @OA\Response(
     *     response=200,
     *     description="Candidate Deleted Successfully",
     *     @OA\JsonContent()
     * ),
     * @OA\Response(
     *     response=404,
     *     description="Resource Not Found"
     * )
     * )
     */

    public function destroy($id)
    {
        try {
            $getCandidateDetails = NewApplicant::find($id);
            $getCandidateDetailsAddress = NewApplicantCabAddress::where('new_applicant_id', $id)->first();

            if (isset($getCandidateDetails)) {
                $getCandidateDetails->delete();
                if (isset($getCandidateDetailsAddress)) {
                    $getCandidateDetailsAddress->delete();
                }
                return $this->successResponse(
                    [],
                    'Candidate Removed Sucessfully'
                );
            } else {
                return $this->validationErrorResponse('the given data is not found');
            }
        } catch (Exception $ex) {
            return $this->errorResponse($ex->getMessage());
        }
    }

    /**
     * @OA\Post(
     * path="/uc/api/new_applicant/candidates/sendEmailToNewApplicant",
     * operationId="sendEmailToNewApplicant",
     * tags={"New Applicant Templates"},
     * summary="Send Email To New Applicant",
     * security={ {"Bearer": {} }},
     * description="Send Email To New Applicant",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 type="object",
     *                 @OA\Property(
     *                     property="id",
     *                     type="integer",
     *                     description="The ID of the new applicant.",
     *                 ),
     *                 @OA\Property(
     *                     property="subject",
     *                     type="string",
     *                     description="The subject of the email.",
     *                 ),
     *              @OA\Property(
     *                     property="stage",
     *                     type="ingeter",
     *                     description="stage 1, 2, 3 like inprogress, offered.",
     *                 ),
     *             ),
     *         ),
     *     ),
     *      @OA\Response(
     *          response=201,
     *          description="Email Sent To New Applicant Successfully",
     *          @OA\JsonContent()
     *       ),
     *      @OA\Response(
     *          response=200,
     *          description="Email Sent To New Applicant Successfully",
     *          @OA\JsonContent()
     *       ),
     *      @OA\Response(
     *          response=422,
     *          description="Unprocessable Entity",
     *          @OA\JsonContent()
     *       ),
     *      @OA\Response(response=400, description="Bad request"),
     *      @OA\Response(response=404, description="Resource Not Found"),
     * )
     */

     public function sendEmailToNewApplicant(SendEmailApplicantRequest $request)
     {
         try {
             $validated = $request->validated();


             if (!empty($validated['hiring_id'])) {
                 // Fetch hiring template details
                //  $hiringTemplate = HiringTemplate::findOrFail($validated['hiring_id']);

                //  // Fetch the user associated with hiring_id (assuming user_id exists in HiringTemplate)
                //  $user = NewApplicant::findOrFail($validated['id']);
                //  $userEmail = $user->email;
                //  $userName = trim($user->first_name . ' ' . $user->last_name); // Concatenate first and last name
                //  $currentDate = now()->format('Y-m-d');

                //  // Prepare data for the second email


                //  // Generate a dynamic PDF
                //  $pdf = PDF::loadView('email.pdf.hiring_template', [
                //      'name' => $userName,
                //      'date' => $currentDate,
                //      'content' => $hiringTemplate->content,
                //      'urlimage' => $hiringTemplate->header_image,
                //  ]);

                //  $timestamp = now()->format('Ymd_His');
                //  // Define file name with timestamp
                //  $pdfFileName = 'Applicant_' . $hiringTemplate->template_name . '_' . $timestamp . '.pdf';

                // // Define correct public folder path
                // $pdfFilePath = public_path('hiring_pdfs/' . $pdfFileName);

                // $directory = public_path('hiring_pdfs');
                // if (!file_exists($directory) && !is_dir($directory)) {
                //     mkdir($directory, 0777, true);
                // }

                // $pdfContent = $pdf->output();
                // // Save the PDF to the public folder
                // file_put_contents($pdfFilePath, $pdf->output());

                //  // Send the email with the dynamic PDF attachment
                //  Mail::to($userEmail)
                //  ->send(
                //      (new HiringTemplateNotification($hiringMailData))
                //          ->attachData($pdfContent, $pdfFileName,  [
                //              'mime' => 'application/pdf', // MIME type
                //          ])
                //  );

                //  $emailData =[
                //     'user_id' => $user->id,
                //     'stage' => $validated['stage'],
                //     'email' => $user->email,
                //     'template_name' => $hiringTemplate->template_name,
                //     'email_title' =>  $hiringTemplate->title,
                //     'email_content' => $hiringTemplate->content,
                //     'email_image' => $hiringTemplate->header_image,
                //     'email_pdf' => $pdfFileName,
                //     'send_date' => now()
                //  ];
                //  HrmsEmployeeEmail::create($emailData);

                // $userId =  $user->id;
                // $email =  $user->email;
                // $databaseName = DB::connection()->getDatabaseName();

                // $this->sendEmailtoOfferacceptDecline($userId, $email, $databaseName, $userName);


             } else {
                 // Regular email for applicants without a hiring template
                 $applicant = NewApplicant::findOrFail($validated['id']);
                 $email = $applicant->email;
                 $mailData = [
                     'subject' => $validated['subject'],
                     'title' => 'Application Update',
                     'body' => $validated['body'],
                 ];

                 // Send the email without attachment
                 Mail::to($email)->send(new NewApplicantNotification($mailData));

                 $emailData =[
                    'user_id' => $applicant->id,
                    'email' => $applicant->email,
                    'template_name' => $hiringTemplate->template_name ?? '',
                    'email_title' =>  $hiringTemplate->title ?? 'Application Update',
                    'email_content' => $hiringTemplate->content ?? $validated['body'],
                    'email_image' => $hiringTemplate->header_image ?? '',
                    'email_pdf' => '',
                    'send_date' => date('Y-m-d H:i:s')
                 ];
                 HrmsEmployeeEmail::create($emailData);
             }

             return $this->successResponse([], 'Mail Sent Successfully');
         } catch (Exception $e) {
             return $this->errorResponse($e->getMessage());
         }
     }





    protected function sendEmailtoOfferacceptDecline($userId, $email, $databaseName, $name)
    {

        $database =  base64_encode($databaseName);
        $applicantId = base64_encode($userId);
        $applicantemail = base64_encode($email);
        $accept = base64_encode("Accept");
        $decline = base64_encode("Decline");

        //base64_encode()

        $acceptUrl = $applicantId.'/'.$applicantemail.'/'.$accept.'/'.$database;
        $declineUrl = $applicantId.'/'.$applicantemail.'/'.$decline.'/'.$database;

        $this->data["details"] = [
            "email" =>$email,
            "name" =>$name,
            'accept_url' =>$acceptUrl,
            'decline_url' =>$declineUrl,
        ];

        $email = $email;
        $subject = 'Offer accept and decline';

        \Mail::send("email.offer_accept", $this->data, function ($message) use ($email, $subject) {
            $message
                ->to($email)
                ->from("info@gmail.com")
                ->subject($subject);
        });

    }



    /**
     * @OA\Post(
     * path="/uc/api/new_applicant/candidates/makeParmanentEmployeeList/{id}",
     * operationId="makeParmanentEmployeeList",
     * tags={"New Applicant Templates"},
     * summary="make Parmanent Employee list",
     * security={ {"Bearer": {} }},
     * description="make Parmanent Employee list",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 type="object",
     *                 @OA\Property(
     *                     property="id",
     *                     type="integer",
     *                     description="The ID of the make parmanent employee.",
     *                 ),
     *             ),
     *         ),
     *     ),
     *      @OA\Response(
     *          response=201,
     *          description="make parmanent employee",
     *          @OA\JsonContent()
     *       ),
     *      @OA\Response(
     *          response=200,
     *          description="make parmanent employee",
     *          @OA\JsonContent()
     *       ),
     *      @OA\Response(
     *          response=422,
     *          description="Unprocessable Entity",
     *          @OA\JsonContent()
     *       ),
     *      @OA\Response(response=400, description="Bad request"),
     *      @OA\Response(response=404, description="Resource Not Found"),
     * )
     */

    public function makeParmanentEmployeeList(Request $request, $id){

        try {
            $id = $request->input('id');
            // Retrieve the candidate details with the cab address
            $this->data['condidate_details'] = $getCandidateDetails = NewApplicant::with('CandidateAddress')->find($id);

            if (!$getCandidateDetails) {
                return $this->validationErrorResponse('The given data is not found');
            }
            $quizId = DB::table('user_answer_detail')
            ->where('new_applicant_id', $id)
            ->value('quiz_id');

            // Count the number of questions for this quiz_id in quiz_question_detail
            $questionCount = 0;
            if ($quizId) {
                $questionCount = DB::table('quiz_question_details')
                    ->where('hiring_quiz_id', $quizId)
                    ->count();
            }

            $correctAnswerCount = DB::table('user_answer_detail')->where('new_applicant_id', $quizId)->where('is_answer_correct', 1)->count();
                // Prepare cab address data
                $cabAddressData = null;
                if ($getCandidateDetails->cab_facility == 1 && $getCandidateDetails->CandidateAddress) {
                    $cabAddressData = [
                        'cab_address' => $getCandidateDetails->CandidateAddress->cab_address,
                        'locality' => $getCandidateDetails->CandidateAddress->locality,
                        'longitude' => $getCandidateDetails->CandidateAddress->longitude,
                        'latitude' => $getCandidateDetails->CandidateAddress->latitude,
                    ];
                }

                $this->data['assign_employe_type'] = HrmsRole::where('name', '!=', 'Manager Attendance View')->get();
                $this->data['department'] = Designation::get();
                $this->data['manager'] =  TeamManager::with('employees')->get();
                $this->data['team_leader'] =   HrmsTeam::with('teamLeader')->get();
                $this->data['shift_type'] =    HrmsTimeAndShift::select('shift_name','shift_finishs_next_day')->get();

                $this->data['cab_address'] = $cabAddressData;
                $this->data['question_count'] = $questionCount;
                $this->data['correct_answers'] = $correctAnswerCount;

                return response()->json([
                    'success' => true,
                    'message'=> 'Employee details',
                    'data'=> $this->data
                ], 200);


        } catch (\Throwable $th) {
            return $this->errorResponse($th->getMessage());
        }
    }


    /**
     * @OA\Post(
     *     path="/uc/api/new_applicant/candidates/makeParmanentUser",
     *     operationId="makeParmanentUser",
     *     tags={"New Applicant Templates"},
     *     summary="Make Permanent User",
     *     security={ {"Bearer": {} }},
     *     description="Make Permanent User",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 type="object",
     *                 @OA\Property(property="first_name", type="string", description="first_name"),
     *                 @OA\Property(property="last_name", type="string", description="last_name"),
     *                 @OA\Property(property="id_card", type="string", description="Employee Card ID"),
     *                 @OA\Property(property="phone", type="string", description="phone"),
     *                 @OA\Property(property="emergency_phone", type="string", description="Emergency Phone Number"),
     *                 @OA\Property(property="email", type="string", description="email"),
     *                 @OA\Property(property="gender", type="string", description="Female or Male"),
     *                 @OA\Property(property="dob", type="date", description="Date of Birthday"),
     *                 @OA\Property(property="date_of_join", type="date", description="Date of Joining"),
     *                 @OA\Property(property="assign_department", type="string", description="Assign designation ** (designation)"),
     *                 @OA\Property(property="department", type="string", description="Assign department"),
     *                 @OA\Property(property="assign_employee_position", type="integer", description="Assign employee position like, teamleader, manager, employee (ID)"),
     *                 @OA\Property(property="manager_id", type="integer", description="Under in manager (ID)"),
     *                 @OA\Property(property="assign_team", type="integer", description="Assign team (ID)"),
     *                 @OA\Property(property="employee_shift", type="string", description="Employee Shift like Morning, Evning"),
     *                 @OA\Property(property="shift_type", type="integer", description="Employee Shift like Morning = 0, Evning = 1"),
     *                 @OA\Property(property="marital_status", type="string", description="Marital status"),
     *                 @OA\Property(property="blood_group", type="string", description="blood group"),
     *                 @OA\Property(property="current_status", type="integer", description="1 => Active, 2 => InActive"),
     *                 @OA\Property(property="address", type="string", description="address"),
     *                 @OA\Property(property="parmanent_address", type="string", description="permanent address"),
     *                 @OA\Property(property="new_applicant_id", type="integer", description="Offered Applicant Id"),
     *                 @OA\Property(property="country", type="string", description="country"),
     *                 @OA\Property(property="state", type="string", description="state"),
     *                 @OA\Property(property="city", type="string", description="city"),
     *                 @OA\Property(property="assign_pc", type="string", description="Assigned PC"),
     *                 @OA\Property(property="sallary", type="string", description="Salary"),
     *                 @OA\Property(property="father_name", type="string", description="Father Name"),
     *                 @OA\Property(property="mother_name", type="string", description="Mother Name"),
     *                 @OA\Property(property="spouse_name", type="string", description="Spouse Name"),
     *                 @OA\Property(property="no_of_childern", type="integer", description="Number of Childerns"),
     *                 @OA\Property(property="documented_birthday", type="date", description="Document Birthday"),
     *                 @OA\Property(property="induction_status", type="string", description="induction status"),
     *                 @OA\Property(property="reporting_leader", type="string", description="Reporting Leader"),
     *                 @OA\Property(property="interview_souce", type="string", description="interview souce"),
     *                 @OA\Property(property="referal_by", type="string", description="referal by"),
     *                 @OA\Property(property="aadhar_card_number", type="string", description="Aadhar Card Number"),
     *                 @OA\Property(property="PAN_card_number", type="string", description="PAN Card Number"),
     *                 @OA\Property(property="voter_id", type="string", description="voter Id"),
     *                 @OA\Property(property="driving_lincense", type="string", description="Driving lincense"),
     *                 @OA\Property(property="account_name", type="string", description="Account Name"),
     *                 @OA\Property(property="account_number", type="string", description="Account Number"),
     *                 @OA\Property(property="IFSC_code", type="string", description="IFSC Code"),
     *                 @OA\Property(property="reason", type="string", description="reason"),
     *                 @OA\Property(property="remark", type="string", description="remark"),
     *                 @OA\Property(property="PF_status", type="string", description="PF Status"),
     *                 @OA\Property(property="pf_no", type="string", description="PF Number"),
     *                 @OA\Property(property="relieving_letter", type="string", description="Relieving Letter"),
     *                 @OA\Property(property="FNF", type="string", description="FNF"),
     *                 @OA\Property(property="UAN_no", type="string", description="UAN Number"),
     *                 @OA\Property(property="assets", type="string", description="assets"),
     *                 @OA\Property(property="recovery", type="string", description="recovery"),
     *                 @OA\Property(property="genious_employee_code", type="string", description="Recgenious Employee Codeovery"),
     *                 @OA\Property(property="salary_cycle", type="string", description="salary cycle"),
     *                 @OA\Property(property="skills", type="string", description="skills"),
     *                 @OA\Property(property="qualification", type="string", description="Qualification"),
     *                 @OA\Property(property="age_in_year", type="string", description="Qualification"),
     *                 @OA\Property(property="experience", type="string", description="Experience"),
     *                 @OA\Property(property="company_email", type="email", format="email", description="Company Email"),
     *                 @OA\Property(property="cab_facility", type="integer", description="Requierment Cab Facility,  yes => 1, no => 0"),
     *                 @OA\Property(property="pickup_address", type="string", description="Pickup Address"),
     *                 @OA\Property(property="latitude", type="numeric", description="latitude"),
     *                 @OA\Property(property="longitude", type="numeric", description="longitude"),
     *                 @OA\Property(property="drug_policy", type="string", description="Drug Policy"),
     *                 @OA\Property(property="transport_policy", type="string", description="Transport Policy"),
     *                 @OA\Property(property="laptop_phone_policy", type="string", description="Laptop Phone Policy"),
     *                 @OA\Property(property="IJP_policy", type="string", description="IJP Policy"),
     *                 @OA\Property(property="appraisal_policy", type="string", description="Appraisal Policy"),
     *             ),
     *         ),
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Make Permanent User",
     *         @OA\JsonContent()
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Make Permanent User",
     *         @OA\JsonContent()
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Unprocessable Entity",
     *         @OA\JsonContent()
     *     ),
     *     @OA\Response(response=400, description="Bad request"),
     *     @OA\Response(response=404, description="Resource Not Found"),
     * )
     */


     public function makeParmanentUser(MakePermanentEmployeeRequest $request){

        try {

            $validatedData = $request->validated();

            $temp_DB_name = DB::connection()->getDatabaseName();
            $makeEmployee = NewApplicant::with('designation','CandidateAddress')->find($request->new_applicant_id);
            $designation = Designation::find($validatedData['assign_department']);
            $shift = HrmsTimeAndShift::first();
            if (!$makeEmployee) {
                return $this->errorResponse("the given data is not found");
            }

            $experience = (int) filter_var($makeEmployee->experience, FILTER_SANITIZE_NUMBER_INT);

            $makeEmployee->is_employee = 1;
            $makeEmployee->save();
            //connecting to parent DB
            $default_DBName = env("DB_DATABASE");
            $this->connectDB($default_DBName);


            $NewEmployee = new SubUser();
            $NewEmployee->first_name = $validatedData['first_name'];
            $NewEmployee->last_name = $validatedData['last_name'];
            $NewEmployee->unique_id = $request->id_card;
            $NewEmployee->email = $validatedData['email'];
            $NewEmployee->phone = $validatedData['phone'];
            $NewEmployee->mobile = $request->emergency_phone;
            $NewEmployee->gender =  $request->gender;
            // $NewEmployee->gender =  $validatedData['gender'] == 1 ? 'Male' :
            // ($makeEmployee->gender == 2 ? 'Female' :
            // ($makeEmployee->gender == 3 ? 'Other' : null));
            $NewEmployee->cab_facility =  $request->cab_facility;

            $NewEmployee->dob = date('Y-m-d', strtotime($validatedData['dob']));
            $NewEmployee->doj = $request->date_of_join;
            $NewEmployee->employement_type = $designation->title;           ///////////////
            $NewEmployee->shift_type = $request->shift_type ?? null;
            $NewEmployee->employee_shift = $request->employee_shift ?? $shift->name;
            $NewEmployee->worked_for = $request->date_of_join;
            $NewEmployee->marital_status = $request->marital_status;
            $NewEmployee->status = $request->current_status;
            $NewEmployee->blood_group =  $request->blood_group;
            $NewEmployee->nationality =  $request->country;
            $NewEmployee->company_name = auth('sanctum')->user()->company_name;
            $NewEmployee->database_path = env("DB_HOST");
            $NewEmployee->database_name = $temp_DB_name;
            $NewEmployee->database_username = env("DB_USERNAME");
            $NewEmployee->database_password = env("DB_PASSWORD");
            $NewEmployee->user_type = "0";                         ////////////////////// role bassed
            $getRole =  'carer';
            $rand = Str::random(10);
            $password = Hash::make($rand);


            $NewEmployee->password = $password;
            if ($makeEmployee->profile_image) {

                $profilefilename = $makeEmployee->profile_image ?? $validatedData['profile_image'];
                //  $makeEmployee->profile_image->move($path, $profilefilename);
                $NewEmployee->profile_image = $profilefilename;
            }
           // $NewEmployee->save();

            $admin = Role::where('name', 'staff')->first();
            if ($NewEmployee->save()) {
                if (!$NewEmployee->hasRole($getRole)) {
                    $NewEmployee->roles()->attach($admin);
                }
            }

            $this->connectDB($temp_DB_name);

            //create staff in users table in child DB
            $child_user = new User();
            $child_user->id = $NewEmployee->id;
            $child_user->first_name = $NewEmployee->first_name;
            $child_user->last_name = $NewEmployee->last_name;
            $child_user->unique_id = $NewEmployee->unique_id;
            $child_user->email = $NewEmployee->email;
            $child_user->phone = $NewEmployee->phone;
            $child_user->mobile = $NewEmployee->mobile;
            $child_user->gender = $NewEmployee->gender;
            $child_user->address = $request->address;
            $child_user->dob = $NewEmployee->dob;
            $child_user->doj = $NewEmployee->doj;
            $child_user->cab_facility = $NewEmployee->cab_facility;
            $child_user->employement_type = $designation->title;
            $child_user->worked_for = $NewEmployee->worked_for;
            $child_user->marital_status = $NewEmployee->marital_status;
            $NewEmployee->status = $request->current_status;
            $child_user->company_name = $NewEmployee->company_name;
            $child_user->shift_type = $NewEmployee->shift_type;
            $child_user->employee_shift = $request->employee_shift ?? $shift->name;
            $child_user->user_type = "0";                      /////////////////
            $child_user->blood_group = $NewEmployee->blood_group;
            $NewEmployee->nationality =  $request->country;

            $child_user->database_path = env("DB_HOST");
            $child_user->database_name = $temp_DB_name;
            $child_user->database_username = env("DB_USERNAME");
            $child_user->database_password = env("DB_PASSWORD");
            if ($makeEmployee->profile_image) {
                $child_user->profile_image = $profilefilename;
            }
            $child_user->password = $NewEmployee->password;
           // $child_user->save();
            $getRole =  'carer';

            $admin = Role::where('name', $getRole)->first();

            if ($child_user->save()) {
                if (!$child_user->hasRole($getRole)) {
                    $child_user->roles()->attach($admin);
                }
            }

            $child_staff = new SubUser();
            $child_staff->id = $NewEmployee->id;
            $child_staff->first_name = $NewEmployee->first_name;
            $child_staff->last_name = $NewEmployee->last_name;
            $child_staff->unique_id = $NewEmployee->unique_id;
            $child_staff->email = $NewEmployee->email;
            $child_staff->phone = $NewEmployee->phone;
            $child_staff->mobile = $NewEmployee->mobile;
            $child_staff->gender = $NewEmployee->gender;
            $child_staff->dob = $NewEmployee->dob;
            $child_staff->cab_facility = $NewEmployee->cab_facility;
            $child_staff->doj = $NewEmployee->doj;
            $child_staff->worked_for = $NewEmployee->worked_for;
            $child_staff->marital_status = $NewEmployee->marital_status;
            $NewEmployee->status = $request->current_status;
            $child_staff->company_name = $NewEmployee->company_name;
            $child_staff->employement_type = $designation->title;
            $child_staff->blood_group = $NewEmployee->blood_group;
            $child_staff->database_path = env("DB_HOST");
            $child_staff->database_name = $temp_DB_name;
            $child_staff->database_username = env("DB_USERNAME");
            $child_staff->database_password = env("DB_PASSWORD");
            $child_staff->shift_type = $NewEmployee->shift_type;
            $child_staff->employee_shift = $request->employee_shift ?? $shift->name;
            $NewEmployee->nationality =  $request->country;
            $child_staff->user_type = "0";
            $child_staff->password = $NewEmployee->password;
            if ($makeEmployee->profile_image) {
                $child_staff->profile_image = $profilefilename;
            }

            $getRole = 'carer';
            $admin = Role::where('name', $getRole)->first();

            if ($child_staff->save()) {
                if (!$child_staff->hasRole($getRole)) {
                    $child_staff->roles()->attach($admin);
                }
            }

            // ------------------- Save Salary Info --------------------
            if ($child_staff->id) {
                // If you have a salary calculator method, replace this block with that logic
                $basic = $request->basic ?? 0;
                $hra = $request->hra ?? 0;
                $medical = $request->medical ?? 0;
                $conveyance = $request->conveyance ?? 0;
                $bonus = $request->bonus ?? 0;
                $gross_salary = $request->gross_salary ?? 0;
                $professional_tax = $request->professional_tax ?? 0;
                $epf_employee = $request->epf_employee ?? 0;
                $esi_employee = $request->esi_employee ?? 0;
                $take_home = $request->take_home ?? 0;
                $epf_employer = $request->epf_employer ?? 0;
                $esi_employer = $request->esi_employer ?? 0;
                $total_package_salary = $request->total_package_salary ?? 0;

                EmployeeSalary::create([
                    'employee_id' => $child_staff->id,
                    'basic' => $basic,
                    'hra' => $hra,
                    'medical' => $medical,
                    'conveyance' => $conveyance,
                    'bonus' => $bonus,
                    'gross_salary' => $gross_salary,
                    'professional_tax' => $professional_tax,
                    'epf_employee' => $epf_employee,
                    'esi_employee' => $esi_employee,
                    'take_home' => $take_home,
                    'epf_employer' => $epf_employer,
                    'esi_employer' => $esi_employer,
                    'total_package_salary' => $total_package_salary,
                ]);
            }
            //***** Save Salary Info End   *****//

            $this->data["detais"] = [
                "email" => $makeEmployee->email,
                "pass" => $rand,
                "unique_id" => $NewEmployee->unique_id,
                "first_name" => $NewEmployee->first_name
            ];
            $email = $makeEmployee->email;
            $subject = "Welcome to Unify Technology Successfully Created";
            Mail::to($email)->send(new SendMailToUser($this->data["detais"], $subject));

            //  *** user address
            $sub_user = SubUser::find($child_staff->id);
            if ($sub_user) {

                if ($validatedData['cab_facility'] == 1) {
                    $sub_new_address = new SubUserAddresse();
                    $sub_new_address->sub_user_id = $sub_user->id;
                    $sub_new_address->address = $request->pickup_address;
                    $sub_new_address->latitude = $request->latitude;
                    $sub_new_address->longitude = $request->longitude;
                    $sub_new_address->start_date = date('Y-m-d');
                    $sub_new_address->save();
                }
                else{
                    $sub_new_address = new SubUserAddresse();
                    $sub_new_address->sub_user_id = $sub_user->id;
                    $sub_new_address->address = $validatedData['address'];
                    $sub_new_address->latitude = null;
                    $sub_new_address->longitude = null;
                    $sub_new_address->start_date = date('Y-m-d');
                    $sub_new_address->save();
                }

            }
            //  *** user address end

            //  *** user information
            $userInfo =  new UserInfo;
            $userInfo->user_id = $NewEmployee->id;
            $userInfo->new_applicant_id = $request->new_applicant_id;
            $userInfo->parmanent_address = $request->parmanent_address;
            $userInfo->country = $request->country;
            $userInfo->state = $request->state;
            $userInfo->city = $request->city;
            $userInfo->assign_pc = $request->assign_pc;
            $userInfo->sallary = $request->sallary;
            $userInfo->father_name = $request->father_name;
            $userInfo->mother_name = $request->mother_name;
            $userInfo->spouse_name = $request->spouse_name;
            $userInfo->no_of_childern = $request->no_of_childern;
            $userInfo->documented_birthday = $request->documented_birthday;
            $userInfo->qualification = $request->qualification;
            $userInfo->induction_status = $request->induction_status;
            $userInfo->reporting_leader = $request->reporting_leader;
            $userInfo->interview_souce = $request->interview_souce;
            $userInfo->referal_by = $request->referal_by;
            //$userInfo->referral_employee_id = $request->referral_employee_id;
            $userInfo->referral_code = $request->referral_code;
            $userInfo->aadhar_card_number = $request->aadhar_card_number;
            $userInfo->PAN_card_number = $request->PAN_card_number;
            $userInfo->voter_id = $request->voter_id;
            $userInfo->driving_lincense = $request->driving_lincense;
            $userInfo->account_name = $request->account_name;
            $userInfo->account_number = $request->account_number;
            $userInfo->IFSC_code = $request->IFSC_code;
            $userInfo->reason = $request->reason;
            $userInfo->remark = $request->remark;
            $userInfo->PF_status = $request->PF_status;
            $userInfo->PF_no = $request->pf_no;
            $userInfo->relieving_letter = $request->relieving_letter;
            $userInfo->FNF = $request->FNF;
            $userInfo->UAN_no = $request->UAN_no;
            $userInfo->assets = $request->assets;
            $userInfo->recovery = $request->recovery;
            $userInfo->genious_employee_code = $request->genious_employee_code;
            $userInfo->salary_cycle = $request->salary_cycle;
            $userInfo->age_in_year = $request->age_in_year;
            $userInfo->skills = $request->skills ? implode(',', $request->skills) : "";
            $userInfo->experience = $request->experience;
            $userInfo->company_email = $request->company_email;
            $userInfo->drug_policy = $request->drug_policy;
            $userInfo->transport_policy = $request->transport_policy;
            $userInfo->laptop_phone_policy = $request->laptop_phone_policy;
            $userInfo->IJP_policy = $request->IJP_policy;
            $userInfo->appraisal_policy = $request->appraisal_policy;
            $userInfo->department = $request->department;
            $userInfo->id_card_receive = $request->id_card_receive;
            $userInfo->save();
             //  *** user information   end

            if (!empty($validatedData['assign_employee_position'])) {
                $role = HrmsRole::with('viewrole')->find($validatedData['assign_employee_position']);

                HrmsEmployeeRole::updateOrCreate(
                    ['employee_id' => $child_staff->id],
                    ['role_id' => $role->id, 'employee_id' => $child_staff->id],
                );

                if ($role->viewrole->name == "Employee View") {
                    $team = HrmsTeam::find($validatedData['assign_team']);
                    if ($team) {
                        HrmsTeamMember::create([
                            'hrms_team_id' => $validatedData['assign_team'],
                            'member_id' => $child_staff->id,
                        ]);
                    }
                }

            }

            $fullName = trim(($makeEmployee->first_name ?? '') . ' ' . ($makeEmployee->last_name ?? ''));
            $changed = $fullName . " has been converted to an employee.";
            UpdateApplicantHistory::create([
                'applicant_id' => $makeEmployee->id,
                'updated_by' => auth('sanctum')->id(),
                'notes' =>  "Converted applicant to employee",
                'date' => now()->format('Y-m-d'),
                'time' => now()->format('H:i:s'),
                'changed' => $changed,
            ]);

            return $this->successResponse( [], "Employee Created Successfully");

        } catch (\Throwable $th) {
            return $this->errorResponse($th->getMessage());
        }

     }





     /**
     * @OA\post(
     * path="/uc/api/new_applicant/candidates/emailList",
     * operationId="emailList",
     * tags={"New Applicant Templates"},
     * summary="Get emailList",
     *   security={ {"Bearer": {} }},
     * description="Get emailList",
     *     @OA\RequestBody(
     *         @OA\JsonContent(),
     *         @OA\MediaType(
     *            mediaType="multipart/form-data",
     *            @OA\Schema(
     *               type="object",
     *              @OA\Property(property="search", type="string"),
     *              @OA\Property(property="filter", type="string"),
     *            ),
     *        ),
     *    ),
     *      @OA\Response(
     *          response=201,
     *          description="Email List Get Successfully",
     *          @OA\JsonContent()
     *       ),
     *      @OA\Response(
     *          response=200,
     *          description="Email List Get Successfully",
     *          @OA\JsonContent()
     *       ),
     *      @OA\Response(
     *          response=422,
     *          description="Unprocessable Entity",
     *          @OA\JsonContent()
     *       ),
     *      @OA\Response(response=400, description="Bad request"),
     *      @OA\Response(response=404, description="Resource Not Found"),
     * )
     */


     public function applicantEmaillist(Request $request){

        try {

            $search = $request->input('search');

            $applicantEmaillist = HrmsEmployeeEmail::where(function ($query) use ($search) {
                    $query->where('email', 'like', "%{$search}%")
                          ->orWhere('template_name', 'like', "%{$search}%")
                          ->orWhere('email_title', 'like', "%{$search}%");
                })->with('user') ->orderBy('id', 'desc')->paginate(HrmsEmployeeEmail::PAGINATE);


           //return $this->successResponse($applicantEmaillist, 'Candidate Details');
           return response()->json([
            'success' => true,
            'message' => 'Applicant email list',
            'url'=> url('public/hiring_pdfs'),
            'data'=>$applicantEmaillist,
           ],200);

        } catch (\Throwable $th) {
            return $this->errorResponse($th->getMessage());
        }
     }





     /**
     * @OA\post(
     * path="/uc/api/new_applicant/candidates/applicantScheduledInterviewList",
     * operationId="applicantScheduledInterviewList",
     * tags={"New Applicant Templates"},
     * summary="Get applicant Scheduled Interview List",
     *   security={ {"Bearer": {} }},
     * description="Get emailList",
     *     @OA\RequestBody(
     *         @OA\JsonContent(),
     *         @OA\MediaType(
     *            mediaType="multipart/form-data",
     *            @OA\Schema(
     *               type="object",
     *              @OA\Property(property="month", type="date", description="filter for month"),
     *            ),
     *        ),
     *    ),
     *      @OA\Response(
     *          response=201,
     *          description="applicant Scheduled Interview List Get Successfully",
     *          @OA\JsonContent()
     *       ),
     *      @OA\Response(
     *          response=200,
     *          description="applicant Scheduled Interview List Get Successfully",
     *          @OA\JsonContent()
     *       ),
     *      @OA\Response(
     *          response=422,
     *          description="Unprocessable Entity",
     *          @OA\JsonContent()
     *       ),
     *      @OA\Response(response=400, description="Bad request"),
     *      @OA\Response(response=404, description="Resource Not Found"),
     * )
     */


    public function applicantScheduledInterviewList(Request $request)
    {
        try {
            $month = $request->month ? Carbon::parse($request->month)->format('m') : now()->format('m');
            $startOfMonth = $request->month ? Carbon::parse($request->month)->startOfMonth() : now()->startOfMonth();
            $endOfMonth = $request->month ? Carbon::parse($request->month)->endOfMonth() : now()->endOfMonth();

            $user_id = auth('sanctum')->user()->id;
            $admin = 0;
            $is_manager = 0;

            $getTeamMembers = SubUser::with('hrmsroles.viewrole')->find($user_id);
            if ($getTeamMembers && isset($getTeamMembers->hrmsroles[0])) {
                $role_view = $getTeamMembers->hrmsroles[0]->viewrole->name;
                if ($role_view == 'Admin View') {
                    $admin = 1;
                } elseif (in_array($role_view, ['Manager View', 'Manager Not Attendance View'])) {
                    $is_manager = 1;
                }
            }

            $auth_role = DB::table('role_user')->where('user_id', $user_id)->first();
            $employeeRole = DB::table('roles')->find($auth_role->role_id);
            if ($employeeRole && $employeeRole->name == "admin") {
                $admin = 1;
            }

            // ---------------- Count Logic (Using updated_at) --------------------
            if ($is_manager == 1) {
                $team_manager = EmployeeTeamManager::where('employee_id', $user_id)->first();
                $countsApplicants = $team_manager
                    ? NewApplicant::where('manager_id', $team_manager->team_manager_id)
                        ->whereBetween('updated_at', [$startOfMonth, $endOfMonth])
                        ->get()
                    : collect([]);
            } elseif ($admin == 1) {
                $countsApplicants = NewApplicant::whereBetween('updated_at', [$startOfMonth, $endOfMonth])->get();
            } else {
                $countsApplicants = collect([]);
            }

            $applicantCount = 0;
            $new_applicant = 0;
            $rejected = 0;
            $featur_refrence = 0;
            $offered = 0;
            $employee = 0;
            $progress = 0;

            foreach ($countsApplicants as $applicant) {
                if ($applicant->stages == 0 && $applicant->is_rejected != 1 && $applicant->is_offered != 1 && $applicant->is_feature_reference != 1 && $applicant->is_employee != 1 && $applicant->quiz_status != 0) {
                    $new_applicant++;
                    $applicantCount++;
                }
                if ($applicant->is_rejected == 1) {
                    $rejected++;
                    $applicantCount++;
                }
                if ($applicant->is_feature_reference == 1 && $applicant->is_employee != 1 && $applicant->is_rejected != 1) {
                    $featur_refrence++;
                    $applicantCount++;
                }
                if (($applicant->stages == 4 || $applicant->is_offered == 1) && $applicant->is_employee != 1 && $applicant->is_feature_reference != 1 && $applicant->is_rejected != 1) {
                    $offered++;
                    $applicantCount++;
                }
                if ($applicant->is_employee == 1) {
                    $employee++;
                    $applicantCount++;
                }
                if ($applicant->is_employee != 1 && $applicant->stages < 4 && $applicant->is_feature_reference != 1
                    && $applicant->stages != 0 && $applicant->is_rejected != 1 && $applicant->is_offered != 1) {
                    $progress++;
                    $applicantCount++;
                }
            }

            // ---------------- Scheduled Logic (Using reason_history Scheduled Date) --------------------
            if ($is_manager == 1) {
                $team_manager = EmployeeTeamManager::where('employee_id', $user_id)->first();
                $allApplicants = $team_manager
                    ? NewApplicant::where('manager_id', $team_manager->team_manager_id)
                        ->orderBy('created_at', 'DESC')
                        ->get()
                    : collect([]);
            } elseif ($admin == 1) {
                $allApplicants = NewApplicant::orderBy('created_at', 'DESC')->get();
            } else {
                $allApplicants = collect([]);
            }

            $scheduledData = [];

            foreach ($allApplicants as $applicant) {
                if (!empty($applicant->reason_history)) {
                    foreach ($applicant->reason_history as $history) {
                        if (!empty($history['Scheduled'])) {
                            $scheduledDate = Carbon::parse($history['Scheduled'])->format('Y-m-d');
                            if (Carbon::parse($scheduledDate)->between($startOfMonth, $endOfMonth)) {
                                if (!isset($scheduledData[$scheduledDate])) {
                                    $scheduledData[$scheduledDate] = [];
                                }
                                $applicantData = $applicant->toArray();
                                $applicantData['schedule_completed'] = $history['scheduledcompleted'] ?? false;
                                $applicantData['rescheduled'] = $history['Rescheduled'] ?? false;
                                $applicantData['scheduled_time'] = $history['ScheduledTime'] ?? null;
                                $scheduledData[$scheduledDate][] = $applicantData;
                            }
                        }
                    }
                }
            }

            // ---------------- Final Response Build --------------------
            $allScheduledData = [
                'Applicant' => $applicantCount,
                'Feature_refrence' => $featur_refrence,
                'Rejected' => $rejected,
                'Offered' => $offered,
                'Employees' => $employee,
                'Progress' => $progress,
                'new_applicant' => $new_applicant,
            ];

            $currentDate = $startOfMonth->copy();
            while ($currentDate->lte($endOfMonth)) {
                $date = $currentDate->format('Y-m-d');
                $allScheduledData[$date] = $scheduledData[$date] ?? [];
                $currentDate->addDay();
            }

            return $this->successResponse(
                $allScheduledData,
                "Scheduled Calendar Data"
            );
        } catch (\Throwable $th) {
            return $this->errorResponse($th->getMessage());
        }
    }





    /**
     * @OA\post(
     * path="/uc/api/new_applicant/candidates/applicantNextRound/{id}",
     * operationId="nextCandidates",
     * tags={"New Applicant Templates"},
     * summary="Update Candidates Request",
     *   security={ {"Bearer": {} }},
     * description="Update Candidates Request",
     *    @OA\Parameter(
     *     name="id",
     *     in="path",
     *     required=true,
     *     @OA\Schema(type="integer")
     *    ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 type="object",
     *                 @OA\Property(property="stages", type="integer", description="stages preference (1 to 5 stages)"),
     *                 @OA\Property(property="is_feature_reference", type="integer", description="Refer to Feature Reference"),
     *                 @OA\Property(property="is_accepted", type="integer", description="accept to second round"),
     *                 @OA\Property(property="reason", type="integer", description="Reason for accepted "),
     *                 @OA\Property(property="reminder_date", type="date", description="date for next round interview"),
     *                 @OA\Property(property="send_email", type="integer", description="1 => send email applicant for informed second round ,  else => 0"),
     *                 @OA\Property(property="template_id", type="integer", description="Type of template for send email"),
     *                 @OA\Property(property="is_rejected", type="integer"),
     *                 @OA\Property(property="is_reminder", type="integer"),
     *                 @OA\Property(property="is_offered", type="integer"),
     *                 @OA\Property(property="stage", type="integer"),
     *                 @OA\Property(property="header_image_scale", type="integer"),
     *                 @OA\Property(property="foter_image_scale", type="integer"),
     *             )
     *         )
     *     ),
     *      @OA\Response(
     *          response=201,
     *          description="Designation Updated Successfully",
     *          @OA\JsonContent()
     *       ),
     *      @OA\Response(
     *          response=200,
     *          description="Designation Updated Successfully",
     *          @OA\JsonContent()
     *       ),
     *      @OA\Response(
     *          response=422,
     *          description="Unprocessable Entity",
     *          @OA\JsonContent()
     *       ),
     *      @OA\Response(response=400, description="Bad request"),
     *      @OA\Response(response=404, description="Resource Not Found"),
     * )
     */


    public function applicantNextRound(Request $request, $id)
    {
        try {

            $validator = \Validator::make($request->all(), [
                'header_image' => ['nullable'],
                'footer_image' => ['nullable'],
                'watermark'    => ['nullable'],
            ]);

            $validator->sometimes('header_image', 'file|mimes:jpeg,png,jpg,gif,svg|max:1024', function ($input) {
                return request()->hasFile('header_image');
            });
            $validator->sometimes('footer_image', 'file|mimes:jpeg,png,jpg,gif,svg|max:1024', function ($input) {
                return request()->hasFile('footer_image');
            });
            $validator->sometimes('watermark', 'file|mimes:jpeg,png,jpg,gif,svg|max:1024', function ($input) {
                return request()->hasFile('watermark');
            });

            if ($validator->fails()) {
                return $this->errorResponse($validator->errors()->first());
            }


            $today = now()->format('Y-m-d');
            $send_email = $request->send_email;
            $is_reminder = $request->is_reminder;
            $reminderDate =  isset($request->reminder_date) ? Carbon::parse($request->reminder_date)->format('Y-m-d') : "";
            $scheduleTime = !empty($request->reminder_time) && strtotime($request->reminder_time) !== false ? Carbon::parse($request->reminder_time)->format('H:i') : "";
            $requestData = $request->all();

            $header_img_width_height = (int) ($request->header_image_scale ?? 40);
            $footer_img_width_height = (int) ($request->footer_image_scale ?? 40);

            $reason = isset($request->reason) ? $request->reason : "";

            $findCandidateDetail = NewApplicant::find($id);

            ////

             if ($findCandidateDetail->stages == 0 && $request->stages == 1) {
                 $oldStage = "Applicant";
                 $newStage = "In Progress";
             }elseif ($findCandidateDetail->stages == 1 && $request->stages == 2) {
                 $oldStage = "First round";
                 $newStage = "Second round";
             }elseif ($findCandidateDetail->stages == 2 && $request->stages == 3) {
                 $oldStage = "Second round";
                 $newStage = "Third round";
             }elseif ($findCandidateDetail->stages == 3 && $request->stages == 4) {
                 $oldStage = "Third round";
                 $newStage = "Forth round";
             }else {
                 $oldStage = $findCandidateDetail->stages;
                 $newStage = $request->stages;
             }
             $fullName = trim(($findCandidateDetail->first_name ?? '') . ' ' . ($findCandidateDetail->last_name ?? ''));
             $changed = $fullName . " has been moved to next round. $oldStage to $newStage.";
             ////

                if ($findCandidateDetail) {

                    $status = ["In Progress"];
                    if ($findCandidateDetail->stages == 1) {
                        $status = ["Accepted", "In Progress"];
                    }
                    if (isset($request->is_rejected)) {
                        if ($request->is_rejected == 1) {
                            ////
                            $changed = $fullName . " has been rejected.";
                            ////
                            $status = ["Rejected", "Canceled"];
                            $findCandidateDetail->is_rejected = 1;

                             $reasonHistory = $findCandidateDetail->reason_history ?? [];
                                if (!empty($reasonHistory)) {
                                    $currentDate = new DateTime(); // Get current date

                                    foreach ($reasonHistory as &$entry) {
                                        if (!empty($entry['Scheduled'])) {
                                            try {
                                                $scheduledDate = new DateTime($entry['Scheduled']);
                                                if ($scheduledDate->format('Y-m-d') >= $currentDate->format('Y-m-d')) {
                                                    $entry['Scheduled'] = null; // Set to null if current/future date
                                                    $entry['ScheduledTime'] = null;
                                                }
                                            } catch (Exception $e) {
                                                $entry['Scheduled'] = null;
                                                $entry['ScheduledTime'] = null;
                                            }
                                        }
                                        if (!empty($entry['Rescheduled'])) {
                                            try {
                                                    $entry['Rescheduled'] = null; // Set to null if current/future date
                                            } catch (Exception $e) {
                                                $entry['Rescheduled'] = null;
                                            }
                                        }

                                    }
                                    unset($entry); // Break reference

                                    $findCandidateDetail->reason_history = $reasonHistory; // Update data
                                }

                        }
                        if ($request->is_rejected == 0) {
                             ////
                            $changed = $fullName . " has been UnRejected.";
                            ////
                            $status = ["Rejected", "Un Rejected"];
                            $findCandidateDetail->is_rejected = 0;
                        }
                    }
                    if (isset($request->is_feature_reference)) {
                        if ($request->is_feature_reference == 1) {
                             ////
                            $changed = $fullName . " has been moved to future reference.";
                            ////
                            $status = ["future Reference", "On Hold"];
                            $findCandidateDetail->is_feature_reference = 1;

                            $reasonHistory = $findCandidateDetail->reason_history ?? [];
                                if (!empty($reasonHistory)) {
                                    $currentDate = new DateTime(); // Get current date

                                    foreach ($reasonHistory as &$entry) {
                                        if (!empty($entry['Scheduled'])) {
                                            try {
                                                $scheduledDate = new DateTime($entry['Scheduled']);
                                                if ($scheduledDate->format('Y-m-d') >= $currentDate->format('Y-m-d')) {
                                                    $entry['Scheduled'] = null; // Set to null if current/future date
                                                    $entry['ScheduledTime'] = null;
                                                }
                                            } catch (Exception $e) {
                                                $entry['Scheduled'] = null;
                                                $entry['ScheduledTime'] = null;
                                            }
                                        }
                                        if (!empty($entry['Rescheduled'])) {
                                            try {
                                                    $entry['Rescheduled'] = false; // Set to null if current/future date
                                            } catch (Exception $e) {
                                                $entry['Rescheduled'] = false;
                                            }
                                        }

                                    }
                                    unset($entry); // Break reference

                                    $findCandidateDetail->reason_history = $reasonHistory; // Update data
                                }

                        }else {
                            $changed = $fullName . " has been removed to future reference.";  /////
                            $status = ["future Reference", "Removed to future refrence"];
                            $findCandidateDetail->is_feature_reference = 0;
                        }

                    }
                    if (isset($request->is_offered)) {
                        if ($request->is_offered == 1) {
                            ////
                            $changed = $fullName . " has been moved to the offered and selected list."; /////
                            ////
                            $status = ["offered", "Selected"];
                            $findCandidateDetail->is_offered = 1;

                            ////  Offered History   /////
                             do {
                                    $uniqueID = mt_rand(100000, 999999);
                                } while (ApplicantOfferedHistory::where('unique_id', $uniqueID)->exists());
                                ApplicantOfferedHistory::create([
                                    'applicant_id' => $findCandidateDetail->id,
                                    'unique_id' => $uniqueID,
                                    'date' => now()->format('Y-m-d'),
                                    'offered_salary' => $request->offered_salary ?? null,
                                    'joining_date' => $request->joining_date ?? null,
                                    'joining_time' => $request->joining_time ?? null,
                                ]);
                        }
                        if ($request->is_offered == 0) {
                                ////
                                $changed = $fullName . " has been removed from the offered list.";
                                ////
                                $status = ["UnOffered", "Removed to offered list"];
                                $findCandidateDetail->is_offered = 0;
                        }
                    }

                    if ($request->application_state == 'schedule' || (isset($request->reminder_date) && isset($request->reminder_time))) {
                        //$changed = $fullName . " has been scheduled for an interview.";
                        $changed = $fullName . " has been scheduled for an interview on " . $request->reminder_date . " at " . $request->reminder_time . ".";
                        if ($findCandidateDetail->stages < $request->stages) {
                             $changed .= "And Moved to next round. Stage changed from " . $oldStage . " to " . $newStage . ".";
                        }
                        //if ($request->application_state == 'schedule') {
                            $status = ["Interview", "schedule"];
                       // }
                    }

                    if (empty($reason)) {
                        $reason_history = [ 'status' => $status, 'date' => $today, 'Scheduled' => $reminderDate, 'ScheduledTime'=> $scheduleTime, 'Rescheduled'=> false];
                    }else {
                        $reason_history = ['status' => $status, 'reason' => $reason, 'date' => $today, 'Scheduled' => $reminderDate, 'ScheduledTime'=> $scheduleTime, 'Rescheduled'=> false];
                    }


                    $reasonHistory = $findCandidateDetail->reason_history ?? [];

                    if (!empty($reasonHistory)) {

                        $lastIndex = count($reasonHistory) - 1;

                        if (!empty($reasonHistory[$lastIndex]['Scheduled'])) {
                            $reasonHistory[$lastIndex]['scheduledcompleted'] = true;
                        }else {
                            $reasonHistory[$lastIndex]['scheduledcompleted'] = false;
                        }
                    }

                    $reasonHistory[] = $reason_history;
                    $findCandidateDetail->reason_history = $reasonHistory;

                    $findCandidateDetail->stages = $request->stages;
                 if ($request->render_switch == 1) {
                    $findCandidateDetail->sent_mail = 1; // Mail sent
                } else {
                        $findCandidateDetail->sent_mail = 0; // Mail not sent
                }

                    $findCandidateDetail->save();

                    $content = $request->mail_content;

                    if ($request->render_switch == 1) {

                        if ($request->is_offered == 1 || $request->stages == 4) {
                            # code...


                                if ($request->hasFile('header_image')) {
                                    $header_imgName = time() . "_" . $request->file('header_image')->getClientOriginalName();
                                    $headerPath = $request->file('header_image')->move(public_path('hiringtemplate/header'), $header_imgName);
                                    $headerAbsolutePath = str_replace('\\', '/', $headerPath->getRealPath());

                                }elseif (!$request->hasFile('header_image') && !empty($request->header_image)) {
                                    $headerAbsolutePath = $request->header_image;
                                }else {
                                    $headerAbsolutePath = null;
                                }

                                if (isset($headerAbsolutePath) && !empty($headerAbsolutePath)) {

                                    $headerPosition = $request->input('headerImagePosition', 2);
                                    $headerAlign = '';
                                    if ($headerPosition == 1) {
                                        $headerAlign = 'text-align: left;';
                                    } elseif ($headerPosition == 2) {
                                        $headerAlign = 'text-align: center;';
                                    } elseif ($headerPosition == 3) {
                                        $headerAlign = 'text-align: right;';
                                    }

                                    $headerTag = '<div style="' . $headerAlign . '">
                                    <img src="'.$headerAbsolutePath.'" style="object-fit: contain; max-width:'.$header_img_width_height.'px; height:'.$header_img_width_height.'px;" /></div>';
                                    $content = $headerTag . $content;
                                }

                                if ($request->hasFile('footer_image')) {

                                    $footer_imgName = time() . "_" . $request->file('footer_image')->getClientOriginalName();
                                    $footerPath = $request->file('footer_image')->move(public_path('hiringtemplate/footer'), $footer_imgName);
                                    $footerAbsolutePath = str_replace('\\', '/', $footerPath->getRealPath());

                                }elseif (!$request->hasFile('footer_image') && !empty($request->footer_image)) {
                                    $footerAbsolutePath  = $request->footer_image;
                                }else {
                                    $footerAbsolutePath = null;
                                }

                                if (isset($footerAbsolutePath) && !empty($footerAbsolutePath)) {

                                    $footerPosition = $request->input('footerImagePosition', 2); // Default to center if not provided
                                    // Set text-align based on the position
                                    $footerAlign = '';
                                    if ($footerPosition == 1) {
                                        $footerAlign = 'text-align: left;';
                                    } elseif ($footerPosition == 2) {
                                        $footerAlign = 'text-align: center;';
                                    } elseif ($footerPosition == 3) {
                                        $footerAlign = 'text-align: right;';
                                    }
                                    $footerTag = '<div style="' . $footerAlign . ' margin-top:20px;">
                                    <img src="'.$footerAbsolutePath.'" style="object-fit: contain; max-width:'.$footer_img_width_height.'px; height:'.$footer_img_width_height.'px;" /></div>';
                                    $content = $content . $footerTag;
                                }

                                /// watermark image
                                if ($request->hasFile('watermark')) {

                                    $watermark_imgName = time() . "_" . $request->file('watermark')->getClientOriginalName();
                                    $watermarkPath = $request->file('watermark')->move(public_path('hiringtemplate/watermark'), $watermark_imgName);

                                    $watermarAbsolutePath = str_replace('\\', '/', $watermarkPath->getRealPath());
                                    $watermarkData = base64_encode(file_get_contents($watermarAbsolutePath));
                                    $mimeType = mime_content_type($watermarAbsolutePath);
                                    $imageSource = "url(data:$mimeType;base64,$watermarkData)";

                                }elseif (!$request->hasFile('watermark') && !empty($request->watermark)) {
                                    $watermarAbsolutePath = $request->watermark;
                                    $imageSource = "url('$watermarAbsolutePath')";

                                }else {
                                    $imageSource = null;
                                }

                                if (isset($imageSource) && !empty($imageSource)) {
                                    $rotationDegree = $request->input('watermarkPosition', 0); // rotation in degrees
                                    $watermarkOpacity = $request->input('watermarkOpacity', 0.2); // between 0.0 and 1.0
                                    $content = '
                                                <div style="position: relative; width: 100%; height: 100%; font-family: Arial, Helvetica, sans-serif;">

                                                    <div style="
                                                        position: absolute;
                                                        top: 0;
                                                        left: 0;
                                                        width: 100%;
                                                        min-height: 100vh
                                                        background-image: '.$imageSource.';
                                                        background-repeat: no-repeat;
                                                        background-position: center;
                                                        background-size: contain;
                                                        opacity: ' . $watermarkOpacity . ';
                                                        transform: rotate(' . $rotationDegree . 'deg);
                                                        z-index: -1;
                                                        pointer-events: none;
                                                        page-break-inside: avoid; /* Prevent the watermark from being split across pages */
                                                    ">
                                                    ' . $content . '
                                                    </div>

                                                </div>';

                                }




                                //////    WaterMark Image End ///
                        }

                        if ($request->is_offered == 1 || $request->stages == 4) {
                                $database =  base64_encode(DB::connection()->getDatabaseName());
                                $applicantId = base64_encode($id);
                                $accept = base64_encode("Accept");
                                $decline = base64_encode("Decline");
                                $uniqueID = base64_encode($uniqueID);

                                $acceptUrl = $applicantId.'/'.$accept.'/'.$database.'/'.$uniqueID;
                                $declineUrl = $applicantId.'/'.$decline.'/'.$database.'/'.$uniqueID;

                                // $acceptUrl = $applicantId.'/'.$accept.'/'.$database;
                                // $declineUrl = $applicantId.'/'.$decline.'/'.$database;

                                if (str_contains($content, '<span style="color: green;">Accept</span>')) {
                                    $acceptUrl = url('api/offeracceptandDecline/'.$acceptUrl);

                                    $content = str_replace(
                                        '<span style="color: green;">Accept</span>',
                                        '<a href="' . $acceptUrl . '" style="color: green; text-decoration: underline;">Accept</a>',
                                        $content
                                    );
                                }

                                if (str_contains($content, '<span style="color: red;">Decline</span>')) {
                                    $declineUrl = url('api/offeracceptandDecline/'.$declineUrl);

                                    $content = str_replace(
                                        '<span style="color: red;">Decline</span>',
                                        '<a href="' . $declineUrl . '" style="color: red; text-decoration: underline;">Decline</a>',
                                        $content
                                    );
                                }
                        }




                        $mailData = [
                            'subject' =>   "Interview next round",
                            'title' => '',
                            'body' =>   $content,
                        ];
                        $pdfFileName = null;
                            if ($request->is_offered == 1 || $request->stages == 4) {

                                    file_put_contents(storage_path('app/debug_content.html'), $content);

                                    $content = $this->removeEmojis($content);

                                    $zeroMarginStyle = '<style>
                                        h1, h2, h3, h4, h5, h6, div, p, span { margin: 0 !important; }
                                    </style>';
                                    $content = $zeroMarginStyle . $content;

                                    try {

                                            if (ob_get_length()) ob_clean();

                                            $pdf = Pdf::loadHTML($content)
                                                ->setOptions([
                                                    'isHtml5ParserEnabled' => true,
                                                    'isRemoteEnabled' => true,
                                                    'chroot' => public_path(),
                                                    'enable_php' => true,
                                                    'defaultFont' => 'dejavu sans',
                                                    'isFontSubsettingEnabled' => true,
                                                    'debugKeepTemp' => false, // Disable debug
                                                    'debugCss' => false, // Disable debug
                                                    'debugLayout' => false, // Disable debug
                                                    'debugPng' => false, // Disable debug
                                                    'fontHeightRatio' => 0.8,
                                                    'dpi' => 96,
                                                    'isPhpEnabled' => true,
                                                    'isJavascriptEnabled' => false,
                                                    'enable_remote' => true, // For external images
                                                ])
                                                ->output();
                                            } catch (\Exception $e) {
                                            return $this->errorResponse('PDF generation failed: ' . $e->getMessage());
                                            }

                                    //////////////
                                    $email = $findCandidateDetail->email;

                                    $pdfFileName = 'hiring-letter'. time(). '.pdf';
                                    $hiringMailData = [
                                        'content' => $content,
                                    ];

                                    // Define correct public folder path
                                    $pdfFilePath = public_path('hiring_pdfs/' . $pdfFileName);

                                    $directory = public_path('hiring_pdfs');
                                    if (!file_exists($directory) && !is_dir($directory)) {
                                        mkdir($directory, 0777, true);
                                    }

                                   // Save the PDF to the public folder
                                    Mail::to($email)
                                    ->send(
                                        (new HiringTemplateNotification($content))
                                            ->attachData($pdf, $pdfFileName,  [
                                                'mime' => 'application/pdf', // MIME type
                                            ])
                                    );

                            }else {

                                $content = $this->removeEmojis($content);

                                $zeroMarginStyle = '<style>
                                        h1, h2, h3, h4, h5, h6, div, p, span { margin: 0 !important; }
                                    </style>';
                                    $content = $zeroMarginStyle . $content;

                                $pdfFileName = null;
                                $email = $findCandidateDetail->email;
                                Mail::to($email) ->send( new HiringTemplateNotification($content));
                            }

                                 $emailData =[
                                    'user_id' => $id,
                                    'stages' => $request->stages ?? "",
                                    'email' => $email,
                                    'template_name' => "",
                                    'email_title' =>  "",
                                    'email_content' => $content,
                                    'email_image' => "",
                                    'email_pdf' => $pdfFileName,
                                    'send_date' => now()
                                ];


                                HrmsEmployeeEmail::create($emailData);


                    }

                    while (ob_get_level()) {
                        ob_end_clean();
                    }
                    ///////////////

                        UpdateApplicantHistory::create([
                            'applicant_id' => $id,
                            'updated_by' => auth('sanctum')->id(),
                            'notes' =>  "Converted applicant to employee",
                            'date' => now()->format('Y-m-d'),
                            'time' => now()->format('H:i:s'),
                            'changed' => $changed,
                        ]);
                    /////////////

                    return $this->successResponse(
                        new CandidatesResource($findCandidateDetail),
                        'Candidate Updated Successfully'
                    );
                }else {
                    return $this->validationErrorResponse("the given data is not found");
                }
        } catch (\Throwable $th) {
            return $this->errorResponse($th->getMessage());
        }
    }


    function removeEmojis($content) {
      // Emoji pattern using Unicode ranges
       return preg_replace('/[\x{1F600}-\x{1F64F}' .  // Emoticons
                        '\x{1F300}-\x{1F5FF}' .  // Misc Symbols and Pictographs
                        '\x{1F680}-\x{1F6FF}' .  // Transport & Map
                        '\x{2600}-\x{26FF}' .    // Misc symbols
                        '\x{2700}-\x{27BF}' .    // Dingbats
                        '\x{1F900}-\x{1F9FF}' .  // Supplemental Symbols and Pictographs
                        '\x{1FA70}-\x{1FAFF}' .  // Symbols and Pictographs Extended-A
                        '\x{1F1E6}-\x{1F1FF}]/u', // Flags
                        '', $content);
    }




    /**
     * @OA\post(
     * path="/uc/api/new_applicant/candidates/reschedule",
     * operationId="CandidatesReschedule",
     * tags={"New Applicant Templates"},
     * summary="Reschedule Candidates Request",
     *   security={ {"Bearer": {} }},
     * description="Reschedule Candidates Request",
     *     @OA\RequestBody(
     *         @OA\JsonContent(),
     *         @OA\MediaType(
     *            mediaType="multipart/form-data",
     *            @OA\Schema(
     *               type="object",
     *              @OA\Property(property="applicant_id", type="integer"),
     *              @OA\Property(property="scheduled_date", type="date"),
     *              @OA\Property(property="new_scheduled_time", type="string", format="time" ),
     *              @OA\Property(property="reschedule_date", type="date"),
     *              @OA\Property(property="email_content", type="text", description="Email content for rescheduling"),
     *            ),
     *        ),
     *    ),
     *      @OA\Response(
     *          response=201,
     *          description="Candidates Rescheduled Successfully",
     *          @OA\JsonContent()
     *       ),
     *      @OA\Response(
     *          response=200,
     *          description="Candidates Rescheduled Successfully",
     *          @OA\JsonContent()
     *       ),
     *      @OA\Response(
     *          response=422,
     *          description="Unprocessable Entity",
     *          @OA\JsonContent()
     *       ),
     *      @OA\Response(response=400, description="Bad request"),
     *      @OA\Response(response=404, description="Resource Not Found"),
     * )
     */



    public function applicantReSchedule(Request $request){
             try {

                  $validator = Validator::make($request->all(), [
                     'applicant_id' => 'required|integer',
                     'scheduled_date' => 'required|date',
                     'new_scheduled_time' => 'required|date_format:H:i',
                     'reschedule_date' => 'required|date',
                     'email_content' => 'nullable|string',
                  ]);

                  if ($validator->fails()) {
                       return $this->errorResponse($validator->errors());
                  }

                $old_scheduled_date = $request->scheduled_date;
                $new_scheduled_date = $request->reschedule_date;
                $new_scheduled_time = $request->new_scheduled_time;
                $content = $request->email_content;

                $applicant = NewApplicant::find($request->applicant_id);

                if (!$applicant) {
                     return $this->errorResponse("The given data is not found");
                }

              $applicant = NewApplicant::find($request->applicant_id);
              $data = [];

               $reason_history = $applicant->reason_history;

                if (!empty($reason_history)) {
                    foreach ($reason_history as $key => $history) {
                        if (!empty($history['Scheduled']) && $history['Scheduled'] === $old_scheduled_date) {
                        $reason_history[$key]['Scheduled'] = $new_scheduled_date;
                        $reason_history[$key]['ScheduledTime'] = $new_scheduled_time;
                        $reason_history[$key]['Rescheduled'] = true;
                        }
                    }
                }
                $applicant->reason_history = $reason_history;
                $applicant->save();

                // ✅ Send reschedule email
                if (!empty($applicant->email)) {
                    Mail::to($applicant->email)->send(new HiringTemplateNotification($content));
                }

                $fullName = trim(($applicant->first_name ?? '') . ' ' . ($applicant->last_name ?? ''));
                $changed = $fullName . " interview rescheduled from " . $old_scheduled_date . " to " . $new_scheduled_date . " at " . $new_scheduled_time . ".";


                UpdateApplicantHistory::create([
                    'applicant_id' => $request->applicant_id,
                    'updated_by' => auth('sanctum')->id(),
                    'notes' =>  "Applicant interview rescheduled",
                    'date' => now()->format('Y-m-d'),
                    'time' => now()->format('H:i:s'),
                    'changed' => $changed,
                ]);

                return $this->successResponse(
                    $applicant,
                    "Rescheduled Interview List"
                );

             } catch (\Throwable $th) {
                 return $this->errorResponse($th->getMessage());
             }
    }

/**
 * @OA\Get(
 *     path="/uc/api/new_applicant/candidates/scheduledDashboardList",
 *     operationId="CandidatescheduledDashboardList",
 *     tags={"New Applicant Templates"},
 *     summary="Scheduled Dashboard Candidates List",
 *     security={{"Bearer": {}}},
 *     description="Scheduled Dashboard Candidates List",
 *     @OA\Parameter(
 *         name="month",
 *         in="query",
 *         description="Month in format YYYY-MM",
 *         required=false,
 *         @OA\Schema(type="date", format="date")
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Scheduled Interview List",
 *         @OA\JsonContent()
 *     ),
 *     @OA\Response(response=400, description="Bad request"),
 *     @OA\Response(response=404, description="Resource Not Found"),
 *     @OA\Response(response=422, description="Unprocessable Entity")
 * )
 */

 public function scheduledDashboardList(Request $request)
{
    try {
        $month = $request->month ? Carbon::parse($request->month)->format('m') : now()->format('m');
        $startOfMonth = $request->month ? Carbon::parse($request->month)->startOfMonth() : now()->startOfMonth();
        $endOfMonth = $request->month ? Carbon::parse($request->month)->endOfMonth() : now()->endOfMonth();

        // Get all applicants, no interview_date column used here
        $applicants = NewApplicant::all();

        $scheduledInterviews = [];

        foreach ($applicants as $applicant) {
            if (!empty($applicant->reason_history)) {
                foreach ($applicant->reason_history as $history) {
                    if (!empty($history['Scheduled'])) {
                        $scheduledDate = Carbon::parse($history['Scheduled']);

                        // Filter by month range
                        if ($scheduledDate->between($startOfMonth, $endOfMonth)) {
                            $scheduledTime = Carbon::parse($history['ScheduledTime']);
                            $date = $scheduledDate->format('Y-m-d');
                            $time = $scheduledTime->format('H:i');

                            $scheduledInterviews[] = [
                                'candidate_name' => $applicant->first_name . " " . $applicant->last_name ?? 'Unknown',
                                'designation' => $applicant->designation->title ?? 'Unknown',
                                'interview_time' => $time,
                                'interview_date' => $date,
                            ];
                        }
                    }
                }
            }
        }

        // Sort by interview_date
        usort($scheduledInterviews, function ($a, $b) {
            return strtotime($a['interview_date']) <=> strtotime($b['interview_date']);
        });

        return $this->successResponse(
            $scheduledInterviews,
            "Scheduled Interview List"
        );

    } catch (\Throwable $th) {
        return $this->errorResponse($th->getMessage());
    }
}

     /**
     * @OA\post(
     * path="/uc/api/new_applicant/candidates/assignManager",
     * operationId="assignManager",
     * tags={"New Applicant Templates"},
     * summary="assign Manager",
     *   security={ {"Bearer": {} }},
     * description="assign Manager",
     *     @OA\RequestBody(
     *         @OA\JsonContent(),
     *         @OA\MediaType(
     *            mediaType="multipart/form-data",
     *            @OA\Schema(
     *               type="object",
     *              @OA\Property(property="applicant_id", type="integer"),
     *              @OA\Property(property="manager_id", type="id"),
     *            ),
     *        ),
     *    ),
     *      @OA\Response(
     *          response=201,
     *          description="Candidates Assign Manager Successfully",
     *          @OA\JsonContent()
     *       ),
     *      @OA\Response(
     *          response=200,
     *          description="Candidates Assign Manager Successfully",
     *          @OA\JsonContent()
     *       ),
     *      @OA\Response(
     *          response=422,
     *          description="Unprocessable Entity",
     *          @OA\JsonContent()
     *       ),
     *      @OA\Response(response=400, description="Bad request"),
     *      @OA\Response(response=404, description="Resource Not Found"),
     * )
     */



     public function assignManager(Request $request){
          try {
               $validated = $request->validate([
                    'applicant_id' => 'required|integer',
                    'manager_id' => 'required|integer',
               ]);

              $applicant = NewApplicant::find($request->applicant_id);
              $applicant->manager_id = $request->manager_id;
              $applicant->save();

              $manager = TeamManager::find($request->manager_id); // Assuming managers are stored in `users` table

              $fullName = trim(($applicant->first_name ?? '') . ' ' . ($applicant->last_name ?? ''));
              $managerName = $manager->name;
              $changed = $fullName . " has been assigned to " . $managerName . ".";


                UpdateApplicantHistory::create([
                    'applicant_id' => $request->applicant_id,
                    'updated_by' => auth('sanctum')->id(),
                    'notes' =>  "Assigned manager to applicant",
                    'date' => now()->format('Y-m-d'),
                    'time' => now()->format('H:i:s'),
                    'changed' => $changed,
                ]);


              return response()->json([
                  'status' => true,
                  'message' => "Assign Manager To Applicant Successfully"
              ]);

          } catch (\Throwable $th) {
               return response()->json([
                   'status' => false,
                   'message' => $th->getMessage(),
               ]);
          }
     }



/**
 * @OA\Get(
 *     path="/uc/api/new_applicant/candidates/referralApplicantsList",
 *     operationId="referralApplicantsList",
 *     tags={"New Applicant Templates"},
 *     summary="Get Referral Applicants List",
 *     description="Returns a list of applicants who used the logged-in user's referral code",
 *     security={{ "Bearer": {} }},
 *
 *     @OA\Response(
 *         response=200,
 *         description="Referral Applicants List Fetched Successfully",
 *         @OA\JsonContent(
 *             type="object",
 *             @OA\Property(property="message", type="string", example="Referral applicants fetched successfully"),
 *             @OA\Property(
 *                 property="data",
 *                 type="array",
 *                 @OA\Items(
 *                     type="object",
 *                     @OA\Property(property="candidate_name", type="string", example="John Doe"),
 *                     @OA\Property(property="email", type="string", example="john@example.com"),
 *                     @OA\Property(property="referral_code", type="string", example="REF123XYZ")
 *                 )
 *             )
 *         )
 *     ),
 *
 *     @OA\Response(
 *         response=500,
 *         description="Internal Server Error",
 *         @OA\JsonContent(
 *             type="object",
 *             @OA\Property(property="message", type="string", example="Something went wrong while fetching referral applicants"),
 *             @OA\Property(property="error", type="string", example="Exception message here")
 *         )
 *     ),
 *
 *     @OA\Response(
 *         response=401,
 *         description="Unauthenticated"
 *     ),
 *
 *     @OA\Response(
 *         response=403,
 *         description="Forbidden"
 *     )
 * )
 */




    public function referralApplicantsList(Request $request)
    {
        try {
            $user = auth('sanctum')->user();

            // Lookup referral_code from Subuser table using the logged-in user's ID
            $subuser = Subuser::where('id', $user->id)->first();

            // Now find applicants who used this referral code
            $applicants = NewApplicant::where('referral_code', $subuser->referral_code)
                ->where('is_employee', 1)
                ->whereNotNull('referral_code')
                ->where('referral_code', '!=', null)
                ->get();

            $referralList = [];

            foreach ($applicants as $applicant) {
                $referralList[] = [
                    'candidate_name' => $applicant->first_name . ' ' . $applicant->last_name,
                    'email' => $applicant->email ?? 'Unknown',
                    'referral_code' => $applicant->referral_code ?? 'Unknown',
                ];
            }

            return response()->json([
                'message' => 'Referral applicants fetched successfully',
                'data' => $referralList,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Something went wrong while fetching referral applicants',
                'error' => $e->getMessage(),
            ], 500);
        }
    }






    /**
     * @OA\post(
     * path="/uc/api/new_applicant/candidates/reOffered",
     * operationId="reOffered",
     * tags={"New Applicant Templates"},
     * summary="Re Offered",
     *   security={ {"Bearer": {} }},
     * description="Re Offered",
     *     @OA\RequestBody(
     *         @OA\JsonContent(),
     *         @OA\MediaType(
     *            mediaType="multipart/form-data",
     *            @OA\Schema(
     *               type="object",
     *              @OA\Property(property="applicant_id", type="integer"),
     *              @OA\Property(property="reason", type="string"),
     *              @OA\Property(property="mail_content", type="string"),
     *              @OA\Property(property="header_image", type="string", format="binary"),
     *              @OA\Property(property="footer_image", type="string", format="binary"),
     *              @OA\Property(property="watermark", type="string", format="binary"),
     *              @OA\Property(property="headerImagePosition", type="integer", description="1 => left, 2 => center, 3 => right"),
     *              @OA\Property(property="footerImagePosition", type="integer", description="1 => left, 2 => center, 3 => right"),
     *              @OA\Property(property="watermarkPosition", type="integer"),
     *              @OA\Property(property="watermarkOpacity", type="integer"),
     *              @OA\Property(property="header_image_scale", type="integer"),
     *              @OA\Property(property="footer_image_scale", type="integer"),
     *            ),
     *        ),
     *    ),
     *      @OA\Response(
     *          response=201,
     *          description="Candidates Re Offered Successfully",
     *          @OA\JsonContent()
     *       ),
     *      @OA\Response(
     *          response=200,
     *          description="Candidates Re Offered Successfully",
     *          @OA\JsonContent()
     *       ),
     *      @OA\Response(
     *          response=422,
     *          description="Unprocessable Entity",
     *          @OA\JsonContent()
     *       ),
     *      @OA\Response(response=400, description="Bad request"),
     *      @OA\Response(response=404, description="Resource Not Found"),
     * )
     */

            //  ***************** my code  *********************** //////////

    //  public function reOffered(Request $request){
    //           try {

    //                  $validatedData = $request->validate([
    //                       'applicant_id' => 'required|integer|exists:new_applicant,id',
    //                       'reason' => 'required|string',
    //                  ]);
    //                  $today = now()->format('Y-m-d');
    //                  $id = $request->applicant_id;

    //                 $reason = isset($request->reason) ? $request->reason : "";

    //                 $findCandidateDetail = NewApplicant::find($request->applicant_id);

    //             if ($findCandidateDetail) {

    //                 $reason_history = ['status' => "Re Offered", 'reason' => $reason, 'date' => $today,];
    //                 $reasonHistory = $findCandidateDetail->reason_history ?? [];
    //                 $reasonHistory[] = $reason_history;
    //                 $findCandidateDetail->reason_history = $reasonHistory;
    //                 $findCandidateDetail->reoffered = 1;

    //                 $findCandidateDetail->sent_mail = 1; // Mail sent

    //                 $findCandidateDetail->save();


    //                 $content = $request->mail_content;
    //                 $header_img_width_height = $request->header_image_scale ?? 40;
    //                 $footer_img_width_height = $request->footer_image_scale ?? 40;
    //                 // header image
    //                 if ($request->hasFile('header_image')) {
    //                     $header_imgName = time() . "_" . $request->file('header_image')->getClientOriginalName();
    //                     $headerPath = $request->file('header_image')->move(public_path('hiringtemplate/header'), $header_imgName);
    //                     $headerAbsolutePath = str_replace('\\', '/', $headerPath->getRealPath());

    //                 }elseif (!$request->hasFile('header_image') && !empty($request->header_image)) {
    //                     $headerAbsolutePath = $request->header_image;
    //                 }else {
    //                     $headerAbsolutePath = null;
    //                 }

    //                 if (isset($headerAbsolutePath) && !empty($headerAbsolutePath)) {

    //                     $headerPosition = $request->input('headerImagePosition', 2);
    //                     $headerAlign = '';
    //                     if ($headerPosition == 1) {
    //                         $headerAlign = 'text-align: left;';
    //                     } elseif ($headerPosition == 2) {
    //                         $headerAlign = 'text-align: center;';
    //                     } elseif ($headerPosition == 3) {
    //                         $headerAlign = 'text-align: right;';
    //                     }

    //                     $headerTag = '<div style="' . $headerAlign . '">
    //                     <img src="'.$headerAbsolutePath.'" style="object-fit: contain; max-width:'.$header_img_width_height.'px; height:'.$header_img_width_height.'px;" /></div>';
    //                     $content = $headerTag . $content;
    //                 }

    //                 // footer image
    //                 if ($request->hasFile('footer_image')) {

    //                     $footer_imgName = time() . "_" . $request->file('footer_image')->getClientOriginalName();
    //                     $footerPath = $request->file('footer_image')->move(public_path('hiringtemplate/footer'), $footer_imgName);
    //                     $footerAbsolutePath = str_replace('\\', '/', $footerPath->getRealPath());

    //                 }elseif (!$request->hasFile('footer_image') && !empty($request->footer_image)) {
    //                     $footerAbsolutePath  = $request->footer_image;
    //                 }else {
    //                     $footerAbsolutePath = null;
    //                 }

    //                 if (isset($footerAbsolutePath) && !empty($footerAbsolutePath)) {

    //                     $footerPosition = $request->input('footerImagePosition', 2); // Default to center if not provided
    //                     // Set text-align based on the position
    //                     $footerAlign = '';
    //                     if ($footerPosition == 1) {
    //                         $footerAlign = 'text-align: left;';
    //                     } elseif ($footerPosition == 2) {
    //                         $footerAlign = 'text-align: center;';
    //                     } elseif ($footerPosition == 3) {
    //                         $footerAlign = 'text-align: right;';
    //                     }
    //                     $footerTag = '<div style="' . $footerAlign . ' margin-top:20px;">
    //                     <img src="'.$footerAbsolutePath.'" style="object-fit: contain; max-width:'.$footer_img_width_height.'px; height:'.$footer_img_width_height.'px;" /></div>';
    //                     $content = $content . $footerTag;
    //                 }

    //                 /// watermark image
    //                 // if ($request->hasFile('watermark')) {

    //                 //     $watermark_imgName = time() . "_" . $request->file('watermark')->getClientOriginalName();
    //                 //     $watermarkPath = $request->file('watermark')->move(public_path('hiringtemplate/watermark'), $watermark_imgName);

    //                 //     $watermarAbsolutePath = str_replace('\\', '/', $watermarkPath->getRealPath());
    //                 //     $watermarkData = base64_encode(file_get_contents($watermarAbsolutePath));
    //                 //     $mimeType = mime_content_type($watermarAbsolutePath);
    //                 //     $imageSource = "url(data:$mimeType;base64,$watermarkData)";

    //                 // }elseif (!$request->hasFile('watermark') && !empty($request->watermark)) {
    //                 //     $watermarAbsolutePath = $request->watermark;
    //                 //     $imageSource = "url('$watermarAbsolutePath')";

    //                 // }else {
    //                 //     $imageSource = null;
    //                 // }

    //                 // if (isset($imageSource) && !empty($imageSource)) {
    //                 //     $rotationDegree = $request->input('watermarkPosition', 0); // rotation in degrees
    //                 //     $watermarkOpacity = $request->input('watermarkOpacity', 0.2); // between 0.0 and 1.0
    //                 //     $content = '
    //                 //                 <div style="position: relative; width: 100%; height: 100%; font-family: Arial, Helvetica, sans-serif;">

    //                 //                     <div style="
    //                 //                         position: absolute;
    //                 //                         top: 0;
    //                 //                         left: 0;
    //                 //                         width: 100%;
    //                 //                         height: 100%;
    //                 //                         background-image: '.$imageSource.';
    //                 //                         background-repeat: no-repeat;
    //                 //                         background-position: center;
    //                 //                         background-size: contain;
    //                 //                         opacity: ' . $watermarkOpacity . ';
    //                 //                         transform: rotate(' . $rotationDegree . 'deg);
    //                 //                         z-index: 0;
    //                 //                         pointer-events: none;
    //                 //                     "></div>
    //                 //                     <div style="position: relative; z-index: 1;">
    //                 //                         ' . $content . '
    //                 //                     </div>
    //                 //                 </div>';
    //                 // }


    //                  /// watermark image
    //                 $watermarAbsolutePath=null;
    //                 if ($request->hasFile('watermark')) {

    //                     $watermark_imgName = time() . "_" . $request->file('watermark')->getClientOriginalName();
    //                     $watermarkPath = $request->file('watermark')->move(public_path('hiringtemplate/watermark'), $watermark_imgName);

    //                     $watermarAbsolutePath = str_replace('\\', '/', $watermarkPath->getRealPath());

    //                     $watermarkData = base64_encode(file_get_contents($watermarAbsolutePath));
    //                     $mimeType = mime_content_type($watermarAbsolutePath);
    //                     $imageSource = "url(data:$mimeType;base64,$watermarkData)";

    //                 }elseif (!$request->hasFile('watermark') && !empty($request->watermark)) {
    //                     $watermarAbsolutePath = $request->watermark;
    //                     $imageSource = "url('$watermarAbsolutePath')";

    //                 }else {
    //                     $imageSource = null;
    //                      $watermarkabsolutePath=$request->watermark; // if not upload then link will work
    //                 }

    //                 if (isset($imageSource) && !empty($imageSource)) {

    //                     $rotationDegree = $request->input('watermarkPosition', 0); // rotation in degrees
    //                     $watermarkOpacity = $request->input('watermarkOpacity', 0.2); // between 0.0 and 1.0
    //                    $watermarkStyle = '
    //                         <style>
    //                             .watermark-container {
    //                                 position: fixed;
    //                                 top: 0;
    //                                 left: 0;
    //                                 width: 100%;
    //                                 height: 100%;
    //                                 z-index: 0;
    //                             }
    //                             .watermark-container img {
    //                                 position: absolute;
    //                                 top: 50%;
    //                                 left: 50%;
    //                                 transform: translate(-50%, -50%) rotate(' . $rotationDegree . 'deg);
    //                                 width: 80%;
    //                                 opacity: ' . $watermarkOpacity . ';
    //                                 pointer-events: none;
    //                             }
    //                             .content {
    //                                 position: relative;
    //                                 z-index: 1;
    //                             }
    //                         </style>
    //                         <div class="watermark-container">
    //                             <img src="' . $watermarAbsolutePath . '" />
    //                         </div>
    //                         <div class="content">
    //                             ' . $content . '
    //                         </div>';
    //                     $content = $watermarkStyle;
    //                 }
    //                 //////    WaterMark Image End


    //                     ////  Offered History   /////
    //                     do {
    //                         $uniqueID = mt_rand(100000, 999999);
    //                     } while (ApplicantOfferedHistory::where('unique_id', $uniqueID)->exists());
    //                     ApplicantOfferedHistory::create([
    //                         'applicant_id' => $request->applicant_id,
    //                         'unique_id' => $uniqueID,
    //                         'date' => now()->format('Y-m-d'),
    //                         'offered_salary' => $request->offered_salary ?? null,
    //                         'joining_date' => $request->joining_date ?? null,
    //                         'joining_time' => $request->joining_time ?? null,
    //                     ]);

    //                     $database =  base64_encode(DB::connection()->getDatabaseName());
    //                     $applicantId = base64_encode($id);
    //                     $accept = base64_encode("Accept");
    //                     $decline = base64_encode("Decline");
    //                     $uniqueID = base64_encode($uniqueID);

    //                     $acceptUrl = $applicantId.'/'.$accept.'/'.$database.'/'.$uniqueID;
    //                     $declineUrl = $applicantId.'/'.$decline.'/'.$database.'/'.$uniqueID;

    //                     if (str_contains($content, '<span style="color: green;">Accept</span>')) {
    //                         $acceptUrl = url('api/offeracceptandDecline/'.$acceptUrl);

    //                         $content = str_replace(
    //                             '<span style="color: green;">Accept</span>',
    //                             '<a href="' . $acceptUrl . '" style="color: green; text-decoration: underline;">Accept</a>',
    //                             $content
    //                         );
    //                     }

    //                     if (str_contains($content, '<span style="color: red;">Decline</span>')) {
    //                         $declineUrl = url('api/offeracceptandDecline/'.$declineUrl);

    //                         $content = str_replace(
    //                             '<span style="color: red;">Decline</span>',
    //                             '<a href="' . $declineUrl . '" style="color: red; text-decoration: underline;">Decline</a>',
    //                             $content
    //                         );
    //                     }



    //                     $mailData = [
    //                         'subject' =>   "Interview next round",
    //                         'title' => '',
    //                         'body' =>   $content,
    //                     ];
    //                     $pdfFileName = null;


    //                     file_put_contents(storage_path('app/debug_content.html'), $content);
    //                     $content = $this->removeEmojis($content);

    //                     $zeroMarginStyle = '<style>
    //                         h1, h2, h3, h4, h5, h6, div, p, span { margin: 0 !important; }
    //                      </style>';
    //                     $content = $zeroMarginStyle . $content;

    //                     try {
    //                         $pdf = Pdf::loadHTML($content)
    //                             ->setOptions([
    //                                 'isHtml5ParserEnabled' => true,
    //                                 'isRemoteEnabled' => true, // Use only local images for speed
    //                                 'chroot' => public_path(), // This is crucial for local file access
    //                                 'enable_php' => true,
    //                                 'defaultFont' => 'dejavu sans', // Critical for Unicode support
    //                                 'isFontSubsettingEnabled' => true,
    //                             ])
    //                             ->output();
    //                     } catch (\Exception $e) {
    //                         return $this->errorResponse('PDF generation failed: ' . $e->getMessage());
    //                     }

    //                     $email = $findCandidateDetail->email;

    //                     $pdfFileName = 'hiring-letter'. time(). '.pdf';
    //                     $hiringMailData = [
    //                         'content' => $content,
    //                     ];

    //                     // Define correct public folder path
    //                     $pdfFilePath = public_path('hiring_pdfs/' . $pdfFileName);

    //                     $directory = public_path('hiring_pdfs');
    //                     if (!file_exists($directory) && !is_dir($directory)) {
    //                         mkdir($directory, 0777, true);
    //                     }

    //                     // Save the PDF to the public folder
    //                     Mail::to($email)
    //                     ->send(
    //                         (new HiringTemplateNotification($content))
    //                             ->attachData($pdf, $pdfFileName,  [
    //                                 'mime' => 'application/pdf', // MIME type
    //                             ])
    //                     );


    //                     $emailData =[
    //                         'user_id' => $id,
    //                         'stage' => $request->stages ?? "",
    //                         'email' => $email,
    //                         'template_name' => "",
    //                         'email_title' =>  "",
    //                         'email_content' => $content,
    //                         'email_image' => "",
    //                         'email_pdf' => $pdfFileName,
    //                         'send_date' => now()->format('Y-m-d')
    //                     ];

    //                 HrmsEmployeeEmail::create($emailData);
    //                 ///

    //                 // Update applicant history
    //                 $fullName = trim(($findCandidateDetail->first_name ?? '') . ' ' . ($findCandidateDetail->last_name ?? ''));
    //                 $reasonText = $reason ? " with reason: " . $reason : "";
    //                 $salary = $request->offered_salary ?? 'N/A';

    //                 $changed = $fullName . " has been re-offered a salary of ₹" . $salary . $reasonText . ".";


    //                 UpdateApplicantHistory::create([
    //                     'applicant_id' => $id,
    //                     'updated_by' => auth('sanctum')->id(),
    //                     'notes' =>  "Applicant interview rescheduled",
    //                     'date' => now()->format('Y-m-d'),
    //                     'time' => now()->format('H:i:s'),
    //                     'changed' => $changed,
    //                 ]);


    //                 return response()->json([
    //                     'status' => true,
    //                     'message' => "Candidate Re Offered Successfully",
    //                 ]);
    //             }else {
    //                 return $this->validationErrorResponse("the given data is not found");
    //             }

    //           } catch (\Throwable $th) {
    //                 return $this->errorResponse($th->getMessage());
    //           }
    //  }


    public function reOffered(Request $request){
              try {

                     $validatedData = $request->validate([
                          'applicant_id' => 'required|integer|exists:new_applicant,id',
                          'reason' => 'required|string',
                     ]);
                     $today = now()->format('Y-m-d');
                     $id = $request->applicant_id;

                    $reason = isset($request->reason) ? $request->reason : "";

                    $findCandidateDetail = NewApplicant::find($request->applicant_id);

                if ($findCandidateDetail) {

                    $reason_history = ['status' => "Re Offered", 'reason' => $reason, 'date' => $today,];
                    $reasonHistory = $findCandidateDetail->reason_history ?? [];
                    $reasonHistory[] = $reason_history;
                    $findCandidateDetail->reason_history = $reasonHistory;
                    $findCandidateDetail->reoffered = 1;

                    $findCandidateDetail->sent_mail = 1; // Mail sent

                    $findCandidateDetail->save();


                    $content = $request->mail_content;
                    $header_img_width_height = $request->header_image_scale ?? 40;
                    $footer_img_width_height = $request->footer_image_scale ?? 40;
                    // header image
                    if ($request->hasFile('header_image')) {
                        $header_imgName = time() . "_" . $request->file('header_image')->getClientOriginalName();
                        $headerPath = $request->file('header_image')->move(public_path('hiringtemplate/header'), $header_imgName);
                        $headerAbsolutePath = str_replace('\\', '/', $headerPath->getRealPath());

                    }elseif (!$request->hasFile('header_image') && !empty($request->header_image)) {
                        $headerAbsolutePath = $request->header_image;
                    }else {
                        $headerAbsolutePath = null;
                    }

                    if (isset($headerAbsolutePath) && !empty($headerAbsolutePath)) {

                        $headerPosition = $request->input('headerImagePosition', 2);
                        $headerAlign = '';
                        if ($headerPosition == 1) {
                            $headerAlign = 'text-align: left;';
                        } elseif ($headerPosition == 2) {
                            $headerAlign = 'text-align: center;';
                        } elseif ($headerPosition == 3) {
                            $headerAlign = 'text-align: right;';
                        }

                        $headerTag = '<div style="' . $headerAlign . '">
                        <img src="'.$headerAbsolutePath.'" style="object-fit: contain; max-width:'.$header_img_width_height.'px; height:'.$header_img_width_height.'px;" /></div>';
                        $content = $headerTag . $content;
                    }

                    // footer image
                      // Handle footer image
                    $footerAbsolutePath = null;
                    if ($request->hasFile('footer_image')) {
                        $footer_imgName = time() . "_" . $request->file('footer_image')->getClientOriginalName();
                        $footerPath = $request->file('footer_image')->move(public_path('hiringtemplate/footer'), $footer_imgName);
                        $footerAbsolutePath = str_replace('\\', '/', $footerPath->getRealPath());
                    } elseif (!empty($request->footer_image)) {
                        $footerAbsolutePath = $request->footer_image;

                    }

                    if ($footerAbsolutePath) {
                        $footerPosition = $request->input('footerImagePosition', 2);
                        $footerAlign = ['text-align: left;', 'text-align: center;', 'text-align: right;'][$footerPosition - 1];

                    $footerTag = '
                    <div style="position: fixed; bottom: 20px; width: 100%; text-align: ' . $footerAlign . '; z-index: 10;">
                        <img src="' . $footerAbsolutePath . '"
                            style="object-fit: contain; max-width:' . $footer_img_width_height . 'px; height:' . $footer_img_width_height . 'px;" />
                    </div>';

                        $content .= $footerTag;
                    }

                    /// watermark image
                    $watermarAbsolutePath=null;
                    if ($request->hasFile('watermark')) {

                        $watermark_imgName = time() . "_" . $request->file('watermark')->getClientOriginalName();
                        $watermarkPath = $request->file('watermark')->move(public_path('hiringtemplate/watermark'), $watermark_imgName);

                        $watermarAbsolutePath = str_replace('\\', '/', $watermarkPath->getRealPath());

                        $watermarkData = base64_encode(file_get_contents($watermarAbsolutePath));
                        $mimeType = mime_content_type($watermarAbsolutePath);
                        $imageSource = "url(data:$mimeType;base64,$watermarkData)";

                    }elseif (!$request->hasFile('watermark') && !empty($request->watermark)) {
                        $watermarAbsolutePath = $request->watermark;
                        $imageSource = "url('$watermarAbsolutePath')";

                    }else {
                        $imageSource = null;
                         $watermarkabsolutePath=$request->watermark; // if not upload then link will work
                    }

                    if (isset($imageSource) && !empty($imageSource)) {

                        $rotationDegree = $request->input('watermarkPosition', 0); // rotation in degrees
                        $watermarkOpacity = $request->input('watermarkOpacity', 0.2); // between 0.0 and 1.0
                       $watermarkStyle = '
                            <style>
                                .watermark-container {
                                    position: fixed;
                                    top: 0;
                                    left: 0;
                                    width: 100%;
                                    height: 100%;
                                    z-index: 0;
                                }
                                .watermark-container img {
                                    position: absolute;
                                    top: 50%;
                                    left: 50%;
                                    transform: translate(-50%, -50%) rotate(' . $rotationDegree . 'deg);
                                    width: 80%;
                                    opacity: ' . $watermarkOpacity . ';
                                    pointer-events: none;
                                }
                                .content {
                                    position: relative;
                                    z-index: 1;
                                }
                            </style>
                            <div class="watermark-container">
                                <img src="' . $watermarAbsolutePath . '" />
                            </div>
                            <div class="content">
                                ' . $content . '
                            </div>';
                        $content = $watermarkStyle;
                    }
                    //////    WaterMark Image End


                        ////  Offered History   /////
                        do {
                            $uniqueID = mt_rand(100000, 999999);
                        } while (ApplicantOfferedHistory::where('unique_id', $uniqueID)->exists());
                        ApplicantOfferedHistory::create([
                            'applicant_id' => $request->applicant_id,
                            'unique_id' => $uniqueID,
                            'date' => now()->format('Y-m-d'),
                            'offered_salary' => $request->offered_salary ?? null,
                            'joining_date' => $request->joining_date ?? null,
                            'joining_time' => $request->joining_time ?? null,
                            'is_accept' => 3, // 3 for reoffered
                        ]);

                        $database =  base64_encode(DB::connection()->getDatabaseName());
                        $applicantId = base64_encode($id);
                        $accept = base64_encode("Accept");
                        $decline = base64_encode("Decline");
                        $uniqueID = base64_encode($uniqueID);

                        $acceptUrl = $applicantId.'/'.$accept.'/'.$database.'/'.$uniqueID;
                        $declineUrl = $applicantId.'/'.$decline.'/'.$database.'/'.$uniqueID;

                        if (str_contains($content, '<span style="color: green;">Accept</span>')) {
                            $acceptUrl = url('api/offeracceptandDecline/'.$acceptUrl);

                            $content = str_replace(
                                '<span style="color: green;">Accept</span>',
                                '<a href="' . $acceptUrl . '" style="color: green; font-size:14px; text-decoration: underline;">Accept</a>',
                                $content
                            );
                        }

                        if (str_contains($content, '<span style="color: red;">Decline</span>')) {
                            $declineUrl = url('api/offeracceptandDecline/'.$declineUrl);

                            $content = str_replace(
                                '<span style="color: red;">Decline</span>',
                                '<a href="' . $declineUrl . '" style="color: red; font-size:14px; text-decoration: underline;">Decline</a>',
                                $content
                            );
                        }



                        $mailData = [
                            'subject' =>   "Interview next round",
                            'title' => '',
                            'body' =>   $content,
                        ];
                        $pdfFileName = null;


                        file_put_contents(storage_path('app/debug_content.html'), $content);
                        $content = $this->removeEmojis($content);

                        $zeroMarginStyle = '<style>
                            h1, h2, h3, h4, h5, h6, div, p, span { margin: 0 !important; }
                         </style>';
                        $content = $zeroMarginStyle . $content;

                        try {
                            $pdf = Pdf::loadHTML($content)
                                ->setOptions([
                                    'isHtml5ParserEnabled' => true,
                                    'isRemoteEnabled' => true, // Use only local images for speed
                                    'chroot' => public_path(), // This is crucial for local file access
                                    'enable_php' => true,
                                    'defaultFont' => 'dejavu sans', // Critical for Unicode support
                                    'isFontSubsettingEnabled' => true,
                                ])
                                ->output();
                        } catch (\Exception $e) {
                            return $this->errorResponse('PDF generation failed: ' . $e->getMessage());
                        }

                        $email = $findCandidateDetail->email;

                        $pdfFileName = 'hiring-letter'. time(). '.pdf';
                        $hiringMailData = [
                            'content' => $content,
                        ];

                        // Define correct public folder path
                        $pdfFilePath = public_path('hiring_pdfs/' . $pdfFileName);

                        $directory = public_path('hiring_pdfs');
                        if (!file_exists($directory) && !is_dir($directory)) {
                            mkdir($directory, 0777, true);
                        }

                        // Save the PDF to the public folder
                        Mail::to($email)
                        ->send(
                            (new HiringTemplateNotification($content))
                                ->attachData($pdf, $pdfFileName,  [
                                    'mime' => 'application/pdf', // MIME type
                                ])
                        );


                        $emailData =[
                            'user_id' => $id,
                            'stage' => $request->stages ?? "",
                            'email' => $email,
                            'template_name' => "",
                            'email_title' =>  "",
                            'email_content' => $content,
                            'email_image' => "",
                            'email_pdf' => $pdfFileName,
                            'send_date' => now()->format('Y-m-d')
                        ];

                    HrmsEmployeeEmail::create($emailData);
                    ///

                    // Update applicant history
                    $fullName = trim(($findCandidateDetail->first_name ?? '') . ' ' . ($findCandidateDetail->last_name ?? ''));
                    $reasonText = $reason ? " with reason: " . $reason : "";
                    $salary = $request->offered_salary ?? 'N/A';

                    $changed = $fullName . " has been re-offered a salary of ₹" . $salary . $reasonText . ".";


                    UpdateApplicantHistory::create([
                        'applicant_id' => $id,
                        'updated_by' => auth('sanctum')->id(),
                        'notes' =>  "Applicant interview rescheduled",
                        'date' => now()->format('Y-m-d'),
                        'time' => now()->format('H:i:s'),
                        'changed' => $changed,
                    ]);


                    return response()->json([
                        'status' => true,
                        'message' => "Candidate Re Offered Successfully",
                    ]);
                }else {
                    return $this->validationErrorResponse("the given data is not found");
                }

              } catch (\Throwable $th) {
                    return $this->errorResponse($th->getMessage());
              }
     }






    /**
     * @OA\post(
     * path="/uc/api/new_applicant/candidates/checkReferralCode",
     * operationId="checkReferralCode",
     * tags={"New Applicant Templates"},
     * summary="Validate Referral Code and Return Employee Info",
     *   security={ {"Bearer": {} }},
     * description="This API checks if a referral code exists in the subusers table and returns the employee's ID and name.",
     *     @OA\RequestBody(
     *         @OA\JsonContent(),
     *         @OA\MediaType(
     *            mediaType="multipart/form-data",
     *            @OA\Schema(
     *               type="object",
     *              @OA\Property(property="referral_code", type="string"),
     *            ),
     *        ),
     *    ),
     *      @OA\Response(
     *          response=201,
     *          description="Candidates Re Offered Successfully",
     *          @OA\JsonContent()
     *       ),
     *      @OA\Response(
     *          response=200,
     *          description="Candidates Re Offered Successfully",
     *          @OA\JsonContent()
     *       ),
     *      @OA\Response(
     *          response=422,
     *          description="Unprocessable Entity",
     *          @OA\JsonContent()
     *       ),
     *      @OA\Response(response=400, description="Bad request"),
     *      @OA\Response(response=404, description="Resource Not Found"),
     * )
     */


    public function checkReferralCode(Request $request)
    {
        // Get authenticated user
       // $user = auth('sanctum')->user();

        // Validate input
        $request->validate([
            'referral_code' => 'nullable|string'
        ]);

        // Find employee with this referral code
        $employee = SubUser::where('referral_code', $request->referral_code)
            ->select('id', 'unique_id', 'first_name', 'last_name')
            ->first();

        if (!$employee) {
            return response()->json([
                'status' => 'error',
                'message' => 'Invalid referral code'
            ], 400);
        }

        $employeeData = [
            'id' => $employee->id,
            'unique_id' => $employee->unique_id,
            'full_name' => trim($employee->first_name . ' ' . $employee->last_name),
        ];

        return response()->json([
            'status' => 'success',
            'data' => [
                'employee' => $employeeData, // Include user ID if authenticated
            ]
        ]);
    }




    /**
     * @OA\post(
     * path="/uc/api/new_applicant/candidates/applicantTemplateDownload",
     * operationId="applicantTemplateDownload",
     * tags={"New Applicant Templates"},
     * summary="Validate Referral Code and Return Employee Info",
     * security={ {"Bearer": {} }},
     * description="This API Applicant Template Download",
     *     @OA\RequestBody(
     *         @OA\JsonContent(),
     *         @OA\MediaType(
     *            mediaType="multipart/form-data",
     *            @OA\Schema(
     *               type="object",
     *              @OA\Property(property="template_content", type="string"),
     *            ),
     *        ),
     *    ),
     *      @OA\Response(
     *          response=201,
     *          description="Applicant Template Download Successfully",
     *          @OA\JsonContent()
     *       ),
     *      @OA\Response(
     *          response=200,
     *          description="Applicant Template Download Successfully",
     *          @OA\JsonContent()
     *       ),
     *      @OA\Response(
     *          response=422,
     *          description="Unprocessable Entity",
     *          @OA\JsonContent()
     *       ),
     *      @OA\Response(response=400, description="Bad request"),
     *      @OA\Response(response=404, description="Resource Not Found"),
     * )
     */


     public function applicantTemplateDownload(Request $request)
     {
        try {
            $request->validate([
                'template_content' => 'required|string',
            ]);

            $content = $request->template_content;

            // Generate PDF from the content
            $pdf = Pdf::loadHTML($content)
                                            ->setOptions([
                                                'isPhpEnabled' => true,
                                                'isRemoteEnabled' => true, // Use only local images for speed
                                                'isHtml5ParserEnabled' => true,
                                                'isRemoteEnabled' => true, // Use only local images for speed
                                            ])
                                            ->output();

            // Define the filename
            $fileName = 'applicant_template_' . time() . '.pdf';

            // Return the PDF as a download response
            return response()->stream(
                function () use ($pdf) {
                    echo $pdf;
                },
                200,
                [
                    'Content-Type' => 'application/pdf',
                    'Content-Disposition' => 'attachment; filename="' . $fileName . '"',
                ]
            );




        } catch (\Throwable $th) {
            return $this->errorResponse($th->getMessage());
        }
     }




    /**
     * @OA\post(
     * path="/uc/api/new_applicant/candidates/updateHistoryList",
     * operationId="updateHistoryList",
     * tags={"New Applicant Templates"},
     * summary="updateHistoryList",
     * security={ {"Bearer": {} }},
     * description="update History List",
     *     @OA\RequestBody(
     *         @OA\JsonContent(),
     *         @OA\MediaType(
     *            mediaType="multipart/form-data",
     *            @OA\Schema(
     *               type="object",
     *              @OA\Property(property="applicant_id", type="integer"),
     *            ),
     *        ),
     *    ),
     *      @OA\Response(
     *          response=201,
     *          description="Applicant update History List Successfully",
     *          @OA\JsonContent()
     *       ),
     *      @OA\Response(
     *          response=200,
     *          description="Applicant update History List Successfully",
     *          @OA\JsonContent()
     *       ),
     *      @OA\Response(
     *          response=422,
     *          description="Unprocessable Entity",
     *          @OA\JsonContent()
     *       ),
     *      @OA\Response(response=400, description="Bad request"),
     *      @OA\Response(response=404, description="Resource Not Found"),
     * )
     */


    public function updateHistoryList(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'applicant_id' => 'required|integer',
            ]);

            if ($validator->fails()) {
                return $this->errorResponse($validator->errors());
            }

            $applicant = NewApplicant::find($request->applicant_id);

            if (!$applicant) {
                return $this->errorResponse("The given data is not found");
            }
            //


            $history = UpdateApplicantHistory::with(['applicant:id,first_name,last_name','changedBy:id,first_name,last_name'])->where('applicant_id', $request->applicant_id)
                ->orderBy('date', 'desc')
                ->orderBy('time', 'desc')
                ->get();

            return $this->successResponse(
                $history,
                "Updated History List"
            );

        } catch (\Throwable $th) {
            return $this->errorResponse($th->getMessage());
        }

    }




























}
