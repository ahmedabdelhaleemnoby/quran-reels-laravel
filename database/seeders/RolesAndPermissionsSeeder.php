<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RolesAndPermissionsSeeder extends Seeder
{
  public function run(): void
  {
    // Reset cached roles and permissions
    app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

    // create permissions
    $permissions = [
      'view_all_salaries',
      'view_dept_salaries',
      'view_own_salary',
      'manage_employees',
      'view_logs',
    ];

    foreach ($permissions as $permission) {
      Permission::firstOrCreate(['name' => $permission]);
    }

    // create roles and assign created permissions

    // Employee Role
    $role = Role::firstOrCreate(['name' => 'employee']);
    $role->syncPermissions('view_own_salary');

    // Department Manager Role
    $role = Role::firstOrCreate(['name' => 'dept_manager']);
    $role->syncPermissions(['view_dept_salaries', 'view_own_salary']);

    // Admin Role
    $role = Role::firstOrCreate(['name' => 'admin']);
    $role->syncPermissions(Permission::all());
  }
}
