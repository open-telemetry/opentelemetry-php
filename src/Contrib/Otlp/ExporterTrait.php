<?php

declare(strict_types=1);

namespace OpenTelemetry\Contrib\Otlp;

use OpenTelemetry\SDK\Behavior\LogsMessagesTrait;
use OpenTelemetry\SDK\Trace\Behavior\UsesSpanConverterTrait;

trait ExporterTrait
{
    use LogsMessagesTrait;
    use UsesSpanConverterTrait;
}
