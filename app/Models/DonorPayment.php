<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\Campaign;
use App\Models\Benefactor;
use App\Models\Employee;

class DonorPayment extends Model
{
    use HasFactory;

    protected $fillable = [
        'benefactor_id',
        'campaign_id',
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

    public function campaign(): BelongsTo
    {
        return $this->belongsTo(Campaign::class);
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }
} 