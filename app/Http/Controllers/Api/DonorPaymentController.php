<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\DonorPayment;
use App\Models\Benefactor;
use App\Models\VolunteerTeam;
use App\Models\Employee;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class DonorPaymentController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $payments = DonorPayment::with(['benefactor', 'team', 'employee'])->get();
        return response()->json($payments);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'benefactor_id' => 'required|exists:benefactors,id',
            'team_id' => 'required|exists:volunteer_teams,id',
            'employee_id' => 'required|exists:employees,id',
            'amount' => 'required|numeric|min:0',
            'date_of_payment' => 'required|date',
            'type' => 'required|in:cash,bank_transfer,check',
            'process_number' => 'required|string|unique:donor_payments',
            'status' => 'required|in:pending,App\roved,rejected',
            'image' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
        ]);

        // Handle image upload if present
        $imagePath = null;
        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('payment_images', 'public');
        }

        $payment = DonorPayment::create([
            'benefactor_id' => $request->benefactor_id,
            'team_id' => $request->team_id,
            'employee_id' => $request->employee_id,
            'amount' => $request->amount,
            'date_of_payment' => $request->date_of_payment,
            'type' => $request->type,
            'process_number' => $request->process_number,
            'status' => $request->status,
            'image' => $imagePath,
        ]);

        // Update team's financial record if payment is App\roved
        if ($request->status === 'App\roved') {
            $team = VolunteerTeam::findOrFail($request->team_id);
            $financial = $team->financial;
            
            if (!$financial) {
                $financial = $team->financial()->create(['total_amount' => 0]);
            }
            
            $financial->total_amount += $request->amount;
            $financial->save();
        }

        return response()->json($payment, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(DonorPayment $payment)
    {
        $payment->load(['benefactor', 'team', 'employee']);
        return response()->json($payment);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, DonorPayment $payment)
    {
        $request->validate([
            'benefactor_id' => 'sometimes|exists:benefactors,id',
            'team_id' => 'sometimes|exists:volunteer_teams,id',
            'employee_id' => 'sometimes|exists:employees,id',
            'amount' => 'sometimes|numeric|min:0',
            'date_of_payment' => 'sometimes|date',
            'type' => 'sometimes|in:cash,bank_transfer,check',
            'process_number' => 'sometimes|string|unique:donor_payments,process_number,' . $payment->id,
            'status' => 'sometimes|in:pending,App\roved,rejected',
            'image' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
        ]);

        // Store old values for financial adjustment
        $oldStatus = $payment->status;
        $oldAmount = $payment->amount;

        // Handle image update
        if ($request->hasFile('image')) {
            // Delete old image if exists
            if ($payment->image) {
                Storage::disk('public')->delete($payment->image);
            }
            $imagePath = $request->file('image')->store('payment_images', 'public');
            $payment->image = $imagePath;
        }

        $payment->update($request->except('image'));

        // Update team's financial record if status changed
        if ($request->has('status') || $request->has('amount')) {
            $team = VolunteerTeam::findOrFail($payment->team_id);
            $financial = $team->financial;
            
            if (!$financial) {
                $financial = $team->financial()->create(['total_amount' => 0]);
            }

            // If status changed from App\roved to something else
            if ($oldStatus === 'App\roved' && $request->status !== 'App\roved') {
                $financial->total_amount -= $oldAmount;
            }
            // If status changed to App\roved
            elseif ($request->status === 'App\roved' && $oldStatus !== 'App\roved') {
                $financial->total_amount += $request->amount;
            }
            // If amount changed while status is App\roved
            elseif ($request->status === 'App\roved' && $request->has('amount')) {
                $financial->total_amount -= $oldAmount;
                $financial->total_amount += $request->amount;
            }
            
            $financial->save();
        }

        return response()->json($payment);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(DonorPayment $payment)
    {
        // Delete associated image if exists
        if ($payment->image) {
            Storage::disk('public')->delete($payment->image);
        }

        // Update team's financial record if payment was App\roved
        if ($payment->status === 'App\roved') {
            $team = VolunteerTeam::findOrFail($payment->team_id);
            $financial = $team->financial;
            
            if ($financial) {
                $financial->total_amount -= $payment->amount;
                $financial->save();
            }
        }

        $payment->delete();
        return response()->json(null, 204);
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
        $validStatuses = ['pending', 'App\roved', 'rejected'];
        
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
