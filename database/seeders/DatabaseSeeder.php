<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Support\Facades\Hash;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            RolesAndPermissionsSeeder::class,
        ]);

        $admin = User::factory()->create([
            'name' => 'Admin',
            'email' => 'admin@admin.com',
            'phone_number' => '012726252',
            'password' => Hash::make('@dmin123'),
        ]);
        $admin->assignRole('admin');

        $tailor = User::factory()->create([
            'name' => 'Test Tailor',
            'email' => 'tailor@gmail.com',
            'phone_number' => '012345678',
            'password' => Hash::make('Password123'),
        ]);
        $tailor->assignRole('tailor');
    }
}
