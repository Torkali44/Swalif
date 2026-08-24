<?php

namespace App\Console\Commands;

use App\Support\PublicMedia;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class PrepareMediaStorage extends Command
{
    protected $signature = 'app:prepare-media';

    protected $description = 'Ensure public/storage exists (no symlink required) and migrate legacy files';

    public function handle(): int
    {
        $publicStorage = PublicMedia::rootPath();
        $legacyRoot = PublicMedia::legacyRootPath();

        // If public/storage is a broken link, remove it
        if (is_link($publicStorage) && ! File::exists($publicStorage)) {
            @unlink($publicStorage);
            $this->warn('Removed broken public/storage symlink.');
        }

        // Prefer a real directory (Namecheap-safe). If a valid symlink already
        // points at storage/app/public, keep it and still ensure folders.
        if (! File::exists($publicStorage) && ! is_link($publicStorage)) {
            File::makeDirectory($publicStorage, 0755, true);
            $this->info("Created: {$publicStorage}");
        } elseif (is_link($publicStorage)) {
            $this->line('public/storage is a symlink (OK if target is writable).');
        }

        // Migrate any files still under storage/app/public
        if (File::isDirectory($legacyRoot) && realpath($legacyRoot) !== realpath($publicStorage)) {
            $this->migrateLegacy($legacyRoot, $publicStorage);
        }

        foreach (PublicMedia::FOLDERS as $folder) {
            $path = $publicStorage.DIRECTORY_SEPARATOR.str_replace('/', DIRECTORY_SEPARATOR, $folder);
            if (! File::isDirectory($path)) {
                File::makeDirectory($path, 0755, true);
                $this->info("Created folder: {$folder}");
            }
        }

        // Prevent directory listing / accidental git of uploads
        $htaccess = $publicStorage.DIRECTORY_SEPARATOR.'.htaccess';
        if (! File::exists($htaccess)) {
            File::put($htaccess, "Options -Indexes\n");
        }

        $gitkeep = $publicStorage.DIRECTORY_SEPARATOR.'.gitignore';
        if (! File::exists($gitkeep)) {
            File::put($gitkeep, "*\n!.gitignore\n!.htaccess\n");
        }

        $writable = is_writable($publicStorage);

        $this->newLine();
        $this->info('Media storage ready (Namecheap-safe).');
        $this->line('App base: '.base_path());
        $this->line('public_path(): '.public_path());
        $this->line('Disk root: '.$publicStorage);
        $this->line('Writable: '.($writable ? 'yes' : 'NO — chmod -R 775 public_html/storage'));
        $this->line('Public URL base: '.rtrim((string) config('app.url'), '/').'/storage');
        $this->line('Folders: '.implode(', ', PublicMedia::FOLDERS));
        $this->newLine();
        $this->comment('Namecheap: web root = public_html, app = Swalif/. Uploads must live in public_html/storage');

        return self::SUCCESS;
    }

    private function migrateLegacy(string $from, string $to): void
    {
        $copied = 0;

        foreach (File::allFiles($from) as $file) {
            $relative = ltrim(str_replace('\\', '/', substr($file->getPathname(), strlen(rtrim($from, DIRECTORY_SEPARATOR)))), '/');
            if ($relative === '' || str_ends_with($relative, '.gitignore')) {
                continue;
            }

            $target = $to.DIRECTORY_SEPARATOR.str_replace('/', DIRECTORY_SEPARATOR, $relative);
            if (File::exists($target)) {
                continue;
            }

            File::ensureDirectoryExists(dirname($target));
            File::copy($file->getPathname(), $target);
            $copied++;
        }

        if ($copied > 0) {
            $this->info("Migrated {$copied} file(s) from storage/app/public → public/storage");
        }
    }
}
