<?php

return [

  /*
  |--------------------------------------------------------------------------
  | Custom API Messages
  |--------------------------------------------------------------------------
  */

  // Success Messages
  'success' => [
    'created' => 'Created successfully',
    'updated' => 'Updated successfully',
    'deleted' => 'Deleted successfully',
    'saved' => 'Saved successfully',
    'submitted' => 'Submitted successfully',
  ],

  // Error Messages
  'error' => [
    'not_found' => 'Item not found',
    'unauthorized' => 'You are not authorized to perform this action',
    'forbidden' => 'Access forbidden',
    'server_error' => 'Server error occurred',
    'validation_failed' => 'Validation failed',
  ],

  // Employee Messages
  'employee' => [
    'created' => 'Employee added successfully',
    'updated' => 'Employee updated successfully',
    'deleted' => 'Employee deleted successfully',
    'not_found' => 'Employee not found',
  ],

  // Attendance Messages
  'attendance' => [
    'clock_in' => 'Clocked in successfully',
    'clock_out' => 'Clocked out successfully',
    'already_clocked_in' => 'You have already clocked in',
    'not_clocked_in' => 'You have not clocked in yet',
  ],

  // Leave Messages
  'leave' => [
    'submitted' => 'Leave request submitted successfully',
    'approved' => 'Leave request approved',
    'rejected' => 'Leave request rejected',
    'cancelled' => 'Leave request cancelled',
    'insufficient_balance' => 'Insufficient leave balance',
  ],

  // Payroll Messages
  'payroll' => [
    'generated' => 'Payslip generated successfully',
    'processed' => 'Payroll processed successfully',
    'approved' => 'Payslip approved',
  ],

  // Authentication Messages
  'auth' => [
    'login_success' => 'Login successful',
    'logout_success' => 'Logout successful',
    'invalid_credentials' => 'Invalid credentials',
    'password_changed' => 'Password changed successfully',
  ],

  // Recruitment Messages
  'recruitment' => [
    'job_posted' => 'Job posted successfully',
    'application_submitted' => 'Application submitted successfully',
    'candidate_added' => 'Candidate added successfully',
    'stage_updated' => 'Candidate stage updated',
  ],

  // Onboarding Messages
  'onboarding' => [
    'checklist_created' => 'Onboarding checklist created successfully',
    'task_completed' => 'Task completed successfully',
    'progress_updated' => 'Progress updated',
  ],

];
