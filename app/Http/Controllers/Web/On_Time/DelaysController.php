<?php

namespace App\Http\Controllers\Web\On_Time;

use App\Http\Controllers\Controller;
use App\Services\On_Time\DelayCodesService;
use App\Services\On_Time\DelayService;
use App\Services\On_Time\DelayImportExportService;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use App\Http\Requests\On_Time\StoreDelayRequest;
use App\Http\Requests\On_Time\UpdateDelayRequest;
use App\Http\Requests\On_Time\StoreDelayCode;
use Illuminate\Http\Request;
use App\Services\On_Time\DelayImportValidationService;
use Illuminate\Support\Facades\Storage;

class DelaysController extends Controller
{
    protected DelayService $delayService;
    protected DelayCodesService $delayCodesService;
    protected DelayImportExportService $delayImportExportService;
    protected DelayImportValidationService $delayImportValidationService;

    public function __construct(
        DelayService $delayService, 
        DelayCodesService $delayCodesService,
        DelayImportExportService $delayImportExportService,
        DelayImportValidationService $delayImportValidationService
    )
    {
        $this->delayService = $delayService;
        $this->delayCodesService = $delayCodesService;
        $this->delayImportExportService = $delayImportExportService;
        $this->delayImportValidationService = $delayImportValidationService;
    }

    // Delay records actions

    public function index()
    {
        $data = $this->delayService->getDelaysIndex();
        return Inertia::render('Delays/Index', $data);
    }

    public function store(StoreDelayRequest $request)
    {
        $this->delayService->createDelay($request->validated());
        return back();
    }

    public function update(UpdateDelayRequest $request, $tenantSlug, $id)
    {
        $this->delayService->updateDelay($id, $request->validated());
        return back();
    }

    public function updateAdmin(UpdateDelayRequest $request, $id)
    {
        $this->delayService->updateDelay($id, $request->validated());
        return back();
    }

    public function destroy($tenantSlug, $id)
    {
        $this->delayService->deleteDelay($id);
        return back();
    }

    public function destroyAdmin($id)
    {
        $this->delayService->deleteDelay($id);
        return back();
    }

    public function destroyBulk(Request $request, $tenantSlug = null)
    {
        $ids = $request->input('ids', []);
        $this->delayService->deleteMultipleDelays($ids);
        return back();
    }

    public function destroyBulkAdmin(Request $request)
    {
        $ids = $request->input('ids', []);
        $this->delayService->deleteMultipleDelays($ids);
        return back();
    }

    // Delay code actions

    public function storeCode(StoreDelayCode $request)
    {
        $this->delayCodesService->createDelayCode($request->validated());
        return back();
    }

    public function destroyCode($id)
    {
        $this->delayCodesService->deleteDelayCode($id);
        return back();
    }

    // New endpoints for SuperAdmin

    /**
     * Restore a soft-deleted delay code.
     */
    public function restoreCode($id)
    {
        $this->delayCodesService->restoreDelayCode($id);
        return back();
    }

    /**
     * Permanently force delete a delay code.
     */
    public function forceDeleteCode($id)
    {
        $this->delayCodesService->forceDeleteDelayCode($id);
        return back();
    }

   public function validateImport(Request $request, $tenantSlug = null)
{
    $request->validate([
        'file' => 'required|file|mimes:csv,txt|max:10240',
    ]);

    try {
        $results = $this->delayImportValidationService->validateDelaysCsv($request->file('file'));

        if (isset($results['header_error'])) {
            session()->forget('ontime_import_validation_results');
            session()->forget('ontime_import_file_path');

            return back()->with('importValidation', [
                'success' => false,
                'header_error' => $results['header_error'],
                'results' => $results,
            ]);
        }

        session(['ontime_import_validation_results' => $results]);

        // Only store the file if everything is valid
        if (($results['summary']['invalid'] ?? 0) === 0) {
            $path = $request->file('file')->store('temp-imports');
            session(['ontime_import_file_path' => $path]);
        } else {
            session()->forget('ontime_import_file_path');
        }

        return back()->with('importValidation', [
            'success' => true,
            'results' => $results,
        ]);
    } catch (\Exception $e) {
        session()->forget('ontime_import_validation_results');
        session()->forget('ontime_import_file_path');

        return back()->with('importValidation', [
            'success' => false,
            'message' => $e->getMessage(),
        ]);
    }
}

public function confirmImport(Request $request, $tenantSlug = null)
{
    try {
        $filePath = session('ontime_import_file_path');

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

        // DelayImportExportService expects csv_file
        $importRequest = new Request();
        $importRequest->files->set('csv_file', $file);

        $this->delayImportExportService->importDelays($importRequest);

        Storage::delete($filePath);
        session()->forget(['ontime_import_file_path', 'ontime_import_validation_results']);

        return back()->with('success', 'Delays imported successfully.');
    } catch (\Exception $e) {
        return back()->with('error', 'Import failed: ' . $e->getMessage());
    }
}

public function downloadErrorReport(Request $request, $tenantSlug = null)
{
    try {
        $results = session('ontime_import_validation_results');

        if (!$results || empty($results['invalid'])) {
            return back()->with('error', 'No validation errors to download.');
        }

        $filePath = $this->delayImportValidationService->generateErrorReport($results['invalid']);

        return response()->download($filePath)->deleteFileAfterSend(true);
    } catch (\Exception $e) {
        return back()->with('error', 'Failed to generate error report: ' . $e->getMessage());
    }
}


    /**
     * Export delays to CSV file.
     */
    public function export($tenantSlug = null)
    {
        try {
            return $this->delayImportExportService->exportDelays();
        } catch (\Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    /**
     * Export delays to CSV file for admin.
     */
    public function exportAdmin()
    {
        try {
            return $this->delayImportExportService->exportDelays();
        } catch (\Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }
}
