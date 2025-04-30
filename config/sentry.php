<?php

use Symfony\Component\Console\Exception\CommandNotFoundException;

/**
 * Sentry Laravel SDK configuration file.
 *
 * @see https://docs.sentry.io/platforms/php/guides/laravel/configuration/options/
 */
return [

    // @see https://docs.sentry.io/product/sentry-basics/dsn-explainer/
    'dsn' => env('SENTRY_DSN'),

    // @see https://spotlightjs.com/
    // 'spotlight' => env('SENTRY_SPOTLIGHT', false),

    // @see: https://docs.sentry.io/platforms/php/guides/laravel/configuration/options/#logger
    // 'logger' => Sentry\Logger\DebugFileLogger::class, // By default this will log to `storage_path('logs/sentry.log')`

    // @see: https://docs.sentry.io/platforms/php/guides/laravel/configuration/options/#traces_sample_rate
    'traces_sample_rate' => (float) env('SENTRY_TRACES_SAMPLE_RATE', '1.0'),

    // @see: https://docs.sentry.io/platforms/php/guides/laravel/configuration/options/#profiles_sample_rate
    'profiles_sample_rate' => (float) env('SENTRY_PROFILES_SAMPLE_RATE', '1.0'),

    // @see: https://docs.sentry.io/platforms/php/guides/laravel/configuration/options/#ignore_exceptions
    'ignore_exceptions' => [
        CommandNotFoundException::class,
    ],

    // @see: https://docs.sentry.io/platforms/php/guides/laravel/configuration/options/#ignore_transactions
    'ignore_transactions' => [
        // Ignore Laravel's default health URL
        '/up',

        // Ignore Livewire frontend URLs
        '/livewire/livewire.js',

        // Ignore Laravel's Horizon URLs
        '/horizon/api/batches',
        '/horizon/api/jobs/complete',
        '/horizon/api/jobs/failed',
        '/horizon/api/jobs/failed/{id}',
        '/horizon/api/jobs/pending',
        '/horizon/api/jobs/silenced',
        '/horizon/api/jobs/{id}',
        '/horizon/api/masters',
        '/horizon/api/metrics/queues',
        '/horizon/api/monitoring',
        '/horizon/api/stats',
        '/horizon/api/workload',
        '/horizon/{view?}',

        // Ignore Laravel Debugbar URLs
        '/_debugbar/open',
        '/_debugbar/assets/javascript',
        '/_debugbar/assets/stylesheets',
    ],

];
