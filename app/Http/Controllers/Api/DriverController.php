<?php

namespace App\Http\Controllers\Api;

use App\Models\ClientDocuments;
use App\Models\Holiday;
use App\Models\Invoice;
use App\Models\Schedule;
use App\Models\ScheduleCarer;
use App\Models\ScheduleCarerStatus;
use App\Models\SubUser;
use App\Models\User;
use App\Models\PriceTableData;
use Carbon\Carbon;
use Google\Service\Sheets\ChartData;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Validator;
use Illuminate\Container\Container;
use Illuminate\Support\Str;
use Illuminate\Pagination\LengthAwarePaginator;

class DriverController extends Controller
{
    //*********************** Store driver documents api******************************** */

    /**
     * @OA\Post(
     * path="/uc/api/store-driver-documents",
     * operationId="uploadDocuments",
     * tags={"Driver"},
     * summary="Upload document",
     *   security={ {"Bearer": {} }},
     * description="Upload document",
     *     @OA\RequestBody(
     *         @OA\JsonContent(),
     *         @OA\MediaType(
     *            mediaType="multipart/form-data",
     *            @OA\Schema(
     *               type="object",
     *               required={"file", "type","no_expiration","category"},
     *               @OA\Property(property="file", type="file"),
     *               @OA\Property(property="type", type="text"),
     *               @OA\Property(property="no_expiration", type="text", description="Indicates if the document has no expiration (1 for true, 0 for false)"),
     *               @OA\Property(property="expire", type="text", description="Expiration date (required if no_expiration is 0)"),
     *               @OA\Property(property="category", type="text"),
     *               
     *            ),
     *        ),
     *    ),
     *      @OA\Response(
     *          response=201,
     *          description="Your Successfully uploaded.",
     *          @OA\JsonContent()
     *       ),
     *      @OA\Response(
     *          response=200,
     *          description="Your Successfully uploaded.",
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

    public function uploadDocuments(Request $request)
    {


        try {
            $user_id = auth('sanctum')->user()->id;
            $user = SubUser::find($user_id);
            if ($user->hasRole('driver')) {

                $request->validate([
                    'file' => 'required|file|mimes:pdf,jpg,jpeg,png,docx',
                    'type' => 'required',
                    'no_expiration' => 'required|integer',
                    'expire' => $request->no_expiration == 0 ? 'required' : 'nullable',
                    'category' => 'required',
                ]);
                $date = null;
                if ($request->expire) {
                    $cleanDateString = trim($request->expire, '"');
                    $date = date('Y-m-d', strtotime($cleanDateString));
                }
                $cleanType = trim($request->type, '"');

                $file = $request->file('file');
                $filename = time() . '.' . $request->file('file')->extension();
                $filePath = public_path() . '/files/uploads/';
                $file->move($filePath, $filename);
                //echo auth('sanctum')->user()->id;die;
                $driverDocuments = new ClientDocuments();
                $driverDocuments->client_id = auth('sanctum')->user()->id;
                $driverDocuments->type = $cleanType;
                $driverDocuments->name = $filename;
                $driverDocuments->category = $request->category;
                $driverDocuments->no_expireation = $request->no_expiration;
                if ($request->no_expiration == 0) {
                    $driverDocuments->expire = $date;
                }
                $documentSaved = $driverDocuments->save();


                if ($documentSaved) {
                    return response()->json(['success' => true, "message" => "Document submitted successfully"], 200);
                } else {
                    return response()->json(['success' => false, "message" => 'Something went wrong'], 500);
                }
            } else {
                return response()->json(['success' => false, "message" => 'User is not a driver'], 401);
            }
        } catch (\Throwable $th) {
            return response()->json([
                'success' => false,
                'message' => $th->getMessage()
            ], 500);
        }
    }
    //********************************Accept driver document api **************************/

    /**
     * @OA\Post(
     * path="/uc/api/acceptDocument",
     * operationId="acceptDocument",
     * tags={"Driver"},
     * summary="Accept document",
     *   security={ {"Bearer": {} }},
     * description="Accept document",
     *     @OA\RequestBody(
     *         @OA\JsonContent(),
     *         @OA\MediaType(
     *            mediaType="multipart/form-data",
     *            @OA\Schema(
     *               type="object",
     *               required={"id"},
     *               @OA\Property(property="id", type="text"),
     *               
     *            ),
     *        ),
     *    ),
     *      @OA\Response(
     *          response=201,
     *          description="Document accepted successfully.",
     *          @OA\JsonContent()
     *       ),
     *      @OA\Response(
     *          response=200,
     *          description="Document accepted successfully.",
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
    public function acceptDocument(Request $request)
    {
        $user = auth('sanctum')->user();
        if ($user) {
            $request->validate([
                'id' => 'required|exists:client_documents,id'
            ]);
            $document = ClientDocuments::where('id', $request->id)->first();
            $document->status = 1;
            $document->save();
            return response()->json(['success' => true, "message" => "Document accepted successfully"], 200);
        }
        return response()->json(['success' => false, "message" => "Unauthorised user"], 401);
    }


    //*********************************** Reject document *********************************************************** */
    /**
     * @OA\Post(
     * path="/uc/api/rejectDocument",
     * operationId="rejectDocument",
     * tags={"Driver"},
     * summary="Accept document",
     *   security={ {"Bearer": {} }},
     * description="Reject document",
     *     @OA\RequestBody(
     *         @OA\JsonContent(),
     *         @OA\MediaType(
     *            mediaType="multipart/form-data",
     *            @OA\Schema(
     *               type="object",
     *               required={"id"},
     *               @OA\Property(property="id", type="text"),
     *               
     *            ),
     *        ),
     *    ),
     *      @OA\Response(
     *          response=201,
     *          description="Document rejected ",
     *          @OA\JsonContent()
     *       ),
     *      @OA\Response(
     *          response=200,
     *          description="Document rejected",
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
    public function rejectDocument(Request $request)
    {
        $user = auth('sanctum')->user();
        if ($user) {
            $request->validate([
                'id' => 'required|exists:client_documents,id'
            ]);
            $document = ClientDocuments::where('id', $request->id)->first();
            $document->status = 2;
            $document->save();
            return response()->json(['success' => true, "message" => "Document rejected"], 200);
        }
        return response()->json(['success' => false, "message" => "Unauthorised user"], 401);
    }




    //******************************** Get driver documents api*************************** */


