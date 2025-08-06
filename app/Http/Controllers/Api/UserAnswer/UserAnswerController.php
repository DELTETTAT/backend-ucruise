<?php

namespace App\Http\Controllers\Api\UserAnswer;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\HiringQuiz;
use App\Models\QuizAnswerDetail;
use App\Models\UserAnswerDetail;
use App\Models\QuizQuestionDetail;
use App\Models\NewApplicant;
use App\Http\Requests\StoreUserAnswerRequest;
use DB;

class UserAnswerController extends Controller
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
     * @OA\get(
     * path="/uc/api/new_applicant/quiz/index",
     * operationId="quizlist",
     * tags={"New Applicant Quiz"},
     * summary="New Applicant Quiz",
     *   security={ {"Bearer": {} }},
     * description="New Applicant Quiz",
     *     @OA\Parameter(
     *         name="new_applicant_id",
     *         in="query",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *      @OA\Response(
     *          response=201,
     *          description="New Applicant Quiz",
     *          @OA\JsonContent()
     *       ),
     *      @OA\Response(
     *          response=200,
     *          description="New Applicant Quiz",
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

            $request->validate([
                'new_applicant_id' => 'required'
            ]);

            $applicant = NewApplicant::findOrFail($request->new_applicant_id);

            if (!$applicant) {
                return response()->json([
                    'error' => 'Applicant not found.',
                ], 404);
            }

            $alreadySubmitted = UserAnswerDetail::where('new_applicant_id', $request->new_applicant_id)->exists();

            if ($alreadySubmitted) {
                return response()->json([
                    'message' => 'You have already submitted the quiz.'
                ], 400);
            }

            // $hiringQuiz = HiringQuiz::with(['getDesignationDetails', 'getQuizLevel'])
            // ->where('desgination_id', $applicant->designation_id)
            // ->where('quiz_level_id', $applicant->role)
            // ->first();

            $experienceLevel = 0;
            $experience = $applicant->experience;
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
            ->where('desgination_id', $applicant->designation_id)
            ->where('quiz_level_id', $experienceLevel)
            ->first();

            $quizQuestions = [];

            if ($hiringQuiz) {
                $quizQuestions = QuizQuestionDetail::where('hiring_quiz_id', $hiringQuiz->id)->get();
                foreach ($quizQuestions as $question) {
                    $question->answers = QuizAnswerDetail::where('quiz_question_detail_id', $question->id)->get();
                }
            }
            else{
                return response()->json([
                    'error' => 'No Quiz found for selected designation.'
                ], 422);
            }
            return response()->json([
                'hiringQuiz'    => $hiringQuiz,
                'quizQuestions' => $quizQuestions,
            ]);
        } catch (\Exception $th) {
            return response()->json([
                'error' => 'An error occurred while processing your request.'
            ], 500);
        }
    }


     /**
     * @OA\post(
     * path="/uc/api/new_applicant/publicquiz/{id}",
     * operationId="publicquiz",
     * tags={"New Applicant Quiz"},
     * summary="New Applicant Quiz",
     *   security={ {"Bearer": {} }},
     * description="New Applicant Quiz",
     *    @OA\Parameter(
     *     name="id",
     *     in="path",
     *     required=true,
     *     @OA\Schema(type="string")
     *    ),
     *      @OA\Response(
     *          response=201,
     *          description="New Applicant Quiz",
     *          @OA\JsonContent()
     *       ),
     *      @OA\Response(
     *          response=200,
     *          description="New Applicant Quiz",
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


     public function publicquiz(Request $request, $id){


        try {


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


            $request->validate([
                'new_applicant_id' => 'required'
            ]);

            $applicant = NewApplicant::findOrFail($request->new_applicant_id);

            if (!$applicant) {
                return response()->json([
                    'error' => 'Applicant not found.',
                ], 404);
            }

            $alreadySubmitted = UserAnswerDetail::where('new_applicant_id', $request->new_applicant_id)->exists();

            if ($alreadySubmitted) {
                return response()->json([
                    'message' => 'You have already submitted the quiz.'
                ], 400);
            }

            $experienceLevel = 0;
            $experience = $applicant->experience;
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
            ->where('desgination_id', $applicant->designation_id)
            ->where('quiz_level_id', $experienceLevel)
            ->first();

            $quizQuestions = [];

            if ($hiringQuiz) {
                $quizQuestions = QuizQuestionDetail::where('hiring_quiz_id', $hiringQuiz->id)->get();
                foreach ($quizQuestions as $question) {
                    $question->answers = QuizAnswerDetail::where('quiz_question_detail_id', $question->id)->get();
                }
            }
            else{
                return response()->json([
                    'error' => 'No Quiz found for selected designation.'
                ], 422);
            }
            return response()->json([
                'hiringQuiz'    => $hiringQuiz,
                'quizQuestions' => $quizQuestions,
            ]);
        } catch (\Exception $th) {
            return response()->json([
                'error' => 'An error occurred while processing your request.'
            ], 500);
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
    public function store(StoreUserAnswerRequest $request)
    {
        try {
            $validatedData = $request->validated();
            $newApplicantId = $validatedData['new_applicant_id'];
            $hiringQuizId = $validatedData['hiring_quiz_id'];
            $questionDetails = $validatedData['questionDetails'];

            $insertData = [];


            foreach ($questionDetails as $question) {
                $questionId = $question['id'];
                $questionTypeId = $question['question_type_id'] ?? null;
                foreach ($question['answer'] as $answer) {
                    if ($answer['is_Select'] ==1) {
                        $description = null;

                        if ($questionTypeId == 3) {
                            $description = $answer['description'] ?? null;
                        }
                        $insertData[] = [
                            'new_applicant_id' => $newApplicantId,
                            'quiz_id' => $hiringQuizId,
                            'question_id' => $questionId,
                            'answer_id' => $answer['id'],
                            'question_type_id' => $questionTypeId,
                            'description' => $description,
                            'is_answer_correct' => $answer['is_answer_correct'],
                        ];
                    }
                }
            }

            if (!empty($insertData)) {
                UserAnswerDetail::insert($insertData);
            }

            $findNewApplicant = NewApplicant::find($newApplicantId);
            $findNewApplicant->quiz_status = 1 ;
            $findNewApplicant->save();

            return response()->json(['message' => 'Selected answers stored successfully.'], 200);
        } catch (QueryException $e) {
            return response()->json(['error' => 'Failed to store selected answers due to a database error.'], 500);
        } catch (\Exception $e) {
            return response()->json(['error' => 'An unexpected error occurred. Please try again later.'], 500);
        }
    }


         /**
     * @OA\post(
     * path="/uc/api/new_applicant/storePublicdate/{id}",
     * operationId="storePublicdate",
     * tags={"New Applicant Quiz"},
     * summary="New Applicant Quiz",
     *   security={ {"Bearer": {} }},
     * description="New Applicant Quiz",
     *    @OA\Parameter(
     *     name="id",
     *     in="path",
     *     required=true,
     *     @OA\Schema(type="string")
     *    ),
     *      @OA\Response(
     *          response=201,
     *          description="New Applicant Quiz",
     *          @OA\JsonContent()
     *       ),
     *      @OA\Response(
     *          response=200,
     *          description="New Applicant Quiz",
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



     public function storePublicdate(StoreUserAnswerRequest $request, $id)
     {
         try {

            //  $token = $request->query('token');

            //  if(empty($token)){
            //      return response()->json(['message' => 'Invalid database ID'], 400);
            //  }

            //  $decodedDatabase = base64_decode($token);

            //  $databaseName = explode('@@', $decodedDatabase)[0];

            //  $this->connectDB($databaseName);

            // DB::connection()->getDatabaseName();

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



             $validatedData = $request->validated();
             $newApplicantId = $validatedData['new_applicant_id'];
             $hiringQuizId = $validatedData['hiring_quiz_id'];
             $questionDetails = $validatedData['questionDetails'];

             $insertData = [];

             foreach ($questionDetails as $question) {
                 $questionId = $question['id'];
                 $questionTypeId = $question['question_type_id'] ?? null;
                 foreach ($question['answer'] as $answer) {
                     if ($answer['is_Select'] ==1) {
                         $description = null;


                         if ($questionTypeId == 3) {
                             $description = $answer['description'] ?? null;
                         }
                         $insertData[] = [
                             'new_applicant_id' => $newApplicantId,
                             'quiz_id' => $hiringQuizId,
                             'question_id' => $questionId,
                             'answer_id' => $answer['id'],
                             'question_type_id' => $questionTypeId,
                             'description' => $description,
                             'is_answer_correct' => $answer['is_answer_correct'],
                         ];
                     }
                 }
             }

             if (!empty($insertData)) {
                 UserAnswerDetail::insert($insertData);
             }

            $findNewApplicant = NewApplicant::find($newApplicantId);
            $findNewApplicant->quiz_status = 1 ;
            $findNewApplicant->save();

             return response()->json(['message' => 'Selected answers stored successfully.'], 200);
         } catch (QueryException $e) {
             return response()->json(['error' => 'Failed to store selected answers due to a database error.'], 500);
         } catch (\Exception $e) {
             return response()->json([
             'error' => 'An unexpected error occurred. Please try again later',
             'line' => $e->getLine(),
             'file' => $e->getFile(),
             'trace' => $e->getTraceAsString(),
             'message' => $e->getMessage()
             ],500);
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




     /**
     * @OA\post(
     * path="/uc/api/new_applicant/quiz/applicantAnswerQuiz",
     * operationId="getApplicantAnswerQuiz",
     * tags={"New Applicant Quiz"},
     * summary="Get Candidates Quiz Request",
     *   security={ {"Bearer": {} }},
     * description="Get Candidates Quiz Request",
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
     *          description="Candidates Quiz Get Successfully",
     *          @OA\JsonContent()
     *       ),
     *      @OA\Response(
     *          response=200,
     *          description="Candidates Quiz Get Successfully",
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
    public function applicantAnswerQuiz(Request $request)
    {
        try {

          $applicantQuiz = UserAnswerDetail::where('new_applicant_id', $request->applicant_id)->first();

          $findHiringQuizDetails = HiringQuiz::with(
              //  "getDesignationDetails",
              //  "getuserDetails",
                "getQuizLevel",
                "getQuizQuestionDetails.answerDetail"
            )->find($applicantQuiz->quiz_id);

            $findHiringQuizDetails->getQuizQuestionDetails->transform(function ($question) use ($request) {

            $applicantAnswer = UserAnswerDetail::where([
                'new_applicant_id' => $request->applicant_id,
                'question_id' => $question->id
            ])->get();

            $question->applicant_answer = $applicantAnswer ? $applicantAnswer : null;

            return $question;
        });

            return $this->successResponse(
                $findHiringQuizDetails,
                "Applicant Quiz"
            );

        } catch (\Throwable $th) {
            return $this->errorResponse($th->getMessage());
        }
    }










}
