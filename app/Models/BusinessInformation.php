<?php

namespace app\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BusinessInformation extends Model
{
    use HasFactory;

    protected $table ="business_informations";

    protected $guarded = []; 
    // protected $fillable = [
    //     'volunteer_team_id',
    //     'team_name',
    //     'license_number',
    //     'logo_image',
    //     'phone',
    //     'bank_account_number',
    // ];

    public function volunteerTeam()
    {
        return $this->belongsTo(VolunteerTeam::class);
    }
} 