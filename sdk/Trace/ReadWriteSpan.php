<?php

declare(strict_types=1);

namespace OpenTelemetry\Sdk\Trace;

use OpenTelemetry\Trace as API;

interface ReadWriteSpan extends API\Span, ReadableSpan
{
}
