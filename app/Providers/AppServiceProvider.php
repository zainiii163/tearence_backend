<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Blade;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        // Custom Blade directives for monetization
        Blade::directive('money', function ($expression) {
            return "<?php echo '$' . number_format($expression, 2); ?>";
        });

        Blade::directive('badge', function ($expression) {
            return "<?php echo '<span class=\"inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium ' . $expression . '\">'; ?>";
        });

        Blade::directive('endbadge', function () {
            return "<?php echo '</span>'; ?>";
        });
    }
}
