<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OTP extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'email',
        'expire_time'
    ];

    protected $casts = [
        'expire_time' => 'datetime'
    ];

    // Scopes
    public function scopeByEmail($query, $email)
    {
        return $query->where('email', $email);
    }

    public function scopeValid($query)
    {
        return $query->where('expire_time', '>', now());
    }

    public function scopeExpired($query)
    {
        return $query->where('expire_time', '<=', now());
    }

    // Methods
    public function generateCode($length = 6)
    {
        $this->code = str_pad(rand(0, pow(10, $length) - 1), $length, '0', STR_PAD_LEFT);
        $this->expire_time = now()->addMinutes(5); // Default 5 minutes expiration
        $this->save();
    }

    public function isValid()
    {
        return $this->expire_time > now();
    }

    public function isExpired()
    {
        return $this->expire_time <= now();
    }

    public function verify($code)
    {
        if (!$this->isValid()) {
            return false;
        }

        return $this->code === $code;
    }

    public function getRemainingTime()
    {
        if ($this->isExpired()) {
            return 0;
        }

        return now()->diffInSeconds($this->expire_time);
    }
} 