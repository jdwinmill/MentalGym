<?php

namespace App\Http\Controllers;

use App\Services\BlindSpotService;
use Inertia\Inertia;
use Inertia\Response;

class BlindSpotDashboardController extends Controller
{
    public function __construct(
        private BlindSpotService $service
    ) {}

    public function index(): Response
    {
        $user = auth()->user();
        $pageData = $this->service->getPageData($user);

        return Inertia::render('blind-spots/index', [
            'pageData' => $pageData->toArray(),
        ]);
    }
}
