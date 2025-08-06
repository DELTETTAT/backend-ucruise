<?php

namespace App\Http\Controllers\Api\Hrms;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Mail;
use DB;
use Carbon\Carbon;
use App\Mail\ResignedEmail;
use App\Mail\ResignationAcceptedMail;
use App\Models\EmailAddressForAttendanceAndLeave;
use App\Models\SubUser;
use App\Models\User;
use App\Models\Reason;
use App\Models\HrmsTeam;
use App\Models\TeamManager;
use App\Models\HrmsTeamMember;
use App\Models\EmployeeTeamManager;
use App\Models\Resignation;
use App\Models\HrmsEmployeeRole;
use App\Models\HrmsRole;


class EmployeeActionsController extends Controller
{


    /**
     * @OA\Post(
     * path="/uc/api/employee/resignation",
     * operationId="resignation",
     * tags={"resignation"},
     * summary="resignation",
     *   security={ {"Bearer": {} }},
     * description="resignation",
     *     @OA\RequestBody(
     *         @OA\JsonContent(),
     *         @OA\MediaType(
     *            mediaType="multipart/form-data",
     *            @OA\Schema(
     *               type="object",
     *               required={"date"},
     *               @OA\Property(property="date", type="date"),
     *               @OA\Property(property="reason", type="string"),
     *               @OA\Property(property="description", type="text"),
     *            ),
     *        ),
     *    ),
     *      @OA\Response(
     *          response=201,
     *          description="resignation request submitted successfully.",
     *          @OA\JsonContent()
     *       ),
     *      @OA\Response(
     *          response=200,
     *          description="resignation request submitted successfully.",
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


        public function resignation(Request $request)
        {
            try {
                $user = auth('sanctum')->user();

                $request->validate([
                    'date' => 'required|date',
                    'reason' => 'required|string|max:500',
                    'description' => 'nullable|string|max:1000',
                ]);

                // Create resignation
                Resignation::create([
                    'user_id' => $user->id,
                    'date' => $request->date,
                    'reason' => $request->reason,
                    'description' => $request->description,
                ]);

                $emailData = [
                    'user_name' => "{$user->first_name} {$user->last_name}",
                    'date' => $request->date,
                    'reason' => $request->reason,
                    'description' => $request->description,
                ];

                $emails = EmailAddressForAttendanceAndLeave::where('type', 0)->pluck('email');

                if ($emails->isNotEmpty()) {
                    $toEmail = $emails->first();
                    $ccEmails = $emails->skip(1)->values()->all();

                    // Find manager
                    $manager = TeamManager::with(['employees', 'teams.teamLeader', 'teams.teamMembers.user'])->get()
                        ->first(function ($manager) use ($user) {
                        //     foreach ($manager->teams as $team) {
                        //         if ($team->team_leader == $user->id ||
                        //             $team->teamMembers->contains(fn($m) => $m->user_id == $user->id)) {
                        //             return true;
                        //         }
                        //     }
                        //     return false;
                        // });
                                foreach ($manager->teams as $team) {
                                    if (
                                        ($team->team_leader ?? null) == $user->id ||
                                        ($team->teamMembers && $team->teamMembers->contains(fn($m) => $m->user_id == $user->id))
                                    ) {
                                        return true;
                                    }
                                }
                                return false;
                            });

                    if ($manager && $manager->employees) {
                        $ccEmails = array_merge($ccEmails, $manager->employees->pluck('email')->toArray());
                    }

                    // Send mail
                    Mail::to($toEmail)
                        ->cc(array_unique($ccEmails))
                        ->send(new ResignedEmail($emailData));
                }

                return response()->json([
                    'success' => true,
                    'message' => 'Resignation request submitted successfully',
                    'data' => $emailData
                ]);
            } catch (\Throwable $th) {
                return response()->json([
                    'success' => false,
                    'message' => $th->getMessage()
                ], 500);
            }
        }

    /**
     * @OA\Get(
     * path="/uc/api/employee/listResignations",
     * operationId="List of Resignations",
     * tags={"resignation"},
     * summary="resignation list",
     *   security={ {"Bearer": {} }},
     * description="resignation list",
     *      @OA\Response(
     *          response=201,
     *          description="resignation request get successfully.",
     *          @OA\JsonContent()
     *       ),
     *      @OA\Response(
     *          response=200,
     *          description="resignation request get successfully.",
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

    public function listResignations()
    {
        try {
            $user_id = auth('sanctum')->user()->id;
            $auth_role = DB::table('role_user')->where('user_id', $user_id)->first();
            $employeeRole = DB::table('roles')->find($auth_role->role_id);

            $user_ids = [];

            if ($employeeRole->name != "admin") {
                $role = HrmsEmployeeRole::where('employee_id', $user_id)->first();
                if (!$role) {
                    return $this->errorResponse("Don't assign role to this user");
                }

                $authRole = HrmsRole::with('viewrole')->find($role->role_id);
                $viewName = $authRole->viewrole->name;

                switch ($viewName) {
                    case 'Manager View':
                        $user_ids[] = $user_id;
                        $employee_team_managers = EmployeeTeamManager::where('employee_id', $user_id)->first();
                        $manager = TeamManager::with('teams.teamLeader', 'teams.teamMembers.user')->find($employee_team_managers->team_manager_id);

                        foreach ($manager->teams as $team) {
                            $user_ids[] = $team->team_leader;
                            foreach ($team->teamMembers as $member) {
                                $user_ids[] = $member->member_id;
                            }
                        }
                        break;

                    case 'Team Leader View':
                        $user_ids[] = $user_id;
                        $teams = HrmsTeam::with('teamMembers')->where('team_leader', $user_id)->get();
                        foreach ($teams as $team) {
                            foreach ($team->teamMembers as $member) {
                                $user_ids[] = $member->id;
                            }
                        }
                        break;

                    case 'Employee View':
                        $user_ids[] = $user_id;
                        break;

                    case 'Admin View':
                    case 'HR View':
                        // No filtering needed
                        break;

                    default:
                        return $this->errorResponse("Invalid view role");
                }
            }

            // Final data fetch
            $resignationQuery = Resignation::with([
                'user:id,first_name,last_name,employement_type',
                'user.hrmsroles:id,name'
            ]);

            if (!empty($user_ids)) {
                $resignationQuery->whereIn('user_id', array_unique($user_ids));
            }

            $resignation = $resignationQuery->get();

            return response()->json([
                'success' => true,
                'message' => 'Resignation request fetched successfully',
                'data' => $resignation
            ], 200);

        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => $th->getMessage()
            ], 500);
        }
    }



    //***************************** Handle Resignation requests api***************************/

