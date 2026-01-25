<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\SkillDimension;
use Illuminate\Http\JsonResponse;

class SkillDimensionController extends Controller
{
    public function index(): JsonResponse
    {
        $dimensions = SkillDimension::active()
            ->ordered()
            ->get(['key', 'label', 'category']);

        return response()->json($dimensions);
    }
}
