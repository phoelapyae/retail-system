<?php

namespace App\Modules\POS\Services;

use App\Modules\POS\Models\{PosSale, Register};
use Carbon\Carbon;

class ReportService
{
    public function getDailySalesReport($date = null)
    {
        $date = $date ? Carbon::parse($date) : today();
        
        $sales = PosSale::whereDate('created_at', $date)
            ->with('items', 'customer')
            ->get();
            
        return [
            'date' => $date->format('Y-m-d'),
            'total_sales' => $sales->count(),
            'total_revenue' => $sales->sum('total_amount'),
            'total_tax' => $sales->sum('tax_amount'),
            'payment_methods' => $sales->groupBy('payment_method')->map->count(),
            'hourly_breakdown' => $this->getHourlyBreakdown($sales)
        ];
    }
    
    public function getMonthlySalesReport($month = null, $year = null)
    {
        $month = $month ?? date('m');
        $year = $year ?? date('Y');
        
        $sales = PosSale::whereMonth('created_at', $month)
            ->whereYear('created_at', $year)
            ->get();
            
        return [
            'month' => $month,
            'year' => $year,
            'total_sales' => $sales->count(),
            'total_revenue' => $sales->sum('total_amount'),
            'daily_breakdown' => $this->getDailyBreakdown($sales, $month, $year)
        ];
    }
    
    private function getHourlyBreakdown($sales)
    {
        return $sales->groupBy(function($sale) {
            return $sale->created_at->format('H');
        })->map(function($hourSales) {
            return [
                'count' => $hourSales->count(),
                'revenue' => $hourSales->sum('total_amount')
            ];
        });
    }
    
    private function getDailyBreakdown($sales, $month, $year)
    {
        $daysInMonth = Carbon::createFromDate($year, $month, 1)->daysInMonth;
        $breakdown = [];
        
        for ($day = 1; $day <= $daysInMonth; $day++) {
            $daySales = $sales->filter(function($sale) use ($day) {
                return $sale->created_at->day == $day;
            });
            
            $breakdown[$day] = [
                'sales_count' => $daySales->count(),
                'revenue' => $daySales->sum('total_amount')
            ];
        }
        
        return $breakdown;
    }
}