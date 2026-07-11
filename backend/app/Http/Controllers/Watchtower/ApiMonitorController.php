<?php

namespace App\Http\Controllers\Watchtower;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ApiMonitorController extends Controller
{
    public function index()
    {
        return response()->json(['status' => 'ok']);
    }

    public function metrics()
    {
        return response()->json(['metrics' => []]);
    }

    public function degradation()
    {
        return response()->json(['degradation' => []]);
    }
}
