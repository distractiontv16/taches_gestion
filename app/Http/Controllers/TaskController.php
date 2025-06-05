<?php
namespace App\Http\Controllers;

use App\Models\Task;
use App\Models\User;
use App\Models\Reminder;
use App\Services\TaskNotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class TaskController extends Controller
{
    protected $taskNotificationService;

    public function __construct(TaskNotificationService $taskNotificationService)
    {
        $this->taskNotificationService = $taskNotificationService;
    }
    public function index()
    {
        // Affiche toutes les tâches de l'utilisateur actuel avec les relations
        $user = Auth::user();
        $tasks = $user->tasks()->with('assignedUser')->get()->groupBy('status');
        return view('tasks.index', compact('tasks'));
    }

    public function create()
    {
        $users = User::all();
        return view('tasks.create', compact('users'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'due_date' => 'nullable|date',
            'priority' => 'required|in:low,medium,high',
            'status' => 'nullable|in:to_do,in_progress,completed',
            'assigned_to' => 'nullable|exists:users,id',
        ]);

        $taskData = $request->all();
        $taskData['user_id'] = Auth::id();
        $taskData['status'] = $request->status ?? 'to_do';

        $task = Task::create($taskData);

        // Create a reminder for the task if due_date is set
        if ($request->filled('due_date')) {
            $this->createTaskReminder($task);
        }

        // Send notification if task is assigned to someone else
        if ($task->assigned_to && $task->assigned_to !== Auth::id()) {
            $assignedUser = User::find($task->assigned_to);
            if ($assignedUser) {
                $this->taskNotificationService->notifyTaskAssignment($task, $assignedUser);
            }
        }

        return redirect()->route('tasks.index')->with('success', 'Tâche créée avec succès.');
    }

    public function show(Task $task)
    {
        return view('tasks.show', compact('task'));
    }

    public function update(Request $request, Task $task)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'due_date' => 'nullable|date',
            'priority' => 'required|in:low,medium,high',
            'status' => 'required|in:to_do,in_progress,completed',
            'assigned_to' => 'nullable|exists:users,id',
        ]);

        // Store old values for comparison
        $oldAssignedTo = $task->assigned_to;
        $oldStatus = $task->status;

        // Check if due_date has changed
        $dueDateChanged = $task->due_date && $request->due_date && $task->due_date->format('Y-m-d') !== $request->due_date;

        // Update the task
        $task->update($request->all());

        // Check if assignment has changed and send notification
        if ($oldAssignedTo !== $task->assigned_to && $task->assigned_to && $task->assigned_to !== Auth::id()) {
            $assignedUser = User::find($task->assigned_to);
            if ($assignedUser) {
                // This is a reassignment since the task already existed
                $this->taskNotificationService->notifyTaskAssignment($task, $assignedUser, true);
            }
        }

        // Check if status has changed and notify
        if ($oldStatus !== $task->status && $task->assigned_to && $task->assigned_to !== $task->user_id) {
            $this->taskNotificationService->notifyTaskStatusChange($task, $oldStatus, $task->status);
        }

        // Update or create reminder if due date has changed
        if ($dueDateChanged || (!$task->due_date && $request->filled('due_date'))) {
            // Delete existing reminders for this task
            $task->reminders()->delete();

            // Create new reminder if due_date is set
            if ($request->filled('due_date')) {
                $this->createTaskReminder($task);
            }
        }

        return redirect()->route('tasks.index')->with('success', 'Tâche mise à jour avec succès.');
    }

    public function updateStatus(Request $request, Task $task)
    {
        $task->status = $request->input('status');
        $task->save();

        return response()->json(['message' => 'Task status updated successfully.']);
    }

    public function toggleComplete(Task $task)
    {
        $wasCompleted = $task->status === 'completed';
        
        $task->status = $wasCompleted ? 'to_do' : 'completed';
        $task->completed_at = $wasCompleted ? null : now();
        $task->save();
        
        // Delete reminders if task is completed
        if (!$wasCompleted) {
            $task->reminders()->delete();
        } else if ($task->due_date) {
            // Recreate reminder if task is uncompleted and has a due date
            $this->createTaskReminder($task);
        }
        
        return response()->json([
            'success' => true,
            'message' => $wasCompleted ? 'La tâche a été marquée comme à faire.' : 'La tâche a été marquée comme terminée.',
            'status' => $task->status,
            'completed_at' => $task->completed_at
        ]);
    }

    public function edit(Task $task)
    {
        $users = User::all();
        return view('tasks.edit', compact('task', 'users'));
    }

    public function destroy(Task $task)
    {
        // Delete any reminders associated with this task
        $task->reminders()->delete();

        // Supprimer la tâche et tous ses éléments de checklist (via la cascade)
        $task->delete();

        // Si cette requête est effectuée via AJAX
        if (request()->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Tâche supprimée avec succès.'
            ]);
        }

        return redirect()->route('tasks.index')->with('success', 'Tâche supprimée avec succès.');
    }

    /**
     * Create a reminder for a task.
     */
    private function createTaskReminder(Task $task)
    {
        // Set reminder time to 2 hours before due date
        $reminderTime = Carbon::parse($task->due_date)->subHours(2);
        
        // Create the reminder
        $task->reminders()->create([
            'user_id' => $task->user_id,
            'title' => 'Rappel: ' . $task->title,
            'description' => 'Rappel pour la tâche: ' . $task->title,
            'date' => $reminderTime->format('Y-m-d'),
            'time' => $reminderTime->format('H:i'),
            'email_sent' => false,
        ]);
    }
}
