<?php

namespace App\Jobs;

use App\Models\Report;
use App\Models\ReportData;
use Illuminate\Bus\Queueable;
use Illuminate\Support\Facades\Http;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\SerializesModels;
use App\Services\CurrencyService;

class GenerateReportJob implements ShouldQueue
{
    use Dispatchable, Queueable, SerializesModels;

    public function handle(CurrencyService $currencyService)
    {
        $reports = Report::where('status', 'pending')->get();

        foreach ($reports as $report) {

            try {
                // Step 1: Determine date range
                [$startDate, $endDate] = $this->getDateRange($report->range);

                // Step 2: Generate interval dates
                $dates = $this->generateDates($startDate, $endDate, $report->interval);

                // Step 3: Fetch base rate (current)
                $rate = $this->fetchCurrentRate($currencyService, $report->currency);

                // Step 4: Insert data
                $this->storeReportData($report->id, $dates, $rate);

                // Step 5: Mark completed
                $report->update(['status' => 'completed']);
            } catch (\Throwable $e) {
                // Optional: mark failed
                $report->update(['status' => 'failed']);            
                // Optional logging
                \Log::error("Report {$report->id} failed: " . $e->getMessage());
            }
        }
    }

    private function fetchCurrentRate(CurrencyService $currencyService, string $currency): float
    {
        $response = $currencyService->getRates([$currency]);

        return $response['quotes']['USD' . $currency] ?? 1;
    }

    private function storeReportData(int $reportId, array $dates, float $rate): void
    {
        $rows = [];

        foreach ($dates as $date) {
            $rows[] = [
                'report_id' => $reportId,
                'date' => $date,
                'rate' => $rate,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        ReportData::insert($rows);
    }

    private function getDateRange($range)
    {
        $end = now();

        return match ($range) {
            '1y' => [$end->copy()->subYear(), $end],
            '6m' => [$end->copy()->subMonths(6), $end],
            '1m' => [$end->copy()->subMonth(), $end],
        };
    }

    private function generateDates($start, $end, $interval)
    {
        $dates = [];
        $current = $start->copy();

        while ($current <= $end) {
            $dates[] = $current->toDateString();

            match ($interval) {
                'monthly' => $current->addMonth(),
                'weekly' => $current->addWeek(),
                'daily' => $current->addDay(),
            };
        }

        return $dates;
    }

}