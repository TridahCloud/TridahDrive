<?php

namespace App\Providers;

use App\Models\Drive;
use App\Models\PayrollEntry;
use App\Models\Person;
use App\Models\Schedule;
use App\Models\TimeLog;
use App\Models\DriveRole;
use App\Models\DriveRoleAssignment;
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
        // Load helper functions explicitly to ensure they're available
        // This ensures helpers work even if composer autoload hasn't been regenerated
        $helpersPath = app_path('helpers.php');
        if (file_exists($helpersPath)) {
            require_once $helpersPath;
        }

        // Custom route model binding for drives
        Route::bind('drive', function ($value) {
            $drive = Drive::where('id', $value)->firstOrFail();
            
            // Check if user has access
            if (auth()->check() && !$drive->hasMember(auth()->user())) {
                abort(403, 'You do not have access to this drive.');
            }
            
            return $drive;
        });

        // Custom route model binding for payroll (resource route uses 'payroll' but model is PayrollEntry)
        Route::bind('payroll', function ($value) {
            return PayrollEntry::findOrFail($value);
        });

        // Custom route model binding for payrollEntry (used in sync route)
        Route::bind('payrollEntry', function ($value) {
            return PayrollEntry::findOrFail($value);
        });

        // Custom route model binding for person (used in time log print report)
        Route::bind('person', function ($value) {
            return Person::findOrFail($value);
        });

        // Custom route model binding for schedule (in user self-service routes)
        Route::bind('schedule', function ($value) {
            return Schedule::findOrFail($value);
        });

        // Custom route model binding for timeLog (in user self-service routes)
        Route::bind('timeLog', function ($value) {
            return TimeLog::findOrFail($value);
        });

        // Custom route model binding for role (in drive roles routes)
        Route::bind('role', function ($value) {
            return DriveRole::findOrFail($value);
        });

        // Custom route model binding for assignment (in drive roles routes)
        Route::bind('assignment', function ($value) {
            return DriveRoleAssignment::findOrFail($value);
        });
    }
}
