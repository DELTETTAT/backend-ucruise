<?php

namespace App\Http\Controllers\Api\NewApplicant;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\JobRequirement;
use App\Models\User;
use App\Models\SubUser;
use App\Models\HrmsTeamMember;
use App\Models\HrmsTeam;
use App\Models\EmployeeTeamManager;
use App\Http\Requests\JobRequirementRequest;
use App\Http\Resources\NewApplicant\JobReqirementCollection;
use App\Http\Resources\NewApplicant\JobReqirementResource;
use DB;

class JobRequirementController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */

    /**
     * @OA\get(
     * path="/uc/api/new_applicant/job_requirement/index",
     * operationId="getJob_requirement",
     * tags={"Job Requirement"},
     * summary="Get job_requirement Request",
     *   security={ {"Bearer": {} }},
     * description="Get job_requirement Request",
     *      @OA\Response(
     *          response=201,
     *          description="Job_requirement Get Successfully",
     *          @OA\JsonContent()
     *       ),
     *      @OA\Response(
     *          response=200,
     *          description="job_requirement Get Successfully",
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
    public function index()
    {
        try {

            $user = auth('sanctum')->user()->id;


            $admin = 0;
            $is_manager = 0;
            $user_id = $user;

            // if view role admin view
            $getTeamMembers = SubUser::with('hrmsroles.viewrole')->find($user_id);
            $unique_id = "";

            $role_view ="";
            if ($getTeamMembers) {
                $role_view = @$getTeamMembers->hrmsroles[0]->viewrole->name;
                if ($role_view == 'Admin View') {
                    $admin = 1;
                    $unique_id ="";
                }elseif ($role_view == 'Manager View') {   // manager view
                    $is_manager = 1;
                    $unique_id = @$getTeamMembers->id;

                }elseif ($role_view == 'Manager Not Attendance View') {   // manager view
                    $is_manager = 1;
                    $unique_id = @$getTeamMembers->id;

                }elseif ($role_view == 'Team Leader View') {

                    // $hrmsteamManager  = HrmsTeam::where('team_leader', $user_id);
                    // $teammanager  = EmployeeTeamManager::where('team_manager_id', $hrmsteamManager->team_manager_id)->first();
                    // $getTeamMembers = SubUser::with('hrmsroles.viewrole')->find($teammanager->employee_id);
                    // $unique_id = @$getTeamMembers->unique_id;

                    $hrmsteamManager = HrmsTeam::with('teamManager.employee.hrmsroles.viewrole')->where('team_leader', $user_id)->first();
                    $unique_id = optional($hrmsteamManager->teamManager->employee)->id;
                }elseif ($role_view == 'Employee View') {

                    // $hrmsteam  = HrmsTeamMember::where('member_id', $user_id)->first(); // first ge tem
                    // $hrmsteamManager  = HrmsTeam::find($hrmsteam->hrms_team_id);
                    // $teammanager  = EmployeeTeamManager::where('team_manager_id', $hrmsteamManager->team_manager_id)->first();
                    // $getTeamMembers = SubUser::with('hrmsroles.viewrole')->find($teammanager->employee_id);
                    // $unique_id = @$getTeamMembers->unique_id;

                    $hrmsteam = HrmsTeamMember::with(['team.teamManager.employee.hrmsroles.viewrole'])->where('member_id', $user_id)->first();
                    $unique_id = optional($hrmsteam->team->teamManager->employee)->id;
                }

            }

            if (!empty($role_view) && $role_view == 'Manager View'  ||  $role_view == 'Employee View' || $role_view == 'Team Leader View') {
                $getJobs = JobRequirement::with('Designation','quizLevel')->orderBy('id', 'desc')->where('employee_id',$unique_id)->paginate(JobRequirement::PAGINATE);
            }else{
                $getJobs = JobRequirement::with('Designation','quizLevel')->orderBy('id', 'desc')->paginate(JobRequirement::PAGINATE);
            }

            return $this->successResponse(
                new JobReqirementCollection($getJobs, false),
                "Jobs List"
            );
        } catch (\Exception $ex) {
            return $this->errorResponse($ex->getMessage());
        }
    }


    /**
     * @OA\get(
     * path="/uc/api/new_applicant/job_requirement/joblistforDashboard",
     * operationId="joblistforDashboard",
     * tags={"Job Requirement"},
     * summary="Get joblist for Dashboard Request",
     *   security={ {"Bearer": {} }},
     * description="Get joblist for Dashboard Request",
     *      @OA\Response(
     *          response=201,
     *          description="joblist for Dashboard Get Successfully",
     *          @OA\JsonContent()
     *       ),
     *      @OA\Response(
     *          response=200,
     *          description="joblist for Dashboard Get Successfully",
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


     public function joblistforDashboard(){

        try {

            $getJobs = JobRequirement::with('Designation', 'quizLevel')->orderBy('id', 'desc')->limit(6)->get();
            return $this->successResponse(
                new JobReqirementCollection($getJobs, false),
                "Jobs List"
            );
        } catch (\Exception $ex) {
            return $this->errorResponse($ex->getMessage());
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
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */

    /**
     * @OA\Post(
     * path="/uc/api/new_applicant/job_requirement/store",
     * operationId="storejob_requirement",
     * tags={"Job Requirement"},
     * summary="Store Job Request",
     *   security={ {"Bearer": {} }},
     * description="Store Job Request",
     *    @OA\RequestBody(
     *         @OA\JsonContent(),
     *         @OA\MediaType(
     *            mediaType="multipart/form-data",
     *            @OA\Schema(
     *               type="object",
     *              @OA\Property(property="designation_id", type="integer"),
     *              @OA\Property(property="roles", type="integer", description="'1 => junior, 2 => medium, 3 => senior'"),
     *              @OA\Property(property="job_type", type="integer", description="'1 => intern, 2 => part_time, 3 => full_time'"),
     *              @OA\Property(property="priority", type="integer", description="'1 => low, 2 => medium, 3 => high'"),
     *              @OA\Property(property="job_description", type="string"),
     *              @OA\Property(property="post_status", type="integer", description="'0=> Not Start, 1 => In Progress , 2 => On Hold, 3 => Done'"),
     *            ),
     *        ),
     *    ),
     *      @OA\Response(
     *          response=201,
     *          description="Job Created Successfully",
     *          @OA\JsonContent()
     *       ),
     *      @OA\Response(
     *          response=200,
     *          description="Job Created Successfully",
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

    public function store(JobRequirementRequest $request)
    {
        try {

            $validatedData = $request->validated();
            $user_id = auth('sanctum')->user()->id;
            $loginUser = User::find($user_id);
            $validatedData['name'] = $loginUser->first_name;
            $validatedData['email'] = $loginUser->email;
            $validatedData['phone'] = $loginUser->phone;
            $validatedData['company_name'] = $loginUser->company_name;
            $validatedData['address'] = $loginUser->address;
            $validatedData['employee_id'] = $loginUser->id;
            
            JobRequirement::create($validatedData);

            return $this->successResponse(
                [],
                 'Job Created Successfully'
            );
        } catch (\Exception $ex) {
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
        
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */

    /**
     * @OA\get(
     * path="/uc/api/new_applicant/job_requirement/edit/{id}",
     * operationId="editjob_requirement",
     * tags={"Job Requirement"},
     * summary="Edit job Request",
     *   security={ {"Bearer": {} }},
     * description="Edit job Request",
     *    @OA\Parameter(
     *     name="id",
     *     in="path",
     *     required=true,
     *     @OA\Schema(type="integer")
     *    ),
     *      @OA\Response(
     *          response=201,
     *          description="Job Edited Successfully",
     *          @OA\JsonContent()
     *       ),
     *      @OA\Response(
     *          response=200,
     *          description="Job Edited Successfully",
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
    public function edit($id)
    {
        try {
            $getJobsDetails = JobRequirement::find($id);

            if ($getJobsDetails) {
                return $this->successResponse(
                    new JobReqirementResource($getJobsDetails),
                    "Job Details"
                );
            }else {
                return $this->validationErrorResponse("the given data is not found");
            }
        } catch (\Exception $ex) {
            return $this->errroResponse($ex->getMessage());
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
     * path="/uc/api/new_applicant/job_requirement/update/{id}",
     * operationId="updateJob_requirement",
     * tags={"Job Requirement"},
     * summary="Update Job Request",
     *   security={ {"Bearer": {} }},
     * description="Update Job Request",
     *    @OA\Parameter(
     *     name="id",
     *     in="path",
     *     required=true,
     *     @OA\Schema(type="integer")
     *    ),
     *    @OA\RequestBody(
     *         @OA\JsonContent(),
     *         @OA\MediaType(
     *            mediaType="multipart/form-data",
     *            @OA\Schema(
     *               type="object",
     *              @OA\Property(property="designation_id", type="integer"),
     *              @OA\Property(property="roles", type="integer", description="'1 => junior, 2 => medium, 3 => senior'"),
     *              @OA\Property(property="job_type", type="integer", description="'1 => intern, 2 => part_time, 3 => full_time'"),
     *              @OA\Property(property="priority", type="integer", description="'1 => low, 2 => medium, 3 => high'"),
     *              @OA\Property(property="job_description", type="string"),
     *              @OA\Property(property="post_status", type="integer", description="'0=> Not Start, 1 => In Progress , 2 => On Hold, 3 => Done'"),
     *            ),
     *        ),
     *    ),
     *      @OA\Response(
     *          response=201,
     *          description="Job Updated Successfully",
     *          @OA\JsonContent()
     *       ),
     *      @OA\Response(
     *          response=200,
     *          description="Job Updated Successfully",
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
    public function update(JobRequirementRequest $request, $id)
    {
        try {
            $getJob = JobRequirement::find($id);
            if ($getJob) {

                $validatedData = $request->validated();

                $user_id = auth('sanctum')->user()->id;
                $loginUser = User::find($user_id);
                $validatedData['name'] = $loginUser->first_name;
                $validatedData['email'] = $loginUser->email;
                $validatedData['phone'] = $loginUser->phone;
                $validatedData['company_name'] = $loginUser->company_name;
                $validatedData['address'] = $loginUser->address;
                $validatedData['employee_id'] = $loginUser->unique_id ;

                $updatedJob = $getJob->update($validatedData);

                return $this->successResponse(
                    new JobReqirementResource($getJob),
                    "Job Updated Successfully"
                );

            }else {
                return $this->validationErrorResponse("the given data is not found");
            }
        } catch (\Exception $ex) {
            return $this->errorResponse($ex->getMessage());
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */

    /**
     * @OA\delete(
     * path="/uc/api/new_applicant/job_requirement/delete/{id}",
     * operationId="deletejob_requirement",
     * tags={"Job Requirement"},
     * summary="Delete job Request",
     * security={ {"Bearer": {} }},
     * description="Delete job Request",
     * @OA\Parameter(
     *     name="id",
     *     in="path",
     *     required=true,
     *     @OA\Schema(type="integer")
     * ),
     * @OA\Response(
     *     response=200,
     *     description="Job Deleted Successfully",
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
             $getJob = JobRequirement::find($id);
             if ($getJob) {
                $getJob->delete();
             }else {
                return $this->validationErrorResponse("the given data is not found");
             }
             return $this->successResponse(
                [],
                "Job Deleted Successfully"
             );
        } catch (\Exception $ex) {
            return $this->errorResponse($ex->getMessage());
        }
    }


        /**
     * @OA\post(
     * path="/uc/api/new_applicant/job_requirement/jobPostStatus/{id}",
     * operationId="jobPostStatus",
     * tags={"Job Requirement"},
     * summary="job Post status",
     * security={ {"Bearer": {} }},
     * description="job Post status",
     * @OA\Parameter(
     *     name="id",
     *     in="path",
     *     required=true,
     *     @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         @OA\JsonContent(),
     *         @OA\MediaType(
     *            mediaType="multipart/form-data",
     *            @OA\Schema(
     *               type="object",
     *              @OA\Property(property="post_status", type="integer", description="'0=> Not Start, 1 => In Progress , 2 => On Hold, 3 => Done, 4 => Close'"),
     *            ),
     *        ),
     *    ),
     * @OA\Response(
     *     response=200,
     *     description="Job status Successfully",
     *     @OA\JsonContent()
     * ),
     * @OA\Response(
     *     response=404,
     *     description="Resource Not Found"
     * )
     * )
     */

    public function jobPostStatus(Request $request, $id){
        
        try {
            $getJob = JobRequirement::find($id);
            if ($getJob) {
               $getJob->post_status = $request->post_status;
               $getJob->save();
            }else {
               return $this->validationErrorResponse("the given data is not found");
            }
            return $this->successResponse(
               [],
               "Job updated Successfully"
            );
       } catch (\Exception $ex) {
           return $this->errorResponse($ex->getMessage());
       }


    }
}
