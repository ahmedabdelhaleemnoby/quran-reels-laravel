<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class QuranService
{
    protected $baseUrl = 'https://api.alquran.cloud/v1';
    protected $audioBaseUrl = 'https://everyayah.com/data';

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
     */
    public function downloadAyahAudio($reciterIdentifier, $surahNumber, $ayahNumber)
    {
        // Format: 001001.mp3 (3 digits for surah, 3 digits for ayah)
        $fileName = sprintf('%03d%03d.mp3', $surahNumber, $ayahNumber);
        $url = "{$this->audioBaseUrl}/{$reciterIdentifier}/{$fileName}";
        
        $localPath = "audio/{$reciterIdentifier}/{$fileName}";
        
        if (Storage::exists($localPath)) {
            return Storage::path($localPath);
        }

        try {
            $response = Http::get($url);
            if ($response->successful()) {
                Storage::put($localPath, $response->body());
                return Storage::path($localPath);
            }
        } catch (\Exception $e) {
            Log::error("Failed to download audio: " . $e->getMessage());
        }

        return null;
    }
}
