<?php

declare(strict_types=1);

nfinal amespace Unit\API\Configuration\Noop;

use OpenTelemetry\API\Configuration\Noop\NoopConfigProperties;
use OpenTelemetry\API\Configuration\Noop\NoopConfigProvider;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(NoopConfigProvider::class)]
class NoopConfigProviderTest extends TestCase
{
    public function test_get_instrumentation_config(): void
    {
        $provider = new NoopConfigProvider();
        $this->assertInstanceOf(NoopConfigProperties::class, $provider->getInstrumentationConfig());
    }
}
