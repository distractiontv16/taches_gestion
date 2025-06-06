<?php

namespace Tests\Feature;

use App\Events\TaskStatusChanged;
use App\Events\TaskOverdue;
use App\Events\DashboardUpdated;
use App\Models\Task;
use App\Models\User;
use App\Services\RealTimeNotificationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;
use Carbon\Carbon;

class RealTimeNotificationTest extends TestCase
{
    use RefreshDatabase;

    protected $user;
    protected $realTimeService;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->user = User::factory()->create();
        $this->realTimeService = new RealTimeNotificationService();
    }

    /** @test */
    public function it_broadcasts_task_status_change_event()
    {
        Event::fake();

        $task = Task::factory()->create([
            'user_id' => $this->user->id,
            'status' => 'to_do'
        ]);

        $this->realTimeService->broadcastTaskStatusChange($task, 'to_do', 'completed');

        Event::assertDispatched(TaskStatusChanged::class, function ($event) use ($task) {
            return $event->task->id === $task->id &&
                   $event->oldStatus === 'to_do' &&
                   $event->newStatus === 'completed';
        });

        Event::assertDispatched(DashboardUpdated::class);
    }

    /** @test */
    public function it_broadcasts_task_overdue_event()
    {
        Event::fake();

        $task = Task::factory()->create([
            'user_id' => $this->user->id,
            'due_date' => Carbon::now()->subHour(),
            'status' => 'to_do'
        ]);

        $this->realTimeService->broadcastTaskOverdue($task);

        Event::assertDispatched(TaskOverdue::class, function ($event) use ($task) {
            return $event->task->id === $task->id &&
                   $event->overdueMinutes > 0;
        });
    }

    /** @test */
    public function it_calculates_correct_badge_data()
    {
        // Create tasks with different statuses and due dates
        $completedTask = Task::factory()->create([
            'user_id' => $this->user->id,
            'status' => 'completed'
        ]);

        $pendingTask = Task::factory()->create([
            'user_id' => $this->user->id,
            'status' => 'to_do',
            'due_date' => Carbon::now()->addDay()
        ]);

        $overdueTask = Task::factory()->create([
            'user_id' => $this->user->id,
            'status' => 'in_progress',
            'due_date' => Carbon::now()->subHour()
        ]);

        $badgeData = $this->realTimeService->getBadgeData($this->user);

        $this->assertEquals(2, $badgeData['total_count']); // Only incomplete tasks
        $this->assertEquals(1, $badgeData['pending_count']);
        $this->assertEquals(1, $badgeData['overdue_count']);
        $this->assertCount(1, $badgeData['pending_tasks']);
        $this->assertCount(1, $badgeData['overdue_tasks']);
    }

    /** @test */
    public function it_distinguishes_between_pending_and_overdue_tasks()
    {
        // Create a pending task (future due date)
        $pendingTask = Task::factory()->create([
            'user_id' => $this->user->id,
            'status' => 'to_do',
            'due_date' => Carbon::now()->addHours(2)
        ]);

        // Create an overdue task (past due date)
        $overdueTask = Task::factory()->create([
            'user_id' => $this->user->id,
            'status' => 'to_do',
            'due_date' => Carbon::now()->subMinutes(45)
        ]);

        // Create a task without due date (should be pending)
        $noDueDateTask = Task::factory()->create([
            'user_id' => $this->user->id,
            'status' => 'to_do',
            'due_date' => null
        ]);

        $badgeData = $this->realTimeService->getBadgeData($this->user);

        $this->assertEquals(3, $badgeData['total_count']);
        $this->assertEquals(2, $badgeData['pending_count']); // Future + no due date
        $this->assertEquals(1, $badgeData['overdue_count']); // Past due date

        // Check that overdue task has overdue_minutes calculated
        $overdueTaskData = $badgeData['overdue_tasks']->first();
        $this->assertGreaterThan(0, $overdueTaskData['overdue_minutes']);
    }

    /** @test */
    public function it_respects_30_minute_overdue_notification_timing()
    {
        // Create a task that's exactly 30 minutes overdue
        $task = Task::factory()->create([
            'user_id' => $this->user->id,
            'status' => 'to_do',
            'due_date' => Carbon::now()->subMinutes(30),
            'overdue_notification_sent' => false
        ]);

        $badgeData = $this->realTimeService->getBadgeData($this->user);

        // Task should be considered overdue
        $this->assertEquals(1, $badgeData['overdue_count']);
        
        $overdueTaskData = $badgeData['overdue_tasks']->first();
        $this->assertEquals(30, $overdueTaskData['overdue_minutes']);
    }

    /** @test */
    public function it_updates_dashboard_stats_correctly()
    {
        // Create various tasks
        Task::factory()->count(3)->create([
            'user_id' => $this->user->id,
            'status' => 'completed'
        ]);

        Task::factory()->count(2)->create([
            'user_id' => $this->user->id,
            'status' => 'to_do',
            'priority' => 'high'
        ]);

        Task::factory()->create([
            'user_id' => $this->user->id,
            'status' => 'in_progress',
            'priority' => 'medium',
            'due_date' => Carbon::now()->subHour() // Overdue
        ]);

        Event::fake();
        $this->realTimeService->broadcastDashboardUpdate($this->user->id);

        Event::assertDispatched(DashboardUpdated::class, function ($event) {
            $stats = $event->stats;
            
            return $stats['total_tasks'] === 6 &&
                   $stats['completed_tasks'] === 3 &&
                   $stats['pending_tasks'] === 3 &&
                   $stats['high_priority_tasks'] === 2 &&
                   $stats['completion_rate'] === 50.0;
        });
    }

    /** @test */
    public function task_controller_broadcasts_events_on_status_change()
    {
        Event::fake();

        $task = Task::factory()->create([
            'user_id' => $this->user->id,
            'status' => 'to_do'
        ]);

        $this->actingAs($this->user)
             ->post(route('tasks.toggle-complete', $task));

        Event::assertDispatched(TaskStatusChanged::class);
        Event::assertDispatched(DashboardUpdated::class);
    }

    /** @test */
    public function notification_badges_api_returns_correct_data()
    {
        Task::factory()->create([
            'user_id' => $this->user->id,
            'status' => 'to_do',
            'due_date' => Carbon::now()->addDay()
        ]);

        Task::factory()->create([
            'user_id' => $this->user->id,
            'status' => 'to_do',
            'due_date' => Carbon::now()->subHour()
        ]);

        $response = $this->actingAs($this->user)
                         ->get(route('api.notification-badges'));

        $response->assertStatus(200)
                 ->assertJsonStructure([
                     'total_count',
                     'pending_count',
                     'overdue_count',
                     'pending_tasks',
                     'overdue_tasks'
                 ]);

        $data = $response->json();
        $this->assertEquals(2, $data['total_count']);
        $this->assertEquals(1, $data['pending_count']);
        $this->assertEquals(1, $data['overdue_count']);
    }
}
