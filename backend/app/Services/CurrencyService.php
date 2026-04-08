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
}