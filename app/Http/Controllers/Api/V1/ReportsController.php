<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\Attendance;
use App\Models\PayrollRecord;
use App\Models\JobApplication;
use App\Models\JobPosting;
use App\Models\LeaveRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use OpenApi\Attributes as OA;

class ReportsController extends Controller
{
  #[OA\Get(
    path: "/api/v1/reports/employees",
    summary: "Get employee demographics and department distribution",
    tags: ["Reports"],
    security: [["sanctum" => []]],
    responses: [new OA\Response(response: 200, description: "Employee reports data")]
  )]
  public function employeeReports()
  {
    $totalEmployees = Employee::count();

    $departmentDistribution = Employee::select('department', DB::raw('count(*) as count'))
      ->groupBy('department')
      ->get();

    $employmentStatusDocs = Employee::select('employment_status', DB::raw('count(*) as count'))
      ->groupBy('employment_status')
      ->get();

    $employmentTypeDocs = Employee::select('employment_type', DB::raw('count(*) as count'))
      ->groupBy('employment_type')
      ->get();

    return response()->json([
      'success' => true,
      'data' => [
        'total_employees' => $totalEmployees,
        'department_distribution' => $departmentDistribution,
        'employment_status' => $employmentStatusDocs,
        'employment_type' => $employmentTypeDocs,
      ]
    ]);
  }

  #[OA\Get(
    path: "/api/v1/reports/attendance",
    summary: "Get attendance trends",
    tags: ["Reports"],
    security: [["sanctum" => []]],
    responses: [new OA\Response(response: 200, description: "Attendance reports data")]
  )]
  public function attendanceReports(Request $request)
  {
    $days = $request->get('days', 30);
    $startDate = Carbon::now()->subDays($days);

    $attendanceTrends = Attendance::where('date', '>=', $startDate)
      ->select('date', DB::raw('count(*) as count'))
      ->groupBy('date')
      ->orderBy('date')
      ->get();

    $statusSummary = Attendance::select('status', DB::raw('count(*) as count'))
      ->where('date', '>=', $startDate)
      ->groupBy('status')
      ->get();

    return response()->json([
      'success' => true,
      'data' => [
        'trends' => $attendanceTrends,
        'status_summary' => $statusSummary,
      ]
    ]);
  }

  #[OA\Get(
    path: "/api/v1/reports/payroll",
    summary: "Get payroll expense analytics",
    tags: ["Reports"],
    security: [["sanctum" => []]],
    responses: [new OA\Response(response: 200, description: "Payroll reports data")]
  )]
  public function payrollReports()
  {
    $monthlyExpenses = PayrollRecord::select(
      DB::raw("DATE_FORMAT(created_at, '%Y-%m') as month"),
      DB::raw('SUM(net_pay) as total_net_pay'),
      DB::raw('SUM(gross_pay) as total_gross_pay')
    )
      ->groupBy('month')
      ->orderBy('month', 'desc')
      ->limit(12)
      ->get();

    $departmentExpenses = PayrollRecord::join('employees', 'payroll_records.employee_id', '=', 'employees.id')
      ->select('employees.department', DB::raw('SUM(payroll_records.net_pay) as total_expense'))
      ->groupBy('employees.department')
      ->get();

    return response()->json([
      'success' => true,
      'data' => [
        'monthly_expenses' => $monthlyExpenses,
        'department_expenses' => $departmentExpenses,
      ]
    ]);
  }

  #[OA\Get(
    path: "/api/v1/reports/recruitment",
    summary: "Get recruitment and ATS metrics",
    tags: ["Reports"],
    security: [["sanctum" => []]],
    responses: [new OA\Response(response: 200, description: "Recruitment reports data")]
  )]
  public function recruitmentReports()
  {
    $applicationsByStage = JobApplication::select('stage', DB::raw('count(*) as count'))
      ->groupBy('stage')
      ->get();

    $sourceEffectiveness = JobApplication::join('candidates', 'job_applications.candidate_id', '=', 'candidates.id')
      ->select('candidates.source', DB::raw('count(*) as count'))
      ->groupBy('candidates.source')
      ->get();

    $jobPostingStatus = JobPosting::select('status', DB::raw('count(*) as count'))
      ->groupBy('status')
      ->get();

    return response()->json([
      'success' => true,
      'data' => [
        'applications_by_stage' => $applicationsByStage,
        'source_effectiveness' => $sourceEffectiveness,
        'job_posting_status' => $jobPostingStatus,
      ]
    ]);
  }
}
