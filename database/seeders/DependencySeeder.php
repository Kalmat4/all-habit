<?php

namespace Database\Seeders;

use App\Models\Dependency;
use App\Models\User;
use Illuminate\Database\Seeder;

class DependencySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $userId = User::first()?->id;
        if (! $userId) {
            return;
        }

        collect(['Порно', 'Никотин', 'Рилсы / соцсети'])->each(fn ($name) => Dependency::create(['user_id' => $userId, 'name' => $name])
        );
    }
}
