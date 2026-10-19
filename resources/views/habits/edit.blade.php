<!DOCTYPE html>
<html lang="fa" dir="rtl" class="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>ویرایش عادت - HabitFlow</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-slate-950 p-4 text-slate-100 md:p-8">
    <main class="mx-auto max-w-2xl">
        <div class="mb-6 flex items-center justify-between">
            <h1 class="text-2xl font-bold">ویرایش عادت</h1>
            <a href="{{ route('habits.index') }}" class="text-sm text-slate-400 hover:text-white">بازگشت به داشبورد</a>
        </div>

        <form action="{{ route('habits.update', $habit) }}" method="POST" class="space-y-5 rounded-2xl border border-slate-800 bg-slate-900 p-6">
            @csrf
            @method('PUT')
            <div>
                <label for="name" class="mb-2 block text-sm text-slate-400">نام عادت</label>
                <input id="name" name="name" value="{{ old('name', $habit->name) }}" required maxlength="100"
                    class="w-full rounded-xl border border-slate-800 bg-slate-950 px-3 py-2.5">
            </div>
            <div>
                <label for="category" class="mb-2 block text-sm text-slate-400">دسته‌بندی</label>
                <input id="category" name="category" value="{{ old('category', $habit->category) }}" required maxlength="50"
                    class="w-full rounded-xl border border-slate-800 bg-slate-950 px-3 py-2.5">
            </div>
            <div class="grid gap-5 md:grid-cols-2">
                <div>
                    <label for="target_per_week" class="mb-2 block text-sm text-slate-400">هدف هفتگی</label>
                    <input id="target_per_week" type="number" name="target_per_week" min="1" max="7"
                        value="{{ old('target_per_week', $habit->target_per_week) }}" required
                        class="w-full rounded-xl border border-slate-800 bg-slate-950 px-3 py-2.5">
                </div>
                <div>
                    <label for="color" class="mb-2 block text-sm text-slate-400">رنگ</label>
                    <input id="color" type="color" name="color" value="{{ old('color', $habit->color) }}"
                        class="h-11 w-full cursor-pointer rounded-xl border border-slate-800 bg-slate-950">
                </div>
            </div>
            @if ($errors->any())
                <div class="rounded-xl border border-red-500/40 bg-red-500/10 p-3 text-sm text-red-300">
                    {{ $errors->first() }}
                </div>
            @endif
            <button type="submit" class="w-full rounded-xl bg-emerald-400 px-5 py-3 font-bold text-slate-950">ذخیره تغییرات</button>
        </form>
    </main>
</body>
</html>
