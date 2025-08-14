<?php

namespace App\Providers;

use OpenAI;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(\OpenAI\Client::class, function () {
            $factory = OpenAI::factory()
                ->withApiKey(config('services.openai.api_key'));

            if (config('services.openai.organization')) {
                $factory->withOrganization(config('services.openai.organization'));
            }
            if (config('services.openai.project')) {
                $factory->withProject(config('services.openai.project'));
            }
            if (config('services.openai.base_uri')) {
                $factory->withBaseUri(config('services.openai.base_uri'));
            }

            return $factory->make();
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
