<?php

namespace App\Http\Controllers\Api\CompanyCalender;

use App\Http\Controllers\Controller;
use App\Models\CompanyCalender;
use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Resources\CompanyCalender\CompanyCalenderResource;
use App\Http\Resources\CompanyCalender\CompanyCalenderCollection;
use App\Http\Requests\StoreCompanyCalenderRequest;
use App\Models\Holiday;

class CompanyCalenderController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
{
    try {
        // Get the company calendar details with pagination
        $getcalenderDetails = CompanyCalender::paginate(CompanyCalender::PAGINATE);

        // Get the holiday details, ordered by ID in descending order
        $this->data['holiday'] = Holiday::orderBy('id', 'DESC')->get();

        // Return the response with both the calendar events and holiday data
        return $this->successResponse(
            [
                'calendar_events' => new CompanyCalenderCollection($getcalenderDetails),
                'holidays' => $this->data['holiday']
            ],
            'Calendar event list with holidays'
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
    public function store(StoreCompanyCalenderRequest $request)
    {
        try {
            $user_id = auth('sanctum')->user()->id;
            $user = User::find($user_id);
    
            if ($user && $user->hasRole('admin')) {
                // Get validated data
                $validated = $request->validated();
                $existingHoliday = Holiday::where('date', $validated['date'])->first();

               if ($existingHoliday) {
                return $this->errorResponse('Public holiday exist for this date', 400);
                }
                // Create the company calendar using the validated data
                $companycalender = CompanyCalender::create($validated);
    
                return $this->successResponse(
                    new CompanyCalenderResource($companycalender),
                    'Company calendar created successfully'
                );
            } else {
                return $this->errorResponse('Unauthorized user', 403);
            }
        } catch (\Throwable $th) {
            return $this->errorResponse($th->getMessage(), 500);
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
    public function edit($EventId)
    {
        try {
            $getcalenderDetails = CompanyCalender::find($EventId);
            if (isset($getcalenderDetails)) {
                return $this->successResponse(
                    new CompanyCalenderResource($getcalenderDetails),
                    'calender details retrieved successfully'
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
    
     public function update(StoreCompanyCalenderRequest $request, $EventId)
     {
         try {
             $getcalenderDetails = CompanyCalender::find($EventId);
             if (isset($getcalenderDetails)) {
                 $validated = $request->validated();
                  $getcalenderDetails->update($validated);
                 return $this->successResponse(
                     new CompanyCalenderResource($getcalenderDetails),
                     'calender  updated Successfully'
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
    public function destroy($EventId)
    {
        try {
            $getcalenderDetails = CompanyCalender::find($EventId);
            if (isset($getcalenderDetails)) {
                $getcalenderDetails->delete();
                return $this->successResponse(
                    [],
                    'calender event Removed Sucessfully'
                );
            } else {
                return $this->validationErrorResponse('the given data is not found');
            }
        } catch (Exception $ex) {
            return $this->errorResponse($ex->getMessage());
        }
    }
}
