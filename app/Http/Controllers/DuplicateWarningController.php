<?php

namespace App\Http\Controllers;

use App\Services\DuplicateWarningService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class DuplicateWarningController extends Controller
{
    protected DuplicateWarningService $duplicateWarningService;

    public function __construct(DuplicateWarningService $duplicateWarningService)
    {
        $this->duplicateWarningService = $duplicateWarningService;
    }

    /**
     * Display a listing of unresolved duplicate warnings.
     * GET /api/duplicate-warnings
     */
    public function index()
    {
        try {
            $warnings = $this->duplicateWarningService->listUnresolvedWarnings();
            return response()->json($warnings, 200);
        }
        catch (\Exception $e) {
            Log::error('Failed to list duplicate warnings', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'message' => 'An error occurred while retrieving duplicate warnings'
            ], 500);
        }
    }
}
