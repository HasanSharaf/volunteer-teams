<?php

namespace App\Http\Controllers\Api;

use App\Models\Volunteer;
use App\Models\Government;
use Illuminate\Http\Request;
use App\Models\VolunteerTeam;
use App\Models\BusinessInformation;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class AuthController extends Controller
{

    //Governments
    public function loginGovernment(Request $request)
    {
        
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $Government = Government::where('email', $request->email)->first();

        if (!$Government || !Hash::check($request->password, $Government->password)) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }

        $token = $Government->createToken('auth_token')->plainTextToken;

        return response()->json([
            'message' => 'Government logged in successfully',
            'Government' => $Government,
            'token' => $token,
        ]);
    }
    

 

    public function updateProfileGovernment(Request $request)
    {
        $government = $request->user(); // احصل على المستخدم أولاً
    
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:governments,email,' . $government->id,
            'password' => 'nullable|string|min:8',
        ]);
    
        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'Validation Error',
                'errors' => $validator->errors()
            ], 422);
        }
    
        $government->name = $request->name;
        $government->email = $request->email;
    
        if ($request->password) {
            $government->password = Hash::make($request->password);
        }
    
        $government->save();
    
        return response()->json([
            'status' => true,
            'message' => 'Profile updated successfully',
            'data' => [
                'government' => $government
            ]
        ]);
    }
    
    



    //Volunteer
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

    /// teams
    public function teamRegister(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'full_name' => 'required|string|max:255',
            'National_number' => 'required|string|unique:volunteer_teams',
            'phone' => 'required|string|max:255|unique:volunteer_teams',
            'gender' => 'required|in:ذكر,أنثى',
            'nationality' => 'required|string',
            'birth_date' => 'required|date_format:Y-m-d',
            'image' => 'required|image',
            'email' => 'required|email|unique:volunteer_teams',
            'password' => 'required|string|min:8',
    
            'team_name' => 'required|string|max:255',
            'bank_account_number' => 'required|string|unique:business_informations',
            'log_image' => 'required|image',
            'logo' => 'required|image',
            'license_number' => 'required|string',
            'address' => 'nullable|string',
        ]);
    
        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }
    
        DB::beginTransaction();
    
        try {
            // حفظ الصور في مجلد public/uploads
            $imagePath = $request->file('image')->move(public_path('uploads/volunteers'), uniqid() . '.' . $request->file('image')->getClientOriginalExtension());
            $logoPath = $request->file('logo')->move(public_path('uploads/logos'), uniqid() . '.' . $request->file('logo')->getClientOriginalExtension());
            $logImagePath = $request->file('log_image')->move(public_path('uploads/log'), uniqid() . '.' . $request->file('log_image')->getClientOriginalExtension());
    
            // تحويل المسارات إلى relative
            $imageRelativePath = 'uploads/volunteers/' . basename($imagePath);
            $logoRelativePath = 'uploads/logos/' . basename($logoPath);
            $logImageRelativePath = 'uploads/log/' . basename($logImagePath);
    
            $volunteerTeam = VolunteerTeam::create([
                'full_name' => $request->full_name,
                'National_number' => $request->National_number,
                'phone' => $request->phone,
                'gender' => $request->gender,
                'nationality' => $request->nationality,
                'birth_date' => $request->birth_date,
                'image' => $imageRelativePath,
                'email' => $request->email,
                'password' => Hash::make($request->password),
            ]);
    
            $businessInfo = BusinessInformation::create([
                'team_name' => $request->team_name,
                'bank_account_number' => $request->bank_account_number,
                'log_image' => $logImageRelativePath,
                'logo' => $logoRelativePath,
                'license_number' => $request->license_number,
                'address' => $request->address,
                'team_id' => $volunteerTeam->id,
            ]);
    
            DB::commit();
    
            return response()->json([
                'message' => 'Team registered successfully',
                'team' => $volunteerTeam,
                'business_info' => $businessInfo,
            ], 201);
    
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Registration failed',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
    

    public function teamLogin(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);
    
        $team = VolunteerTeam::where('email', $request->email)->first();
    
        if (!$team || !$team->status) {
            return response()->json([
                'message' => !$team ? 'The provided credentials are incorrect.' : 'عذرًا، حسابك غير مفعل.',
            ], 403); 
        }
    
        // تحقق من كلمة المرور
        if (!Hash::check($request->password, $team->password)) {
            return response()->json([
                'message' => 'The provided credentials are incorrect.',
            ], 403);
        }
    
        // إذا كان الحساب مفعل وكلمة المرور صحيحة، أنشئ التوكن
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