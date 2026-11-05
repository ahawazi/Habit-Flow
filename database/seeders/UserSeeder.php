<?php

namespace Database\Seeders;

use App\Models\Habit;
use App\Models\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /**
     * Seed a ready-to-login demo user with sample habits.
     */
    public function run(): void
    {
        $user = User::updateOrCreate(
            ['email' => 'test@example.com'],
            [
                'name' => 'Test User',
                'password' => 'password',
                'email_verified_at' => now(),
            ],
        );

        $habits = [
            ['name' => 'Drink 8 glasses of water', 'category' => 'Health', 'color' => '#10b981', 'target_per_week' => 7],
            ['name' => 'Morning workout', 'category' => 'Fitness', 'color' => '#f59e0b', 'target_per_week' => 5],
            ['name' => 'Meditate 10 minutes', 'category' => 'Mind', 'color' => '#8b5cf6', 'target_per_week' => 7],
            ['name' => 'Deep work session', 'category' => 'Work', 'color' => '#3b82f6', 'target_per_week' => 5],
        ];

        foreach ($habits as $data) {
            $habit = Habit::firstOrCreate(
                ['user_id' => $user->id, 'name' => $data['name']],
                $data,
            );

            if (! $habit->wasRecentlyCreated) {
                continue;
            }

            // Random completions over the last 14 days.
            foreach (range(0, 13) as $daysAgo) {
                if (random_int(1, 100) <= 70) {
                    $habit->logs()->create([
                        'completed_date' => now()->subDays($daysAgo)->toDateString(),
                    ]);
                }
            }
        }
    }
}
