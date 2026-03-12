<?php

namespace App\Http\Controllers\Web\On_Time;

use App\Http\Controllers\Controller;
use App\Services\On_Time\DelayService;
use App\Services\On_Time\DelayImportExportService;
use App\Services\On_Time\DelayImportValidationService;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use App\Http\Requests\On_Time\StoreDelayRequest;
use App\Http\Requests\On_Time\UpdateDelayRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class DelaysController extends Controller
{
    protected DelayService $delayService;
    protected DelayImportExportService $delayImportExportService;
    protected DelayImportValidationService $delayImportValidationService;

    public function __construct(
        DelayService $delayService,
        DelayImportExportService $delayImportExportService,
        DelayImportValidationService $delayImportValidationService
    ) {
        $this->delayService = $delayService;
        $this->delayImportExportService = $delayImportExportService;
        $this->delayImportValidationService = $delayImportValidationService;
    }

    // ─── CRUD ───────────────────────────────────────────

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
        $this->delayService->deleteMultipleDelays($request->input('ids', []));
        return back();
    }

    public function destroyBulkAdmin(Request $request)
    {
        $this->delayService->deleteMultipleDelays($request->input('ids', []));
        return back();
    }

    // ─── Import ─────────────────────────────────────────

    public function validateImport(Request $request, $tenantSlug = null)
    {
        $request->validate([
            'file' => 'required|file|mimes:csv,txt|max:10240',
            'import_type' => 'required|in:origin,destination',
        ]);

        $isSuperAdmin = is_null(Auth::user()->tenant_id);

        // Super admin must pass tenant_id explicitly
        if ($isSuperAdmin) {
            $request->validate(['tenant_id' => 'required|exists:tenants,id']);
            session(['ontime_import_tenant_id' => $request->input('tenant_id')]);
        }

        try {
            $results = $this->delayImportValidationService->validateDelaysCsv(
                $request->file('file'),
                $request->input('import_type')
            );

            if (isset($results['header_error'])) {
                session()->forget(['ontime_import_validation_results', 'ontime_import_file_path', 'ontime_import_type', 'ontime_import_tenant_id']);
                return back()->with('importValidation', [
                    'success' => false,
                    'header_error' => $results['header_error'],
                    'results' => $results,
                ]);
            }

            session(['ontime_import_validation_results' => $results]);
            session(['ontime_import_type' => $request->input('import_type')]);

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
            session()->forget(['ontime_import_validation_results', 'ontime_import_file_path', 'ontime_import_type', 'ontime_import_tenant_id']);
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
            $importType = session('ontime_import_type');
            $isSuperAdmin = is_null(Auth::user()->tenant_id);
            $correctedRows = $request->input('corrected_rows', []); // ✅ NEW

            if (!$filePath || !Storage::exists($filePath)) {
                return back()->with('error', 'Import session expired. Please upload the file again.');
            }

            $tenantId = $isSuperAdmin
                ? session('ontime_import_tenant_id')
                : Auth::user()->tenant_id;

            if (!$tenantId) {
                return back()->with('error', 'Could not resolve tenant for import.');
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
            $importRequest->merge([
                'import_type' => $importType,
                'tenant_id' => $tenantId,
                'corrected_rows' => $correctedRows, // ✅ NEW
            ]);

            $this->delayImportExportService->importDelays($importRequest);

            Storage::delete($filePath);
            session()->forget([
                'ontime_import_file_path',
                'ontime_import_validation_results',
                'ontime_import_type',
                'ontime_import_tenant_id'
            ]);

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

    // ─── Export ─────────────────────────────────────────

    public function export($tenantSlug = null)
    {
        try {
            return $this->delayImportExportService->exportDelays();
        } catch (\Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    public function exportAdmin()
    {
        try {
            return $this->delayImportExportService->exportDelays();
        } catch (\Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }
}
