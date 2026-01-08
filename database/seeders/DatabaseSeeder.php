<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => 'tosinoluwaseun10@gmail.com'],
            [
                'name' => 'Tosin Olarewaju',
                'password' => Hash::make('tosintech91'),

            ]
        );
    }
}
