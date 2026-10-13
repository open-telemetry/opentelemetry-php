<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\Unit\Config\SDK;

use OpenTelemetry\API\Behavior\Internal\Logging;
use OpenTelemetry\API\Behavior\Internal\LogWriter\LogWriterInterface;
use OpenTelemetry\API\Configuration\Noop\NoopConfigProperties;
use OpenTelemetry\API\Instrumentation\Configuration\General\HttpConfig;
use OpenTelemetry\Config\SDK\ConfigProvider;
use OpenTelemetry\SDK\Common\Configuration\Variables;
use OpenTelemetry\Tests\TestState;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(ConfigProvider::class)]
class ConfigProviderTest extends TestCase
{
    use TestState;

    public function test_no_config_file_returns_noop(): void
    {
        $this->assertInstanceOf(
            NoopConfigProperties::class,
            (new ConfigProvider())->getInstrumentationConfig(),
        );
    }

    public function test_config_file_returns_instrumentation_config(): void
    {
        $this->setEnvironmentVariable(Variables::OTEL_CONFIG_FILE, __DIR__ . '/../../SDK/fixtures/otel-sdk.yaml');

        $this->assertInstanceOf(
            HttpConfig::class,
            (new ConfigProvider())->getInstrumentationConfig()->get(HttpConfig::class),
        );
    }

    public function test_unusable_config_file_logs_and_returns_noop(): void
    {
        $logWriter = $this->createMock(LogWriterInterface::class);
        $logWriter->expects($this->atLeastOnce())->method('write')->with(
            $this->anything(),
            $this->stringContains('Error during opentelemetry initialization'),
            $this->anything(),
        );
        Logging::setLogWriter($logWriter);

        $this->setEnvironmentVariable(Variables::OTEL_CONFIG_FILE, __DIR__ . '/does-not-exist.yaml');

        $this->assertInstanceOf(
            NoopConfigProperties::class,
            (new ConfigProvider())->getInstrumentationConfig(),
        );
    }
}
