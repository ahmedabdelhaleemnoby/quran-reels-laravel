<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Faker\Factory as Faker;

class EmployeeSeeder extends Seeder
{
  public function run(): void
  {
    $faker = Faker::create();

    $departments = ['Engineering', 'Sales', 'Marketing', 'HR', 'Finance'];
    $positions = ['Manager', 'Senior Developer', 'Developer', 'Specialist', 'Analyst'];

    // Create demo admin employee
    DB::table('employees')->updateOrInsert(
      ['employee_code' => 'EMP00000'],
      [
        'first_name' => 'Demo',
        'last_name' => 'Admin',
        'email' => 'admin@demo.com',
        'phone' => '+1-555-0000',
        'date_of_birth' => '1985-01-01',
        'gender' => 'male',
        'department' => 'HR',
        'position' => 'System Administrator',
        'employment_status' => 'active',
        'employment_type' => 'full_time',
        'hire_date' => '2015-01-01',
        'salary' => 150000,
        'address' => 'Demo Office, Silicon Valley, CA',
        'updated_at' => now(),
      ]
    );

    // Create admin employee
    DB::table('employees')->updateOrInsert(
      ['employee_code' => 'EMP00001'],
      [
        'first_name' => 'Admin',
        'last_name' => 'User',
        'email' => 'admin@democorp.com',
        'phone' => '+1-555-0100',
        'date_of_birth' => '1990-01-01',
        'gender' => 'male',
        'department' => 'HR',
        'position' => 'Manager',
        'employment_status' => 'active',
        'employment_type' => 'full_time',
        'hire_date' => '2020-01-01',
        'salary' => 100000,
        'address' => '123 Main St, New York, NY',
        'updated_at' => now(),
      ]
    );

    // Create 99 more employees
    for ($i = 2; $i <= 100; $i++) {
      $hireDate = $faker->dateTimeBetween('-5 years', 'now')->format('Y-m-d');

      // Force some recent dates for testing
      if ($i === 2)
        $hireDate = now()->format('Y-m-d'); // Today
      if ($i === 3)
        $hireDate = now()->subDays(2)->format('Y-m-d'); // This week
      if ($i === 4)
        $hireDate = now()->subDays(10)->format('Y-m-d'); // This month
      if ($i <= 10)
        $hireDate = now()->subDays(rand(0, 25))->format('Y-m-d'); // Mostly this month

      DB::table('employees')->updateOrInsert(
        ['employee_code' => 'EMP' . str_pad($i, 5, '0', STR_PAD_LEFT)],
        [
          'first_name' => $faker->firstName,
          'last_name' => $faker->lastName,
          'email' => $faker->unique()->email,
          'phone' => $faker->phoneNumber,
          'date_of_birth' => $faker->date('Y-m-d', '-25 years'),
          'gender' => $faker->randomElement(['male', 'female']),
          'department' => $faker->randomElement($departments),
          'position' => $faker->randomElement($positions),
          'employment_status' => $faker->randomElement(['active', 'active', 'active', 'on_leave']),
          'employment_type' => $faker->randomElement(['full_time', 'full_time', 'part_time']),
          'hire_date' => $hireDate,
          'salary' => $faker->numberBetween(50000, 150000),
          'address' => $faker->address,
          'updated_at' => now(),
        ]
      );
    }
  }
}
