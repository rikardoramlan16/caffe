<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Schema;

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
        if (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https') {
            \Illuminate\Support\Facades\URL::forceScheme('https');
        }

        Schema::defaultStringLength(191);

        view()->composer('*', function ($view) {
            try {
                if (\Illuminate\Support\Facades\Schema::hasTable('settings')) {
                    $appLogo = \App\Models\Setting::where('key', 'app_logo')->first()?->value;
                    $appName = \App\Models\Setting::where('key', 'app_name')->first()?->value;
                    $companyPhone = \App\Models\Setting::where('key', 'company_phone')->first()?->value;
                    $companyAddress = \App\Models\Setting::where('key', 'company_address')->first()?->value;
                    $serviceFee = \App\Models\Setting::where('key', 'service_fee')->first()?->value ?? 0;
                    $view->with('appLogo', $appLogo);
                    $view->with('appName', $appName);
                    $view->with('companyPhone', $companyPhone);
                    $view->with('companyAddress', $companyAddress);
                    $view->with('serviceFee', intval($serviceFee));
                }
                $view->with('authUser', session('auth_user'));
            } catch (\Exception $e) {
                // Prevent crash during migrations/seeding
            }
        });
    }
}
