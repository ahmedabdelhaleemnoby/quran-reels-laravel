<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Exports\StatsExport;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\App;

class DashboardExportController extends Controller
{
  public function exportExcel(Request $request)
  {
    $period = $request->query('period', 'all');
    $locale = $request->header('Accept-Language', 'en');
    App::setLocale($locale);

    // Get stats using existing controller logic (or just instantiate it)
    $dashboardController = new DashboardController();
    $response = $dashboardController->stats($request);
    $data = json_decode($response->getContent(), true);

    if (!$data['success']) {
      return response()->json(['success' => false, 'message' => 'Failed to fetch data'], 500);
    }

    $fileName = 'dashboard_stats_' . $period . '_' . now()->format('Y-m-d_His') . '.xlsx';

    return Excel::download(new StatsExport($data['data']), $fileName);
  }
}
