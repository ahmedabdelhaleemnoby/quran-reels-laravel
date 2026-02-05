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
        <label for="reciter">?????? (Reciter)</label>
        <select name="reciter" id="reciter" required>
          <option value="">Choose a reciter...</option>
          @foreach($reciters as $reciter)
            <option value="{{ $reciter['identifier'] }}">{{ $reciter['name'] }} ({{ $reciter['englishName'] }})</option>
          @endforeach
        </select>
      </div>

      <div class="form-group">
        <label for="surah">?????? (Surah)</label>
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
          <label for="ayah_from">?? ????? (From Ayah)</label>
          <input type="number" name="ayah_from" id="ayah_from" min="1" max="286" value="1" required>
        </div>
        <div class="form-group">
          <label for="ayah_to">??? ????? (To Ayah)</label>
          <input type="number" name="ayah_to" id="ayah_to" min="1" max="286" value="1" required>
        </div>
      </div>

      <div class="form-group">
        <label for="duration">???? ??? (Max Duration - Sec)</label>
        <input type="number" name="duration" id="duration" min="5" max="60" value="30">
      </div>

      <div class="form-group">
        <label for="background">????? ????? (Custom Background - Image/Video)</label>
        <input type="file" name="background" id="background" accept="image/*,video/*">
        <div class="upload-hint">Upload an image for "Smart Movement" or a video background. Max: 50MB.</div>
      </div>


      <div class="form-group">
        <label style="display: flex; align-items: center; gap: 10px; cursor: pointer;">
          <input type="checkbox" name="no_text_overlay" id="no_text_overlay" value="1" style="width: auto; cursor: pointer;">
          <span>????? ???? ????? (Audio & Background Only)</span>
        </label>
        <div class="upload-hint">???? ??? ?????? ?????? ????? ?????? ???????? ??? ???? ?? ??????</div>
      </div>
      <button type="submit">Generate Quran Reel ?</button>
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
            style="color: var(--primary); font-weight: 600; text-decoration: none;">?? Download Video</a>
        </p>
      </div>
    @endif

    <div class="footer">
      Built for the love of Quran ??
    </div>
  </div>

  <!-- Progress Overlay -->
  <div id="progress-overlay">
    <div class="loader"></div>
    <div class="progress-status" id="progress-status">???? ???????? ???? ????? ???????...</div>
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

    document.getElementById('generator-form').addEventListener('submit', async function (e) {
      e.preventDefault();

      const overlay = document.getElementById('progress-overlay');
      const progressStatus = document.getElementById('progress-status');

      // Reset UI
      overlay.style.display = 'flex';
      progressStatus.innerText = '???? ???????? ???? ????? ???????...';

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
          alert('??? ?? ??????. ?????? ???????? ??? ????.');
          return;
        }

        const result = await response.json();
        console.log('Server response:', result); // Debug log

        if (result.success && result.video_url) {
          console.log('Video URL:', result.video_url); // Debug log
          progressStatus.innerText = '?? ????????!';

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
                        style="color: var(--primary); font-weight: 600; text-decoration: none;">?? Download Video</a>
                    </p>
                  </div>
                `;

            const footer = document.querySelector('.footer');
            footer.insertAdjacentHTML('beforebegin', previewHtml);
            console.log('Video inserted into DOM'); // Debug log
          }, 1000);
        } else {
          console.error('Generation failed:', result); // Debug log
          overlay.style.display = 'none';
          alert('??? ????? ???????: ' + (result.message || '??? ??? ?????'));
        }

      } catch (error) {
        clearInterval(pollInterval); // Make sure to stop polling
        console.error('Submission error:', error);
        overlay.style.display = 'none';
        alert('??? ??? ??? ?????. ?????? ???????? ??? ????.');
      }
    });
  </script>
</body>

</html>


