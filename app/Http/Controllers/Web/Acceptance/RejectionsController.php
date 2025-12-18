<?php

namespace App\Http\Controllers\Web\Acceptance;

use App\Http\Controllers\Controller;
use App\Services\Acceptance\RejectionReasonCodesService;
use App\Services\Acceptance\{RejectionImportExportService, RejectionImportValidationService};
use Illuminate\Http\Request;
use App\Http\Requests\Acceptance\StoreRejectionRequest;
use App\Http\Requests\Acceptance\UpdateRejectionRequest;
use App\Services\Acceptance\RejectionService;
use Inertia\Inertia;
use App\Http\Requests\Acceptance\StoreRejectionReasonCode;
use Illuminate\Support\Facades\Storage;
/**
 * Class RejectionsController
 *
 * This controller manages rejection entries and rejection reason codes.
 * It delegates logic to the RejectionService.
 *
 * Command:
 *   php artisan make:controller Web/RejectionsController
 */
class RejectionsController extends Controller
{
    protected RejectionService $rejectionService;
    protected RejectionReasonCodesService $rejectionReasonCodesService;
    protected RejectionImportExportService $rejectionImportExportService;
    protected RejectionImportValidationService $rejectionImportValidationService;

    /**
     * Constructor.
     *
     * @param RejectionService $rejectionService Service for rejection processing.
     * @param RejectionReasonCodesService $rejectionReasonCodesService
     * @param RejectionImportExportService $rejectionImportExportService
     * @param RejectionImportValidationService $rejectionImportValidationService
     */
    public function __construct(
        RejectionService $rejectionService, 
        RejectionReasonCodesService $rejectionReasonCodesService,
        RejectionImportExportService $rejectionImportExportService,
        RejectionImportValidationService $rejectionImportValidationService
    )
    {
        $this->rejectionService = $rejectionService;
        $this->rejectionReasonCodesService = $rejectionReasonCodesService;
        $this->rejectionImportExportService = $rejectionImportExportService;
        $this->rejectionImportValidationService = $rejectionImportValidationService;
    }

    /**
     * Display a list of rejections.
     *
     * @return \Inertia\Response
     */
    public function index()
    {
        $data = $this->rejectionService->getRejectionsIndex();
        return Inertia::render('Rejections/Index', $data);
    }

    /**
     * Store a new rejection.
     *
     * @param StoreRejectionRequest $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(StoreRejectionRequest $request)
    {
        
        $this->rejectionService->createRejection($request->validated());
        return back();
    }

    /**
     * Update an existing rejection.
     *
     * @param UpdateRejectionRequest $request
     * @param string $tenantSlug
     * @param int $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(UpdateRejectionRequest $request, $tenantSlug, $id)
    {
        
        $this->rejectionService->updateRejection($id, $request->validated());
        return back();
    }

    /**
     * Update a rejection as Admin.
     *
     * @param UpdateRejectionRequest $request
     * @param int $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function updateAdmin(UpdateRejectionRequest $request, $id)
    {
        
        $this->rejectionService->updateRejection($id, $request->validated());
        return back();
    }

    /**
     * Delete a rejection.
     *
     * @param string $tenantSlug
     * @param int $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy($tenantSlug, $id)
    {
        $this->rejectionService->deleteRejection($id);
        return back();
    }

    /**
     * Delete a rejection as Admin.
     *
     * @param int $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroyAdmin($id)
    {
        $this->rejectionService->deleteRejection($id);
        return back();
    }

    /**
 * Create a new rejection reason code.
 *
 * @param \App\Http\Requests\Acceptance\StoreRejectionReasonCode $request
 * @return \Illuminate\Http\RedirectResponse
 */
public function storeCode(StoreRejectionReasonCode $request)
{
    // The incoming request will be automatically validated by the StoreRejectionReasonCode request class
    // No need to manually validate, just access the validated data
    $this->rejectionReasonCodesService->createReasonCode($request->validated());

    // Redirect back after successfully creating the reason code
    return back();
}

