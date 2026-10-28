<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Trace\AutoRootSpan;

use OpenTelemetry\API\Instrumentation\AutoInstrumentation\InstrumentationConfiguration;

final readonly class AutoRootSpanConfiguration implements InstrumentationConfiguration
{
    public function __construct(
        public bool $enabled = false,
    ) {}
}
