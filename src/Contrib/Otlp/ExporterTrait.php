<?php

declare(strict_types=1);

namespace OpenTelemetry\Contrib\Otlp;

use OpenTelemetry\SDK\Common\Environment\EnvironmentVariablesTrait;
use OpenTelemetry\SDK\Trace\Behavior\UsesSpanConverterTrait;

trait ExporterTrait
{
    use EnvironmentVariablesTrait;
    use UsesSpanConverterTrait;
}
