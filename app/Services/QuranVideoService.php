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
   * Generate Arabic text overlay image using PHP GD.
   * This handles basic RTL and Shaping (Ligatures).
   */
  public function generateTextOverlay($text, $index, $sessionId, $options = [])
  {
    $outputPath = Storage::disk('public')->path("images/overlay_{$sessionId}_{$index}.png");
    $fontPath = $this->getFontPath();

    if (!$fontPath) {
      Log::error("No suitable Arabic font found for GD rendering.");
      return null;
    }

    $image = imagecreatetruecolor($this->width, $this->height);
    imagesavealpha($image, true);
    $transparent = imagecolorallocatealpha($image, 0, 0, 0, 127);
    imagefill($image, 0, 0, $transparent);

    $this->renderStyledText($image, $text, $fontPath, $options);

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
    $glyphs = [
      'ا' => ['\uFE8D', '\uFE8E', '\uFE8D', '\uFE8E'],
      'أ' => ['\uFE83', '\uFE84', '\uFE83', '\uFE84'],
      'إ' => ['\uFE87', '\uFE88', '\uFE87', '\uFE88'],
      'آ' => ['\uFE81', '\uFE82', '\uFE81', '\uFE82'],
      'ٱ' => ['\uFB50', '\uFB51', '\uFB50', '\uFB51'],
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

    $word = str_replace(['لأ', 'لإ', 'لآ', 'لا'], ['\uFEF7', '\uFEF9', '\uFEF5', '\uFEFB'], $word);
    $controlRegex = '/[\x{200B}-\x{200F}\x{00A0}\x{FEFF}\x{0000}-\x{001F}]/u';

    preg_match_all('/./us', $word, $rawChars);
    $rawChars = $rawChars[0];
    $segments = [];
    $currentSegment = null;

    foreach ($rawChars as $char) {
      if (preg_match($controlRegex, $char))
        continue;
      $isBase = isset($glyphs[$char]) || in_array($char, ['\uFEF7', '\uFEF9', '\uFEF5', '\uFEFB']);
      if ($isBase) {
        if ($currentSegment)
          $segments[] = $currentSegment;
        $currentSegment = ['base' => $char, 'marks' => ''];
      } else {
        if ($currentSegment)
          $currentSegment['marks'] .= $char;
        else
          $segments[] = ['base' => $char, 'marks' => '', 'is_mark_only' => true];
      }
    }
    if ($currentSegment)
      $segments[] = $currentSegment;

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
      if ($joinsPrev && $joinsNext)
        $form = 2;
      elseif ($joinsPrev)
        $form = 1;
      elseif ($joinsNext)
        $form = 3;
      else
        $form = 0;
      $shapedBase = json_decode('"' . $glyphs[$char][$form] . '"');
      $result .= $shapedBase . $segment['marks'];
    }
    return $result;
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
    $filename = "reels_{$sessionId}.mp4";
    if ($surahName && $fromAyah && $toAyah) {
      $cleanSurahName = preg_replace('/[^a-zA-Z0-9\x{0600}-\x{06FF}\s_-]/u', '', $surahName);
      $cleanSurahName = str_replace(' ', '_', $cleanSurahName);
      $filename = "{$cleanSurahName}_من_{$fromAyah}-{$toAyah}.mp4";
    }
    $outputPath = Storage::disk('public')->path("videos/output/{$filename}");
    if (empty($backgroundPaths))
      $backgroundPaths = [$this->getNatureBackground($sessionId)];
    $duration = $this->getDuration($audioPath);
    $backgroundInput = "";
    $bgFilter = "";

    if (count($backgroundPaths) == 1) {
      $backgroundPath = $backgroundPaths[0];
      $isImage = true;
      if ($backgroundPath) {
        $mime = mime_content_type($backgroundPath);
        $isImage = strpos($mime, 'image') !== false;
      }
      if (!$backgroundPath) {
        $backgroundInput = "-f lavfi -i color=c=black:s={$this->width}x{$this->height}:d=1000";
        $bgFilter = "scale={$this->width}:{$this->height}[bg];";
      } else {
        if ($isImage) {
          $backgroundInput = "-loop 1 -i \"{$backgroundPath}\"";
          $bgFilter = "scale=trunc(iw/2)*2:trunc(ih/2)*2[bg];";
        } else {
          $backgroundInput = "-stream_loop -1 -i \"{$backgroundPath}\"";
          $bgFilter = "scale=trunc(iw/2)*2:trunc(ih/2)*2[bg];";
        }
      }
    } else {
      $imageDuration = $duration / count($backgroundPaths);
      foreach ($backgroundPaths as $path)
        $backgroundInput .= "-loop 1 -t {$imageDuration} -i \"{$path}\" ";
      $scaleFilters = "";
      $concatInputs = "";
      for ($i = 0; $i < count($backgroundPaths); $i++) {
        $scaleFilters .= "[{$i}:v]scale={$this->width}:{$this->height}:force_original_aspect_ratio=decrease,pad={$this->width}:{$this->height}:(ow-iw)/2:(oh-ih)/2:black,setsar=1[v{$i}];";
        $concatInputs .= "[v{$i}]";
      }
      $bgFilter = $scaleFilters . "{$concatInputs}concat=n=" . count($backgroundPaths) . ":v=1:a=0[bg];";
    }

    $audioInputIdx = count($backgroundPaths);
    $overlayInputs = "";
    $overlayFilters = "";
    $lastLabel = "[bg]";
    foreach ($overlayData as $i => $data) {
      $inputIdx = $audioInputIdx + 1 + $i;
      $overlayInputs .= " -loop 1 -i \"{$data['path']}\"";
      $start = number_format($data['start'], 3, '.', '');
      $end = number_format($data['end'], 3, '.', '');
      $nextLabel = "[v{$i}]";
      $overlayFilters .= "{$lastLabel}[{$inputIdx}:v]overlay=(W-w)/2:(H-h)/2:enable='between(t,{$start},{$end})'{$nextLabel}; ";
      $lastLabel = $nextLabel;
    }
    $filterComplex = $bgFilter . $overlayFilters;
    $filterComplex = rtrim($filterComplex, "; ");
    $command = "{$this->ffmpeg} -y {$backgroundInput} -i \"{$audioPath}\" {$overlayInputs} -filter_complex \"{$filterComplex}\" -map \"{$lastLabel}\" -map {$audioInputIdx}:a -c:v libx264 -c:a aac -shortest -pix_fmt yuv420p \"{$outputPath}\"";
    $success = $this->runCommand($command, "creating final video");
    foreach ($backgroundPaths as $bgPath) {
      if ($bgPath && strpos($bgPath, 'nature_bg_') !== false && file_exists($bgPath))
        @unlink($bgPath);
    }
    return $success ? $outputPath : null;
  }

  public function generateSlidePreview($text, $index, $sessionId, $backgroundPath = null, $options = [])
  {
    $outputPath = Storage::disk('public')->path("images/slide_{$sessionId}_{$index}.png");
    $fontPath = $this->getFontPath();
    if (!$fontPath)
      return null;

    if ($backgroundPath && file_exists($backgroundPath)) {
      $mime = mime_content_type($backgroundPath);
      if (strpos($mime, 'image') !== false) {
        $ext = pathinfo($backgroundPath, PATHINFO_EXTENSION);
        if (in_array(strtolower($ext), ['jpg', 'jpeg']))
          $image = imagecreatefromjpeg($backgroundPath);
        elseif (strtolower($ext) === 'png')
          $image = imagecreatefrompng($backgroundPath);
        else {
          $image = imagecreatetruecolor($this->width, $this->height);
          $bg = imagecolorallocate($image, 20, 20, 40);
          imagefill($image, 0, 0, $bg);
        }
        $resized = imagescale($image, $this->width, $this->height);
        imagedestroy($image);
        $image = $resized;
      } else {
        $image = imagecreatetruecolor($this->width, $this->height);
        $bg = imagecolorallocate($image, 20, 20, 40);
        imagefill($image, 0, 0, $bg);
      }
    } else {
      $image = imagecreatetruecolor($this->width, $this->height);
      $bg = imagecolorallocate($image, 20, 20, 40);
      imagefill($image, 0, 0, $bg);
    }

    $this->renderStyledText($image, $text, $fontPath, $options);
    imagepng($image, $outputPath);
    imagedestroy($image);
    return $outputPath;
  }

  protected function renderStyledText($image, $text, $fontPath, $options = [])
  {
    $fontSize = $options['font_size'] ?? 65;
    $textColor = $options['text_color'] ?? '#FFFFFF';
    $isBold = $options['bold'] ?? true;
    $lineHeightMultiplier = $options['line_height'] ?? 1.6;
    $position = $options['text_position'] ?? 'middle';
    $bgStyle = $options['text_bg_style'] ?? 'shadow';
    $bgColor = $options['text_bg_color'] ?? '#000000';
    $bgOpacity = $options['text_bg_opacity'] ?? 0.5;

    list($r, $g, $b) = sscanf($textColor, "#%02x%02x%02x");
    $mainColor = imagecolorallocate($image, $r, $g, $b);
    $lines = $this->wrapArabicText($text, 25, $fontSize, $fontPath);
    $lineHeight = $fontSize * $lineHeightMultiplier;
    $totalHeight = count($lines) * $lineHeight;

    if ($position === 'top')
      $startY = ($this->height * 0.2) + $fontSize;
    elseif ($position === 'bottom')
      $startY = ($this->height * 0.8) - $totalHeight + $fontSize;
    else
      $startY = ($this->height - $totalHeight) / 2 + $fontSize;

    foreach ($lines as $i => $line) {
      $bbox = imagettfbbox($fontSize, 0, $fontPath, $line);
      $textWidth = $bbox[2] - $bbox[0];
      $x = ($this->width - $textWidth) / 2;
      $y = $startY + ($i * $lineHeight);
      if ($bgStyle === 'letterbox') {
        list($br, $bg, $bb) = sscanf($bgColor, "#%02x%02x%02x");
        $alpha = (int) (127 * (1 - $bgOpacity));
        $boxColor = imagecolorallocatealpha($image, $br, $bg, $bb, $alpha);
        $padding = $fontSize * 0.3;
        imagefilledrectangle($image, $x - $padding, $y - $fontSize - $padding / 2, $x + $textWidth + $padding, $y + $padding / 2, $boxColor);
      } elseif ($bgStyle === 'shadow') {
        $shadowColor = imagecolorallocatealpha($image, 0, 0, 0, 80);
        imagettftext($image, $fontSize, 0, $x + 4, $y + 4, $shadowColor, $fontPath, $line);
      }
      if ($isBold) {
        $offsets = [[0, 0], [1, 0], [-1, 0], [0, 1], [0, -1], [1, 1], [-1, -1], [1, -1], [-1, 1]];
        foreach ($offsets as $offset)
          imagettftext($image, $fontSize, 0, $x + $offset[0], $y + $offset[1], $mainColor, $fontPath, $line);
      } else
        imagettftext($image, $fontSize, 0, $x, $y, $mainColor, $fontPath, $line);
    }
  }

  /**
   * Delete old temporary files to keep the project clean.
   */
  public function cleanupFiles()
  {
    $folders = ['images', 'videos/output', 'audio', 'backgrounds'];
    foreach ($folders as $folder) {
      $files = Storage::disk('public')->files($folder);
      foreach ($files as $file) {
        if (basename($file) !== '.gitignore')
          Storage::disk('public')->delete($file);
      }
    }
    Log::info("Project cleanup performed: deleted temporary files.");
    return true;
  }
}
