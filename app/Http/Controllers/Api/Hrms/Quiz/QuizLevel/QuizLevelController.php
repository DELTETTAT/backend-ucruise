<?php

namespace App\Http\Controllers\Api\Hrms\Quiz\QuizLevel;

use App\Http\Controllers\Controller;
use App\Http\Resources\Hrms\Quiz\QuizLevel\QuizLevelCollection;
use App\Http\Resources\Hrms\Quiz\QuizLevel\QuizLevelResource;
use App\Http\Requests\QuizLevelRequest;
use App\Models\QuizLevel;
use Exception;
use Illuminate\Http\Request;

class QuizLevelController extends Controller
{

     /*****  List All Quiz Level With Pagination */
     
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
 
   
          /**
     * @OA\Get(
     * path="/uc/api/quiz/question_level/index",
     * operationId="getquizlevels",
     * tags={"Quiz Level"},
     * summary="Get Quiz Level",
     *   security={ {"Bearer": {} }},
     * description="Get Quiz Level",
     *      @OA\Response(
     *          response=201,
     *          description="Quiz Level Get Successfully",
     *          @OA\JsonContent()
     *       ),
     *      @OA\Response(
     *          response=200,
     *          description="Quiz Level Get Successfully",
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
            $getQuizLevelList = QuizLevel::paginate(QuizLevel::PAGINATE);
            return $this->successResponse(
                new QuizLevelCollection($getQuizLevelList),
                'Quiz level list'
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

    /*********** Function to store the Quiz Level *************/

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */


               /**
         * @OA\Post(
         *     path="/uc/api/quiz/question_level/store",
         *     operationId="addquizlevel",
         *     tags={"Quiz Level"},
         *     summary="Submit Quiz Level data",
         *     security={{"Bearer": {}}},
         *     description="Endpoint to process Quiz Level data.",
         *     @OA\RequestBody(
         *         required=true,
         *         @OA\MediaType(
         *             mediaType="multipart/form-data",
         *             @OA\Schema(
         *                 type="object",
         *                 @OA\Property(property="title", type="string", description="Title of the Quiz Level."),
         *                 @OA\Property(property="status", type="integer", description="Status of the Quiz Level (1 for active, 0 for inactive)."),
         *             )
         *         )
         *     ),
         *     @OA\Response(
         *         response=201,
         *         description="Quiz Level created successfully.",
         *         @OA\JsonContent(
         *             type="object",
         *             @OA\Property(property="message", type="string", example="Quiz Level created successfully."),
         *             @OA\Property(property="template", type="object", description="Details of the created Quiz Level.")
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

    public function store(QuizLevelRequest $request)
    {
        try {
            // Retrieve validated data from the request
            $data = $request->validated();
            $data['status'] = $data['status'] ?? 1;
            $quizLevel = QuizLevel::create($data);
    
            return response()->json([
                'message' => 'Quiz level created successfully.',
                'data' => $quizLevel,
            ], 201);
        } catch (Exception $e) {
            
            return response()->json([
                'message' => 'Failed to create quiz level.',
                'error' => $e->getMessage(),
            ], 500);
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


    /*****  Edit Quiz Level  */

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
   
      /**
     * @OA\Get(
     * path="/uc/api/quiz/question_level/edit/{id}",
     * operationId="editquizlevel",
     * tags={"Quiz Level"},
     * summary="Edit Quiz Level Request",
     * security={ {"Bearer": {} }},
     * description="Edit Quiz Level Request",
     * @OA\Parameter(
     *     name="id",
     *     in="path",
     *     required=true,
     *     @OA\Schema(type="integer")
     * ),
     * @OA\Response(
     *     response=200,
     *     description="Quiz Level Edit Successfully",
     *     @OA\JsonContent()
     * ),
     * @OA\Response(
     *     response=404,
     *     description="Resource Not Found"
     * )
     * )
     */
    public function edit($QuizLevelId)
    {
        try {
            $getQuizLevelDetails = QuizLevel::find($QuizLevelId);
            if (isset($getQuizLevelDetails)) {
                return $this->successResponse(
                    new QuizLevelResource($getQuizLevelDetails),
                    'Quiz Level details retrieved successfully'
                );
            } else {
                return $this->validationErrorResponse('the given data is not found');
            }
        } catch (Exception $ex) {
            return $this->errorResponse($ex->getMessage());
        }
    }


