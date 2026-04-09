<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use App\Models\Report;
use App\Models\ReportData;
use App\Jobs\GenerateReportJob;
use App\Services\CurrencyService;

class ApiFullTest extends TestCase
{
    use RefreshDatabase;

    /** ----------------------- AUTH TESTS ----------------------- */

    public function test_register_creates_user_and_returns_token()
    {
        $response = $this->postJson('/api/register', [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => 'password',
        ]);

        $response->assertStatus(200)
                 ->assertJsonStructure(['token']);

        $this->assertDatabaseHas('users', [
            'email' => 'john@example.com',
        ]);
    }

    public function test_login_returns_token_for_valid_credentials()
    {
        $user = User::factory()->create([
            'password' => bcrypt('password'),
        ]);

        $response = $this->postJson('/api/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $response->assertStatus(200)
                 ->assertJsonStructure(['token']);
    }

    public function test_login_fails_with_invalid_credentials()
    {
        $user = User::factory()->create([
            'password' => bcrypt('password'),
        ]);

        $response = $this->postJson('/api/login', [
            'email' => $user->email,
            'password' => 'wrongpassword',
        ]);

        $response->assertStatus(401)
                 ->assertJson(['error' => 'Invalid credentials']);
    }

    /** ----------------------- CURRENCY TESTS ----------------------- */

    public function test_authenticated_user_can_fetch_currencies()
    {
        $user = User::factory()->create();

        $this->mock(CurrencyService::class, function ($mock) {
            $mock->shouldReceive('getCurrencyList')->andReturn(['USD', 'EUR', 'INR']);
        });

        $response = $this->actingAs($user, 'sanctum')->getJson('/api/currencies');

        $response->assertStatus(200)
                 ->assertJson(['USD', 'EUR', 'INR']);
    }

    public function test_rates_requires_authentication()
    {
        $response = $this->postJson('/api/rates', [
            'currencies' => ['USD', 'EUR']
        ]);

        $response->assertStatus(401); // Unauthorized
    }

    /** ----------------------- REPORT TESTS ----------------------- */

    public function test_user_can_create_report()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user, 'sanctum')->postJson('/api/reports', [
            'currency' => 'USD',
            'range' => '1m',
            'interval' => 'daily',
        ]);

        $response->assertStatus(200)
                 ->assertJsonFragment([
                     'currency' => 'USD',
                     'range' => '1m',
                     'interval' => 'daily',
                     'status' => 'pending'
                 ]);

        $this->assertDatabaseHas('reports', [
            'user_id' => $user->id,
            'currency' => 'USD',
        ]);
    }

    public function test_user_can_list_own_reports()
    {
        $user = User::factory()->create();
        Report::factory()->count(3)->create(['user_id' => $user->id]);

        $response = $this->actingAs($user, 'sanctum')->getJson('/api/reports');

        $response->assertStatus(200)
                 ->assertJsonCount(3);
    }

    public function test_user_cannot_view_others_report()
    {
        $user = User::factory()->create();
        $other = User::factory()->create();
        $report = Report::factory()->create(['user_id' => $other->id]);

        $response = $this->actingAs($user, 'sanctum')->getJson("/api/reports/{$report->id}");

        $response->assertStatus(403);
    }

    /** ----------------------- JOB TESTS ----------------------- */

    public function test_generate_report_job_processes_pending_reports()
    {
        $report = Report::factory()->create([
            'status' => 'pending',
            'currency' => 'USD',
            'range' => '1m',
            'interval' => 'daily',
        ]);

        $this->mock(CurrencyService::class, function ($mock) {
            $mock->shouldReceive('getTimeframeRates')->andReturn([
                'quotes' => [
                    now()->toDateString() => ['USD' => 1.0]
                ]
            ]);
        });

        $job = new GenerateReportJob();
        $job->handle(app(CurrencyService::class));

        $this->assertDatabaseHas('reports', [
            'id' => $report->id,
            'status' => 'completed',
        ]);

        $this->assertDatabaseHas('report_data', [
            'report_id' => $report->id,
            'rate' => 1.0,
        ]);
    }
}