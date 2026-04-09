<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Report;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    /**
     * Create a new report request
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'currency' => 'required|string|size:3', // INR, EUR
            'range' => 'required|in:1m,6m,1y',
            'interval' => 'required|in:daily,weekly,monthly',
        ]);

        $report = Report::create([
            'user_id' => auth()->id(), // enable if using auth
            'currency' => strtoupper($validated['currency']),
            'range' => $validated['range'],
            'interval' => $validated['interval'],
            'status' => 'pending',
        ]);

        return response()->json($report);
    }

    /**
     * List all reports (latest first)
     */
    public function index()
    {
        return auth()->user()->reports()->orderBy('created_at', 'desc')->get();
    }

    /**
     * Show a specific report
     */
    public function show(Report $report)
    {
        // Ensure the report belongs to the authenticated user
        if ($report->user_id !== auth()->id()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        return $report->load('data'); // Load related data if needed
    }
        
}