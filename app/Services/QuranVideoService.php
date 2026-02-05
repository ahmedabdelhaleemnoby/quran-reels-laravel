<?php

namespace App\Services;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Process;
use GuzzleHttp\Client;

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

    // Ensure directories exist on public disk
    $this->ensureDirectoriesExist();
  }

  protected function ensureDirectoriesExist()
  {
    $directories = ['audio', 'images', 'videos/output', 'backgrounds'];
    foreach ($directories as $dir) {
      if (!Storage::disk('public')->exists($dir)) {
        Storage::disk('public')->makeDirectory($dir);
      }
    }
  }

  /**
   * Find an available Arabic font.
   */
  protected function getFontPath()
  {
    $fonts = [
      storage_path('fonts/Amiri-Regular.ttf'),
      base_path('storage/fonts/Amiri-Regular.ttf'),
      '/Library/Fonts/Amiri-Regular.ttf',
      '/System/Library/Fonts/GeezaPro.ttc',
      '/System/Library/Fonts/Supplemental/Arial Unicode.ttf',
      'C:\Windows\Fonts\arial.ttf',
    ];

    foreach ($fonts as $font) {
      if (file_exists($font)) {
        return $font;
      }
    }

    return null;
  }

  /**
   * Run a shell command and log errors if it fails.
   */
  protected function runCommand($command, $context = "")
  {
    $output = [];
    $returnVar = 0;
    Log::info("Executing command: {$command}");
    exec($command . " 2>&1", $output, $returnVar);

    if ($returnVar !== 0) {
      Log::error("Command failed ($context). Return code: {$returnVar}. Output: " . implode("\n", $output));
      return false;
    }

    return true;
  }

  /**
   * Merge audio files and return the merged file path.
   */
  public function mergeAudio(array $audioPaths, $sessionId)
  {
    $outputPath = Storage::disk('public')->path("audio/merged_{$sessionId}.mp3");
    $listFile = Storage::disk('public')->path("audio/list_{$sessionId}.txt");

    $content = "";
    foreach ($audioPaths as $path) {
      $content .= "file '" . str_replace("'", "'\\''", $path) . "'\n";
    }
    file_put_contents($listFile, $content);

    $command = "{$this->ffmpeg} -y -f concat -safe 0 -i \"{$listFile}\" -c copy \"{$outputPath}\"";
    $this->runCommand($command, "merging audio");

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
  /**
   * Generate Arabic text overlay image using PHP GD.
   * This handles basic RTL and Shaping (Ligatures).
   */
  public function generateTextOverlay($text, $index, $sessionId)
  {
    $outputPath = Storage::disk('public')->path("images/overlay_{$sessionId}_{$index}.png");
    $fontPath = $this->getFontPath();

    if (!$fontPath) {
      Log::error("No suitable Arabic font found for GD rendering.");
      return null;
    }

    // 1. Create a transparent canvas
    $image = imagecreatetruecolor($this->width, $this->height);
    imagesavealpha($image, true);
    $transparent = imagecolorallocatealpha($image, 0, 0, 0, 127);
    imagefill($image, 0, 0, $transparent);

    // 2. Text settings
    $fontSize = 65; // Increased from 45
    $white = imagecolorallocate($image, 255, 255, 255);
    $black = imagecolorallocatealpha($image, 0, 0, 0, 80); // Shadow

    // 3. Prepare RTL and Word Wrap
    $lines = $this->wrapArabicText($text, 25, $fontSize, $fontPath);

    // 4. Centering calculation
    $lineHeight = $fontSize * 1.6;
    $totalHeight = count($lines) * $lineHeight;
    $startY = ($this->height - $totalHeight) / 2 + $fontSize;

    foreach ($lines as $i => $line) {
      $bbox = imagettfbbox($fontSize, 0, $fontPath, $line);
      $textWidth = $bbox[2] - $bbox[0];
      $x = ($this->width - $textWidth) / 2;
      $y = $startY + ($i * $lineHeight);

      // Draw Shadow
      imagettftext($image, $fontSize, 0, $x + 4, $y + 4, $black, $fontPath, $line);

      // Draw "Simulated Bold" (Multiple passes with slight offset)
      // Offsets for thickness: center, +1x, -1x, +1y, -1y
      $offsets = [
        [0, 0],
        [1, 0],
        [-1, 0],
        [0, 1],
        [0, -1],
        [1, 1],
        [-1, -1],
        [1, -1],
        [-1, 1] // Diagonals for smoother bold
      ];

      foreach ($offsets as $offset) {
        imagettftext($image, $fontSize, 0, $x + $offset[0], $y + $offset[1], $white, $fontPath, $line);
      }
    }

    imagepng($image, $outputPath);
    imagedestroy($image);

    return $outputPath;
  }

  /**
   * Custom Wrapper for Arabic (RTL Aware)
   */
  protected function wrapArabicText($text, $maxCharsPerLine, $fontSize, $fontPath)
  {
    $words = explode(' ', $text);
    $lines = [];
    $currentLine = [];
    $currentChars = 0;

    foreach ($words as $word) {
      $wordLen = mb_strlen($word);
      if ($currentChars + $wordLen > $maxCharsPerLine && !empty($currentLine)) {
        // Process the line: shape each word, reverse words order
        $lines[] = $this->processRtlLine($currentLine);
        $currentLine = [];
        $currentChars = 0;
      }
      $currentLine[] = $word;
      $currentChars += $wordLen + 1;
    }

    if (!empty($currentLine)) {
      $lines[] = $this->processRtlLine($currentLine);
    }

    return $lines;
  }

  protected function processRtlLine($words)
  {
    $shapedWords = [];
    foreach ($words as $word) {
      $shapedWords[] = $this->utf8_strrev($this->shapeArabicWord($word));
    }
    // Reverse word order for RTL rendering in LTR engine
    return implode(' ', array_reverse($shapedWords));
  }

  /**
   * Fetch a random nature background image from loremflickr.
   */
  protected function getNatureBackground($sessionId)
  {
    $url = "https://loremflickr.com/{$this->width}/{$this->height}/nature";
    $outputPath = Storage::disk('public')->path("images/bg_{$sessionId}.jpg");

    Log::info("Fetching nature background from: {$url}");

    try {
      $client = new Client();
      $response = $client->get($url, ['sink' => $outputPath]);

      if ($response->getStatusCode() === 200 && file_exists($outputPath)) {
        return $outputPath;
      }
    } catch (\Exception $e) {
      Log::error("Failed to fetch nature background: " . $e->getMessage());
    }

    return null;
  }

  protected function shapeArabicWord($word)
  {
    // 1. Initial Glyphs and Mapping
    $glyphs = [
      'ا' => ['\uFE8D', '\uFE8E', '\uFE8D', '\uFE8E'],
      'أ' => ['\uFE83', '\uFE84', '\uFE83', '\uFE84'],
      'إ' => ['\uFE87', '\uFE88', '\uFE87', '\uFE88'],
      'آ' => ['\uFE81', '\uFE82', '\uFE81', '\uFE82'],
      'ٱ' => ['\uFB50', '\uFB51', '\uFB50', '\uFB51'], // Alef Wasla
      'ب' => ['\uFE8F', '\uFE90', '\uFE92', '\uFE91'],
      'ت' => ['\uFE95', '\uFE96', '\uFE98', '\uFE97'],
      'ث' => ['\uFE99', '\uFE9A', '\uFE9C', '\uFE9B'],
      'ج' => ['\uFE9D', '\uFE9E', '\uFEA0', '\uFE9F'],
      'ح' => ['\uFEA1', '\uFEA2', '\uFEA4', '\uFEA3'],
      'خ' => ['\uFEA5', '\uFEA6', '\uFEA8', '\uFEA7'],
      'د' => ['\uFEA9', '\uFEAA', '\uFEA9', '\uFEAA'],
      'ذ' => ['\uFEAB', '\uFEAC', '\uFEAB', '\uFEAC'],
      'ر' => ['\uFEAD', '\uFEAE', '\uFEAD', '\uFEAE'],
      'ز' => ['\uFEAF', '\uFEB0', '\uFEAF', '\uFEB0'],
      'س' => ['\uFEB1', '\uFEB2', '\uFEB4', '\uFEB3'],
      'ش' => ['\uFEB5', '\uFEB6', '\uFEB8', '\uFEB7'],
      'ص' => ['\uFEB9', '\uFEBA', '\uFEBC', '\uFEBB'],
      'ض' => ['\uFEBD', '\uFEBE', '\uFEC0', '\uFEBF'],
      'ط' => ['\uFEC1', '\uFEC2', '\uFEC4', '\uFEC3'],
      'ظ' => ['\uFEC5', '\uFEC6', '\uFEC8', '\uFEC7'],
      'ع' => ['\uFEC9', '\uFECA', '\uFECC', '\uFECB'],
      'غ' => ['\uFECD', '\uFECE', '\uFED0', '\uFECF'],
      'ف' => ['\uFED1', '\uFED2', '\uFED4', '\uFED3'],
      'ق' => ['\uFED5', '\uFED6', '\uFED8', '\uFED7'],
      'ك' => ['\uFED9', '\uFEDA', '\uFEDC', '\uFEDB'],
      'ل' => ['\uFEDD', '\uFEDE', '\uFEE0', '\uFEDF'],
      'م' => ['\uFEE1', '\uFEE2', '\uFEE4', '\uFEE3'],
      'ن' => ['\uFEE5', '\uFEE6', '\uFEE8', '\uFEE7'],
      'ه' => ['\uFEE9', '\uFEEA', '\uFEEC', '\uFEEB'],
      'و' => ['\uFEED', '\uFEEE', '\uFEED', '\uFEEE'],
      'ي' => ['\uFEF1', '\uFEF2', '\uFEF4', '\uFEF3'],
      'ى' => ['\uFEEF', '\uFEF0', '\uFEEF', '\uFEF0'],
      'ة' => ['\uFE93', '\uFE94', '\uFE93', '\uFE94'],
      'ء' => ['\uFE80', '\uFE80', '\uFE80', '\uFE80'],
    ];

    // 2. Pre-process ligatures (Simplified handling)
    $word = str_replace(['لأ', 'لإ', 'لآ', 'لا'], ['\uFEF7', '\uFEF9', '\uFEF5', '\uFEFB'], $word);

    // 3. Regex for Tashkeel and control characters
    $tashkeelRegex = '/[\x{064B}-\x{065F}\x{0670}\x{06D6}-\x{06ED}]/u';
    $controlRegex = '/[\x{200B}-\x{200F}\x{00A0}\x{FEFF}\x{0000}-\x{001F}]/u';

    // 4. Tokenize word into segments (base char + following marks)
    preg_match_all('/./us', $word, $rawChars);
    $rawChars = $rawChars[0];
    $segments = [];
    $currentSegment = null;

    foreach ($rawChars as $char) {
      // Remove control characters instantly but keep Tashkeel
      if (preg_match($controlRegex, $char))
        continue;

      $isBase = isset($glyphs[$char]) || in_array($char, ['\uFEF7', '\uFEF9', '\uFEF5', '\uFEFB']);

      if ($isBase) {
        if ($currentSegment)
          $segments[] = $currentSegment;
        $currentSegment = ['base' => $char, 'marks' => ''];
      } else {
        if ($currentSegment) {
          $currentSegment['marks'] .= $char;
        } else {
          // Initial marks if they exist (rare)
          $segments[] = ['base' => $char, 'marks' => '', 'is_mark_only' => true];
        }
      }
    }
    if ($currentSegment)
      $segments[] = $currentSegment;

    // 5. Shaping logic
    $canJoinLeft = function ($char) use ($glyphs) {
      $nonLeftJoiners = ['ا', 'أ', 'إ', 'آ', 'ٱ', 'د', 'ذ', 'ر', 'ز', 'و', 'ء', 'ة', '\uFEF7', '\uFEF9', '\uFEF5', '\uFEFB'];
      return (isset($glyphs[$char]) || in_array($char, ['\uFEF7', '\uFEF9', '\uFEF5', '\uFEFB'])) && !in_array($char, $nonLeftJoiners);
    };

    $result = '';
    $count = count($segments);

    for ($i = 0; $i < $count; $i++) {
      $segment = $segments[$i];
      $char = $segment['base'];

      if (isset($segment['is_mark_only']) || !isset($glyphs[$char])) {
        $result .= $char . ($segment['marks'] ?? '');
        continue;
      }

      $prevBase = ($i > 0 && !isset($segments[$i - 1]['is_mark_only'])) ? $segments[$i - 1]['base'] : null;
      $nextBase = ($i < $count - 1 && !isset($segments[$i + 1]['is_mark_only'])) ? $segments[$i + 1]['base'] : null;

      $joinsPrev = ($prevBase && $canJoinLeft($prevBase));
      $joinsNext = ($nextBase && (isset($glyphs[$nextBase]) || in_array($nextBase, ['\uFEF7', '\uFEF9', '\uFEF5', '\uFEFB'])) && $canJoinLeft($char));

      if ($joinsPrev && $joinsNext) {
        $form = 2; // Middle
      } elseif ($joinsPrev) {
        $form = 1; // End
      } elseif ($joinsNext) {
        $form = 3; // Beginning
      } else {
        $form = 0; // Isolated
      }

      $shapedBase = json_decode('"' . $glyphs[$char][$form] . '"');
      $result .= $shapedBase . $segment['marks'];
    }

    return $result;
  }

  protected function prepareArabicText($text)
  {
    // No longer used directly, integrated into wrapArabicText
    return $text;
  }

  protected function utf8_strrev($str)
  {
    preg_match_all('/./us', $str, $ar);
    return implode('', array_reverse($ar[0]));
  }

  /**
   * Create final video by combining background, audio, and sync'd overlays.
   */
  public function createFinalVideo($audioPath, array $overlayData, $sessionId, $backgroundPaths = [], $surahName = null, $fromAyah = null, $toAyah = null)
  {
    $this->ensureDirectoriesExist();

    // Generate descriptive filename
    $filename = "reels_{$sessionId}.mp4";
    if ($surahName && $fromAyah && $toAyah) {
      // Clean surah name for filename (remove special characters)
      $cleanSurahName = preg_replace('/[^a-zA-Z0-9\x{0600}-\x{06FF}\s_-]/u', '', $surahName);
      $cleanSurahName = str_replace(' ', '_', $cleanSurahName);
      $filename = "{$cleanSurahName}_من_{$fromAyah}-{$toAyah}.mp4";
    }

    $outputPath = Storage::disk('public')->path("videos/output/{$filename}");

    // Use provided backgrounds or fetch a random nature one
    if (empty($backgroundPaths)) {
      $backgroundPaths = [$this->getNatureBackground($sessionId)];
    }

    $duration = $this->getDuration($audioPath);

    // Handle multiple images: create a concatenated background
    $backgroundInput = "";
    $bgFilter = "";

    if (count($backgroundPaths) == 1) {
      // Single background (image or video)
      $backgroundPath = $backgroundPaths[0];
      $isImage = true;
      if ($backgroundPath) {
        $mime = mime_content_type($backgroundPath);
        $isImage = strpos($mime, 'image') !== false;
      }

      if (!$backgroundPath) {
        Log::warning("No background available, using solid color fallback.");
        $backgroundInput = "-f lavfi -i color=c=black:s={$this->width}x{$this->height}:d=1000";
        $bgFilter = "scale={$this->width}:{$this->height}[bg];";
      } else {
        if ($isImage) {
          $backgroundInput = "-loop 1 -i \"{$backgroundPath}\"";
          $bgFilter = "null[bg];";
        } else {
          // Video background: loop it
          $backgroundInput = "-stream_loop -1 -i \"{$backgroundPath}\"";
          $bgFilter = "scale=w='if(gt(iw/ih,{$this->width}/{$this->height}),-1,{$this->width})':h='if(gt(iw/ih,{$this->width}/{$this->height}),{$this->height},-1)',crop={$this->width}:{$this->height}[bg];";
        }
      }
    } else {
      // Multiple images: cycle through them
      $imageDuration = $duration / count($backgroundPaths);

      // Add all images as inputs
      foreach ($backgroundPaths as $path) {
        $backgroundInput .= "-loop 1 -t {$imageDuration} -i \"{$path}\" ";
      }

      // Build concat filter for multiple images - scale to fit without cropping (with padding)
      $scaleFilters = "";
      $concatInputs = "";
      for ($i = 0; $i < count($backgroundPaths); $i++) {
        $scaleFilters .= "[{$i}:v]scale={$this->width}:{$this->height}:force_original_aspect_ratio=decrease,pad={$this->width}:{$this->height}:(ow-iw)/2:(oh-ih)/2:black,setsar=1[v{$i}];";
        $concatInputs .= "[v{$i}]";
      }
      $bgFilter = $scaleFilters . "{$concatInputs}concat=n=" . count($backgroundPaths) . ":v=1:a=0[bg];";
    }

    // Calculate audio input index based on number of background images
    $audioInputIdx = count($backgroundPaths);

    $overlayInputs = "";
    $overlayFilters = "";
    $lastLabel = "[bg]";

    foreach ($overlayData as $i => $data) {
      // Overlay inputs start after backgrounds and audio
      $inputIdx = $audioInputIdx + 1 + $i;
      $overlayInputs .= " -loop 1 -i \"{$data['path']}\"";
      $start = $data['start'];
      $end = $data['end'];
      $nextLabel = "[v{$i}]";
      $overlayFilters .= "{$lastLabel}[{$inputIdx}:v]overlay=(W-w)/2:(H-h)/2:enable='between(t,{$start},{$end})'{$nextLabel}; ";
      $lastLabel = $nextLabel;
    }

    $filterComplex = $bgFilter . $overlayFilters;
    $filterComplex = rtrim($filterComplex, "; ");

    $command = "{$this->ffmpeg} -y " .
      "{$backgroundInput} " .
      "-i \"{$audioPath}\" " .
      "{$overlayInputs} " .
      "-filter_complex \"{$filterComplex}\" " .
      "-map \"{$lastLabel}\" -map {$audioInputIdx}:a " .
      "-c:v libx264 -c:a aac -shortest -pix_fmt yuv420p \"{$outputPath}\"";

    $success = $this->runCommand($command, "creating final video");

    // Cleanup temporary backgrounds
    foreach ($backgroundPaths as $bgPath) {
      if ($bgPath && strpos($bgPath, 'nature_bg_') !== false && file_exists($bgPath)) {
        @unlink($bgPath);
      }
    }

    return $success ? $outputPath : null;
  }
}
