<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use App\Services\CurrencyService;

class CurrencyController extends Controller
{

    public function __construct(private CurrencyService $currencyService) {}

    public function rates(Request $request)
    {
        $request->validate([
            'currencies' => 'required|array|max:5',
            'currencies.*' => 'string|size:3',
            'dry_run' => 'sometimes|boolean'
        ]);

        $data = $this->currencyService->getRates(
            $request->currencies,
            $request->boolean('dry_run')
        );

        return response()->json($data);
    }

    public function currencies(Request $request)
    {
        $data = $this->currencyService->getCurrencyList(
            $request->boolean('dry_run')
        );

        return response()->json($data);
    }
    
}