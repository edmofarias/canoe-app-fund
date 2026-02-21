<?php

namespace App\Http\Controllers;

use App\Services\FundService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class FundController extends Controller
{
    protected FundService $fundService;

    public function __construct(FundService $fundService)
    {
        $this->fundService = $fundService;
    }

    /**
     * Display a listing of funds with optional filters.
     * GET /api/funds
     */
    public function index(Request $request)
    {
        try {
            $filters = $request->only(['name', 'fund_manager_id', 'start_year', 'company_id']);
            $funds = $this->fundService->listFunds($filters);

            return response()->json($funds, 200);
        }
        catch (\Exception $e) {
            Log::error('Failed to list funds', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'filters' => $request->only(['name', 'fund_manager_id', 'start_year', 'company_id'])
            ]);

            return response()->json([
                'message' => 'An error occurred while retrieving funds'
            ], 500);
        }
    }

    /**
     * Store a newly created fund.
     * POST /api/funds
     */
    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'start_year' => 'required|integer',
                'fund_manager_id' => 'required|integer|exists:fund_managers,id',
                'aliases' => 'nullable|array',
                'aliases.*' => 'string|max:255|unique:aliases,name',
                'company_ids' => 'nullable|array',
                'company_ids.*' => 'integer|exists:companies,id',
            ]);

            $fund = $this->fundService->createFund($validated);
            return response()->json($fund, 201);
        }
        catch (ValidationException $e) {
            throw $e;
        }
        catch (\Exception $e) {
            Log::error('Failed to create fund', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'request_data' => $request->all()
            ]);

            return response()->json([
                'message' => 'An error occurred while creating the fund'
            ], 500);
        }
    }

    /**
     * Display the specified fund.
     * GET /api/funds/{id}
     */
    public function show($id)
    {
        try {
            $fund = $this->fundService->getFund($id);

            if (!$fund) {
                return response()->json([
                    'message' => 'Fund not found'
                ], 404);
            }

            return response()->json($fund, 200);
        }
        catch (\Exception $e) {
            Log::error('Failed to retrieve fund', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'fund_id' => $id
            ]);

            return response()->json([
                'message' => 'An error occurred while retrieving the fund'
            ], 500);
        }
    }

    /**
     * Update the specified fund.
     * PUT /api/funds/{id}
     */
    public function update(Request $request, $id)
    {
        try {
            $fund = $this->fundService->getFund($id);

            if (!$fund) {
                return response()->json([
                    'message' => 'Fund not found'
                ], 404);
            }

            $validated = $request->validate([
                'name' => 'sometimes|required|string|max:255',
                'start_year' => 'sometimes|required|integer',
                'fund_manager_id' => 'sometimes|required|integer|exists:fund_managers,id',
                'aliases' => 'nullable|array',
                'aliases.*' => 'string|max:255',
                'company_ids' => 'nullable|array',
                'company_ids.*' => 'integer|exists:companies,id',
            ]);

            $fund = $this->fundService->updateFund($fund, $validated);
            return response()->json($fund, 200);
        }
        catch (ValidationException $e) {
            throw $e;
        }
        catch (\Exception $e) {
            Log::error('Failed to update fund', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'fund_id' => $id,
                'request_data' => $request->all()
            ]);

            return response()->json([
                'message' => 'An error occurred while updating the fund'
            ], 500);
        }
    }

    /**
     * Soft delete the specified fund.
     * DELETE /api/funds/{id}
     */
    public function destroy($id)
    {
        try {
            $fund = $this->fundService->getFund($id);

            if (!$fund) {
                return response()->json([
                    'message' => 'Fund not found'
                ], 404);
            }

            $this->fundService->deleteFund($fund);

            return response()->json(null, 204);
        }
        catch (\Exception $e) {
            Log::error('Failed to delete fund', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'fund_id' => $id
            ]);

            return response()->json([
                'message' => 'An error occurred while deleting the fund'
            ], 500);
        }
    }
}
