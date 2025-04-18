<?php

namespace app\Http\Controllers\Api;

use app\Http\Controllers\Controller;
use app\Models\Request as VolunteerRequest;
use app\Models\VolunteerTeam;
use app\Models\Volunteer;
use Illuminate\Http\Request;

class RequestController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $requests = VolunteerRequest::with(['team', 'volunteer'])->get();
        return response()->json($requests);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'team_id' => 'required|exists:volunteer_teams,id',
            'type' => 'required|in:join_team,leave_team,change_team,other',
            'content' => 'required|string',
            'status' => 'required|in:pending,App\\roved,rejected',
            'volunteer_id' => 'required|exists:volunteers,id',
        ]);

        // Check if volunteer is already in the team for join requests
        if ($request->type === 'join_team') {
            $team = VolunteerTeam::findOrFail($request->team_id);
            if ($team->volunteers()->where('volunteer_id', $request->volunteer_id)->exists()) {
                return response()->json(['message' => 'Volunteer is already a member of this team'], 422);
            }
        }

        // Check if volunteer is not in the team for leave requests
        if ($request->type === 'leave_team') {
            $team = VolunteerTeam::findOrFail($request->team_id);
            if (!$team->volunteers()->where('volunteer_id', $request->volunteer_id)->exists()) {
                return response()->json(['message' => 'Volunteer is not a member of this team'], 422);
            }
        }

        // Check for existing pending requests
        $existingRequest = VolunteerRequest::where('volunteer_id', $request->volunteer_id)
            ->where('team_id', $request->team_id)
            ->where('type', $request->type)
            ->where('status', 'pending')
            ->first();

        if ($existingRequest) {
            return response()->json(['message' => 'A pending request of this type already exists'], 422);
        }

        $volunteerRequest = VolunteerRequest::create($request->all());

        return response()->json($volunteerRequest, 201);
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
    public function update(Request $request, VolunteerRequest $volunteerRequest)
    {
        $request->validate([
            'team_id' => 'sometimes|exists:volunteer_teams,id',
            'type' => 'sometimes|in:join_team,leave_team,change_team,other',
            'content' => 'sometimes|string',
            'status' => 'sometimes|in:pending,App\\roved,rejected',
            'volunteer_id' => 'sometimes|exists:volunteers,id',
        ]);

        // Store old values for status change handling
        $oldStatus = $volunteerRequest->status;
        $oldType = $volunteerRequest->type;

        $volunteerRequest->update($request->all());

        // Handle status change
        if ($request->has('status') && $oldStatus !== $request->status) {
            if ($request->status === 'App\\roved') {
                $this->handleApprovedRequest($volunteerRequest);
            }
        }

        return response()->json($volunteerRequest);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(VolunteerRequest $volunteerRequest)
    {
        $volunteerRequest->delete();
        return response()->json(null, 204);
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
