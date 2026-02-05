<?php

namespace App\Policies;

use App\Models\Employee;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class EmployeePolicy
{
  /**
   * Determine whether the user can view any models.
   */
  public function viewAny(User $user): bool
  {
    return $user->hasPermissionTo('manage_employees') ||
      $user->hasPermissionTo('view_dept_salaries') ||
      $user->hasPermissionTo('view_own_salary');
  }

  /**
   * Determine whether the user can view the model.
   */
  public function view(User $user, Employee $employee): bool
  {
    if ($user->hasRole('admin')) {
      return true;
    }

    if ($user->hasPermissionTo('view_dept_salaries')) {
      // Check if user is in the same department
      // This assumes User acts as an employee or is linked to one.
      // Based on previous User model, there is an employee_id.
      $userEmployee = $user->employee;
      if ($userEmployee && $userEmployee->department === $employee->department) {
        return true;
      }
    }

    if ($user->hasPermissionTo('view_own_salary')) {
      return $user->employee_id === $employee->id;
    }

    return false;
  }

  /**
   * Determine whether the user can create models.
   */
  public function create(User $user): bool
  {
    return $user->hasPermissionTo('manage_employees');
  }

  /**
   * Determine whether the user can update the model.
   */
  public function update(User $user, Employee $employee): bool
  {
    if ($user->hasPermissionTo('manage_employees')) {
      return true;
    }

    if ($user->hasPermissionTo('view_dept_salaries')) {
      $userEmployee = $user->employee;
      if ($userEmployee && $userEmployee->department === $employee->department) {
        return true;
      }
    }

    return false;
  }

  /**
   * Determine whether the user can delete the model.
   */
  public function delete(User $user, Employee $employee): bool
  {
    return $user->hasRole('admin') || $user->hasPermissionTo('manage_employees');
  }
}
