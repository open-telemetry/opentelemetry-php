<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Common\Event;

use OpenTelemetry\SDK\Common\Event\Event\DebugEvent;
use OpenTelemetry\SDK\Common\Event\Event\ErrorEvent;
use OpenTelemetry\SDK\Common\Event\Event\WarningEvent;

class EventType
{
    public const ERROR = ErrorEvent::class;
    public const WARNING = WarningEvent::class;
    public const DEBUG = DebugEvent::class;
}
