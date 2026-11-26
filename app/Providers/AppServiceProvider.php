<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->fitUploadLimitsToPhp();
    }

    /**
     * Never promise more than PHP will accept. A web request is limited by
     * upload_max_filesize (one file) and post_max_size (everything in the request),
     * so the limits shown on the form and checked by validation are capped to them.
     * Left alone in the console, where PHP's web limits do not apply.
     */
    private function fitUploadLimitsToPhp(): void
    {
        if ($this->app->runningInConsole()) {
            return;
        }

        $perFile = $this->iniToKb(ini_get('upload_max_filesize'));
        $request = $this->iniToKb(ini_get('post_max_size'));
        $want    = (int) config('citation.uploads.max_size_kb');

        $limit = $want;
        foreach ([$perFile, $request] as $phpLimit) {
            if ($phpLimit > 0) {
                $limit = min($limit, $phpLimit);
            }
        }

        config([
            'citation.uploads.max_size_kb'  => $limit,
            // All files in one submission share post_max_size (0 = no limit)
            'citation.uploads.max_total_kb' => $request,
        ]);
    }

    /** "512M", "2G", "800K" or plain bytes, to kilobytes. 0 or -1 means unlimited. */
    private function iniToKb(string|false $value): int
    {
        $value = trim((string) $value);
        if ($value === '' || (int) $value <= 0) {
            return 0;
        }

        $number = (float) $value;

        return (int) floor(match (strtolower(substr($value, -1))) {
            'g'     => $number * 1024 * 1024,
            'm'     => $number * 1024,
            'k'     => $number,
            default => $number / 1024,
        });
    }
}
