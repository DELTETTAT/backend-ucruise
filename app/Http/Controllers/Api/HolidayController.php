<?php

namespace App\Http\Controllers\Api;

use Carbon\Carbon;
use App\Models\Holiday;

class HolidayController extends Controller
{
    //************************* Holidays api ************************************ */

    /**
     * @OA\Get(
     * path="/uc/api/upcoming-holidays",
     * operationId="upcomingHolidays",
     * tags={"Home Data"},
     * summary="Upcomming holidays",
     *   security={ {"Bearer": {} }},
     * description="Upcomming holidays",
     *      @OA\Response(
     *          response=201,
     *          description="List holidays",
     *          @OA\JsonContent()
     *       ),
     *      @OA\Response(
     *          response=200,
     *          description="List holidays",
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

    public function upcomingHolidays()
    {
        try {
            $currentDate = Carbon::now();


            $upcomingHolidays = Holiday::whereYear('date', '=', date('Y'))
                ->where('status', 1)
                ->orderBy('date')
                
                ->get();
                $this->data['upcomingHoliday'] = $upcomingHolidays;
                return response()->json(['success' => true, "data" => $this->data], 200);
            
        } catch (\Throwable $th) {
            return response()->json([
                'success' => false,
                'message' => $th->getMessage()
            ], 500);
        }
    }
}
