<?php

namespace App\Http\Controllers\Web\Performance;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Requests\Performance\StorePerformanceRequest;
use App\Http\Requests\Performance\UpdatePerformanceRequest;
use App\Services\Performance\PerformanceImportExportService;
use App\Services\Performance\PerformanceService;
use Inertia\Inertia;
use Illuminate\Support\Facades\Auth;
use App\Services\Performance\PerformanceImportValidationService;
use Illuminate\Support\Facades\Storage;

/**
 * Class PerformanceController
 *
 * This controller handles Performance records including CRUD, import/export,
 * and rating calculations. It delegates business logic to PerformanceService.
 *
 * Command:
 *   php artisan make:controller Web/PerformanceController
 */
class PerformanceController extends Controller
{
    protected PerformanceService $PerformanceService;
    protected PerformanceImportExportService $PerformanceImportExportService;
    protected PerformanceImportValidationService $PerformanceImportValidationService;
    /**
     * Constructor.
     *
     * @param PerformanceService $PerformanceService Service for Performance operations.
     * @param PerformanceImportExportService $PerformanceImportExportService
     */
    public function __construct(PerformanceService $PerformanceService, PerformanceImportExportService $PerformanceImportExportService, PerformanceImportValidationService $PerformanceImportValidationService)
    {
        $this->PerformanceService = $PerformanceService;
        $this->PerformanceImportExportService = $PerformanceImportExportService;
        $this->PerformanceImportValidationService = $PerformanceImportValidationService;
    }

    /**
     * Display the Performance records.
     *
     * @param Request $request
     * @return \Inertia\Response
     */
    public function index(Request $request)
    {
        $data = $this->PerformanceService->getPerformanceIndex();
        return Inertia::render('Performance/Index', $data);
    }

    /**
     * Store a new Performance record.
     *
     * @param StorePerformanceRequest $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(StorePerformanceRequest $request)
    {
        $this->PerformanceService->storePerformance($request->validated());
        return redirect()->back()->with('success', 'Performance saved successfully.');
    }

    /**
     * Update an existing Performance record.
     *
     * @param UpdatePerformanceRequest $request
     * @param string $tenantSlug
     * @param int $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(UpdatePerformanceRequest $request, $tenantSlug, $id)
    {
        
        $this->PerformanceService->updatePerformance($id, $request->validated());
        return redirect()->back()->with('success', 'Performance updated successfully.');
    }

    /**
     * Update a Performance record as Admin.
     *
     * @param UpdatePerformanceRequest $request
     * @param int $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function adminUpdate(UpdatePerformanceRequest $request, $id)
    {
        
        $this->PerformanceService->updatePerformance($id, $request->validated());
        return redirect()->back()->with('success', 'Performance updated by admin successfully.');
    }

    /**
     * Delete a Performance record.
     *
     * @param string $tenantSlug
     * @param int $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy($tenantSlug, $id)
    {
        $this->PerformanceService->deletePerformance($id, Auth::user()->tenant_id);
        return redirect()->back()->with('success', 'Performance deleted successfully.');
    }

    /**
     * Delete a Performance record as Admin.
     *
     * @param int $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function adminDestroy($id)
    {
        $this->PerformanceService->deletePerformance($id);
        return redirect()->back()->with('success', 'Performance deleted by admin.');
    }

     /**
     * Step 1: Validate CSV file (NO import yet)
     */
public function validateImport(Request $request, $tenantSlug = null)
{
    $request->validate([
        'file' => 'required|file|mimes:csv,txt|max:10240',
    ]);

    try {
        $results = $this->PerformanceImportValidationService
            ->validatePerformancesCsv($request->file('file'));

        // Header error
        if (isset($results['header_error'])) {
            // clear stale sessions
            session()->forget('performance_import_validation_results');
            session()->forget('performance_import_file_path');

            return back()->with('importValidation', [
                'success' => false,
                'header_error' => $results['header_error'],
                'results' => $results,
            ]);
        }

        // Store results for error report download / confirm step
        session(['performance_import_validation_results' => $results]);

        // Store file temporarily ONLY if fully valid
        if (($results['summary']['invalid'] ?? 0) === 0) {
            $path = $request->file('file')->store('temp-imports');
            session(['performance_import_file_path' => $path]);
        } else {
            session()->forget('performance_import_file_path');
        }

        return back()->with('importValidation', [
            'success' => true,
            'results' => $results,
        ]);
    } catch (\Exception $e) {
        session()->forget('performance_import_validation_results');
        session()->forget('performance_import_file_path');

        return back()->with('importValidation', [
            'success' => false,
            'message' => $e->getMessage(),
        ]);
    }
}


    /**
     * Step 2: Confirm import (only after successful validation)
     */
    public function confirmImport(Request $request, $tenantSlug = null)
    {
        try {
            $filePath = session('performance_import_file_path');

            if (!$filePath || !Storage::exists($filePath)) {
                return back()->with('error', 'Import session expired. Please upload the file again.');
            }

            $storedFile = Storage::path($filePath);

            // Create UploadedFile and pass it to the existing import service (expects csv_file)
            $file = new \Illuminate\Http\UploadedFile(
                $storedFile,
                basename($filePath),
                mime_content_type($storedFile),
                null,
                true
            );

            $importRequest = new Request();
            $importRequest->files->set('csv_file', $file);

            // IMPORTANT: if your import logic depends on Auth tenant, it will still work
            $this->PerformanceImportExportService->importPerformances($importRequest);

            // cleanup
            Storage::delete($filePath);
            session()->forget(['performance_import_file_path', 'performance_import_validation_results']);

            return back()->with('success', 'Performances imported successfully.');
        } catch (\Exception $e) {
            return back()->with('error', 'Import failed: ' . $e->getMessage());
        }
    }

    /**
     * Download error report CSV
     */
    public function downloadErrorReport(Request $request, $tenantSlug = null)
    {
        try {
            $results = session('performance_import_validation_results');

            if (!$results || empty($results['invalid'])) {
                return back()->with('error', 'No validation errors to download.');
            }

            $filePath = $this->PerformanceImportValidationService
                ->generateErrorReport($results['invalid']);

            return response()->download($filePath)->deleteFileAfterSend(true);
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to generate error report: ' . $e->getMessage());
        }
    }

    /**
     * Export Performance records.
     *
     * @return \Symfony\Component\HttpFoundation\BinaryFileResponse
     */
    public function export()
    {
        return $this->PerformanceImportExportService->exportPerformances();
    }

    /**
     * Delete multiple performance entries.
     *
     * @param Request $request
     * @param string|null $tenantSlug
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroyBulk(Request $request, $tenantSlug = null)
    {
        $ids = $request->input('ids', []);
        $this->PerformanceService->deleteMultiplePerformances($ids, Auth::user()->tenant_id);
        return redirect()->back()->with('success', 'Performance records deleted successfully.');
    }

    /**
     * Delete multiple performance entries as Admin.
     *
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroyBulkAdmin(Request $request)
    {
        $ids = $request->input('ids', []);
        $this->PerformanceService->deleteMultiplePerformances($ids);
        return redirect()->back()->with('success', 'Performance records deleted successfully.');
    }
}
