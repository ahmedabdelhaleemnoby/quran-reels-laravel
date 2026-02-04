<?php

namespace App\Services;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Process;

class QuranVideoService
{
  protected $ffmpeg;
  protected $ffprobe;
  protected $magick;
  protected $width = 1080;
  protected $height = 1920;

  public function __construct()
  {
    $this->ffmpeg = env('FFMPEG_PATH', 'ffmpeg');
    $this->ffprobe = env('FFPROBE_PATH', 'ffprobe');
    $this->magick = env('MAGICK_PATH', 'magick');
  }

  /**
   * Merge audio files and return the merged file path.
   */
  public function mergeAudio(array $audioPaths, $sessionId)
  {
    $outputPath = Storage::path("audio/merged_{$sessionId}.mp3");
    $listFile = Storage::path("audio/list_{$sessionId}.txt");

    $content = "";
    foreach ($audioPaths as $path) {
      $content .= "file '" . str_replace("'", "'\\''", $path) . "'\n";
    }
    File_put_contents($listFile, $content);

    $command = "{$this->ffmpeg} -y -f concat -safe 0 -i {$listFile} -c copy {$outputPath}";
    exec($command);

    return $outputPath;
  }

  /**
   * Get duration of an audio/video file using ffprobe.
   */
  public function getDuration($path)
  {
    $command = "{$this->ffprobe} -v error -show_entries format=duration -of default=noprint_wrappers=1:nokey=1 \"{$path}\"";
    return (float) shell_exec($command);
  }

  /**
   * Generate Arabic text overlay image using ImageMagick.
   */
  public function generateTextOverlay($text, $index, $sessionId)
  {
    $outputPath = Storage::path("images/overlay_{$sessionId}_{$index}.png");

    // Using a clean, elegant styling for the Arabic text
    // Note: Assumes Amiri or similar font is installed. We'll fallback to sans if not.
    $command = "{$this->magick} -size {$this->width}x{$this->height} xc:none " .
      "-gravity center -fill white -font \"Amiri-Regular\" -pointsize 60 " .
      "-draw \"text 0,0 '{$text}'\" " .
      "-shadow 100x3+5+5 \"{$outputPath}\"";

    // Fallback for RTL and complex rendering: Using pango or simple text
    // Better: magick -background none -fill white -font Amiri -pointsize 70 -size 900x -gravity center caption:"TEXT" ...
    $safeText = escapeshellarg($text);
    $command = "{$this->magick} -background none -fill white -font \"Amiri\" -pointsize 65 " .
      "-size 900x -gravity center caption:{$safeText} " .
      "\( +clone -background black -shadow 80x3+2+2 \) +swap -layers merge +repage " .
      "-size {$this->width}x{$this->height} -gravity center -extent {$this->width}x{$this->height} " .
      "\"{$outputPath}\"";

    exec($command);
    return $outputPath;
  }

  /**
   * Create final video by combining background, audio, and sync'd overlays.
   */
  public function createFinalVideo($audioPath, array $overlayData, $sessionId)
  {
    $outputPath = Storage::path("videos/output/reels_{$sessionId}.mp4");
    $bgPath = Storage::path("images/background_{$sessionId}.png");

    // Create a simple gradient background if no image provided
    if (!file_exists($bgPath)) {
      $cmdBg = "{$this->magick} -size {$this->width}x{$this->height} gradient:'#1a1c2c'-'#4a192c' {$bgPath}";
      exec($cmdBg);
    }

    $duration = $this->getDuration($audioPath);

    // Build FFmpeg command for overlays
    $filter = "nullsrc=s={$this->width}x{$this->height}:d={$duration} [bg]; ";
    $filter .= "movie='{$bgPath}' [base]; ";

    $inputs = "";
    $overlayFilter = "[base]";
    foreach ($overlayData as $i => $data) {
      $inputs .= "-i \"{$data['path']}\" ";
      $start = $data['start'];
      $end = $data['end'];
      $overlayFilter .= "[{$i}] overlay=0:0:enable='between(t,{$start},{$end})' [v{$i}]; [v{$i}]";
    }

    // Remove trailing [vX]
    $overlayFilter = rtrim($overlayFilter, "; [v" . (count($overlayData) - 1) . "]");

    // For simplicity in this first iteration, let's use a simpler approach if many overlays
    // This is a complex filter string. For 3-5 ayahs it's fine.

    $finalFilter = "[base]";
    foreach ($overlayData as $i => $data) {
      $finalFilter .= " [ovl{$i}] overlay=0:0:enable='between(t,{$data['start']},{$data['end']})' [base];";
    }
    $finalFilter = rtrim($finalFilter, " [base];");

    // Final Command
    $overlayInputs = "";
    foreach ($overlayData as $data) {
      $overlayInputs .= " -i \"{$data['path']}\"";
    }

    // Simpler filter logic
    $filterComplex = "";
    $lastLabel = "[0:v]";
    for ($i = 0; $i < count($overlayData); $i++) {
      $inputIdx = $i + 1;
      $start = $overlayData[$i]['start'];
      $end = $overlayData[$i]['end'];
      $nextLabel = "[v{$i}]";
      $filterComplex .= "{$lastLabel}[{$inputIdx}:v] overlay=0:0:enable='between(t,{$start},{$end})'{$nextLabel}; ";
      $lastLabel = $nextLabel;
    }
    $filterComplex = rtrim($filterComplex, "; ");

    $command = "{$this->ffmpeg} -y -loop 1 -i \"{$bgPath}\" {$overlayInputs} -i \"{$audioPath}\" " .
      "-filter_complex \"{$filterComplex}\" " .
      "-map \"{$lastLabel}\" -map " . (count($overlayData) + 1) . ":a -c:v libx264 -t {$duration} -pix_fmt yuv420p {$outputPath}";

    exec($command);

    return $outputPath;
  }
}
