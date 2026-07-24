<?php

namespace App\Console\Commands;

use App\Support\PublicMedia;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;

class PrepareMediaStorage extends Command
{
    protected $signature = 'app:prepare-media';

    protected $description = 'Ensure media folders exist and public/storage is linked (required on the server)';

    public function handle(): int
    {
        $disk = Storage::disk(PublicMedia::DISK);
        $root = $disk->path('');

        if (! File::isDirectory($root)) {
            File::makeDirectory($root, 0755, true);
            $this->info("Created: {$root}");
        }

        foreach (PublicMedia::FOLDERS as $folder) {
            $path = $disk->path($folder);
            if (! File::isDirectory($path)) {
                File::makeDirectory($path, 0755, true);
                $this->info("Created folder: {$folder}");
            }

            $keep = $path.DIRECTORY_SEPARATOR.'.gitignore';
            if (! File::exists($keep)) {
                File::put($keep, "*\n!.gitignore\n");
            }
        }

        $link = public_path('storage');

        if (! File::exists($link) && ! is_link($link)) {
            $this->call('storage:link');
        } else {
            $this->line('public/storage link OK.');
        }

        $writable = is_writable($root);
        $this->newLine();
        $this->info('Media storage ready.');
        $this->line('Disk root: '.$root);
        $this->line('Writable: '.($writable ? 'yes' : 'NO — fix permissions (chmod -R 775 storage bootstrap/cache)'));
        $this->line('Public URL base: '.asset('storage'));
        $this->line('Folders: '.implode(', ', PublicMedia::FOLDERS));

        return self::SUCCESS;
    }
}
