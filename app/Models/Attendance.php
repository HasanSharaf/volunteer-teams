<?php

namespace app\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Attendance extends Model
{
    use HasFactory;

    protected $fillable = [
        'attendable_type',
        'attendable_id',
        'campaign_id',
        'check_in',
        'check_out',
        'status',
        'notes',
        'verified_by',
        'verified_at',
        'location',
        'device_info',
        'metadata'
    ];

    // Relationships
    public function attendable()
    {
        return $this->morphTo();
    }

    public function campaign(): BelongsTo
    {
        return $this->belongsTo(Campaign::class);
    }

    public function verifier(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'verified_by');
    }

}

   