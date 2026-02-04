<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\LeaveRequest;
use App\Models\LeaveType;
use App\Models\Employee;
use App\Notifications\NewLeaveRequestNotification;
use Illuminate\Http\Request;
use Carbon\Carbon;
use OpenApi\Attributes as OA;

class LeaveController extends Controller
{
  #[OA\Get(
    path: "/api/v1/leaves/types",
    summary: "Get all leave types",
    tags: ["Leaves"],
    security: [["sanctum" => []]],
    responses: [
      new OA\Response(response: 200, description: "Leave types list")
    ]
  )]
  public function types()
  {
    $types = LeaveType::where('active', true)->get();
    return response()->json(['success' => true, 'data' => $types]);
  }

  #[OA\Get(
    path: "/api/v1/leaves",
    summary: "Get leave requests",
    tags: ["Leaves"],
    security: [["sanctum" => []]],
    responses: [
      new OA\Response(response: 200, description: "Leave requests list")
    ]
  )]
  public function index(Request $request)
  {
    $user = $request->user();
    $query = LeaveRequest::with(['employee', 'leaveType']);

    // If not admin/hr, only show their own requests
    // For now, let's just show all or filter by employee if provided
    // Check permissions
    // If not admin/hr, check if manager or just employee
    if (!$user->hasRole('admin') && !$user->hasRole('hr_manager')) {
      if (!$user->employee_id && !$user->employee) {
        return response()->json(['success' => false, 'message' => 'User not linked to employee'], 403);
      }

      $employee = $user->employee ?? Employee::find($user->employee_id);

      // If user is a manager (has 'dept_manager' role for example, or simply has direct reports), allow viewing those requests
      // We can also check if they are requesting for themselves specifically
      if ($request->has('view_team') && $request->view_team == 'true') {
        // Get IDs of direct reports
        $reportIds = $employee->directReports()->pluck('id')->toArray();
        $query->whereIn('employee_id', $reportIds);
      } else {
        // Default: view own requests
        $query->where('employee_id', $employee->id);
      }
    } elseif ($request->has('employee_id')) {
      $query->where('employee_id', $request->employee_id);
    }

    $leaves = $query->orderBy('created_at', 'desc')->paginate(20);

    return response()->json([
      'success' => true,
      'data' => $leaves->items(),
      'meta' => [
        'total' => $leaves->total(),
        'per_page' => $leaves->perPage(),
        'current_page' => $leaves->currentPage()
      ]
    ]);
  }

  #[OA\Post(
    path: "/api/v1/leaves",
    summary: "Create a leave request",
    tags: ["Leaves"],
    security: [["sanctum" => []]],
    responses: [
      new OA\Response(response: 200, description: "Leave request created")
    ]
  )]
  public function store(Request $request)
  {
    $user = $request->user();
    if (!$user->employee_id && !$user->employee) {
      return response()->json(['success' => false, 'message' => 'User not linked to employee'], 400);
    }

    $employeeId = $user->employee->id ?? $user->employee_id;

    $validated = $request->validate([
      'leave_type_id' => 'required|exists:leave_types,id',
      'start_date' => 'required|date',
      'end_date' => 'required|date|after_or_equal:start_date',
      'reason' => 'nullable|string',
    ]);

    $start = Carbon::parse($validated['start_date']);
    $end = Carbon::parse($validated['end_date']);
    $totalDays = $start->diffInDays($end) + 1;

    $leave = LeaveRequest::create([
      'employee_id' => $employeeId,
      'leave_type_id' => $validated['leave_type_id'],
      'start_date' => $validated['start_date'],
      'end_date' => $validated['end_date'],
      'total_days' => $totalDays,
      'reason' => $validated['reason'],
      'status' => 'pending'
    ]);

    // Notify Manager
    $employee = Employee::find($employeeId);
    if ($employee && $employee->manager && $employee->manager->user) {
      $employee->manager->user->notify(new NewLeaveRequestNotification($leave));
    }

    return response()->json([
      'success' => true,
      'data' => $leave,
      'message' => 'Leave request submitted successfully'
    ]);
  }

  #[OA\Put(
    path: "/api/v1/leaves/{id}",
    summary: "Update/Approve/Reject leave request",
    tags: ["Leaves"],
    security: [["sanctum" => []]],
    responses: [
      new OA\Response(response: 200, description: "Leave request updated")
    ]
  )]
  public function update(Request $request, $id)
  {
    $leave = LeaveRequest::findOrFail($id);

    $data = $request->all();

    if (isset($data['status']) && in_array($data['status'], ['approved', 'rejected'])) {
      $data['approved_by'] = $request->user()->id;
      $data['approved_at'] = Carbon::now();
    }

    $leave->update($data);

    return response()->json([
      'success' => true,
      'data' => $leave,
      'message' => 'Leave request updated successfully'
    ]);
  }
}
