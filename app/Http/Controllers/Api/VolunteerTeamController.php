<?php

namespace App\Http\Controllers\Api;

use App\Models\Employee;
use App\Models\Financial;
use App\Models\Volunteer;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Models\VolunteerTeam;
use App\Models\BusinessInformation;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class VolunteerTeamController extends Controller
{
    public function index()
    {
        $teams = VolunteerTeam::with(['businessInformation', 'employees', 'volunteers', 'financial'])->get();
        return response()->json($teams);
    }

    public function store(Request $request)
    {
        $request->validate([
            'full_name' => 'required|string|max:255',
            'national_id' => 'required|string|unique:volunteer_teams',
            'gender' => 'required|in:male,female',
            'nationality' => 'required|string',
            'address' => 'required|string',
            'date_of_birth' => 'required|date',
            'email' => 'required|email|unique:volunteer_teams',
            'password' => 'required|string|min:8',
            'status' => 'required|in:active,inactive',
            'image' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
        ]);

        // Handle image upload if present
        $imagePath = null;
        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('team_images', 'public');
        }

        $team = VolunteerTeam::create([
            'full_name' => $request->full_name,
            'national_id' => $request->national_id,
            'gender' => $request->gender,
            'nationality' => $request->nationality,
            'address' => $request->address,
            'date_of_birth' => $request->date_of_birth,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'status' => $request->status,
            'image' => $imagePath,
        ]);

        // Create initial financial record
        $team->financial()->create(['total_amount' => 0]);

        return response()->json($team, 201);
    }

    public function show(VolunteerTeam $team)
    {
        $team->load(['businessInformation', 'employees', 'volunteers', 'financial', 'campaigns', 'requests', 'donorPayments', 'contracts']);
        return response()->json($team);
    }

    public function update(Request $request, VolunteerTeam $team)
    {
        $request->validate([
            'full_name' => 'sometimes|string|max:255',
            'national_id' => 'sometimes|string|unique:volunteer_teams,national_id,' . $team->id,
            'gender' => 'sometimes|in:male,female',
            'nationality' => 'sometimes|string',
            'address' => 'sometimes|string',
            'date_of_birth' => 'sometimes|date',
            'email' => 'sometimes|email|unique:volunteer_teams,email,' . $team->id,
            'password' => 'sometimes|string|min:8',
            'status' => 'sometimes|in:active,inactive',
            'image' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
        ]);

        // Handle image update
        if ($request->hasFile('image')) {
            // Delete old image if exists
            if ($team->image) {
                Storage::disk('public')->delete($team->image);
            }
            $imagePath = $request->file('image')->store('team_images', 'public');
            $team->image = $imagePath;
        }

        $data = $request->except('image');
        if (isset($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        }

        $team->update($data);
        return response()->json($team);
    }

    public function destroy(VolunteerTeam $team)
    {
        // Delete associated image if exists
        if ($team->image) {
            Storage::disk('public')->delete($team->image);
        }

        $team->delete();
        return response()->json(null, 204);
    }

    public function addVolunteer(Request $request, VolunteerTeam $team)
    {
        $request->validate([
            'volunteer_id' => 'required|exists:volunteers,id',
        ]);

        $volunteer = Volunteer::findOrFail($request->volunteer_id);

        // Check if volunteer is already in the team
        if ($team->volunteers()->where('volunteer_id', $volunteer->id)->exists()) {
            return response()->json(['message' => 'Volunteer is already a member of this team'], 422);
        }

        $team->volunteers()->attach($volunteer->id);
        return response()->json(['message' => 'Volunteer added to team successfully']);
    }

    public function removeVolunteer(VolunteerTeam $team, Volunteer $volunteer)
    {
        if (!$team->volunteers()->where('volunteer_id', $volunteer->id)->exists()) {
            return response()->json(['message' => 'Volunteer is not a member of this team'], 422);
        }

        $team->volunteers()->detach($volunteer->id);
        return response()->json(['message' => 'Volunteer removed from team successfully']);
    }

    public function getVolunteers(VolunteerTeam $team)
    {
        $volunteers = $team->volunteers()->with('specialization')->get();
        return response()->json($volunteers);
    }

    public function getEmployees(VolunteerTeam $team)
    {
        $employees = $team->employees()->with('specialization')->get();
        return response()->json($employees);
    }

    public function getCampaigns(VolunteerTeam $team)
    {
        $campaigns = $team->campaigns()->with(['specialization', 'campaignType', 'employee'])->get();
        return response()->json($campaigns);
    }

    public function getFinancial(VolunteerTeam $team)
    {
        $financial = $team->financial;
        return response()->json($financial);
    }

    public function getBusinessInformation(VolunteerTeam $team)
    {
        $businessInfo = $team->businessInformation;
        return response()->json($businessInfo);
    }

    public function getDonorPayments(VolunteerTeam $team)
    {
        $payments = $team->donorPayments()->with(['benefactor', 'employee'])->get();
        return response()->json($payments);
    }

    public function getContracts(VolunteerTeam $team)
    {
        $contracts = $team->contracts;
        return response()->json($contracts);
    }

    public function getRequests(VolunteerTeam $team)
    {
        $requests = $team->requests()->with('volunteer')->get();
        return response()->json($requests);
    }


    public function storeEmployee(Request $request)
    {
        $request->validate([
            'full_name' => 'required|string',
            'email' => 'required|email|unique:employees',
            'password' => 'required|min:6',
            'national_number' => 'nullable|unique:employees',
            'position' => 'required|in:مشرف,موظف مالي',
            'phone' => 'required|string',
            'address' => 'nullable|string',
            'date_accession' => 'required|date_format:Y-m-d',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048', 
            'team_id' => 'exists:volunteer_teams,id',
            'specialization_id' => 'nullable|exists:specializations,id',
        ]);
    
        $imagePath = null;
        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->move(public_path('uploads/employee'), uniqid() . '.' . $request->file('image')->getClientOriginalExtension());
            $imageRelativePath = 'uploads/employee/' . basename($imagePath);

         
        }
    
        $employee = Employee::create([
            'full_name' => $request->full_name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'national_number' => $request->national_number,
            'position' => $request->position,
            'phone' => $request->phone,
            'address' => $request->address,
            'date_accession' => $request->date_accession,
            'image' => $imageRelativePath, 
            'team_id' => auth()->user()->id,
            'specialization_id' => $request->specialization_id,
        ]);
    
        return response()->json(['employee' => $employee], 201);
    }

    public function LoginEmployee(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);
    
        $employee = Employee::where('email', $request->email)->first();
    
        if (!$employee || !Hash::check($request->password, $employee->password)) {
            return response()->json([
                'message' => 'The provided credentials are incorrect.',
            ], 403);
        }
    


        $token = $employee->createToken('auth_token')->plainTextToken;
    
        return response()->json([
            'message' => 'employee logged in successfully',
            'Employee' => $employee,
            'token' => $token,
        ]);
    }


} 