    /**
     * @OA\Get(
     * path="/uc/api/show-documents",
     * operationId="showDocuments",
     * tags={"Driver"},
     * summary="Show Documents",
     *   security={ {"Bearer": {} }},
     * description="Show Documents",
     *      @OA\Response(
     *          response=201,
     *          description="List docuemnts",
     *          @OA\JsonContent()
     *       ),
     *      @OA\Response(
     *          response=200,
     *          description="List docuemnts",
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

    public function showDocuments()
    {
        try {
            $user_id = auth('sanctum')->user()->id;
            $user = SubUser::find($user_id);
            if ($user->hasRole('driver')) {
                //echo '<pre>';print_r(\DB::connection()->getDatabaseName());die;
                $clientDocuments = ClientDocuments::where('client_id', $user_id)->get();
                foreach ($clientDocuments as $document) {

                    if ($document->expire) {

                        $expirationDate = Carbon::parse($document->expire);
                        $isExpired = $expirationDate->isPast();
                        $document->expired = $isExpired;
                    } else {
                        $document->expired = 'no_expiration';
                    }
                }
                if ($clientDocuments) {
                    $this->data = $clientDocuments;
                    return response()->json(['success' => true, "data" => $this->data, "image_path" => url('public/files/uploads/')], 200);
                }
            } else {
                return response()->json(['success' => false, "message" => 'User is not a driver'], 401);
            }
        } catch (\Throwable $th) {
            return response()->json([
                'success' => false,
                'message' => $th->getMessage()
            ], 500);
        }
    }

    /**
     * @OA\Post(
     * path="/uc/api/driver/schedules",
     * operationId="schedules",
     * tags={"Driver"},
     * summary="Driver schedules",
     *   security={ {"Bearer": {} }},
     * description="Driver schedules",
     *     @OA\RequestBody(
     *         @OA\JsonContent(),
     *         @OA\MediaType(
     *            mediaType="multipart/form-data",
     *            @OA\Schema(
     *               type="object",
     *               required={"date"},
     *               @OA\Property(property="date", type="date"),
     *                
     *            ),
     *        ),
     *    ),
     *      @OA\Response(
     *          response=201,
     *          description="The driver schedules  listed successfully.",
     *          @OA\JsonContent()
     *       ),
     *      @OA\Response(
     *          response=200,
     *          description="The driver schedules  listed successfully.",
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

    public function schedules(Request $request)
    {
        try {
            $request->validate([
                'date' => 'required|date'
            ]);
            $user_id = auth('sanctum')->user()->id;
            $user = SubUser::find($user_id);
            $user_ids = array($user_id);
            $date = $request->date;
            $dates = array($date);

            if ($user) {
                if ($user->hasRole('driver')) {
                    $this->data['schedules'] = $this->getWeeklyScheduleInfo($user_ids, $dates, 1, "all");
                }
                return response()->json(['success' => true, "data" => $this->data, "message" => "The driver schedules  listed successfully"], 200);
            }
            return response()->json(['success' => false, "message" => "The user is not a driver"], 500);
        } catch (\Throwable $th) {
            return response()->json([
                'success' => false,
                'message' => $th->getMessage()
            ], 500);
        }
    }

    //********************** Update driver profile api******************************* */

    /**
     * @OA\Post(
     * path="/uc/api/update-profile",
     * operationId="updateProfile",
     * tags={"Home Data"},
     * summary="Update profile",
     *   security={ {"Bearer": {} }},
     * description="Update profile",
     *     @OA\RequestBody(
     *         @OA\JsonContent(),
     *         @OA\MediaType(
     *            mediaType="multipart/form-data",
     *            @OA\Schema(
     *               type="object",
     *              
     *               @OA\Property(property="first_name", type="text"),
     *               @OA\Property(property="middle_name", type="text"),
     *               @OA\Property(property="last_name", type="text"),
     *               
     *                
     *            ),
     *        ),
     *    ),
     *      @OA\Response(
     *          response=201,
     *          description="The profile updated successfully.",
     *          @OA\JsonContent()
     *       ),
     *      @OA\Response(
     *          response=200,
     *          description="The profile updated successfully.",
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

    public function updateProfile(Request $request)
    {
        try {

            $request->validate([
                'first_name' => 'string|nullable',
                'middle_name' => 'string|nullable',
                'last_name' => 'string|nullable',

            ]);

            $user_id = auth('sanctum')->user()->id;
            $user = SubUser::where('id', $user_id)->first();




            if ($user) {
                if ($user->hasRole('driver')) {
                    $temp_DB_name = DB::connection()->getDatabaseName();
                    //connecting to parent DB
                    $default_DBName = env("DB_DATABASE");
                    // dd($default_DBName);
                    $this->connectDB($default_DBName);
                    //updating driver in parent DB
                    $update = SubUser::where('email', $user->email)->first();
                    $update->first_name = $request->first_name ? $request->first_name : $update->first_name;
                    $update->middle_name = $request->middle_name ? $request->middle_name : $update->middle_name;
                    $update->last_name = $request->last_name ? $request->last_name : $update->last_name;

                    $update->save();
                    //connecting back to Child DB
                    $this->connectDB($temp_DB_name);

                    $child_update = SubUser::where('email', $user->email)->first();
                    $child_update->first_name = $request->first_name ? $request->first_name : $child_update->first_name;
                    $child_update->middle_name = $request->middle_name ? $request->middle_name : $child_update->middle_name;
                    $child_update->last_name = $request->last_name ? $request->last_name : $child_update->last_name;

                    $child_update->save();
                }
                if ($user->hasRole('carer')) {
                    $temp_DB_name = DB::connection()->getDatabaseName();
                    //connecting to parent DB
                    $default_DBName = env("DB_DATABASE");
                    // dd($default_DBName);
                    $this->connectDB($default_DBName);
                    //updating staff in parent DB
                    $update = SubUser::where('email', $user->email)->first();
                    $update->first_name = $request->first_name ? $request->first_name : $update->first_name;
                    $update->middle_name = $request->middle_name ? $request->middle_name : $update->middle_name;
                    $update->last_name = $request->last_name ? $request->last_name : $update->last_name;

                    $update->save();
                    //connecting back to Child DB
                    $this->connectDB($temp_DB_name);
                    $child_update = User::where('email', $user->email)->first();
                    $child_update->first_name = $request->first_name ? $request->first_name : $child_update->first_name;
                    $child_update->middle_name = $request->middle_name ? $request->middle_name : $child_update->middle_name;
                    $child_update->last_name = $request->last_name ? $request->last_name : $child_update->last_name;

                    $child_update->save();

                    $child_user = SubUser::where('email', $user->email)->first();
                    $child_user->first_name = $request->first_name ? $request->first_name : $child_user->first_name;
                    $child_user->middle_name = $request->middle_name ? $request->middle_name : $child_user->middle_name;
                    $child_user->last_name = $request->last_name ? $request->last_name : $child_user->last_name;

                    $child_user->save();
                }

                return response()->json(['success' => true, "message" => "Profile updated successfully"], 200);
            }
            return response()->json(['success' => false, "message" => "The user is not a driver"], 500);
        } catch (\Throwable $th) {
            return response()->json([
                'success' => false,
                'message' => $th->getMessage()
            ], 500);
        }
    }

    //*************************************** Driver Invoice *******************************/

