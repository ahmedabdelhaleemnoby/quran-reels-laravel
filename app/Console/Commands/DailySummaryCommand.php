<?php

namespace App\Console\Commands;

use App\Mail\DailySummaryMail;
use App\Models\Attendance;
use App\Models\Employee;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class DailySummaryCommand extends Command
{
  protected $signature = 'daily:summary';
  protected $description = 'Send daily summary email to managers about absent employees and expiring contracts';

  public function handle()
  {
    $this->info('Generating daily summary...');

    // Get today's date
    $today = Carbon::today();

    // Get employees who were expected but didn't mark attendance today
    $absentEmployees = Employee::where('employment_status', 'active')
      ->whereDoesntHave('attendances', function ($query) use ($today) {
        $query->whereDate('record_date', $today);
      })
      ->get();

    // Get employees with contracts expiring within 30 days
    $expiringContracts = Employee::where('employment_status', 'active')
      ->where('employment_type', 'contract')
      ->whereNotNull('contract_end_date')
      ->whereBetween('contract_end_date', [$today, $today->copy()->addDays(30)])
      ->orderBy('contract_end_date')
      ->get();

    // Get birthdays today
    $birthdays = Employee::whereMonth('date_of_birth', $today->month)
      ->whereDay('date_of_birth', $today->day)
      ->get();

    // Get probation endings within 7 days
    $probationEndings = Employee::where('employment_status', 'active')
      ->whereNotNull('probation_end_date')
      ->whereBetween('probation_end_date', [$today, $today->copy()->addDays(7)])
      ->orderBy('probation_end_date')
      ->get();

    $this->info(sprintf('Found %d absent employees', $absentEmployees->count()));
    $this->info(sprintf('Found %d expiring contracts', $expiringContracts->count()));
    $this->info(sprintf('Found %d birthdays today', $birthdays->count()));
    $this->info(sprintf('Found %d probation endings soon', $probationEndings->count()));

    // Get all users with admin role or dept_manager role
    $recipients = User::role(['admin', 'dept_manager'])->get();

    if ($recipients->isEmpty()) {
      $this->warn('No managers found to send email to');
      \App\Models\ScheduledEmailLog::create([
        'type' => 'daily_summary',
        'recipients_count' => 0,
        'absent_count' => $absentEmployees->count(),
        'expiring_contracts_count' => $expiringContracts->count(),
        'birthdays_count' => $birthdays->count(),
        'probation_endings_count' => $probationEndings->count(),
        'status' => 'skipped',
        'error_message' => 'No managers found to send email to',
      ]);
      return 0;
    }

    try {
      // Send email to each manager
      foreach ($recipients as $recipient) {
        Mail::to($recipient->email)->send(
          new DailySummaryMail($absentEmployees, $expiringContracts, $birthdays, $probationEndings, $today)
        );
        $this->info(sprintf('Email sent to %s', $recipient->email));
      }

      // Log success
      \App\Models\ScheduledEmailLog::create([
        'type' => 'daily_summary',
        'recipients_count' => $recipients->count(),
        'absent_count' => $absentEmployees->count(),
        'expiring_contracts_count' => $expiringContracts->count(),
        'birthdays_count' => $birthdays->count(),
        'probation_endings_count' => $probationEndings->count(),
        'status' => 'success',
      ]);

      $this->info('Daily summary sent successfully!');
    } catch (\Exception $e) {
      $this->error('Failed to send daily summary: ' . $e->getMessage());

      // Log failure
      \App\Models\ScheduledEmailLog::create([
        'type' => 'daily_summary',
        'recipients_count' => $recipients->count(),
        'absent_count' => $absentEmployees->count(),
        'expiring_contracts_count' => $expiringContracts->count(),
        'birthdays_count' => $birthdays->count(),
        'probation_endings_count' => $probationEndings->count(),
        'status' => 'failed',
        'error_message' => $e->getMessage(),
      ]);
    }
    return 0;
  }
}
