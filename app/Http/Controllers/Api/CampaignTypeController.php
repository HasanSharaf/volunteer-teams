<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CampaignType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class CampaignTypeController extends Controller
{
    public function index()
    {
        $campaignTypes = CampaignType::with('campaigns')->get();
        return response()->json($campaignTypes);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:campaign_types',
            'description' => 'nullable|string',
            'icon' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'color' => 'nullable|string|max:7',
            'status' => 'boolean',
            'requirements' => 'nullable|array',
            'benefits' => 'nullable|array',
            'notes' => 'nullable|string'
        ]);

        $data = $request->except('icon');

        // Handle icon upload if present
        if ($request->hasFile('icon')) {
            $iconPath = $request->file('icon')->store('campaign_types/icons', 'public');
            $data['icon'] = $iconPath;
        }

        $campaignType = CampaignType::create($data);

        return response()->json($campaignType, 201);
    }

    public function show(CampaignType $campaignType)
    {
        $campaignType->load('campaigns');
        return response()->json($campaignType);
    }

    public function update(Request $request, CampaignType $campaignType)
    {
        $request->validate([
            'name' => 'sometimes|string|max:255|unique:campaign_types,name,' . $campaignType->id,
            'description' => 'nullable|string',
            'icon' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'color' => 'nullable|string|max:7',
            'status' => 'boolean',
            'requirements' => 'nullable|array',
            'benefits' => 'nullable|array',
            'notes' => 'nullable|string'
        ]);

        $data = $request->except('icon');

        // Handle icon update
        if ($request->hasFile('icon')) {
            // Delete old icon if exists
            if ($campaignType->icon) {
                Storage::disk('public')->delete($campaignType->icon);
            }
            $iconPath = $request->file('icon')->store('campaign_types/icons', 'public');
            $data['icon'] = $iconPath;
        }

        $campaignType->update($data);

        return response()->json($campaignType);
    }

    public function destroy(CampaignType $campaignType)
    {
        // Delete icon if exists
        if ($campaignType->icon) {
            Storage::disk('public')->delete($campaignType->icon);
        }

        $campaignType->delete();
        return response()->json(null, 204);
    }

    public function getActiveTypes()
    {
        $types = CampaignType::active()->with('campaigns')->get();
        return response()->json($types);
    }

    public function getTypeMetrics(CampaignType $campaignType)
    {
        return response()->json($campaignType->getTypeMetrics());
    }

    public function getTypeDetails(CampaignType $campaignType)
    {
        return response()->json($campaignType->getTypeDetails());
    }

    public function getRecentCampaigns(CampaignType $campaignType)
    {
        $campaigns = $campaignType->getRecentCampaigns();
        return response()->json($campaigns);
    }

    public function getPopularCampaigns(CampaignType $campaignType)
    {
        $campaigns = $campaignType->getPopularCampaigns();
        return response()->json($campaigns);
    }

    public function getSuccessfulCampaigns(CampaignType $campaignType)
    {
        $campaigns = $campaignType->getSuccessfulCampaigns();
        return response()->json($campaigns);
    }

    public function getCampaignCount(CampaignType $campaignType)
    {
        return response()->json([
            'total_campaigns' => $campaignType->getCampaignCount(),
            'active_campaigns' => $campaignType->getActiveCampaignCount(),
            'completed_campaigns' => $campaignType->getCompletedCampaignCount(),
            'upcoming_campaigns' => $campaignType->getUpcomingCampaignCount(),
            'ongoing_campaigns' => $campaignType->getOngoingCampaignCount()
        ]);
    }

    public function getDonationStats(CampaignType $campaignType)
    {
        return response()->json([
            'total_donations' => $campaignType->getTotalDonations(),
            'total_volunteers' => $campaignType->getTotalVolunteers()
        ]);
    }
} 