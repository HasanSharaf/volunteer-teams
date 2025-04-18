<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Financial;
use App\Http\Resources\FinancialResource;
use Illuminate\Http\Request;

class FinancialController extends Controller
{
    public function index()
    {
        $financials = Financial::paginate(10);
        return FinancialResource::collection($financials);
    }

    public function store(Request $request)
    {
        $request->validate([
            'total_amount' => 'required|numeric|min:0',
            'team_id' => 'required|exists:volunteer_teams,id',
        ]);

        $financial = Financial::create($request->all());
        return new FinancialResource($financial);
    }

    public function show(Financial $financial)
    {
        return new FinancialResource($financial);
    }

    public function update(Request $request, Financial $financial)
    {
        $request->validate([
            'total_amount' => 'sometimes|numeric|min:0',
            'team_id' => 'sometimes|exists:volunteer_teams,id',
        ]);

        $financial->update($request->all());
        return new FinancialResource($financial);
    }

    public function destroy(Financial $financial)
    {
        $financial->delete();
        return response()->json(['message' => 'Financial record deleted successfully']);
    }

    public function getTeamFinancial($teamId)
    {
        $financial = Financial::where('team_id', $teamId)->firstOrFail();
        return new FinancialResource($financial);
    }
} 