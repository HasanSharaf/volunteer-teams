<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Http\Resources\EmployeeResource;
use App\Http\Resources\CampaignResource;
use App\Http\Resources\PointResource;
use App\Http\Resources\AttendanceResource;
use App\Http\Resources\DonorPaymentResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class EmployeeController extends Controller
{
    public function index()
    {
        $employees = Employee::all();
        return response()->json($employees);
    }

    public function store(Request $request)
    {
        $request->validate([
            'full_name' => 'required|string|max:255',
            'phone' => 'required|string',
            'national_id' => 'required|string|unique:employees',
            'position' => 'required|string',
            'date_of_access' => 'required|date',
            'team_id' => 'required|exists:volunteer_teams,id',
            'specialization_id' => 'required|exists:specializations,id',
        ]);

        $employee = Employee::create($request->all());
        return response()->json($employee, 201);
    }

    public function show(Employee $employee)
    {
        return response()->json($employee);
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
} 