<!DOCTYPE html>
<html lang="ar" dir="rtl">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Quran Reels Generator</title>
  <link href="https://fonts.googleapis.com/css2?family=Amiri:wght@400;700&family=Inter:wght@300;400;600&display=swap"
    rel="stylesheet">
  <style>
    :root {
      --primary: #064e3b;
      --primary-light: #065f46;
      --accent: #fbbf24;
      --bg: #f8fafc;
      --card-bg: #ffffff;
      --text: #1e293b;
    }

    body {
      font-family: 'Inter', 'Amiri', serif;
      background-color: var(--bg);
      color: var(--text);
      margin: 0;
      padding: 20px;
      display: flex;
      justify-content: center;
      align-items: center;
      min-height: 100vh;
    }

    .container {
      max-width: 600px;
      width: 100%;
      background: var(--card-bg);
      padding: 40px;
      border-radius: 20px;
      box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1), 0 8px 10px -6px rgba(0, 0, 0, 0.1);
      border-top: 8px solid var(--primary);
    }

    h1 {
      color: var(--primary);
      text-align: center;
      margin-bottom: 30px;
      font-family: 'Amiri', serif;
      font-size: 2.5rem;
    }

    .form-group {
      margin-bottom: 20px;
    }

    label {
      display: block;
      margin-bottom: 8px;
      font-weight: 600;
      color: var(--primary);
    }

    select,
    input {
      width: 100%;
      padding: 12px;
      border: 2px solid #e2e8f0;
      border-radius: 10px;
      font-size: 1rem;
      transition: border-color 0.2s;
      box-sizing: border-box;
    }

    select:focus,
    input:focus {
      outline: none;
      border-color: var(--primary);
    }

    .grid {
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 20px;
    }

    button {
      width: 100%;
      background-color: var(--primary);
      color: white;
      padding: 15px;
      border: none;
      border-radius: 10px;
      font-size: 1.1rem;
      font-weight: 600;
      cursor: pointer;
      transition: background-color 0.2s, transform 0.1s;
      margin-top: 10px;
    }

    button:hover {
      background-color: var(--primary-light);
    }

    button:active {
      transform: scale(0.98);
    }

    .alert {
      padding: 15px;
      border-radius: 10px;
      margin-bottom: 20px;
      text-align: center;
    }

    .alert-success {
      background-color: #d1fae5;
      color: #065f46;
      border: 1px solid #10b981;
    }

    .alert-error {
      background-color: #fee2e2;
      color: #991b1b;
      border: 1px solid #ef4444;
    }

    .video-preview {
      margin-top: 30px;
      text-align: center;
    }

    video {
      max-width: 100%;
      border-radius: 15px;
      box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
    }



    .social-share {
      margin-top: 25px;
      padding: 20px;
      background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%);
      border-radius: 15px;
      text-align: center;
    }

    .social-share h4 {
      color: var(--primary);
      font-size: 1.1rem;
      margin-bottom: 15px;
      font-weight: 600;
    }

    .social-icons {
      display: flex;
      justify-content: center;
      gap: 15px;
      flex-wrap: wrap;
    }

    .social-icon {
      width: 50px;
      height: 50px;
      border-radius: 50%;
      display: flex;
      align-items: center;
      justify-content: center;
      color: white;
      font-size: 1.5rem;
      text-decoration: none;
      transition: all 0.3s ease;
      box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
      cursor: pointer;
    }

    .social-icon:hover {
      transform: translateY(-5px);
      box-shadow: 0 8px 12px rgba(0, 0, 0, 0.2);
    }

    .social-icon.facebook {
      background: linear-gradient(135deg, #1877f2 0%, #0c63d4 100%);
    }

    .social-icon.whatsapp {
      background: linear-gradient(135deg, #25d366 0%, #1da851 100%);
    }

    .social-icon.tiktok {
      background: linear-gradient(135deg, #000000 0%, #333333 100%);
    }

    .social-icon.instagram {
      background: linear-gradient(135deg, #f09433 0%, #e6683c 25%, #dc2743 50%, #cc2366 75%, #bc1888 100%);
    }

    .social-icon.snapchat {
      background: linear-gradient(135deg, #fffc00 0%, #f5e600 100%);
      color: #000000;
    }

    .social-icon.twitter {
      background: linear-gradient(135deg, #000000 0%, #1a1a1a 100%);
    }


    #progress-overlay {
      display: none;
      position: fixed;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      background: rgba(0, 0, 0, 0.85);
      z-index: 9999;
      justify-content: center;
      align-items: center;
      flex-direction: column;
      color: white;
      backdrop-filter: blur(8px);
    }

    /* Circular Loader */
    .loader {
      border: 5px solid #f3f3f3;
      border-top: 5px solid var(--accent);
      border-radius: 50%;
      width: 60px;
      height: 60px;
      animation: spin 1s linear infinite;
      margin-bottom: 20px;
    }

    @keyframes spin {
      0% {
        transform: rotate(0deg);
      }

      100% {
        transform: rotate(360deg);
      }
    }

    #progress-text {
      font-size: 1.5rem;
      font-weight: 700;
      margin-top: 10px;
      font-family: 'Inter', sans-serif;
    }

    /* Custom Alert Popup */
    .custom-alert {
      display: none;
      position: fixed;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      background: rgba(0, 0, 0, 0.7);
      z-index: 10000;
      justify-content: center;
      align-items: center;
      backdrop-filter: blur(5px);
    }

    .custom-alert-content {
      background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
      padding: 30px 40px;
      border-radius: 20px;
      box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
      text-align: center;
      max-width: 500px;
      animation: popIn 0.3s ease-out;
    }

    @keyframes popIn {
      0% {
        transform: scale(0.8);
        opacity: 0;
      }
      100% {
        transform: scale(1);
        opacity: 1;
      }
    }

    .custom-alert-content h3 {
      color: white;
      font-size: 1.5rem;
      margin-bottom: 15px;
      font-weight: 700;
    }

    .custom-alert-content p {
      color: rgba(255, 255, 255, 0.95);
      font-size: 1.1rem;
      margin-bottom: 25px;
      line-height: 1.6;
    }

    .custom-alert-btn {
      background: white;
      color: #667eea;
      border: none;
      padding: 12px 40px;
      border-radius: 50px;
      font-size: 1rem;
      font-weight: 600;
      cursor: pointer;
      transition: all 0.3s ease;
      box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
    }

    .custom-alert-btn:hover {
      transform: translateY(-2px);
      box-shadow: 0 6px 20px rgba(0, 0, 0, 0.3);
    }

    .progress-status {
      margin-top: 15px;
      font-size: 1.1rem;
      color: #cbd5e1;
    }

    .upload-hint {
      font-size: 0.8rem;
      color: #71717a;
      margin-top: 4px;
    }
  </style>
</head>

<body>
  <div class="container">
    <h1>Quran Reels Generator</h1>

    @if(session('success'))
      <div class="alert alert-success">
        {{ session('success') }}
      </div>
    @endif

    @if(session('error'))
      <div class="alert alert-error">
        {{ session('error') }}
      </div>
    @endif

    @if($errors->any())
      <div class="alert alert-error">
        <ul style="list-style: none; padding: 0; margin: 0;">
          @foreach($errors->all() as $error)
            <li>{{ $error }}</li>
          @endforeach
        </ul>
      </div>
    @endif

    <form action="{{ route('generator.generate') }}" method="POST" enctype="multipart/form-data" id="generator-form">
      @csrf
      <div class="form-group">
        <label for="reciter">القارئ (Reciter)</label>
        <select name="reciter" id="reciter" required>
          <option value="">Choose a reciter...</option>
          @foreach($reciters as $reciter)
            <option value="{{ $reciter['identifier'] }}">{{ $reciter['name'] }} ({{ $reciter['englishName'] }})</option>
          @endforeach
        </select>
      </div>

      <div class="form-group">
        <label for="surah">السورة (Surah)</label>
        <select name="surah" id="surah" required>
          <option value="">Choose a surah...</option>
          @foreach($surahs as $surah)
            <option value="{{ $surah['number'] }}" data-verses="{{ $surah['numberOfAyahs'] }}">{{ $surah['number'] }}.
              {{ $surah['name'] }}
              ({{ $surah['englishName'] }})</option>
          @endforeach
        </select>
      </div>

      <div class="grid">
        <div class="form-group">
          <label for="ayah_from">من الآية (From Ayah)</label>
          <input type="number" name="ayah_from" id="ayah_from" min="1" max="286" value="1" required>
        </div>
        <div class="form-group">
          <label for="ayah_to">إلى الآية (To Ayah)</label>
          <input type="number" name="ayah_to" id="ayah_to" min="1" max="286" value="1" required>
        </div>
      </div>

      <div class="form-group">
        <label for="duration">المدة القصوى (Max Duration - Sec)</label>
        <input type="number" name="duration" id="duration" min="5" max="60" value="30">
      </div>

      <div class="form-group">
        <label for="background">خلفية مخصصة (Custom Background - Images/Video)</label>
        <input type="file" name="background[]" id="background" accept="image/*,video/*" multiple>
        <div class="upload-hint">رفع صورة واحدة أو مجموعة صور (حد أقصى 10 صور - سيتم عرضها بالترتيب) أو فيديو. الحد الأقصى: 50 ميجابايت لكل ملف.</div>
      </div>


      <div class="form-group">
        <label style="display: flex; align-items: center; gap: 10px; cursor: pointer;">
          <input type="checkbox" name="no_text_overlay" id="no_text_overlay" value="1" style="width: auto; cursor: pointer;">
          <span>فيديو بدون كتابة (Audio & Background Only)</span>
        </label>
        <div class="upload-hint">اختر هذا الخيار إذا كنت تريد الفيديو بالصوت والخلفية فقط بدون نص الآيات</div>
      </div>
      <button type="submit">🎬 إنشاء الريل</button>
    </form>

    @if(session('video_url'))
      <div class="video-preview">
        <h3>Your Generated Reel</h3>
        <video controls>
          <source src="{{ session('video_url') }}" type="video/mp4">
          Your browser does not support the video tag.
        </video>
        <p style="margin-top: 10px;">
          <a href="{{ session('video_url') }}" download
            style="color: var(--primary); font-weight: 600; text-decoration: none;">⬇️ تحميل الفيديو</a>
        </p>

        <div class="social-share">
          <h4>شارك الفيديو على السوشيال ميديا</h4>
          <div class="social-icons">
            <div class="social-icon facebook" onclick="shareOnFacebook('{{ session('video_url') }}')" title="Facebook">
              <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="currentColor"><path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/></svg>
            </div>
            <div class="social-icon whatsapp" onclick="shareOnWhatsApp('{{ session('video_url') }}')" title="WhatsApp">
              <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="currentColor"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413Z"/></svg>
            </div>
            <div class="social-icon tiktok" onclick="shareOnTikTok()" title="TikTok">
              <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="currentColor"><path d="M12.525.02c1.31-.02 2.61-.01 3.91-.02.08 1.53.63 3.09 1.75 4.17 1.12 1.11 2.7 1.62 4.24 1.79v4.03c-1.44-.05-2.89-.35-4.2-.97-.57-.26-1.1-.59-1.62-.93-.01 2.92.01 5.84-.02 8.75-.08 1.4-.54 2.79-1.35 3.94-1.31 1.92-3.58 3.17-5.91 3.21-1.43.08-2.86-.31-4.08-1.03-2.02-1.19-3.44-3.37-3.65-5.71-.02-.5-.03-1-.01-1.49.18-1.9 1.12-3.72 2.58-4.96 1.66-1.44 3.98-2.13 6.15-1.72.02 1.48-.04 2.96-.04 4.44-.99-.32-2.15-.23-3.02.37-.63.41-1.11 1.04-1.36 1.75-.21.51-.15 1.07-.14 1.61.24 1.64 1.82 3.02 3.5 2.87 1.12-.01 2.19-.66 2.77-1.61.19-.33.4-.67.41-1.06.1-1.79.06-3.57.07-5.36.01-4.03-.01-8.05.02-12.07z"/></svg>
            </div>
            <div class="social-icon instagram" onclick="shareOnInstagram()" title="Instagram">
              <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="currentColor"><path d="M12 0C8.74 0 8.333.015 7.053.072 5.775.132 4.905.333 4.14.63c-.789.306-1.459.717-2.126 1.384S.935 3.35.63 4.14C.333 4.905.131 5.775.072 7.053.012 8.333 0 8.74 0 12s.015 3.667.072 4.947c.06 1.277.261 2.148.558 2.913.306.788.717 1.459 1.384 2.126.667.666 1.336 1.079 2.126 1.384.766.296 1.636.499 2.913.558C8.333 23.988 8.74 24 12 24s3.667-.015 4.947-.072c1.277-.06 2.148-.262 2.913-.558.788-.306 1.459-.718 2.126-1.384.666-.667 1.079-1.335 1.384-2.126.296-.765.499-1.636.558-2.913.06-1.28.072-1.687.072-4.947s-.015-3.667-.072-4.947c-.06-1.277-.262-2.149-.558-2.913-.306-.789-.718-1.459-1.384-2.126C21.319 1.347 20.651.935 19.86.63c-.765-.297-1.636-.499-2.913-.558C15.667.012 15.26 0 12 0zm0 2.16c3.203 0 3.585.016 4.85.071 1.17.055 1.805.249 2.227.415.562.217.96.477 1.382.896.419.42.679.819.896 1.381.164.422.36 1.057.413 2.227.057 1.266.07 1.646.07 4.85s-.015 3.585-.074 4.85c-.061 1.17-.256 1.805-.421 2.227-.224.562-.479.96-.899 1.382-.419.419-.824.679-1.38.896-.42.164-1.065.36-2.235.413-1.274.057-1.649.07-4.859.07-3.211 0-3.586-.015-4.859-.074-1.171-.061-1.816-.256-2.236-.421-.569-.224-.96-.479-1.379-.899-.421-.419-.69-.824-.9-1.38-.165-.42-.359-1.065-.42-2.235-.045-1.26-.061-1.649-.061-4.844 0-3.196.016-3.586.061-4.861.061-1.17.255-1.814.42-2.234.21-.57.479-.96.9-1.381.419-.419.81-.689 1.379-.898.42-.166 1.051-.361 2.221-.421 1.275-.045 1.65-.06 4.859-.06l.045.03zm0 3.678c-3.405 0-6.162 2.76-6.162 6.162 0 3.405 2.76 6.162 6.162 6.162 3.405 0 6.162-2.76 6.162-6.162 0-3.405-2.76-6.162-6.162-6.162zM12 16c-2.21 0-4-1.79-4-4s1.79-4 4-4 4 1.79 4 4-1.79 4-4 4zm7.846-10.405c0 .795-.646 1.44-1.44 1.44-.795 0-1.44-.646-1.44-1.44 0-.794.646-1.439 1.44-1.439.793-.001 1.44.645 1.44 1.439z"/></svg>
            </div>
            <div class="social-icon snapchat" onclick="shareOnSnapchat()" title="Snapchat">
              <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="currentColor"><path d="M12.206.793c.99 0 4.347.276 5.93 3.821.529 1.193.403 3.219.299 4.847l-.003.06c-.012.18-.022.345-.03.51.075.045.203.09.401.09.3-.016.659-.12 1.033-.301.165-.088.344-.104.464-.104.182 0 .359.029.509.09.45.149.734.479.734.838.015.449-.39.839-1.213 1.168-.089.029-.209.075-.344.119-.45.135-1.139.36-1.333.81-.09.224-.061.524.12.868l.015.015c.06.136 1.526 3.475 4.791 4.014.255.044.435.27.42.509 0 .075-.015.149-.045.225-.24.569-1.273.988-3.146 1.271-.059.091-.12.375-.164.57-.029.179-.074.36-.134.553-.076.271-.27.405-.555.405h-.03c-.135 0-.313-.031-.538-.074-.36-.075-.765-.135-1.273-.135-.3 0-.599.015-.913.074-.6.104-1.123.464-1.723.884-.853.599-1.826 1.288-3.294 1.288-.06 0-.119-.015-.18-.015h-.149c-1.468 0-2.427-.675-3.279-1.288-.599-.42-1.107-.779-1.707-.884-.314-.045-.629-.074-.928-.074-.54 0-.958.089-1.272.149-.211.043-.391.074-.54.074-.374 0-.523-.224-.583-.42-.061-.192-.09-.389-.135-.567-.046-.181-.105-.494-.166-.57-1.918-.222-2.95-.642-3.189-1.226-.031-.063-.052-.15-.055-.225-.015-.243.165-.465.42-.509 3.264-.54 4.73-3.879 4.791-4.02l.016-.029c.18-.345.224-.645.119-.869-.195-.434-.884-.658-1.332-.809-.121-.029-.24-.074-.346-.119-1.107-.435-1.257-.93-1.197-1.273.09-.479.674-.793 1.168-.793.146 0 .27.029.383.074.42.194.789.3 1.104.3.234 0 .384-.06.465-.105l-.046-.569c-.098-1.626-.225-3.651.307-4.837C7.392 1.077 10.739.807 11.727.807l.419-.015h.06z"/></svg>
            </div>
            <div class="social-icon twitter" onclick="shareOnTwitter('{{ session('video_url') }}')" title="X (Twitter)">
              <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="currentColor"><path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-5.214-6.817L4.99 21.75H1.68l7.73-8.835L1.254 2.25H8.08l4.713 6.231zm-1.161 17.52h1.833L7.084 4.126H5.117z"/></svg>
            </div>
          </div>
        </div>
      </div>
    @endif

    <div class="footer">
      Built for the love of Quran 🌙
    </div>
  </div>

  <!-- Progress Overlay -->
  <div id="progress-overlay">
    <div class="loader"></div>
    <div class="progress-status" id="progress-status">يرجى الانتظار جاري إنشاء الفيديو...</div>
  </div>

  <script>
    const ayahFromInput = document.getElementById('ayah_from');
    const ayahToInput = document.getElementById('ayah_to');
    const surahSelect = document.getElementById('surah');

    // Update ayah_to max value based on selected surah
    surahSelect.addEventListener('change', function () {
      const selectedOption = this.options[this.selectedIndex];
      const numberOfAyahs = selectedOption.getAttribute('data-verses');

      if (numberOfAyahs) {
        ayahToInput.setAttribute('max', numberOfAyahs);
        ayahFromInput.setAttribute('max', numberOfAyahs);

        // If current value exceeds max, reset to max
        if (parseInt(ayahToInput.value) > parseInt(numberOfAyahs)) {
          ayahToInput.value = numberOfAyahs;
        }
        if (parseInt(ayahFromInput.value) > parseInt(numberOfAyahs)) {
          ayahFromInput.value = numberOfAyahs;
        }
      }
    });

    // Prevent typing numbers beyond max while typing
    ayahFromInput.addEventListener('input', function() {
      const max = parseInt(this.getAttribute('max'));
      const value = parseInt(this.value);

      if (max && value > max) {
        this.value = max;
      }

      // Prevent negative numbers
      if (value < 1) {
        this.value = 1;
      }
    });

    ayahToInput.addEventListener('input', function() {
      const max = parseInt(this.getAttribute('max'));
      const value = parseInt(this.value);

      if (max && value > max) {
        this.value = max;
      }

      // Prevent negative numbers
      if (value < 1) {
        this.value = 1;
      }
    });

    // Custom Alert Function
    function showCustomAlert(message) {
      const alertDiv = document.createElement('div');
      alertDiv.className = 'custom-alert';
      alertDiv.style.display = 'flex';
      alertDiv.innerHTML = `
        <div class="custom-alert-content">
          <h3>⚠️ تنبيه</h3>
          <p>${message}</p>
          <button class="custom-alert-btn" onclick="this.closest('.custom-alert').remove()">حسناً</button>
        </div>
      `;
      document.body.appendChild(alertDiv);
    }

    // Limit background images to maximum 10
    const backgroundInput = document.getElementById('background');
    if (backgroundInput) {
      backgroundInput.addEventListener('change', function(e) {
        if (this.files.length > 10) {
          showCustomAlert('الحد الأقصى هو 10 صور فقط. يرجى اختيار 10 صور أو أقل.');
          this.value = ''; // Clear the selection
        }
      });
    }

    document.getElementById('generator-form').addEventListener('submit', async function (e) {
      e.preventDefault();

      const overlay = document.getElementById('progress-overlay');
      const progressStatus = document.getElementById('progress-status');

      // Reset UI
      overlay.style.display = 'flex';
      progressStatus.innerText = 'يرجى الانتظار جاري إنشاء الفيديو...';

      const formData = new FormData(this);

      try {
        // Start polling immediately
        let progress = 0;
        const pollInterval = setInterval(async () => {
          try {
            const res = await fetch('{{ route("generator.progress") }}');
            const data = await res.json();

            if (data.progress !== undefined) {
              progress = data.progress;
              if (data.status) progressStatus.innerText = data.status;
            }
          } catch (err) {
            console.error("Polling error:", err);
          }
        }, 1500);

        // Submit form via AJAX
        console.log('Submitting form...'); // Debug log
        const response = await fetch('{{ route("generator.generate") }}', {
          method: 'POST',
          body: formData,
          headers: {
            'X-Requested-With': 'XMLHttpRequest', // Inform Laravel it's AJAX
            'Accept': 'application/json'
          }
        });

        clearInterval(pollInterval); // Stop polling on response

        console.log('Response status:', response.status); // Debug log

        // Check if response is OK
        if (!response.ok) {
          console.error('HTTP error! status:', response.status);
          overlay.style.display = 'none';
          alert('حدث خطأ بالطلب. الرجاء المحاولة مرة أخرى.');
          return;
        }

        const result = await response.json();
        console.log('Server response:', result); // Debug log

        if (result.success && result.video_url) {
          console.log('Video URL:', result.video_url); // Debug log
          progressStatus.innerText = 'تم بنجاح!';

          // Wait a moment then show video
          setTimeout(() => {
            overlay.style.display = 'none';

            // Dynamically insert video preview
            const container = document.querySelector('.container');
            let preview = document.querySelector('.video-preview');
            if (preview) preview.remove();

            const previewHtml = `
                  <div class="video-preview">
                    <h3>Your Generated Reel</h3>
                    <video controls autoplay>
                      <source src="${result.video_url}" type="video/mp4">
                      Your browser does not support the video tag.
                    </video>
                    <p style="margin-top: 10px;">
                      <a href="${result.video_url}" download
                        style="color: var(--primary); font-weight: 600; text-decoration: none;">⬇️ تحميل الفيديو</a>
                    </p>

                    <div class="social-share">
                      <h4>شارك الفيديو على السوشيال ميديا</h4>
                      <div class="social-icons">
                        <div class="social-icon facebook" onclick="shareOnFacebook('${result.video_url}')" title="Facebook">
                          <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="currentColor"><path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/></svg>
                        </div>
                        <div class="social-icon whatsapp" onclick="shareOnWhatsApp('${result.video_url}')" title="WhatsApp">
                          <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="currentColor"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413Z"/></svg>
                        </div>
                        <div class="social-icon tiktok" onclick="shareOnTikTok()" title="TikTok">
                          <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="currentColor"><path d="M12.525.02c1.31-.02 2.61-.01 3.91-.02.08 1.53.63 3.09 1.75 4.17 1.12 1.11 2.7 1.62 4.24 1.79v4.03c-1.44-.05-2.89-.35-4.2-.97-.57-.26-1.1-.59-1.62-.93-.01 2.92.01 5.84-.02 8.75-.08 1.4-.54 2.79-1.35 3.94-1.31 1.92-3.58 3.17-5.91 3.21-1.43.08-2.86-.31-4.08-1.03-2.02-1.19-3.44-3.37-3.65-5.71-.02-.5-.03-1-.01-1.49.18-1.9 1.12-3.72 2.58-4.96 1.66-1.44 3.98-2.13 6.15-1.72.02 1.48-.04 2.96-.04 4.44-.99-.32-2.15-.23-3.02.37-.63.41-1.11 1.04-1.36 1.75-.21.51-.15 1.07-.14 1.61.24 1.64 1.82 3.02 3.5 2.87 1.12-.01 2.19-.66 2.77-1.61.19-.33.4-.67.41-1.06.1-1.79.06-3.57.07-5.36.01-4.03-.01-8.05.02-12.07z"/></svg>
                        </div>
                        <div class="social-icon instagram" onclick="shareOnInstagram()" title="Instagram">
                          <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="currentColor"><path d="M12 0C8.74 0 8.333.015 7.053.072 5.775.132 4.905.333 4.14.63c-.789.306-1.459.717-2.126 1.384S.935 3.35.63 4.14C.333 4.905.131 5.775.072 7.053.012 8.333 0 8.74 0 12s.015 3.667.072 4.947c.06 1.277.261 2.148.558 2.913.306.788.717 1.459 1.384 2.126.667.666 1.336 1.079 2.126 1.384.766.296 1.636.499 2.913.558C8.333 23.988 8.74 24 12 24s3.667-.015 4.947-.072c1.277-.06 2.148-.262 2.913-.558.788-.306 1.459-.718 2.126-1.384.666-.667 1.079-1.335 1.384-2.126.296-.765.499-1.636.558-2.913.06-1.28.072-1.687.072-4.947s-.015-3.667-.072-4.947c-.06-1.277-.262-2.149-.558-2.913-.306-.789-.718-1.459-1.384-2.126C21.319 1.347 20.651.935 19.86.63c-.765-.297-1.636-.499-2.913-.558C15.667.012 15.26 0 12 0zm0 2.16c3.203 0 3.585.016 4.85.071 1.17.055 1.805.249 2.227.415.562.217.96.477 1.382.896.419.42.679.819.896 1.381.164.422.36 1.057.413 2.227.057 1.266.07 1.646.07 4.85s-.015 3.585-.074 4.85c-.061 1.17-.256 1.805-.421 2.227-.224.562-.479.96-.899 1.382-.419.419-.824.679-1.38.896-.42.164-1.065.36-2.235.413-1.274.057-1.649.07-4.859.07-3.211 0-3.586-.015-4.859-.074-1.171-.061-1.816-.256-2.236-.421-.569-.224-.96-.479-1.379-.899-.421-.419-.69-.824-.9-1.38-.165-.42-.359-1.065-.42-2.235-.045-1.26-.061-1.649-.061-4.844 0-3.196.016-3.586.061-4.861.061-1.17.255-1.814.42-2.234.21-.57.479-.96.9-1.381.419-.419.81-.689 1.379-.898.42-.166 1.051-.361 2.221-.421 1.275-.045 1.65-.06 4.859-.06l.045.03zm0 3.678c-3.405 0-6.162 2.76-6.162 6.162 0 3.405 2.76 6.162 6.162 6.162 3.405 0 6.162-2.76 6.162-6.162 0-3.405-2.76-6.162-6.162-6.162zM12 16c-2.21 0-4-1.79-4-4s1.79-4 4-4 4 1.79 4 4-1.79 4-4 4zm7.846-10.405c0 .795-.646 1.44-1.44 1.44-.795 0-1.44-.646-1.44-1.44 0-.794.646-1.439 1.44-1.439.793-.001 1.44.645 1.44 1.439z"/></svg>
                        </div>
                        <div class="social-icon snapchat" onclick="shareOnSnapchat()" title="Snapchat">
                          <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="currentColor"><path d="M12.206.793c.99 0 4.347.276 5.93 3.821.529 1.193.403 3.219.299 4.847l-.003.06c-.012.18-.022.345-.03.51.075.045.203.09.401.09.3-.016.659-.12 1.033-.301.165-.088.344-.104.464-.104.182 0 .359.029.509.09.45.149.734.479.734.838.015.449-.39.839-1.213 1.168-.089.029-.209.075-.344.119-.45.135-1.139.36-1.333.81-.09.224-.061.524.12.868l.015.015c.06.136 1.526 3.475 4.791 4.014.255.044.435.27.42.509 0 .075-.015.149-.045.225-.24.569-1.273.988-3.146 1.271-.059.091-.12.375-.164.57-.029.179-.074.36-.134.553-.076.271-.27.405-.555.405h-.03c-.135 0-.313-.031-.538-.074-.36-.075-.765-.135-1.273-.135-.3 0-.599.015-.913.074-.6.104-1.123.464-1.723.884-.853.599-1.826 1.288-3.294 1.288-.06 0-.119-.015-.18-.015h-.149c-1.468 0-2.427-.675-3.279-1.288-.599-.42-1.107-.779-1.707-.884-.314-.045-.629-.074-.928-.074-.54 0-.958.089-1.272.149-.211.043-.391.074-.54.074-.374 0-.523-.224-.583-.42-.061-.192-.09-.389-.135-.567-.046-.181-.105-.494-.166-.57-1.918-.222-2.95-.642-3.189-1.226-.031-.063-.052-.15-.055-.225-.015-.243.165-.465.42-.509 3.264-.54 4.73-3.879 4.791-4.02l.016-.029c.18-.345.224-.645.119-.869-.195-.434-.884-.658-1.332-.809-.121-.029-.24-.074-.346-.119-1.107-.435-1.257-.93-1.197-1.273.09-.479.674-.793 1.168-.793.146 0 .27.029.383.074.42.194.789.3 1.104.3.234 0 .384-.06.465-.105l-.046-.569c-.098-1.626-.225-3.651.307-4.837C7.392 1.077 10.739.807 11.727.807l.419-.015h.06z"/></svg>
                        </div>
                        <div class="social-icon twitter" onclick="shareOnTwitter('${result.video_url}')" title="X (Twitter)">
                          <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="currentColor"><path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-5.214-6.817L4.99 21.75H1.68l7.73-8.835L1.254 2.25H8.08l4.713 6.231zm-1.161 17.52h1.833L7.084 4.126H5.117z"/></svg>
                        </div>
                      </div>
                    </div>
                  </div>
                `;

            const footer = document.querySelector('.footer');
            footer.insertAdjacentHTML('beforebegin', previewHtml);
            console.log('Video inserted into DOM'); // Debug log
          }, 1000);
        } else {
          console.error('Generation failed:', result); // Debug log
          overlay.style.display = 'none';
          alert('فشل إنشاء الفيديو: ' + (result.message || 'خطأ غير معروف'));
        }

      } catch (error) {
        clearInterval(pollInterval); // Make sure to stop polling
        console.error('Submission error:', error);
        overlay.style.display = 'none';
        alert('حدث خطأ غير متوقع. الرجاء المحاولة مرة أخرى.');
      }
    });

    // Social Media Share Functions
    function shareOnFacebook(videoUrl) {
      const fullUrl = window.location.origin + videoUrl;
      window.open(`https://www.facebook.com/sharer/sharer.php?u=${encodeURIComponent(fullUrl)}`, '_blank', 'width=600,height=400');
    }

    function shareOnWhatsApp(videoUrl) {
      const fullUrl = window.location.origin + videoUrl;
      const text = 'شاهد هذا الفيديو القرآني الرائع!';
      window.open(`https://wa.me/?text=${encodeURIComponent(text + ' ' + fullUrl)}`, '_blank');
    }

    function shareOnTikTok() {
      alert('يرجى تحميل الفيديو ثم رفعه يدوياً على TikTok');
    }

    function shareOnInstagram() {
      alert('يرجى تحميل الفيديو ثم رفعه يدوياً على Instagram');
    }

    function shareOnSnapchat() {
      alert('يرجى تحميل الفيديو ثم رفعه يدوياً على Snapchat');
    }

    function shareOnTwitter(videoUrl) {
      const fullUrl = window.location.origin + videoUrl;
      const text = 'شاهد هذا الفيديو القرآني الرائع!';
      window.open(`https://twitter.com/intent/tweet?text=${encodeURIComponent(text)}&url=${encodeURIComponent(fullUrl)}`, '_blank', 'width=600,height=400');
    }
  </script>
</body>

</html>


