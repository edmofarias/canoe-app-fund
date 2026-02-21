<?php

namespace App\Http\Controllers;

use App\Models\FundManager;
use App\Services\FundManagerService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class FundManagerController extends Controller
{
    protected FundManagerService $fundManagerService;

    public function __construct(FundManagerService $fundManagerService)
    {
        $this->fundManagerService = $fundManagerService;
    }

    /**
     * Display a listing of fund managers.
     * GET /api/fund-managers
     */
    public function index()
    {
        try {
            $fundManagers = $this->fundManagerService->listFundManagers();
            return response()->json($fundManagers, 200);
        }
        catch (\Exception $e) {
            Log::error('Failed to list fund managers', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'message' => 'An error occurred while retrieving fund managers'
            ], 500);
        }
    }

    /**
     * Store a newly created fund manager.
     * POST /api/fund-managers
     */
    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'name' => 'required|string|max:255|unique:fund_managers,name',
            ]);

            $fundManager = $this->fundManagerService->createFundManager($validated);
            return response()->json($fundManager, 201);
        }
        catch (ValidationException $e) {
            throw $e;
        }
        catch (\Exception $e) {
            Log::error('Failed to create fund manager', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'request_data' => $request->all()
            ]);

            return response()->json([
                'message' => 'An error occurred while creating the fund manager'
            ], 500);
        }
    }

    /**
     * Soft delete the specified fund manager.
     * DELETE /api/fund-managers/{id}
     */
    public function destroy($id)
    {
        try {
            $fundManager = FundManager::find($id);

            if (!$fundManager) {
                return response()->json([
                    'message' => 'Fund manager not found'
                ], 404);
            }

            $this->fundManagerService->deleteFundManager($fundManager);

            return response()->json(null, 204);
        }
        catch (\Exception $e) {
            Log::error('Failed to delete fund manager', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'fund_manager_id' => $id
            ]);

            return response()->json([
                'message' => 'An error occurred while deleting the fund manager'
            ], 500);
        }
    }
}
