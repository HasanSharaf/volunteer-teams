<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Campaign;
use App\Models\Volunteer;
use App\Http\Resources\CampaignResource;
use App\Http\Resources\VolunteerResource;
use Illuminate\Http\Request;

class CampaignController extends Controller
{
    public function index()
    {
        $campaigns = Campaign::with(['specialization', 'campaignType', 'team', 'employee'])
            ->where('status','pending')->get();

        return CampaignResource::collection($campaigns);
    }

        public function getcampaignsBySpecialty()
    {
        $user = auth()->user(); 

        $campaigns = Campaign::where('specialization_id', $user->specialization_id)
            ->with(['specialization', 'campaignType', 'team', 'employee'])
            ->get();

        return CampaignResource::collection($campaigns);
    }


    public function store(Request $request)
    {
        $request->validate([
            'name_campaign' => 'required|string|max:255',
            'number_volunteers' => 'required|integer|min:1',
            'cost' => 'required|numeric|min:0',
            'address' => 'required|string|max:255',
            'from_time' => 'required|date',
            'to_time' => 'required|date|after:from_time',
            'points' => 'required|integer|min:0',
            'status' => 'required|in:pending,active,completed,cancelled',
            'specialization_id' => 'required|exists:specializations,id',
            'campaign_type_id' => 'required|exists:campaign_types,id',
            'team_id' => 'required|exists:volunteer_teams,id',
            'employee_id' => 'nullable|exists:employees,id',
        ]);

        $campaign = Campaign::create($request->all());

        return new CampaignResource($campaign);
    }

    public function show($id)
    {
        $campaign = Campaign::with(['specialization', 'campaignType', 'team', 'employee'])->find($id);
    
        if (!$campaign) {
            return response()->json([
                'message' => 'Campaign not found'
            ], 404);
        }
    
        return new CampaignResource($campaign);
    }

    public function update(Request $request, Campaign $campaign)
    {
        $request->validate([
            'name_campaign' => 'sometimes|string|max:255',
            'number_volunteers' => 'sometimes|integer|min:1',
            'cost' => 'sometimes|numeric|min:0',
            'address' => 'sometimes|string|max:255',
            'from_time' => 'sometimes|date',
            'to_time' => 'sometimes|date|after:from_time',
            'points' => 'sometimes|integer|min:0',
            'status' => 'sometimes|in:pending,active,completed,cancelled',
            'specialization_id' => 'sometimes|exists:specializations,id',
            'campaign_type_id' => 'sometimes|exists:campaign_types,id',
            'team_id' => 'sometimes|exists:volunteer_teams,id',
            'employee_id' => 'nullable|exists:employees,id',
        ]);

        $campaign->update($request->all());

        return new CampaignResource($campaign);
    }

    public function destroy(Campaign $campaign)
    {
        $campaign->delete();

        return response()->json(['message' => 'Campaign deleted successfully']);
    }

    public function volunteers(Campaign $campaign)
    {
        return VolunteerResource::collection($campaign->volunteers()->paginate(10));
    }

    public function addVolunteer(Campaign $campaign, Volunteer $volunteer)
    {
        if ($campaign->volunteers()->where('volunteer_id', $volunteer->id)->exists()) {
            return response()->json(['message' => 'Volunteer is already assigned to this campaign'], 422);
        }

        $campaign->volunteers()->attach($volunteer->id);

        return response()->json(['message' => 'Volunteer added to campaign successfully']);
    }

    public function removeVolunteer(Campaign $campaign, Volunteer $volunteer)
    {
        if (!$campaign->volunteers()->where('volunteer_id', $volunteer->id)->exists()) {
            return response()->json(['message' => 'Volunteer is not assigned to this campaign'], 422);
        }

        $campaign->volunteers()->detach($volunteer->id);

        return response()->json(['message' => 'Volunteer removed from campaign successfully']);
    }
} 