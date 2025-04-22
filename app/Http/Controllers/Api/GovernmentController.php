<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\TeamResource;
use App\Http\Resources\VolunteerResource;
use App\Models\Team;
use App\Models\Volunteer;
use App\Models\VolunteerTeam;
use App\Models\Campaign;
use App\Models\Employee;
use App\Models\DonorPayment;
use App\Models\Financial;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class GovernmentController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:sanctum');
        $this->middleware('government.only');
    }

    // Teams Tab
    public function getTeams()
    {
        $teams = VolunteerTeam::where('status', 'accepted')
            ->get()
            ->map(function ($team) {
                return [
                    'id' => $team->id,
                    'name' => $team->full_name,
                    'team_name' => $team->team_name,
                    'address' => $team->address,
                    'created_at' => $team->created_at,
                ];
            });

        return response()->json([
            'success' => true,
            'data' => $teams
        ]);
    }

    // Manager Requests Tab
    public function getPendingTeams()
    {
        $teams = VolunteerTeam::where('status', 'pending')->get();

        return response()->json([
            'success' => true,
            'data' => $teams
        ]);
    }

    public function approveTeam(VolunteerTeam $team)
    {
        $team->update(['status' => 'accepted']);

        return response()->json([
            'success' => true,
            'message' => 'Team approved successfully'
        ]);
    }

    public function rejectTeam(VolunteerTeam $team)
    {
        $team->update(['status' => 'rejected']);

        return response()->json([
            'success' => true,
            'message' => 'Team rejected successfully'
        ]);
    }

    // Volunteers Tab
    public function getVolunteers()
    {
        $volunteers = Volunteer::with(['team', 'campaigns'])
            ->get()
            ->map(function ($volunteer) {
                return [
                    'id' => $volunteer->id,
                    'name' => $volunteer->name,
                    'email' => $volunteer->email,
                    'team' => $volunteer->team ? $volunteer->team->team_name : null,
                    'campaign_count' => $volunteer->campaigns->count(),
                    'created_at' => $volunteer->created_at,
                ];
            });

        return response()->json([
            'success' => true,
            'data' => $volunteers
        ]);
    }

    public function getTeamDetails(VolunteerTeam $team)
    {
        return response()->json([
            'success' => true,
            'data' => [
                'id' => $team->id,
                'full_name' => $team->full_name,
                'team_name' => $team->team_name,
                'license_number' => $team->license_number,
                'phone' => $team->phone,
                'bank_account_number' => $team->bank_account_number,
                'email' => $team->email,
                'address' => $team->address,
                'status' => $team->status,
                'total_finance' => $team->campaigns->flatMap->financials->sum('amount'),
                'total_campaigns' => $team->campaigns->count(),
                'total_employees' => $team->employees->count(),
                'created_at' => $team->created_at,
            ]
        ]);
    }

    public function getTeamFinance(VolunteerTeam $team)
    {
        $payments = DonorPayment::whereHas('campaign', function ($query) use ($team) {
                $query->where('team_id', $team->id);
            })
            ->with(['benefactor', 'campaign'])
            ->get()
            ->map(function ($payment) {
                return [
                    'name' => $payment->benefactor->name,
                    'details' => $payment->campaign->campaign_name,
                    'date' => $payment->created_at,
                    'cost' => $payment->amount,
                ];
            });

        return response()->json([
            'success' => true,
            'data' => $payments
        ]);
    }

    public function getTeamCampaigns(VolunteerTeam $team)
    {
        $ongoingCampaigns = Campaign::where('team_id', $team->id)
            ->where('status', 'ongoing')
            ->with('campaignType')
            ->get()
            ->map(function ($campaign) {
                return [
                    'id' => $campaign->id,
                    'name' => $campaign->name,
                    'location' => $campaign->location,
                    'date' => $campaign->start_date,
                    'category' => $campaign->campaignType->name,
                    'cost' => $campaign->financials->sum('amount'),
                    'supplies' => $campaign->description,
                ];
            });

        $completedCampaigns = Campaign::where('team_id', $team->id)
            ->where('status', 'completed')
            ->with('campaignType')
            ->get()
            ->map(function ($campaign) {
                return [
                    'id' => $campaign->id,
                    'name' => $campaign->name,
                    'location' => $campaign->location,
                    'date' => $campaign->start_date,
                    'category' => $campaign->campaignType->name,
                    'cost' => $campaign->financials->sum('amount'),
                    'supplies' => $campaign->description,
                ];
            });

        return response()->json([
            'success' => true,
            'data' => [
                'ongoing_campaigns' => $ongoingCampaigns,
                'completed_campaigns' => $completedCampaigns
            ]
        ]);
    }

    public function getTeamEmployees(VolunteerTeam $team)
    {
        $employees = Employee::where('team_id', $team->id)
            ->get()
            ->map(function ($employee) {
                return [
                    'id' => $employee->id,
                    'name' => $employee->name,
                    'email' => $employee->email,
                    'phone' => $employee->phone,
                    'position' => $employee->position,
                    'salary' => $employee->salary,
                    'hire_date' => $employee->hire_date,
                ];
            });

        return response()->json([
            'success' => true,
            'data' => $employees
        ]);
    }

    public function approveCampaign(Campaign $campaign)
    {
        $campaign->update(['status' => 'ongoing']);

        return response()->json([
            'success' => true,
            'message' => 'Campaign approved successfully'
        ]);
    }

    public function rejectCampaign(Campaign $campaign)
    {
        $campaign->update(['status' => 'rejected']);

        return response()->json([
            'success' => true,
            'message' => 'Campaign rejected successfully'
        ]);
    }
} 