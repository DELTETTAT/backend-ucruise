<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\NewApplicantCabAddress;
use App\Models\Designation;
use App\Models\ApplicantOfferedHistory;
use Illuminate\Database\Eloquent\SoftDeletes;

class NewApplicant extends Model
{
    use HasFactory;
    use SoftDeletes; // Enable soft deletes

    protected $dates = ['deleted_at'];

    protected $table = 'new_applicant';
    protected $fillable = [
        'first_name',
        'last_name',
        'email',
        'phone',
        'gender',
        'dob',
        'designation_id',
        'role',
        'linkedin_url',
        'upload_resume',
        'cover_letter',
        'skills',
        'experience',
        'typing_speed',
        'notice_period',
        'is_notice',
        'expected_date_of_join',
        'working_nightshift',
        'cab_facility',
        'referral_name',
        'employee_code',
        'current_salary',
        'salary_expectation',
        'why_do_you_want_to_join_unify',
        'how_do_you_come_to_know_about_unify',
        'weakness',
        'strength',
        'address',
        'city',
        'state',
        'country',
        'stages',
        'is_rejected',
        'is_feature_reference',
        'blood_group',
        'profile_image',
        'is_employee',
        'marital_status',
        'is_feature_reference',
        'reason_history',
        'exists_history',
        'quiz_status',
        'manager_id',
        'is_accept',
        'interview_mode',
        'referral_code',
        'sent_mail',

    ];

    protected $casts = [
        'skills' => 'array',
        'reason_history' => 'json',
        'exists_history' => 'json',
    ];


  public const PAGINATE = 15;

  public function CandidateAddress(){
     return $this->hasOne(NewApplicantCabAddress::class, 'new_applicant_id', 'id');
  }

  public function Designation()
  {
      return $this->belongsTo(Designation::class, 'designation_id', 'id');
  }
  public function manager()
  {
      return $this->belongsTo(TeamManager::class, 'manager_id', 'id');
  }

    public function HrmsEmployeeEmail(){
        return $this->hasMany(HrmsEmployeeEmail::class, 'user_id','id');
    }

    public function referrer(){
        return $this->belongsTo(Subuser::class, 'referral_code', 'id');
    }

    public function OfferedHistory()
    {
        return $this->hasMany(ApplicantOfferedHistory::class, 'applicant_id', 'id');
    }

}
