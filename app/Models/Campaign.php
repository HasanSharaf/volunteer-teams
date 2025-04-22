<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Campaign extends Model
{
    use HasFactory;

    protected $fillable = [
        'campaign_name',
        'number_of_volunteer',
        'cost',
        'address',
        'from',
        'to',
        'points',
        'status',
        'specialization_id',
        'campaign_type_id',
        'team_id',
        'employee_id',
        'name',
        'description',
        'start_date',
        'end_date',
        'location',
        'target_amount',
        'current_amount',
        'image',
    ];

    // protected $casts = [
    //     'from' => 'datetime',
    //     'to' => 'datetime',
    //     'cost' => 'decimal:2',
    // ];

    public function specialization(): BelongsTo
    {
        return $this->belongsTo(Specialization::class);
    }

    public function campaignType(): BelongsTo
    {
        return $this->belongsTo(CampaignType::class, 'campaign_type_id');
    }

    public function team(): BelongsTo
    {
        return $this->belongsTo(VolunteerTeam::class, 'team_id');
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function volunteers(): BelongsToMany
    {
        return $this->belongsToMany(Volunteer::class, 'campaign_volunteers');
    }

    public function points(): HasMany
    {
        return $this->hasMany(Point::class);
    }

    public function attendances(): HasMany
    {
        return $this->hasMany(Attendance::class);
    }

    public function requests(): HasMany
    {
        return $this->hasMany(Request::class);
    }

    public function donorPayments(): HasMany
    {
        return $this->hasMany(DonorPayment::class);
    }

    public function financials(): HasMany
    {
        return $this->hasMany(Financial::class);
    }
} 