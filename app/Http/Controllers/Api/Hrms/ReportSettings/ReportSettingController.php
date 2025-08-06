<?php

namespace App\Http\Controllers\Api\Hrms\ReportSettings;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\HrmsReportTitle;
use App\Models\HrmsReportSetting;
class ReportSettingController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */

    /**
     * @OA\post(
     * path="/uc/api/reports/index",
     * operationId="reportssettings",
     * tags={"HRMS Report Settings"},
     * summary="Index reports settings",
     *   security={ {"Bearer": {} }},
     * description="Index reports settings",
     *      @OA\Response(
     *          response=201,
     *          description=" Payrolls Get Successfully",
     *          @OA\JsonContent()
     *       ),
     *      @OA\Response(
     *          response=200,
     *          description="Payrolls Get Successfully",
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
        $reportTitle =  HrmsReportTitle::with('reportTitle')->get();
        return $this->successResponse($reportTitle, 'Report title settings list');
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */

    /**
     * @OA\Post(
     * path="/uc/api/reports/create",
     * operationId="reports settings",
     * tags={"HRMS Report Settings"},
     * summary="Create reports settings",
     *   security={ {"Bearer": {} }},
     * description="Create reports settings",
     *    @OA\RequestBody(
     *         @OA\JsonContent(),
     *         @OA\MediaType(
     *            mediaType="multipart/form-data",
     *            @OA\Schema(
     *               type="object",
     *               required={"name"},
     *              @OA\Property(property="hrms_report_title_id", type="string", example="1"),
     *              @OA\Property(property="name", type="text"),
     *              @OA\Property(property="status", type="string", description="'1 => Active, 0 => In Active'", example="1"),
     *            ),
     *        ),
     *    ),
     *      @OA\Response(
     *          response=201,
     *          description="Report Created Successfully",
     *          @OA\JsonContent()
     *       ),
     *      @OA\Response(
     *          response=200,
     *          description="Report Created Successfully",
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


    public function create(Request $request)
    {
        $data = $request->all();
        $data['hrms_report_title_id'];

        $reportTitle = HrmsReportTitle::find($data['hrms_report_title_id']);
        if($reportTitle){
            $request->validate([
                'hrms_report_title_id' => 'required|integer',
                'name' => 'required|string|max:255',
                'status' => 'required|integer|in:0,1',
            ]);
            $reporttitlesettings = HrmsReportSetting::create($request->all());
            return $this->successResponse($reporttitlesettings, 'Report title settings created successfully!');
        }else{

            return $this->errorResponse('Report title not found');
        }

         
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */

    /**
     * @OA\Post(
     * path="/uc/api/reports/store",
     * operationId="reports",
     * tags={"HRMS Report Settings"},
     * summary="Store reports",
     *   security={ {"Bearer": {} }},
     * description="Store reports",
     *    @OA\RequestBody(
     *         @OA\JsonContent(),
     *         @OA\MediaType(
     *            mediaType="multipart/form-data",
     *            @OA\Schema(
     *               type="object",
     *               required={"name"},
     *              @OA\Property(property="name", type="text"),
     *              @OA\Property(
     *                     property="tags",
     *                     type="array",
     *                     @OA\Items(type="string"),
     *                     description="Array of tasgs name"
     *                 ),
     *              @OA\Property(property="status", type="string", description="'1 => Active, 0 => In Active'", example="1"),
     *            ),
     *        ),
     *    ),
     *      @OA\Response(
     *          response=201,
     *          description="Report Created Successfully",
     *          @OA\JsonContent()
     *       ),
     *      @OA\Response(
     *          response=200,
     *          description="Report Created Successfully",
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

    public function store(Request $request)
    {

        try {
            $request->validate([
                'name' => 'required|string|max:255',
                'status' => 'required|integer|in:0,1',
                'tags' => 'required|string',
            ]);

    
            $data = [];
            if (!is_array($request->tags)) {
                $data['tags'] = explode(',', $request->tags);
            }
    
            //$reporttitle = HrmsReportTitle::create($request->all());
    
            $reporttitle = HrmsReportTitle::create([
                'name' => $request->name,
                'status' => $request->status,
            ]);

        
            if(!empty($data['tags'])){
                foreach ($data['tags'] as $tags) {
                    HrmsReportSetting::create([
                        'name' =>$tags,
                        'hrms_report_title_id' => $reporttitle->id,
                    ]);
                }
            }
            
            return $this->successResponse($reporttitle, 'Report title created successfully!');

        } catch (\Throwable $th) {

            return response()->json([
                'message' => 'Failed to create Report.',
                'error' => $th->getMessage(),
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

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */

    /**
     * @OA\get(
     * path="/uc/api/reports/edit/{id}",
     * operationId="reports edit",
     * tags={"HRMS Report Settings"},
     * summary="edit reports",
     *   security={ {"Bearer": {} }},
     * description="Edit reports",
     *    @OA\Parameter(
     *     name="id",
     *     in="path",
     *     required=true,
     *     @OA\Schema(type="integer")
     *    ),
     *      @OA\Response(
     *          response=201,
     *          description="Report Settings Edited Successfully",
     *          @OA\JsonContent()
     *       ),
     *      @OA\Response(
     *          response=200,
     *          description="Report Settings Edited Successfully",
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
        $reportSettings =  HrmsReportTitle::with('reportTitle')->find($id);

        //$reportSettings =  HrmsReportSetting::with('reportTitle')->get();
        //HrmsReportTitle::with('reportTitle')->get();

        if($reportSettings){
            return $this->successResponse($reportSettings, 'Edit list data');
        }else{
             return $this->errorResponse('Report settings not found');
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
     * @OA\Post(
     * path="/uc/api/reports/update/{id}",
     * operationId="reports settings update",
     * tags={"HRMS Report Settings"},
     * summary="update reports settings",
     *   security={ {"Bearer": {} }},
     * description="update reports settings",
     *     @OA\Parameter(name="id", in="path", required=true,
     *      @OA\Schema(type="integer")
     *     ),
     *    @OA\RequestBody(
     *         @OA\JsonContent(),
     *         @OA\MediaType(
     *            mediaType="multipart/form-data",
     *            @OA\Schema(
     *               type="object",
     *               required={"name"},
     *              @OA\Property(property="name", type="text"),
     *              @OA\Property(
     *                     property="tags",
     *                     type="array",
     *                     @OA\Items(type="string"),
     *                     description="Array of tasgs name"
     *                 ),
     *              @OA\Property(property="status", type="string", description="'1 => Active, 0 => In Active'", example="1"),
     *            ),
     *        ),
     *    ),
     *      @OA\Response(
     *          response=201,
     *          description="Report Created Successfully",
     *          @OA\JsonContent()
     *       ),
     *      @OA\Response(
     *          response=200,
     *          description="Report Created Successfully",
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
    
    public function update(Request $request, $id)
    {

        $request->validate([
            'name' => 'required|string|max:255',
            'tags' => 'required|string',
        ]);

        $reportTitle = HrmsReportTitle::find($id);

        $data = [];
        if (!is_array($request->tags)) {
            $data['tags'] = explode(',', $request->tags);
        }

        if ($reportTitle) {
            $reportTitle->name = $request->name;
            $reportTitle->save();

            HrmsReportSetting::where('hrms_report_title_id', $id)->delete();
        
            if(!empty($data['tags'])){
                foreach ($data['tags'] as $tags) {
                    HrmsReportSetting::create([
                        'name' =>$tags,
                        'hrms_report_title_id' => $reportTitle->id,
                    ]);
                }
            }
            return $this->successResponse($reportTitle, 'Report title settings update successfully!');
        }else{

            return $this->errorResponse('Report title not found');
        }
    }

    /**
     * @OA\get(
     * path="/uc/api/reports/titleEdit/{id}",
     * operationId="reports title edit",
     * tags={"HRMS Report Settings"},
     * summary="edit title reports",
     *   security={ {"Bearer": {} }},
     * description="Edit title reports",
     *    @OA\Parameter(
     *     name="id",
     *     in="path",
     *     required=true,
     *     @OA\Schema(type="integer")
     *    ),
     *      @OA\Response(
     *          response=201,
     *          description="Report Settings Title Edited Successfully",
     *          @OA\JsonContent()
     *       ),
     *      @OA\Response(
     *          response=200,
     *          description="Report Settings Title Edited Successfully",
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

    public function titleEdit($id){

        $reportTitles =  HrmsReportTitle::find($id);
        if($reportTitles){
            return $this->successResponse($reportTitles, 'Edit title list data');
        }else{
            return $this->errorResponse('Report settings not found');
        }
    }


        /**
     * @OA\Post(
     * path="/uc/api/reports/titleUpdate/{id}",
     * operationId="reportstitle",
     * tags={"HRMS Report Settings"},
     * summary="Store reports",
     *   security={ {"Bearer": {} }},
     * description="Store reports",
     *    @OA\Parameter(name="id", in="path", required=true,
     *      @OA\Schema(type="integer")
     *     ),
     *    @OA\RequestBody(
     *         @OA\JsonContent(),
     *         @OA\MediaType(
     *            mediaType="multipart/form-data",
     *            @OA\Schema(
     *               type="object",
     *               required={"name"},
     *              @OA\Property(property="name", type="text"),
     *              @OA\Property(property="status", type="string", description="'1 => Active, 0 => In Active'", example="1"),
     *            ),
     *        ),
     *    ),
     *      @OA\Response(
     *          response=201,
     *          description="Report Created Successfully",
     *          @OA\JsonContent()
     *       ),
     *      @OA\Response(
     *          response=200,
     *          description="Report Created Successfully",
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

     public function titleUpdate(Request $request, $id)
     {
        $reportTitle = HrmsReportTitle::find($id);
        if($reportTitle){
                $request->validate([
                    'name' => 'required|string|max:255',
                    'status' => 'required|integer|in:0,1',
                ]);
                $reportTitles = $reportTitle->update($request->all());
                return $this->successResponse($reportTitles, 'Report title update successfully!');
        }else{

            return $this->errorResponse('Report title not found');
        }
     }


    

    /**
     * @OA\delete(
     * path="/uc/api/reports/titleDestroy/{id}",
     * operationId="reports title destroy",
     * tags={"HRMS Report Settings"},
     * summary="destroy reports title",
     *   security={ {"Bearer": {} }},
     * description="destroy reports title",
     *    @OA\Parameter(
     *     name="id",
     *     in="path",
     *     required=true,
     *     @OA\Schema(type="integer")
     *    ),
     *      @OA\Response(
     *          response=201,
     *          description="reports Edited Successfully",
     *          @OA\JsonContent()
     *       ),
     *      @OA\Response(
     *          response=200,
     *          description="reports Edited Successfully",
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


    public function titleDestroy($id){
        
        $reportTitle = HrmsReportTitle::find($id);
        if($reportTitle){
            $reportTitle->delete();
            return $this->successResponse([],'Report title deleted successfully');
        }else{
            return $this->errorResponse('Report title not found');
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
     * path="/uc/api/reports/destroy/{id}",
     * operationId="reports destroy",
     * tags={"HRMS Report Settings"},
     * summary="destroy reports title",
     *   security={ {"Bearer": {} }},
     * description="destroy reports",
     *    @OA\Parameter(
     *     name="id",
     *     in="path",
     *     required=true,
     *     @OA\Schema(type="integer")
     *    ),
     *      @OA\Response(
     *          response=201,
     *          description="reports destroy Successfully",
     *          @OA\JsonContent()
     *       ),
     *      @OA\Response(
     *          response=200,
     *          description="reports destroy Successfully",
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
        $reportTitleSettings = HrmsReportSetting::find($id);
        if($reportTitleSettings){
            $reportTitleSettings->delete();
            return $this->successResponse([],'Report settings deleted successfully');
        }else{
            return $this->errorResponse('Report settings not found');
        }
    }
}
