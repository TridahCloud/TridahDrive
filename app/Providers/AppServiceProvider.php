<?php

namespace App\Providers;

use App\Models\Drive;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Fix for open_basedir restrictions - create directories early
        // This must run before Laravel's exception handler checks for vendor views
        // Use a static call to ensure it runs immediately
        static::ensureVendorViewsDirectory();
    }
    
    /**
     * Ensure vendor views directory exists to prevent open_basedir errors
     * This is called statically to ensure it runs as early as possible
     */
    protected static function ensureVendorViewsDirectory(): void
    {
        try {
            // Use base_path() helper which should resolve correctly
            $basePath = base_path();
            $vendorPath = $basePath . '/resources/views/vendor';
            $exceptionPath = $vendorPath . '/laravel-exceptions';
            
            // Use @ to suppress warnings for open_basedir restrictions
            if (!@is_dir($vendorPath)) {
                @mkdir($vendorPath, 0755, true);
            }
            
            if (!@is_dir($exceptionPath)) {
                @mkdir($exceptionPath, 0755, true);
            }
        } catch (\Exception $e) {
            // Silently fail if we can't create directories due to open_basedir
        }
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
