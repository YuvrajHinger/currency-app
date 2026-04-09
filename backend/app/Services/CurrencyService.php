<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class CurrencyService
{
    private string $baseUrl;
    private string $apiKey;
    private string $source;
    private bool $dryRun;

    public function __construct()
    {
        $this->baseUrl = config('services.currency.url');
        $this->apiKey = config('services.currency.key');
        $this->source = config('services.currency.source', 'USD');
        $this->dryRun = config('services.currency.dry_run', false);
    }

    /**
     * Get live rates
     */
    public function getRates(array $currencies, bool $dryRunOverride = false): array
    {
        $isDryRun = $dryRunOverride || $this->dryRun;

        if ($isDryRun) {
            return [
                'success' => true,
                'source' => $this->source,
                'quotes' => $this->simulateRates($currencies),
                'dry_run' => true
            ];
        }

        $response = Http::get($this->baseUrl . '/live', [
            'access_key' => $this->apiKey,
            'currencies' => implode(',', $currencies),
            'source' => $this->source,
        ]);

        return $response->json();
    }

    /**
     * Get currency list
     */
    public function getCurrencyList(bool $dryRunOverride = false): array
    {
        $isDryRun = $dryRunOverride || $this->dryRun;

        if ($isDryRun) {
            return [
                'success' => true,
                'currencies' => [
                    'USD' => 'United States Dollar',
                    'INR' => 'Indian Rupee',
                    'EUR' => 'Euro',
                    'GBP' => 'British Pound'
                ],
                'dry_run' => true
            ];
        }

        $response = Http::get($this->baseUrl . '/list', [
            'access_key' => $this->apiKey
        ]);

        return $response->json();
    }

    /**
     * Get historical rates for a timeframe
     */
    public function getTimeframeRates(string $currency, string $startDate, string $endDate, bool $dryRunOverride = false): array
    {
        $isDryRun = $dryRunOverride || $this->dryRun;

        if ($isDryRun) {
            return [
                'success' => true,
                'source' => $this->source,
                'quotes' => $this->simulateTimeframeRates($currency, $startDate, $endDate),
                'dry_run' => true
            ];
        }

        $response = Http::get($this->baseUrl . '/timeframe', [
            'access_key' => $this->apiKey,
            'start_date' => $startDate,
            'end_date'   => $endDate,
            'currencies' => $currency,
            'source'     => $this->source            
        ]);

        return $response->json();
    }

    /**
     * Simulated rates for dry run
     */
    private function simulateRates(array $currencies): array
    {
        $rates = [];

        foreach ($currencies as $currency) {
            $pair = $this->source . strtoupper($currency);
            $rates[$pair] = round(mt_rand(50, 100) + (mt_rand(0, 99) / 100), 2);
        }

        return $rates;
    }

    /**
     * Simulate historical rates for a timeframe (dry run)
     */
    private function simulateTimeframeRates(string $currency, string $startDate, string $endDate): array
    {
        $quotes = [];
        $start = \Carbon\Carbon::parse($startDate);
        $end = \Carbon\Carbon::parse($endDate);
        $pair = $this->source . strtoupper($currency);

        $current = $start->copy();

        // Initialize starting rate
        $rate = round(mt_rand(50, 100) + mt_rand(0, 99)/100, 4);

        while ($current <= $end) {
            // Small daily fluctuation ±0.5%
            $fluctuation = ($rate * 0.005) * (mt_rand(-100, 100) / 100);
            $rate = max(1, $rate + $fluctuation); // avoid going below 1

            $quotes[$current->toDateString()] = [
                $pair => round($rate, 4)
            ];

            $current->addDay();
        }

        return $quotes;
    }
}