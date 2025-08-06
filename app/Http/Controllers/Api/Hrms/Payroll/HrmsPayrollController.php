<?php

namespace App\Http\Controllers\Api\Hrms\Payroll;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\SubUser;
use App\Models\HrmsPayroll;
use Carbon\Carbon;
use App\Http\Resources\Employees\EmployeeCollection;

class HrmsPayrollController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */

    /**
     * @OA\post(
     * path="/uc/api/payrolls/index",
     * operationId="getpayrolls",
     * tags={"HRMS Employee Payrolls"},
     * summary="Get payrolls Request",
     *   security={ {"Bearer": {} }},
     * description="Get payrolls Request",
     *      @OA\RequestBody(
     *         @OA\JsonContent(),
     *         @OA\MediaType(
     *            mediaType="multipart/form-data",
     *            @OA\Schema(
     *               type="object",
     *                 @OA\Property(property="date", type="date"),
     *                 @OA\Property(property="search", type="string", description="Searching by employee name or employee Id"),
     *            ),
     *        ),
     *    ),
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

    public function index(Request $request)
    {
          try {

              $month = $request->date ? Carbon::parse($request->date)->format('m') : now()->format('m');
              $days_in_month = Carbon::parse($request->date)->daysInMonth;
              $search = $request->search;

                $users = SubUser::select('id', 'first_name', 'last_name', 'email', 'employement_type', 'unique_id')
                                    ->FilterBySearch($search)
                                    ->with('payrolls')
                                    ->paginate(SubUser::PAGINATE);



             return $this->successResponse(
                 new EmployeeCollection($users),
                 "users List"
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
    public function store($request)
    {

             $user_id = $request->user_id;
             $date = Carbon::parse($request->date)->toDateString();
             $month = Carbon::parse($request->date)->month;
             $year = Carbon::parse($request->date)->year;
             $days_in_month = Carbon::parse($request->date)->daysInMonth;

             $existDateEntry = HrmsPayroll:: where('user_id', $user_id)
                                             ->whereDate('date', $date)->first();
             if ($existDateEntry) {
                return;
             }

             $existThisMonthEntry = HrmsPayroll:: where('user_id', $user_id)
                                                 ->whereMonth('date', $month)
                                                 ->whereYear('date', $year)->first();

             if ($existThisMonthEntry) {

                $existThisMonthEntry->update([
                    'count_of_persent' => $existThisMonthEntry->count_of_persent +1,
                    'date' => $date
                ]);
             }else {
                HrmsPayroll:: create([
                    'user_id' => $user_id,
                    'count_of_persent' => 1,
                    'total_paid_days' => $days_in_month,
                    'date' => $date,
                    'status' => 3        // pending
                ]);
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

    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */


    public function update(Request $request)
    {

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
     * path="/uc/api/payrolls/approvedStatus",
     * operationId="approvedStatus",
     * tags={"HRMS Employee Payrolls"},
     * summary="approvedStatus Request",
     *   security={ {"Bearer": {} }},
     * description="approvedStatus payrolls Request",
     *      @OA\RequestBody(
     *         @OA\JsonContent(),
     *         @OA\MediaType(
     *            mediaType="multipart/form-data",
     *            @OA\Schema(
     *               type="object",
     *                 @OA\Property(property="user_id", type="integer"),
     *                 @OA\Property(property="date", type="date"),
     *                 @OA\Property(property="status", type="integer"),
     *            ),
     *        ),
     *    ),
     *      @OA\Response(
     *          response=201,
     *          description=" Status changed Successfully",
     *          @OA\JsonContent()
     *       ),
     *      @OA\Response(
     *          response=200,
     *          description="Payrolls Chenged Successfully",
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

     public function approvedStatus(Request $request){
              try {
                   $request->validate([
                    'user_id' => 'required',
                    'date' => 'required|date',
                    'status' => 'required|integer|in:1,2,3'
                   ]);

                   $user_id = $request->user_id;
                   $month = Carbon::parse($request->date)->month;
                   $yaer = Carbon::parse($request->date)->year;
                   $status = $request->status;

                   $getPayroll = HrmsPayroll::where('user_id', $user_id)->whereMonth('date', $month)->whereYear('date', $yaer)->first();

                   if ($getPayroll) {
                       $getPayroll->status = $status;
                       $getPayroll->save();

                       return $this->successResponse(
                            [],
                            "Approved Successfully"
                       );
                   }else {
                      return $this->errorResponse("the given data is not found");
                   }


              } catch (\Throwable $th) {
                  return $this->errorResponse($th->getMessage());
              }
     }
}
