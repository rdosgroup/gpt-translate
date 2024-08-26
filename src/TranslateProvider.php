<?php

namespace Edeoliv\GptTranslate;

use Illuminate\Support\ServiceProvider;
use Edeoliv\GptTranslate\Console\TranslateMake;
use Edeoliv\GptTranslate\Console\TranslateLang;

class TranslateProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([__DIR__.'/../config/openai.php' => config_path('openai.php')], 'config');
            $this->publishes([__DIR__.'/../config/gpt-translate.php' => config_path('gpt-translate.php')], 'config');
        }

        if ($this->app->runningInConsole()) {
            $this->commands([
                TranslateMake::class,
                TranslateLang::class,
            ]);
        }

    }
}
