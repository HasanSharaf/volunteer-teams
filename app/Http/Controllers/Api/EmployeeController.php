<?php

namespace App\Http\Controllers\Api;

use App\Models\Campaign;
use App\Models\Employee;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use App\Http\Resources\PointResource;
use App\Http\Resources\CampaignResource;
use App\Http\Resources\EmployeeResource;
use App\Http\Resources\AttendanceResource;
use App\Http\Resources\DonorPaymentResource;

class EmployeeController extends Controller
{
 
    public function profileEmployee(Request $request)
    {
        $Employee = $request->user(); 
        $Employee->load('specialization');
    
        return response()->json([
            'success' => true,
            'message' => 'Employee profile retrieved successfully',
            'data' => $Employee,
        ]);
    }

    public function index()
    {
        $employees = Employee::all();
        return response()->json($employees);
    }


    public function store(Request $request)
    {
        $request->validate([
            'full_name' => 'required|string',
            'email' => 'required|email|unique:employees',
            'password' => 'required|min:6',
            'national_number' => 'nullable|unique:employees',
            'position' => 'required|in:مشرف,موظف مالي',
            'phone' => 'required|string',
            'address' => 'nullable|string',
            'date_accession' => 'required|date',
            'image' => 'nullable|string',
            'team_id' => 'required|exists:volunteer_teams,id',
            'specialization_id' => 'nullable|exists:specializations,id',
        ]);

        $employee = Employee::create([
            'full_name' => $request->full_name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'national_number' => $request->national_number,
            'position' => $request->position,
            'phone' => $request->phone,
            'address' => $request->address,
            'date_accession' => $request->date_accession,
            'image' => $request->image,
            'team_id' => $request->team_id,
            'specialization_id' => $request->specialization_id,
        ]);

        return response()->json(['employee' => $employee], 201);
    }
    
    public function show(Employee $employee)
    {
        return response()->json($employee);
    }

    public function updateEmployee(Request $request)
    {
        $employee = auth()->user(); // الموظف الحالي

        $request->validate([
            'full_name' => 'nullable|string',
            'email' => 'nullable|email|unique:employees,email,' . $employee->id,
            'password' => 'nullable|min:6',
            'national_number' => 'nullable|unique:employees,national_number,' . $employee->id,
            'position' => 'nullable|in:مشرف,موظف مالي',
            'phone' => 'sometimes|nullable|string',
            'address' => 'nullable|string',
            'date_accession' => 'nullable|date_format:Y-m-d',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
            'team_id' => 'nullable|exists:volunteer_teams,id',
            'specialization_id' => 'nullable|exists:specializations,id',
        ]);

        if ($request->hasFile('image')) {
            // حذف الصورة القديمة لو تريد (اختياري)
            if ($employee->image && file_exists(public_path($employee->image))) {
                unlink(public_path($employee->image));
            }

            $image = $request->file('image');
            $imageName = uniqid() . '.' . $image->getClientOriginalExtension();
            $image->move(public_path('uploads/employee'), $imageName);
            $employee->image = 'uploads/employee/' . $imageName;
        }

        // تحديث الحقول
        $employee->full_name = $request->input('full_name', $employee->full_name);
        $employee->email = $request->input('email', $employee->email);
        if ($request->filled('password')) {
            $employee->password = Hash::make($request->password);
        }
        $employee->national_number = $request->input('national_number', $employee->national_number);
        $employee->position = $request->input('position', $employee->position);
        $employee->phone = $request->input('phone', $employee->phone);
        $employee->address = $request->input('address', $employee->address);
        $employee->date_accession = $request->input('date_accession', $employee->date_accession);
        $employee->team_id = $request->input('team_id', $employee->team_id);
        $employee->specialization_id = $request->input('specialization_id', $employee->specialization_id);

        Employee::where('id', $employee->id)->update([
            'full_name' => $employee->full_name,
            'email' => $employee->email,
            'national_number' => $employee->national_number,
            'position' => $employee->position,
            'phone' => $employee->phone,
            'address' => $employee->address,
            'date_accession' => $employee->date_accession,
            'image' => $employee->image,
            'team_id' => $employee->team_id,
            'specialization_id' => $employee->specialization_id
        ]);

        return response()->json(['employee' => $employee], 200);
    }

    public function update(Request $request, Employee $employee)
    {
        $request->validate([
            'full_name' => 'sometimes|string|max:255',
            'phone' => 'sometimes|string',
            'national_id' => 'sometimes|string|unique:employees,national_id,' . $employee->id,
            'position' => 'sometimes|string',
            'date_of_access' => 'sometimes|date',
            'team_id' => 'sometimes|exists:volunteer_teams,id',
            'specialization_id' => 'sometimes|exists:specializations,id',
        ]);

        $employee->update($request->all());
        return response()->json($employee);
    }

    public function destroy(Employee $employee)
    {
        $employee->delete();
        return response()->json(null, 204);
    }

    public function campaigns(Employee $employee)
    {
        return CampaignResource::collection($employee->campaigns()->paginate(10));
    }

    public function points(Employee $employee)
    {
        return PointResource::collection($employee->points()->paginate(10));
    }

    public function attendances(Employee $employee)
    {
        return AttendanceResource::collection($employee->attendances()->paginate(10));
    }

    public function donorPayments(Employee $employee)
    {
        return DonorPaymentResource::collection($employee->donorPayments()->paginate(10));
    }

    public function getEmployeeCampaigns()
    {
        $employee = auth()->user(); 
        $campaigns = $employee->team->campaigns;
        $campaigns = Campaign::with(['specialization', 'campaignType', 'team', 'employee', 'volunteers'])->get();
        return CampaignResource::collection($campaigns);
        
    }

} 