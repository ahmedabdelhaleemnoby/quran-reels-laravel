<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\QuranService;
use App\Services\QuranVideoService;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;

class VideoGeneratorController extends Controller
{
  protected $quranService;
  protected $videoService;

  public function __construct(QuranService $quranService, QuranVideoService $videoService)
  {
    $this->quranService = $quranService;
    $this->videoService = $videoService;
  }

  /**
   * Show the generator form.
   */
  public function index()
  {
    $reciters = $this->quranService->getReciters();
    $surahs = $this->quranService->getSurahs();

    return view('welcome', compact('reciters', 'surahs'));
  }

  /**
   * Handle video generation request.
   */
  public function generate(Request $request)
  {
    set_time_limit(300); // Allow up to 5 minutes
    ini_set('memory_limit', '512M');

    // Initialize progress
    session(['generation_progress' => 0, 'generation_status' => 'Starting...']);
    session()->save();

    $validator = \Illuminate\Support\Facades\Validator::make($request->all(), [
      'reciter' => 'required|string',
      'surah' => 'required|integer|min:1|max:114',
      'ayah_from' => 'required|integer|min:1',
      'ayah_to' => 'required|integer|min:1|gte:ayah_from',
      'duration' => 'nullable|integer|min:5|max:60',
      'background' => 'nullable|file|mimes:jpeg,png,jpg,mp4,mov|max:51200', // 50MB
    ]);

    if ($validator->fails()) {
      if ($request->expectsJson()) {
        return response()->json(['success' => false, 'message' => $validator->errors()->first()], 422);
      }
      return back()->withErrors($validator)->withInput();
    }

    $sessionId = Str::random(10);

    // Handle background upload
    $backgroundPath = null;
    if ($request->hasFile('background')) {
      $file = $request->file('background');
      $fileName = "bg_upload_{$sessionId}." . $file->getClientOriginalExtension();
      $backgroundPath = $file->storeAs('backgrounds', $fileName, 'public');
      $backgroundPath = Storage::disk('public')->path($backgroundPath);
    }
    $reciter = $request->reciter;
    $surah = $request->surah;
    $from = $request->ayah_from;
    $to = $request->ayah_to;

    // 1. Fetch Ayah Texts
    $ayahs = $this->quranService->getAyahTexts($surah, $from, $to);
    if (empty($ayahs)) {
      if ($request->expectsJson()) {
        return response()->json(['success' => false, 'message' => 'Could not fetch ayah texts.'], 400);
      }
      return back()->with('error', 'Could not fetch ayah texts.');
    }

    // 2. Download and Merge Audio
    $audioPaths = [];
    $overlayData = [];
    $currentTime = 0;

    if (count($ayahs) > 0) {
      $progressStep = 70 / count($ayahs); // Main loop is 70% of progress
    } else {
      $progressStep = 0;
    }

    foreach ($ayahs as $i => $ayah) {
      session(['generation_status' => "Processing Ayah " . ($i + 1) . " of " . count($ayahs)]);
      session(['generation_progress' => 5 + ($i * $progressStep)]);
      session()->save();

      $path = $this->quranService->downloadAyahAudio($reciter, $ayah['number']);
      if (!$path)
        continue;

      $duration = $this->videoService->getDuration($path);

      // Generate overlay for this ayah
      $overlayPath = $this->videoService->generateTextOverlay($ayah['text'], $ayah['numberInSurah'], $sessionId);

      $overlayData[] = [
        'path' => $overlayPath,
        'start' => $currentTime,
        'end' => $currentTime + $duration,
        'text' => $ayah['text']
      ];

      $audioPaths[] = $path;
      $currentTime += $duration;
    }

    if (empty($audioPaths)) {
      session(['generation_progress' => 0, 'generation_status' => 'Error: No audio found']);
      session()->save();
      if ($request->expectsJson()) {
        return response()->json(['success' => false, 'message' => 'Could not download audio files.'], 400);
      }
      return back()->with('error', 'Could not download audio files.');
    }

    session(['generation_status' => 'Merging assets...', 'generation_progress' => 75]);
    session()->save();
    $mergedAudio = $this->videoService->mergeAudio($audioPaths, $sessionId);

    // 3. Create Final Video
    session(['generation_status' => 'Encoding final video (this may take a minute)...', 'generation_progress' => 85]);
    session()->save();
    $finalVideoPath = $this->videoService->createFinalVideo($mergedAudio, $overlayData, $sessionId, $backgroundPath);

    session(['generation_progress' => 100, 'generation_status' => 'Success!']);
    session()->save();

    if (!$finalVideoPath || !file_exists($finalVideoPath)) {
      if ($request->expectsJson()) {
        return response()->json(['success' => false, 'message' => 'Video generation failed. Please check logs for details.'], 500);
      }
      return back()->with('error', 'Video generation failed. Please check logs for details.');
    }

    // Generate proper URL using asset() helper
    $videoUrl = asset('storage/videos/output/' . basename($finalVideoPath));

    if ($request->expectsJson()) {
      return response()->json(['success' => true, 'video_url' => $videoUrl]);
    }
    return back()->with('success', 'Video generated successfully!')->with('video_url', $videoUrl);
  }

  /**
   * Get generation progress via AJAX.
   */
  public function progress()
  {
    return response()->json([
      'progress' => session('generation_progress', 0),
      'status' => session('generation_status', 'Waiting...')
    ]);
  }
}
