<?php

declare(strict_types=1);

namespace OpenTelemetry\API\Common\Event;

use OpenTelemetry\API\Common\Event\Event\DebugEvent;
use OpenTelemetry\API\Common\Event\Event\ErrorEvent;
use OpenTelemetry\API\Common\Event\Event\WarningEvent;

class EventType
{
    public const ERROR = ErrorEvent::class;
    public const WARNING = WarningEvent::class;
    public const DEBUG = DebugEvent::class;
}
