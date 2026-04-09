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

                // Step 2: Fetch base rate (current)                
                $data = $this->fetchTimeframeRates(
                    $currencyService,
                    $report->currency,
                    $startDate,
                    $endDate
                );

                $filteredData = $this->filterByInterval($data['quotes'], $report->interval);                

                // Step 3: Insert data
                $this->storeReportData($report->id, $filteredData);

                // Step 4: Mark completed
                $report->update(['status' => 'completed']);
            } catch (\Throwable $e) {
                // Optional: mark failed
                $report->update(['status' => 'failed']);            
                // Optional logging
                \Log::error("Report {$report->id} failed: " . $e->getMessage());
            }
        }
    }

    private function fetchTimeframeRates(CurrencyService $currencyService, string $currency, $startDate, $endDate)
    {
        return $currencyService->getTimeframeRates(
            $currency,
            $startDate->toDateString(),
            $endDate->toDateString()
        );
    }

    private function storeReportData(int $reportId, array $data): void
    {
        $rows = [];

        foreach ($data as $date => $rateData) {
            foreach ($rateData as $pair => $rate) {
                $rows[] = [
                    'report_id' => $reportId,
                    'date' => $date,
                    'rate' => $rate,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }
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

    private function filterByInterval(array $quotes, string $interval): array
    {
        $result = [];
        foreach ($quotes as $date => $rateData) {

            $carbonDate = \Carbon\Carbon::parse($date);

            if ($interval === 'daily') {
                $result[$date] = $rateData;

            } elseif ($interval === 'weekly' && $carbonDate->dayOfWeek === 1) {
                $result[$date] = $rateData;

            } elseif ($interval === 'monthly' && $carbonDate->day === 1) {
                $result[$date] = $rateData;
            }                        
        }

        return $result;
    }

}