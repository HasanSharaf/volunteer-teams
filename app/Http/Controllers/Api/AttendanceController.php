<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\Volunteer;
use App\Models\Employee;
use App\Models\Campaign;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class AttendanceController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $attendances = Attendance::with(['volunteer', 'employee', 'campaign'])->get();
        return response()->json($attendances);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'is_attended' => 'required|boolean',
            'banned_point' => 'required|integer|min:0',
            'date_of_attendance' => 'required|date',
            'image' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'campaign_id' => 'required|exists:campaigns,id',
            'employee_id' => 'required|exists:employees,id',
            'volunteer_id' => 'required|exists:volunteers,id',
        ]);

        // Check if attendance already exists for this combination
        $existingAttendance = Attendance::where('campaign_id', $request->campaign_id)
            ->where('volunteer_id', $request->volunteer_id)
            ->where('date_of_attendance', $request->date_of_attendance)
            ->first();

        if ($existingAttendance) {
            return response()->json(['message' => 'Attendance record already exists for this date'], 422);
        }

        // Handle image upload if present
        $imagePath = null;
        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('attendance_images', 'public');
        }

        $attendance = Attendance::create([
            'is_attended' => $request->is_attended,
            'banned_point' => $request->banned_point,
            'date_of_attendance' => $request->date_of_attendance,
            'image' => $imagePath,
            'campaign_id' => $request->campaign_id,
            'employee_id' => $request->employee_id,
            'volunteer_id' => $request->volunteer_id,
        ]);

        // Update volunteer's points if attendance is marked as not attended
        if (!$request->is_attended) {
            $volunteer = Volunteer::findOrFail($request->volunteer_id);
            $volunteer->total_points -= $request->banned_point;
            $volunteer->save();
        }

        return response()->json($attendance, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Attendance $attendance)
    {
        $attendance->load(['volunteer', 'employee', 'campaign']);
        return response()->json($attendance);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Attendance $attendance)
    {
        $request->validate([
            'is_attended' => 'sometimes|boolean',
            'banned_point' => 'sometimes|integer|min:0',
            'date_of_attendance' => 'sometimes|date',
            'image' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'campaign_id' => 'sometimes|exists:campaigns,id',
            'employee_id' => 'sometimes|exists:employees,id',
            'volunteer_id' => 'sometimes|exists:volunteers,id',
        ]);

        // Store old values for point adjustment
        $oldIsAttended = $attendance->is_attended;
        $oldBannedPoint = $attendance->banned_point;

        // Handle image update
        if ($request->hasFile('image')) {
            // Delete old image if exists
            if ($attendance->image) {
                Storage::disk('public')->delete($attendance->image);
            }
            $imagePath = $request->file('image')->store('attendance_images', 'public');
            $attendance->image = $imagePath;
        }

        $attendance->update($request->except('image'));

        // Update volunteer's points if attendance status changed
        if ($request->has('is_attended') || $request->has('banned_point')) {
            $volunteer = Volunteer::findOrFail($attendance->volunteer_id);
            
            if ($oldIsAttended && !$request->is_attended) {
                // Was attended, now not attended - subtract points
                $volunteer->total_points -= $request->banned_point;
            } elseif (!$oldIsAttended && $request->is_attended) {
                // Was not attended, now attended - add back points
                $volunteer->total_points += $oldBannedPoint;
            } elseif (!$oldIsAttended && !$request->is_attended && $request->has('banned_point')) {
                // Was not attended, still not attended but banned points changed
                $volunteer->total_points += $oldBannedPoint; // Add back old points
                $volunteer->total_points -= $request->banned_point; // Subtract new points
            }
            
            $volunteer->save();
        }

        return response()->json($attendance);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Attendance $attendance)
    {
        // Delete associated image if exists
        if ($attendance->image) {
            Storage::disk('public')->delete($attendance->image);
        }

        // Update volunteer's points if attendance was not marked
        if (!$attendance->is_attended) {
            $volunteer = Volunteer::findOrFail($attendance->volunteer_id);
            $volunteer->total_points += $attendance->banned_point;
            $volunteer->save();
        }

        $attendance->delete();
        return response()->json(null, 204);
    }

    public function getVolunteerAttendance(Volunteer $volunteer)
    {
        $attendances = Attendance::where('volunteer_id', $volunteer->id)
            ->with('campaign')
            ->orderBy('date_of_attendance', 'desc')
            ->get();

        return response()->json($attendances);
    }

    public function getEmployeeAttendance(Employee $employee)
    {
        $attendances = Attendance::where('employee_id', $employee->id)
            ->with(['volunteer', 'campaign'])
            ->orderBy('date_of_attendance', 'desc')
            ->get();

        return response()->json($attendances);
    }

    public function getCampaignAttendance(Campaign $campaign)
    {
        $attendances = Attendance::where('campaign_id', $campaign->id)
            ->with(['volunteer', 'employee'])
            ->orderBy('date_of_attendance', 'desc')
            ->get();

        return response()->json($attendances);
    }

    public function getVolunteerCampaignAttendance(Volunteer $volunteer, Campaign $campaign)
    {
        $attendance = Attendance::where('volunteer_id', $volunteer->id)
            ->where('campaign_id', $campaign->id)
            ->with('campaign')
            ->first();

        return response()->json($attendance);
    }
}