    /**
     * @OA\Post(
     * path="/uc/api/driverInvoice",
     * operationId="getTotalInvoiceForDriverInRange",
     * tags={"Driver"},
     * summary="Driver billing",
     *   security={ {"Bearer": {} }},
     * description="Driver billing",
     *     @OA\RequestBody(
     *         @OA\JsonContent(),
     *         @OA\MediaType(
     *            mediaType="multipart/form-data",
     *            @OA\Schema(
     *               type="object",
     *               required={"startDate", "driverId"},
     *               @OA\Property(property="startDate", type="text"),
     *               @OA\Property(property="endDate", type="text"),
     *               @OA\Property(property="driverId", type="text"),
     *               @OA\Property(property="payment_status", type="text", description="1:Unpaid, 2:Paid"),
     *                
     *            ),
     *        ),
     *    ),
     *      @OA\Response(
     *          response=201,
     *          description="The driver schedules invoice listed successfully.",
     *          @OA\JsonContent()
     *       ),
     *      @OA\Response(
     *          response=200,
     *          description="The driver schedules invoice listed successfully.",
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

    public function getTotalInvoiceForDriverInRange(Request $request)
    {
        try {
            $request->validate([
                'startDate' => 'required|date_format:Y-m-d|before_or_equal:today',
                'endDate' => 'nullable|date_format:Y-m-d',
                'driverId' => 'required|numeric|exists:sub_users,id',
                'payment_status' => 'nullable|in:1,2',
            ]);

            $driverId = $request->driverId;

            $startDate = $request->startDate;
            if ($request->has('endDate')) {
                $endDate = min($request->endDate, now()->toDateString());
            }
            if ($endDate === null) {
                $endDate = $startDate;
            }

            $dates = $this->generateDatesInRange($startDate, $endDate);
            $payment_status = $request->payment_status;

            // Fetch driver information
            $driver = SubUser::where('id', $driverId)
                ->whereHas('roles', function ($q) {
                    $q->where('name', 'driver');
                })->first();


            // $invoice_data = Invoice::whereIn('date', $dates)->where('ride_status', 8)->where('driver_id', $driver->id)->get();


            $invoice_data = Invoice::whereIn('date', $dates)->where('is_included', 1)->where('driver_id', $driver->id)->get();


            $totalAmount =0;
            foreach($invoice_data as $invoice){
                $daytype = Carbon::createFromFormat('Y-m-d', $invoice->date);
                $dayOfWeek = $daytype->dayOfWeek;

                $category ="";
                if ($dayOfWeek >= 1 && $dayOfWeek <= 5) {
                    // Monday to Friday
                    $category = 'Weekdays (mon- fri)';
                } elseif ($dayOfWeek === 6) {
                    // Saturday
                    $category = 'saturday';
                } else {
                    // Sunday
                    $category = 'sunday';
                }
                $pricebookId =  $invoice->pricebook_id;
                $schedulePrice = PriceTableData::where('price_book_id', $pricebookId)
                        ->where('day_of_week', $category)
                        ->first();
                $totalAmount+= $schedulePrice->per_ride;
            }

            $unpaids = Invoice::whereIn('date', $dates)->where('status', 1)->where('driver_id', $driver->id)->get();

            $totalunpaid =0;
            foreach($unpaids as $invoice){
                $daytype = Carbon::createFromFormat('Y-m-d', $invoice->date);
                $dayOfWeek = $daytype->dayOfWeek;

                $category ="";
                if ($dayOfWeek >= 1 && $dayOfWeek <= 5) {
                    // Monday to Friday
                    $category = 'Weekdays (mon- fri)';
                } elseif ($dayOfWeek === 6) {
                    // Saturday
                    $category = 'saturday';
                } else {
                    // Sunday
                    $category = 'sunday';
                }
                $pricebookId =  $invoice->pricebook_id;
                $schedulePrice = PriceTableData::where('price_book_id', $pricebookId)
                        ->where('day_of_week', $category)
                        ->first();
                $totalunpaid+= $schedulePrice->per_ride;
            }


            $paids = Invoice::whereIn('date', $dates)->where('status', 2)->where('driver_id', $driver->id)->get();

            $totalpaid =0;
            foreach($paids as $invoice){
                $daytype = Carbon::createFromFormat('Y-m-d', $invoice->date);
                $dayOfWeek = $daytype->dayOfWeek;

                $category ="";
                if ($dayOfWeek >= 1 && $dayOfWeek <= 5) {
                    // Monday to Friday
                    $category = 'Weekdays (mon- fri)';
                } elseif ($dayOfWeek === 6) {
                    // Saturday
                    $category = 'saturday';
                } else {
                    // Sunday
                    $category = 'sunday';
                }
                $pricebookId =  $invoice->pricebook_id;
                $schedulePrice = PriceTableData::where('price_book_id', $pricebookId)
                        ->where('day_of_week', $category)
                        ->first();
                $totalpaid+= $schedulePrice->per_ride;
            }


            $total_billing = $invoice_data->sum('fare');
            $total_count = $invoice_data->count();
            $unpaid_count = $invoice_data->where('status', 1)->count();
            $paid_count = $invoice_data->where('status', 2)->count();
            $unpaid = $invoice_data->where('status', 1)->sum('fare');
            $paid = $invoice_data->where('status', 2)->sum('fare');

            $billing_data = [
                'total_billing' => $totalAmount,  //$total_billing
                'unpaid' => $totalunpaid, //$unpaid,
                'paid' => $totalpaid, //$paid,
                'total_count' => $total_count,
                'unpaid_count' => $unpaid_count,
                'paid_count' => $paid_count,
            ];

            $invoicesQuery = Invoice::with(['driver', 'schedule', 'pricebook'])
                ->where('driver_id', $driverId)
                ->whereIn('date', $dates);

            if ($payment_status == 1) {
                $invoicesQuery->where('status', 1);
            } elseif ($payment_status == 2) {
                $invoicesQuery->where('status', 2);
            }

            // Fetch invoices
            $invoices = $invoicesQuery->get();
            $rides = [];
            $absent_rides = $this->getAbsentRidesInfo($driver->id, $dates)['absent_rides']; // Fetch absent rides
            $pie_chart = $this->getAbsentRidesInfo($driver->id, $dates)['pie_chart']; // Fetch absent rides



            foreach ($invoices as $invoice) {
                $scheduleCarers = ScheduleCarer::where('schedule_id', $invoice->schedule_id)
                    ->where('shift_type', $invoice->type)
                    ->get();
                $carersDetails = [];
                foreach ($scheduleCarers as $scheduleCarer) {
                    $scheduleCarerStatus = ScheduleCarerStatus::where('schedule_carer_id', $scheduleCarer->id)
                        ->where('date', $invoice->date)
                        ->first();
                    $address = $scheduleCarer->userAddress->address;
                    $latitude = $scheduleCarer->userAddress->latitude;
                    $longitude = $scheduleCarer->userAddress->longitude;
                    $carer = $scheduleCarer->user;
                    $carerName = @$carer->first_name;
                    $carerStartTime = @$scheduleCarerStatus->start_time;
                    $carerEndTime = @$scheduleCarerStatus->end_time;
                    // Add carer details to array
                    $carersDetails[] = [
                        'name' => $carerName,
                        'start_time' => $carerStartTime ?? 'Absent',
                        'end_time' => $carerEndTime ?? 'Absent',
                        'address' => $address,
                        'latitude' => $latitude,
                        'longitude' => $longitude,
                    ];
                }

                $day = Carbon::createFromFormat('Y-m-d', $invoice->date)->englishDayOfWeek;

                $daytype = Carbon::createFromFormat('Y-m-d', $invoice->date);
                $dayOfWeek = $daytype->dayOfWeek;

                $category ="";
                if ($dayOfWeek >= 1 && $dayOfWeek <= 5) {
                    // Monday to Friday
                    $category = 'Weekdays (mon- fri)';
                } elseif ($dayOfWeek === 6) {
                    // Saturday
                    $category = 'saturday';
                } else {
                    // Sunday
                    $category = 'sunday';
                }

                $pricebookId =  $invoice->pricebook_id;
                $schedulePrice = PriceTableData::where('price_book_id', $pricebookId)
                        ->where('day_of_week', $category)
                        ->first();
                $scheduleridePrice = $schedulePrice->per_ride;

                // Add invoice details to rides array
                $rides[] = [
                    'id' => $invoice->id,
                    'driver_id' => $invoice->driver_id,
                    'schedule_id' => $invoice->schedule_id,
                    'pricebook_id' => $invoice->pricebook_id,
                    'date' => $invoice->date,
                    'day' => $day,
                    'type' => $invoice->type,
                    'start_time' => $invoice->start_time,
                    'end_time' => $invoice->end_time,
                    'fare' => $invoice->fare,
                    'ride_status' => $invoice->ride_status,
                    'is_included' => $invoice->is_included,
                    'carers' => $carersDetails,
                    'day_category' =>$category,
                    'ride_price' =>$scheduleridePrice,
                    'driver' =>$invoice->driver,
                    'schedule' =>$invoice->schedule,
                    'pricebook' =>$invoice->pricebook,
                ];
            }

            // Calculate total invoice amount for rides with ride_status of 8
            $totalInvoice = $invoicesQuery->sum('fare');

            //$allRides = array_merge($rides, $absent_rides);
            $allRides = $rides;
            usort($allRides, function ($a, $b) {
                return strcmp($a['date'], $b['date']);
            });

            return response()->json([
                'success' => true,
                'data' => [
                    'driver_id' => $driver->id,
                    'driver_name' => $driver->first_name,
                    'billing_data' => $billing_data,
                    'pie_chart' => $pie_chart,
                    'allRides' => $allRides,
                    'totalInvoice' => $totalInvoice,
                ]
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'success' => false,
                'message' => $th->getMessage(),
            ], 500);
        }
    }

    public function getAbsentRidesInfo($driverId, $dates)
    {
        $absent_rides = [];
        $totalAbsentFare = 0;
        $scheduleInfo = $this->getWeeklyScheduleInfo([$driverId], $dates, 1, 'all');
        // dd($scheduleInfo);
        if (!$scheduleInfo) {
            return ['absent_rides' => $absent_rides, 'total_absent_fare' => $totalAbsentFare];
        }

        $home = new HomeController();
        $scheduleController = Container::getInstance()->make(ScheduleController::class);
        $absent = 0;
        $completed = 0;
        $pending = 0;
        foreach ($scheduleInfo as $schedule) {
            $schedule_id = $schedule['id'];
            if ($schedule['type'] == 'drop' && $schedule['shift_finishes_next_day'] == 1) {
                $scheduleDate = date('Y-m-d', strtotime($schedule['date'] . ' +1 day'));
            } else {
                $scheduleDate = date('Y-m-d', strtotime($schedule['date']));
            }
            $date = Carbon::createFromFormat('Y-m-d', $scheduleDate);
            $day = $date->englishDayOfWeek;
            $status = @$home->checkRideStatus($schedule['id'], $schedule['type'], $scheduleDate)['id'];
            if ($scheduleDate <= date('Y-m-d') && in_array($scheduleDate, $dates)) {
                if ($status == 9) {
                    $absent_amount = @$scheduleController->getFare($date, $schedule['pricebook_id']);
                    $totalAbsentFare += $absent_amount;
                    $absent++;

                    $absent_rides[] = [
                        'date' => $scheduleDate,
                        'day' => $day,
                        'schedule_id' => $schedule_id,
                        'type' => $schedule['type'],
                        'start_time' => null,
                        'end_time' => null,
                        'ride_status' => $status,
                        'absent_fare' => @$absent_amount ?? 0,
                        'is_included' =>  0,
                    ];
                } else if ($status == 8) {
                    $completed++;
                } else {
                    $pending++;
                }
            }
        }
        $pie_chart[] = [
            'absent' => $absent,
            'completed' => $completed,
            'pending' => $pending,
        ];

        return ['absent_rides' => $absent_rides, 'total_absent_fare' => $totalAbsentFare, 'pie_chart' => $pie_chart];
    }


    //************************** All driver Billing ******************************/
    /**
     * @OA\Post(
     * path="/uc/api/allDriverBilling",
     * operationId="allDriverBilling",
     * tags={"Driver"},
     * summary="All driver billing",
     *   security={ {"Bearer": {} }},
     * description="All driver billing",
     *     @OA\RequestBody(
     *         @OA\JsonContent(),
     *         @OA\MediaType(
     *            mediaType="multipart/form-data",
     *            @OA\Schema(
     *               type="object",
     *               required={"startDate","endDate"},
     *               @OA\Property(property="startDate",type="string", format="date", nullable=true, example="2025-07-01"),
     *               @OA\Property(property="endDate",type="string", format="date", nullable=true, example="2025-07-15"),
     *               @OA\Property(property="payment_status", type="text", description="1:Unpaid, 2:Paid"),
     *               @OA\Property(property="all", type="text", description="all"),
     *               @OA\Property(property="per_page", type="text", description="10"),
     *               @OA\Property(property="page", type="text", description="1"),
     *            ),
     *        ),
     *    ),
     *      @OA\Response(
     *          response=201,
     *          description="The driver schedules invoice listed successfully.",
     *          @OA\JsonContent()
     *       ),
     *      @OA\Response(
     *          response=200,
     *          description="The driver schedules invoice listed successfully.",
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

    public function allDriverBilling(Request $request)
    {

        try {

            $request->validate([
                'startDate' => 'nullable|date_format:Y-m-d',
                'endDate' => 'nullable|date_format:Y-m-d',
                'payment_status' => 'nullable|in:1,2',
                'all' => 'nullable|in:all'
            ]);
            
            $perPage = max((int) $request->input('per_page', 15), 15);
            $page = max((int) $request->input('page', 1), 1);

            //********************************** new code *********************************************/
            // $currentYear = Carbon::now()->year;
            // $currentMonth = Carbon::now()->month;

