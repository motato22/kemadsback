<?php

namespace App\Services\Advertising;

use App\Models\Advertising\AdvTablet;
use Illuminate\Support\Facades\Cache;

class TabletCommandService
{
    private const CACHE_TTL_HOURS = 24;

    public static function push(AdvTablet $tablet, array $command): void
    {
        $key      = "adv:pending_commands:{$tablet->id}";
        $commands = Cache::get($key, []);
        $commands[] = $command;

        Cache::put($key, $commands, now()->addHours(self::CACHE_TTL_HOURS));
    }

    public static function flush(AdvTablet $tablet): array
    {
        $key      = "adv:pending_commands:{$tablet->id}";
        $commands = Cache::get($key, []);
        Cache::forget($key);

        return $commands;
    }

    public static function hasPending(AdvTablet $tablet): bool
    {
        return ! empty(Cache::get("adv:pending_commands:{$tablet->id}", []));
    }
}

