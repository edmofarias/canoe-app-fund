<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Services\CompanyService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class CompanyController extends Controller
{
    protected CompanyService $companyService;

    public function __construct(CompanyService $companyService)
    {
        $this->companyService = $companyService;
    }

    /**
     * Display a listing of companies.
     * GET /api/companies
     */
    public function index()
    {
        try {
            $companies = $this->companyService->listCompanies();
            return response()->json($companies, 200);
        }
        catch (\Exception $e) {
            Log::error('Failed to list companies', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'message' => 'An error occurred while retrieving companies'
            ], 500);
        }
    }

    /**
     * Store a newly created company.
     * POST /api/companies
     */
    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'name' => 'required|string|max:255|unique:companies,name',
            ]);

            $company = $this->companyService->createCompany($validated);
            return response()->json($company, 201);
        }
        catch (ValidationException $e) {
            throw $e;
        }
        catch (\Exception $e) {
            Log::error('Failed to create company', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'request_data' => $request->all()
            ]);

            return response()->json([
                'message' => 'An error occurred while creating the company'
            ], 500);
        }
    }

    /**
     * Soft delete the specified company.
     * DELETE /api/companies/{id}
     */
    public function destroy($id)
    {
        try {
            $company = Company::find($id);

            if (!$company) {
                return response()->json([
                    'message' => 'Company not found'
                ], 404);
            }

            $this->companyService->deleteCompany($company);

            return response()->json(null, 204);
        }
        catch (\Exception $e) {
            Log::error('Failed to delete company', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'company_id' => $id
            ]);

            return response()->json([
                'message' => 'An error occurred while deleting the company'
            ], 500);
        }
    }
}
