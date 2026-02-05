<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\LeaveRequest;
use App\Models\Interview;
use Illuminate\Http\Request;
use Carbon\Carbon;

class CalendarController extends Controller
{
  public function index(Request $request)
  {
    $start = $request->query('start') ? Carbon::parse($request->query('start')) : Carbon::now()->startOfMonth();
    $end = $request->query('end') ? Carbon::parse($request->query('end')) : Carbon::now()->endOfMonth();

    $events = [];

    // 1. Leaves
    $leaves = LeaveRequest::with(['employee', 'leaveType'])
      ->where('status', 'approved')
      ->where(function ($query) use ($start, $end) {
        $query->whereBetween('start_date', [$start, $end])
          ->orWhereBetween('end_date', [$start, $end]);
      })
      ->get();

    foreach ($leaves as $leave) {
      $startDate = Carbon::parse($leave->start_date);
      $endDate = Carbon::parse($leave->end_date);

      $events[] = [
        'id' => 'leave-' . $leave->id,
        'title' => 'إجازة: ' . $leave->employee->full_name,
        'start' => $startDate->format('Y-m-d'),
        'end' => $endDate->addDay()->format('Y-m-d'), // FullCalendar end date is exclusive
        'type' => 'leave',
        'color' => '#10b981', // green
        'extendedProps' => [
          'employee' => $leave->employee->full_name,
          'leave_type' => $leave->leaveType->name,
        ]
      ];
    }

    // 2. Birthdays (Annually)
    // We need to find employees whose birthday falls within the month of the current view
    // FullCalendar usually loads a bit more than one month, so we'll check the month of $start and $end
    $employeesWithBirthdays = Employee::whereNotNull('date_of_birth')->get();

    foreach ($employeesWithBirthdays as $employee) {
      // Adjust birthday to the current year being viewed
      $birthdayThisYear = Carbon::parse($employee->date_of_birth)->year($start->year);

      if ($birthdayThisYear->between($start, $end)) {
        $events[] = [
          'id' => 'birthday-' . $employee->id,
          'title' => 'ميلاد: ' . $employee->full_name,
          'start' => $birthdayThisYear->format('Y-m-d'),
          'allDay' => true,
          'type' => 'birthday',
          'color' => '#3b82f6', // blue
        ];
      }
    }

    // 3. Interviews
    $interviews = Interview::with(['jobApplication.candidate'])
      ->whereBetween('start_time', [$start, $end])
      ->get();

    foreach ($interviews as $interview) {
      $events[] = [
        'id' => 'interview-' . $interview->id,
        'title' => 'مقابلة: ' . $interview->jobApplication->candidate->full_name,
        'start' => $interview->start_time->toIso8601String(),
        'end' => $interview->end_time->toIso8601String(),
        'type' => 'interview',
        'color' => '#f59e0b', // orange
        'extendedProps' => [
          'candidate' => $interview->jobApplication->candidate->full_name,
          'location' => $interview->location,
        ]
      ];
    }

    return response()->json([
      'success' => true,
      'data' => $events
    ]);
  }
}
