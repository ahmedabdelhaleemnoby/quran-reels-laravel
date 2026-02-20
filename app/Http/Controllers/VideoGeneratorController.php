<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\QuranService;
use App\Services\QuranVideoService;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

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
    try {
      set_time_limit(600); // Allow up to 10 minutes
      ini_set('memory_limit', '1024M');

    // Initialize progress
    session(['generation_progress' => 0, 'generation_status' => 'Starting...']);
    session()->save();

    $validator = \Illuminate\Support\Facades\Validator::make($request->all(), [
      'reciter' => ['required', 'string'],
      'surah' => ['required', 'integer', 'min:1', 'max:114'],
      'ayah_from' => ['required', 'integer', 'min:1'],
      'ayah_to' => ['required', 'integer', 'min:1', 'gte:ayah_from'],
      'duration' => ['nullable', 'integer', 'min:5', 'max:60'],
      'font_size' => ['nullable', 'integer', 'min:20', 'max:150'],
      'text_color' => ['nullable', 'string', 'regex:/^#([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3})$/'],
      'text_position' => ['nullable', 'string', 'in:top,middle,bottom'],
      'text_bg_style' => ['nullable', 'string', 'in:none,shadow,letterbox'],
      'text_bg_color' => ['nullable', 'string', 'regex:/^#([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3})$/'],
      'text_bg_opacity' => ['nullable', 'numeric', 'min:0', 'max:1'],
      'bold' => ['nullable', 'boolean'],
      'line_height' => ['nullable', 'numeric', 'min:1', 'max:3'],
      'background' => ['nullable', 'array', 'max:10'], // Max 10 images
      'background.*' => ['nullable', 'file', 'mimes:jpeg,png,jpg,mp4,mov', 'max:51200'], // 50MB per file
      'no_text_overlay' => ['nullable', 'boolean'],
    ]);

    if ($validator->fails()) {
      if ($request->expectsJson()) {
        return response()->json(['success' => false, 'message' => $validator->errors()->first()], 422);
      }
      return back()->withErrors($validator)->withInput();
    }

    $sessionId = Str::random(10);

    // Handle background uploads (single or multiple)
    $backgroundPaths = [];
    if ($request->hasFile('background')) {
      $files = $request->file('background');
      foreach ($files as $index => $file) {
        $fileName = "bg_upload_{$sessionId}_{$index}." . $file->getClientOriginalExtension();
        $storedPath = $file->storeAs('backgrounds', $fileName, 'public');
        $backgroundPaths[] = Storage::disk('public')->path($storedPath);
      }
    }
    $reciter = $request->reciter;
    $surah = $request->surah;
    $from = $request->ayah_from;
    $to = $request->ayah_to;
    $noTextOverlay = $request->has('no_text_overlay') && $request->no_text_overlay == '1';

    $options = [
      'font_size' => $request->font_size ?? 65,
      'text_color' => $request->text_color ?? '#FFFFFF',
      'bold' => $request->has('bold') ? (bool) $request->bold : true,
      'line_height' => $request->line_height ?? 1.6,
      'text_position' => $request->text_position ?? 'middle',
      'text_bg_style' => $request->text_bg_style ?? 'shadow',
      'text_bg_color' => $request->text_bg_color ?? '#000000',
      'text_bg_opacity' => $request->text_bg_opacity ?? 0.5,
    ];

    // 1. Fetch Ayah Texts
    $ayahs = $this->quranService->getAyahTexts($surah, $from, $to);
    if (empty($ayahs)) {
      if ($request->expectsJson()) {
        return response()->json(['success' => false, 'message' => 'Could not fetch ayah texts.'], 400);
      }
      return back()->with('error', 'Could not fetch ayah texts.');
    }

    // Get surah name for filename
    $surahData = $this->quranService->getSurahInfo($surah);
    $surahName = $surahData['name'] ?? "سورة_{$surah}";

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

      // Generate overlay for this ayah (skip if no_text_overlay is checked)
      if (!$noTextOverlay) {
        $overlayPath = $this->videoService->generateTextOverlay($ayah['text'], $ayah['numberInSurah'], $sessionId, $options);

        $overlayData[] = [
          'path' => $overlayPath,
          'start' => $currentTime,
          'end' => $currentTime + $duration,
          'text' => $ayah['text']
        ];
      }

      $audioPaths[] = $path;

      Log::info("Ayah {$ayah['numberInSurah']}: Start={$currentTime}, Duration={$duration}, End=" . ($currentTime + $duration));

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
    $finalVideoPath = $this->videoService->createFinalVideo($mergedAudio, $overlayData, $sessionId, $backgroundPaths, $surahName, $from, $to);

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
    } catch (\Exception $e) {
      \Log::error('Video generation error: ' . $e->getMessage());
      \Log::error('Stack trace: ' . $e->getTraceAsString());

      session(['generation_progress' => 0, 'generation_status' => 'Error: ' . $e->getMessage()]);
      session()->save();

      if ($request->expectsJson()) {
        return response()->json([
          'success' => false,
          'message' => 'Error: ' . $e->getMessage(),
          'details' => $e->getTraceAsString()
        ], 500);
      }
      return back()->with('error', 'Error: ' . $e->getMessage());
    }
  }

  /**
   * Generate slide previews for all selected ayahs.
   */
  public function preview(Request $request)
  {
    $validator = \Illuminate\Support\Facades\Validator::make($request->all(), [
      'reciter' => ['required', 'string'],
      'surah' => ['required', 'integer', 'min:1', 'max:114'],
      'ayah_from' => ['required', 'integer', 'min:1'],
      'ayah_to' => ['required', 'integer', 'min:1', 'gte:ayah_from'],
      'font_size' => ['nullable', 'integer', 'min:20', 'max:150'],
      'text_color' => ['nullable', 'string', 'regex:/^#([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3})$/'],
      'bold' => ['nullable', 'boolean'],
      'line_height' => ['nullable', 'numeric', 'min:1', 'max:3'],
      'text_position' => ['nullable', 'string', 'in:top,middle,bottom'],
      'text_bg_style' => ['nullable', 'string', 'in:none,shadow,letterbox'],
      'text_bg_color' => ['nullable', 'string', 'regex:/^#([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3})$/'],
      'text_bg_opacity' => ['nullable', 'numeric', 'min:0', 'max:1'],
      'background' => ['nullable', 'array', 'max:10'],
      'background.*' => ['nullable', 'file', 'mimes:jpeg,png,jpg,mp4,mov', 'max:51200'],
    ]);

    if ($validator->fails()) {
      return response()->json(['success' => false, 'message' => $validator->errors()->first()], 422);
    }

    $sessionId = Str::random(10);
    $backgroundPaths = [];
    if ($request->hasFile('background')) {
      $files = $request->file('background');
      foreach ($files as $index => $file) {
        $fileName = "preview_bg_{$sessionId}_{$index}." . $file->getClientOriginalExtension();
        $storedPath = $file->storeAs('backgrounds', $fileName, 'public');
        $backgroundPaths[] = Storage::disk('public')->path($storedPath);
      }
    }

    $ayahs = $this->quranService->getAyahTexts($request->surah, $request->ayah_from, $request->ayah_to);
    if (empty($ayahs)) {
      return response()->json(['success' => false, 'message' => 'Could not fetch ayah texts.'], 400);
    }

    $options = [
      'font_size' => $request->font_size ?? 65,
      'text_color' => $request->text_color ?? '#FFFFFF',
      'bold' => $request->has('bold') ? (bool) $request->bold : true,
      'line_height' => $request->line_height ?? 1.6,
      'text_position' => $request->text_position ?? 'middle',
      'text_bg_style' => $request->text_bg_style ?? 'shadow',
      'text_bg_color' => $request->text_bg_color ?? '#000000',
      'text_bg_opacity' => $request->text_bg_opacity ?? 0.5,
    ];

    $previews = [];
    foreach ($ayahs as $index => $ayah) {
      // Pick background (cycle through if multiple)
      $bg = count($backgroundPaths) > 0 ? $backgroundPaths[$index % count($backgroundPaths)] : null;

      $previewPath = $this->videoService->generateSlidePreview($ayah['text'], $index, $sessionId, $bg, $options);
      if ($previewPath) {
        $previews[] = asset('storage/images/' . basename($previewPath));
      }
    }

    // Store state in session for final generation
    session([
      'preview_session_id' => $sessionId,
      'preview_ayahs' => $ayahs,
      'preview_background_paths' => $backgroundPaths,
      'preview_options' => $options,
      'preview_reciter' => $request->reciter,
      'preview_surah' => $request->surah,
      'preview_from' => $request->ayah_from,
      'preview_to' => $request->ayah_to,
      'preview_no_text' => $request->has('no_text_overlay') && $request->no_text_overlay == '1'
    ]);
    session()->save();

    return response()->json([
      'success' => true,
      'previews' => $previews,
      'count' => count($previews)
    ]);
  }

  /**
   * Final video generation using settings stored in preview session.
   */
  public function generateFromPreview(Request $request)
  {
    set_time_limit(600);
    ini_set('memory_limit', '1024M');

    $sessionId = session('preview_session_id');
    $ayahs = session('preview_ayahs');
    $backgroundPaths = session('preview_background_paths');
    $options = session('preview_options');
    $reciter = session('preview_reciter');
    $surah = session('preview_surah');
    $from = session('preview_from');
    $to = session('preview_to');
    $noTextOverlay = session('preview_no_text');

    if (!$sessionId || !$ayahs) {
      return response()->json(['success' => false, 'message' => 'Preview session expired or invalid.'], 400);
    }

    session(['generation_progress' => 0, 'generation_status' => 'Starting from preview...']);
    session()->save();

    $surahData = $this->quranService->getSurahInfo($surah);
    $surahName = $surahData['name'] ?? "سورة_{$surah}";

    $audioPaths = [];
    $overlayData = [];
    $currentTime = 0;
    $progressStep = 70 / count($ayahs);

    foreach ($ayahs as $i => $ayah) {
      session(['generation_status' => "Processing Ayah " . ($i + 1) . " of " . count($ayahs)]);
      session(['generation_progress' => 5 + ($i * $progressStep)]);
      session()->save();

      $path = $this->quranService->downloadAyahAudio($reciter, $ayah['number']);
      if (!$path)
        continue;

      $duration = $this->videoService->getDuration($path);

      if (!$noTextOverlay) {
        $overlayPath = $this->videoService->generateTextOverlay($ayah['text'], $ayah['numberInSurah'], $sessionId, $options);
        $overlayData[] = [
          'path' => $overlayPath,
          'start' => $currentTime,
          'end' => $currentTime + $duration,
          'text' => $ayah['text']
        ];
      }

      $audioPaths[] = $path;
      $currentTime += $duration;
    }

    session(['generation_status' => 'Merging assets...', 'generation_progress' => 75]);
    session()->save();
    $mergedAudio = $this->videoService->mergeAudio($audioPaths, $sessionId);

    session(['generation_status' => 'Encoding final video...', 'generation_progress' => 85]);
    session()->save();
    $finalVideoPath = $this->videoService->createFinalVideo($mergedAudio, $overlayData, $sessionId, $backgroundPaths, $surahName, $from, $to);

    session(['generation_progress' => 100, 'generation_status' => 'Success!']);
    session()->save();

    if ($finalVideoPath && file_exists($finalVideoPath)) {
      $videoUrl = asset('storage/videos/output/' . basename($finalVideoPath));
      return response()->json(['success' => true, 'video_url' => $videoUrl]);
    }

    return response()->json(['success' => false, 'message' => 'Video generation failed.'], 500);
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

  public function cleanup()
  {
    $this->videoService->cleanupFiles();
    return response()->json(['success' => true, 'message' => 'Project cleaned successfully.']);
  }
}
