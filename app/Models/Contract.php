<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Contract extends Model
{
    use HasFactory;

    protected $fillable = [
        'date',
        'contract',
        'image',
        'company_name',
        'team_id',
    ];

    public function team()
    {
        return $this->belongsTo(VolunteerTeam::class, 'team_id');
    }
} 