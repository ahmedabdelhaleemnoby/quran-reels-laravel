<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Spatie\Activitylog\Models\Activity;

class AuditLogController extends Controller
{
  /**
   * Display a listing of the activity logs.
   */
  public function index(Request $request)
  {
    $query = Activity::with('causer')
      ->orderBy('created_at', 'desc');

    // Filter by subject type
    if ($request->has('subject_type')) {
      $query->where('subject_type', 'like', '%' . $request->subject_type . '%');
    }

    // Filter by event
    if ($request->has('event')) {
      $query->where('event', $request->event);
    }

    // Filter by user (causer)
    if ($request->has('user_id')) {
      $query->where('causer_id', $request->user_id);
    }

    $logs = $query->paginate($request->get('limit', 20));

    return response()->json([
      'success' => true,
      'data' => $logs
    ]);
  }

  /**
   * Display the specified activity log.
   */
  public function show($id)
  {
    $log = Activity::with('causer', 'subject')->findOrFail($id);

    return response()->json([
      'success' => true,
      'data' => $log
    ]);
  }
}
