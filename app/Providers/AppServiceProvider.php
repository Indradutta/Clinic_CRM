<?php

namespace App\Providers;

use App\Models\Setting;
use Illuminate\Contracts\View\View as ViewContract;
use Illuminate\Support\Facades\Gate;
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
        Gate::before(function ($user, $ability) {
            if (method_exists($user, 'hasPermission') && $user->hasPermission($ability)) {
                return true;
            }
        });

        View::composer('layouts.app', function (ViewContract $view): void {
            $user = auth()->user();

            $user?->loadMissing('permissions');

            $view->with('profilePhoto', Setting::get('doctor_profile_photo'));
            $view->with('headerNotifications', $user?->notifications()->latest()->take(6)->get() ?? collect());
            $view->with('unreadNotificationCount', $user?->unreadNotifications()->count() ?? 0);
            $view->with('readNotificationCount', $user?->readNotifications()->count() ?? 0);
        });
    }
}
