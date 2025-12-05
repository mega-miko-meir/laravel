<?php

namespace Database\Seeders;

use App\Models\Employee;
use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        Employee::create(
            [
                'first_name' => 'Aiym',
                'last_name' => 'Baimkuhanbetova',
                'middle_name' => '',
                'email' => 'aiym.b@nobel.kz',
                'team' => '2',
                'manager_id' => '2',
                'city' => 'Almaty',
                'position' => 'MP',
                'hiring_date' => '2023-06-06'
            ]
        );

        Employee::create(
            [
                'first_name' => 'Aigerim',
                'last_name' => 'Ibrayeva',
                'middle_name' => '',
                'email' => 'aigerim.ibrayeva@nobel.kz',
                'team' => '1',
                'manager_id' => '2',
                'city' => 'Almaty',
                'position' => 'MP',
                'hiring_date' => '2022-09-10'
            ]
        );


        User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);
    }
}
