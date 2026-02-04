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

    .footer {
      margin-top: 40px;
      text-align: center;
      font-size: 0.875rem;
      color: #64748b;
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

    <form action="{{ route('generator.generate') }}" method="POST">
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
            <option value="{{ $surah['number'] }}">{{ $surah['number'] }}. {{ $surah['name'] }}
              ({{ $surah['englishName'] }})</option>
          @endforeach
        </select>
      </div>

      <div class="grid">
        <div class="form-group">
          <label for="ayah_from">من الآية (From Ayah)</label>
          <input type="number" name="ayah_from" id="ayah_from" min="1" value="1" required>
        </div>
        <div class="form-group">
          <label for="ayah_to">إلى الآية (To Ayah)</label>
          <input type="number" name="ayah_to" id="ayah_to" min="1" value="3" required>
        </div>
      </div>

      <div class="form-group">
        <label for="duration">أقصى مدة (Max Duration - Sec)</label>
        <input type="number" name="duration" id="duration" min="5" max="60" value="30">
      </div>

      <button type="submit">Generate Quran Reel ✨</button>
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
            style="color: var(--primary); font-weight: 600; text-decoration: none;">⬇️ Download Video</a>
        </p>
      </div>
    @endif

    <div class="footer">
      Built for the love of Quran 🌙
    </div>
  </div>
</body>

</html>