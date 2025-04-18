<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\OTP;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class OTPController extends Controller
{
    public function sendOTP(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
        ]);

        $code = Str::random(6);
        $expireTime = now()->addMinutes(5);

        OTP::create([
            'code' => $code,
            'email' => $request->email,
            'expire_time' => $expireTime,
        ]);

        // Send email with OTP
        Mail::raw("Your OTP code is: $code", function ($message) use ($request) {
            $message->to($request->email)
                   ->subject('Your OTP Code');
        });

        return response()->json(['message' => 'OTP sent successfully']);
    }

    public function verifyOTP(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'code' => 'required|string',
        ]);

        $otp = OTP::where('email', $request->email)
                  ->where('code', $request->code)
                  ->where('expire_time', '>', now())
                  ->first();

        if (!$otp) {
            return response()->json(['message' => 'Invalid or expired OTP'], 422);
        }

        $otp->delete();

        return response()->json(['message' => 'OTP verified successfully']);
    }
} 