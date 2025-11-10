<?php

namespace Tests\Feature;

use App\Models\Calculation;
use App\Models\Client;
use App\Models\ProductionRate;
use App\Models\SiteVisit;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class SynTurfCalculatorTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_creates_a_calculation_with_materials_and_totals()
    {
        $user = User::factory()->create();

        $client = Client::create([
            'first_name' => 'Test',
            'last_name' => 'Client',
            'email' => 'client@example.com',
        ]);

        $siteVisit = SiteVisit::create([
            'client_id' => $client->id,
            'visit_date' => Carbon::now()->toDateString(),
            'notes' => null,
        ]);

        $rates = [
            ['task' => 'base', 'unit' => 'sqft', 'rate' => 0.0060],
            ['task' => 'edging', 'unit' => 'linear ft', 'rate' => 0.0200],
            ['task' => 'excavation', 'unit' => 'sqft', 'rate' => 0.0050],
            ['task' => 'infill', 'unit' => 'sqft', 'rate' => 0.0020],
            ['task' => 'syn_turf_install', 'unit' => 'sqft', 'rate' => 0.0070],
        ];

        foreach ($rates as $rate) {
            ProductionRate::create(array_merge($rate, ['calculator' => 'syn_turf']));
        }

        $payload = [
            'labor_rate' => 65,
            'crew_size' => 4,
            'drive_distance' => 10,
            'drive_speed' => 35,
            'site_conditions' => 5,
            'material_pickup' => 2,
            'cleanup' => 1,
            'site_visit_id' => $siteVisit->id,
            'area_sqft' => 2500,
            'edging_linear_ft' => 300,
            'turf_grade' => 'better',
            'tasks' => [
                'base' => ['qty' => 2500],
                'edging' => ['qty' => 300],
                'excavation' => ['qty' => 2500],
                'infill' => ['qty' => 2500],
                'syn_turf_install' => ['qty' => 2500],
            ],
        ];

        $response = $this
            ->actingAs($user)
            ->post(route('calculators.syn_turf.calculate'), $payload);

        $response->assertRedirect();

        $calculation = Calculation::first();

        $this->assertNotNull($calculation, 'Calculation record was not created');
        $this->assertSame('syn_turf', $calculation->calculation_type);
        $this->assertEquals(2500, $calculation->data['area_sqft']);

        $materials = $calculation->data['materials'] ?? [];
        $this->assertArrayHasKey('Better Synthetic Turf', $materials);
        $this->assertEqualsWithDelta(7500, $materials['Better Synthetic Turf']['total'], 0.01);
        $this->assertEquals(50, $materials['Infill Bags']['qty']);
        $this->assertEquals(15, $materials['Composite Edging Boards']['qty']);
        $this->assertEquals(2, $materials['Weed Barrier Rolls']['qty']);

        $this->assertArrayHasKey('final_price', $calculation->data);
        $this->assertGreaterThan($calculation->data['material_total'], $calculation->data['final_price']);
    }
}
