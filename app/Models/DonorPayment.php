<?php

namespace app\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DonorPayment extends Model
{
    use HasFactory;

    protected $fillable = [
        'benefactor_id',
        'team_id',
        'employee_id',
        'amount',
        'date_of_payment',
        'type',
        'process_number',
        'status',
        'image',
    ];

    public function benefactor(): BelongsTo
    {
        return $this->belongsTo(Benefactor::class);
    }

    public function team(): BelongsTo
    {
        return $this->belongsTo(VolunteerTeam::class, 'team_id');
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }
} 