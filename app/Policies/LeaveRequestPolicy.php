<?php

namespace App\Policies;

use App\Models\LeaveRequest;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class LeaveRequestPolicy
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
  public function view(User $user, LeaveRequest $leaveRequest): bool
  {
    if ($user->hasRole('admin') || $user->hasRole('hr_manager')) {
      return true;
    }

    return $leaveRequest->employee_id === $user->employee_id;
  }

  /**
   * Determine whether the user can create models.
   */
  public function create(User $user): bool
  {
    return true; // All authenticated users (employees) can request leave
  }

  /**
   * Determine whether the user can update the model.
   */
  public function update(User $user, LeaveRequest $leaveRequest): bool
  {
    if ($user->hasRole('admin') || $user->hasRole('hr_manager')) {
      return true;
    }

    // Employees can only update their own pending requests
    return $leaveRequest->employee_id === $user->employee_id && $leaveRequest->status === 'pending';
  }

  /**
   * Determine whether the user can delete the model.
   */
  public function delete(User $user, LeaveRequest $leaveRequest): bool
  {
    if ($user->hasRole('admin') || $user->hasRole('hr_manager')) {
      return true;
    }

    // Employees can only delete (cancel) their own pending requests
    return $leaveRequest->employee_id === $user->employee_id && $leaveRequest->status === 'pending';
  }
}