    /**
     * Delete a rejection reason code.
     *
     * @param int $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroyCode($id)
    {
        $this->rejectionReasonCodesService->deleteReasonCode($id);
        return back();
    }

    /**
     * Restore a soft-deleted rejection reason code.
     *
     * @param int $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function restoreCode($id)
    {
        $this->rejectionReasonCodesService->restoreReasonCode($id);
        return back();
    }

    /**
     * Permanently force delete a rejection reason code.
     *
     * @param int $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function forceDeleteCode($id)
    {
        $this->rejectionReasonCodesService->forceDeleteReasonCode($id);
        return back();
    }

    /**
     * Delete multiple rejection records.
     *
     * @param Request $request
     * @param string|null $tenantSlug
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroyBulk(Request $request, $tenantSlug = null)
    {
        $ids = $request->input('ids', []);
        $this->rejectionService->deleteMultipleRejections($ids);
        return redirect()->back()->with('success', 'Rejections deleted successfully.');
    }

    /**
     * Delete multiple rejection records as Admin.
     *
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroyBulkAdmin(Request $request)
    {
        $ids = $request->input('ids', []);
        $this->rejectionService->deleteMultipleRejections($ids);
        return redirect()->back()->with('success', 'Rejections deleted successfully.');
    }

    public function validateImport(Request $request, $tenantSlug = null)
{
    $request->validate([
        'file' => 'required|file|mimes:csv,txt|max:10240',
    ]);

    try {
        $results = $this->rejectionImportValidationService
            ->validateRejectionsCsv($request->file('file'));

        if (isset($results['header_error'])) {
            session()->forget('acceptance_import_validation_results');
            session()->forget('acceptance_import_file_path');

            return back()->with('importValidation', [
                'success' => false,
                'header_error' => $results['header_error'],
                'results' => $results,
            ]);
        }

        session(['acceptance_import_validation_results' => $results]);

        if (($results['summary']['invalid'] ?? 0) === 0) {
            $path = $request->file('file')->store('temp-imports');
            session(['acceptance_import_file_path' => $path]);
        } else {
            session()->forget('acceptance_import_file_path');
        }

        return back()->with('importValidation', [
            'success' => true,
            'results' => $results,
        ]);
    } catch (\Exception $e) {
        session()->forget('acceptance_import_validation_results');
        session()->forget('acceptance_import_file_path');

        return back()->with('importValidation', [
            'success' => false,
            'message' => $e->getMessage(),
        ]);
    }
}
public function confirmImport(Request $request, $tenantSlug = null)
{
    try {
        $filePath = session('acceptance_import_file_path');

        if (!$filePath || !Storage::exists($filePath)) {
            return back()->with('error', 'Import session expired. Please upload the file again.');
        }

        $storedFile = Storage::path($filePath);

        $file = new \Illuminate\Http\UploadedFile(
            $storedFile,
            basename($filePath),
            mime_content_type($storedFile),
            null,
            true
        );

        $importRequest = new Request();
        $importRequest->files->set('csv_file', $file);

        $this->rejectionImportExportService->importRejections($importRequest);

        Storage::delete($filePath);
        session()->forget(['acceptance_import_file_path', 'acceptance_import_validation_results']);

        return back()->with('success', 'Rejections imported successfully.');
    } catch (\Exception $e) {
        return back()->with('error', 'Import failed: ' . $e->getMessage());
    }
}
public function downloadErrorReport(Request $request, $tenantSlug = null)
{
    try {
        $results = session('acceptance_import_validation_results');

        if (!$results || empty($results['invalid'])) {
            return back()->with('error', 'No validation errors to download.');
        }

        $filePath = $this->rejectionImportValidationService
            ->generateErrorReport($results['invalid']);

        return response()->download($filePath)->deleteFileAfterSend(true);
    } catch (\Exception $e) {
        return back()->with('error', 'Failed to generate error report: ' . $e->getMessage());
    }
}


    /**
     * Export rejections to CSV file.
     */
    public function export($tenantSlug = null)
    {
        return $this->rejectionImportExportService->exportRejections();
    }

    /**
     * Export rejections to CSV file for admin.
     */
    public function exportAdmin()
    {
        return $this->rejectionImportExportService->exportRejections();
    }
}
