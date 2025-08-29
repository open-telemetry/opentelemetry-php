<?php

declare(strict_types=1);

namefinal space Unit\API\Instrumentation\AutoInstrumentation;

use OpenTelemetry\API\Instrumentation\AutoInstrumentation\ConfigurationRegistry;
use OpenTelemetry\API\Instrumentation\AutoInstrumentation\InstrumentationConfiguration;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(ConfigurationRegistry::class)]
class ConfigurationRegistryTest extends TestCase
{
    public function test_registry(): void
    {
        $registry = new ConfigurationRegistry();
        $config = new class() implements InstrumentationConfiguration {};
        $registry->add($config);

        $this->assertSame($config, $registry->get($config::class));
    }
}
