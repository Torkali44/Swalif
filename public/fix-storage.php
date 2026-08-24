<?php

/**
 * Namecheap media fixer.
 *
 * Layout:
 *   public_html/          ← this file + images/ + storage/
 *   Swalif/               ← Laravel app
 *
 * Open (use EITHER):
 *   https://swaliif.com/fix-storage.php?key=swalif-fix-2026
 *   https://swaliif.com/fix-storage.php?key=URL_ENCODED_APP_KEY
 *
 * DELETE this file after it works.
 */

declare(strict_types=1);

$webRoot = __DIR__;

$laravelRoot = dirname($webRoot);
if (! is_file($laravelRoot.'/vendor/autoload.php')
    && is_file($webRoot.'/../Swalif/vendor/autoload.php')) {
    $laravelRoot = realpath($webRoot.'/../Swalif') ?: ($webRoot.'/../Swalif');
}

if (! is_file($laravelRoot.'/vendor/autoload.php')) {
    http_response_code(500);
    header('Content-Type: text/plain; charset=utf-8');
    echo "Laravel not found at ../Swalif\nTried: {$laravelRoot}\n";
    exit;
}

require $laravelRoot.'/vendor/autoload.php';

/** @var \Illuminate\Foundation\Application $app */
$app = require $laravelRoot.'/bootstrap/app.php';
$app->usePublicPath($webRoot);

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$given = (string) ($_GET['key'] ?? $_POST['key'] ?? '');
// Query strings turn "+" into space — restore APP_KEY format
$given = str_replace(' ', '+', rawurldecode($given));

$appKey = (string) env('APP_KEY', '');
$simpleKey = (string) env('MEDIA_FIX_KEY', '');

$allowed = false;
if ($given !== '' && $simpleKey !== '' && hash_equals($simpleKey, $given)) {
    $allowed = true;
}
if (! $allowed && $appKey !== '' && $given !== '' && hash_equals($appKey, $given)) {
    $allowed = true;
}

if (! $allowed) {
    http_response_code(403);
    header('Content-Type: text/html; charset=utf-8');
    echo '<!doctype html><html lang="ar" dir="rtl"><meta charset="utf-8"><body style="font-family:Tahoma;padding:24px">';
    echo '<h2>Forbidden</h2>';
    echo '<p>ضع MEDIA_FIX_KEY في .env ثم افتح:</p>';
    echo '<p><code>/fix-storage.php?key=YOUR_MEDIA_FIX_KEY</code></p>';
    echo '<p>أو مرّر APP_KEY (مع URL-encode لأن + تتحول لمسافة).</p>';
    echo '<p><b>احذف هذا الملف من السيرفر بعد الإصلاح.</b></p>';
    echo '</body></html>';
    exit;
}

header('Content-Type: text/html; charset=utf-8');

$publicStorage = $webRoot.'/storage';
$legacy = $laravelRoot.'/storage/app/public';
$legacyPublic = $laravelRoot.'/public/storage';
$images = $webRoot.'/images';
$lines = [];

$ok = static fn (string $m) => $lines[] = ['ok', $m];
$bad = static fn (string $m) => $lines[] = ['bad', $m];
$info = static fn (string $m) => $lines[] = ['info', $m];

$info('Laravel: '.$laravelRoot);
$info('Web root: '.$webRoot);
$info('public_path(): '.public_path());
$info('Uploads go to: '.public_path('storage'));

if (is_link($publicStorage) && ! file_exists($publicStorage)) {
    @unlink($publicStorage);
    $ok('Removed broken storage symlink');
}

if (! is_dir($publicStorage)) {
    if (@mkdir($publicStorage, 0755, true)) {
        $ok('Created public_html/storage');
    } else {
        $bad('Cannot create public_html/storage');
    }
} else {
    $ok('public_html/storage exists');
}

foreach (['categories', 'classifications', 'questions', 'questions/videos', 'questions/audio', 'avatars'] as $folder) {
    $path = $publicStorage.'/'.$folder;
    if (! is_dir($path)) {
        @mkdir($path, 0755, true);
        $ok('Created storage/'.$folder);
    }
}

@file_put_contents($publicStorage.'/.htaccess', "Options -Indexes\n");

