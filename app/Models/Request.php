<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Request extends Model
{
    use HasFactory;

    protected $fillable = [
        'team_id',
        'type',
        'content',
        'status',
        'volunteer_id',
    ];

    public function team(): BelongsTo
    {
        return $this->belongsTo(VolunteerTeam::class, 'team_id');
    }

    public function volunteer(): BelongsTo
    {
        return $this->belongsTo(Volunteer::class);
    }



} 