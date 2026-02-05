<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\OnboardingTemplate;
use App\Models\OnboardingChecklist;
use App\Models\OnboardingTask;
use Illuminate\Http\Request;
use Carbon\Carbon;
use OpenApi\Attributes as OA;

class OnboardingController extends Controller
{
  // ===================== TEMPLATES =====================

  #[OA\Get(
    path: "/api/v1/onboarding/templates",
    summary: "Get onboarding templates",
    tags: ["Onboarding"],
    security: [["sanctum" => []]],
    responses: [new OA\Response(response: 200, description: "Templates list")]
  )]
  public function templates()
  {
    $templates = OnboardingTemplate::where('active', true)->get();

    return response()->json([
      'success' => true,
      'data' => $templates
    ]);
  }

  #[OA\Post(
    path: "/api/v1/onboarding/templates",
    summary: "Create an onboarding template",
    tags: ["Onboarding"],
    security: [["sanctum" => []]],
    responses: [new OA\Response(response: 200, description: "Template created")]
  )]
  public function createTemplate(Request $request)
  {
    $validated = $request->validate([
      'name' => 'required|string|max:255',
      'description' => 'nullable|string',
      'department' => 'nullable|string',
      'position' => 'nullable|string',
      'duration_days' => 'nullable|integer|min:1',
    ]);

    $template = OnboardingTemplate::create($validated);

    return response()->json([
      'success' => true,
      'data' => $template,
      'message' => 'Template created successfully'
    ]);
  }

  // ===================== CHECKLISTS =====================

  #[OA\Get(
    path: "/api/v1/onboarding/checklists",
    summary: "Get onboarding checklists",
    tags: ["Onboarding"],
    security: [["sanctum" => []]],
    responses: [new OA\Response(response: 200, description: "Checklists list")]
  )]
  public function checklists(Request $request)
  {
    $query = OnboardingChecklist::with(['employee', 'template', 'tasks']);

    if ($request->has('employee_id')) {
      $query->where('employee_id', $request->employee_id);
    }

    if ($request->has('status')) {
      $query->where('status', $request->status);
    }

    $checklists = $query->orderBy('start_date', 'desc')->paginate(20);

    return response()->json([
      'success' => true,
      'data' => $checklists->items(),
      'meta' => [
        'total' => $checklists->total(),
        'per_page' => $checklists->perPage(),
        'current_page' => $checklists->currentPage()
      ]
    ]);
  }

  #[OA\Post(
    path: "/api/v1/onboarding/checklists",
    summary: "Create an onboarding checklist for an employee",
    tags: ["Onboarding"],
    security: [["sanctum" => []]],
    responses: [new OA\Response(response: 200, description: "Checklist created")]
  )]
  public function createChecklist(Request $request)
  {
    $validated = $request->validate([
      'employee_id' => 'required|exists:employees,id',
      'template_id' => 'nullable|exists:onboarding_templates,id',
      'title' => 'required|string|max:255',
      'start_date' => 'required|date',
      'target_completion_date' => 'required|date|after:start_date',
      'assigned_to' => 'nullable|exists:users,id',
    ]);

    $validated['status'] = 'not_started';
    $validated['progress'] = 0;

    $checklist = OnboardingChecklist::create($validated);

    // Add default tasks if a template is used
    if ($validated['template_id']) {
      $this->addDefaultTasks($checklist);
    }

    return response()->json([
      'success' => true,
      'data' => $checklist->load('tasks'),
      'message' => 'Onboarding checklist created successfully'
    ]);
  }

  private function addDefaultTasks(OnboardingChecklist $checklist)
  {
    $defaultTasks = [
      ['title' => 'Complete HR paperwork', 'category' => 'documentation', 'day_due' => 1],
      ['title' => 'ID badge and access card setup', 'category' => 'it_setup', 'day_due' => 1],
      ['title' => 'Workstation and equipment setup', 'category' => 'it_setup', 'day_due' => 1],
      ['title' => 'Email and system accounts setup', 'category' => 'it_setup', 'day_due' => 2],
      ['title' => 'Company policies review', 'category' => 'compliance', 'day_due' => 3],
      ['title' => 'Team introduction meeting', 'category' => 'orientation', 'day_due' => 1],
      ['title' => 'Department orientation', 'category' => 'orientation', 'day_due' => 5],
      ['title' => 'Product/Service training', 'category' => 'training', 'day_due' => 7],
      ['title' => 'Safety and security training', 'category' => 'compliance', 'day_due' => 5],
      ['title' => 'Benefits enrollment', 'category' => 'documentation', 'day_due' => 14],
      ['title' => 'First week check-in', 'category' => 'orientation', 'day_due' => 7],
      ['title' => '30-day performance review', 'category' => 'other', 'day_due' => 30],
    ];

    foreach ($defaultTasks as $index => $task) {
      OnboardingTask::create([
        'checklist_id' => $checklist->id,
        'title' => $task['title'],
        'category' => $task['category'],
        'day_due' => $task['day_due'],
        'order' => $index + 1,
        'is_required' => true,
        'status' => 'pending',
      ]);
    }
  }

  // ===================== TASKS =====================

  #[OA\Get(
    path: "/api/v1/onboarding/tasks",
    summary: "Get tasks for a checklist",
    tags: ["Onboarding"],
    security: [["sanctum" => []]],
    responses: [new OA\Response(response: 200, description: "Tasks list")]
  )]
  public function tasks(Request $request)
  {
    $query = OnboardingTask::with('checklist.employee');

    if ($request->has('checklist_id')) {
      $query->where('checklist_id', $request->checklist_id);
    }

    $tasks = $query->orderBy('day_due')->orderBy('order')->get();

    return response()->json([
      'success' => true,
      'data' => $tasks
    ]);
  }

  #[OA\Post(
    path: "/api/v1/onboarding/tasks",
    summary: "Add a task to a checklist",
    tags: ["Onboarding"],
    security: [["sanctum" => []]],
    responses: [new OA\Response(response: 200, description: "Task added")]
  )]
  public function createTask(Request $request)
  {
    $validated = $request->validate([
      'checklist_id' => 'required|exists:onboarding_checklists,id',
      'title' => 'required|string|max:255',
      'description' => 'nullable|string',
      'category' => 'required|in:documentation,training,it_setup,orientation,compliance,other',
      'day_due' => 'nullable|integer|min:1',
      'is_required' => 'nullable|boolean',
      'assigned_to' => 'nullable|exists:users,id',
    ]);

    $validated['status'] = 'pending';
    $validated['order'] = OnboardingTask::where('checklist_id', $validated['checklist_id'])->max('order') + 1;

    $task = OnboardingTask::create($validated);

    return response()->json([
      'success' => true,
      'data' => $task,
      'message' => 'Task added successfully'
    ]);
  }

  #[OA\Put(
    path: "/api/v1/onboarding/tasks/{id}",
    summary: "Update a task (mark complete, etc.)",
    tags: ["Onboarding"],
    security: [["sanctum" => []]],
    responses: [new OA\Response(response: 200, description: "Task updated")]
  )]
  public function updateTask(Request $request, $id)
  {
    $task = OnboardingTask::findOrFail($id);
    $data = $request->all();

    // Mark completion timestamp
    if (isset($data['status']) && $data['status'] === 'completed' && !$task->completed_at) {
      $data['completed_at'] = Carbon::now();
      $data['completed_by'] = $request->user()->id;
    }

    $task->update($data);

    // Update checklist progress
    $task->checklist->updateProgress();

    return response()->json([
      'success' => true,
      'data' => $task->load('checklist'),
      'message' => 'Task updated successfully'
    ]);
  }

  // ===================== SUMMARY =====================

  #[OA\Get(
    path: "/api/v1/onboarding/summary",
    summary: "Get onboarding dashboard summary",
    tags: ["Onboarding"],
    security: [["sanctum" => []]],
    responses: [new OA\Response(response: 200, description: "Onboarding summary")]
  )]
  public function summary()
  {
    $activeChecklists = OnboardingChecklist::whereIn('status', ['not_started', 'in_progress'])->count();
    $completedThisMonth = OnboardingChecklist::where('status', 'completed')
      ->whereMonth('actual_completion_date', Carbon::now()->month)
      ->count();
    $overdueChecklists = OnboardingChecklist::where('status', 'overdue')->count();
    $templates = OnboardingTemplate::where('active', true)->count();

    // Status breakdown
    $statusBreakdown = OnboardingChecklist::selectRaw('status, COUNT(*) as count')
      ->groupBy('status')
      ->pluck('count', 'status');

    return response()->json([
      'success' => true,
      'data' => [
        'active_checklists' => $activeChecklists,
        'completed_this_month' => $completedThisMonth,
        'overdue_checklists' => $overdueChecklists,
        'templates_count' => $templates,
        'status_breakdown' => $statusBreakdown,
      ]
    ]);
  }
}
