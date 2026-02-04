<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Employee;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
  public function run(): void
  {
    // 0. Demo Admin User
    $demoAdminEmployee = Employee::where('email', 'admin@demo.com')->first();
    if ($demoAdminEmployee) {
      $demoAdminUser = User::firstOrCreate(
        ['email' => 'admin@demo.com'],
        [
          'name' => 'Demo Admin',
          'password' => Hash::make('password'),
          'employee_id' => $demoAdminEmployee->id,
        ]
      );
      $demoAdminUser->assignRole('admin');
    }

    // 1. Admin User (already created if logged on via AuthController demo, but let's Ensure existence)
    // Find employee with admin email
    $adminEmployee = Employee::where('email', 'admin@democorp.com')->first();
    if ($adminEmployee) {
      $adminUser = User::firstOrCreate(
        ['email' => 'admin@democorp.com'],
        [
          'name' => 'Admin User',
          'password' => Hash::make('password'),
          'employee_id' => $adminEmployee->id,
        ]
      );
      $adminUser->assignRole('admin');
    }

    // 2. Department Manager (HR)
    // Find an employee in HR who is NOT the admin
    $hrManager = Employee::where('department', 'HR')
      ->where('email', '!=', 'admin@democorp.com')
      ->first();

    if ($hrManager) {
      $hrUser = User::firstOrCreate(
        ['email' => $hrManager->email],
        [
          'name' => $hrManager->first_name . ' ' . $hrManager->last_name,
          'password' => Hash::make('password'),
          'employee_id' => $hrManager->id,
        ]
      );
      $hrUser->assignRole('dept_manager');
    }

    // 3. Regular Employee (Engineering)
    $engineer = Employee::where('department', 'Engineering')->first();
    if ($engineer) {
      $empUser = User::firstOrCreate(
        ['email' => $engineer->email],
        [
          'name' => $engineer->first_name . ' ' . $engineer->last_name,
          'password' => Hash::make('password'),
          'employee_id' => $engineer->id,
        ]
      );
      $empUser->assignRole('employee');
    }
  }
}
