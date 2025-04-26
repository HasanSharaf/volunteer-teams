<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasMany;

class VolunteerTeam extends Model
{
    use HasFactory;

    protected $fillable = [
        'full_name',
        'national_number',
        'phone',
        'gender',
        'nationality',
        'birth_date',
        'image',
        'email',
        'password',
        'status'
    ];

    public function businessInformation(): HasOne
    {
        return $this->hasOne(BusinessInformation::class,'team_id');
    }

    public function financial(): HasOne
    {
        return $this->hasOne(Financial::class);
    }

    public function employees(): HasMany
    {
        return $this->hasMany(Employee::class);
    }

    public function campaigns(): HasMany
    {
        return $this->hasMany(Campaign::class);
    }

    public function requests(): HasMany
    {
        return $this->hasMany(Request::class);
    }

    public function donorPayments(): HasMany
    {
        return $this->hasMany(DonorPayment::class);
    }

    public function contracts(): HasMany
    {
        return $this->hasMany(Contract::class);
    }
} 