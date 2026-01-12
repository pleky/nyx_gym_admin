<?php

namespace Database\Seeders;

use App\Models\Gym;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class OwnerSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {

        User::create([
            'name' => 'Nyx Gym Owner',
            'email' => 'owner@nyxgym.com',
            'password' => Hash::make('password'),
            'role' => 'OWNER',
            'phone' => '+6281111111111',
            'status' => 'ACTIVE',
            'gym_id' => Gym::firstOrFail()->id,
            'email_verified_at' => now(),
        ]);

        $this->command->info('Owner user created: owner@nyxgym.com');
        $this->command->info('Password: password');
        $this->command->info('Role: OWNER');
        
    }
}
