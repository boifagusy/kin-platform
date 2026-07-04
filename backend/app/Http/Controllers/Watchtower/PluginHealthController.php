<?php

namespace App\Http\Controllers\Watchtower;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class PluginHealthController extends Controller
{
    public function index()
    {
        return response()->json(['status' => 'ok']);
    }
}