    /**
     * @OA\Post(
     * path="/uc/api/employee/handleResignation",
     * operationId="handleResignation",
     * tags={"resignation"},
     * summary="Handle Resignation",
     *   security={ {"Bearer": {} }},
     * description="Handle Resignation",
     *     @OA\RequestBody(
     *         @OA\JsonContent(),
     *         @OA\MediaType(
     *            mediaType="multipart/form-data",
     *            @OA\Schema(
     *               type="object",
     *               required={"id"},
     *               @OA\Property(property="id", type="text"),
     *               @OA\Property(property="status", type="integer",description="1 => accepted, 2 => rejected"),
     * 
     *
     *            ),
     *        ),
     *    ),
     *      @OA\Response(
     *          response=201,
     *          description="Leave accepted successfully",
     *          @OA\JsonContent()
     *       ),
     *      @OA\Response(
     *          response=200,
     *          description="Leave accepted successfully",
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

public function handleResignation(Request $request)
{
    try {
        // Validate request - status is now required
        $validatedData = $request->validate([
            'id' => 'required|exists:resignations,id',
            'status' => 'required|integer|in:1,2', // 1=accepted, 2=rejected  
        ]);

        $resignation = Resignation::findOrFail($request->id);

        // Get authenticated admin user
        $admin = auth('sanctum')->user();
        $fromName = $admin->first_name ." ". $admin->last_name;

        // Update resignation
        $resignation->update([
            'status' => $request->status,
            'accept_or_reject_date_of_resignation' => now()->format('Y-m-d'), // Always set date here
            //'admin_remarks' => $request->admin_remarks
        ]);

        // Send email only if status is accepted (1)
        if ($request->status == 1) {

            $temp_DB_name = DB::connection()->getDatabaseName();

            $default_DBName = env("DB_DATABASE");
            $this->connectDB($default_DBName);  // parent database connection

            $employee = SubUser::find($resignation->user_id);
            $employee->status = 3;
            $employee->save();


              // child database connection
            $this->connectDB($temp_DB_name);

            $employee = User::find($resignation->user_id);
            $employee->status = 3;
            $employee->save();

            $employee = SubUser::find($resignation->user_id);
            $employee->status = 3;
            $employee->save();

            $emailData = [
                'employee_name' => $resignation->user->first_name ." ". $resignation->user->last_name,
                'resignation_date' => $resignation->date,
                'processed_date' => now()->format('Y-m-d'),
                'admin' => $fromName
            ];

            Mail::to($resignation->user->email)
                ->send(new ResignationAcceptedMail($emailData, $fromName));
        }

        // Prepare response message based on status
        $statusMessage = $request->status == 1 ? 'accepted' : 'rejected';

        return response()->json([
            'success' => true,
            'message' => "Resignation {$statusMessage} successfully",
        ]);

    } catch (\Throwable $th) {
        return response()->json([
            'success' => false,
            'message' => $th->getMessage()
        ], 500);
    }
}

}