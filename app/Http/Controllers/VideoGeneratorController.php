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
    $request->validate([
      'reciter' => 'required|string',
      'surah' => 'required|integer|min:1|max:114',
      'ayah_from' => 'required|integer|min:1',
      'ayah_to' => 'required|integer|min:1|gte:ayah_from',
      'duration' => 'nullable|integer|min:5|max:60',
    ]);

    $sessionId = Str::random(10);
    $reciter = $request->reciter;
    $surah = $request->surah;
    $from = $request->ayah_from;
    $to = $request->ayah_to;

    // 1. Fetch Ayah Texts
    $ayahs = $this->quranService->getAyahTexts($surah, $from, $to);
    if (empty($ayahs)) {
      return back()->with('error', 'Could not fetch ayah texts.');
    }

    // 2. Download and Merge Audio
    $audioPaths = [];
    $overlayData = [];
    $currentTime = 0;

    foreach ($ayahs as $ayah) {
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
      return back()->with('error', 'Could not download audio files.');
    }

    $mergedAudio = $this->videoService->mergeAudio($audioPaths, $sessionId);

    // 3. Create Final Video
    $finalVideoPath = $this->videoService->createFinalVideo($mergedAudio, $overlayData, $sessionId);

    $videoUrl = Storage::url('videos/output/' . basename($finalVideoPath));

    return back()->with('success', 'Video generated successfully!')->with('video_url', $videoUrl);
  }
}
