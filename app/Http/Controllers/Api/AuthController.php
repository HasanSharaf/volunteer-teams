<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Volunteer;
use App\Models\VolunteerTeam;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function volunteerRegister(Request $request)
    {
        try {
            $request->validate([
                'full_name' => 'required|string|max:255',
                'national_id' => 'required|string',
                'nationality' => 'required|string|max:255',
                'phone_number' => 'required|string|max:255',
                'email' => 'required|string|email|max:255|unique:volunteers',
                'password' => 'required|string|min:8',
                'birth_date' => 'required|date',
                'specialization_id' => 'required|exists:specializations,id',
                'team_id' => 'required|exists:volunteer_teams,id',
            ]);

            $volunteer = Volunteer::create([
                'full_name' => $request->full_name,
                'national_id' => $request->national_id,
                'nationality' => $request->nationality,
                'phone_number' => $request->phone_number,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'birth_date' => $request->birth_date,
                'specialization_id' => $request->specialization_id,
                'team_id' => $request->team_id,
                'total_points' => 0,
            ]);

            $token = $volunteer->createToken('auth_token')->plainTextToken;

            return response()->json([
                'success' => true,
                'message' => 'Volunteer registered successfully',
                'data' => [
                    'volunteer' => $volunteer,
                    'token' => $token,
                ]
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
                'errors' => $e instanceof ValidationException ? $e->errors() : []
            ], 422);
        }
    }

    public function volunteerLogin(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $volunteer = Volunteer::where('email', $request->email)->first();

        if (!$volunteer || !Hash::check($request->password, $volunteer->password)) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }

        $token = $volunteer->createToken('auth_token')->plainTextToken;

        return response()->json([
            'message' => 'Volunteer logged in successfully',
            'volunteer' => $volunteer,
            'token' => $token,
        ]);
    }

    public function teamRegister(Request $request)
    {
        $request->validate([
            'full_name' => 'required|string|max:255',
            'team_name' => 'required|string|max:255',
            'license_number' => 'required|string|unique:volunteer_teams',
            'phone' => 'required|string|max:255',
            'bank_account_number' => 'required|string|unique:volunteer_teams',
            'email' => 'required|string|email|max:255|unique:volunteer_teams',
            'password' => 'required|string|min:8',
        ]);
        
        $team = VolunteerTeam::create([
            'full_name' => $request->full_name,
            'team_name' => $request->team_name,
            'license_number' => $request->license_number,
            'phone' => $request->phone,
            'bank_account_number' => $request->bank_account_number,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);
        
        $token = $team->createToken('auth_token')->plainTextToken;

        return response()->json([
            'message' => 'Team registered successfully',
            'team' => $team,
            'token' => $token,
        ], 201);
    }

    public function teamLogin(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $team = VolunteerTeam::where('email', $request->email)->first();

        if (!$team || !Hash::check($request->password, $team->password)) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }

        $token = $team->createToken('auth_token')->plainTextToken;

        return response()->json([
            'message' => 'Team logged in successfully',
            'team' => $team,
            'token' => $token,
        ]);
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => 'Successfully logged out',
        ]);
    }
} 