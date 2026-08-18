<?php

namespace Tests\Feature;

use App\Models\AcademicPeriod;
use App\Models\Partial;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AcademicPeriodManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_create_academic_period_and_auto_generates_p1_and_p2(): void
    {
        $admin = User::factory()->admin()->create();

        $response = $this->actingAs($admin)->post('/admin/academic-periods', [
            'name' => 'Período Lectivo 2026–2027',
            'starts_at' => '2026-09-01',
            'ends_at' => '2027-07-31',
            'active' => true,
        ]);

        $response->assertRedirect('/admin/academic-periods');

        $period = AcademicPeriod::where('name', 'Período Lectivo 2026–2027')->firstOrFail();
        $this->assertTrue($period->active);
        $this->assertCount(2, $period->partials);

        $p1 = $period->partials->where('number', 1)->first();
        $p2 = $period->partials->where('number', 2)->first();

        $this->assertNotNull($p1);
        $this->assertEquals('Primer parcial', $p1->name);
        $this->assertEquals('50.00', $p1->weight);

        $this->assertNotNull($p2);
        $this->assertEquals('Segundo parcial', $p2->name);
        $this->assertEquals('50.00', $p2->weight);
    }

    public function test_teacher_and_student_cannot_access_academic_periods(): void
    {
        $teacher = User::factory()->teacher()->create();
        $student = User::factory()->student()->create();

        $this->actingAs($teacher)->get('/admin/academic-periods')->assertForbidden();
        $this->actingAs($student)->get('/admin/academic-periods')->assertForbidden();
    }

    public function test_end_date_before_or_equal_to_start_date_is_rejected(): void
    {
        $admin = User::factory()->admin()->create();

        $response = $this->actingAs($admin)->post('/admin/academic-periods', [
            'name' => 'Período Inválido',
            'starts_at' => '2026-09-01',
            'ends_at' => '2026-09-01',
        ]);

        $response->assertSessionHasErrors('ends_at');
    }

    public function test_duplicate_academic_period_name_is_rejected(): void
    {
        $admin = User::factory()->admin()->create();
        AcademicPeriod::factory()->create(['name' => 'Período 2026']);

        $response = $this->actingAs($admin)->post('/admin/academic-periods', [
            'name' => 'Período 2026',
            'starts_at' => '2026-09-01',
            'ends_at' => '2027-07-31',
        ]);

        $response->assertSessionHasErrors('name');
    }

    public function test_only_one_academic_period_can_be_active_at_a_time(): void
    {
        $admin = User::factory()->admin()->create();
        $period1 = AcademicPeriod::factory()->create(['name' => 'P1', 'active' => true]);
        $period2 = AcademicPeriod::factory()->create(['name' => 'P2', 'active' => false]);

        $this->assertTrue($period1->fresh()->active);
        $this->assertFalse($period2->fresh()->active);

        // Activate period 2
        $this->actingAs($admin)->patch("/admin/academic-periods/{$period2->id}/activate")
            ->assertRedirect('/admin/academic-periods');

        $this->assertFalse($period1->fresh()->active);
        $this->assertTrue($period2->fresh()->active);
    }

    public function test_editing_academic_period_does_not_duplicate_partials(): void
    {
        $admin = User::factory()->admin()->create();
        
        $this->actingAs($admin)->post('/admin/academic-periods', [
            'name' => 'Período Original',
            'starts_at' => '2026-09-01',
            'ends_at' => '2027-07-31',
            'active' => true,
        ]);

        $period = AcademicPeriod::where('name', 'Período Original')->firstOrFail();
        $this->assertCount(2, Partial::where('academic_period_id', $period->id)->get());

        // Update period
        $this->actingAs($admin)->put("/admin/academic-periods/{$period->id}", [
            'name' => 'Período Renombrado',
            'starts_at' => '2026-09-01',
            'ends_at' => '2027-08-15',
            'active' => true,
        ]);

        $this->assertCount(2, Partial::where('academic_period_id', $period->id)->get());
    }

    public function test_running_seeder_twice_is_idempotent(): void
    {
        $seeder = new DatabaseSeeder();
        $seeder->run();
        $seeder->run();

        $this->assertEquals(2, \App\Models\Course::count());
        $this->assertEquals(3, \App\Models\Subject::count());
        $this->assertEquals(1, AcademicPeriod::count());
        $this->assertEquals(2, Partial::count());
    }

    public function test_third_partial_cannot_be_created_via_web_routes(): void
    {
        $admin = User::factory()->admin()->create();
        $period = AcademicPeriod::factory()->create();

        $this->actingAs($admin)->post("/admin/academic-periods/{$period->id}/partials", [
            'number' => 3,
            'name' => 'Tercer parcial',
        ])->assertNotFound();
    }
}
