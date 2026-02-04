<?php

namespace App\Policies;

use App\Models\PayrollRecord;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class PayrollRecordPolicy
{
  /**
   * Determine whether the user can view any models.
   */
  public function viewAny(User $user): bool
  {
    return true; // Controller filters data
  }

  /**
   * Determine whether the user can view the model.
   */
  public function view(User $user, PayrollRecord $payrollRecord): bool
  {
    if ($user->hasRole('admin') || $user->hasRole('finance_manager')) {
      return true;
    }

    return $payrollRecord->employee_id === $user->employee_id;
  }

  /**
   * Determine whether the user can create models.
   */
  public function create(User $user): bool
  {
    return $user->hasRole('admin') || $user->hasRole('finance_manager');
  }

  /**
   * Determine whether the user can update the model.
   */
  public function update(User $user, PayrollRecord $payrollRecord): bool
  {
    return $user->hasRole('admin') || $user->hasRole('finance_manager');
  }

  /**
   * Determine whether the user can delete the model.
   */
  public function delete(User $user, PayrollRecord $payrollRecord): bool
  {
    return $user->hasRole('admin') || $user->hasRole('finance_manager');
  }
}
