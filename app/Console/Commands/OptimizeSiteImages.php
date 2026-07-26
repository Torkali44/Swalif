<?php

namespace App\Console\Commands;

use App\Models\Category;
use App\Models\Classification;
use App\Models\Question;
use App\Models\User;
use App\Support\PublicMedia;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class OptimizeSiteImages extends Command
{
    protected $signature = 'app:optimize-images {--only=all : all|site|storage}';

    protected $description = 'Re-encode site/upload images as small baseline JPEGs (stops band-by-band loading)';

    public function handle(): int
    {
        if (! function_exists('imagecreatefromstring') || ! function_exists('imagejpeg')) {
            $this->error('PHP GD with JPEG support is required.');

            return self::FAILURE;
        }

        $only = (string) $this->option('only');
        $done = 0;

        if (in_array($only, ['all', 'site'], true)) {
            $done += $this->optimizeSiteAssets();
        }

        if (in_array($only, ['all', 'storage'], true)) {
            $done += $this->optimizeStorageUploads();
        }

        $this->newLine();
        $this->info("Optimized {$done} image(s).");
        $this->comment('Upload to server: public/images optimized files + public/storage (Namecheap: public_html/images & public_html/storage)');

        return self::SUCCESS;
    }

    private function optimizeSiteAssets(): int
    {
        $map = [
            'logo.png' => ['logo-nav.jpg', 220, 82],
            'logo.jpg' => ['logo-nav.jpg', 220, 82],
            'hero-character-custom.png' => ['hero-light.jpg', 720, 78],
            'hero-character-dark.png' => ['hero-dark.jpg', 720, 78],
            'faq-bubbles.png' => ['faq-bubbles.jpg', 640, 78],
            'game-controller.png' => ['game-controller.jpg', 480, 78],
            'gift-box.png' => ['gift-box.jpg', 480, 78],
        ];

        $dir = public_path('images');
        $count = 0;

        foreach ($map as $source => [$dest, $maxWidth, $quality]) {
            $from = $dir.DIRECTORY_SEPARATOR.$source;
            if (! is_file($from)) {
                continue;
            }
            $to = $dir.DIRECTORY_SEPARATOR.$dest;
            $bg = str_contains($dest, 'dark') ? [11, 16, 32] : [255, 255, 255];
            if ($this->encodeFile($from, $to, $maxWidth, $quality, $bg)) {
                $kb = round((filesize($to) ?: 0) / 1024, 1);
                $this->line("site: {$source} → {$dest} ({$kb} KB)");
                $count++;
            }
        }

        return $count;
    }

    private function optimizeStorageUploads(): int
    {
        $root = PublicMedia::rootPath();
        if (! is_dir($root)) {
            $this->warn('Storage root missing: '.$root);

            return 0;
        }

        $count = 0;

        foreach (File::allFiles($root) as $file) {
            $ext = strtolower($file->getExtension());
            if (! in_array($ext, ['jpg', 'jpeg', 'png', 'webp'], true)) {
                continue;
            }

            if ($file->getSize() < 30 * 1024) {
                continue;
            }

            $path = $file->getPathname();
            $relative = ltrim(str_replace('\\', '/', substr($path, strlen(rtrim($root, '/\\')))), '/');
            $maxWidth = str_contains($relative, 'questions') ? 1200 : 900;

            $tmp = $path.'.opt.jpg';
            if (! $this->encodeFile($path, $tmp, $maxWidth, 78)) {
                continue;
            }

            $newSize = filesize($tmp) ?: 0;
            $oldSize = $file->getSize();
            if ($newSize < 1 || $newSize >= $oldSize * 0.98) {
                @unlink($tmp);
                continue;
            }

            $newRelative = preg_replace('/\.(png|webp|jpeg|jpg)$/i', '.jpg', $relative) ?: ($relative.'.jpg');
            $target = $root.DIRECTORY_SEPARATOR.str_replace('/', DIRECTORY_SEPARATOR, $newRelative);

            if ($target !== $path && is_file($path)) {
                @unlink($path);
            }
            File::ensureDirectoryExists(dirname($target));
            @rename($tmp, $target);

            if ($newRelative !== $relative) {
                $this->retargetDbPath($relative, $newRelative);
            }

            $this->line('storage: '.$relative.' → '.$newRelative.' ('.round($oldSize / 1024).'→'.round($newSize / 1024).'KB)');
            $count++;
        }

        return $count;
    }

    private function retargetDbPath(string $old, string $new): void
    {
        Category::query()->where('image', $old)->update(['image' => $new]);
        Classification::query()->where('image', $old)->update(['image' => $new]);
        Question::query()->where('image', $old)->update(['image' => $new]);
        Question::query()->where('answer_image', $old)->update(['answer_image' => $new]);
        User::query()->where('avatar', $old)->update(['avatar' => $new]);
    }

    /**
     * @param  array{0:int,1:int,2:int}  $bg
     */
    private function encodeFile(string $from, string $to, int $maxWidth, int $quality, array $bg = [255, 255, 255]): bool
    {
        $binary = @file_get_contents($from);
        if ($binary === false || $binary === '') {
            return false;
        }

        $source = @imagecreatefromstring($binary);
        if ($source === false) {
            return false;
        }

        $width = imagesx($source);
        $height = imagesy($source);
        if ($width < 1 || $height < 1) {
            imagedestroy($source);

            return false;
        }

        $newWidth = $width > $maxWidth ? $maxWidth : $width;
        $newHeight = (int) max(1, round($height * ($newWidth / $width)));

        $canvas = imagecreatetruecolor($newWidth, $newHeight);
        if ($canvas === false) {
            imagedestroy($source);

            return false;
        }

        $fill = imagecolorallocate($canvas, $bg[0], $bg[1], $bg[2]);
        imagefilledrectangle($canvas, 0, 0, $newWidth, $newHeight, $fill);
        imagecopyresampled($canvas, $source, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);
        imagedestroy($source);

        imageinterlace($canvas, false); // baseline JPEG — full image paints at once
        $ok = imagejpeg($canvas, $to, $quality);
        imagedestroy($canvas);

        return (bool) $ok;
    }
}
