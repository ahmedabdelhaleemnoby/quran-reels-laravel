<?php
header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>فحص متطلبات السيرفر</title>
    <style>
        body { font-family: Arial, sans-serif; padding: 20px; background: #f5f5f5; }
        .container { max-width: 800px; margin: 0 auto; background: white; padding: 30px; border-radius: 10px; }
        h1 { color: #064e3b; }
        .check { padding: 15px; margin: 10px 0; border-radius: 5px; }
        .success { background: #d1fae5; border-left: 4px solid #10b981; }
        .error { background: #fee2e2; border-left: 4px solid #ef4444; }
        .warning { background: #fef3c7; border-left: 4px solid #f59e0b; }
        code { background: #f1f5f9; padding: 2px 6px; border-radius: 3px; }
        pre { background: #1e293b; color: #e2e8f0; padding: 15px; border-radius: 5px; overflow-x: auto; }
    </style>
</head>
<body>
    <div class="container">
        <h1>🔍 فحص متطلبات السيرفر</h1>
        
        <h2>1. PHP Version</h2>
        <div class="check <?php echo version_compare(PHP_VERSION, '8.2.0', '>=') ? 'success' : 'error'; ?>">
            <strong>PHP Version:</strong> <?php echo PHP_VERSION; ?>
            <?php if (version_compare(PHP_VERSION, '8.2.0', '>=')): ?>
                ✅ مناسب (يحتاج 8.2+)
            <?php else: ?>
                ❌ غير مناسب - يحتاج PHP 8.2 أو أعلى
            <?php endif; ?>
        </div>

        <h2>2. PHP Extensions</h2>
        <?php
        $required_extensions = [
            'mbstring' => 'ضروري لمعالجة النصوص العربية',
            'pdo' => 'ضروري لقاعدة البيانات',
            'pdo_mysql' => 'ضروري لـ MySQL',
            'curl' => 'ضروري للاتصال بالـ APIs',
            'zip' => 'ضروري لـ Composer',
            'gd' => 'مفيد لمعالجة الصور',
            'fileinfo' => 'ضروري لرفع الملفات',
            'xml' => 'ضروري لـ Laravel',
        ];
        
        foreach ($required_extensions as $ext => $desc) {
            $loaded = extension_loaded($ext);
            echo '<div class="check ' . ($loaded ? 'success' : 'error') . '">';
            echo '<strong>' . $ext . '</strong>: ' . ($loaded ? '✅ مفعّل' : '❌ غير مفعّل');
            echo '<br><small>' . $desc . '</small>';
            echo '</div>';
        }
        ?>

        <h2>3. FFmpeg (لتوليد الفيديو)</h2>
        <?php
        $ffmpeg_paths = ['ffmpeg', '/usr/bin/ffmpeg', '/usr/local/bin/ffmpeg'];
        $ffmpeg_found = false;
        $ffmpeg_path = '';
        
        foreach ($ffmpeg_paths as $path) {
            $output = shell_exec("which $path 2>&1");
            if (!empty($output) && strpos($output, 'not found') === false) {
                $ffmpeg_found = true;
                $ffmpeg_path = trim($output);
                break;
            }
        }
        
        if (!$ffmpeg_found) {
            exec('ffmpeg -version 2>&1', $output, $return_var);
            if ($return_var === 0) {
                $ffmpeg_found = true;
                $ffmpeg_path = 'ffmpeg (in PATH)';
            }
        }
        ?>
        <div class="check <?php echo $ffmpeg_found ? 'success' : 'error'; ?>">
            <strong>FFmpeg:</strong> <?php echo $ffmpeg_found ? '✅ موجود' : '❌ غير موجود'; ?>
            <?php if ($ffmpeg_found): ?>
                <br><code><?php echo $ffmpeg_path; ?></code>
                <pre><?php 
                    $version = shell_exec('ffmpeg -version 2>&1 | head -n 1');
                    echo htmlspecialchars($version);
                ?></pre>
            <?php else: ?>
                <br><strong>⚠️ FFmpeg غير مثبت - لن يعمل توليد الفيديو!</strong>
                <br><small>تواصل مع الدعم الفني لتثبيت FFmpeg</small>
            <?php endif; ?>
        </div>

        <h2>4. ImageMagick (لمعالجة الصور)</h2>
        <?php
        $magick_found = false;
        $magick_path = '';
        
        exec('convert -version 2>&1', $output, $return_var);
        if ($return_var === 0) {
            $magick_found = true;
            $magick_path = 'convert (ImageMagick)';
        }
        ?>
        <div class="check <?php echo $magick_found ? 'success' : 'warning'; ?>">
            <strong>ImageMagick:</strong> <?php echo $magick_found ? '✅ موجود' : '⚠️ غير موجود'; ?>
            <?php if ($magick_found): ?>
                <pre><?php echo htmlspecialchars(implode("\n", array_slice($output, 0, 3))); ?></pre>
            <?php else: ?>
                <br><small>اختياري - يستخدم GD كبديل</small>
            <?php endif; ?>
        </div>

        <h2>5. Storage Permissions</h2>
        <?php
        $storage_path = __DIR__ . '/../storage';
        $writable = is_writable($storage_path);
        ?>
        <div class="check <?php echo $writable ? 'success' : 'error'; ?>">
            <strong>Storage Directory:</strong> <?php echo $writable ? '✅ قابل للكتابة' : '❌ غير قابل للكتابة'; ?>
            <br><code><?php echo $storage_path; ?></code>
            <?php if (!$writable): ?>
                <br><strong>⚠️ قم بتغيير الصلاحيات:</strong>
                <pre>chmod -R 775 storage bootstrap/cache</pre>
            <?php endif; ?>
        </div>

        <h2>6. .env File</h2>
        <?php
        $env_exists = file_exists(__DIR__ . '/../.env');
        ?>
        <div class="check <?php echo $env_exists ? 'success' : 'error'; ?>">
            <strong>.env File:</strong> <?php echo $env_exists ? '✅ موجود' : '❌ غير موجود'; ?>
            <?php if (!$env_exists): ?>
                <br><strong>⚠️ انسخ .env.example إلى .env</strong>
            <?php endif; ?>
        </div>

        <h2>📋 الخلاصة</h2>
        <?php if (!$ffmpeg_found): ?>
            <div class="check error">
                <strong>❌ المشكلة الرئيسية:</strong> FFmpeg غير مثبت على السيرفر.
                <br><br>
                <strong>الحل:</strong>
                <ol>
                    <li>تواصل مع الدعم الفني للاستضافة</li>
                    <li>اطلب منهم تثبيت FFmpeg</li>
                    <li>أو انقل الموقع لاستضافة تدعم FFmpeg (مثل VPS)</li>
                </ol>
            </div>
        <?php elseif (!extension_loaded('mbstring')): ?>
            <div class="check error">
                <strong>❌ المشكلة:</strong> mbstring extension غير مفعّل.
                <br><strong>الحل:</strong> فعّله من cPanel > Select PHP Version > Extensions
            </div>
        <?php else: ?>
            <div class="check success">
                <strong>✅ السيرفر جاهز!</strong> جميع المتطلبات متوفرة.
            </div>
        <?php endif; ?>
    </div>
</body>
</html>
