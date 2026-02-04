# 📖 Quran Reels Generator

[![Laravel Version](https://img.shields.io/badge/Laravel-12.0-red.svg)](https://laravel.com)
[![PHP Version](https://img.shields.io/badge/PHP-8.4-777bb4.svg)](https://php.net)
[![License](https://img.shields.io/badge/license-MIT-blue.svg)](LICENSE)
[![FFmpeg](https://img.shields.io/badge/Engine-FFmpeg-green.svg)](https://ffmpeg.org)

A powerful Laravel 12.0 application designed to generate high-quality Quran recitation reels for social media platforms like TikTok, Instagram, and YouTube Shorts.

---

## ✨ Features

- **🎯 Reciter Selection**: Choose from 100+ world-renowned reciters via the [Al Quran Cloud API](https://alquran.cloud).
- **📖 Custom Range**: Select any Surah and Ayah range to create specific thematic reels.
- **🎨 Premium Visuals**: 
  - **Ken Burns Effect**: Dynamic zooming and panning for nature backgrounds.
  - **Elegant Typography**: High-quality Arabic rendering using the **Amiri** font.
  - **RTL Shadows**: Smooth, readable text with professional-grade drop shadows.
- **⚡ Automated Pipeline**: 
  - Instant audio merging.
  - Real-time text-to-image overlay generation.
  - Optimized MP4 encoding in vertical **1080x1920** resolution.

---

## 🛠️ Tech Stack

- **Backend**: Laravel 12.x (PHP 8.4)
- **Video Engine**: FFmpeg & FFprobe
- **Graphics**: ImageMagick (with Pango & Ghostscript support)
- **Storage**: Laravel File Storage (S3 compatible)
- **Frontend**: Blade, Vanilla CSS (Glassmorphic UI)

---

## 🚀 Quick Start (Local)

### 1. Prerequisites
Ensure you have the following installed:
- PHP 8.2+
- Composer 2.x
- FFmpeg & ImageMagick

### 2. Setup
```bash
# Clone and Install
git clone https://github.com/ahmedabdelhaleemnoby/quran-reels-laravel.git
cd quran-reels-laravel
composer install

# Environment
cp .env.example .env
php artisan key:generate

# Storage Link
php artisan storage:link
mkdir -p storage/app/audio storage/app/images storage/app/videos/output
```

---

## 🐳 Docker Setup

The project includes a production-ready `Dockerfile` based on Alpine Linux.

```bash
# Build the image
docker build -t quran-reels .

# Run the container
docker run -p 8000:8000 \
  -v $(pwd)/storage:/var/www/html/storage \
  --env-file .env \
  quran-reels
```

---

## ⚙️ Environment Variables

| Key | Description | Default |
|-----|-------------|---------|
| `FFMPEG_PATH` | Path to FFmpeg binary | `ffmpeg` |
| `FFPROBE_PATH` | Path to FFprobe binary | `ffprobe` |
| `MAGICK_PATH` | Path to ImageMagick binary | `magick` |
| `APP_RECITER_DEFAULT` | Default reciter identifier | `ar.alafasy` |

---

## 🛠 Troubleshooting

- **Text Rendering**: If Arabic text looks like "boxes", ensure the **Amiri** font is installed on your system.
  - *Ubuntu*: `sudo apt install fonts-amiri`
  - *MacOS*: `brew install font-amiri`
- **FFmpeg Errors**: Verify paths using `which ffmpeg` and update `.env`.
- **Memory Limit**: For long ayah ranges, ensure `php.ini` has `memory_limit` set to at least `256M`.

---

**Developed for Spiritual Engagement & Social Media Excellence**
