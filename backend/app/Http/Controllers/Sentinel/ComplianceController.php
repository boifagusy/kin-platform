<?php

namespace App\Http\Controllers\Sentinel;

use App\Http\Controllers\Controller;
use App\Services\Sentinel\ComplianceReportService;
use Illuminate\Http\Request;

class ComplianceController extends Controller
{
    protected $reportService;

    public function __construct(ComplianceReportService $reportService)
    {
        $this->reportService = $reportService;
    }

    public function index()
    {
        $report = $this->reportService->generateReport('overview');
        
        return view('sentinel.compliance', compact('report'));
    }

    public function show(Request $request, string $type)
    {
        $report = $this->reportService->generateReport($type, $request->all());
        
        if ($request->has('export')) {
            $format = $request->get('format', 'json');
            $content = $this->reportService->exportReport($type, $format, $request->all());
            
            return response($content)
                ->header('Content-Type', $format === 'json' ? 'application/json' : 'text/csv')
                ->header('Content-Disposition', "attachment; filename=security-report-{$type}.{$format}");
        }

        return view('sentinel.compliance', compact('report'));
    }

    public function security()
    {
        $report = $this->reportService->generateReport('security');
        return view('sentinel.compliance', compact('report'));
    }

    public function settings()
    {
        $rules = \App\Models\SecurityAlertRule::all();
        return view('sentinel.settings', compact('rules'));
    }
}
