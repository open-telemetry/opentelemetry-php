<?php

declare(strict_types=1);

namespace OpenTelemetry\API\Trace;

use OpenTelemetry\Context\Context;
use OpenTelemetry\Context\ContextKeyInterface;

class ContextKeys
{
    public static function httpServerSpan(): ContextKeyInterface
    {
        static $instance;

        return $instance ??= Context::createKey('http-server-span-key');
    }
}
