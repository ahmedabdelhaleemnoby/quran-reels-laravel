<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\App;
use Carbon\Carbon;
use OpenApi\Attributes as OA;

class DashboardController extends Controller
{
  #[OA\Get(
    path: "/api/v1/dashboard/stats",
    summary: "Get dashboard statistics",
    tags: ["Dashboard"],
    security: [["sanctum" => []]],
    responses: [
      new OA\Response(
        response: 200,
        description: "Dashboard stats",
        content: new OA\JsonContent(
          properties: [
            new OA\Property(property: "success", type: "boolean", example: true),
            new OA\Property(property: "data", type: "object")
          ]
        )
      ),
      new OA\Response(response: 401, description: "Unauthenticated")
    ]
  )]
  public function stats(Request $request)
  {
    $locale = App::getLocale();
    $isArabic = $locale === 'ar';
    $period = $request->query('period', 'all');

    // Set Carbon locale for human-readable dates
    Carbon::setLocale($locale);

    $query = Employee::query();
    $activityQuery = Employee::query();
    $deptQuery = Employee::query();

    // Apply period filters
    if ($period !== 'all') {
      $date = null;
      switch ($period) {
        case 'today':
          $date = Carbon::today();
          break;
        case 'week':
          $date = Carbon::now()->startOfWeek();
          break;
        case 'month':
          $date = Carbon::now()->startOfMonth();
          break;
      }

      if ($date) {
        $query->where('hire_date', '>=', $date);
        $activityQuery->where('hire_date', '>=', $date);
        $deptQuery->where('hire_date', '>=', $date);
      }
    }

    $totalEmployees = Employee::count(); // Keep total overall
    $periodEmployees = (clone $query)->count();
    $activeEmployees = Employee::where('employment_status', 'active')->count();
    $onLeaveEmployees = Employee::where('employment_status', 'on_leave')->count();

    // Department distribution with translated labels
    $departmentStats = $deptQuery->select('department', DB::raw('count(*) as count'))
      ->whereNotNull('department')
      ->groupBy('department')
      ->get()
      ->map(function ($dept) use ($isArabic) {
        return [
          'department' => $isArabic ? $this->translateDepartment($dept->department) : $dept->department,
          'count' => $dept->count
        ];
      });

    // Recent activities with localized action text
    $actionText = $isArabic ? 'انضم للشركة' : 'joined the company';

    $recentHires = $activityQuery->orderBy('hire_date', 'desc')
      ->limit(5)
      ->get()
      ->map(function ($emp) use ($actionText) {
        return [
          'id' => $emp->id,
          'name' => $emp->full_name,
          'action' => $actionText,
          'time' => $emp->created_at->diffForHumans(),
          'avatar' => strtoupper(substr($emp->first_name, 0, 1))
        ];
      });

    // Localized labels for dashboard cards
    $labels = $isArabic ? [
      'total_employees' => 'إجمالي الموظفين',
      'present_today' => 'الحضور اليوم',
      'on_leave' => 'في إجازة',
      'departments' => 'الأقسام',
      'recent_hires' => 'أحدث التعيينات',
      'department_distribution' => 'توزيع الأقسام',
      'period_employees' => 'تعيينات الفترة',
    ] : [
      'total_employees' => 'Total Employees',
      'present_today' => 'Present Today',
      'on_leave' => 'On Leave',
      'departments' => 'Departments',
      'recent_hires' => 'Recent Hires',
      'department_distribution' => 'Department Distribution',
      'period_employees' => 'Period Hires',
    ];

    return response()->json([
      'success' => true,
      'data' => [
        'total_employees' => $totalEmployees,
        'period_employees' => $periodEmployees,
        'active_employees' => $activeEmployees,
        'on_leave_employees' => $onLeaveEmployees,
        'department_stats' => $departmentStats,
        'recent_activities' => $recentHires,
        'labels' => $labels,
        'locale' => $locale,
        'period' => $period,
      ]
    ]);
  }

  /**
   * Translate department names to Arabic
   */
  private function translateDepartment(string $department): string
  {
    $translations = [
      'Engineering' => 'الهندسة',
      'Sales' => 'المبيعات',
      'Marketing' => 'التسويق',
      'HR' => 'الموارد البشرية',
      'Human Resources' => 'الموارد البشرية',
      'Finance' => 'المالية',
      'IT' => 'تقنية المعلومات',
      'Operations' => 'العمليات',
      'Legal' => 'الشؤون القانونية',
      'Customer Service' => 'خدمة العملاء',
      'Research' => 'البحث والتطوير',
      'Administration' => 'الإدارة',
      'Product' => 'المنتجات',
      'Design' => 'التصميم',
    ];

    return $translations[$department] ?? $department;
  }
}