            // // Get the selected year and month from the request
            // $selectedYear = $currentYear;
            // $selectedMonth = $request->month;

            // if ($currentMonth != $selectedMonth) {
            //     // If the selected month is not the current month, use the current year
            //     $selectedYear = $currentYear;
            // }

            // $startDate = Carbon::createFromDate($selectedYear, $selectedMonth, 1);

            // $endDate = $startDate->copy()->endOfMonth();

            // if ($currentMonth == $selectedMonth) {
            //     $endDate = Carbon::now();
            // }

            $startDate = Carbon::parse($request->input('startDate'));
            $endDate = Carbon::parse($request->input('endDate'));

            if ($request->filled('all')) {

                $firstInvoice = Invoice::orderBy('date', 'asc')->first();
                $lastInvoice = Invoice::orderBy('date', 'desc')->first();

                if ($firstInvoice && $lastInvoice) {
                    
                    $startDate =  Carbon::parse($firstInvoice->date);
                    $endDate =  Carbon::parse($lastInvoice->date);
                    $dates = $this->generateDatesInRange($startDate, $endDate);
                }else{
                    
                    $start_Date = $startDate->format('Y-m-d');
                    $end_Date = $endDate->format('Y-m-d');
                    $dates = $this->generateDatesInRange($startDate, $endDate);
                }
            } else {
                    // Format the dates as strings
                    $start_Date = $startDate->format('Y-m-d');
                    $end_Date = $endDate->format('Y-m-d');
                    $dates = $this->generateDatesInRange($start_Date, $end_Date);
            }


