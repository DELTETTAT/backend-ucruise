<?php

namespace App\Http\Controllers\Api\Hrms\RolePermissions;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\HrmsRolePermissionTitle;
use App\Models\HrmsRolePermission;
use App\Models\Role;
use App\Models\HrmsRole;
use App\Models\HrmsPermission;
use App\Models\HrmsEmployeeRole;
use App\Models\HrmsNewRole;
use Illuminate\Support\Facades\Validator;
use DB;

class RoleandpermissionController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */

    /**
     * @OA\post(
     * path="/uc/api/roles_permission/index",
     * operationId="getroles",
     * tags={"HRMS Employee roles permissions"},
     * summary="Get roles Request",
     *   security={ {"Bearer": {} }},
     *    description="Get roles Request",
     *      @OA\Response(
     *          response=201,
     *          description=" Roles Get Successfully",
     *          @OA\JsonContent()
     *       ),
     *      @OA\Response(
     *          response=200,
     *          description="Roles Get Successfully",
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
            $roles = HrmsRole::with(['viewrole','hrms_permissions'])->get();

            $viewRole = HrmsNewRole::find('specific_role_id');
            $permissions = HrmsPermission::all();

            return response()->json([
                'roles' => $roles,
                'permissions' => $permissions,

            ]);

       } catch (\Throwable $th) {
           return $this->errorResponse($th->getMessage());
       }
    }



    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */



    public function create(Request $request)
    {



    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */


    public function store(Request $request)
    {



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

    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */

    /**
     * @OA\Post(
     * path="/uc/api/roles_permission/update",
     * operationId="roles permissions update",
     * tags={"HRMS Employee roles permissions"},
     * summary="update roles and permissions",
     *   security={ {"Bearer": {} }},
     *    description="update/{id} roles and permissions",
     * @OA\RequestBody(
     *    required=true,
     *    @OA\MediaType(
     *        mediaType="application/json",
     *        @OA\Schema(
     *            required={"role_id", "permissions"},
     *            @OA\Property(property="role_id", type="integer", description="Define role like HR, Manager, TL"),
     *            @OA\Property(property="specific_role_id", type="integer", description="Define Main Role id"),
     *            @OA\Property(
     *                property="permissions",
     *                type="array",
     *                @OA\Items(
     *                    type="object",
     *                    required={"permission_id", "can_view", "can_edit", "can_access"},
     *                    @OA\Property(property="permission_id", type="integer", description="Permission ID"),
     *                    @OA\Property(property="can_view", type="integer", description="Permission for view"),
     *                    @OA\Property(property="can_edit", type="integer", description="Permission for edit"),
     *                    @OA\Property(property="can_access", type="integer", description="Permission for access"),
     *                )
     *            )
     *        )
     *    )
     * ),
     *      @OA\Response(
     *          response=201,
     *          description="Permission updated successfully",
     *          @OA\JsonContent()
     *       ),
     *      @OA\Response(
     *          response=200,
     *          description="Permission updated successfully",
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

    public function update(Request $request)
    {
        try {

                $validator = Validator::make($request->all(),[
                      'role_id' => 'required|integer|exists:hrms_roles,id',
                      'specific_role_id' => 'required|integer|exists:hrms_new_roles,id',
                      'permissions' => 'required',
                      'permissions.*.permission_id' => 'required|integer|exists:hrms_permissions,id',
                      'permissions.*.can_view' => 'required|integer',
                      'permissions.*.can_edit' => 'required|integer',
                ]);

                if ($validator->fails()) {
                    return $this->errorResponse($validator->errors());
                }

                $findRole =   HrmsRole::find($request->role_id);

                if (!$findRole) {
                    return $this->errorResponse("This roles not found");
                }

                $findRole->specific_role_id = $request->specific_role_id;
                $findRole->save();

                $permissions = $request->permissions;
                $roleId = $request->role_id;
                $permissions = $request->permissions;

                // Fetch existing permissions BEFORE updating them
                $existingPermissions = HrmsRolePermission::where('role_id', $roleId)
                    ->get()
                    ->keyBy('permission_id');

                foreach ($permissions as $key => $permission) {
                    HrmsRolePermission::updateOrCreate(
                        ['role_id' => $roleId, 'permission_id' => $permission['permission_id']],
                        [
                            'can_view' => $permission['can_view'],
                            'can_edit' => $permission['can_edit'],
                           // 'can_access' => $permission->can_access,
                        ]
                    );
                }
                // Record update history
                //$this->recordRoleUpdate($findRole->name, $request->specific_role_id, $permissions);
                $this->recordRoleUpdate($findRole->name, $request->specific_role_id, $permissions, $existingPermissions);


                return response()->json(['message' => 'Permission updated successfully']);

        } catch (\Throwable $th) {
              return $this->errorResponse($th->getMessage());
        }

    }

    private function recordRoleUpdate($roleName, $specificRoleId, $newPermissions, $oldPermissions)
    {
        $user = auth('sanctum')->user();
        if (!$user) return;

        $specificRoleName = HrmsNewRole::find($specificRoleId)->name ?? 'Unknown';

        $changes = [];

        foreach ($newPermissions as $permission) {
            $permissionId = $permission['permission_id'];
            $permissionName = HrmsPermission::find($permissionId)->name ?? "Permission #$permissionId";

            $old = $oldPermissions->get($permissionId);
            $oldView = $old ? (int)$old->can_view : null;
            $oldEdit = $old ? (int)$old->can_edit : null;

            $newView = (int)$permission['can_view'];
            $newEdit = (int)$permission['can_edit'];

            $changedParts = [];

            if ($oldView !== $newView) {
                $changedParts[] = "view " . ($oldView === null ? 'empty' : ($oldView ? 'Yes' : 'No')) . "→" . ($newView ? 'Yes' : 'No');
            }

            if ($oldEdit !== $newEdit) {
                $changedParts[] = "edit " . ($oldEdit === null ? 'empty' : ($oldEdit ? 'Yes' : 'No')) . "→" . ($newEdit ? 'Yes' : 'No');
            }

            if (!empty($changedParts)) {
                $changes[] = "$permissionName: " . implode(', ', $changedParts);
            }
        }

        if (!empty($changes)) {
            DB::table('update_system_setup_histories')->insert([
                'employee_id' => $user->id,
                'date' => now()->format('Y-m-d'),
                'time' => now()->format('H:i:s'),
                'updated_by' => $user->id,
                'notes' => 'Role permissions updated',
                'changed' => "Specific Role: $specificRoleName\nChanges:\n" . implode("\n", $changes),
                'created_at' => now(),
                'updated_at' => now()
            ]);
        }
    }


    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */

    /**
     * @OA\Delete(
     * path="/uc/api/roles_permission/destroy/{id}",
     * operationId="delete roles",
     * tags={"HRMS Employee roles permissions"},
     * summary="delete roles",
     *   security={ {"Bearer": {} }},
     *    description="update/{id} deleteroles",
     *    @OA\Parameter(
     *     name="id",
     *     in="path",
     *     required=true,
     *     @OA\Schema(type="string")
     *    ),
     *      @OA\Response(
     *          response=201,
     *          description="Role Deleted successfully",
     *          @OA\JsonContent()
     *       ),
     *      @OA\Response(
     *          response=200,
     *          description="Role Deleted successfully",
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

    public function destroy($id)
    {

            try {

               $role = HrmsRole::find($id);

               if ($role) {

                   $role->delete();

                   $rolePermissions =  HrmsRolePermission::where('role_id', $id)->get();

                   foreach ($rolePermissions as $key => $permissions) {
                       $permissions->delete();
                   }
                   // Record deletion history
                   $this->recordRoleDeletion($role->name, $rolePermissions->count());

                   return $this->successResponse(
                       [],
                       "Role Deleted Successfully"
                   );
               }else {
                   return $this->errorResponse("The given data is not found");
               }
            } catch (\Throwable $th) {
                return $this->errorResponse($th->getMessage());
            }
    }

    private function recordRoleDeletion($roleName, $permissionCount)
    {
        $user = auth('sanctum')->user();
        if (!$user) return;

        DB::table('update_system_setup_histories')->insert([
            'employee_id' => $user->id,
            'date' => now()->format('Y-m-d'),
            'time' => now()->format('H:i:s'),
            'updated_by' => $user->id,
            'notes' => 'Role deleted',
            'changed' => sprintf(
                "Deleted role: %s (with %d permissions)",
                $roleName,
                $permissionCount
            ),
            'created_at' => now(),
            'updated_at' => now()
        ]);
    }



     /**
     * @OA\Post(
     * path="/uc/api/roles_permission/addNewRole",
     * operationId="add new roles",
     * tags={"HRMS Employee roles permissions"},
     * summary="add new roles",
     *   security={ {"Bearer": {} }},
     *    description="update/{id} add new roles",
     *    @OA\RequestBody(
     *         @OA\JsonContent(),
     *         @OA\MediaType(
     *            mediaType="multipart/form-data",
     *            @OA\Schema(
     *               type="object",
     *               @OA\Property(property="name", type="string", description="define Role name like HR, Manager, TL"),
     *               @OA\Property(property="specific_role_id", type="integer"),
     *            ),
     *        ),
     *    ),
     *      @OA\Response(
     *          response=201,
     *          description="Role Created successfully",
     *          @OA\JsonContent()
     *       ),
     *      @OA\Response(
     *          response=200,
     *          description="Role Created successfully",
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


     public function addNewRole(Request $request){

             try {
                $validator = Validator::make($request->all(), [
                    'name' => 'required|string|unique:hrms_roles,name',
                    'specific_role_id' => 'required|integer|exists:hrms_new_roles,id',
                ], [
                    'name.unique' => 'This role name already exists.', // Custom error message
                ]);

                 if ($validator->fails()) {
                    return response()->json([
                        'success' => false,
                        'errors' => $validator->errors()
                    ], 422);
                }

                $validatedData = $validator->validated();
               // $validatedData['hrms_roles'] = 1;

                $role = HrmsRole::create($validatedData);

                // Added history tracking
                $this->recordRoleCreation($role);

                return $this->successResponse(
                    [],
                    "New Role Created Successfully"
                );

             } catch (\Throwable $th) {
                return $this->errorResponse($th->getMessage());
             }
     }

     private function recordRoleCreation($role)
    {
        $user = auth('sanctum')->user();
        if (!$user) return;

        $specificRoleName = DB::table('hrms_new_roles')
                            ->where('id', $role->specific_role_id)
                            ->value('name') ?? 'Unknown Role';

        DB::table('update_system_setup_histories')->insert([
            'employee_id' => $user->id,
            'date' => now()->format('Y-m-d'),
            'time' => now()->format('H:i:s'),
            'updated_by' => $user->id,
            'notes' => 'New role created',
            'changed' => sprintf(
                "Created role: %s (Specific Role: %s)",
                $role->name,
                $specificRoleName
            ),
            'created_at' => now(),
            'updated_at' => now()
        ]);
    }



    /**
     * @OA\Post(
     * path="/uc/api/roles_permission/roleUpdate/{id}",
     * operationId="update roles",
     * tags={"HRMS Employee roles permissions"},
     * summary="add new roles",
     *   security={ {"Bearer": {} }},
     *    description="update/{id} add new roles",
     *    @OA\Parameter(
     *     name="id",
     *     in="path",
     *     required=true,
     *     @OA\Schema(type="string")
     *    ),
     *     @OA\RequestBody(
     *         @OA\JsonContent(),
     *         @OA\MediaType(
     *            mediaType="multipart/form-data",
     *            @OA\Schema(
     *               type="object",
     *               @OA\Property(property="name", type="string", description="define Role name like HR, Manager, TL"),
     *            ),
     *        ),
     *    ),
     *      @OA\Response(
     *          response=201,
     *          description="Role Updated successfully",
     *          @OA\JsonContent()
     *       ),
     *      @OA\Response(
     *          response=200,
     *          description="Role Updated successfully",
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

    public function roleUpdate(Request $request, $id){
         try {
            $validator = Validator::make($request->all(),[
                'name' => 'required|string',
             ]);

             if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'errors' => $validator->errors()
                ], 422);
            }

            $validatedData = $validator->validated();

            $role = HrmsRole::find($id);

            if ($role) {
                $role->update($validatedData);

                return $this->successResponse(
                    $role,
                    "Role Updated Successfully"
                );
            }else {
                return $this->errorResponse("The given data is not found");
            }
         } catch (\Throwable $th) {
             return $this->errorResponse($th->getMessage());
         }
    }




    /**
     * @OA\Get(
     * path="/uc/api/roles_permission/authRolepermissions",
     * operationId="authRolepermissions",
     * tags={"HRMS Employee roles permissions"},
     * summary="Get Auth roles and permissions Request",
     *   security={ {"Bearer": {} }},
     *    description="Get roles Request",
     *      @OA\Response(
     *          response=201,
     *          description="Auth Roles and Permissions Get Successfully",
     *          @OA\JsonContent()
     *       ),
     *      @OA\Response(
     *          response=200,
     *          description="Auth Roles and Permissions Get Successfully",
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

     public function authRolepermissions()
     {

         try {

               $auth_id =  auth('sanctum')->user()->id;
               $auth_role = DB::table('role_user')->where('user_id',$auth_id)->first();

               $employeeRole = DB::table('roles')->find($auth_role->role_id);

                if ($employeeRole->name != "admin") {
                        $role =   HrmsEmployeeRole::where('employee_id', $auth_id)->first();
                        if (!$role) {
                            return $this->errorResponse("don`t exists this role");
                        }

                             $authRolePermissions = HrmsRole::with(['viewrole', 'hrms_permissions'])->where('id', $role->role_id)->first();
                             $authRolePermissions["Admin"] = 0;

                        if (!$authRolePermissions) {
                             return $this->errorResponse("This role no permissions asign");
                        }

                        return $this->successResponse(
                             $authRolePermissions,
                             "Auth Role Premissions List"
                        );

               }else {

                       $admin["Admin"] = 1;
                        return $this->successResponse(
                            $admin,
                            "All Permissions For Admin"

                        );
               }




         } catch (\Throwable $th) {
             return $this->errorResponse($th->getMessage());
         }

     }





    /**
     * @OA\Get(
     * path="/uc/api/roles_permission/mainNewRoles",
     * operationId="seederrole",
     * tags={"HRMS Employee roles permissions"},
     * summary="Get Auth roles and permissions Request",
     *   security={ {"Bearer": {} }},
     *    description="Get roles Request",
     *      @OA\Response(
     *          response=201,
     *          description="Roles  Get Successfully",
     *          @OA\JsonContent()
     *       ),
     *      @OA\Response(
     *          response=200,
     *          description="Roles Get Successfully",
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

     public function mainNewRoles()
     {

         try {

             $newroles =   HrmsNewRole::all();

             if (!$newroles) {
                 return $this->errorResponse("This roles not found");
             }

            return $this->successResponse(
                $newroles,
                "Roles  List"

            );
         } catch (\Throwable $th) {
             return $this->errorResponse($th->getMessage());
         }

     }


    /**
     * @OA\Post(
     * path="/uc/api/roles_permission/updateNewRoles",
     * operationId="seederroleupdate",
     * tags={"HRMS Employee roles permissions"},
     * summary="Get Auth roles and permissions Request",
     *   security={ {"Bearer": {} }},
     *    description="Get roles Request",
     *     @OA\RequestBody(
     *         @OA\JsonContent(),
     *         @OA\MediaType(
     *            mediaType="multipart/form-data",
     *            @OA\Schema(
     *               type="object",
     *               @OA\Property(property="role_id", type="integer", description="define Role Id "),
     *               @OA\Property(property="view_id", type="integer", description="define view Id"),
     *            ),
     *        ),
     *    ),
     *      @OA\Response(
     *          response=201,
     *          description="Roles  Updated Successfully",
     *          @OA\JsonContent()
     *       ),
     *      @OA\Response(
     *          response=200,
     *          description="Roles Updated Successfully",
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

    //  public function updateNewRoles(Request $request)
    //  {

    //      try {

    //          $findRole =   HrmsRole::find($request->role_id);

    //         if (!$findRole) {
    //              return $this->errorResponse("This roles not found");
    //         }

    //         $findRole->specific_role_id = $request->view_id;

    //         $findRole->save();


    //         $permissions = $request->permissions;

    //          if (!empty($permissions)) {
    //             foreach ($permissions as $key => $permission) {
    //                 HrmsRolePermission::updateOrCreate(
    //                     ['role_id' => $request->role_id, 'permission_id' => $permission['permission_id']],
    //                     [
    //                         'can_view' => $permission['can_view'],
    //                         'can_edit' => $permission['can_edit'],
    //                        // 'can_access' => $permission->can_access,
    //                     ]
    //                 );
    //             }
    //          }

    //         return $this->successResponse(
    //             $findRole,
    //             "Roles and Permissions Updated Successfully"
    //         );
    //      } catch (\Throwable $th) {
    //          return $this->errorResponse($th->getMessage());
    //      }

    //  }




     /**
     * @OA\Get(
     * path="/uc/api/roles_permission/getRoleandPermissions",
     * operationId="getrolesandpermission",
     * tags={"HRMS Employee roles permissions"},
     * summary="Get roles Request",
     *   security={ {"Bearer": {} }},
     *    description="Get roles Request",
     *      @OA\Response(
     *          response=201,
     *          description=" Roles and Permissions Get Successfully",
     *          @OA\JsonContent()
     *       ),
     *      @OA\Response(
     *          response=200,
     *          description="Roles Permissions Get Successfully",
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

    //  public function getRoleandPermissions()
    //  {
    //     try {
    //         //  $roles = HrmsRole::with('hrms_permissions')->get();
    //          // $permissions = HrmsPermission::all();


    //         $roles = HrmsNewRole::with('hrms_permissions')->get();

    //          return response()->json([
    //              'roles' => $roles,
    //            //  'permissions' => $permissions,

    //          ]);

    //     } catch (\Throwable $th) {
    //         return $this->errorResponse($th->getMessage());
    //     }
    //  }




}


