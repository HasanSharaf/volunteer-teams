<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VolunteerTeam extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'full_name',
        'team_name',
        'license_number',
        'phone',
        'bank_account_number',
        'email',
        'password',
        'government_id',
        'address'
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    public function government(): BelongsTo
    {
        return $this->belongsTo(Government::class);
    }

    public function businessInformation(): HasOne
    {
        return $this->hasOne(BusinessInformation::class, 'team_id');
    }

    public function employees(): HasMany
    {
        return $this->hasMany(Employee::class, 'team_id');
    }

    public function volunteers(): HasMany
    {
        return $this->hasMany(Volunteer::class, 'team_id');
    }

    public function campaigns(): HasMany
    {
        return $this->hasMany(Campaign::class, 'team_id');
    }

    public function requests(): HasMany
    {
        return $this->hasMany(Request::class, 'team_id');
    }

    public function donorPayments(): HasMany
    {
        return $this->hasMany(DonorPayment::class, 'team_id');
    }

    public function financial(): HasOne
    {
        return $this->hasOne(Financial::class, 'team_id');
    }

    public function contracts(): HasMany
    {
        return $this->hasMany(Contract::class, 'team_id');
    }
} 