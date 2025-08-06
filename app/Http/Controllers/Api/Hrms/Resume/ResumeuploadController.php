<?php

namespace App\Http\Controllers\Api\Hrms\Resume;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\HrmsResumeUpload;
use App\Models\User;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Redirect;
use DB;

class ResumeuploadController extends Controller
{


    protected $databaseSelect = [
        '1' => 'UC_hrms',
        '2' => 'UC_shivam_uc',
        '3' => 'UC_unifysmartsolutions'
    ];

    //protected $databaseSelect =  ['1'=>'uc_sdna','2'=>'uc_new','3'=>'student'];

    /**
     * @OA\Post(
     * path="/uc/api/resume/show/{id}",
     * operationId="showresume",
     * tags={"HRMS Resume Upload"},
     * summary="show resume",
     * security={ {"Bearer": {} }},
     * description="show resume",
     *
     * @OA\Parameter(
     *      name="id",
     *      in="path",
     *      required=true,
     *      description="Resume ID",
     *      @OA\Schema(type="string")
     * ),
     *
     *      @OA\Response(
     *          response=200,
     *          description="resume Get Successfully",
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

     public function show($id){
        try {


            $resume = HrmsResumeUpload::where('session_id', $id)->first();
            if (!$resume) {
                return response()->json(['error' => 'Resume not found'], 404);
            }

            return response()->json([
                'status' => true,
                'url' => url('public/newApplicantResume'),
                'message' => 'Show successfully',
                'data' => $resume
            ], 200);

        } catch (\Throwable $th) {
            return $this->errorResponse($th->getMessage());
        }
     }



        /**
     * @OA\Post(
     * path="/uc/api/resume/showPublic/{db}/{id}",
     * operationId="showPublic",
     * tags={"HRMS Resume Upload"},
     * summary="show resume public",
     * security={ {"Bearer": {} }},
     * description="show resume public",
     *
     *     @OA\Parameter(
     *      name="db",
     *      in="path",
     *      required=true,
     *      description="cdb",
     *      @OA\Schema(type="string")
     *     ),
     *
     * @OA\Parameter(
     *      name="id",
     *      in="path",
     *      required=true,
     *      description="Resume ID",
     *      @OA\Schema(type="string")
     * ),
     *
     *      @OA\Response(
     *          response=200,
     *          description="resume Get Successfully",
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


     public function showPublic(Request $request, $db, $id){

        try {

            // if (!isset($this->databaseSelect[$db])) {
            //     return response()->json(['message' => 'Invalid database ID'], 400);
            // }
            // $this->connectDB($this->databaseSelect[$db]);

            if(empty($db)){
                return response()->json(['message' => 'Invalid database ID'], 400);
            }
            $this->connectDB(base64_decode($db));

           // return  DB::connection()->getDatabaseName();

            $resume = HrmsResumeUpload::where('session_id', $id)->first();
            if (!$resume) {
                return response()->json(['error' => 'Resume not found'], 404);
            }
            return response()->json([
                'status' => true,
                'url' => url('public/newApplicantResume'),
                'message' => 'Show successfully',
                'data' => $resume
            ], 200);

        } catch (\Throwable $th) {

            return $this->errorResponse($th->getMessage());

        }

     }

    /**
     * @OA\post(
     * path="/uc/api/resume/create",
     * operationId="resumecreate",
     * tags={"HRMS Resume Upload"},
     * summary="Resume Upload Request",
     *   security={ {"Bearer": {} }},
     *    description="Get resume Request",
     *      @OA\Response(
     *          response=201,
     *          description=" resume Get Successfully",
     *          @OA\JsonContent()
     *       ),
     *      @OA\Response(
     *          response=200,
     *          description="resume Get Successfully",
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

    public function create(Request $request){

        try {

            $data =['session_id' => session()->getId()];
            $resumeId = HrmsResumeUpload::create($data);

            $user = Auth::guard('sanctum')->user();
            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized',
                ], 401);
            }
            $db = base64_encode($user->database_name);
            $resumeId['cdb'] = $db;
            return $this->successResponse( $resumeId, 'Resume date created successfully');

        } catch (\Throwable $th) {
            return $this->errorResponse($th->getMessage());
        }
    }



    /**
     * @OA\post(
     * path="/uc/api/resume/createPublic/{id}",
     * operationId="createPublic",
     * tags={"HRMS Resume Upload"},
     * summary="Resume Upload Request public",
     *   security={ {"Bearer": {} }},
     *    description="Get resume Request",
     *    @OA\Parameter(
     *           name="id",
     *           in="path",
     *           required=true,
     *           @OA\Schema(type="string")
     *          ),
     *      @OA\Response(
     *          response=201,
     *          description=" resume Get Successfully",
     *          @OA\JsonContent()
     *       ),
     *      @OA\Response(
     *          response=200,
     *          description="resume Get Successfully",
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

    public function createPublic(Request $request, $id){

        try {
            // if (!isset($this->databaseSelect[$id])) {
            //     return response()->json(['message' => 'Invalid database ID'], 400);
            // }
            // $this->connectDB($this->databaseSelect[$id]);

            if(empty($id)){
                return response()->json(['message' => 'Invalid database ID'], 400);
            }
            $this->connectDB(base64_decode($id));

           //return  DB::connection()->getDatabaseName();


            $data =['session_id' => session()->getId()];
            $resumeId = HrmsResumeUpload::create($data);

            // $user = Auth::guard('sanctum')->user();
            // if (!$user) {
            //     return response()->json([
            //         'success' => false,
            //         'message' => 'Unauthorized',
            //     ], 401);
            // }

            $db = base64_encode($this->databaseSelect[$id]);
            $resumeId['cdb'] = $id;
            return $this->successResponse( $resumeId, 'Resume date created successfully');


        } catch (\Throwable $th) {
            return $this->errorResponse($th->getMessage());
        }

    }


    /**
     * @OA\Post(
     * path="/uc/api/resume/update/{id}",
     * operationId="updateresume",
     * tags={"HRMS Resume Upload"},
     * summary="Update resume",
     * security={ {"Bearer": {} }},
     * description="Update resume",
     *
     * @OA\Parameter(
     *      name="id",
     *      in="path",
     *      required=true,
     *      description="Resume ID",
     *      @OA\Schema(type="string")
     * ),
     *
     * @OA\RequestBody(
     *     required=true,
     *     @OA\MediaType(
     *        mediaType="multipart/form-data",
     *        @OA\Schema(
     *           type="object",
     *           required={"resume"},
     *           @OA\Property(property="resume", type="string", description="Resume file to upload")
     *        ),
     *     ),
     * ),
     *
     *      @OA\Response(
     *          response=200,
     *          description="resume Get Successfully",
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

     public function update(Request $request, $id){
        try {
            $resume = HrmsResumeUpload::where('session_id', $id)->first();
            if (!$resume) {
                return response()->json(['error' => 'Resume not found'], 404);
            }

            $resume->resume_name = $request->input('resume');
            $resume->save();

            return $this->successResponse( $resume, 'Resume update successfully');

        } catch (\Throwable $th) {
            return $this->errorResponse($th->getMessage());
        }
    }


    /**
     * @OA\Post(
     * path="/uc/api/resume/updatePublic/{db}/{id}",
     * operationId="updatePublic",
     * tags={"HRMS Resume Upload"},
     * summary="Update resume public",
     * security={ {"Bearer": {} }},
     * description="Update resume",
     *     @OA\Parameter(
     *      name="db",
     *      in="path",
     *      required=true,
     *      description="cdb",
     *      @OA\Schema(type="string")
     *     ),
     * @OA\Parameter(
     *      name="id",
     *      in="path",
     *      required=true,
     *      description="Resume ID",
     *      @OA\Schema(type="string")
     * ),
     *
     * @OA\RequestBody(
     *     required=true,
     *     @OA\MediaType(
     *        mediaType="multipart/form-data",
     *        @OA\Schema(
     *           type="object",
     *           required={"resume"},
     *           @OA\Property(property="resume", type="string", description="Resume file to upload")
     *        ),
     *     ),
     *    ),
     *
     *      @OA\Response(
     *          response=200,
     *          description="resume Get Successfully",
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


     public function updatePublic(Request $request, $db, $id){

        try {

            // if (!isset($this->databaseSelect[$db])) {
            //     return response()->json(['message' => 'Invalid database ID'], 400);
            // }
            // $this->connectDB($this->databaseSelect[$db]);

            if(empty($db)){
                return response()->json(['message' => 'Invalid database ID'], 400);
            }
            $this->connectDB(base64_decode($db));

            $resume = HrmsResumeUpload::where('session_id', $id)->first();
            if (!$resume) {
                return response()->json(['error' => 'Resume not found'], 404);
            }

            $resume->resume_name = $request->input('resume');
            $resume->save();

            return $this->successResponse( $resume, 'Resume update successfully');

        } catch (\Throwable $th) {
            return $this->errorResponse($th->getMessage());
        }
     }


        /**
     * @OA\Post(
     * path="/uc/api/applicantResume/store/{id}",
     * operationId="storeresume",
     * tags={"HRMS Resume Upload"},
     * summary="store resume",
     * security={ {"Bearer": {} }},
     * description="store resume",
     *
     * @OA\Parameter(
     *      name="id",
     *      in="path",
     *      required=true,
     *      description="Resume ID",
     *      @OA\Schema(type="string")
     * ),
     *
     * @OA\RequestBody(
     *     required=true,
     *     @OA\MediaType(
     *        mediaType="multipart/form-data",
     *        @OA\Schema(
     *           type="object",
     *           required={"resume"},
     *           @OA\Property(property="resume", type="string", format="binary", description="Resume file to upload"),
     *           @OA\Property(property="from", type="string", description="Resume upload from db"),
     *        ),
     *     ),
     * ),
     *
     *      @OA\Response(
     *          response=200,
     *          description="resume Get Successfully",
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

    public function store(Request $request, $id){

        try {

        $request->validate([
            'resume' => 'required|file|mimes:pdf,doc,docx|max:307200',
            'from' => 'required|string',
        ]);

        $cdb = $request->input('from'); // 'UC_unifysmartsolutions'; //live db
        $databaseName = base64_decode($cdb);
        $this->connectDB($databaseName);
        // Find the resume entry by session_id
        $resume = HrmsResumeUpload::where('session_id', $id)->first();
        if (!$resume) {
            return response()->json(['message' => 'Resume not found'], 404);
        }

        $directory = '/public/newApplicantResume/';

        // Handle the file upload
        if ($request->hasFile('resume')) {
            $file = $request->file('resume');
            $filename = time() . '_' . $file->getClientOriginalName();

            // Ensure the directory exists
            $destinationPath = public_path('newApplicantResume');
            if (!file_exists($destinationPath)) {
                mkdir($destinationPath, 0775, true);
            }
            // Move the uploaded file
            $file->move($destinationPath, $filename);

            $resume->resume_name = $filename;
            $resume->save();
        }

        return response()->json([
            'message' => 'Resume updated successfully',
            'data' => $resume
        ], 200);

        } catch (\Throwable $th) {
            return $this->errorResponse([
                'line' => $th->getLine(),
                'file' => $th->getFile(),
                'trace' => $th->getTraceAsString(),
                'message' => $th->getMessage()
            ]);
        }

    }


    /**
     * @OA\Post(
     * path="/uc/api/applicantResume/storePublic/{db}/{id}",
     * operationId="storeresumepublic",
     * tags={"HRMS Resume Upload"},
     * summary="store resume public",
     * security={ {"Bearer": {} }},
     * description="store resume public",
     *
     * @OA\Parameter(
     *      name="db",
     *      in="path",
     *      required=true,
     *      description="Resume ID",
     *      @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *      name="id",
     *      in="path",
     *      required=true,
     *      description="Resume ID",
     *      @OA\Schema(type="string")
     * ),
     *
     * @OA\RequestBody(
     *     required=true,
     *     @OA\MediaType(
     *        mediaType="multipart/form-data",
     *        @OA\Schema(
     *           type="object",
     *           required={"resume"},
     *           @OA\Property(property="resume", type="string", format="binary", description="Resume file to upload"),
     *           @OA\Property(property="from", type="string", description="Resume upload from db"),
     *        ),
     *     ),
     * ),
     *
     *      @OA\Response(
     *          response=200,
     *          description="resume Get Successfully",
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

     public function storePublic(Request $request, $db, $id){


        try {


            // if (!isset($this->databaseSelect[$db])) {
            //     return response()->json(['message' => 'Invalid database ID'], 400);
            // }
            // $this->connectDB($this->databaseSelect[$db]);


            if(empty($db)){
                return response()->json(['message' => 'Invalid database ID'], 400);
            }
            $this->connectDB(base64_decode($db));

           // return  DB::connection()->getDatabaseName();

            $request->validate([
                'resume' => 'required|file|mimes:pdf,doc,docx|max:307200',
                'from' => 'required|string',
            ]);

            // $cdb = $request->input('from'); // 'UC_unifysmartsolutions'; //live db
            // $databaseName = base64_decode($cdb);
            // $this->connectDB($databaseName);
            // Find the resume entry by session_id

            $resume = HrmsResumeUpload::where('session_id', $id)->first();
            if (!$resume) {
                return response()->json(['message' => 'Resume not found'], 404);
            }

            $directory = '/public/newApplicantResume/';

            // Handle the file upload
            if ($request->hasFile('resume')) {
                $file = $request->file('resume');
                $filename = time() . '_' . $file->getClientOriginalName();

                // Ensure the directory exists
                $destinationPath = public_path('newApplicantResume');
                if (!file_exists($destinationPath)) {
                    mkdir($destinationPath, 0775, true);
                }
                // Move the uploaded file
                $file->move($destinationPath, $filename);

                $resume->resume_name = $filename;
                $resume->save();
            }

            return response()->json([
                'message' => 'Resume updated successfully',
                'data' => $resume
            ], 200);

            } catch (\Throwable $th) {
                return $this->errorResponse([
                    'line' => $th->getLine(),
                    'file' => $th->getFile(),
                    'trace' => $th->getTraceAsString(),
                    'message' => $th->getMessage()
                ]);
            }

     }





    /**
     * @OA\Get(
     *     path="/uc/api/generatePublicToken",
     *     operationId="generateToken",
     *     tags={"HRMS Resume Upload"},
     *     summary="Generate token",
     *     security={ {"Bearer": {} }},
     *     description="Store resume public",
     *     @OA\Parameter(
     *         name="cdb",
     *         in="query",
     *         description="Some string parameter",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="manager_id",
     *         in="query",
     *         description="manager Id",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Generate token Successfully",
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




    public function generatePublicToken(Request $request)
    {
        try {
            // Validate the input
            $cdb = $request->input('cdb');
            $manId = $request->input('manager_id');
            $interviewMode = (int)$request->input('interview_mode', 0); // Cast to int, default 0 (offline)
            if (!$cdb || !is_string($cdb)) {
                return response()->json(['message' => 'Invalid request: CDB is required'], 400);
            }

            // if (!$manId) {
            //     return response()->json(['message' => 'in valid manager id'], 400);
            // }

            $manId = $manId ?? "null";

            // Validate interview mode (should be 0 or 1)
            $interviewMode = ($interviewMode === 1) ? 1 : 0;

            // Generate a unique token
            $tokens = Str::random(30);
            // $token = $cdb . '@@' . $tokens.'@@@manId'.$manId;
            $token = $cdb . '@@' . $tokens . '@@@manId' . $manId;

            // Store the token in cache with expiration
            Cache::put('public_token:' . $token, ['cdb' => $cdb, 'expires_at' => now()->addMinutes(50)], now()->addMinutes(50));

            return Redirect::away(config('app.frontend_url') . 'application-form?token=' . $token .'&interview_mode=' . $interviewMode);
        } catch (\Throwable $th) {
            return $this->errorResponse($th->getMessage());
        }
    }



     public function publicdata(Request $request){

        return "Okay token is working";

     }



}
