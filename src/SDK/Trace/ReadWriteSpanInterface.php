<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Trace;

use OpenTelemetry\API\Trace as API;

interface ReadWriteSpanInterface extends API\SpanInterface, ReadableSpanInterface
{
}
