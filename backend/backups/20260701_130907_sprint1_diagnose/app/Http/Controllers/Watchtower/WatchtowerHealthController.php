<?php

namespace App\Http\Controllers\Watchtower;

use App\Http\Controllers\Controller;
use App\Services\Watchtower\WatchtowerHealthService;
use Illuminate\Http\Request;

class WatchtowerHealthController extends Controller
{
    protected $watchtowerHealth;

    public function __construct(WatchtowerHealthService $watchtowerHealth)
    {
        $this->watchtowerHealth = $watchtowerHealth;
    }

    public function index()
    {
        return response()->json([
            'success' => true,
            'data' => $this->watchtowerHealth->getHealth(),
            'timestamp' => now()->toISOString(),
        ]);
    }
}
