<?php

namespace Database\Seeders;

use App\Models\Dependency;
use App\Models\Impulse;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

class DemoDataSeeder extends Seeder
{
    public function run(): void
    {
        $userId = User::first()?->id;
        if (! $userId) {
            $this->command->warn('Нет юзера — создай сначала.');

            return;
        }

        // профиль каждой зависимости по неделям (4 нед назад → эта неделя)
        // [импульсов в неделю, resist rate %]  — видно прогресс + flatline на 3-й нед
        $profiles = [
            'Порно' => [
                [18, 45], [14, 55], [16, 50], [9, 72], // 3я неделя — откат (flatline)
            ],
            'Никотин' => [ // тяжелее всех, медленный рост — физическая тяга
                [25, 32], [22, 40], [20, 44], [17, 53],
            ],
            'Рилсы / соцсети' => [
                [30, 50], [24, 62], [19, 70], [12, 81],
            ],
        ];

        $triggers = ['скука', 'стресс', 'трудная задача', 'вечер', 'тревога', 'усталость', 'после ленты'];
        $comments = [
            'устоял, пошёл умылся', 'тянуло сильно, переждал 10 минут', '',
            'сорвался на автомате, даже не заметил', 'записал и отпустило', '',
            'после трудного таска накрыло', 'лёг спать вместо этого', '',
        ];

        foreach ($profiles as $depName => $weeks) {
            $dep = Dependency::firstOrCreate(
                ['user_id' => $userId, 'name' => $depName],
                ['is_active' => true]
            );

            // неделя 0 = самая старая (4 недели назад), 3 = текущая
            foreach ($weeks as $w => [$count, $rate]) {
                $weekStart = Carbon::now()->startOfWeek()->subWeeks(3 - $w);
                $resistedCount = (int) round($count * $rate / 100);

                for ($i = 0; $i < $count; $i++) {
                    // равномерно разбрасываем по 7 дням недели, дневное время
                    $ts = (clone $weekStart)
                        ->addDays(rand(0, 6))
                        ->addHours(rand(8, 23))
                        ->addMinutes(rand(0, 59));

                    // не уходим в будущее для текущей недели
                    if ($ts->isFuture()) {
                        $ts = Carbon::now()->subMinutes(rand(10, 300));
                    }

                    Impulse::create([
                        'user_id' => $userId,
                        'dependency_id' => $dep->id,
                        'resisted' => $i < $resistedCount, // первые N — устоял
                        'trigger' => $triggers[array_rand($triggers)],
                        'comment' => $comments[array_rand($comments)],
                        'created_at' => $ts,
                        'updated_at' => $ts,
                    ]);
                }
            }
        }

        $this->command->info('Демо-данные за 4 недели созданы.');
    }
}
