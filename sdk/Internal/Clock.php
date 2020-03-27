<?php

declare(strict_types=1);

namespace OpenTelemetry\Sdk\Internal;

class Clock
{
    public function __construct()
    {
    }

    public function millitime(): string
    {
        return self::format_microtime_to_millitime(\microtime());
    }

    public static function format_microtime_to_millitime(string $microtime): string
    {
        $space_at = \strpos($microtime, ' ');
        $decimal = (float) \substr($microtime, 0, $space_at);
        $seconds = \substr($microtime, $space_at + 1);
        $milliseconds = (string) ($decimal * 1000);

        return "{$seconds}${milliseconds}";
    }
}
