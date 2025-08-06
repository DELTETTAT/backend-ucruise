<?php


namespace App\Http\Controllers\Api;
use Illuminate\Support\Facades\Hash;
use App\Models\CompanyAddresse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\CompanyDetails;
use App\Models\SubUser;
use App\Models\GroupLoginUser;
use App\Models\User;
use DB;

use Illuminate\Support\Facades\Config;


class AuthController extends Controller
{

    public function __construct()
    {
        $this->middleware('auth:api', ['except' => ['login', 'logout','getMultipleSubUsers']]);
    }

    /**
     * @OA\Post(
     * path="/uc/api/login",
     * operationId="login",
     * tags={"Login User"},
     * summary="Login",
     *   security={ {"Bearer": {} }},
     * description="User Login",
     *     @OA\RequestBody(
     *         @OA\JsonContent(),
     *         @OA\MediaType(
     *            mediaType="multipart/form-data",
     *            @OA\Schema(
     *               type="object",
     *               required={"password"},
     *               @OA\Property(property="email", type="text"),
     *               @OA\Property(property="password", type="text"),
     *               @OA\Property(property="loginType", type="text", description="Required if loginType is driver, employee"),
     *               @OA\Property(property="fcm_token", type="text"),
     *               
     *            ),
     *        ),
     *    ),
     *      @OA\Response(
     *          response=201,
     *          description="Login Successfully",
     *          @OA\JsonContent()
     *       ),
     *      @OA\Response(
     *          response=200,
     *          description="Login Successfully",
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


    //old code

    // public function login(Request $request)
    // {
    //     try {
    //         $token = null;
    //         $request->validate([
    //             'email' => 'required|string|email',
    //             'password' => 'required|string',
    //         ]);
    //         $credentials = $request->only('email', 'password');
    //         if (!Auth::guard('staff')->attempt($credentials)) {
    //             return response()->json(['success' => false, 'message' => 'Invalid credentials'], 401);
    //         }

    //         $user = auth()->guard('staff')->user();
    //         $role = $user->roles()->pluck('name')->implode(',');

    //         return response()->json([
    //             'success' => true,
    //             'user' => $user,
    //             'role' => $role,
    //             'message' => 'Login successful',
    //             'token' => auth()->guard('staff')->user()->createToken('MyApp')->plainTextToken
    //         ], 200);
    //     } catch (\Throwable $th) {
    //         return response()->json([
    //             'success' => false,
    //             'message' => $th->getMessage()
    //         ], 500);
    //     }
    // }

    //new code
    public function login(Request $request)
    {
        try {
            $token = null;
            // $request->validate([
            //     'email' => 'required|string|email',
            //     'password' => 'required|string',
            // ]);


            $request->validate([
                'loginType' => 'nullable|in:driver,employee',
                'email'     => [
                'required',
                'string',
                function ($attribute, $value, $fail) {
                    if (is_numeric($value)) {
                        if (!preg_match('/^[0-9]{10}$/', $value)) {
                            $fail('The phone must be a valid 10-digit number.');
                        }
                    } else {
                        if (!filter_var($value, FILTER_VALIDATE_EMAIL)) {
                            $fail('The ' . $attribute . ' must be a valid email address.');
                        }
                    }
                }
            ],
                'password'  => 'required|string',
            ]);

            $loginType = $request->input('loginType');
            $email = $request->input('email');

            if($request->filled('email') && is_numeric($email)) {
                $credentials = ['phone' => $email,'password' => $request->input('password')];
            }else{
                $credentials = $request->only('email', 'password');
            }

            // Attempt login using staff guard
            if (!Auth::guard('staff')->attempt($credentials)) {
                // If staff login fails, attempt login using admin guard
                if (!Auth::guard('web')->attempt($credentials)) {
                    return response()->json(['success' => false, 'message' => 'Invalid credentials'], 401);
                }
            }
            
            if (!Auth::guard('web')->attempt($credentials)) {
                if (!Auth::guard('staff')->attempt($credentials)) {
                    return response()->json(['success' => false, 'message' => 'Invalid credentials'], 401);
                }
            }

            // Determine the user based on the guard
            // if (Auth::guard('staff')->check()) {
            //     $user_id = auth()->guard('staff')->user()->id;
            //     if($request->user_id){

            //     }else{
            //     $update_sub_user = SubUser::find($user_id);
            //     }


            if (Auth::guard('staff')->check()) {

                $user_id = auth()->guard('staff')->user()->id;
                
                // Check if request has a 'user_id'
                if ($request->has('user_id')) {
                    $custom_user_id = $request->user_id; // Use the custom user_id from request
                    $user_id = $custom_user_id;
                    // Do something with $custom_user_id if necessary
                } else {
                    // If no 'user_id' in request, use the authenticated user's ID
                    $custom_user_id = $user_id;
                }
            
                // Now, find the SubUser with the custom_user_id
                $update_sub_user = SubUser::find($custom_user_id);
            
            
                if ($update_sub_user) {
                    $update_sub_user->fcm_id = $request->fcm_token;
                    $update_sub_user->save();
                }
                $temp_DB_name = auth()->guard('staff')->user()->database_name;
                $default_DBName = env("DB_DATABASE");

                $this->connectDB($temp_DB_name);
                if($request->user_id){
                    $update_child_sub_user = SubUser::where('id', $request->user_id)->first();
                }
                else{
                    $update_child_sub_user = SubUser::where('email', $update_sub_user->email)->first();
                }
                

                if ($update_child_sub_user) {
                    $update_child_sub_user->fcm_id = $request->fcm_token;
                    $update_child_sub_user->save();
                }
                //new code added

              // $all_sub_users = SubUser::where('email', $update_sub_user->email)->get();
             
                $companyAddressExists = CompanyAddresse::exists() ? 1 : 0;
                //end of new 
                $this->connectDB($default_DBName);
                $all_sub_users = SubUser::where('email', $update_sub_user->email)
                ->whereHas('roles', function ($query) {
                    $query->where('name', 'driver');
                 })
                ->get();
  
                //info('here ' . $subUsers);
                
                
                // Return all subusers' IDs, company names, and roles for checkbox selection
                $subUserData = $all_sub_users->map(function ($all_sub_users) {
                $role = $all_sub_users->roles()->pluck('name')->implode(','); // Get all roles
                return [
                    'id' => $all_sub_users->id,
                    'company_name' => $all_sub_users->company_name,
                    'roles' => $role, 
                ];
                });
  
                  // Update FCM tokens for all SubUsers in the temporary database
                  foreach ($all_sub_users as $child_user) {
                      $child_user->fcm_id = $request->fcm_token;
                      $child_user->save();
                  }
                $user = $update_sub_user;
                $guard = 'staff';
                $token_user = $update_sub_user ?? auth()->guard('staff')->user(); // Ensure token is for the correct user
                $token = $token_user->createToken('MyApp')->plainTextToken;
            } else if (Auth::guard('web')->check()) {
                $user_id = auth()->guard('web')->user()->id;

                $update_user = User::find($user_id);
                if ($update_user) {
                    $update_user->fcm_id = $request->fcm_token;
                    $update_user->update();
                }
                //new code added
                $temp_DB_name = auth()->guard('web')->user()->database_name;
                $default_DBName = env("DB_DATABASE");
                $this->connectDB($temp_DB_name);
                $companyAddressExists = CompanyAddresse::exists() ? 1 : 0;
                $this->connectDB($default_DBName);
                $subUserData = User::where('email', $update_user->email)
               
                ->first();
  
                //info('here ' . $subUsers);
                //end of new code
                $user = $update_user;
                $guard = 'web';
                $token_user = $update_user ?? auth()->guard('web')->user(); // Ensure token is for the correct user
                $token = $token_user->createToken('MyApp')->plainTextToken;
            }

            $role = $user->roles()->pluck('name')->implode(',');


            return response()->json([
                'success' => true,
                'user' => $user,
                'role' => $role,
                'all_user'=>$subUserData,
                'companyAddressExists' => @$companyAddressExists,
                'message' => 'Login successful',
                'token' => $token,
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'success' => false,
                'message' => $th->getMessage()
            ], 500);
        }
    }

    /**
     * @OA\Get(
     * path="/uc/api/logout",
     * operationId="logout",
     * tags={"Login User"},
     * summary="Logout",
     *   security={ {"Bearer": {} }},
     * description="Logout",
     *      @OA\Response(
     *          response=201,
     *          description="Successfully logout",
     *          @OA\JsonContent()
     *       ),
     *      @OA\Response(
     *          response=200,
     *          description="Successfully logout",
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
    public function logout()
    {
        try {
            // Check if the user is authenticated
            $user = auth('sanctum')->user();

            if ($user) {
                // Delete the current access token
                $deleteToken = $user->currentAccessToken()->delete();

                if ($deleteToken) {
                    // Logout successful response
                    return response()->json([
                        'success' => true,
                        'message' => 'Logout successful',
                    ], 200);
                } else {
                    // Failed to delete token response
                    return response()->json([
                        'success' => false,
                        'message' => 'Failed to logout',
                    ], 500);
                }
            } else {
                // User not authenticated response
                return response()->json([
                    'success' => false,
                    'message' => 'User not authenticated',
                ], 401);
            }
        } catch (\Throwable $th) {
            // Exception caught response
            return response()->json([
                'success' => false,
                'message' => $th->getMessage(),
            ], 500);
        }
    }

    public function connectDB($db_name)
    {
        $default = [
            "driver" => env("DB_CONNECTION", "mysql"),
            "host" => env("DB_HOST"),
            "port" => env("DB_PORT"),
            "database" => $db_name,
            "username" => env("DB_USERNAME"),
            "password" => env("DB_PASSWORD"),
            "charset" => "utf8mb4",
            "collation" => "utf8mb4_unicode_ci",
            "prefix" => "",
            "prefix_indexes" => true,
            "strict" => false,
            "engine" => null,
        ];

        Config::set("database.connections.$db_name", $default);
        Config::set("client_id", 1);
        Config::set("client_connected", true);
        DB::setDefaultConnection($db_name);
        DB::purge($db_name);
    }

       /**
     * @OA\Post(
     * path="/uc/api/getMultipleSubUsers",
     * operationId="getMultipleSubUsers",
     * tags={"Login User"},
     * summary="Login",
     *   security={ {"Bearer": {} }},
     * description="User Login",
     *     @OA\RequestBody(
     *         @OA\JsonContent(),
     *         @OA\MediaType(
     *            mediaType="multipart/form-data",
     *            @OA\Schema(
     *               type="object",
     *               required={"email"},
     *               @OA\Property(property="email", type="text"),
     
     *               
     *            ),
     *        ),
     *    ),
     *      @OA\Response(
     *          response=201,
     *          description="Login Successfully",
     *          @OA\JsonContent()
     *       ),
     *      @OA\Response(
     *          response=200,
     *          description="Login Successfully",
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
    public function getMultipleSubUsers(Request $request)
    {
        try {
            $authUser = auth('sanctum')->user();
            $email = $authUser->email;
            
            // Get the authenticated user's role(s)
            $authRoles = $authUser->roles()->pluck('name')->toArray();
           
            if (empty($authRoles)) {
                return response()->json([
                    'success' => false,
                    'message' => 'No roles assigned to authenticated user',
                ], 403);
            }

        
           $groupUser = GroupLoginUser::where('email', $email)->first();
           $subUsers = collect(); 
            if ($groupUser && is_array($groupUser->user_id)) {
                $subUsers = SubUser::whereIn('id', $groupUser->user_id)
                    ->whereHas('roles', function ($query) use ($authRoles) {
                        $query->whereIn('name', $authRoles);
                    })
                    ->get();
            }

    
            // $subUsers = SubUser::where('email', $email)
            //                   ->whereHas('roles', function ($query) use ($authRoles) {
            //                       $query->whereIn('name', $authRoles);
            //                    })
            //                   ->get();

    
            if ($subUsers->isEmpty()) {
                return response()->json([
                    'success' => false,
                    //'message' => 'No sub-users found with matching roles',
                ], 404);
            }
    
            // Generate tokens and collect user data
            $subUserData = $subUsers->map(function ($subUser) use ($authUser) {
                $roles = $subUser->roles()->pluck('name')->implode(',');
                $token = $subUser->createToken('MyApp')->plainTextToken;
                
                return [
                    'id' => $subUser->id,
                    'company_name' => $subUser->company_name,
                    'roles' => $roles,
                    'token' => $token,
                    'is_login' => $subUser->id == $authUser->id ? 1 : 0,
                ];
            });
    
            return response()->json([
                'success' => true,
                'role'=> $authRoles,
                'sub_users' => $subUserData,
            ], 200);
    
        } catch (\Throwable $th) {
            return response()->json([
                'success' => false,
                'message' => $th->getMessage(),
            ], 500);
        }
    }
}
