<?php

namespace App\Http\Controllers\Api\Hrms\Report;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\DailyWorkReport;
use App\Models\HrmsEmployeeRole;
use App\Models\HrmsRole;
use App\Models\EmployeeTeamManager;
use App\Models\TeamManager;
use App\Models\HrmsTeam;
use DB;

class DailyWorkReportController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */


    /**
     * @OA\Get(
     * path="/uc/api/report/index",
     * operationId="getting work report",
     * tags={"Employee Daily Work Report"},
     * summary="Employee Daily Work Report Request",
     *   security={ {"Bearer": {} }},
     *    description="Get roles Request",
     *      @OA\Response(
     *          response=201,
     *          description="Employee Daily Work Report Get Successfully",
     *          @OA\JsonContent()
     *       ),
     *      @OA\Response(
     *          response=200,
     *          description="Employee Daily Work Report Get Successfully",
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
             $user_id = auth('sanctum')->user()->id;

            //  $auth_role = DB::table('role_user')->where('user_id',$user_id)->first();

            //  $employeeRole = DB::table('roles')->find($auth_role->role_id);

            //     if ($employeeRole->name != "admin") {
            //             $role =   HrmsEmployeeRole::where('employee_id', $user_id)->first();
            //             if (!$role) {
            //                 return $this->errorResponse("don`t asign role this user");
            //             }

            //              $authRole = HrmsRole::with('viewrole',)->where('id', $role->role_id)->first();
            //              $authRole->viewrole->name;
            //              if ($authRole->viewrole->name == 'Manager View') {
            //                  $user_ids = [];
            //                  $manager =  EmployeeTeamManager::where('employee_id', $user_id)->first();

            //                  $getManagerList = TeamManager::with(['employees','teams.teamLeader', 'teams.teamMembers.user'])->find($manager->team_manager_id);

            //                  $user_ids[] = $getManagerList->employees->first()->id;

            //                  foreach ($getManagerList->teams as $key => $teams) {
            //                     $user_ids[] = $teams->team_leader;

            //                     foreach ($teams->teamMembers as $key => $members) {
            //                         $user_ids[] = $members->user->id;
            //                     }
            //                  }
            //                  $user_ids[] = $user_id;
            //                  $allReports = DailyWorkReport::with('users:id,first_name,last_name')->whereIn('user_id',$user_ids)->get();
            //              }elseif ($authRole->viewrole->name == 'Team Leader View') {
            //                  $team = HrmsTeam::with('teamMembers')->where('team_leader',$user_id)->first();

            //                  foreach ($team->teamMembers as $key => $member) {
            //                      $user_ids[] = $member->member_id;
            //                  }
            //                  $user_ids[] = $user_id;
            //                  $allReports = DailyWorkReport::with('users:id,first_name,last_name')->whereIn('user_id',$user_ids)->get();
            //              }elseif ($authRole->viewrole->name == 'Employee View') {
            //                  $allReports = DailyWorkReport::with('users:id,first_name,last_name')->where('user_id',$user_id)->get();
            //              }else {
            //                  $allReports = DailyWorkReport::with('users:id,first_name,last_name')->get();
            //              }

            //    }else {
            //         $allReports = DailyWorkReport::with('users:id,first_name,last_name')->get();
            //    }

               $allReports = DailyWorkReport::with('users:id,first_name,last_name')
               //->orderBy('created_at', 'DESC') // Most recent first
               ->get();
               $modifiedReports = $allReports->map(function ($report) use ($user_id) {
                                        $report->auth_user = $report->user_id == $user_id ? 1 : 0;
                                        return $report;
                                    });

               return $this->successResponse([
                             $modifiedReports,
                             "Report List"
                        ],200);

        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => $th->getMessage()
            ],500);
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
     * path="/uc/api/report/store",
     * operationId="work Report",
     * tags={"Employee Daily Work Report"},
     * summary="Employee Daily Work Report Store",
     * security={ {"Bearer": {} }},
     * description="Employee Daily Work Report Store",
     *       @OA\RequestBody(
     *         @OA\JsonContent(),
     *         @OA\MediaType(
     *            mediaType="multipart/form-data",
     *            @OA\Schema(
     *               type="object",
     *                 @OA\Property(property="report_content", type="text", description="Report Content With HTML"),
     *            ),
     *        ),
     *    ),
     *      @OA\Response(
     *          response=201,
     *          description="Employee Daily Work Report Store Successfully",
     *          @OA\JsonContent()
     *       ),
     *      @OA\Response(
     *          response=200,
     *          description="Employee Daily Work Report Store Successfully",
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
    public function store(Request $request)
    {
        try {
             $validated = $request->validate(['report_content' => 'required']);
             $validated['user_id'] = auth('sanctum')->user()->id;
             $validated['date'] = now()->format('Y-m-d');

             $report = [
                'time' => now()->format('h:i:a'),
                'report_content' => $validated['report_content']
             ];

             $todayExistsReport = DailyWorkReport::where('user_id', $validated['user_id'])->whereDate('date', $validated['date'])->latest()->first();


             if ($todayExistsReport) {
                   $existReport = is_array($todayExistsReport->report_content) ? $todayExistsReport->report_content : [];
                   $existReport[] = $report;
                   $todayExistsReport->report_content = $existReport;
                   $todayExistsReport->save();
             }else {
                   $validated['report_content'] = [$report];
                   DailyWorkReport::create($validated);
             }

             return response()->json([
                 'status' => true,
                 'message' => "Report Submited Successfully"
             ]);

        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => $th->getMessage()
            ]);
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
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
