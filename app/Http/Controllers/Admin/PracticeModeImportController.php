<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PracticeMode;
use App\Services\PracticeModeImportService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class PracticeModeImportController extends Controller
{
    public function __construct(
        private PracticeModeImportService $importService
    ) {}

    /**
     * Show the import page.
     */
    public function index(): Response
    {
        $this->authorize('create', PracticeMode::class);

        return Inertia::render('admin/practice-modes/import');
    }

    /**
     * Validate JSON schema only.
     */
    public function validate(Request $request): JsonResponse
    {
        $this->authorize('create', PracticeMode::class);

        $json = $request->input('json');

        if (empty($json)) {
            return response()->json([
                'valid' => false,
                'errors' => ['JSON content is required'],
            ]);
        }

        $data = json_decode($json, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            return response()->json([
                'valid' => false,
                'errors' => ['Invalid JSON: '.json_last_error_msg()],
            ]);
        }

        $result = $this->importService->validateSchema($data);

        return response()->json($result);
    }

    /**
     * Test run - validate and show what would be created.
     */
    public function testRun(Request $request): JsonResponse
    {
        $this->authorize('create', PracticeMode::class);

        $json = $request->input('json');

        if (empty($json)) {
            return response()->json([
                'valid' => false,
                'errors' => ['JSON content is required'],
            ]);
        }

        $data = json_decode($json, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            return response()->json([
                'valid' => false,
                'errors' => ['Invalid JSON: '.json_last_error_msg()],
            ]);
        }

        $result = $this->importService->testRun($data);

        return response()->json($result);
    }

    /**
     * Execute the import.
     */
    public function import(Request $request): JsonResponse
    {
        $this->authorize('create', PracticeMode::class);

        $json = $request->input('json');

        if (empty($json)) {
            return response()->json([
                'success' => false,
                'errors' => ['JSON content is required'],
            ]);
        }

        $data = json_decode($json, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            return response()->json([
                'success' => false,
                'errors' => ['Invalid JSON: '.json_last_error_msg()],
            ]);
        }

        $result = $this->importService->import($data);

        return response()->json($result);
    }
}
