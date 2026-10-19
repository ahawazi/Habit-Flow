<!DOCTYPE html>
<html lang="fa" dir="rtl" class="dark">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>HabitFlow - داشبورد لاراول ردیابی عادت‌ها</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link href="https://cdn.jsdelivr.net/gh/rastikerdar/vazirmatn@v33.003/Vazirmatn-font-face.css" rel="stylesheet"
        type="text/css" />
    <style>
        body {
            font-family: 'Vazirmatn', sans-serif;
        }
    </style>
</head>

<body class="bg-slate-950 text-slate-100 min-h-screen p-4 md:p-8" x-data="habitTracker()">

    <div class="max-w-6xl mx-auto space-y-6">
        <!-- هدر داشبورد -->
        <header
            class="flex flex-col md:flex-row md:items-center justify-between gap-4 bg-slate-900/90 border border-slate-800 p-6 rounded-2xl">
            <div>
                <h1
                    class="text-2xl font-bold bg-gradient-to-r from-emerald-400 to-cyan-400 bg-clip-text text-transparent">
                    داشبورد عادت‌های روزانه (Laravel HabitFlow)
                </h1>
                <p class="text-slate-400 text-sm mt-1">اهداف روزانه خود را ثبت کنید و نمودار استریک هفتگی را زنده ببینید
                </p>
            </div>
            <button @click="showModal = true"
                class="bg-emerald-500 hover:bg-emerald-400 text-slate-950 font-bold px-5 py-2.5 rounded-xl transition">
                + عادت جدید
            </button>
        </header>

        <!-- کارت‌های آمار سریع -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div class="bg-slate-900 border border-slate-800 p-5 rounded-2xl">
                <span class="text-slate-400 text-sm">کل عادت‌های فعال</span>
                <div class="text-3xl font-bold mt-2" x-text="habits.length"></div>
            </div>
            <div class="bg-slate-900 border border-slate-800 p-5 rounded-2xl">
                <span class="text-slate-400 text-sm">تیک‌های ثبت‌شده این هفته</span>
                <div class="text-3xl font-bold text-emerald-400 mt-2" x-text="totalCompletedThisWeek()"></div>
            </div>
            <div class="bg-slate-900 border border-slate-800 p-5 rounded-2xl">
                <span class="text-slate-400 text-sm">درصد تحقق اهداف هفتگی</span>
                <div class="text-3xl font-bold text-cyan-400 mt-2" x-text="weeklyCompletionRate() + '%'"></div>
            </div>
        </div>

        <!-- نمودار هفتگی -->
        <div class="bg-slate-900 border border-slate-800 p-6 rounded-2xl">
            <h2 class="text-lg font-semibold mb-4">نمودار پیشرفت هفتگی</h2>
            <div class="h-64">
                <canvas id="weeklyChart"></canvas>
            </div>
        </div>

        <!-- جدول ماتریس عادت‌ها -->
        <div class="bg-slate-900 border border-slate-800 rounded-2xl overflow-x-auto">
            <table class="w-full text-right border-collapse">
                <thead>
                    <tr class="border-b border-slate-800 text-slate-400 text-sm">
                        <th class="p-4">عادت</th>
                        <template x-for="day in weekDays" :key="day.date">
                            <th class="p-3 text-center" :class="day.is_today ? 'text-emerald-400 font-bold' : ''">
                                <div x-text="day.day_name"></div>
                                <div class="text-xs opacity-75" x-text="day.day_number"></div>
                            </th>
                        </template>
                        <th class="p-4 text-center">پیشرفت هفتگی</th>
                        <th class="p-4 text-center">حذف</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-800/60">
                    <template x-for="habit in habits" :key="habit.id">
                        <tr class="hover:bg-slate-800/30 transition">
                            <td class="p-4">
                                <div class="flex items-center gap-3">
                                    <span class="w-3 h-3 rounded-full"
                                        :style="`background-color: ${habit.color}`"></span>
                                    <div>
                                        <div class="font-medium" x-text="habit.name"></div>
                                        <div class="text-xs text-slate-400" x-text="habit.category"></div>
                                    </div>
                                </div>
                            </td>

                            <!-- دکمه‌های ۷ روز هفته -->
                            <template x-for="day in weekDays" :key="day.date">
                                <td class="p-2 text-center">
                                    <button @click="toggleHabitDay(habit, day.date)"
                                        class="w-9 h-9 rounded-xl border transition flex items-center justify-center mx-auto"
                                        :style="isCompleted(habit, day.date) ?
                                            `background-color: ${habit.color}; border-color: ${habit.color}` : ''"
                                        :class="isCompleted(habit, day.date) ? 'text-slate-950 font-bold' :
                                            'border-slate-700 hover:border-slate-500 text-transparent'">
                                        ✓
                                    </button>
                                </td>
                            </template>

                            <!-- نوار پیشرفت هر عادت -->
                            <td class="p-4 w-44">
                                <div class="flex items-center justify-between text-xs mb-1">
                                    <span x-text="`${habit.completed_dates.length} از ${habit.target_per_week}`"></span>
                                    <span
                                        x-text="Math.min(100, Math.round((habit.completed_dates.length / habit.target_per_week) * 100)) + '%'"></span>
                                </div>
                                <div class="w-full h-2 bg-slate-800 rounded-full overflow-hidden">
                                    <div class="h-full transition-all duration-300"
                                        :style="`width: ${Math.min(100, (habit.completed_dates.length / habit.target_per_week) * 100)}%; background-color: ${habit.color}`">
                                    </div>
                                </div>
                            </td>

                            <!-- حذف عادت -->
                            <td class="p-4 text-center">
                                <form :action="`/habits/${habit.id}`" method="POST"
                                    onsubmit="return confirm('حذف شود؟')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit"
                                        class="text-rose-400 hover:text-rose-300 text-sm">حذف</button>
                                </form>
                            </td>
                        </tr>
                    </template>
                </tbody>
            </table>
        </div>
    </div>

    <!-- مودال افزودن عادت جدید -->
    <div x-show="showModal" x-cloak class="fixed inset-0 bg-black/70 flex items-center justify-center p-4 z-50">
        <div @click.away="showModal = false"
            class="bg-slate-900 border border-slate-800 rounded-2xl max-w-md w-full p-6">
            <h3 class="text-lg font-bold mb-4">ساخت عادت جدید</h3>
            <form action="{{ route('habits.store') }}" method="POST" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-sm text-slate-400 mb-1">عنوان عادت</label>
                    <input type="text" name="name" required placeholder="مثلاً: مطالعه ۳۰ دقیقه کتاب"
                        class="w-full bg-slate-950 border border-slate-800 rounded-xl px-4 py-2.5 text-sm">
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-sm text-slate-400 mb-1">دسته‌بندی</label>
                        <select name="category"
                            class="w-full bg-slate-950 border border-slate-800 rounded-xl px-3 py-2.5 text-sm">
                            <option value="سلامتی">سلامتی</option>
                            <option value="ورزش">ورزش</option>
                            <option value="ذهن و تمرکز">ذهن و تمرکز</option>
                            <option value="کار و مهارت">کار و مهارت</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm text-slate-400 mb-1">هدف در هفته (روز)</label>
                        <input type="number" name="target_per_week" min="1" max="7" value="5"
                            class="w-full bg-slate-950 border border-slate-800 rounded-xl px-3 py-2.5 text-sm">
                    </div>
                </div>
                <div>
                    <label class="block text-sm text-slate-400 mb-1">رنگ</label>
                    <input type="color" name="color" value="#10b981"
                        class="w-full h-10 rounded-xl bg-slate-950 border border-slate-800 cursor-pointer">
                </div>
                <div class="flex justify-end gap-2 pt-3">
                    <button type="button" @click="showModal = false"
                        class="px-4 py-2 rounded-xl bg-slate-800 text-sm">انصراف</button>
                    <button type="submit"
                        class="px-5 py-2 rounded-xl bg-emerald-500 text-slate-950 font-bold text-sm">ذخیره</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function habitTracker() {
            return {
                showModal: false,
                @php
                    $formattedHabits = $habits->map(function ($h) {
                        return [
                            'id' => $h->id,
                            'name' => $h->name,
                            'category' => $h->category,
                            'color' => $h->color,
                            'target_per_week' => $h->target_per_week,
                            'completed_dates' => $h->logs->pluck('completed_date')->map(fn($d) => (string) $d)->values(),
                        ];
                    });
                @endphp
                weekDays: {{ Js::from($weekDays) }},
                habits: {{ Js::from($formattedHabits) }},
                chartInstance: null,

                init() {
                    this.renderChart();
                },

                isCompleted(habit, date) {
                    return habit.completed_dates.includes(date);
                },

                async toggleHabitDay(habit, date) {
                    // آپدیت خوش‌بینانه (Optimistic UI)
                    if (this.isCompleted(habit, date)) {
                        habit.completed_dates = habit.completed_dates.filter(d => d !== date);
                    } else {
                        habit.completed_dates.push(date);
                    }
                    this.updateChart();

                    // ارسال به کنترلر لاراول
                    await fetch(`/habits/${habit.id}/toggle`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                        },
                        body: JSON.stringify({
                            date
                        })
                    });
                },

                totalCompletedThisWeek() {
                    return this.habits.reduce((sum, h) => sum + h.completed_dates.length, 0);
                },

                weeklyCompletionRate() {
                    const totalTarget = this.habits.reduce((sum, h) => sum + Number(h.target_per_week), 0);
                    if (!totalTarget) return 0;
                    return Math.min(100, Math.round((this.totalCompletedThisWeek() / totalTarget) * 100));
                },

                getDailyCounts() {
                    return this.weekDays.map(day => {
                        return this.habits.filter(h => h.completed_dates.includes(day.date)).length;
                    });
                },

                renderChart() {
                    const ctx = document.getElementById('weeklyChart').getContext('2d');
                    this.chartInstance = new Chart(ctx, {
                        type: 'bar',
                        data: {
                            labels: this.weekDays.map(d => d.day_name),
                            datasets: [{
                                label: 'تعداد عادت‌های انجام‌شده در روز',
                                data: this.getDailyCounts(),
                                backgroundColor: '#10b981',
                                borderRadius: 8
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            scales: {
                                y: {
                                    beginAtZero: true,
                                    ticks: {
                                        stepSize: 1,
                                        color: '#94a3b8'
                                    }
                                },
                                x: {
                                    ticks: {
                                        color: '#94a3b8'
                                    }
                                }
                            }
                        }
                    });
                },

                updateChart() {
                    if (this.chartInstance) {
                        this.chartInstance.data.datasets[0].data = this.getDailyCounts();
                        this.chartInstance.update();
                    }
                }
            }
        }
    </script>
</body>

</html>
