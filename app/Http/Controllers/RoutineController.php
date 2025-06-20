<?php
namespace App\Http\Controllers;

use App\Models\Routine;
use App\Services\RoutineTaskGeneratorService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class RoutineController extends Controller
{
    protected RoutineTaskGeneratorService $generatorService;

    public function __construct(RoutineTaskGeneratorService $generatorService)
    {
        $this->generatorService = $generatorService;
    }
    public function index()
    {
        $today = Carbon::today();
        $isWorkday = !in_array($today->dayOfWeek, [0, 6]); // 0 = Sunday, 6 = Saturday
        
        $upcomingDailyRoutines = Auth::user()->routines()
            ->where('frequency', 'daily')
            ->where(function($query) use ($isWorkday) {
                // If it's a weekend, only show routines that are not workdays only
                if (!$isWorkday) {
                    $query->where('workdays_only', false);
                }
            })
            ->whereJsonContains('days', strtolower($today->format('l')))
            ->take(2)
            ->get();

        $upcomingWeeklyRoutines = Auth::user()->routines()
            ->where('frequency', 'weekly')
            ->where(function($query) use ($isWorkday) {
                // If it's a weekend, only show routines that are not workdays only
                if (!$isWorkday) {
                    $query->where('workdays_only', false);
                }
            })
            ->whereJsonContains('weeks', $today->weekOfYear)
            ->take(2)
            ->get();

        $upcomingMonthlyRoutines = Auth::user()->routines()
            ->where('frequency', 'monthly')
            ->where(function($query) use ($isWorkday) {
                // If it's a weekend, only show routines that are not workdays only
                if (!$isWorkday) {
                    $query->where('workdays_only', false);
                }
            })
            ->whereJsonContains('months', $today->month)
            ->take(2)
            ->get();

        return view('routines.index', compact('upcomingDailyRoutines', 'upcomingWeeklyRoutines', 'upcomingMonthlyRoutines'));
    }

    public function create()
    {
        return view('routines.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'frequency' => 'required|in:daily,weekly,monthly',
            'days' => 'nullable|array',
            'weeks' => 'nullable|array',
            'months' => 'nullable|array',
            'start_time' => 'required',
            'end_time' => 'required',
            'due_time' => 'nullable|date_format:H:i',
            'priority' => 'required|in:low,medium,high',
            'workdays_only' => 'boolean',
            'is_active' => 'boolean',
        ]);

        $routineData = $request->all();
        if ($request->has('days')) {
            $routineData['days'] = json_encode($request->days);
        }
        if ($request->has('weeks')) {
            $routineData['weeks'] = json_encode($request->weeks);
        }
        if ($request->has('months')) {
            $routineData['months'] = json_encode($request->months);
        }
        
        // Set defaults for boolean fields
        $routineData['workdays_only'] = $request->has('workdays_only') ? (bool)$request->workdays_only : false;
        $routineData['is_active'] = $request->has('is_active') ? (bool)$request->is_active : true;

        // Set default priority if not provided
        $routineData['priority'] = $request->priority ?? 'medium';

        Auth::user()->routines()->create($routineData);

        return redirect()->route('routines.index')->with('success', 'Routine créée avec succès.');
    }

    public function edit(Routine $routine)
    {
        return view('routines.edit', compact('routine'));
    }

    public function update(Request $request, Routine $routine)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'frequency' => 'required|in:daily,weekly,monthly',
            'days' => 'nullable|array',
            'weeks' => 'nullable|array',
            'months' => 'nullable|array',
            'start_time' => 'required',
            'end_time' => 'required',
            'workdays_only' => 'boolean',
        ]);

        $routineData = $request->all();
        if ($request->has('days')) {
            $routineData['days'] = json_encode($request->days);
        }
        if ($request->has('weeks')) {
            $routineData['weeks'] = json_encode($request->weeks);
        }
        if ($request->has('months')) {
            $routineData['months'] = json_encode($request->months);
        }
        
        // Set workdays_only default if not provided
        $routineData['workdays_only'] = $request->has('workdays_only') ? (bool)$request->workdays_only : false;

        $routine->update($routineData);

        return redirect()->route('routines.index')->with('success', 'Routine updated successfully.');
    }

    public function destroy(Routine $routine)
    {
        $routine->delete();
        return redirect()->route('routines.index')->with('success', 'Routine deleted successfully.');
    }

    public function showAll()
    {
        $dailyRoutines = Auth::user()->routines()->where('frequency', 'daily')->get();
        $weeklyRoutines = Auth::user()->routines()->where('frequency', 'weekly')->get();
        $monthlyRoutines = Auth::user()->routines()->where('frequency', 'monthly')->get();

        return view('routines.all', compact('dailyRoutines', 'weeklyRoutines', 'monthlyRoutines'));
    }

    public function showDaily()
    {
        $dailyRoutines = Auth::user()->routines()->where('frequency', 'daily')->get();
        return view('routines.daily', compact('dailyRoutines'));
    }

    public function showWeekly()
    {
        $weeklyRoutines = Auth::user()->routines()->where('frequency', 'weekly')->get();
        return view('routines.weekly', compact('weeklyRoutines'));
    }

    public function showMonthly()
    {
        $monthlyRoutines = Auth::user()->routines()->where('frequency', 'monthly')->get();
        return view('routines.monthly', compact('monthlyRoutines'));
    }

    /**
     * Prévisualise les tâches qui seraient générées pour une routine
     */
    public function preview(Routine $routine)
    {
        $preview = $this->generatorService->previewTasksForRoutine($routine, 14); // 2 semaines

        return response()->json([
            'success' => true,
            'preview' => $preview,
            'routine' => [
                'id' => $routine->id,
                'title' => $routine->title,
                'frequency' => $routine->frequency,
                'is_active' => $routine->is_active
            ]
        ]);
    }

    /**
     * Active ou désactive une routine
     */
    public function toggleActive(Routine $routine)
    {
        $routine->update(['is_active' => !$routine->is_active]);

        $status = $routine->is_active ? 'activée' : 'désactivée';

        return response()->json([
            'success' => true,
            'message' => "Routine {$status} avec succès",
            'is_active' => $routine->is_active
        ]);
    }
}
