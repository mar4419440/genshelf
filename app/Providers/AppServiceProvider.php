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

    public function boot(): void
    {
        // Load settings into config for global access and performance
        if (!app()->runningInConsole() || app()->runningUnitTests()) {
            try {
                if (\Schema::hasTable('settings')) {
                    $settings = \DB::table('settings')->pluck('value', 'key')->all();
                    config(['settings' => $settings]);
                }
            } catch (\Exception $e) {
                // Settings table might not exist yet during migration
            }
        }
    }
}
