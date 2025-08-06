<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Tymon\JWTAuth\Contracts\JWTSubject;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use App\Models\Role;
use App\Models\ScheduleClient;
use App\Models\Language;
use DB;
use Illuminate\Database\Eloquent\SoftDeletes;

class User extends Authenticatable implements JWTSubject
{
    use HasApiTokens, HasFactory, Notifiable, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'first_name',
        'user_type',
        'cab_facility',
        'latitude',
        'longitude',
    ];

    public const PAGINATE = 15;

    protected $appends = ['staff_language','schedule_date'];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'database_password',
        'database_username',
        'database_path'
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
            if($lngName){
                return $lngName->language_name;
            }
        }
    }

    function clients() {

        return $this->hasMany(ScheduleClient::class, 'client_id', 'id');
    }

    public function getScheduleDateAttribute()
    {
        // $scheduleId = ScheduleClient::where('client_id',$this->id)->pluck('schedule_id')->toArray();
        // $datesArray = Schedule::whereIn('id',$scheduleId)->pluck('date')->toArray();
        // return $datesArray;
    }
    public function userSubscription()
    {
        return $this->hasOne(UserSubscription::class);
    }

    public function teamMember(){
        return $this->hasMany(HrmsTeamMember::class, 'member_id', 'id');
    }


    public function hasHrmsPermission($permission) {
        // checking for admin role
        // foreach ($this->roles as $role) {
        //     if ($role->name === 'admin') {
        //         return true;
        //     }
        // }

        [$module, $action] = explode('.', $permission);

        foreach ($this->roles as $role) {
            foreach ($role->hrms_permissions as $perm) {
                if ($perm->name === $module) {
                    if ($action === 'view' && $perm->pivot->can_view) return true;
                    if ($action === 'edit' && $perm->pivot->can_edit) return true;
                    if ($action === 'access' && $perm->pivot->can_access) return true;
                }
            }
        }
        return false;
    }


    public function hrms_roles(){
         $this->belongsToMany(Role::class);
    }


    public function roleSubUser(){
        return $this->hasMany(DB::raw('(SELECT * FROM role_sub_user) as role_sub_user'), 'user_id');
    }


    public function schedulesAsDriver(){
        return $this->hasMany(Schedule::class, 'driver_id');
    }

    public function scheduleCarers()
    {
        return $this->hasMany(ScheduleCarer::class, 'carer_id');
    }


    public function schedules()
    {
        return $this->hasManyThrough(Schedule::class, ScheduleCarer::class, 'carer_id', 'id', 'id', 'schedule_id');
    }

    public function address()
    {
        return $this->hasOne(SubUserAddresse::class, 'sub_user_id', 'id');
    }

    public function dailyschedules()
    {
        return $this->hasManyThrough(DailySchedule::class, DailyScheduleCarer::class, 'carer_id', 'id', 'id', 'schedule_id');
    }



}
