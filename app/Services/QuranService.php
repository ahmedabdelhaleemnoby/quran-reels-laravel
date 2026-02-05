<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class QuranService
{
  protected $baseUrl = 'https://api.alquran.cloud/v1';
  protected $audioBaseUrl = 'https://cdn.alquran.cloud/media/audio/ayah';

  /**
   * Fetch all available reciters (audio editions).
   */
  public function getReciters()
  {
    return cache()->remember('quran_reciters', 86400, function () {
      $response = Http::get("{$this->baseUrl}/edition?format=audio&language=ar&type=versebyverse");
      if ($response->successful()) {
        return $response->json()['data'];
      }
      return [];
    });
  }

  /**
   * Fetch all Surahs.
   */
  public function getSurahs()
  {
    return cache()->remember('quran_surahs', 86400, function () {
      $response = Http::get("{$this->baseUrl}/surah");
      if ($response->successful()) {
        return $response->json()['data'];
      }
      return [];
    });
  }

  /**
   * Get specific surah information by number.
   */
  public function getSurahInfo($surahNumber)
  {
    $surahs = $this->getSurahs();
    foreach ($surahs as $surah) {
      if ($surah['number'] == $surahNumber) {
        return $surah;
      }
    }
    return null;
  }

  /**
   * Fetch Arabic text for a specific range of ayahs in a surah.
   */
  public function getAyahTexts($surahNumber, $fromAyah, $toAyah)
  {
    $response = Http::get("{$this->baseUrl}/surah/{$surahNumber}/editions/quran-uthmani");

    if ($response->successful()) {
      $ayahs = $response->json()['data'][0]['ayahs'];
      return array_filter($ayahs, function ($ayah) use ($fromAyah, $toAyah) {
        return $ayah['numberInSurah'] >= $fromAyah && $ayah['numberInSurah'] <= $toAyah;
      });
    }

    return [];
  }

  /**
   * Download audio file for a specific ayah.
   * Use Global Ayah Number (1-6236)
   */
  public function downloadAyahAudio($reciterIdentifier, $globalAyahNumber)
  {
    $url = "{$this->audioBaseUrl}/{$reciterIdentifier}/{$globalAyahNumber}";

    $localPath = "audio/{$reciterIdentifier}/{$globalAyahNumber}.mp3";

    if (Storage::exists($localPath)) {
      return Storage::path($localPath);
    }

    try {
      Log::info("Attempting to download audio from: {$url}");
      $response = Http::get($url);
      if ($response->successful()) {
        Storage::put($localPath, $response->body());
        return Storage::path($localPath);
      } else {
        Log::error("Failed to download audio. Status: " . $response->status() . " URL: {$url}");
      }
    } catch (\Exception $e) {
      Log::error("Exception while downloading audio: " . $e->getMessage() . " URL: {$url}");
    }

    return null;
  }
}
