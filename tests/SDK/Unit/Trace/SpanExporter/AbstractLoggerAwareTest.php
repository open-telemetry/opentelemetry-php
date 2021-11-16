<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\SDK\Unit\Trace\SpanExporter;

use PHPUnit\Framework\TestCase;

abstract class AbstractLoggerAwareTest extends TestCase
{
    use LoggerAwareTestTrait;
}
