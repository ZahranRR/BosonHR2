<?php

namespace App\Providers;

use App\Models\CompanyName;
use App\Models\Role;
use Illuminate\Support\Facades\View;
use Closure;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\URL;
use Spatie\Permission\Models\Role as SpatieRole;
use App\Models\Attandance;
use App\Observers\AttandanceObserver;
use App\Models\Offrequest;
use App\Models\Overtime;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function handle($request, Closure $next)
    {
        //
        app()->bind('role', function () {
            return new \App\Models\Role();
        });
        // Binding model Role dari Spatie ke model Role Anda
        // $this->app->bind(\Spatie\Permission\Models\Role::class, \App\Models\Role::class);
    }

    public function boot(): void
    {
        View::composer('*', function ($view) {
            $companynames = CompanyName::first(); // Ambil setting pertama (jika hanya satu row)
            $view->with('companyname', $companynames);
        });

        Attandance::observe(AttandanceObserver::class);

        view()->composer('*', function ($view) {
            if (auth()->check() && auth()->user()->can('offrequest.approver')) {
                $pendingCount = \App\Models\Offrequest::where('status', 'pending')->count();
                $view->with('pendingCount', $pendingCount);
            }
            if (auth()->check() && auth()->user()->can('overtime.approvals')) {
                $pendingOvertimeCount = \App\Models\Overtime::where('status', 'pending')
                    ->where('manager_id',auth()->id())
                    ->count();
                $view->with('pendingOvertimeCount', $pendingOvertimeCount);
            }
        });

        if (config('app.env') === 'production') {
            URL::forceScheme('https');
        }
    }
}
