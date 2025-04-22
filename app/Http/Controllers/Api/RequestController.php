<?php

namespace App\Http\Controllers\Api;

use App\Models\Volunteer;
use Illuminate\Http\Request;
use App\Models\VolunteerTeam;
use App\Http\Controllers\Controller;
use App\Http\Resources\RequestResource;
use App\Models\Request as VolunteerRequest;

class RequestController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $auth = auth()->user();
        $Request = VolunteerRequest::with(['volunteer', 'team'])->where('volunteer_id', $auth->id)
        ->get();
        return RequestResource::collection($Request);

        
    }
    

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'content' => 'required|string',
            'type' => 'required|in:complaints,suggestion',
            'team_id' => 'nullable|exists:volunteer_teams,id',
        ]);
    
        $validated['volunteer_id'] = auth()->id(); // تعيين المستخدم الحالي تلقائيًا
    
        $requestModel = VolunteerRequest::create($validated);
    
        return response()->json([
            'message' => 'Request created successfully',
            'data' => $requestModel
        ]);
    }
    

    /**
     * Display the specified resource.
     */
    public function show(VolunteerRequest $volunteerRequest)
    {
        $volunteerRequest->load(['team', 'volunteer']);
        return response()->json($volunteerRequest);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $requestModel = VolunteerRequest::find($id);
    
        if (!$requestModel) {
            return response()->json(['message' => 'Request not found'], 404);
        }
    
        $validated = $request->validate([
            'content' => 'sometimes|string',
            'type' => 'sometimes|in:complaints,suggestion',
            'team_id' => 'nullable|exists:volunteer_teams,id',
        ]);
    
        $requestModel->update($validated);
    
        return response()->json([
            'message' => 'Request updated successfully',
            'data' => $requestModel
        ]);
    }
    
    

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $requestModel = VolunteerRequest::findOrFail($id);
        $requestModel->delete();

        return response()->json(['message' => 'Request deleted successfully']);
    }






    public function getTeamRequests(VolunteerTeam $team)
    {
        $requests = VolunteerRequest::where('team_id', $team->id)
            ->with('volunteer')
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json($requests);
    }

    public function getVolunteerRequests(Volunteer $volunteer)
    {
        $requests = VolunteerRequest::where('volunteer_id', $volunteer->id)
            ->with('team')
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json($requests);
    }

    public function getRequestsByStatus($status)
    {
        $validStatuses = ['pending', 'App\\roved', 'rejected'];
        
        if (!in_array($status, $validStatuses)) {
            return response()->json(['message' => 'Invalid status'], 422);
        }

        $requests = VolunteerRequest::where('status', $status)
            ->with(['team', 'volunteer'])
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json($requests);
    }

    public function getRequestsByType($type)
    {
        $validTypes = ['join_team', 'leave_team', 'change_team', 'other'];
        
        if (!in_array($type, $validTypes)) {
            return response()->json(['message' => 'Invalid request type'], 422);
        }

        $requests = VolunteerRequest::where('type', $type)
            ->with(['team', 'volunteer'])
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json($requests);
    }

    private function handleApprovedRequest(VolunteerRequest $request)
    {
        $team = VolunteerTeam::findOrFail($request->team_id);
        $volunteer = Volunteer::findOrFail($request->volunteer_id);

        switch ($request->type) {
            case 'join_team':
                if (!$team->volunteers()->where('volunteer_id', $volunteer->id)->exists()) {
                    $team->volunteers()->attach($volunteer->id);
                }
                break;

            case 'leave_team':
                if ($team->volunteers()->where('volunteer_id', $volunteer->id)->exists()) {
                    $team->volunteers()->detach($volunteer->id);
                }
                break;

            case 'change_team':
                // Handle team change logic here
                // This would typically involve removing from old team and adding to new team
                break;
        }
    }
}
