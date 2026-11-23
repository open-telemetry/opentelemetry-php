<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\Unit\Config\SDK;

use OpenTelemetry\API\Configuration\ConfigProperties;
use OpenTelemetry\Config\SDK\ConfigProvider;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(ConfigProvider::class)]
class ConfigProviderTest extends TestCase
{
    /**
     * @see https://github.com/open-telemetry/opentelemetry-specification/blob/v1.61.0/specification/configuration/api.md#get-instrumentation-config
     */
    public function test_returns_the_given_instrumentation_config(): void
    {
        $configProperties = $this->createStub(ConfigProperties::class);

        $this->assertSame($configProperties, (new ConfigProvider($configProperties))->getInstrumentationConfig());
    }
}
