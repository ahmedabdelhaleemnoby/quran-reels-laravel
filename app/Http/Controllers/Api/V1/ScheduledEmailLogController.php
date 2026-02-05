<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\ScheduledEmailLog;

class ScheduledEmailLogController extends Controller
{
  /**
   * Display a listing of the resource.
   */
  public function index()
  {
    $logs = ScheduledEmailLog::orderBy('executed_at', 'desc')->paginate(15);
    return response()->json($logs);
  }

  /**
   * Store a newly created resource in storage.
   */
  public function store(Request $request)
  {
    //
  }

  /**
   * Display the specified resource.
   */
  public function show(string $id)
  {
    //
  }

  /**
   * Update the specified resource in storage.
   */
  public function update(Request $request, string $id)
  {
    //
  }

  /**
   * Remove the specified resource from storage.
   */
  public function destroy(string $id)
  {
    //
  }
}
