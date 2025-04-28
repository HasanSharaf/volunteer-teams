<?php

namespace App\Http\Controllers\Api;

use App\Models\Campaign;
use App\Models\Volunteer;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Http\Resources\CampaignResource;
use App\Http\Resources\VolunteerResource;

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


    public function storeCampaign(Request $request)
    {
        $request->validate([
            'campaign_name' => 'required|string|max:255',
            'number_of_volunteer' => 'required|integer',
            'cost' => 'required|numeric',
            'address' => 'required|string',
            'from' => 'required|date_format:Y-m-d H:i:s',
            'to' => 'required|date_format:Y-m-d H:i:s',
            'points' => 'required|integer',
            'specialization_id' => 'nullable|exists:specializations,id',
            'campaign_type_id' => 'required|exists:campaign_types,id',
        ]);
    
        $employee = Auth::user();
    
        $team_id = $employee->team_id;
    
        $campaign = Campaign::create([
            'campaign_name' => $request->campaign_name,
            'number_of_volunteer' => $request->number_of_volunteer,
            'cost' => $request->cost,
            'address' => $request->address,
            'from' => $request->from,
            'to' => $request->to,
            'points' => $request->points,
            'status' => 'pending',
            'specialization_id' => $request->specialization_id,
            'campaign_type_id' => $request->campaign_type_id,
            'team_id' => $team_id, 
            'employee_id' => $employee->id, 
        ]);
    
        return response()->json(['campaign' => $campaign], 201);
    }

    public function show($id)
    {
        try {
            $campaign = Campaign::find($id);
    
            if (!$campaign) {
                return response()->json(['message' => 'Campaign not found'], 404);
            }
    
        $campaign->load(['specialization', 'campaignType', 'team', 'employee', 'volunteers']);
        
        return new CampaignResource($campaign);
        } catch (\Exception $e) {
            return response()->json(['message' => 'An error occurred while deleting the campaign', 'error' => $e->getMessage()], 500);
        }
    }

    public function update(Request $request, $id)
    {
        // التحقق من صحة البيانات المدخلة
        $request->validate([
            'campaign_name' => 'nullable|string|max:255',
            'number_of_volunteer' => 'nullable|integer',
            'cost' => 'nullable|numeric',
            'address' => 'nullable|string',
            'from' => 'nullable|date_format:Y-m-d H:i:s',
            'to' => 'nullable|date_format:Y-m-d H:i:s',
            'points' => 'nullable|integer',
            'specialization_id' => 'nullable|exists:specializations,id',
            'campaign_type_id' => 'nullable|exists:campaign_types,id',
        ]);
    
        // البحث عن الحملة باستخدام الـ ID
        $campaign = Campaign::find($id);
    
        // إذا لم يتم العثور على الحملة، نرجع رسالة خطأ
        if (!$campaign) {
            return response()->json(['message' => 'Campaign not found'], 404);
        }
    
        // تحديث الحملة بالقيم الجديدة أو الاحتفاظ بالقيم القديمة إذا كانت الحقول فارغة
        $campaign->update([
            'campaign_name' => $request->campaign_name ?: $campaign->campaign_name,
            'number_of_volunteer' => $request->number_of_volunteer ?: $campaign->number_of_volunteer,
            'cost' => $request->cost ?: $campaign->cost,
            'address' => $request->address ?: $campaign->address,
            'from' => $request->from ?: $campaign->from,
            'to' => $request->to ?: $campaign->to,
            'points' => $request->points ?: $campaign->points,
            'specialization_id' => $request->specialization_id ?: $campaign->specialization_id,
            'campaign_type_id' => $request->campaign_type_id ?: $campaign->campaign_type_id,
        ]);
    
        // إرجاع الحملة المحدثة
        return response()->json(['campaign' => $campaign], 200);
    }
    
    

    public function destroy($id)
    {
        try {
            $campaign = Campaign::find($id);
    
            if (!$campaign) {
                return response()->json(['message' => 'Campaign not found'], 404);
            }
    
            $campaign->delete();
    
            return response()->json(['message' => 'Campaign deleted successfully'], 200);
        } catch (\Exception $e) {
            return response()->json(['message' => 'An error occurred while deleting the campaign', 'error' => $e->getMessage()], 500);
        }
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