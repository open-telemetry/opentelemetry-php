<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Common\Export;

use ArrayObject;

class InMemoryStorageManager
{
    private static ?ArrayObject $spans = null;
    private static ?ArrayObject $metrics = null;
    private static ?ArrayObject $logs = null;

    public static function metrics(): ArrayObject
    {
        return self::$metrics ??= new ArrayObject();
    }

    public static function logs(): ArrayObject
    {
        return self::$logs ??= new ArrayObject();
    }

    public static function spans(): ArrayObject
    {
        return self::$spans ??= new ArrayObject();
    }

    /**
     * @internal
     */
    public static function reset(): void
    {
        self::$spans = null;
        self::$metrics = null;
        self::$logs = null;
    }
}
