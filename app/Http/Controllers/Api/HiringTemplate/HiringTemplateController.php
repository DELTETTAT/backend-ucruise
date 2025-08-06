<?php

namespace App\Http\Controllers\Api\HiringTemplate;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Mail\HiringTemplateMail;
use App\Models\HiringTemplate;
use App\Models\NewApplicant;
use App\Models\UpdateSystemSetupHistory;
use Illuminate\Support\Facades\Mail;
use App\Http\Requests\HiringTemplateRequest;
use App\Http\Resources\HiringTemplate\HiringTemplateCollection;
use App\Http\Resources\HiringTemplate\HiringTemplateResource;
use Carbon\Carbon;

class HiringTemplateController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */

      /**
     * @OA\Post(
     * path="/uc/api/hiringtemplate/index",
     * operationId="gethiringtemplates",
     * tags={"Hiring Templates"},
     * summary="Get Hiring Templates",
     *   security={ {"Bearer": {} }},
     * description="Get Hiring Templates",
     *     @OA\RequestBody(
    *         required=true,
    *         @OA\MediaType(
    *             mediaType="multipart/form-data",
    *             @OA\Schema(
    *                 type="object",
    *                 @OA\Property(property="template_type", type="integer", description="0 for email template, 1 for offered template."),
    *             )
    *         )
    *     ),
     *      @OA\Response(
     *          response=201,
     *          description="Hiring Templates Get Successfully",
     *          @OA\JsonContent()
     *       ),
     *      @OA\Response(
     *          response=200,
     *          description="Hiring Templates Get Successfully",
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
        //
         try {
            $template_type = (int) $request->template_type;
             $getHiringTemplateList = HiringTemplate::when(isset($template_type) && ($template_type == 0 || $template_type == 1), function ($query) use ($template_type) {
                 return $query->where('template_type', $template_type);
             })
             ->paginate(HiringTemplate::PAGINATE);
             return $this->successResponse(
                 new HiringTemplateCollection($getHiringTemplateList),
                 'Hiring TEmplate list'
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
     * function is used to store the hiring template
     */




    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */

        /**
         * @OA\Post(
         *     path="/uc/api/hiringtemplate/store",
         *     operationId="hiringTemplates",
         *     tags={"Hiring Templates"},
         *     summary="Submit hiring template data",
         *     security={{"Bearer": {}}},
         *     description="Endpoint to process hiring template data.",
         *     @OA\RequestBody(
         *         required=true,
         *         @OA\MediaType(
         *             mediaType="multipart/form-data",
         *             @OA\Schema(
         *                 type="object",
         *                 @OA\Property(property="template_name", type="string", description="Name of the template."),
         *                 @OA\Property(property="title", type="string", description="Title of the template."),
         *                 @OA\Property(property="status", type="integer", description="Status of the template (1 for active, 0 for inactive)."),
         *                 @OA\Property(property="header_image", type="string", format="binary", description="Header image"),
         *                 @OA\Property(property="background_image", type="string",format="binary", description="Background image"),
         *                 @OA\Property(property="watermark", type="string",format="binary",description="Watermark image."),
         *                 @OA\Property(property="footer_image", type="string",format="binary",description="Footer image."),
         *                 @OA\Property(property="name", type="string",description="Name of the person"),
         *                 @OA\Property(property="phone", type="string",description="Phone number"),
         *                 @OA\Property(property="email", type="string",description="Email address"),
         *                 @OA\Property(property="date_of_issue", type="string",description="Date of issue"),
         *                 @OA\Property(property="content", type="string", description="Content of the template"),
         *                 @OA\Property(property="template_type", type="integer", description="0 for email template, 1 for offered template."),
         *                 @OA\Property(property="icon_files[]",type="array",@OA\Items(type="string", format="binary"),description="Array of icon image files"),
         *                 @OA\Property(property="icon_positions",type="object",example="[{'x': 100, 'y': 200}, {'x': 300, 'y': 100}]",description="JSON string of array of icon coordinates"),
         *             )
         *         )
         *     ),
         *     @OA\Response(
         *         response=201,
         *         description="Template created successfully.",
         *         @OA\JsonContent(
         *             type="object",
         *             @OA\Property(property="message", type="string", example="Template created successfully."),
         *             @OA\Property(property="template", type="object", description="Details of the created template.")
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
        // public function store(HiringTemplateRequest $request)
        // {
        //     try {
        //         $data = $request->validated();

        //         if ($request->hasFile('watermark')) {
        //             $watermarkPath = public_path('hiringtemplate/watermark');
        //             $data['watermark'] = $this->uploadImage($request->file('watermark'), $watermarkPath);
        //         }

        //         if ($request->hasFile('header_image')) {
        //             $headerPath = public_path('hiringtemplate/header');
        //             $data['header_image'] = $this->uploadImage($request->file('header_image'), $headerPath);
        //         }

        //         if ($request->hasFile('background_image')) {
        //             $backgroundPath = public_path('hiringtemplate/background');
        //             $data['background_image'] = $this->uploadImage($request->file('background_image'), $backgroundPath);
        //         }


        //         if ($request->hasFile('footer_image')) {
        //             $footerPath = public_path('hiringtemplate/footer');
        //             $data['footer_image'] = $this->uploadImage($request->file('footer_image'), $footerPath);
        //         }

        //         // Create a new HiringTemplate
        //         $hiringTemplate = HiringTemplate::create($data);


        //         // Return a success response
        //         return response()->json([
        //             'message' => 'Hiring template created successfully!',
        //             'data' => $hiringTemplate,
        //         ], 201);

        //     } catch (Exception $e) {
        //     // Return an error response
        //         return response()->json([
        //             'message' => 'Failed to create hiring template. Please try again later.',
        //             'error' => $e->getMessage(),
        //         ], 500);
        //     }
        // }




    public function store(HiringTemplateRequest $request)
    {
        try {
            $data = $request->validated();
 
            if ($request->hasFile('watermark')) {
                $watermarkPath = public_path('hiringtemplate/watermark');
                $data['watermark'] = $this->uploadImage($request->file('watermark'), $watermarkPath);
            }
 
            if ($request->hasFile('header_image')) {
                $headerPath = public_path('hiringtemplate/header');
                $data['header_image'] = $this->uploadImage($request->file('header_image'), $headerPath);
            }
 
            if ($request->hasFile('background_image')) {
                $backgroundPath = public_path('hiringtemplate/background');
                $data['background_image'] = $this->uploadImage($request->file('background_image'), $backgroundPath);
            }
 
 
            if ($request->hasFile('footer_image')) {
                $footerPath = public_path('hiringtemplate/footer');
                $data['footer_image'] = $this->uploadImage($request->file('footer_image'), $footerPath);
            }
 
             if ($request->hasFile('icon_files') ) {
                $empty_array=[];
                $all_files = $request->file('icon_files');
               
                $iconPositionsRaw =$request->input('icon_positions');
                $watermarkPath = public_path('hiringtemplate/watermark');
                foreach ( $all_files as $index => $iconFile) {
                    $newimage= $this->uploadImage($iconFile, $watermarkPath);
                 
                  $empty_array[$index]= $newimage;
                }
                 
                   
                 $data['icon_positions'] = json_encode($iconPositionsRaw);
                 $data['icon_files'] = json_encode($empty_array); //$data['icon_positions']
             }
           
            // Create a new HiringTemplate
            $hiringTemplate = HiringTemplate::create($data);

            $this->logHiringTemplateCreation($hiringTemplate);
 
 
            // Return a success response
            return response()->json([
                'message' => 'Hiring template created successfully!',
                'data' => $hiringTemplate,
            ], 201);
 
        } catch (Exception $e) {
           // Return an error response
            return response()->json([
                'message' => 'Failed to create hiring template. Please try again later.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    private function logHiringTemplateCreation($template)
    {
        try {
            $user = auth('sanctum')->user();
            if (!$user) return;

            $templateName = $template->template_name ?? 'Unknown Template';

            UpdateSystemSetupHistory::create([
                'employee_id' => $user->id,
                'updated_by' => $user->id,
                'date' => now()->format('Y-m-d'),
                'time' => now()->format('H:i:s'),
                'notes' => 'Hiring Template Created',
                'changed' => "{$templateName} Hiring Template created",
                'created_at' => now(),
                'updated_at' => now()
            ]);
        } catch (\Throwable $e) {
            \Log::error('Failed to log Hiring Template creation: ' . $e->getMessage());
        }
}


/**
 *
 * comman function for the upload image.
 */

    // private function uploadImage($image, $path){
    //     // Ensure the directory exists
    //     if (!is_dir($path)) {
    //         mkdir($path, 0777, true);
    //     }

    //     // Generate a unique filename and move the file to the directory
    //     $filename = time() . '.' . $image->extension();
    //     $image->move($path, $filename);

    //     return $filename;
    // }


    private function uploadImage($image, $path){
        // Ensure the directory exists
        if (!is_dir($path)) {
            mkdir($path, 0777, true);
        }
        // Generate a unique filename and move the file to the directory
        $filename =rand()+ time() . '.' . $image->extension();
        $image->move($path, $filename);
        \Log::info("replay");
        \Log::info( $filename);
        \Log::info("replay");
        return $filename;
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
     * @OA\Post(
     * path="/uc/api/hiringtemplate/edit/{id}",
     * operationId="editHiringTemplate",
     * tags={"Hiring Templates"},
     * summary="Edit Hiring Templates Request",
     * security={ {"Bearer": {} }},
     * description="Edit Hiring Templates Request",
     * @OA\Parameter(
     *     name="id",
     *     in="path",
     *     required=true,
     *     @OA\Schema(type="integer")
     * ),
     *   @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 type="object",
     *                 @OA\Property(property="flag", type="integer", description="1 => view template with applicant details , 0 => view only simple template"),
     *                 @OA\Property(property="applicant_id", type="integer"),
     *                 @OA\Property(property="reminder_date", type="date"),
     *                 @OA\Property(property="offered_salary", type="integer", description="Offered salary for the applicant"),
     *             )
     *         )
     *     ),
     * @OA\Response(
     *     response=200,
     *     description=" Hiring Templates Edit  Successfully",
     *     @OA\JsonContent()
     * ),
     * @OA\Response(
     *     response=404,
     *     description="Resource Not Found"
     * )
     * )
     */
    public function edit(Request $request, $HiringTemplateId)
    {
        try {
            $getHiringTemplateDetails = HiringTemplate::find($HiringTemplateId);
            if (isset($getHiringTemplateDetails)) {
                $true = false;
                if ($request->flag == 1) {

                    $applicantDetails = NewApplicant::with('designation')->find($request->applicant_id);

                    $content = $getHiringTemplateDetails->content;


                   // Placeholder mapping
                    $placeholders = [
                        '[candidate_name]' => trim(($applicantDetails->first_name ?? '') . ' ' . ($applicantDetails->last_name ?? '')),
                        '[designation]' => $applicantDetails->designation->title ?? ' ',

                        '[joining_date]' => $request->joining_date ?? ' ',
                        '[joining_time]' => $request->joining_time ?? ' ',
                        '[salary]' => $applicantDetails->salary_expectation ?? ' ',
                        '[offered_salary]' => $request->offered_salary ?? '',

                        '[HR_email_address]' => "hrJoanne.smith@yopmail.com",
                        '[HR_name]' => "Joanne Smith",
                        '[interview_date]' => $request->reminder_date,
                        '[interview_time]' => Carbon::parse($request->reminder_time)->format('h:i A'),
                        '[company_name]' => 'Unify Technology',
                        '[company_email_address]' => "unify.tech@yopmail.com",
                        '[company_website_url]' => 'http://unifytechnology.com',
                        '[company_logo]' => null,
                        '[phone_number]' => 7900675867,
                        '[work_location]' => "Mohali Sectore 67",
                        '[offered_accept_button]' => "<button style='color:green; margin:0;font-size:16px'>Accept</button>",
                        '[offered_decline_button]' => "<button style='color:red; margin:0; font-size:16px'>Decline</button>",
                    ];

                    foreach ($placeholders as $key => $value) {
                        if ($value !== null && str_contains($content, $key)) {
                            $content = str_replace($key, $value, $content);
                        }
                    }

                    $getHiringTemplateDetails->content = $content;
                    return $this->successResponse(
                        new HiringTemplateResource($getHiringTemplateDetails),
                        'Hiring template details retrieved successfully with flag 1'
                    );
                }else {
                    return $this->successResponse(
                        new HiringTemplateResource($getHiringTemplateDetails),
                        'Hiring template details retrieved successfully'
                    );
                }
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
         * @OA\Post(
         *     path="/uc/api/hiringtemplate/update/{id}",
         *     operationId="updatehiringTemplates",
         *     tags={"Hiring Templates"},
         *     summary="Update hiring template data",
         *     security={{"Bearer": {}}},
         *     description="Endpoint to process hiring template data.",
         *    @OA\Parameter(
         *     name="id",
         *     in="path",
         *     required=true,
         *     @OA\Schema(type="integer")
         *    ),
         *     @OA\RequestBody(
         *         required=true,
         *         @OA\MediaType(
         *             mediaType="multipart/form-data",
         *             @OA\Schema(
         *                 type="object",
         *                 @OA\Property(property="template_name", type="string", description="Name of the template."),
         *                 @OA\Property(property="title", type="string", description="Title of the template."),
         *                 @OA\Property(property="status", type="integer", description="Status of the template (1 for active, 0 for inactive)."),
         *                 @OA\Property(property="header_image", type="string", format="binary", description="Header image"),
         *                 @OA\Property(property="background_image", type="string",format="binary", description="Background image"),
         *                 @OA\Property(property="watermark", type="string",format="binary",description="Watermark image."),
         *                 @OA\Property(property="footer_image", type="string",format="binary",description="Footer image."),
         *                 @OA\Property(property="name", type="string",description="Name of the person"),
         *                 @OA\Property(property="phone", type="string",description="Phone number"),
         *                 @OA\Property(property="email", type="string",description="Email address"),
         *                 @OA\Property(property="date_of_issue", type="string",description="Date of issue"),
         *                 @OA\Property(property="content", type="string", description="Content of the template")
         *             )
         *         )
         *     ),
         *     @OA\Response(
         *         response=201,
         *         description="Template updated successfully.",
         *         @OA\JsonContent(
         *             type="object",
         *             @OA\Property(property="message", type="string", example="Template update successfully."),
         *             @OA\Property(property="template", type="object", description="Details of the updated template.")
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

        //  public function update(HiringTemplateRequest $request, $HiringTemplateId)
        //  {
        //      try {
        //          $findHiringTemplateDetail = HiringTemplate::find($HiringTemplateId);
        //          if (!$findHiringTemplateDetail) {
        //              return $this->validationErrorResponse("The given data is not found");
        //          }

        //          $validated = $request->validated();

        //          /**
        //           * Handle file uploads or retain previous images
        //           */
        //          $data = [
        //              'header_image' => $findHiringTemplateDetail->header_image,
        //              'background_image' => $findHiringTemplateDetail->background_image,
        //              'watermark' => $findHiringTemplateDetail->watermark,
        //              'footer_image' => $findHiringTemplateDetail->footer_image
        //          ];

        //          if ($request->hasFile('header_image')) {
        //              $headerPath = public_path('hiringtemplate/header');
        //              $data['header_image'] = $this->uploadImage($request->file('header_image'), $headerPath);
        //          }

        //          if ($request->hasFile('background_image')) {
        //              $backgroundPath = public_path('hiringtemplate/background');
        //              $data['background_image'] = $this->uploadImage($request->file('background_image'), $backgroundPath);
        //          }

        //          if ($request->hasFile('watermark')) {
        //              $watermarkPath = public_path('hiringtemplate/watermark');
        //              $data['watermark'] = $this->uploadImage($request->file('watermark'), $watermarkPath);
        //          }

        //          if ($request->hasFile('footer_image')) {
        //              $footerPath = public_path('hiringtemplate/footer');
        //              $data['footer_image'] = $this->uploadImage($request->file('footer_image'), $footerPath);
        //          }

        //          // Merge validated data with image paths
        //          $findHiringTemplateDetail->update(array_merge($validated, $data));

        //          return $this->successResponse(
        //              new HiringTemplateResource($findHiringTemplateDetail),
        //              'Hiring Template updated Successfully'
        //          );
        //      } catch (Exception $ex) {
        //          return $this->errorResponse($ex->getMessage());
        //      }
        //  }


        public function update(HiringTemplateRequest $request, $HiringTemplateId)
        { 
             try {
                 $findHiringTemplateDetail = HiringTemplate::find($HiringTemplateId);
                 if (!$findHiringTemplateDetail) {
                     return $this->validationErrorResponse("The given data is not found");
                 }
 
                 $validated = $request->validated();
 
                 /**
                  * Handle file uploads or retain previous images
                  */
                 $data = [
                     'header_image' => $findHiringTemplateDetail->header_image,
                     'background_image' => $findHiringTemplateDetail->background_image,
                     'watermark' => $findHiringTemplateDetail->watermark,
                     'footer_image' => $findHiringTemplateDetail->footer_image,
                     'icon_positions'=>$findHiringTemplateDetail->icon_positions,
                     'icon_files'=>$findHiringTemplateDetail->icon_files,
                 ];
 
                 if ($request->hasFile('header_image')) {
                     $headerPath = public_path('hiringtemplate/header');
                     $data['header_image'] = $this->uploadImage($request->file('header_image'), $headerPath);
                 }
 
                 if ($request->hasFile('background_image')) {
                     $backgroundPath = public_path('hiringtemplate/background');
                     $data['background_image'] = $this->uploadImage($request->file('background_image'), $backgroundPath);
                 }
 
                 if ($request->hasFile('watermark')) {
                     $watermarkPath = public_path('hiringtemplate/watermark');
                     $data['watermark'] = $this->uploadImage($request->file('watermark'), $watermarkPath);
                 }
 
                 if ($request->hasFile('footer_image')) {
                     $footerPath = public_path('hiringtemplate/footer');
                     $data['footer_image'] = $this->uploadImage($request->file('footer_image'), $footerPath);
                 }
 
                if ($request->has('icon_files') ) {
           
                    $iconFiles = $request->input('icon_files', []);
                    $iconFilesUploads = $request->file('icon_files', []);
                    if($iconFiles=="Data not found") // if they dont haver any image they will  send this string
                    {
                        $iconFiles =[];
                    }
                    $processed_files = [];
                    $processed_positions = [];
                    $watermarkPath = public_path('hiringtemplate/watermark');
                
    
                    foreach ($iconFiles as $index => $entry) {  
                
                        $fileInput = $iconFilesUploads[$index]['file'] ?? $entry['file'] ?? null;
                            $position = isset($entry['position']) ? $entry['position'] : [];
    
                            if ($fileInput instanceof \Illuminate\Http\UploadedFile) {
                                $filename = $this->uploadImage($fileInput, $watermarkPath);
                            } elseif (is_string($fileInput) && filter_var($fileInput, FILTER_VALIDATE_URL)) {
                                $filename = basename($fileInput);
                            } else {
                                continue; // Skip if not valid
                            }
                            $processed_files[] = $filename;
                            $processed_positions[] = $position;
                        }
                    
                    
                    
                    $data['icon_positions'] = json_encode($processed_positions);
                    $data['icon_files'] = json_encode( $processed_files); //$data['icon_positions']
    
                    
                }
            
                //Merge validated data with image/file info
                $mergedData = array_merge($validated, $data);

                //Check if anything has changed
                $changedFields = $this->getChangedFields($findHiringTemplateDetail, $validated, $data);

                if (empty($changedFields)) {
                    return $this->successResponse(
                        new HiringTemplateResource($findHiringTemplateDetail),
                        'No changes detected in Hiring Template'
                    );
                }

                $findHiringTemplateDetail->update($mergedData);
                $this->logHiringTemplateUpdateDetails($changedFields, $findHiringTemplateDetail);
 
                 return $this->successResponse(
                     new HiringTemplateResource($findHiringTemplateDetail),
                     'Hiring Template updated Successfully'
                 );
             } catch (Exception $ex) {
                 return $this->errorResponse($ex->getMessage());
             }
        }

        private function logHiringTemplateUpdateDetails(array $changes, $template)
        {
            try {
                $user = auth('sanctum')->user();
                if (!$user) return;

                $templateName = $template->template_name ?? 'Unknown Template';
                $typeLabel = match ((int) $template->template_type) {
                    0 => 'Email Template',
                    1 => 'Offered Template',
                    default => 'Unknown Type',
                };

                $statusLabel = match ((int) $template->status) {
                    0 => 'Inactive',
                    1 => 'Active',
                    default => 'Unknown Status',
                };

                foreach ($changes as $field => $change) {
                    $fieldLabel = ucwords(str_replace('_', ' ', $field));

                    UpdateSystemSetupHistory::create([
                        'employee_id' => $user->id,
                        'updated_by' => $user->id,
                        'date' => now()->format('Y-m-d'),
                        'time' => now()->format('H:i:s'),
                        'notes' => "{$templateName} ({$typeLabel}, {$statusLabel}) - Field updated: {$fieldLabel}",
                        'changed' => "{$fieldLabel} changed from '{$change['old']}' to '{$change['new']}'",
                        'created_at' => now(),
                        'updated_at' => now()
                    ]);
                }
            } catch (\Throwable $e) {
                \Log::error('Failed to log detailed Hiring Template update: ' . $e->getMessage());
            }
        }


        private function getChangedFields($template, $validated, $data): array
        {
            $changes = [];

            foreach ($validated as $key => $value) {
                if ((string)$template->$key !== (string)$value) {
                    $changes[$key] = [
                        'old' => $template->$key,
                        'new' => $value
                    ];
                }
            }

            $fileFields = ['header_image', 'background_image', 'watermark', 'footer_image'];
            foreach ($fileFields as $field) {
                if ((string)$template->$field !== (string)$data[$field]) {
                    $changes[$field] = [
                        'old' => $template->$field,
                        'new' => $data[$field]
                    ];
                }
            }

            // JSON fields
            $jsonFields = ['icon_positions', 'icon_files'];
            foreach ($jsonFields as $field) {
                $oldValue = is_string($template->$field) ? json_decode($template->$field, true) : $template->$field;
                $newValue = is_string($data[$field]) ? json_decode($data[$field], true) : $data[$field];

                if ($oldValue != $newValue) {
                    $changes[$field] = [
                        'old' => json_encode($oldValue),
                        'new' => json_encode($newValue)
                    ];
                }
            }

            return $changes;
        }



    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */

     /**
     * @OA\Delete(
     * path="/uc/api/hiringtemplate/destroy/{id}",
     * operationId="deleteHiringTemplate",
     * tags={"Hiring Templates"},
     * summary="Delete Hiring Templates Request",
     * security={ {"Bearer": {} }},
     * description="Delete Hiring Templates Request",
     * @OA\Parameter(
     *     name="id",
     *     in="path",
     *     required=true,
     *     @OA\Schema(type="integer")
     * ),
     * @OA\Response(
     *     response=200,
     *     description="Hiring Templates Deleted Successfully",
     *     @OA\JsonContent()
     * ),
     * @OA\Response(
     *     response=404,
     *     description="Resource Not Found"
     * )
     * )
     */
    public function destroy($HiringTemplateId)
    {
        try {
            $getHiringTemplateDetails = HiringTemplate::find($HiringTemplateId);
            if (isset($getHiringTemplateDetails)) {

                // Log the deletion BEFORE deleting the record
                $this->logHiringTemplateDeletion($getHiringTemplateDetails);

                $getHiringTemplateDetails->delete();
                return $this->successResponse(
                    [],
                    'Hiring Template Removed Sucessfully'
                );
            } else {
                return $this->validationErrorResponse('the given data is not found');
            }
        } catch (Exception $ex) {
            return $this->errorResponse($ex->getMessage());
        }
    }

    private function logHiringTemplateDeletion($template)
    {
        try {
            $user = auth('sanctum')->user();
            if (!$user) return;

            $templateName = $template->template_name ?? 'Unknown Template';

            UpdateSystemSetupHistory::create([
                'employee_id' => $user->id,
                'updated_by' => $user->id,
                'date' => now()->format('Y-m-d'),
                'time' => now()->format('H:i:s'),
                'notes' => 'Hiring Template Deleted',
                'changed' => "{$templateName} Hiring Template deleted",
                'created_at' => now(),
                'updated_at' => now()
            ]);
        } catch (\Throwable $e) {
            \Log::error('Failed to log Hiring Template deletion: ' . $e->getMessage());
        }
    }





     /**
     * @OA\Get(
     * path="/uc/api/hiringtemplate/template_list",
     * operationId="template_list",
     * tags={"Hiring Templates"},
     * summary="Get Hiring Templates List",
     *   security={ {"Bearer": {} }},
     * description="Get Hiring Templates List",
     *      @OA\Response(
     *          response=201,
     *          description="Hiring Templates List Get Successfully",
     *          @OA\JsonContent()
     *       ),
     *      @OA\Response(
     *          response=200,
     *          description="Hiring Templates List Get Successfully",
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

     public function templateList()
     {
        //
         try {
             $getHiringTemplateList = HiringTemplate::get();
             return $this->successResponse(
                 new HiringTemplateCollection($getHiringTemplateList, false),
                 'Hiring Template list'
             );
         } catch (Exception $ex) {
             return $this->errorResponse($ex->getMessage());
         }
     }


          /**
     * @OA\Get(
     * path="/uc/api/hiringtemplate/templateVariableNameList",
     * operationId="templateVariableNameList",
     * tags={"Hiring Templates"},
     * summary="Get Hiring Templates Variable Name List",
     *   security={ {"Bearer": {} }},
     * description="Get Hiring Templates Variable Name List",
     *      @OA\Response(
     *          response=201,
     *          description="Hiring Templates Variable Name List Get Successfully",
     *          @OA\JsonContent()
     *       ),
     *      @OA\Response(
     *          response=200,
     *          description="Hiring Templates Variable Name List Get Successfully",
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


     public function templateVariableNameList()
{
    try {
        $placeholders = [
            '[candidate_name]',
            '[designation]',
            '[joining_date]',
            '[salary]',
            '[HR_email_address]',
            '[HR_name]',
            '[interview_date]',
            '[interview_time]',
            '[company_name]',
            '[company_email_address]',
            '[company_website_url]',
            '[company_logo]',
            '[phone_number]',
            '[work_location]',
            '[offered_accept_button]',
            '[offered_decline_button]',
        ];

        return $this->successResponse($placeholders, 'Template variable name list');
    } catch (Exception $ex) {
        return $this->errorResponse($ex->getMessage());
    }
}



}
