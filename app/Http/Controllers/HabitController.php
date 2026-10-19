<?php

namespace App\Http\Controllers;

use App\Models\Habit;
use App\Models\HabitLog;
use Carbon\Carbon;
use Illuminate\Http\Request;

class HabitController extends Controller
{
    public function index()
    {
        // شروع هفته از دوشنبه (یا شنبه بسته به نیاز شما: Carbon::SATURDAY)
        $startOfWeek = Carbon::now()->startOfWeek(Carbon::MONDAY);
        $endOfWeek = Carbon::now()->endOfWeek(Carbon::SUNDAY);

        // ساخت آرایه ۷ روز هفته جاری
        $weekDays = collect(range(0, 6))->map(function ($offset) use ($startOfWeek) {
            $date = $startOfWeek->copy()->addDays($offset);
            return [
                'date' => $date->toDateString(),
                'day_name' => $date->translatedFormat('D'), // نام کوتاه روز
                'day_number' => $date->format('d'),
                'is_today' => $date->isToday(),
            ];
        });

        // دریافت عادت‌ها به همراه لاگ‌های ثبت‌شده در این هفته
        $habits = Habit::with(['logs' => function ($query) use ($startOfWeek, $endOfWeek) {
            $query->whereBetween('completed_date', [$startOfWeek->toDateString(), $endOfWeek->toDateString()]);
        }])->latest()->get();

        return view('habits.index', compact('habits', 'weekDays'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'category' => 'required|string|max:50',
            'color' => 'required|string|max:20',
            'target_per_week' => 'required|integer|min:1|max:7',
        ]);

        Habit::create($validated);

        return redirect()->route('habits.index')->with('success', 'عادت جدید با موفقیت ساخته شد!');
    }

    // متد تیک زدن یا برداشتن تیک به صورت AJAX
    public function toggleDay(Request $request, Habit $habit)
    {
        $request->validate([
            'date' => 'required|date',
        ]);

        $date = $request->input('date');

        $existing = HabitLog::where('habit_id', $habit->id)
            ->where('completed_date', $date)
            ->first();

        if ($existing) {
            $existing->delete();
            $status = 'removed';
        } else {
            HabitLog::create([
                'habit_id' => $habit->id,
                'completed_date' => $date,
            ]);
            $status = 'added';
        }

        return response()->json([
            'status' => $status,
            'habit_id' => $habit->id,
            'date' => $date,
        ]);
    }

    public function destroy(Habit $habit)
    {
        $habit->delete();
        return redirect()->route('habits.index');
    }
}
