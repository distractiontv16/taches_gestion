<?php
namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\Task;
use App\Models\Reminder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class TaskController extends Controller
{
    public function index(Project $project = null)
    {
        // Si un projet est fourni, affiche les tâches de ce projet
        if ($project) {
            $tasks = $project->tasks()->get()->groupBy('status');
            $users = $project->users()->get();  
            return view('tasks.index', compact('project', 'tasks', 'users'));
        }
        
        // Si aucun projet n'est fourni, affiche toutes les tâches de l'utilisateur actuel
        $user = Auth::user();
        $tasks = $user->tasks()->get()->groupBy('status');
        return view('tasks.index', compact('tasks'));
    }

    public function store(Request $request, Project $project)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'due_date' => 'nullable|date',
            'priority' => 'required|in:low,medium,high',
        ]);

        $task = $project->tasks()->create($request->all());

        // Create a reminder for the task if due_date is set
        if ($request->filled('due_date')) {
            $this->createTaskReminder($task);
        }

        return redirect()->route('projects.tasks.index', $project)->with('success', 'Task created successfully.');
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
        ]);

        // Check if due_date has changed
        $dueDateChanged = $task->due_date && $request->due_date && $task->due_date->format('Y-m-d') !== $request->due_date;
        
        $task->update($request->all());

        // Update or create reminder if due date has changed
        if ($dueDateChanged || (!$task->due_date && $request->filled('due_date'))) {
            // Delete existing reminders for this task
            $task->reminders()->delete();
            
            // Create new reminder if due_date is set
            if ($request->filled('due_date')) {
                $this->createTaskReminder($task);
            }
        }

        return redirect()->route('projects.tasks.index', $task->project_id)->with('success', 'Task updated successfully.');
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

    public function destroy(Task $task)
    {
        // Récupérer l'ID du projet avant de supprimer la tâche
        $projectId = $task->project_id;
        
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
        
        // Rediriger vers la liste des tâches du projet ou la liste des tâches générales
        if ($projectId) {
            return redirect()->route('projects.tasks.index', $projectId)->with('success', 'Tâche supprimée avec succès.');
        } else {
            return redirect()->route('tasks.index')->with('success', 'Tâche supprimée avec succès.');
        }
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
