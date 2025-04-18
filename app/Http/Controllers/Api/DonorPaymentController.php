<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\DonorPayment;
use App\Models\Benefactor;
use App\Models\VolunteerTeam;
use App\Models\Employee;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Http\Resources\DonorPaymentResource;

class DonorPaymentController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $payments = DonorPayment::paginate(10);
        return DonorPaymentResource::collection($payments);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'amount' => 'required|numeric|min:0',
            'payment_date' => 'required|date',
            'payment_method' => 'required|string|max:255',
            'status' => 'required|in:pending,completed,failed',
            'donor_id' => 'required|exists:benefactors,id',
            'team_id' => 'required|exists:volunteer_teams,id',
        ]);

        $payment = DonorPayment::create($request->all());
        return new DonorPaymentResource($payment);
    }

    /**
     * Display the specified resource.
     */
    public function show(DonorPayment $donorPayment)
    {
        return new DonorPaymentResource($donorPayment);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, DonorPayment $donorPayment)
    {
        $request->validate([
            'amount' => 'sometimes|numeric|min:0',
            'payment_date' => 'sometimes|date',
            'payment_method' => 'sometimes|string|max:255',
            'status' => 'sometimes|in:pending,completed,failed',
            'donor_id' => 'sometimes|exists:benefactors,id',
            'team_id' => 'sometimes|exists:volunteer_teams,id',
        ]);

        $donorPayment->update($request->all());
        return new DonorPaymentResource($donorPayment);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(DonorPayment $donorPayment)
    {
        $donorPayment->delete();
        return response()->json(['message' => 'Donor payment deleted successfully']);
    }

    public function getBenefactorPayments(Benefactor $benefactor)
    {
        $payments = DonorPayment::where('benefactor_id', $benefactor->id)
            ->with(['team', 'employee'])
            ->orderBy('date_of_payment', 'desc')
            ->get();

        return response()->json($payments);
    }

    public function getTeamPayments(VolunteerTeam $team)
    {
        $payments = DonorPayment::where('team_id', $team->id)
            ->with(['benefactor', 'employee'])
            ->orderBy('date_of_payment', 'desc')
            ->get();

        return response()->json($payments);
    }

    public function getEmployeePayments(Employee $employee)
    {
        $payments = DonorPayment::where('employee_id', $employee->id)
            ->with(['benefactor', 'team'])
            ->orderBy('date_of_payment', 'desc')
            ->get();

        return response()->json($payments);
    }

    public function getPaymentsByStatus($status)
    {
        $validStatuses = ['pending', 'completed', 'failed'];
        
        if (!in_array($status, $validStatuses)) {
            return response()->json(['message' => 'Invalid status'], 422);
        }

        $payments = DonorPayment::where('status', $status)
            ->with(['benefactor', 'team', 'employee'])
            ->orderBy('date_of_payment', 'desc')
            ->get();

        return response()->json($payments);
    }
}
