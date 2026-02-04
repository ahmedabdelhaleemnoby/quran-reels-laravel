<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\PerformanceReview;
use App\Models\Okr;
use App\Models\Goal;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

class PerformanceController extends Controller
{
  // ===================== PERFORMANCE REVIEWS =====================

  #[OA\Get(
    path: "/api/v1/performance/reviews",
    summary: "Get performance reviews",
    tags: ["Performance"],
    security: [["sanctum" => []]],
    responses: [new OA\Response(response: 200, description: "Performance reviews list")]
  )]
  public function reviews(Request $request)
  {
    $query = PerformanceReview::with(['employee', 'reviewer']);

    if ($request->has('employee_id')) {
      $query->where('employee_id', $request->employee_id);
    }

    $reviews = $query->orderBy('created_at', 'desc')->paginate(20);

    return response()->json([
      'success' => true,
      'data' => $reviews->items(),
      'meta' => [
        'total' => $reviews->total(),
        'per_page' => $reviews->perPage(),
        'current_page' => $reviews->currentPage()
      ]
    ]);
  }

  #[OA\Post(
    path: "/api/v1/performance/reviews",
    summary: "Create a performance review",
    tags: ["Performance"],
    security: [["sanctum" => []]],
    responses: [new OA\Response(response: 200, description: "Performance review created")]
  )]
  public function createReview(Request $request)
  {
    $validated = $request->validate([
      'employee_id' => 'required|exists:employees,id',
      'review_period' => 'required|string',
      'type' => 'required|in:quarterly,annual,probation,project',
      'overall_rating' => 'nullable|numeric|min:0|max:5',
      'quality_rating' => 'nullable|numeric|min:0|max:5',
      'productivity_rating' => 'nullable|numeric|min:0|max:5',
      'communication_rating' => 'nullable|numeric|min:0|max:5',
      'teamwork_rating' => 'nullable|numeric|min:0|max:5',
      'initiative_rating' => 'nullable|numeric|min:0|max:5',
      'strengths' => 'nullable|string',
      'areas_for_improvement' => 'nullable|string',
      'reviewer_comments' => 'nullable|string',
    ]);

    $validated['reviewer_id'] = $request->user()->id;
    $validated['status'] = 'draft';

    $review = PerformanceReview::create($validated);

    return response()->json([
      'success' => true,
      'data' => $review,
      'message' => 'Performance review created successfully'
    ]);
  }

  #[OA\Put(
    path: "/api/v1/performance/reviews/{id}",
    summary: "Update a performance review",
    tags: ["Performance"],
    security: [["sanctum" => []]],
    responses: [new OA\Response(response: 200, description: "Performance review updated")]
  )]
  public function updateReview(Request $request, $id)
  {
    $review = PerformanceReview::findOrFail($id);
    $review->update($request->all());

    return response()->json([
      'success' => true,
      'data' => $review,
      'message' => 'Performance review updated successfully'
    ]);
  }

  // ===================== OKRs =====================

  #[OA\Get(
    path: "/api/v1/performance/okrs",
    summary: "Get OKRs",
    tags: ["Performance"],
    security: [["sanctum" => []]],
    responses: [new OA\Response(response: 200, description: "OKRs list")]
  )]
  public function okrs(Request $request)
  {
    $query = Okr::with(['employee', 'keyResults'])->where('type', 'objective');

    if ($request->has('employee_id')) {
      $query->where('employee_id', $request->employee_id);
    }

    if ($request->has('period')) {
      $query->where('period', $request->period);
    }

    $okrs = $query->orderBy('created_at', 'desc')->paginate(20);

    return response()->json([
      'success' => true,
      'data' => $okrs->items(),
      'meta' => [
        'total' => $okrs->total(),
        'per_page' => $okrs->perPage(),
        'current_page' => $okrs->currentPage()
      ]
    ]);
  }

  #[OA\Post(
    path: "/api/v1/performance/okrs",
    summary: "Create an OKR",
    tags: ["Performance"],
    security: [["sanctum" => []]],
    responses: [new OA\Response(response: 200, description: "OKR created")]
  )]
  public function createOkr(Request $request)
  {
    $user = $request->user();
    $employeeId = $user->employee_id ?? ($user->employee->id ?? null);

    if (!$employeeId && !$request->has('employee_id')) {
      return response()->json(['success' => false, 'message' => 'Employee ID required'], 400);
    }

    $validated = $request->validate([
      'title' => 'required|string|max:255',
      'description' => 'nullable|string',
      'type' => 'required|in:objective,key_result',
      'parent_id' => 'nullable|exists:okrs,id',
      'period' => 'required|string',
      'start_date' => 'required|date',
      'end_date' => 'required|date|after:start_date',
      'target_value' => 'nullable|integer',
      'priority' => 'nullable|in:low,medium,high,critical',
    ]);

    $validated['employee_id'] = $request->employee_id ?? $employeeId;
    $validated['status'] = 'not_started';
    $validated['progress'] = 0;
    $validated['current_value'] = 0;

    $okr = Okr::create($validated);

    return response()->json([
      'success' => true,
      'data' => $okr,
      'message' => 'OKR created successfully'
    ]);
  }

  #[OA\Put(
    path: "/api/v1/performance/okrs/{id}",
    summary: "Update an OKR",
    tags: ["Performance"],
    security: [["sanctum" => []]],
    responses: [new OA\Response(response: 200, description: "OKR updated")]
  )]
  public function updateOkr(Request $request, $id)
  {
    $okr = Okr::findOrFail($id);

    $data = $request->all();

    // Auto-calculate progress if target and current values are set
    if (isset($data['current_value']) && $okr->target_value > 0) {
      $data['progress'] = min(100, round(($data['current_value'] / $okr->target_value) * 100));
    }

    $okr->update($data);

    return response()->json([
      'success' => true,
      'data' => $okr,
      'message' => 'OKR updated successfully'
    ]);
  }

  // ===================== GOALS =====================

  #[OA\Get(
    path: "/api/v1/performance/goals",
    summary: "Get goals",
    tags: ["Performance"],
    security: [["sanctum" => []]],
    responses: [new OA\Response(response: 200, description: "Goals list")]
  )]
  public function goals(Request $request)
  {
    $query = Goal::with('employee');

    if ($request->has('employee_id')) {
      $query->where('employee_id', $request->employee_id);
    }

    if ($request->has('category')) {
      $query->where('category', $request->category);
    }

    $goals = $query->orderBy('target_date', 'asc')->paginate(20);

    return response()->json([
      'success' => true,
      'data' => $goals->items(),
      'meta' => [
        'total' => $goals->total(),
        'per_page' => $goals->perPage(),
        'current_page' => $goals->currentPage()
      ]
    ]);
  }

  #[OA\Post(
    path: "/api/v1/performance/goals",
    summary: "Create a goal",
    tags: ["Performance"],
    security: [["sanctum" => []]],
    responses: [new OA\Response(response: 200, description: "Goal created")]
  )]
  public function createGoal(Request $request)
  {
    $user = $request->user();
    $employeeId = $user->employee_id ?? ($user->employee->id ?? null);

    if (!$employeeId && !$request->has('employee_id')) {
      return response()->json(['success' => false, 'message' => 'Employee ID required'], 400);
    }

    $validated = $request->validate([
      'title' => 'required|string|max:255',
      'description' => 'nullable|string',
      'category' => 'required|in:career,skill,project,personal,team',
      'target_date' => 'required|date',
      'priority' => 'nullable|in:low,medium,high',
      'milestones' => 'nullable|array',
    ]);

    $validated['employee_id'] = $request->employee_id ?? $employeeId;
    $validated['status'] = 'pending';
    $validated['progress'] = 0;

    $goal = Goal::create($validated);

    return response()->json([
      'success' => true,
      'data' => $goal,
      'message' => 'Goal created successfully'
    ]);
  }

  #[OA\Put(
    path: "/api/v1/performance/goals/{id}",
    summary: "Update a goal",
    tags: ["Performance"],
    security: [["sanctum" => []]],
    responses: [new OA\Response(response: 200, description: "Goal updated")]
  )]
  public function updateGoal(Request $request, $id)
  {
    $goal = Goal::findOrFail($id);
    $goal->update($request->all());

    return response()->json([
      'success' => true,
      'data' => $goal,
      'message' => 'Goal updated successfully'
    ]);
  }

  // ===================== DASHBOARD SUMMARY =====================

  #[OA\Get(
    path: "/api/v1/performance/summary",
    summary: "Get performance summary for dashboard",
    tags: ["Performance"],
    security: [["sanctum" => []]],
    responses: [new OA\Response(response: 200, description: "Performance summary")]
  )]
  public function summary(Request $request)
  {
    $user = $request->user();
    $employeeId = $user->employee_id ?? ($user->employee->id ?? null);

    $okrs = Okr::where('employee_id', $employeeId)->where('type', 'objective');
    $goals = Goal::where('employee_id', $employeeId);
    $reviews = PerformanceReview::where('employee_id', $employeeId);

    return response()->json([
      'success' => true,
      'data' => [
        'okrs' => [
          'total' => $okrs->count(),
          'on_track' => (clone $okrs)->where('status', 'on_track')->count(),
          'at_risk' => (clone $okrs)->where('status', 'at_risk')->count(),
          'completed' => (clone $okrs)->where('status', 'completed')->count(),
        ],
        'goals' => [
          'total' => $goals->count(),
          'in_progress' => (clone $goals)->where('status', 'in_progress')->count(),
          'completed' => (clone $goals)->where('status', 'completed')->count(),
        ],
        'reviews' => [
          'total' => $reviews->count(),
          'latest_rating' => $reviews->latest()->first()?->overall_rating,
        ],
      ]
    ]);
  }
}