$copyTree = static function (string $from, string $to): int {
    if (! is_dir($from)) {
        return 0;
    }
    $fromReal = realpath($from);
    $toReal = realpath($to) ?: $to;
    if ($fromReal && $toReal && $fromReal === $toReal) {
        return 0;
    }

    $copied = 0;
    $iterator = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($from, FilesystemIterator::SKIP_DOTS)
    );

    foreach ($iterator as $file) {
        if (! $file->isFile()) {
            continue;
        }
        $relative = ltrim(str_replace('\\', '/', substr($file->getPathname(), strlen(rtrim($from, '/\\')))), '/');
        if ($relative === '' || str_ends_with($relative, '.gitignore')) {
            continue;
        }
        $target = $to.'/'.$relative;
        if (is_file($target)) {
            continue;
        }
        if (! is_dir(dirname($target))) {
            @mkdir(dirname($target), 0755, true);
        }
        if (@copy($file->getPathname(), $target)) {
            $copied++;
        }
    }

    return $copied;
};

$n1 = $copyTree($legacy, $publicStorage);
$ok("Copied {$n1} file(s) from Swalif/storage/app/public → public_html/storage");

if (is_dir($legacyPublic)) {
    $n2 = $copyTree($legacyPublic, $publicStorage);
    $ok("Copied {$n2} file(s) from Swalif/public/storage → public_html/storage");
}

if (! is_writable($publicStorage)) {
    $bad('public_html/storage NOT writable');
} else {
    $ok('public_html/storage writable');
}

if (is_dir($images)) {
    $ok('public_html/images exists');
} else {
    $bad('public_html/images missing');
}

$probe = $publicStorage.'/questions/.write_probe';
if (@file_put_contents($probe, 'ok') !== false) {
    @unlink($probe);
    $ok('Write probe OK');
} else {
    $bad('Write probe FAILED');
}

$sample = null;
$qDir = $publicStorage.'/questions';
if (is_dir($qDir)) {
    foreach (scandir($qDir) ?: [] as $f) {
        if ($f === '.' || $f === '..' || ! is_file($qDir.'/'.$f)) {
            continue;
        }
        $sample = 'https://swaliif.com/storage/questions/'.$f;
        break;
    }
}

if ($sample) {
    $ok('Sample image URL: '.$sample);
} else {
    $bad('No files yet in public_html/storage/questions — re-upload a question image after this fix');
}

try {
    Illuminate\Support\Facades\Artisan::call('config:clear');
    Illuminate\Support\Facades\Artisan::call('cache:clear');
    Illuminate\Support\Facades\Artisan::call('view:clear');
    $ok('Caches cleared');
} catch (Throwable $e) {
    $info('Cache: '.$e->getMessage());
}

try {
    Illuminate\Support\Facades\Artisan::call('app:prepare-media');
    $ok('prepare-media: '.trim(Illuminate\Support\Facades\Artisan::output()));
} catch (Throwable $e) {
    $info('prepare-media: '.$e->getMessage());
}

?>
<!doctype html>
<html lang="ar" dir="rtl">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>إصلاح التخزين</title>
  <style>
    body{font-family:Tahoma,sans-serif;background:#F7F1E6;padding:24px;color:#0B1220}
    .card{max-width:860px;margin:auto;background:#fff;border-radius:16px;padding:22px;box-shadow:0 10px 30px rgba(0,0,0,.08)}
    li{list-style:none;margin:8px 0;padding:10px;border-radius:10px;word-break:break-all}
    .ok{background:#ECFDF5;color:#065F46}.bad{background:#FEF2F2;color:#991B1B}.info{background:#EFF6FF;color:#1E3A8A}
    .warn{margin-top:16px;padding:12px;background:#FFF7ED;color:#9A3412;font-weight:700;border-radius:12px}
    ul{padding:0}
  </style>
</head>
<body>
<div class="card">
  <h1>إصلاح public_html/storage</h1>
  <ul>
    <?php foreach ($lines as [$t, $m]): ?>
      <li class="<?= htmlspecialchars($t) ?>"><?= htmlspecialchars($m) ?></li>
    <?php endforeach; ?>
  </ul>
  <div class="warn">
    احذف الملف fix-storage.php الآن من public_html بعد ما تتأكد إن رابط الصورة اشتغل.
  </div>
</div>
</body>
</html>
