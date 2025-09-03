<?php

namespace App\Modules\POS\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\POS\Services\ReportService;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    private $reportService;
    
    public function __construct(ReportService $reportService)
    {
        $this->reportService = $reportService;
    }
    
    public function dailySales(Request $request)
    {
        $date = $request->get('date');
        $report = $this->reportService->getDailySalesReport($date);
        
        return response()->json([
            'success' => true,
            'data' => $report
        ]);
    }
    
    public function monthlySales(Request $request)
    {
        $month = $request->get('month');
        $year = $request->get('year');
        $report = $this->reportService->getMonthlySalesReport($month, $year);
        
        return response()->json([
            'success' => true,
            'data' => $report
        ]);
    }
}