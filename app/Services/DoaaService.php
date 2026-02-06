<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class DoaaService
{
  protected $hadithApiBase = 'https://api.hadith.gading.dev';
  protected $adhkarJsonUrl = 'https://raw.githubusercontent.com/rn0x/Adhkar-json/main/adhkar.json';
  protected $adhkarAudioBase = 'https://raw.githubusercontent.com/rn0x/Adhkar-json/main';

  public function getBooks()
  {
    try {
      $response = Http::get("{$this->hadithApiBase}/books");
      if ($response->successful()) {
        return $response->json()['data'] ?? [];
      }
    } catch (\Exception $e) {
      Log::error("Failed to fetch hadith books: " . $e->getMessage());
    }
    return [];
  }

  public function getHadithsByBook($bookId, $start = 1, $end = 50)
  {
    try {
      $response = Http::get("{$this->hadithApiBase}/books/{$bookId}?range={$start}-{$end}");
      if ($response->successful()) {
        $data = $response->json();
        return $data['data']['hadiths'] ?? [];
      }
    } catch (\Exception $e) {
      Log::error("Failed to fetch hadiths: " . $e->getMessage());
    }
    return [];
  }

  public function getSpecificHadith($bookId, $number)
  {
    try {
      $response = Http::get("{$this->hadithApiBase}/books/{$bookId}/{$number}");
      if ($response->successful()) {
        $data = $response->json();
        return $data['data'] ?? null;
      }
    } catch (\Exception $e) {
      Log::error("Failed to fetch specific hadith: " . $e->getMessage());
    }
    return null;
  }

  public function getDoaaCategories()
  {
    // Map GitHub categories to our category IDs
    return [
      [
        'id' => 1,
        'key' => 'morning',
        'name' => 'أذكار الصباح والمساء',
        'name_en' => 'Morning & Evening Azkar'
      ],
      [
        'id' => 2,
        'key' => 'sleep',
        'name' => 'أذكار النوم',
        'name_en' => 'Sleep Azkar'
      ],
      [
        'id' => 3,
        'key' => 'wakeup',
        'name' => 'أذكار الاستيقاظ من النوم',
        'name_en' => 'Waking Up Azkar'
      ],
      [
        'id' => 4,
        'key' => 'prayer',
        'name' => 'أذكار الصلاة',
        'name_en' => 'Prayer Azkar'
      ],
      [
        'id' => 5,
        'key' => 'mosque',
        'name' => 'أذكار المسجد',
        'name_en' => 'Mosque Azkar'
      ],
      [
        'id' => 6,
        'key' => 'adhan',
        'name' => 'أذكار الأذان',
        'name_en' => 'Adhan Azkar'
      ],
      [
        'id' => 7,
        'key' => 'home',
        'name' => 'أذكار المنزل',
        'name_en' => 'Home Azkar'
      ]
    ];
  }

  public function getDoaasByCategory($categoryId)
  {
    try {
      // Fetch adhkar data from GitHub
      $response = Http::timeout(10)->get($this->adhkarJsonUrl);

      if (!$response->successful()) {
        Log::error('Failed to fetch adhkar JSON from GitHub');
        return [];
      }

      $adhkarData = $response->json();

      // Find the category by ID
      $categoryData = collect($adhkarData)->firstWhere('id', (int)$categoryId);

      if (!$categoryData) {
        return [];
      }

      // Format the doaas for our application
      $doaas = [];
      foreach ($categoryData['array'] as $index => $item) {
        $doaas[] = [
          'id' => $item['id'],
          'text' => $item['text'],
          'title' => $categoryData['category'] . ' - ' . ($index + 1),
          'count' => $item['count'] ?? 1,
          'audio' => $item['audio'] ?? null,
          'filename' => $item['filename'] ?? null
        ];
      }

      return $doaas;

    } catch (\Exception $e) {
      Log::error('Error fetching doaas by category: ' . $e->getMessage());
      return [];
    }
  }

  public function getDoaaReciters()
  {
    return [
      [
        'id' => 'mishary',
        'name' => 'مشاري راشد العفاسي',
        'name_en' => 'Mishary Rashid Alafasy'
      ],
      [
        'id' => 'sudais',
        'name' => 'عبد الرحمن السديس',
        'name_en' => 'Abdul Rahman Al-Sudais'
      ],
      [
        'id' => 'shuraim',
        'name' => 'سعود الشريم',
        'name_en' => 'Saud Al-Shuraim'
      ],
      [
        'id' => 'ajmy',
        'name' => 'أحمد العجمي',
        'name_en' => 'Ahmad Al-Ajmy'
      ],
      [
        'id' => 'juhany',
        'name' => 'عبد الله الجهني',
        'name_en' => 'Abdullah Al-Juhany'
      ]
    ];
  }

  public function downloadDoaaAudio($audioPath, $sessionId)
  {
    try {
      if (!$audioPath) {
        Log::warning('No audio path provided for doaa');
        return null;
      }

      $audioDir = Storage::disk('public')->path('audio');
      if (!file_exists($audioDir)) {
        mkdir($audioDir, 0755, true);
      }

      // Extract filename from path
      $filename = basename($audioPath);
      $localPath = Storage::disk('public')->path("audio/doaa_{$sessionId}_{$filename}");

      // Check if already downloaded
      if (file_exists($localPath)) {
        return $localPath;
      }

      // Download from GitHub
      $audioUrl = $this->adhkarAudioBase . $audioPath;
      Log::info("Downloading doaa audio from: {$audioUrl}");

      $response = Http::timeout(30)->get($audioUrl);

      if ($response->successful()) {
        file_put_contents($localPath, $response->body());

        if (file_exists($localPath) && filesize($localPath) > 0) {
          Log::info("Successfully downloaded doaa audio to: {$localPath}");
          return $localPath;
        }
      }

      Log::warning("Failed to download doaa audio from GitHub");
      return null;

    } catch (\Exception $e) {
      Log::error("Error downloading doaa audio: " . $e->getMessage());
      return null;
    }
  }

  protected function createSilentAudio($sessionId)
  {
    $audioPath = Storage::disk('public')->path("audio/silent_{$sessionId}.mp3");
    $ffmpeg = env('FFMPEG_PATH', 'ffmpeg');
    $command = "{$ffmpeg} -f lavfi -i anullsrc=r=44100:cl=stereo -t 5 -q:a 9 -acodec libmp3lame \"{$audioPath}\" -y";
    exec($command . ' 2>&1', $output, $returnCode);

    if ($returnCode === 0 && file_exists($audioPath)) {
      return $audioPath;
    }

    return null;
  }
}
