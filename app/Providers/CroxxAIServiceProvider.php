<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Services\CroxxAI\CroxxAIService;
use App\Services\CroxxAI\CompetencyService;
use App\Services\CroxxAI\AssessmentService;
use App\Services\CroxxAI\CourseService;
use App\Services\CroxxAI\TemplateService;
use App\Services\CroxxAI\TextGenerationService;

class CroxxAIServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Register individual services as singletons
        $this->app->singleton(CompetencyService::class, function ($app) {
            return new CompetencyService();
        });

        $this->app->singleton(AssessmentService::class, function ($app) {
            return new AssessmentService();
        });

        $this->app->singleton(CourseService::class, function ($app) {
            return new CourseService();
        });

        $this->app->singleton(TemplateService::class, function ($app) {
            return new TemplateService();
        });

        $this->app->singleton(TextGenerationService::class, function ($app) {
            return new TextGenerationService();
        });

        // Register the main CroxxAI service
        $this->app->singleton(CroxxAIService::class, function ($app) {
            return new CroxxAIService();
        });

        // Create an alias for easier access
        $this->app->alias(CroxxAIService::class, 'croxx-ai');
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Any bootstrapping logic can go here
        // For example, you could publish config files, views, etc.
    }

    /**
     * Get the services provided by the provider.
     */
    public function provides(): array
    {
        return [
            CroxxAIService::class,
            CompetencyService::class,
            AssessmentService::class,
            CourseService::class,
            TemplateService::class,
            TextGenerationService::class,
            'croxx-ai',
        ];
    }
}