<?php

namespace Database\Seeders;

use App\Models\Gym;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class GymSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */

    public function run(): void
    {
        Gym::create([
            'name' => 'Nyx Gym',
            'address' => '123 Fitness St, Muscle City, Fitland',
            'phone' => '+6281234567890',
        ]);

        $this->command->info('Gym created: Nyx Gym');
    }
}
