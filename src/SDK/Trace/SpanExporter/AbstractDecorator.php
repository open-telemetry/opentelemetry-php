<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Trace\SpanExporter;

use OpenTelemetry\SDK\Trace\Behavior\SpanExporterDecoratorTrait;

abstract class AbstractDecorator
{
    use SpanExporterDecoratorTrait;
}
