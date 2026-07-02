<?php

namespace App\Http\Controllers\Watchtower;

use App\Http\Controllers\Controller;
use App\Services\Watchtower\PluginHealthService;
use Illuminate\Http\Request;

class PluginHealthController extends Controller
{
    protected $pluginHealth;

    public function __construct(PluginHealthService $pluginHealth)
    {
        $this->pluginHealth = $pluginHealth;
    }

    public function all()
    {
        return response()->json([
            'success' => true,
            'data' => $this->pluginHealth->getPluginHealth(),
            'timestamp' => now()->toISOString(),
        ]);
    }

    public function show(Request $request, string $name)
    {
        $plugin = $this->pluginHealth->getPlugin($name);

        if (!$plugin['exists']) {
            return response()->json([
                'success' => false,
                'error' => $plugin['error'],
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $plugin['data'],
            'timestamp' => now()->toISOString(),
        ]);
    }
}
