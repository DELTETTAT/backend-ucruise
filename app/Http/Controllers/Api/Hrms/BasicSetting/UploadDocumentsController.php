<?php

namespace App\Http\Controllers\Api\Hrms\BasicSetting;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Requests\UploadDocumentRequest;
use App\Models\HrmsDocument;
use App\Models\HrmsDocumentCategories;
use App\Http\Resources\Documents\DocumentResource;
use App\Http\Resources\Documents\DocumentCollection;

class UploadDocumentsController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */


     /**
     * @OA\get(
     * path="/uc/api/documents/index",
     * operationId="getdocuments",
     * tags={"Upload Documents"},
     * summary="Get Documents Request",
     *   security={ {"Bearer": {} }},
     * description="Get Documents Request",
     *      @OA\Response(
     *          response=201,
     *          description="Documents Get Successfully",
     *          @OA\JsonContent()
     *       ),
     *      @OA\Response(
     *          response=200,
     *          description="Documents Get Successfully",
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
            $getDocuments = HrmsDocument::get();

            return $this->successResponse(
                new DocumentCollection($getDocuments),
                "Documents List"
            );
        } catch (\Exception $ex) {
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
     * path="/uc/api/documents/store",
     * operationId="stodocuments",
     * tags={"Upload Documents"},
     * summary="Store documents Request",
     *   security={ {"Bearer": {} }},
     * description="Store documents Request",
     *    @OA\RequestBody(
     *         @OA\JsonContent(),
     *         @OA\MediaType(
     *            mediaType="multipart/form-data",
     *            @OA\Schema(
     *               type="object",
     *              @OA\Property(property="title", type="string"),
     *              @OA\Property(property="category", type="array",
     *                   @OA\Items(type="string")
     *                ),
     *            ),
     *        ),
     *    ),
     *      @OA\Response(
     *          response=201,
     *          description="documents Created Successfully",
     *          @OA\JsonContent()
     *       ),
     *      @OA\Response(
     *          response=200,
     *          description="documents Created Successfully",
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
    public function store(UploadDocumentRequest $request)
    {
        try {
            $validated = $request->validated();

            $storeDocumentTitle = HrmsDocument::create([
                'title' => $validated['title']
            ]);

            $documentCategories = is_array($validated['category']) ? $validated['category'] : explode(',', $validated['category']);
            
            foreach ($documentCategories as $category) {
                HrmsDocumentCategories::create([
                    'document_id' => $storeDocumentTitle->id,
                    'category' => $category
                ]);
            }

            return $this->successResponse(
                [],
                "Document Created Successfully"
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



    /**
     * @OA\get(
     * path="/uc/api/documents/edit/{id}",
     * operationId="editdocuments",
     * tags={"Upload Documents"},
     * summary="Edit documents Request",
     *   security={ {"Bearer": {} }},
     * description="Edit documents Request",
     *    @OA\Parameter(
     *     name="id",
     *     in="path",
     *     required=true,
     *     @OA\Schema(type="integer")
     *    ),
     *      @OA\Response(
     *          response=201,
     *          description="documents Edited Successfully",
     *          @OA\JsonContent()
     *       ),
     *      @OA\Response(
     *          response=200,
     *          description="documents Edited Successfully",
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
    public function edit($id)
    {
        try {
            $getDocuments = HrmsDocument::find($id);

            if ($getDocuments) {
                 return $this->successResponse(
                    new DocumentResource($getDocuments),
                    "Document List"
                 );
            }else {
                return $this->errorResponse("the given data is not found");
            }

        } catch (\Exception $ex) {
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
     * path="/uc/api/documents/update/{id}",
     * operationId="updatedocuments",
     * tags={"Upload Documents"},
     * summary="Update documents Request",
     *   security={ {"Bearer": {} }},
     * description="Store documents Request",
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
     *              @OA\Property(property="category", type="array",
     *                   @OA\Items(type="string")
     *                ),
     *            ),
     *        ),
     *    ),
     *      @OA\Response(
     *          response=201,
     *          description="documents Updated Successfully",
     *          @OA\JsonContent()
     *       ),
     *      @OA\Response(
     *          response=200,
     *          description="documents Updated Successfully",
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
    public function update(UploadDocumentRequest $request, $id)
    {
        try {
            $getDocuments = HrmsDocument::find($id);
            if ($getDocuments) {
                $validated = $request->validated();

                $storeDocument = $getDocuments->update($validated);

                $documentCategories = is_array($validated['category']) ? $validated['category'] : explode(',', $validated['category']);

                $getCategories = HrmsDocumentCategories::where('document_id', $id)->get();

                foreach ($getCategories as $key => $Category) {
                    $Category->delete();
                }
                
                foreach ($documentCategories as $category) {
                    $storeDocumentCategory = new HrmsDocumentCategories();
                    $storeDocumentCategory->document_id = $id;
                    $storeDocumentCategory->category = $category;
                    $storeDocumentCategory->save();
                }
            
                return $this->successResponse(
                    [],
                    "Document Updated Successfully"
                );

            }else {
                return $this->errorResponse("the given data is not found");
            }
        } catch (\Exception $ex) {
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
     * path="/uc/api/documents/destroy/{id}",
     * operationId="deletedocuments",
     * tags={"Upload Documents"},
     * summary="Delete documents Request",
     * security={ {"Bearer": {} }},
     * description="Delete documents Request",
     * @OA\Parameter(
     *     name="id",
     *     in="path",
     *     required=true,
     *     @OA\Schema(type="integer")
     * ),
     * @OA\Response(
     *     response=200,
     *     description="documents Deleted Successfully",
     *     @OA\JsonContent()
     * ),
     * @OA\Response(
     *     response=404,
     *     description="Resource Not Found"
     * )
     * )
     */
    public function destroy($id)
    {
        try {
            $getDocuments = HrmsDocument::find($id);

            if ($getDocuments) {
                $getDocuments->delete();

                return $this->successResponse(
                    [],
                    "Document Deleted Successfully"
                );
            }else {
                return $this->errorResponse("the given data is not found");
            }
        } catch (\Exception $ex) {
            return $this->errorResponse($ex->getMessage());
        }
    }



     /**
     * @OA\delete(
     * path="/uc/api/documents/deleteDocumentCategory/{id}",
     * operationId="deletecategory",
     * tags={"Upload Documents"},
     * summary="Delete documents Category Request",
     * security={ {"Bearer": {} }},
     * description="Delete documents Category Request",
     * @OA\Parameter(
     *     name="id",
     *     in="path",
     *     required=true,
     *     @OA\Schema(type="integer")
     * ),
     * @OA\Response(
     *     response=200,
     *     description="documents Category Deleted Successfully",
     *     @OA\JsonContent()
     * ),
     * @OA\Response(
     *     response=404,
     *     description="Resource Not Found"
     * )
     * )
     */
    public function deleteDocumentCategory($id)
    {
        try {
            $getDocumentsCategory = HrmsDocumentCategories::find($id);

            if ($getDocumentsCategory) {
                $getDocumentsCategory->delete();

                return $this->successResponse(
                    [],
                    "Document Category Deleted Successfully"
                );
            }else {
                return $this->errorResponse("the given data is not found");
            }
        } catch (\Exception $ex) {
            return $this->errorResponse($ex->getMessage());
        }
    }
}
