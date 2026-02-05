<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\Employee;
use Illuminate\Http\Request;
use Carbon\Carbon;
use OpenApi\Attributes as OA;

class AttendanceController extends Controller
{
  #[OA\Get(
    path: "/api/v1/attendance",
    summary: "Get attendance list",
    tags: ["Attendance"],
    security: [["sanctum" => []]],
    responses: [
      new OA\Response(response: 200, description: "Attendance records List")
    ]
  )]
  public function index(Request $request)
  {
    $query = Attendance::with('employee');

    if ($request->has('date')) {
      $query->where('record_date', $request->date);
    }

    if ($request->has('employee_id')) {
      $query->where('employee_id', $request->employee_id);
    }

    $attendances = $query->orderBy('record_date', 'desc')->paginate(20);

    return response()->json([
      'success' => true,
      'data' => $attendances->items(),
      'meta' => [
        'total' => $attendances->total(),
        'per_page' => $attendances->perPage(),
        'current_page' => $attendances->currentPage()
      ]
    ]);
  }

  #[OA\Post(
    path: "/api/v1/attendance/clock-in",
    summary: "Clock in current user",
    tags: ["Attendance"],
    security: [["sanctum" => []]],
    responses: [
      new OA\Response(response: 200, description: "Clocked in successfully")
    ]
  )]
  public function clockIn(Request $request)
  {
    $user = $request->user();
    if (!$user->employee_id && !$user->employee) {
      return response()->json(['success' => false, 'message' => 'User not linked to employee'], 400);
    }

    $employeeId = $user->employee->id ?? $user->employee_id;
    $today = Carbon::today()->toDateString();

    $attendance = Attendance::firstOrCreate(
      ['employee_id' => $employeeId, 'record_date' => $today],
      ['status' => 'present']
    );

    if ($attendance->clock_in_time) {
      return response()->json(['success' => false, 'message' => 'Already clocked in today'], 422);
    }

    $attendance->update([
      'clock_in_time' => Carbon::now(),
      'clock_in_ip' => $request->ip()
    ]);

    return response()->json([
      'success' => true,
      'data' => $attendance,
      'message' => 'Clocked in successfully'
    ]);
  }

  #[OA\Post(
    path: "/api/v1/attendance/clock-out",
    summary: "Clock out current user",
    tags: ["Attendance"],
    security: [["sanctum" => []]],
    responses: [
      new OA\Response(response: 200, description: "Clocked out successfully")
    ]
  )]
  public function clockOut(Request $request)
  {
    $user = $request->user();
    $employeeId = $user->employee->id ?? $user->employee_id;
    $today = Carbon::today()->toDateString();

    $attendance = Attendance::where('employee_id', $employeeId)
      ->where('record_date', $today)
      ->first();

    if (!$attendance || !$attendance->clock_in_time) {
      return response()->json(['success' => false, 'message' => 'Not clocked in today'], 422);
    }

    if ($attendance->clock_out_time) {
      return response()->json(['success' => false, 'message' => 'Already clocked out today'], 422);
    }

    $clockOutTime = Carbon::now();
    $clockInTime = Carbon::parse($attendance->clock_in_time);
    $totalHours = $clockInTime->diffInMinutes($clockOutTime) / 60;

    $attendance->update([
      'clock_out_time' => $clockOutTime,
      'clock_out_ip' => $request->ip(),
      'total_hours' => round($totalHours, 2)
    ]);

    return response()->json([
      'success' => true,
      'data' => $attendance,
      'message' => 'Clocked out successfully'
    ]);
  }

  #[OA\Put(
    path: "/api/v1/attendance/{id}",
    summary: "Update attendance record",
    tags: ["Attendance"],
    security: [["sanctum" => []]],
    responses: [
      new OA\Response(response: 200, description: "Updated successfully")
    ]
  )]
  public function update(Request $request, $id)
  {
    $attendance = Attendance::findOrFail($id);
    $attendance->update($request->all());

    return response()->json([
      'success' => true,
      'data' => $attendance,
      'message' => 'Attendance updated successfully'
    ]);
  }
}
