<?php

namespace App\Providers;

// use Illuminate\Support\Facades\Gate;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        'App\Models\User' => 'App\Policies\UserPolicy',
        'App\Models\Vehicle' => 'App\Policies\VehiclePolicy',
        'App\Models\Customer' => 'App\Policies\CustomerPolicy',
        'App\Models\PromotedAdvert' => 'App\Policies\PromotedAdvertPolicy',
        'App\Models\Model' => 'App\Policies\ModelPolicy',
        'App\Models\CustomerBusiness' => 'App\Policies\CustomerBusinessPolicy',
    ];

    /**
     * Register any authentication / authorization services.
     *
     * @return void
     */
    public function boot()
    {
        $this->registerPolicies();

        //
    }
}
