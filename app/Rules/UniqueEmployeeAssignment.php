<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;
use DB;
use App\Models\SubUser;

class UniqueEmployeeAssignment implements Rule
{
    /**
     * Create a new rule instance.
     *
     * @return void
     */

    protected $teamId;
    public $message;

    public function __construct($teamId = null)
    {
        $this->teamId = $teamId;
    }

    /**
     * Determine if the validation rule passes.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @return bool
     */
    public function passes($attribute, $value)
    {

        $employeeIds = is_array($value) ? $value : [$value];

        foreach ($employeeIds as $key => $value) {
            $query = DB::table('employee_team_managers')->where('employee_id', $value);

            if (!is_null($this->teamId)) {
                  $query->where('team_manager_id', '!=', $this->teamId);
            }

            if ($query->exists()) {
                $user = SubUser::find($value);
                $this->message = "$user->first_name $user->last_name is already assigned as a team manager.";
                return false;
            }

            if (DB::table('hrms_teams')->where('team_leader', $value)->where('id', '!=', $this->teamId)->exists()) {
                $user = SubUser::find($value);
                $this->message = "$user->first_name $user->last_name is already a team leader of another team.";
                return false;
            }

            if (DB::table('hrms_team_members')->where('member_id', $value)->where('hrms_team_id', '!=', $this->teamId)->exists()) {
                $user = SubUser::find($value);
                $this->message = "$user->first_name $user->last_name is already a member of another team.";
                return false;
            }
        }

        return true;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return $this->message ?? 'The validation error.';
    }
}
