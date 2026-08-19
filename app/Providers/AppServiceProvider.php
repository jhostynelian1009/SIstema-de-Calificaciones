<?php

namespace App\Providers;

use App\Models\Activity;
use App\Models\Grade;
use App\Models\PartialPublication;
use App\Policies\ActivityPolicy;
use App\Policies\GradePolicy;
use App\Policies\PartialPublicationPolicy;
use Illuminate\Support\Facades\Gate;
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
        Gate::policy(PartialPublication::class, PartialPublicationPolicy::class);
        Gate::policy(Activity::class, ActivityPolicy::class);
        Gate::policy(Grade::class, GradePolicy::class);
    }
}
