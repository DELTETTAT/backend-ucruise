<?php

namespace App\Http\Controllers\Api\Hrms\Quiz\HiringQuiz;

use App\Http\Controllers\Controller;
use App\Http\Requests\Hrms\Quiz\HiringQuiz\HiringQuizRequest;
use App\Http\Resources\Hrms\Quiz\HiringQuiz\HiringQuizCollection;
use App\Http\Resources\Hrms\Quiz\HiringQuiz\HiringQuizEditResource;
use App\Models\HiringQuiz;
use App\Models\QuizAnswerDetail;
use App\Models\QuizQuestionDetail;
use App\Models\UpdateSystemSetupHistory;
use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;

class HiringquizController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */

    /**
     * @OA\Get(
     * path="/uc/api/quiz/hiring_quiz/index",
     * operationId="gethiring_quiz",
     * tags={"Hiring Quiz"},
     * summary="Get hiring_quiz Request",
     *   security={ {"Bearer": {} }},
     * description="Get hiring_quiz Request",
     *      @OA\Response(
     *          response=201,
     *          description="hiring_quiz Get Successfully",
     *          @OA\JsonContent()
     *       ),
     *      @OA\Response(
     *          response=200,
     *          description="hiring_quiz Get Successfully",
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
            // Fetching quizzes with their related details
            $getQuestionTypeList = HiringQuiz::select("id", "name", "desgination_id", "quiz_level_id", "created_at")->with("getDesignationDetails:id,title", "getUserDetails")
                ->join('quiz_levels', 'hiring_quizzes.quiz_level_id', '=', 'quiz_levels.id') // Joining quiz_levels table
                ->select('hiring_quizzes.*', 'quiz_levels.title as level_name') // Selecting required fields
                //->orderBy('hiring_quizzes.id', 'desc')
                ->orderBy('quiz_levels.id', 'desc')
                ->get()
                ->groupBy('level_name'); // Grouping by the level name
            // Transforming the grouped data into a paginated format

            $paginatedData = $getQuestionTypeList->map(function ($items, $level) {
                return [
                    'level' => $level,
                    'quizzes' => $items
                ];
            })->values();

            // Paginate manually if needed
            $currentPage = LengthAwarePaginator::resolveCurrentPage();
            $perPage = HiringQuiz::PAGINATE;
            $currentItems = $paginatedData->slice(($currentPage - 1) * $perPage, $perPage)->all();
            $paginatedCollection = new LengthAwarePaginator($currentItems, $paginatedData->count(), $perPage);

            return $this->successResponse(
                new HiringQuizCollection($paginatedCollection),
                'Question type list grouped by quiz level'
            );
        } catch (Exception $ex) {
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
     * path="/uc/api/quiz/hiring_quiz/store",
     * operationId="storehiring_quiz",
     * tags={"Hiring Quiz"},
     * summary="Store hiring_quiz Request",
     *   security={ {"Bearer": {} }},
     * description="Store hiring_quiz Request",
     *    @OA\RequestBody(
     *         @OA\JsonContent(),
     *         @OA\MediaType(
     *            mediaType="multipart/form-data",
     *            @OA\Schema(
     *               type="object",
     *              @OA\Property(property="name", type="string"),
     *              @OA\Property(property="description", type="string"),
     *              @OA\Property(property="desgination_id", type="integer"),
     *              @OA\Property(property="quiz_level_id", type="integer"),
     *           @OA\Property(
     *                 property="questions_details",
     *                 type="array",
     *                 @OA\Items(
     *                     type="object",
     *                     required={"question_type_id", "question", "description", "answer_options"},
     *                     @OA\Property(property="question_type_id", type="integer", example=1),
     *                     @OA\Property(property="question", type="string", example="What is Laravel?"),
     *                     @OA\Property(property="description", type="string", example="Explain Laravel framework"),
     *                     @OA\Property(
     *                         property="answer_options",
     *                         type="array",
     *                         @OA\Items(
     *                             type="object",
     *                             required={"answer", "is_correct"},
     *                             @OA\Property(property="answer", type="string", example="Laravel is a PHP framework"),
     *                             @OA\Property(property="is_correct", type="boolean", example=true)
     *                         )
     *                     )
     *                 )
     *             )
     *            ),
     *        ),
     *    ),
     *      @OA\Response(
     *          response=201,
     *          description="Hiring_quiz Created Successfully",
     *          @OA\JsonContent()
     *       ),
     *      @OA\Response(
     *          response=200,
     *          description="Hiring_quiz Created Successfully",
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
    public function store(HiringQuizRequest $request)
    {
        try {
            $validated = $request->validated();
            $existingQuiz = HiringQuiz::where('desgination_id', $validated['desgination_id'])
            ->where('quiz_level_id', $validated['quiz_level_id'])
            ->exists();

        if ($existingQuiz) {
            return response()->json([
                'success' => false,
                'message' => 'A quiz with the same designation and quiz level already exists.'
            ], 422);
        }
            unset($validated["questions_details"]);
            $validated["created_by"] = auth('sanctum')->user()->id;
            $storeHiringQuizBasicDetails = HiringQuiz::create($validated);

            /**
             * Here we are storing the details on quiz_question_details table
             */
            $questions_details = is_array($request->questions_details)  ? $request->questions_details : json_decode($request->questions_details, true);

            foreach ($questions_details as $key => $questions_detail) {
                $storeQuizQuestionDetails = new QuizQuestionDetail();
                $storeQuizQuestionDetails->hiring_quiz_id = $storeHiringQuizBasicDetails->id;
                $storeQuizQuestionDetails->question_type_id = $questions_detail["question_type_id"];
                $storeQuizQuestionDetails->question = $questions_detail["question"];
                $storeQuizQuestionDetails->description = $questions_detail["description"];
                $storeQuizQuestionDetails->save();

                /**
                 * Here we are storing the details on quiz_answer_details table
                 */
                foreach ($questions_detail["answer_options"] as $key => $answer_option) {
                    $storeQuizAnserDetails = new QuizAnswerDetail();
                    $storeQuizAnserDetails->quiz_question_detail_id = $storeQuizQuestionDetails->id;
                    $storeQuizAnserDetails->answer = $answer_option["answer"];
                    $storeQuizAnserDetails->is_correct = $answer_option["is_correct"];
                    $storeQuizAnserDetails->save();
                }
            }
            $this->logHiringQuizCreationHistory($storeHiringQuizBasicDetails, count($questions_details));
            return $this->successResponse(
                [],
                'Hiring Quiz created Successfully'
            );
        } catch (Exception $ex) {
            return $this->errorResponse($ex->getMessage());
        }
    }

    private function logHiringQuizCreationHistory($quiz, $questionCount)
    {
        try {
            $user = auth('sanctum')->user();
            if (!$user) return;

            // Load related names
            $designationName = optional($quiz->getDesignationDetails)->title ?? 'Unknown Designation';
            $quizLevelName = optional($quiz->getQuizLevel)->title ?? 'Unknown Quiz Level';

            $changed = "Hiring Quiz created for Designation: {$designationName}, Quiz Level: {$quizLevelName}; ";
            $changed .= "Total Questions: {$questionCount}";

            UpdateSystemSetupHistory::create([
                'employee_id' => $user->id,
                'updated_by' => $user->id,
                'date' => now()->format('Y-m-d'),
                'time' => now()->format('H:i:s'),
                'notes' => 'Hiring Quiz Created',
                'changed' => $changed,
                'created_at' => now(),
                'updated_at' => now()
            ]);
        } catch (\Throwable $e) {
            \Log::error('Failed to log hiring quiz creation: ' . $e->getMessage());
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
     * @param  int  $hiringQuizId
     * @return \Illuminate\Http\Response
     */

    /**
     * @OA\get(
     * path="/uc/api/quiz/hiring_quiz/edit/{id}",
     * operationId="edithiring_quiz",
     * tags={"Hiring Quiz"},
     * summary="Edit hiring_quiz Request",
     *   security={ {"Bearer": {} }},
     * description="Edit hiring_quiz Request",
     *    @OA\Parameter(
     *     name="id",
     *     in="path",
     *     required=true,
     *     @OA\Schema(type="integer")
     *    ),
     *      @OA\Response(
     *          response=201,
     *          description="Hiring_quiz Edited Successfully",
     *          @OA\JsonContent()
     *       ),
     *      @OA\Response(
     *          response=200,
     *          description="Hiring_quiz Edited Successfully",
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
    public function edit($hiringQuizId)
    {
        try {
            $findHiringQuizDetails = HiringQuiz::with(
                "getDesignationDetails",
                "getuserDetails",
                "getQuizLevel",
                "getQuizQuestionDetails"
            )->find($hiringQuizId);
            if ($findHiringQuizDetails) {
                return $this->successResponse(
                    new HiringQuizEditResource($findHiringQuizDetails),
                    'hiring quiz detail'
                );
            } else {
                return $this->validationErrorResponse('the given data is not found');
            }
        } catch (Exception $ex) {
            return $this->errorResponse($ex->getMessage());
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $hiringQuizId
     * @return \Illuminate\Http\Response
     */


    /**
     * @OA\post(
     * path="/uc/api/quiz/hiring_quiz/update/{id}",
     * operationId="updatehiring_quiz",
     * tags={"Hiring Quiz"},
     * summary="Update hiring_quiz Request",
     *   security={ {"Bearer": {} }},
     * description="Store hiring_quiz Request",
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
     *              @OA\Property(property="name", type="string"),
     *              @OA\Property(property="description", type="string"),
     *              @OA\Property(property="desgination_id", type="integer"),
     *              @OA\Property(property="quiz_level_id", type="integer"),
     *              @OA\Property(property="questions_details", type="string"),
     *            ),
     *        ),
     *    ),
     *      @OA\Response(
     *          response=201,
     *          description="Hiring_quiz Updated Successfully",
     *          @OA\JsonContent()
     *       ),
     *      @OA\Response(
     *          response=200,
     *          description="Hiring_quiz Updated Successfully",
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
    public function update(HiringQuizRequest $request, $hiringQuizId)
    {
        try {


            $findHiringQuizDetails = HiringQuiz::find($hiringQuizId);
            if ($findHiringQuizDetails) {
                $validated = $request->validated();
                $existingQuiz = HiringQuiz::where('desgination_id', $validated['desgination_id'])
            ->where('quiz_level_id', $validated['quiz_level_id'])
            ->where('id', '!=', $hiringQuizId)
            ->exists();

            if ($existingQuiz) {
                return response()->json([
                    'success' => false,
                    'message' => 'A quiz with the same designation and quiz level already exists.'
                ], 422);
            }

                $questionsDetails = $validated['questions_details'];
                unset($validated["questions_details"]);
                $validated["created_by"] = auth('sanctum')->user()->id;

                //Do not proceed if nothing changed
                if (!$this->hasQuizChanged($findHiringQuizDetails, $validated, $questionsDetails)) {
                    return $this->successResponse([], 'No changes detected in the Hiring Quiz');
                }
                $findHiringQuizDetails->update($validated);

                /**
                 * Here first we are finding all the question and answers and remove all those so, we can update all questions and answer again
                 */
                $findQuizQuestionDetails = QuizQuestionDetail::where('hiring_quiz_id', $findHiringQuizDetails->id)->get();
                foreach ($findQuizQuestionDetails as $key => $findQuizQuestionDetail) {
                    QuizAnswerDetail::where('quiz_question_detail_id', $findQuizQuestionDetail->id)->delete();
                }
               // $findQuizQuestionDetails->delete();
                QuizQuestionDetail::where('hiring_quiz_id', $findHiringQuizDetails->id)->delete();
                /**
                 * Here we are storing the details on quiz_question_details table
                 */
                foreach ($request->questions_details as $key => $questions_detail) {
                    $storeQuizQuestionDetails = new QuizQuestionDetail();
                    $storeQuizQuestionDetails->hiring_quiz_id = $findHiringQuizDetails->id;
                    $storeQuizQuestionDetails->question_type_id = $questions_detail["question_type_id"];
                    $storeQuizQuestionDetails->question = $questions_detail["question"];
                    $storeQuizQuestionDetails->description = $questions_detail["description"];
                    $storeQuizQuestionDetails->save();

                    /**
                     * Here we are storing the details on quiz_answer_details table
                     */
                    foreach ($questions_detail["answer_options"] as $key => $answer_option) {
                        info('answer..'.$answer_option["is_correct"]);
                        $storeQuizAnserDetails = new QuizAnswerDetail();
                        $storeQuizAnserDetails->quiz_question_detail_id = $storeQuizQuestionDetails->id;
                        $storeQuizAnserDetails->answer = $answer_option["answer"];
                        $storeQuizAnserDetails->is_correct = $answer_option["is_correct"];
                        $storeQuizAnserDetails->save();
                    }
                }
                $this->logHiringQuizUpdateHistory($findHiringQuizDetails, count($questionsDetails));
                return $this->successResponse(
                    [],
                    'Hiring Quiz updated Successfully'
                );
            } else {
                return $this->validationErrorResponse('the given data is not found');
            }
        } catch (Exception $ex) {
            return $this->errorResponse($ex->getMessage());
        }
    }

    private function logHiringQuizUpdateHistory($quiz, $questionCount)
    {
        try {
            $user = auth('sanctum')->user();
            if (!$user) return;

            $designationName = optional($quiz->getDesignationDetails)->title ?? 'Unknown Designation';
            $quizLevelName = optional($quiz->getQuizLevel)->title ?? 'Unknown Quiz Level';

            $changed = "Hiring Quiz updated for Designation: {$designationName}, Quiz Level: {$quizLevelName}; ";
            $changed .= "Total Questions: {$questionCount}";

            UpdateSystemSetupHistory::create([
                'employee_id' => $user->id,
                'updated_by' => $user->id,
                'date' => now()->format('Y-m-d'),
                'time' => now()->format('H:i:s'),
                'notes' => 'Hiring Quiz Updated',
                'changed' => $changed,
                'created_at' => now(),
                'updated_at' => now()
            ]);
        } catch (\Throwable $e) {
            \Log::error('Failed to log hiring quiz update: ' . $e->getMessage());
        }
    }

    private function hasQuizChanged($quiz, $newValidatedData, $newQuestions)
    {
        // Compare main fields
        foreach ($newValidatedData as $key => $value) {
            if ($quiz->$key != $value) {
                return true;
            }
        }

        // Fetch existing questions with answers
        $oldQuestions = QuizQuestionDetail::with('answerDetail')
            ->where('hiring_quiz_id', $quiz->id)
            ->get();

        if (count($oldQuestions) !== count($newQuestions)) {
            return true;
        }

        foreach ($oldQuestions as $index => $oldQuestion) {
            $newQuestion = $newQuestions[$index];

            if (
                $oldQuestion->question_type_id != $newQuestion['question_type_id'] ||
                $oldQuestion->question != $newQuestion['question'] ||
                $oldQuestion->description != $newQuestion['description']
            ) {
                return true;
            }

            $oldAnswers = $oldQuestion->answerDetail;
            $newAnswers = $newQuestion['answer_options'];

            if (count($oldAnswers) !== count($newAnswers)) {
                return true;
            }

            foreach ($oldAnswers as $ansIndex => $oldAnswer) {
                $newAnswer = $newAnswers[$ansIndex];

                if (
                    $oldAnswer->answer !== $newAnswer['answer'] ||
                    (bool)$oldAnswer->is_correct !== (bool)$newAnswer['is_correct']
                ) {
                    return true;
                }
            }
        }

        return false;
    }




    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $hiringQuizId
     * @return \Illuminate\Http\Response
     */



    /**
     * @OA\delete(
     * path="/uc/api/quiz/hiring_quiz/destroy/{id}",
     * operationId="deletehiring_quiz",
     * tags={"Hiring Quiz"},
     * summary="Delete hiring_quiz Request",
     * security={ {"Bearer": {} }},
     * description="Delete hiring_quiz Request",
     * @OA\Parameter(
     *     name="id",
     *     in="path",
     *     required=true,
     *     @OA\Schema(type="integer")
     * ),
     * @OA\Response(
     *     response=200,
     *     description="Hiring_quiz Deleted Successfully",
     *     @OA\JsonContent()
     * ),
     * @OA\Response(
     *     response=404,
     *     description="Resource Not Found"
     * )
     * )
     */
    public function destroy($hiringQuizId)
    {
        try {
            $findHiringQuizDetails = HiringQuiz::find($hiringQuizId);
            if ($findHiringQuizDetails) {
                /**
                 * Here first we are finding all the question and answers and remove all those so, we can update all questions and answer again
                 */

                $findQuizQuestionDetails = QuizQuestionDetail::where('hiring_quiz_id', $hiringQuizId)->get();
                foreach ($findQuizQuestionDetails as $findQuizQuestionDetail) {
                    QuizAnswerDetail::where('quiz_question_detail_id', $findQuizQuestionDetail->id)->delete();

                }

                QuizQuestionDetail::where('hiring_quiz_id', $hiringQuizId)->delete();
                /**
                 * Here we are deleting the Quiz details
                 */
                $findHiringQuizDetails->delete();

                //Log history after deletion
                $this->logHiringQuizDeletionHistory($findHiringQuizDetails);

                return $this->successResponse(
                    [],
                    'hiring quiz deleted successfully'
                );
            } else {
                return $this->validationErrorResponse('the given data is not found');
            }
        } catch (Exception $ex) {
            return $this->errorResponse($ex->getMessage());
        }
    }

    /**
     * Log quiz deletion history
     */
    private function logHiringQuizDeletionHistory($quiz)
    {
        try {
            $user = auth('sanctum')->user();
            if (!$user) return;

            // Load related designation and quiz level
            $designationName = optional($quiz->getDesignationDetails)->title ?? 'Unknown Designation';
            $quizLevelName = optional($quiz->getQuizLevel)->title ?? 'Unknown Level';

            $changed = "Hiring Quiz deleted for Designation: {$designationName}, Quiz Level: {$quizLevelName}";

            UpdateSystemSetupHistory::create([
                'employee_id' => $user->id,
                'updated_by' => $user->id,
                'date' => now()->format('Y-m-d'),
                'time' => now()->format('H:i:s'),
                'notes' => 'Hiring Quiz Deleted',
                'changed' => $changed,
                'created_at' => now(),
                'updated_at' => now()
            ]);
        } catch (\Throwable $e) {
            \Log::error('Failed to log hiring quiz deletion: ' . $e->getMessage());
        }
    }



    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $quizQuestionDetailID
     * @return \Illuminate\Http\Response
     */


    /**
     * @OA\delete(
     * path="/uc/api/quiz/hiring_quiz/destroyQuizQuestion/{id}",
     * operationId="deletedestroyQuizQuestion",
     * tags={"Hiring Quiz"},
     * summary="Delete hiring_quiz Request",
     * security={ {"Bearer": {} }},
     * description="Delete hiring_quiz Request",
     * @OA\Parameter(
     *     name="id",
     *     in="path",
     *     required=true,
     *     @OA\Schema(type="integer")
     * ),
     * @OA\Response(
     *     response=200,
     *     description="Hiring_quiz Deleted Successfully",
     *     @OA\JsonContent()
     * ),
     * @OA\Response(
     *     response=404,
     *     description="Resource Not Found"
     * )
     * )
     */
    public function destroyQuizQuestion($quizQuestionDetailID)
    {
        try {
            $findQuizQuestionDetail = QuizQuestionDetail::find($quizQuestionDetailID);
            if ($findQuizQuestionDetail) {
                /**
                 * Here first we are finding all the question and answers and remove all those so, we can update all questions and answer again
                 */
                $findQuizAnswerDetails = QuizAnswerDetail::where('quiz_question_detail_id', $quizQuestionDetailID)->get();
                foreach ($findQuizAnswerDetails as $findQuizAnswerData) {
                    $findQuizAnswerData->delete();
                }
                $findQuizQuestionDetail->delete();

                return $this->successResponse(
                    [],
                    'Quiz Question deleted successfully'
                );
            } else {
                return $this->validationErrorResponse('the given data is not found');
            }
        } catch (Exception $ex) {
            return $this->errorResponse($ex->getMessage());
        }
    }
}