            // $startDate = Carbon::parse($request->input('startDate'));
            // $endDate = Carbon::parse($request->input('endDate'));
            // // Format the dates as strings
            // $start_Date = $startDate->format('Y-m-d');
            // $end_Date = $endDate->format('Y-m-d');
            // $dates = $this->generateDatesInRange($start_Date, $end_Date);

            $paymentStatus = $request->payment_status;
            $chartData = $this->getChartData($dates, $paymentStatus);
           // $driversData = $this->getDriversData($dates, $paymentStatus);


            $collection = collect($this->getDriversData($dates, $paymentStatus));
            $paginatedDrivers = new LengthAwarePaginator(
                $collection->forPage($page, $perPage),
                $collection->count(),
                $perPage,
                $page,
                ['path' => $request->url(), 'query' => $request->query()]
            );
            

            $is_month_pay = Invoice::whereMonth('date', $startDate->format('m'))
                ->where('status', 2)
                ->count();

            return response()->json([
                'success' => true,
                'chart_data' => $chartData,
                'is_month_pay' => ($is_month_pay > 0) ? 1 : 0,
                'data' => $paginatedDrivers,
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'success' => false,
                'message' => $th->getMessage(),
                'line' => $th->getLine(),
                'file' => $th->getFile(),
            ], 500);
        }
    }

    public function getChartData($dates, $paymentStatus)
    {
        $invoiceData = Invoice::whereIn('date', $dates)->where('is_included', 1);
        if ($paymentStatus == 1) {
            $invoiceData->where('status', 1);
        } elseif ($paymentStatus == 2) {
            $invoiceData->where('status', 2);
        }
        // $totalBilling = $invoiceData->sum('fare');
        // $totalCount = $invoiceData->count();
        // $unpaidCount = $invoiceData->where('status', 1)->count();
        // $paidCount = $invoiceData->where('status', 2)->count();
        // $unpaid = $invoiceData->where('status', 1)->sum('fare');
        // $paid = $invoiceData->where('status', 2)->sum('fare');

        $invoiceResults = $invoiceData->get();


            $totalBillings =0;
            foreach($invoiceResults as $invoice){
                $daytype = Carbon::createFromFormat('Y-m-d', $invoice->date);
                $dayOfWeek = $daytype->dayOfWeek;

                $category ="";
                if ($dayOfWeek >= 1 && $dayOfWeek <= 5) {
                    // Monday to Friday
                    $category = 'Weekdays (mon- fri)';
                } elseif ($dayOfWeek === 6) {
                    // Saturday
                    $category = 'saturday';
                } else {
                    // Sunday
                    $category = 'sunday';
                }
                $pricebookId =  $invoice->pricebook_id;
                $schedulePrice = PriceTableData::where('price_book_id', $pricebookId)
                        ->where('day_of_week', $category)
                        ->first();
                $totalBillings+= @$schedulePrice->per_ride;
            }

        // Perform operations on the results
        $totalBilling = $totalBillings; //$invoiceResults->sum('fare');
        $totalCount = $invoiceResults->count();
        $unpaidCount = $invoiceResults->where('status', 1)->count();
        $paidCount = $invoiceResults->where('status', 2)->count();
        $unpaid = $invoiceResults->where('status', 1)->sum('fare');
        $paid = $invoiceResults->where('status', 2)->sum('fare');

        return [
            'total_billing' => $totalBilling,
            'unpaid' => $unpaid,
            'paid' => $paid,
            'total_count' => $totalCount,
            'unpaid_count' => $unpaidCount,
            'paid_count' => $paidCount,
        ];
    }

    private function getDriversData($dates, $paymentStatus)
    {
        $driversData = [];
        $drivers = SubUser::whereHas("roles", function ($q) {
            $q->where("name", "driver");
        })->get();

        foreach ($drivers as $driver) {

           $invoices = Invoice::where('driver_id', $driver->id)
                ->whereIn('date', $dates)
                ->when($paymentStatus, function ($query) use ($paymentStatus) {
                    $query->where('status', $paymentStatus);
                })
                //->where('ride_status',8) // only complted ride of a driver
                ->get();

            if($invoices->isEmpty()){
                continue;
            }

            $driverData = [
                'id' => $driver->id,
                'name' => $driver->first_name . ' ' . $driver->last_name,
                'email' => $driver->email,
                'profile_image' => $driver->profile_image,
                'image_path' => url('/uc/images/'),
                'total_amount' => 0,
                'absent_amount' => 0,
                'invoice_amount' => 0,
                'invoice_not_complete_amount' => 0,
            ];

            $absentRidesInfo = $this->getAbsentRidesInfo($driver->id, $dates);
            $absentRidesFare = $absentRidesInfo['total_absent_fare'] ?? 0;
            $completedRideCount = $invoices->count();

            
            // $invoices = Invoice::where('driver_id', $driver->id)
            //     ->whereIn('date', $dates)
            //     ->when($paymentStatus, function ($query) use ($paymentStatus) {
            //         $query->where('status', $paymentStatus);
            //     })
            //     ->get();

                
             // New code of price data
                $scheduleridePrice =0;
                foreach($invoices as $invoice){
                    $daytype = Carbon::createFromFormat('Y-m-d', $invoice->date);
                    $dayOfWeek = $daytype->dayOfWeek;

                    $category ="";
                    if ($dayOfWeek >= 1 && $dayOfWeek <= 5) {
                        // Monday to Friday
                        $category = 'Weekdays (mon- fri)';
                    } elseif ($dayOfWeek === 6) {
                        // Saturday
                        $category = 'saturday';
                    } else {
                        // Sunday
                        $category = 'sunday';
                    }
                    $pricebookId =  $invoice->pricebook_id;
                    $schedulePrice = PriceTableData::where('price_book_id', $pricebookId)
                            ->where('day_of_week', $category)
                            ->first();
                    $scheduleridePrice+= @$schedulePrice->per_ride;
                }

            // End of new code 

    
            $is_paid = Invoice::whereIn('date', $dates)->where('driver_id', $driver->id)->where('status', 2)->count();
            $driverData['paid_unpaid_status'] = ($is_paid > 0) ? "paid" : "unpaid";
            $driverData['invoice_status'] = ($invoice->status == 2) ? "paid" : "unpaid";

            $drivercompleteRideFare = $invoices->where('ride_status', 8)->sum('fare');
            $driverIncompleteRideFare = $invoices->where('ride_status', 6)->where('is_included', 0)->sum('fare');

            $driverIncompleteRideFareIncluded = $invoices->where('ride_status', 6)->where('is_included', 1)->sum('fare');
            $absentAmount =  $absentRidesFare;

            $driverData['absent_amount'] = $absentAmount;
            $driverData['invoice_amount'] = $drivercompleteRideFare;
            $driverData['total_driver_billing'] = $drivercompleteRideFare + $driverIncompleteRideFareIncluded;
            // $driverData['invoice_not_complete_amount'] = $driverIncompleteRideFare + $driverIncompleteRideFareIncluded;
            $driverData['invoice_not_complete_amount_but_included'] = $driverIncompleteRideFareIncluded;
            $driverData['invoice_not_complete_amount_but_not_included'] = $driverIncompleteRideFare;
            $driverData['total_amount'] =  $drivercompleteRideFare + $driverIncompleteRideFareIncluded + $driverIncompleteRideFare + $absentAmount;

            $driverData['total_ride_amount'] = $scheduleridePrice;
            $driverData['total_ride_completed'] = $completedRideCount;

            $driversData[] = $driverData;
        }

        return $driversData;
    }

    //****************** Function for generating  dates *******************************/
    public function generateDatesInRange($startDate, $endDate)
    {
        $dates = [];

        $start = Carbon::parse($startDate);
        $end = Carbon::parse($endDate);

        // Iterate through the range of dates and add them to the array
        while ($start->lte($end)) {
            $dates[] = $start->toDateString();
            $start->addDay();
        }

        return $dates;
    }



        //************************** All driver Billing ******************************/
    /**
     * @OA\Post(
     * path="/uc/api/allDriversPayrollHistory",
     * operationId="allDriversPayrollHistory",
     * tags={"Driver"},
     * summary="All driver billing pay roll history",
     *   security={ {"Bearer": {} }},
     * description="All driver billing",
     *      @OA\Response(
     *          response=201,
     *          description="The driver pay roll history",
     *          @OA\JsonContent()
     *       ),
     *      @OA\Response(
     *          response=200,
     *          description="The driver pay roll history",
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

    public function allDriversPayrollHistory(Request $request){

        try {

           
        // Step 1: Fetch all current year invoices
        $invoices = DB::table('invoices')
            ->whereYear('date', Carbon::now()->year)
            ->get();

        $drivercompleteRideFare = $invoices->sum('fare');

        // Step 2: Loop through all 12 months and calculate totals
        $monthlySummary = collect(range(1, 12))->map(function ($month) use ($invoices) {
            // Filter invoices for current month
            $monthlyInvoices = $invoices->filter(function ($invoice) use ($month) {
                return Carbon::parse($invoice->date)->month == $month;
            });

            // Total fare for the month
            $totalFare = $monthlyInvoices->sum('fare');

            // Fare of incomplete rides that are included
            $includedNotComplete = $monthlyInvoices->where('ride_status', 6)->where('is_included', 1)->sum('fare');
            $driverIncompleteRideFare = $monthlyInvoices->where('ride_status', 6)->where('is_included', 0)->sum('fare');

            return [
                'month' => date('M', mktime(0, 0, 0, $month, 1)),
                'total' => $totalFare,
                'invoice_not_complete_amount_but_included' => $includedNotComplete,
                'invoice_not_complete_amount_but_not_included' => $driverIncompleteRideFare,
            ];
        });

            return response()->json([
                'success' => true,
                'totalAmonut' => $drivercompleteRideFare,
                'data' =>$monthlySummary,
            ], 200);

        } catch (\Throwable $th) {
            return response()->json([
                'success' => false,
                'message' => $th->getMessage(),
                'line' => $th->getLine(),
                'file' => $th->getFile(),
            ], 500);
        }
    }





    //*************************** Change Payment status in the billing api **********************************/
    /**
     * @OA\Post(
     * path="/uc/api/changePaymentStatus",
     * operationId="changePaymentStatus",
     * tags={"Driver"},
     * summary="Change Payment Status",
     * security={ {"Bearer": {} }},
     * description="Change Payment Status",
     *     @OA\RequestBody(
     *         @OA\JsonContent(),
     *         @OA\MediaType(
     *            mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 type="object",
     *                 @OA\Property(
     *                     property="data",
     *                     type="object",
     *                     description="Your description here"
     *                 )
     *             )
     *         )
     *     ),
     *      @OA\Response(
     *          response=201,
     *          description="Payment status changed successfully.",
     *          @OA\JsonContent()
     *       ),
     *      @OA\Response(
     *          response=200,
     *          description="Payment status changed successfully.",
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
    public function changePaymentStatus(Request $request)
    {
        try {

            $request = json_decode(@$request->data);

            $paymentIds = $request->ids;
            $affectedRows = Invoice::whereIn('id', $paymentIds)->update(['status' => 2]);

            if ($affectedRows === 0) {

                return response()->json([
                    'success' => false,
                    'message' => 'No records were updated',
                ], 404);
            }

            return response()->json([
                'success' => true,
                'message' => 'Payment Status updated successfully for the selected records',
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'success' => false,
                'message' => $th->getMessage(),
            ], 500);
        }
    }

    //*************************** Pay to driver  api **********************************/

    /**
     * @OA\Post(
     * path="/uc/api/payToDrivers",
     * operationId="payToDrivers",
     * tags={"Driver"},
     * summary="Pay to drivers",
     * security={ {"Bearer": {} }},
     * description="Pay to drivers",
     *     @OA\RequestBody(
     *         @OA\JsonContent(),
     *         @OA\MediaType(
     *            mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 type="object",
     *                 @OA\Property(
     *                     property="data",
     *                     type="object",
     *                     description="Your description here"
     *                 )
     *             )
     *         )
     *     ),
     *      @OA\Response(
     *          response=201,
     *          description="Paid  successfully.",
     *          @OA\JsonContent()
     *       ),
     *      @OA\Response(
     *          response=200,
     *          description="Paid  successfully.",
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


    public function payToDrivers(Request $request)
    {
        try {
            $requestData = json_decode(@$request->data, true); // Convert to array

            // Define validation rules
            $rules = [
                'month' => 'required',
                'driver_ids' => 'required'

            ];

            // Create validator instance
            $validator = Validator::make($requestData, $rules);

            // Perform validation
            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422); // Return validation errors
            }

            $request = json_decode($request->data);

            $mon = $request->month;
            $driverIds = $request->driver_ids;

            $invoices = Invoice::whereMonth('date', $mon)
                ->where('is_included', 1)
                ->whereIn('driver_id', $driverIds)
                ->where('status', 1)
                ->get();

            $affectedRows = 0;
            if ($invoices) {
                foreach ($invoices as $invoice) {
                    $invoice->status = 2;
                    $invoice->save();
                    $affectedRows++;
                }
            }

            if ($affectedRows === 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'No invoices found for payment in the specified month.',
                ], 404);
            }

            return response()->json([
                'success' => true,
                'message' => 'Payment successful.',
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'success' => false,
                'message' => $th->getMessage(),
            ], 500);
        }
    }

    //*************************** Include in the invoice **********************************/
    /**
     * @OA\Post(
     * path="/uc/api/includeToInvoice",
     * operationId="includeToInvoice",
     * tags={"Driver"},
     * summary="Pay to drivers",
     * security={ {"Bearer": {} }},
     * description="Pay to drivers",
     *     @OA\RequestBody(
     *         @OA\JsonContent(),
     *         @OA\MediaType(
     *            mediaType="multipart/form-data",
     *             @OA\Schema(
     *               type="object",
     *               required={"invoice_ids"},
     *               @OA\Property(property="invoice_ids", type="text"),
     *                
     *            ),
     *         )
     *     ),
     *      @OA\Response(
     *          response=201,
     *          description="Added to invoice successfully.",
     *          @OA\JsonContent()
     *       ),
     *      @OA\Response(
     *          response=200,
     *          description="Added to invoice successfully.",
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
    public function includeToInvoice(Request $request)
    {
        try {

            // $request = json_decode(@$request->data);
            // echo '<pre>';print_r($request);die;
            $invoiceIds = $request->invoice_ids;
            $affectedRows = Invoice::where('id', $invoiceIds)->update(['is_included' => 1]);

            if ($affectedRows === 0) {

                return response()->json([
                    'success' => false,
                    'message' => '"Failed to include in the invoice',
                ], 404);
            }

            return response()->json([
                'success' => true,
                'message' => 'Added to invoice successfully',
            ], 200);
        } catch (\Throwable $th) {
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

    //****************************************************** Function to get weekly schedules ************/
    public function getWeeklyScheduleInfo($user_ids, $dates, $clientStaff, $shift_type_id)
    {

        $schedule_id_arr = array();

        $previous_date = Carbon::createFromFormat('Y-m-d', min($dates))->subDay()->format('Y-m-d');

        $schedules = Schedule::where(function ($query) use ($dates, $previous_date) {
            $query->where(function ($query) use ($dates) {
                $query->whereIn('date', $dates);

                $query->exists();
            });
            $query->orwhere(function ($query) {
                $query->where('is_repeat', 1);
                $query->where('end_date', '>', now());
            });
            $query->orwhere(function ($query) use ($dates) {
                $query->where('is_repeat', 1);
                $query->where('end_date', '>', min($dates));
                $query->where('end_date', '<', max($dates));
            });
            $query->orwhere(function ($query) use ($previous_date) {
                $query->where('date', $previous_date);
                $query->where('shift_finishes_next_day', 1);
            });
        });

        if ($clientStaff) {
            if ($clientStaff == 2) {
                $schedules = $schedules->whereHas('carers', function ($q) use ($user_ids) {

                    $q->whereIn('carer_id', $user_ids);
                });
                $schedules = $schedules->whereNotNull('driver_id');
                // $leaves = Leave::where('status', 'Approved')->whereIn('date', $dates)->whereIn('staff_id', $user_ids)->pluck('date', 'staff_id');
            } else {
                $schedules = $schedules->whereIn('driver_id', $user_ids);
            }
        }


        // Old code
        // $schedules = $schedules->with('shiftType')->with('driver')->with(['carers' => function ($q) {
        //     $q->with('user');  

        // }]);

        // New code

        $schedules = $schedules->with('shiftType')

            ->with('vehicle');
        // ->with(['carers' => function ($q) {
        // $q->with('user');
        // // ->groupBy('carer_id');
        // }]);




        if ($shift_type_id) {
            if ($shift_type_id != "all") {
                $schedules = $schedules->where('shift_type_id', $shift_type_id);
            }
        }

        $holidays = Holiday::whereIn('date', $dates)->pluck('date');

        $schedules = $schedules->get();


        // array_push($dates, $previous_date);

        foreach ($schedules as $schedule) {
            $exc_dates = array();
            if ($schedule->excluded_dates) {
                foreach (json_decode($schedule->excluded_dates) as $exc_date) {
                    array_push($exc_dates, Carbon::createFromFormat('Y-m-d', $exc_date));
                }
            }

            $current_date = Carbon::createFromFormat('Y-m-d', $schedule->date);
            $date = $current_date->copy()->format('Y-m-d');
            $day_name = $current_date->copy()->format('D');
            $checkArr = json_decode($schedule->occurs_on);
            if ((!empty($checkArr) && in_array(strtolower($day_name), json_decode($schedule->occurs_on)) && $schedule->reacurrance == 1) || $schedule->reacurrance == 0 || $schedule->reacurrance == null) {
                // if (!in_array($current_date->copy(), $public_dates)) {
                if (in_array($date, $dates)) {
                    if ($schedule->shift_type_id == 2) {
                        $schedule->type = "pick";
                        array_push($schedule_id_arr, $schedule->toArray());
                        if ($schedule->shift_finishes_next_day == 0) {
                            $schedule->type = "drop";
                            array_push($schedule_id_arr, $schedule->toArray());
                        }
                    } else if ($schedule->shift_type_id == 1) {
                        $schedule->type = "pick";
                        array_push($schedule_id_arr, $schedule->toArray());
                    } else if ($schedule->shift_type_id == 3) {
                        $schedule->type = "drop";
                        array_push($schedule_id_arr, $schedule->toArray());
                    }
                }
                // if ($date == $previous_date) {
                    if ($schedule->shift_finishes_next_day == 1) {
                        $schedule->type = "drop";
                        array_push($schedule_id_arr, $schedule->toArray());
                    }
                // }
            }
            // }
            if ($schedule->is_repeat == 1) {
                if ($schedule->reacurrance == 0) {
                    $current_date = Carbon::createFromFormat('Y-m-d', $schedule->date)->addDays($schedule->repeat_time);
                    while ($current_date < $schedule->end_date) {
                        $date = $current_date->format('Y-m-d');
                        // if (!in_array($current_date, $public_dates)) {
                        if (!in_array($current_date, $exc_dates)) {
                            $schedule->date = $current_date->copy()->format('Y-m-d');
                            if (in_array($date, $dates) && !$holidays->contains($date)) {
                                if ($schedule->shift_type_id == 2) {
                                    $schedule->type = "pick";
                                    array_push($schedule_id_arr, $schedule->toArray());
                                    if ($schedule->shift_finishes_next_day == 0) {
                                        $schedule->type = "drop";
                                        array_push($schedule_id_arr, $schedule->toArray());
                                    }
                                } else if ($schedule->shift_type_id == 1) {
                                    $schedule->type = "pick";
                                    array_push($schedule_id_arr, $schedule->toArray());
                                } else if ($schedule->shift_type_id == 3) {
                                  //  $schedule->type = "drop";
                                  //  array_push($schedule_id_arr, $schedule->toArray());
                                  if($schedule->previous_day_pick == 1){
                                    $holidays = Holiday::pluck('date'); 
    
                                       $previous_date = $current_date->copy()->subDay()->format('Y-m-d');  
    
                                        $holidays = $holidays->map(function($holiday) {
                                        return Carbon::parse($holiday)->format('Y-m-d');  // Convert to string if it's not
                                        });
                                        if (!$holidays->contains($previous_date)) {
                                            $schedule->type = "drop";
                                            array_push($schedule_id_arr, $schedule->toArray());
                                        } 
                                        else {
                                            info('Skipping "drop" schedule for ' . $current_date->format('Y-m-d') . ' because the previous date (' . $previous_date . ') is a holiday.');
                                        }
                                    }
                                    else{
                                        $schedule->type = "drop";
                                            array_push($schedule_id_arr, $schedule->toArray());
                                    }
                                }

                                if ($schedule->shift_finishes_next_day == 1) {
                                    $schedule->type = "drop";
                                    array_push($schedule_id_arr, $schedule->toArray());
                                }
                            } else if ($date == $previous_date) {
                                if ($schedule->shift_finishes_next_day == 1) {
                                    $schedule->type = "drop";
                                    array_push($schedule_id_arr, $schedule->toArray());
                                }
                            }
                        }
                        // }
                        $current_date = $current_date->addDays($schedule->repeat_time);
                    }
                } else if ($schedule->reacurrance == 1) {
                    $current_date = Carbon::createFromFormat('Y-m-d', $schedule->date);
                    $scheduleDate = $current_date->copy();
                    while ($current_date->copy()->startOfWeek() < $schedule->end_date) {
                        $current_date = $current_date->copy()->startOfWeek() < $scheduleDate ? $scheduleDate->addDay() : $current_date->copy()->startOfWeek();
                        $endofthisweek = $current_date->copy()->endOfWeek();
                        if ($this->dates_in_range($current_date->copy()->format('Y-m-d'), $endofthisweek->copy()->format('Y-m-d'), $dates) == true) {
                            while ($current_date->copy() < $endofthisweek & $current_date->copy() < $schedule->end_date) {
                                $date = $current_date->format('Y-m-d');
                                $day_name = $current_date->copy()->format('D');
                                // if (!in_array($current_date, $public_dates)) {
                                if (!in_array($current_date, $exc_dates)&& !$holidays->contains($date)) {
                                    $schedule->date = $current_date->copy()->format('Y-m-d');
                                    if (in_array($date, $dates) & in_array(strtolower($day_name), json_decode($schedule->occurs_on))) {
                                        if ($schedule->shift_type_id == 2) {
                                            $schedule->type = "pick";
                                            array_push($schedule_id_arr, $schedule->toArray());
                                            if ($schedule->shift_finishes_next_day == 0) {
                                                $schedule->type = "drop";
                                                array_push($schedule_id_arr, $schedule->toArray());
                                            }
                                        } else if ($schedule->shift_type_id == 1) {
                                            $schedule->type = "pick";
                                            array_push($schedule_id_arr, $schedule->toArray());
                                        } else if ($schedule->shift_type_id == 3) {
                                          //  $schedule->type = "drop";
                                           // array_push($schedule_id_arr, $schedule->toArray());
                                           if($schedule->previous_day_pick == 1){
                                            $holidays = Holiday::pluck('date'); 
            
                                               $previous_date = $current_date->copy()->subDay()->format('Y-m-d');  
            
                                                $holidays = $holidays->map(function($holiday) {
                                                return Carbon::parse($holiday)->format('Y-m-d');  // Convert to string if it's not
                                                });
                                                if (!$holidays->contains($previous_date)) {
                                                    $schedule->type = "drop";
                                                    array_push($schedule_id_arr, $schedule->toArray());
                                                } 
                                                else {
                                                    info('Skipping "drop" schedule for ' . $current_date->format('Y-m-d') . ' because the previous date (' . $previous_date . ') is a holiday.');
                                                }
                                            }
                                            else{
                                                $schedule->type = "drop";
                                                    array_push($schedule_id_arr, $schedule->toArray());
                                            }
                                        }

                                        if ($schedule->shift_finishes_next_day == 1) {
                                            $schedule->type = "drop";
                                            array_push($schedule_id_arr, $schedule->toArray());
                                        }
                                    } 
                                    else if ($date == $previous_date & in_array(strtolower($day_name), json_decode($schedule->occurs_on))) {
                                        if ($schedule->shift_finishes_next_day == 1) {
                                            $schedule->type = "drop";
                                            array_push($schedule_id_arr, $schedule->toArray());
                                        }
                                    }
                                    
                                }
                                // }
                                $current_date = $current_date->copy()->addDay();
                            }
                            $current_date = $current_date->copy()->subDay();
                        }
                        $current_date = $current_date->copy()->addWeeks($schedule->repeat_time);
                    }
                } else if ($schedule->reacurrance == 2) {
                    $current_date = Carbon::createFromFormat('Y-m-d', $schedule->date);
                    $scheduleDate = $current_date->copy();
                    while ($current_date->copy()->startOfMonth() < $schedule->end_date) {
                        $current_date = $current_date->copy()->startOfMonth() < $scheduleDate ? $scheduleDate->addDay() : $current_date->copy()->startOfMonth();
                        // $current_date = $current_date->copy()->addDays($schedule->occurs_on);
                        $endofthismonth = $current_date->copy()->endOfMonth();
                        if ($this->dates_in_range($current_date->copy()->format('Y-m-d'), $endofthismonth->copy()->format('Y-m-d'), $dates) == true) {
                            while ($current_date->copy() < $endofthismonth & $current_date->copy() < $schedule->end_date) {
                                $date = $current_date->format('Y-m-d');
                                // if (!in_array($current_date, $public_dates)) {
                                if (!in_array($current_date, $exc_dates) && !$holidays->contains($date)) {
                                    $schedule->date = $current_date->copy()->format('Y-m-d');
                                    if (in_array($date, $dates)) {
                                        if ($schedule->shift_type_id == 2) {
                                            $schedule->type = "pick";
                                            array_push($schedule_id_arr, $schedule->toArray());
                                            if ($schedule->shift_finishes_next_day == 0) {
                                                $schedule->type = "drop";
                                                array_push($schedule_id_arr, $schedule->toArray());
                                            }
                                        } else if ($schedule->shift_type_id == 1) {
                                            $schedule->type = "pick";
                                            array_push($schedule_id_arr, $schedule->toArray());
                                        } else if ($schedule->shift_type_id == 3) {
                                          //  $schedule->type = "drop";
                                         //   array_push($schedule_id_arr, $schedule->toArray());
                                         if($schedule->previous_day_pick == 1){
                                            $holidays = Holiday::pluck('date'); 
            
                                               $previous_date = $current_date->copy()->subDay()->format('Y-m-d');  
            
                                                $holidays = $holidays->map(function($holiday) {
                                                return Carbon::parse($holiday)->format('Y-m-d');  // Convert to string if it's not
                                                });
                                                if (!$holidays->contains($previous_date)) {
                                                    $schedule->type = "drop";
                                                    array_push($schedule_id_arr, $schedule->toArray());
                                                } 
                                                else {
                                                    info('Skipping "drop" schedule for ' . $current_date->format('Y-m-d') . ' because the previous date (' . $previous_date . ') is a holiday.');
                                                }
                                            }
                                            else{
                                                $schedule->type = "drop";
                                                    array_push($schedule_id_arr, $schedule->toArray());
                                            }
                                        }
                                    } else if ($date == $previous_date) {
                                        if ($schedule->shift_finishes_next_day == 1) {
                                            $schedule->type = "drop";
                                            array_push($schedule_id_arr, $schedule->toArray());
                                        }
                                    }
                                }
                                // }
                                $current_date = $current_date->copy()->addDays($schedule->occurs_on);
                            }
                            $current_date = $current_date->copy()->subDays($schedule->occurs_on);
                        }
                        $current_date = $current_date->copy()->addMonths($schedule->repeat_time);
                    }
                }
            }
        }

        $this->data['schedules'] = collect($schedule_id_arr);


        return $this->data['schedules'];
    }

    function dates_in_range(string $start_date, string $end_date, array $dates): bool
    {
        foreach ($dates as $date) {
            if ($date >= $start_date & $date <= $end_date) {
                return true;
            }
        }
        return false;
    }

    



}
