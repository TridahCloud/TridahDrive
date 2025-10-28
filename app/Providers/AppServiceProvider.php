<?php

namespace App\Providers;

use App\Models\Drive;
use Illuminate\Support\Facades\Route;
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
        // Custom route model binding for drives
        Route::bind('drive', function ($value) {
            $drive = Drive::where('id', $value)->firstOrFail();
            
            // Check if user has access
            if (auth()->check() && !$drive->hasMember(auth()->user())) {
                abort(403, 'You do not have access to this drive.');
            }
            
            return $drive;
        });
    }
}
