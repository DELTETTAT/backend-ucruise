<?php

namespace App\Http\Controllers\Api\Hrms\Quiz\QuestionType;

use App\Http\Controllers\Controller;
use App\Http\Resources\Hrms\Quiz\QuestionType\QuestiontypeCollection;
use App\Models\QuestionType;
use Exception;
use Illuminate\Http\Request;
use App\Http\Requests\QuestionTypeRequest;
use App\Http\Resources\Hrms\Quiz\QuestionType\QuestiontypeResource;

class QuestiontypeController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */


     /**
     * @OA\Get(
     * path="/uc/api/quiz/question_type/index",
     * operationId="getQuestion_type",
     * tags={"Question_type"},
     * summary="Get questiontype Request",
     *   security={ {"Bearer": {} }},
     * description="Get questiontype Request",
     *      @OA\Response(
     *          response=201,
     *          description="Questiontype Get Successfully",
     *          @OA\JsonContent()
     *       ),
     *      @OA\Response(
     *          response=200,
     *          description="Questiontype Get Successfully",
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
            $getQuestionTypeList = QuestionType::paginate(QuestionType::PAGINATE);
            return $this->successResponse(
                new QuestiontypeCollection($getQuestionTypeList),
                'Question type list'
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
     * @OA\post(
     * path="/uc/api/quiz/question_type/store",
     * operationId="storeQuestion_type",
     * tags={"Question_type"},
     * summary="Store Question_type Request",
     *   security={ {"Bearer": {} }},
     * description="Store Question_type Request",
     *    @OA\RequestBody(
     *         @OA\JsonContent(),
     *         @OA\MediaType(
     *            mediaType="multipart/form-data",
     *            @OA\Schema(
     *               type="object",
     *              @OA\Property(property="title", type="string"),
     *              @OA\Property(property="status", type="integer"),
     *            ),
     *        ),
     *    ),
     *      @OA\Response(
     *          response=201,
     *          description="Question_type Created Successfully",
     *          @OA\JsonContent()
     *       ),
     *      @OA\Response(
     *          response=200,
     *          description="Question_type Created Successfully",
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
    public function store(QuestionTypeRequest $request)
    {

        try {
            $validated = $request->validated();

            $storeQuestionTypeDetails = QuestionType::create($validated);
            return $this->successResponse(
                new QuestiontypeResource($storeQuestionTypeDetails),
                'QuestionType created Successfully'
            );
        } catch (Exception $ex) {
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
     * path="/uc/api/quiz/question_type/edit/{id}",
     * operationId="editQuestion_type",
     * tags={"Question_type"},
     * summary="Edit Question_type Request",
     *   security={ {"Bearer": {} }},
     * description="Edit Question_type Request",
     *    @OA\Parameter(
     *     name="id",
     *     in="path",
     *     required=true,
     *     @OA\Schema(type="integer")
     *    ),
     *      @OA\Response(
     *          response=201,
     *          description="Question_type Edited Successfully",
     *          @OA\JsonContent()
     *       ),
     *      @OA\Response(
     *          response=200,
     *          description="Question_type Edited Successfully",
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
    public function edit($questionTypeId)
    {
        try {
            $getQuestionTypeDetails = QuestionType::find($questionTypeId);
            if (isset($getQuestionTypeDetails)) {
                return $this->successResponse(
                    new QuestiontypeResource($getQuestionTypeDetails),
                    'QuestionType Details'
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
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */


       /**
     * @OA\post(
     * path="/uc/api/quiz/question_type/update/{id}",
     * operationId="updateQuestion_type",
     * tags={"Question_type"},
     * summary="Update Question_type Request",
     *   security={ {"Bearer": {} }},
     * description="Store Question_type Request",
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
     *              @OA\Property(property="title", type="string"),
     *              @OA\Property(property="status", type="integer"),
     *            ),
     *        ),
     *    ),
     *      @OA\Response(
     *          response=201,
     *          description="Question_type Updated Successfully",
     *          @OA\JsonContent()
     *       ),
     *      @OA\Response(
     *          response=200,
     *          description="Question_type Updated Successfully",
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
    public function update(QuestionTypeRequest $request, $questionTypeId)
    {
        try {
            $findQuestionTypeDetail = QuestionType::find($questionTypeId);
            if (isset($findQuestionTypeDetail)) {
                $validated = $request->validated();
               
                $findQuestionTypeDetail->update($validated);
                return $this->successResponse(
                    new QuestiontypeResource($findQuestionTypeDetail),
                    'QuestionType updated Successfully'
                );
            } else {
                return $this->validationErrorResponse("the given data is not found");
            }
        } catch (Exception $ex) {
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
     * path="/uc/api/quiz/question_type/destroy/{id}",
     * operationId="deleteQuestion_type",
     * tags={"Question_type"},
     * summary="Delete Question_type Request",
     * security={ {"Bearer": {} }},
     * description="Delete Question_type Request",
     * @OA\Parameter(
     *     name="id",
     *     in="path",
     *     required=true,
     *     @OA\Schema(type="integer")
     * ),
     * @OA\Response(
     *     response=200,
     *     description="Question_type Deleted Successfully",
     *     @OA\JsonContent()
     * ),
     * @OA\Response(
     *     response=404,
     *     description="Resource Not Found"
     * )
     * )
     */
    public function destroy($questionTypeId)
    {
        try {
            $getQuestionTypeDetails = QuestionType::find($questionTypeId);
            if (isset($getQuestionTypeDetails)) {
                $getQuestionTypeDetails->delete();
                return $this->successResponse(
                    [],
                    'QuestionType Removed Sucessfully'
                );
            } else {
                return $this->validationErrorResponse('the given data is not found');
            }
        } catch (Exception $ex) {
            return $this->errorResponse($ex->getMessage());
        }
    }


     /**
     * @OA\Get(
     * path="/uc/api/quiz/question_type/listAllQuestionType",
     * operationId="getAllQuestion_type",
     * tags={"Question_type"},
     * summary="Get all questiontype Request",
     *   security={ {"Bearer": {} }},
     * description="Get all questiontype Request",
     *      @OA\Response(
     *          response=201,
     *          description="All Questiontype Get Successfully",
     *          @OA\JsonContent()
     *       ),
     *      @OA\Response(
     *          response=200,
     *          description="All Questiontype Get Successfully",
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
    public function listAllQuestionType()
    {
        try {
            $getQuestionTypeAllList = QuestionType::all();
            return $this->successResponse(
                $getQuestionTypeAllList,
                'Question type all list'
            );
        } catch (Exception $ex) {
            return $this->errorResponse($ex->getMessage());
        }
    }



}
