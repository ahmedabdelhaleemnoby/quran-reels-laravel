# Quran Reels Generator

A Laravel 12.0 application that generates high-quality Quran recitation reels for social media (TikTok, Instagram, YouTube Shorts).

## 🚀 Features

- **Reciter Selection**: Access to a wide range of Quran reciters via the Al Quran Cloud API.
- **Surah & Ayah Customization**: Select specific Surahs and Ayah ranges for video generation.
- **Automated Video Processing**: 
  - Dynamic Arabic text overlays with beautiful typography (Amiri font).
  - High-quality audio fetching and merging.
  - Video composition using **FFmpeg** and **ImageMagick**.
- **Vertical Format**: Generates videos in 1080x1920 format, optimized for mobile platforms.
- **Arabic RTL Support**: Proper rendering of Quranic text with RTL support and shadows.

## 🛠️ Tech Stack

- **Framework**: Laravel 12.x
- **Video Engine**: FFmpeg
- **Image Processing**: ImageMagick
- **APIs**: Al Quran Cloud (api.alquran.cloud)
- **Frontend**: Blade + Vanilla CSS (Glassmorphism design)

## 📁 Installation

### 1. Prerequisites

- PHP 8.2+
- Composer 2.x
- **FFmpeg** installed and accessible in the system path.
- **ImageMagick** installed with **pango** support (for high-quality Arabic rendering).
- **Amiri** font installed on the system.

### 2. Setup

```bash
# Clone the repository
git clone <repository-url>
cd quran-reels-laravel

# Install dependencies
composer install

# Environment setup
cp .env.example .env
php artisan key:generate

# Storage setup
php artisan storage:link
mkdir -p storage/app/audio storage/app/images storage/app/videos/output
```

### 3. Configuration

Update your `.env` file with the paths to your FFmpeg and ImageMagick binaries if they are not in the default system path:

```env
FFMPEG_PATH=/usr/local/bin/ffmpeg
FFPROBE_PATH=/usr/local/bin/ffprobe
MAGICK_PATH=/usr/local/bin/magick
```

### 4. Running

```bash
php artisan serve
```

Access the application at: `http://localhost:8000`

## 📚 How it Works

1. **Fetch Data**: The application fetches surah names and reciter lists from Al Quran Cloud.
2. **Audio Sync**: Audio files for each ayah are downloaded and their durations calculated.
3. **Overlay Generation**: ImageMagick creates transparent PNG overlays with the Quranic text.
4. **Final Render**: FFmpeg combines a background image (or gradient), the merged audio, and the text overlays synced to the audio timing.

---

**Crafted for Spiritual Engagement**
