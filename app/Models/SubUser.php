<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\SoftDeletes;
use Tymon\JWTAuth\Contracts\JWTSubject;
use Illuminate\Database\Eloquent\Builder;
use App\Models\HrmsPayroll;
use App\Models\TeamManager;
use App\Models\UserInfo;
use App\Models\SubUserAddresse;
use App\Models\Resignation;
use App\Models\EmployeeSeparation;
use App\Models\HrmsTimeAndShift;

class SubUser extends Authenticatable implements JWTSubject
{
    use HasApiTokens, HasFactory, Notifiable, SoftDeletes;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'sub_users';

    protected $guard = "staff";

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'user_type',
        'id',
        'referral_code',
        'expires_at',
    ];


    public const PAGINATE = 15;
    protected $appends = ['staff_language'];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'database_password',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    public function getJWTIdentifier()
    {
        return $this->getKey(); // This should return the unique identifier for the user (usually the 'id' field).
    }
    public function getJWTCustomClaims()
    {
        // You can add any custom claims to the JWT token here.
        return [];
    }

    public function hasRole($role)
    {
        return null !== $this->roles()->where('name', $role)->first();
    }

    public function roles()
    {
        return $this->belongsToMany(Role::class);
    }


    public function permissions()
    {
        $permissionList = [];

        foreach ($this->roles as $role) {
            foreach ($role->permissions as $permission) {
                if (!in_array($permission->name, $permissionList)) {
                    $permissionList[] = $permission->name;
                }
            }
        }

        return $permissionList;
    }

    public function hasPermission($permission_name)
    {
        foreach ($this->roles as $role) {
            foreach ($role->permissions as $permission) {
                if ($permission->name == $permission_name) {
                    return true;
                }
            }
        }
        return false;
    }


    public function getStaffLanguageAttribute()
    {
        $array = explode(", ", $this->language_spoken);
        foreach ($array as $lng) {
            $lngName =  Language::where('code', $lng)->first();
            if ($lngName) {
                return $lngName->language_name;
            }
        }
    }
    public function addresses()
    {
        return $this->hasMany(SubUserAddresse::class);
    }
    public function vehicle()
    {
        return $this->hasOne(Vehicle::class, 'driver_id', 'id');
    }
    function pricebook()
    {
        return $this->belongsTo(PriceBook::class);
    }

    public function timeSheet(){
        return $this->hasMany(EmployeeAttendance::class, 'user_id', 'id');
    }

    public function payrolls(){
        return $this->hasMany(HrmsPayroll::class, 'user_id', 'id');
    }

    public function leave(){
        return $this->hasMany(Leave::class, 'staff_id', 'id');
    }


     // Scope for filtering by department
     public function scopeFilterByDepartment(Builder $query, $department)
     {
         if (!empty($department)) {
             return $query->where('employement_type', 'LIKE', "%{$department}%");
         }
         return $query;
     }

     // Scope for filtering by search
     public function scopeFilterBySearch(Builder $query, $search)
     {
         if (!empty($search)) {
             return $query->where('first_name', 'LIKE', "%{$search}%")
                            ->orWhere('last_name', 'like', '%' .$search. '%')
                            ->orWhere('email', 'like', '%' .$search. '%')
                            ->orWhere('unique_id', 'like', '%' .$search. '%');
         }
         return $query;
     }

    public function schedulesAsDriver(){
        return $this->hasMany(Schedule::class, 'driver_id');
    }

    public function dailyschedulesAsDriver(){
        return $this->hasMany(DailySchedule::class, 'driver_id');
    }


    public function scheduleCarers()
    {
        return $this->hasMany(ScheduleCarer::class, 'carer_id');
    }


    public function teamManagers()
    {
        return $this->belongsToMany(TeamManager::class, 'employee_team_managers', 'employee_id', 'team_manager_id');
    }


    public function hrmsroles()
    {
        return $this->belongsToMany(HrmsRole::class, 'hrms_employee_roles', 'employee_id', 'role_id');
    }


    public function hrmsTeam(){
        return $this->hasOne(HrmsTeamMember::class, 'member_id', 'id');
    }

    public function userInfo(){
         return $this->hasOne(UserInfo::class, 'user_id', 'id');
    }

    public function subUserAddress(){
         return $this->hasOne(SubUserAddresse::class, 'sub_user_id', 'id');
    }

    public function invoices(){
        return $this->hasMany(Invoice::class, 'driver_id');
    }

    public function referredApplicants()    {
        return $this->hasMany(NewApplicant::class, 'referral_code', 'id');
    }

    public function resignation()
    {
        return $this->hasOne(Resignation::class, 'user_id', 'id');
    }

    public function employeesUnderOfManagerRelation(){
             return $this->hasMany(\App\Models\EmployeesUnderOfManager::class, 'employee_id', 'id');
    }

    public function statusUpdateReason(){
         return $this->hasMany(EmployeeSeparation::class, 'user_id', 'id');
    }

    public function employeeShift(){
         return $this->belongsTo(HrmsTimeAndShift::class, 'employee_shift', 'shift_name');
    }

    public function salary()  
    {
        return $this->hasMany(EmployeeSalary::class, 'employee_id');
    }

    

}
