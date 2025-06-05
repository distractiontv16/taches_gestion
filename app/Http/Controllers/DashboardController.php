<?php

namespace App\Http\Controllers;

use App\Models\Task;
use App\Models\User;
use App\Models\Routine;
use App\Models\Note;
use App\Models\Reminder;
use App\Models\File;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        // Statistiques générales des tâches
        $taskStats = $this->getTaskStatistics($user);

        // Statistiques des routines
        $routineStats = $this->getRoutineStatistics($user);

        // Statistiques des rappels
        $reminderStats = $this->getReminderStatistics($user);

        // Données pour les graphiques
        $chartData = $this->getChartData($user);

        // Données récentes
        $recentData = $this->getRecentData($user);

        return view('dashboard', compact(
            'taskStats',
            'routineStats',
            'reminderStats',
            'chartData',
            'recentData'
        ));
    }

    private function getTaskStatistics($user)
    {
        $totalTasks = $user->tasks()->count();
        $completedTasks = $user->tasks()->where('status', 'completed')->count();
        $inProgressTasks = $user->tasks()->where('status', 'in_progress')->count();
        $todoTasks = $user->tasks()->where('status', 'to_do')->count();

        // Tâches complétées cette semaine
        $completedThisWeek = $user->tasks()
            ->where('status', 'completed')
            ->whereBetween('completed_at', [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()])
            ->get();

        // Tâches par priorité
        $highPriorityTasks = $user->tasks()->where('priority', 'high')->where('status', '!=', 'completed')->count();
        $mediumPriorityTasks = $user->tasks()->where('priority', 'medium')->where('status', '!=', 'completed')->count();
        $lowPriorityTasks = $user->tasks()->where('priority', 'low')->where('status', '!=', 'completed')->count();

        // Taux de completion
        $completionRate = $totalTasks > 0 ? round(($completedTasks / $totalTasks) * 100, 1) : 0;

        return [
            'total' => $totalTasks,
            'completed' => $completedTasks,
            'in_progress' => $inProgressTasks,
            'todo' => $todoTasks,
            'completed_this_week' => $completedThisWeek,
            'high_priority' => $highPriorityTasks,
            'medium_priority' => $mediumPriorityTasks,
            'low_priority' => $lowPriorityTasks,
            'completion_rate' => $completionRate
        ];
    }

    private function getRoutineStatistics($user)
    {
        $totalRoutines = $user->routines()->count();
        $dailyRoutines = $user->routines()->where('frequency', 'daily')->count();
        $weeklyRoutines = $user->routines()->where('frequency', 'weekly')->count();
        $monthlyRoutines = $user->routines()->where('frequency', 'monthly')->count();

        // Routines d'aujourd'hui
        $todayRoutines = $user->routines()
            ->whereDate('start_time', Carbon::today())
            ->get();

        return [
            'total' => $totalRoutines,
            'daily' => $dailyRoutines,
            'weekly' => $weeklyRoutines,
            'monthly' => $monthlyRoutines,
            'today' => $todayRoutines
        ];
    }

    private function getReminderStatistics($user)
    {
        $totalReminders = $user->reminders()->count();
        $upcomingReminders = $user->reminders()
            ->whereDate('date', '>=', Carbon::today())
            ->count();
        $overdueReminders = $user->reminders()
            ->whereDate('date', '<', Carbon::today())
            ->where('email_sent', false)
            ->count();
        $sentReminders = $user->reminders()
            ->where('email_sent', true)
            ->count();

        return [
            'total' => $totalReminders,
            'upcoming' => $upcomingReminders,
            'overdue' => $overdueReminders,
            'sent' => $sentReminders
        ];
    }

    private function getChartData($user)
    {
        // Données pour le graphique de progression des tâches (7 derniers jours)
        $taskProgressData = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = Carbon::now()->subDays($i);
            $completed = $user->tasks()
                ->where('status', 'completed')
                ->whereDate('completed_at', $date)
                ->count();
            $taskProgressData[] = [
                'date' => $date->format('d/m'),
                'completed' => $completed
            ];
        }

        // Répartition des tâches par utilisateur (si assignées)
        $tasksByUser = Task::select('assigned_to', DB::raw('count(*) as count'))
            ->where('user_id', $user->id)
            ->whereNotNull('assigned_to')
            ->groupBy('assigned_to')
            ->with('assignedUser')
            ->get()
            ->map(function ($task) {
                return [
                    'user' => $task->assignedUser ? $task->assignedUser->name : 'Non assigné',
                    'count' => $task->count
                ];
            });

        return [
            'task_progress' => $taskProgressData,
            'tasks_by_user' => $tasksByUser
        ];
    }

    private function getRecentData($user)
    {
        return [
            'tasks' => $user->tasks()->where('status', '!=', 'completed')->latest()->take(5)->get(),
            'notes' => $user->notes()->latest()->take(3)->get(),
            'reminders' => $user->reminders()
                ->whereDate('date', '>=', Carbon::today())
                ->orderBy('date')
                ->orderBy('time')
                ->take(5)
                ->get(),
            'files' => $user->files()->latest()->take(3)->get()
        ];
    }
}
