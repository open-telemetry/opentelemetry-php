<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Common\Dev\Compatibility\BC;

use OpenTelemetry\SDK\Common\Dev\Compatibility\Util;
use OpenTelemetry\SDK\Common\Time\SystemClock as Moved;

/**
 * @codeCoverageIgnoreStart
 */
const OpenTelemetry_SDK_SystemClock = 'OpenTelemetry\SDK\SystemClock';

class SystemClock implements ClockInterface
{
    private Moved $adapted;

    public function __construct()
    {
        $this->adapted = new Moved();
        Util::triggerClassDeprecationNotice(
            OpenTelemetry_SDK_SystemClock,
            Moved::class
        );
    }

    public static function getInstance(): self
    {
        return new self();
    }

    public function now(): int
    {
        return $this->adapted->now();
    }

    public function nanoTime(): int
    {
        return $this->adapted->now();
    }
}

class_alias(SystemClock::class, OpenTelemetry_SDK_SystemClock);
/**
 * @codeCoverageIgnoreEnd
 */