     /*****  Update Quiz Level  */

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    
        /**
         * @OA\Post(
         *     path="/uc/api/quiz/question_level/update/{id}",
         *     operationId="updatequizlevel",
         *     tags={"Quiz Level"},
         *     summary="Update quiz level data",
         *     security={{"Bearer": {}}},
         *     description="Endpoint to process quiz level data.",
         *     @OA\RequestBody(
         *         required=true,
         *         @OA\MediaType(
         *             mediaType="multipart/form-data",
         *             @OA\Schema(
         *                 type="object",
         *                 @OA\Property(property="title", type="string", description="Title of the Quiz Level."),
         *                 @OA\Property(property="status", type="integer", description="Status of the Quiz Level (1 for active, 0 for inactive)."),
         *             )
         *         )
         *     ),
         *     @OA\Response(
         *         response=201,
         *         description="quiz level updated successfully.",
         *         @OA\JsonContent(
         *             type="object",
         *             @OA\Property(property="message", type="string", example="quiz level update successfully."),
         *             @OA\Property(property="template", type="object", description="Details of the updated quiz level.")
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

    public function update(QuizLevelRequest $request, $QuizLevelId)
    {
        try {
            $findQuizLevelDetail = QuizLevel::find($QuizLevelId);
            if (isset($findQuizLevelDetail)) {
                $validated = $request->validated();
                 $findQuizLevelDetail->update($validated);
                return $this->successResponse(
                    new QuizLevelResource($findQuizLevelDetail),
                    'Quiz Level updated Successfully'
                );
            } else {
                return $this->validationErrorResponse("the given data is not found");
            }
        } catch (Exception $ex) {
            return $this->errorResponse($ex->getMessage());
        }
    }


     /*****  Delete Quiz Level  */
    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
  
       /**
     * @OA\Delete(
     * path="/uc/api/quiz/question_level/destroy/{id}",
     * operationId="deletequizlevel",
     * tags={"Quiz Level"},
     * summary="Delete Quiz Level Request",
     * security={ {"Bearer": {} }},
     * description="Delete Quiz Level Request",
     * @OA\Parameter(
     *     name="id",
     *     in="path",
     *     required=true,
     *     @OA\Schema(type="integer")
     * ),
     * @OA\Response(
     *     response=200,
     *     description="Quiz Level Deleted Successfully",
     *     @OA\JsonContent()
     * ),
     * @OA\Response(
     *     response=404,
     *     description="Resource Not Found"
     * )
     * )
     */
    public function destroy($QuizLevelId)
    {
        try {
            $getQuizLevelDetails = QuizLevel::find($QuizLevelId);
            if (isset($getQuizLevelDetails)) {
                $getQuizLevelDetails->delete();
                return $this->successResponse(
                    [],
                    'Quiz Level Removed Sucessfully'
                );
            } else {
                return $this->validationErrorResponse('the given data is not found');
            }
        } catch (Exception $ex) {
            return $this->errorResponse($ex->getMessage());
        }
    }

/*****  List All Quiz Level  */

    /**
 * @OA\Get(
 * path="/uc/api/quiz/question_level/allquizlevels",
 * operationId="getAllQuizLevels",
 * tags={"Quiz Level"},
 * summary="Get All Quiz Levels",
 * security={ {"Bearer": {} }},
 * description="Get All Quiz Levels",
 *      @OA\Response(
 *          response=201,
 *          description="Quiz Levels Retrieved Successfully",
 *          @OA\JsonContent()
 *       ),
 *      @OA\Response(
 *          response=200,
 *          description="Quiz Levels Retrieved Successfully",
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
public function allquizlevels()
{
    try {
        $getAllQuizLevels = QuizLevel::all(); 
        return $this->successResponse(
            $getAllQuizLevels, 
            'All Quiz Levels'
        );
    } catch (Exception $ex) {
        return $this->errorResponse($ex->getMessage());
    }
}

}
