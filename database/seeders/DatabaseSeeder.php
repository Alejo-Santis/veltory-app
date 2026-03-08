<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        User::factory()->create([
            'name'  => 'Admin Veltory',
            'email' => 'admin@veltory.test',
        ]);

        $this->call([
            RoleSeeder::class,
            UnitSeeder::class,
            CategorySeeder::class,
            ProductSeeder::class,
        ]);
    }
}
