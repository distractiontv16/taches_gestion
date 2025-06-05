<?php

namespace Tests\Unit;

use App\Models\Routine;
use App\Models\Task;
use App\Models\User;
use App\Services\RoutineTaskGeneratorService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RoutineTaskGeneratorServiceTest extends TestCase
{
    use RefreshDatabase;

    protected RoutineTaskGeneratorService $service;
    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new RoutineTaskGeneratorService();
        $this->user = User::factory()->create();
    }

    /** @test */
    public function it_can_generate_task_for_daily_routine()
    {
        // Créer une routine quotidienne active
        $routine = Routine::factory()->create([
            'user_id' => $this->user->id,
            'title' => 'Test Daily Routine',
            'frequency' => 'daily',
            'is_active' => true,
            'priority' => 'medium',
            'due_time' => '14:00:00',
            'days' => json_encode(['monday', 'tuesday', 'wednesday', 'thursday', 'friday']),
            'workdays_only' => true
        ]);

        // Tester pour un lundi
        $monday = Carbon::parse('2025-06-09'); // Un lundi
        
        $task = $this->service->generateTaskForRoutine($routine, $monday);

        $this->assertNotNull($task);
        $this->assertEquals($routine->title, $task->title);
        $this->assertEquals($routine->user_id, $task->user_id);
        $this->assertEquals($routine->id, $task->routine_id);
        $this->assertTrue($task->is_auto_generated);
        $this->assertEquals($monday->format('Y-m-d'), $task->target_date->format('Y-m-d'));
        $this->assertEquals('14:00:00', $task->due_date->format('H:i:s'));
    }

    /** @test */
    public function it_does_not_generate_task_for_inactive_routine()
    {
        $routine = Routine::factory()->create([
            'user_id' => $this->user->id,
            'frequency' => 'daily',
            'is_active' => false
        ]);

        $today = Carbon::today();
        $task = $this->service->generateTaskForRoutine($routine, $today);

        $this->assertNull($task);
    }

    /** @test */
    public function it_does_not_generate_duplicate_tasks()
    {
        $routine = Routine::factory()->create([
            'user_id' => $this->user->id,
            'frequency' => 'daily',
            'is_active' => true,
            'last_generated_date' => Carbon::today()
        ]);

        $today = Carbon::today();
        $task = $this->service->generateTaskForRoutine($routine, $today);

        $this->assertNull($task);
    }

    /** @test */
    public function it_respects_workdays_only_setting()
    {
        $routine = Routine::factory()->create([
            'user_id' => $this->user->id,
            'frequency' => 'daily',
            'is_active' => true,
            'workdays_only' => true
        ]);

        // Tester pour un samedi (weekend)
        $saturday = Carbon::parse('2025-06-07'); // Un samedi
        
        $task = $this->service->generateTaskForRoutine($routine, $saturday);

        $this->assertNull($task);
    }

    /** @test */
    public function it_can_preview_tasks_for_routine()
    {
        $routine = Routine::factory()->create([
            'user_id' => $this->user->id,
            'title' => 'Test Routine',
            'frequency' => 'daily',
            'is_active' => true,
            'priority' => 'high',
            'due_time' => '09:00:00',
            'days' => json_encode(['monday', 'wednesday', 'friday']),
            'workdays_only' => false
        ]);

        $preview = $this->service->previewTasksForRoutine($routine, 7);

        $this->assertIsArray($preview);
        $this->assertGreaterThan(0, count($preview));
        
        foreach ($preview as $task) {
            $this->assertArrayHasKey('date', $task);
            $this->assertArrayHasKey('day_name', $task);
            $this->assertArrayHasKey('due_datetime', $task);
            $this->assertArrayHasKey('title', $task);
            $this->assertArrayHasKey('priority', $task);
            $this->assertEquals('Test Routine', $task['title']);
            $this->assertEquals('high', $task['priority']);
        }
    }

    /** @test */
    public function it_can_generate_tasks_for_date_range()
    {
        // Créer plusieurs routines
        $routine1 = Routine::factory()->create([
            'user_id' => $this->user->id,
            'frequency' => 'daily',
            'is_active' => true,
            'days' => json_encode(['monday', 'tuesday', 'wednesday', 'thursday', 'friday']),
            'workdays_only' => true
        ]);

        $routine2 = Routine::factory()->create([
            'user_id' => $this->user->id,
            'frequency' => 'daily',
            'is_active' => true,
            'days' => json_encode(['monday', 'wednesday', 'friday']),
            'workdays_only' => false
        ]);

        $startDate = Carbon::parse('2025-06-09'); // Lundi
        $endDate = Carbon::parse('2025-06-13'); // Vendredi

        $results = $this->service->generateTasksForDateRange($startDate, $endDate);

        $this->assertArrayHasKey('total_days_processed', $results);
        $this->assertArrayHasKey('total_tasks_generated', $results);
        $this->assertArrayHasKey('daily_results', $results);
        $this->assertEquals(5, $results['total_days_processed']);
        $this->assertGreaterThan(0, $results['total_tasks_generated']);
    }

    /** @test */
    public function routine_should_generate_for_date_works_correctly()
    {
        $routine = Routine::factory()->create([
            'user_id' => $this->user->id,
            'frequency' => 'daily',
            'is_active' => true,
            'days' => json_encode(['monday', 'wednesday', 'friday']),
            'workdays_only' => false
        ]);

        $monday = Carbon::parse('2025-06-09'); // Lundi
        $tuesday = Carbon::parse('2025-06-10'); // Mardi
        $wednesday = Carbon::parse('2025-06-11'); // Mercredi

        $this->assertTrue($routine->shouldGenerateForDate($monday));
        $this->assertFalse($routine->shouldGenerateForDate($tuesday));
        $this->assertTrue($routine->shouldGenerateForDate($wednesday));
    }

    /** @test */
    public function routine_calculates_due_datetime_correctly()
    {
        $routine = Routine::factory()->create([
            'due_time' => '15:30:00'
        ]);

        $date = Carbon::parse('2025-06-09');
        $dueDateTime = $routine->calculateDueDateTime($date);

        $this->assertEquals('2025-06-09 15:30:00', $dueDateTime->format('Y-m-d H:i:s'));
    }
}
