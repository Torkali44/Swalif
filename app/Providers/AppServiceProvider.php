<?php

namespace App\Providers;

use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Namecheap:
        //   /home/USER/Swalif
        //   /home/USER/public_html   ← document root
        $configured = env('PUBLIC_PATH');
        if (is_string($configured) && $configured !== '' && is_dir($configured)) {
            $this->app->usePublicPath($configured);

            return;
        }

        $siblingPublicHtml = dirname($this->app->basePath()).DIRECTORY_SEPARATOR.'public_html';
        if (is_dir($siblingPublicHtml) && (
            is_file($siblingPublicHtml.DIRECTORY_SEPARATOR.'index.php')
            || is_dir($siblingPublicHtml.DIRECTORY_SEPARATOR.'images')
        )) {
            $this->app->usePublicPath($siblingPublicHtml);
        }
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Paginator::defaultView('vendor.pagination.swalif');
        Paginator::defaultSimpleView('vendor.pagination.swalif-simple');

        // On shared hosting a wrong APP_URL breaks asset()/logos.
        // Prefer the live request host so images always load on the current domain.
        if (! $this->app->runningInConsole() && $this->app->bound('request')) {
            $request = request();
            if ($request) {
                $root = $request->getSchemeAndHttpHost().$request->getBasePath();
                URL::forceRootUrl(rtrim($root, '/'));

                if ($request->isSecure() || str_starts_with((string) config('app.url'), 'https://')) {
                    URL::forceScheme('https');
                }
            }
        }
    }
}
