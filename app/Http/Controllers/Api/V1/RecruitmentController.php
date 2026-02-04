<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\JobPosting;
use App\Models\Candidate;
use App\Models\JobApplication;
use Illuminate\Http\Request;
use Carbon\Carbon;
use OpenApi\Attributes as OA;

class RecruitmentController extends Controller
{
  // ===================== JOB POSTINGS =====================

  #[OA\Get(
    path: "/api/v1/recruitment/jobs",
    summary: "Get all job postings",
    tags: ["Recruitment"],
    security: [["sanctum" => []]],
    responses: [new OA\Response(response: 200, description: "Job postings list")]
  )]
  public function jobs(Request $request)
  {
    $query = JobPosting::withCount('applications');

    if ($request->has('status')) {
      $query->where('status', $request->status);
    }

    if ($request->has('department')) {
      $query->where('department', $request->department);
    }

    $jobs = $query->orderBy('created_at', 'desc')->paginate(20);

    return response()->json([
      'success' => true,
      'data' => $jobs->items(),
      'meta' => [
        'total' => $jobs->total(),
        'per_page' => $jobs->perPage(),
        'current_page' => $jobs->currentPage()
      ]
    ]);
  }

  #[OA\Post(
    path: "/api/v1/recruitment/jobs",
    summary: "Create a job posting",
    tags: ["Recruitment"],
    security: [["sanctum" => []]],
    responses: [new OA\Response(response: 200, description: "Job posting created")]
  )]
  public function createJob(Request $request)
  {
    $validated = $request->validate([
      'title' => 'required|string|max:255',
      'description' => 'required|string',
      'requirements' => 'nullable|string',
      'responsibilities' => 'nullable|string',
      'department' => 'nullable|string',
      'location' => 'required|string',
      'employment_type' => 'required|in:full_time,part_time,contract,internship,remote',
      'experience_level' => 'required|in:entry,mid,senior,lead,executive',
      'salary_min' => 'nullable|numeric',
      'salary_max' => 'nullable|numeric',
      'positions_available' => 'nullable|integer|min:1',
      'closing_date' => 'nullable|date',
    ]);

    $validated['created_by'] = $request->user()->id;
    $validated['status'] = 'draft';

    $job = JobPosting::create($validated);

    return response()->json([
      'success' => true,
      'data' => $job,
      'message' => 'Job posting created successfully'
    ]);
  }

  #[OA\Put(
    path: "/api/v1/recruitment/jobs/{id}",
    summary: "Update a job posting",
    tags: ["Recruitment"],
    security: [["sanctum" => []]],
    responses: [new OA\Response(response: 200, description: "Job posting updated")]
  )]
  public function updateJob(Request $request, $id)
  {
    $job = JobPosting::findOrFail($id);

    $data = $request->all();

    // Auto-set posted_date when publishing
    if (isset($data['status']) && $data['status'] === 'published' && !$job->posted_date) {
      $data['posted_date'] = Carbon::now();
    }

    $job->update($data);

    return response()->json([
      'success' => true,
      'data' => $job,
      'message' => 'Job posting updated successfully'
    ]);
  }

  // ===================== CANDIDATES =====================

  #[OA\Get(
    path: "/api/v1/recruitment/candidates",
    summary: "Get all candidates",
    tags: ["Recruitment"],
    security: [["sanctum" => []]],
    responses: [new OA\Response(response: 200, description: "Candidates list")]
  )]
  public function candidates(Request $request)
  {
    $query = Candidate::withCount('applications');

    if ($request->has('source')) {
      $query->where('source', $request->source);
    }

    $candidates = $query->orderBy('created_at', 'desc')->paginate(20);

    return response()->json([
      'success' => true,
      'data' => $candidates->items(),
      'meta' => [
        'total' => $candidates->total(),
        'per_page' => $candidates->perPage(),
        'current_page' => $candidates->currentPage()
      ]
    ]);
  }

  #[OA\Post(
    path: "/api/v1/recruitment/candidates",
    summary: "Create a candidate",
    tags: ["Recruitment"],
    security: [["sanctum" => []]],
    responses: [new OA\Response(response: 200, description: "Candidate created")]
  )]
  public function createCandidate(Request $request)
  {
    $validated = $request->validate([
      'first_name' => 'required|string|max:255',
      'last_name' => 'required|string|max:255',
      'email' => 'required|email|unique:candidates,email',
      'phone' => 'nullable|string',
      'linkedin_url' => 'nullable|url',
      'portfolio_url' => 'nullable|url',
      'current_company' => 'nullable|string',
      'current_position' => 'nullable|string',
      'years_of_experience' => 'nullable|integer',
      'skills' => 'nullable|array',
      'source' => 'nullable|in:website,linkedin,referral,job_board,agency,other',
    ]);

    $candidate = Candidate::create($validated);

    return response()->json([
      'success' => true,
      'data' => $candidate,
      'message' => 'Candidate created successfully'
    ]);
  }

  // ===================== APPLICATIONS =====================

  #[OA\Get(
    path: "/api/v1/recruitment/applications",
    summary: "Get all applications",
    tags: ["Recruitment"],
    security: [["sanctum" => []]],
    responses: [new OA\Response(response: 200, description: "Applications list")]
  )]
  public function applications(Request $request)
  {
    $query = JobApplication::with(['jobPosting', 'candidate']);

    if ($request->has('job_id')) {
      $query->where('job_posting_id', $request->job_id);
    }

    if ($request->has('stage')) {
      $query->where('stage', $request->stage);
    }

    $applications = $query->orderBy('applied_date', 'desc')->paginate(20);

    return response()->json([
      'success' => true,
      'data' => $applications->items(),
      'meta' => [
        'total' => $applications->total(),
        'per_page' => $applications->perPage(),
        'current_page' => $applications->currentPage()
      ]
    ]);
  }

  #[OA\Post(
    path: "/api/v1/recruitment/applications",
    summary: "Create an application",
    tags: ["Recruitment"],
    security: [["sanctum" => []]],
    responses: [new OA\Response(response: 200, description: "Application created")]
  )]
  public function createApplication(Request $request)
  {
    $validated = $request->validate([
      'job_posting_id' => 'required|exists:job_postings,id',
      'candidate_id' => 'required|exists:candidates,id',
      'cover_letter' => 'nullable|string',
      'expected_salary' => 'nullable|numeric',
      'available_from' => 'nullable|date',
    ]);

    $validated['stage'] = 'applied';
    $validated['status'] = 'active';
    $validated['applied_date'] = Carbon::now();
    $validated['last_activity_date'] = Carbon::now();

    $application = JobApplication::create($validated);

    return response()->json([
      'success' => true,
      'data' => $application->load(['jobPosting', 'candidate']),
      'message' => 'Application created successfully'
    ]);
  }

  #[OA\Put(
    path: "/api/v1/recruitment/applications/{id}",
    summary: "Update application (move stage, rate, etc.)",
    tags: ["Recruitment"],
    security: [["sanctum" => []]],
    responses: [new OA\Response(response: 200, description: "Application updated")]
  )]
  public function updateApplication(Request $request, $id)
  {
    $application = JobApplication::findOrFail($id);

    $data = $request->all();
    $data['last_activity_date'] = Carbon::now();

    if (isset($data['rating']) || isset($data['evaluation_notes'])) {
      $data['evaluated_by'] = $request->user()->id;
    }

    $application->update($data);

    return response()->json([
      'success' => true,
      'data' => $application->load(['jobPosting', 'candidate']),
      'message' => 'Application updated successfully'
    ]);
  }

  // ===================== DASHBOARD SUMMARY =====================

  #[OA\Get(
    path: "/api/v1/recruitment/summary",
    summary: "Get recruitment dashboard summary",
    tags: ["Recruitment"],
    security: [["sanctum" => []]],
    responses: [new OA\Response(response: 200, description: "Recruitment summary")]
  )]
  public function summary()
  {
    $openJobs = JobPosting::where('status', 'published')->count();
    $totalCandidates = Candidate::count();
    $activeApplications = JobApplication::where('status', 'active')->count();
    $hiredThisMonth = JobApplication::where('stage', 'hired')
      ->whereMonth('updated_at', Carbon::now()->month)
      ->count();

    // Pipeline breakdown
    $pipeline = JobApplication::where('status', 'active')
      ->selectRaw('stage, COUNT(*) as count')
      ->groupBy('stage')
      ->pluck('count', 'stage');

    return response()->json([
      'success' => true,
      'data' => [
        'open_jobs' => $openJobs,
        'total_candidates' => $totalCandidates,
        'active_applications' => $activeApplications,
        'hired_this_month' => $hiredThisMonth,
        'pipeline' => $pipeline,
      ]
    ]);
  }
}
