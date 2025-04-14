<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Common\Export;

use ArrayObject;

class InMemoryStorageManager
{
    private static ArrayObject $spans;
    private static ArrayObject $metrics;
    private static ArrayObject $logs;

    public static function metrics(): ArrayObject
    {
        /** @psalm-suppress RedundantPropertyInitializationCheck */
        return self::$metrics ??= new ArrayObject();
    }

    public static function logs(): ArrayObject
    {
        /** @psalm-suppress RedundantPropertyInitializationCheck */
        return self::$logs ??= new ArrayObject();
    }

    public static function spans(): ArrayObject
    {
        /** @psalm-suppress RedundantPropertyInitializationCheck */
        return self::$spans ??= new ArrayObject();
    }
}
