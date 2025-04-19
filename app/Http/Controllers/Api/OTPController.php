<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\OTP;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class OTPController extends Controller
{
    public function sendOTP(Request $request)
    {
        try {
            $request->validate([
                'email' => 'required|email',
            ]);

            // Generate a 6-digit OTP
            $otp = str_pad(rand(0, 999999), 6, '0', STR_PAD_LEFT);
            $expiresAt = now()->addMinutes(5);

            // Create OTP record
            OTP::create([
                'otp' => $otp,
                'email' => $request->email,
                'expires_at' => $expiresAt,
            ]);

            // Log the OTP instead of sending email
            Log::info('OTP Code', [
                'email' => $request->email,
                'otp' => $otp,
                'expires_at' => $expiresAt
            ]);

            return response()->json([
                'success' => true,
                'message' => 'OTP sent successfully',
                'otp' => $otp, // For testing purposes only
                'expires_at' => $expiresAt
            ]);

        } catch (\Exception $e) {
            Log::error('OTP Error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to send OTP',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function verifyOTP(Request $request)
    {
        try {
            $request->validate([
                'email' => 'required|email',
                'otp' => 'required|string',
            ]);

            $otp = OTP::where('email', $request->email)
                      ->where('otp', $request->otp)
                      ->where('expires_at', '>', now())
                      ->first();

            if (!$otp) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid or expired OTP'
                ], 422);
            }

            $otp->delete();

            return response()->json([
                'success' => true,
                'message' => 'OTP verified successfully'
            ]);

        } catch (\Exception $e) {
            Log::error('OTP Verification Error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to verify OTP',
                'error' => $e->getMessage()
            ], 500);
        }
    }
} 