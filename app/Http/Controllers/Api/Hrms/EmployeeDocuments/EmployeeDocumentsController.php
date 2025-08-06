<?php

namespace App\Http\Controllers\Api\Hrms\EmployeeDocuments;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\HrmsEmployeeDocument;
use App\Models\HrmsEmployeeSubDocument;
use App\Models\HrmsDocument;
use DB;
use App\Http\Resources\EmployeeDocuments\EmployeeDocumentResource;
use App\Http\Resources\EmployeeDocuments\EmployeeDocumentCollection;
use App\Http\Requests\EmployeeDocumentRequest;

class EmployeeDocumentsController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */

     
    /**
     * @OA\Post(
     * path="/uc/api/employee_documents/index",
     * operationId="geteemployee_documents",
     * tags={"Employee Upload Documents"},
     * summary="Get employee_documents Request",
     *   security={ {"Bearer": {} }},
     * description="Get employee_documents Request",
     *    @OA\RequestBody(
     *         @OA\JsonContent(),
     *         @OA\MediaType(
     *            mediaType="multipart/form-data",
     *            @OA\Schema(
     *               type="object",
     *              @OA\Property(property="employee_id", type="integer", description="Enter Employee id"),
     *            ),
     *        ),
     *    ),
     *      @OA\Response(
     *          response=201,
     *          description="employee_documents Get Successfully",
     *          @OA\JsonContent()
     *       ),
     *      @OA\Response(
     *          response=200,
     *          description="employee_documents Get Successfully",
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

            $request->validate([ 'employee_id' => 'required|integer']);

            $employee_id = $request->employee_id;
            $getDocuments = HrmsDocument::with([
                'documentCategories.employeeDocuments' => function ($query) use ($employee_id) {
                    $query->where('employee_id', $employee_id);
                }
            ])->get();

            return $this->successResponse(
                new EmployeeDocumentCollection($getDocuments),
                 "Documents List"
            );
        } catch (\Throwable $th) {
            return $this->errorResponse($th->getMessage());
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
     * path="/uc/api/employee_documents/store",
     * operationId="storeemployee_documents",
     * tags={"Employee Upload Documents"},
     * summary="Store employee_documents Request",
     *   security={ {"Bearer": {} }},
     * description="Upload employee_documents Request",
     *    @OA\RequestBody(
     *         @OA\JsonContent(),
     *         @OA\MediaType(
     *            mediaType="multipart/form-data",
     *            @OA\Schema(
     *               type="object",
     *              @OA\Property(property="employee_id", type="string"),
     *              @OA\Property(property="document_title_id", type="string"),
     *              @OA\Property(property="sub_document_id", type="string"),
     *              @OA\Property(property="file", type="string", format="binary"),
     *            ),
     *        ),
     *    ),
     *      @OA\Response(
     *          response=201,
     *          description="employee_documents Uploaded Successfully",
     *          @OA\JsonContent()
     *       ),
     *      @OA\Response(
     *          response=200,
     *          description="employee_documents Uploaded Successfully",
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
    public function store(EmployeeDocumentRequest $request)
    {
        try {
            
            $validated = $request->validated();

            if ($request->hasFile('file')) {
                $filename = time(). "_" . $request->file('file')->getClientOriginalName();
                $request->file('file')->move(public_path('EmployeeDocuments'), $filename); 
                $validated['file'] = $filename;
            }

            $getDocuments = HrmsEmployeeDocument::where('employee_id', $request->employee_id)
                                               ->where('document_title_id', $request->document_title_id)
                                               ->where('sub_document_id', $request->sub_document_id)
                                               ->get();
            if (isset($getDocuments)) {
                foreach ($getDocuments as $document) {
                    $document->delete();
               }  
            }                                   
                                            
            $uploadDocument = HrmsEmployeeDocument::create($validated);
            return $this->successResponse(
                [],
                "Document Uploaded Successfully"
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
     * path="/uc/api/employee_documents/delete/{id}",
     * operationId="deleteEmployee_documents",
     * tags={"Employee Upload Documents"},
     * summary="Delete Document Request",
     * security={ {"Bearer": {} }},
     * description="Delete Document Request",
     *      @OA\RequestBody(
     *         @OA\MediaType(
     *            mediaType="multipart/form-data",
     *            @OA\Schema(
     *               type="object",
     *              @OA\Property(property="employee_id", type="integer"),
     *              @OA\Property(property="document_title_id", type="integer"),
     *              @OA\Property(property="sub_document_id", type="integer"),
     *            ),
     *        ),
     *    ),
     * @OA\Parameter(
     *     name="id",
     *     in="path",
     *     required=true,
     *     @OA\Schema(type="integer")
     * ),
     * @OA\Response(
     *     response=200,
     *     description="Document Deleted Successfully",
     *     @OA\JsonContent()
     * ),
     * @OA\Response(
     *     response=404,
     *     description="Resource Not Found"
     * )
     * )
     */

     public function delete(Request $request, $id)
     {

        $request->validate([
            'employee_id' =>  'required|integer',
            'document_title_id' =>  'required|integer',
            'sub_document_id' =>  'required|integer'
        ]);

         try {
             $getDocument = HrmsEmployeeDocument::where('employee_id', $request->employee_id)
                                            ->where('document_title_id', $request->document_title_id)
                                            ->where('sub_document_id', $request->sub_document_id)
                                            ->find($id);
                                        
             if (isset($getDocument)) {
                 $getDocument->delete();
                 return $this->successResponse(
                     [],
                     'Document Removed Sucessfully'
                 );
             } else {
                 return $this->validationErrorResponse('the given data is not found');
             }
         } catch (Exception $ex) {
             return $this->errorResponse($ex->getMessage());
         }
     }



}
