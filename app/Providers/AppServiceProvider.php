<?php

namespace App\Providers;

use App\Models\SiteSettings;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\View;
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
        Carbon::setLocale('ru');

        View::share('siteSettings', $this->loadSiteSettings());
    }

    private function loadSiteSettings(): array
    {
        return cache()->remember('site_settings', 300, function () {
            try {
                if (!Schema::hasTable('site_settings')) {
                    return [];
                }
                return SiteSettings::pluck('value', 'key')->toArray();
            } catch (\Exception $e) {
                return [];
            }
        });
    }
}
