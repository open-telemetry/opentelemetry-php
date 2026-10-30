<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\Unit\Config\SDK;

use OpenTelemetry\API\Behavior\Internal\Logging;
use OpenTelemetry\API\Behavior\Internal\LogWriter\LogWriterInterface;
use OpenTelemetry\API\Configuration\Context;
use OpenTelemetry\API\Configuration\Noop\NoopConfigProperties;
use OpenTelemetry\API\Instrumentation\Configuration\General\HttpConfig;
use OpenTelemetry\Config\SDK\ConfigProvider;
use OpenTelemetry\SDK\Common\Configuration\Variables;
use OpenTelemetry\Tests\TestState;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;

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

    /**
     * @see https://github.com/open-telemetry/opentelemetry-specification/blob/v1.61.0/specification/configuration/sdk-environment-variables.md#declarative-configuration
     */
    public function test_config_file_path_may_contain_comma(): void
    {
        $this->setEnvironmentVariable(Variables::OTEL_CONFIG_FILE, __DIR__ . '/../../SDK/fixtures/otel-sdk,with-comma.yaml');

        $this->assertInstanceOf(
            HttpConfig::class,
            (new ConfigProvider())->getInstrumentationConfig()->get(HttpConfig::class),
        );
    }

    public function test_invalid_sdk_config_does_not_affect_instrumentation_config(): void
    {
        $this->setEnvironmentVariable(Variables::OTEL_CONFIG_FILE, __DIR__ . '/../../SDK/fixtures/otel-sdk-invalid-sdk.yaml');

        $http = (new ConfigProvider())->getInstrumentationConfig()->get(HttpConfig::class);

        $this->assertInstanceOf(HttpConfig::class, $http);
        $this->assertSame(['Content-Type'], $http->config['client']['request_captured_headers']);
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

    /**
     * The global log writer is left at a level that would discard the warning, so this only passes
     * if the logger carried by the context is the one used.
     */
    public function test_unusable_config_file_logs_through_context_logger(): void
    {
        $logWriter = $this->createMock(LogWriterInterface::class);
        $logWriter->expects($this->never())->method('write');
        Logging::setLogWriter($logWriter);
        Logging::setLogLevel(LogLevel::EMERGENCY);

        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects($this->once())->method('log')->with(
            LogLevel::WARNING,
            $this->stringContains('Error during opentelemetry initialization'),
            $this->arrayHasKey('exception'),
        );

        $this->setEnvironmentVariable(Variables::OTEL_CONFIG_FILE, __DIR__ . '/does-not-exist.yaml');

        $this->assertInstanceOf(
            NoopConfigProperties::class,
            (new ConfigProvider())->getInstrumentationConfig((new Context())->withLogger($logger)),
        );
    }
}
