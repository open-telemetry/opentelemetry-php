<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\Unit\SDK;

use Nevay\SPI\ServiceLoader;
use OpenTelemetry\API\Behavior\Internal\Logging;
use OpenTelemetry\API\Behavior\Internal\LogWriter\LogWriterInterface;
use OpenTelemetry\API\Configuration\Config\ComponentProvider;
use OpenTelemetry\API\Configuration\Config\ComponentProviderRegistry;
use OpenTelemetry\API\Configuration\Context;
use OpenTelemetry\API\Globals;
use OpenTelemetry\API\Instrumentation\AutoInstrumentation\InstrumentationConfiguration;
use OpenTelemetry\API\Instrumentation\Configurator;
use OpenTelemetry\API\LoggerHolder;
use OpenTelemetry\API\Logs\NoopEventLoggerProvider;
use OpenTelemetry\API\Logs\NoopLoggerProvider;
use OpenTelemetry\API\Metrics\Noop\NoopMeterProvider;
use OpenTelemetry\API\Trace\NoopTracerProvider;
use OpenTelemetry\Context\Propagation\NoopResponsePropagator;
use OpenTelemetry\Context\Propagation\NoopTextMapPropagator;
use OpenTelemetry\Context\Propagation\ResponsePropagatorInterface;
use OpenTelemetry\Context\Propagation\TextMapPropagatorInterface;
use OpenTelemetry\SDK\Common\Configuration\Variables;
use OpenTelemetry\SDK\SdkAutoloader;
use OpenTelemetry\Tests\TestState;
use Override;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\PreserveGlobalState;
use PHPUnit\Framework\Attributes\RunInSeparateProcess;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\Definition\Builder\NodeBuilder;
use TypeError;

#[CoversClass(SdkAutoloader::class)]
class SdkAutoloaderTest extends TestCase
{
    use TestState;

    /**
     * @var LoggerInterface&MockObject
     */
    private LoggerInterface $logger;

    #[Override]
    public function setUp(): void
    {
        $this->logger = $this->createMock(LoggerInterface::class);
        LoggerHolder::set($this->logger);
        Globals::reset();
    }

    public function test_disabled_by_default(): void
    {
        $this->assertFalse(SdkAutoloader::isEnabled());
    }

    public function test_enabled(): void
    {
        $this->setEnvironmentVariable(Variables::OTEL_PHP_AUTOLOAD_ENABLED, 'true');
        $this->assertTrue(SdkAutoloader::isEnabled());
    }

    public function test_disabled(): void
    {
        $this->setEnvironmentVariable(Variables::OTEL_PHP_AUTOLOAD_ENABLED, 'false');
        $this->assertFalse(SdkAutoloader::isEnabled());
    }

    public function test_disabled_with_invalid_setting(): void
    {
        $this->setEnvironmentVariable(Variables::OTEL_PHP_AUTOLOAD_ENABLED, 'invalid-value');
        $this->assertFalse(SdkAutoloader::isEnabled());
    }

    public function test_noop_if_disabled(): void
    {
        $this->setEnvironmentVariable(Variables::OTEL_PHP_AUTOLOAD_ENABLED, 'false');
        $this->assertFalse(SdkAutoloader::autoload());
        $this->assertInstanceOf(NoopMeterProvider::class, Globals::meterProvider());
        $this->assertInstanceOf(NoopTracerProvider::class, Globals::tracerProvider());
        $this->assertInstanceOf(NoopLoggerProvider::class, Globals::loggerProvider());
        $this->assertInstanceOf(NoopEventLoggerProvider::class, Globals::eventLoggerProvider());
        $this->assertInstanceOf(NoopTextMapPropagator::class, Globals::propagator(), 'propagator not initialized by disabled autoloader');
        $this->assertInstanceOf(NoopResponsePropagator::class, Globals::responsePropagator(), 'response propagator not initialized by disabled autoloader');
    }

    public function test_sdk_disabled_does_not_disable_propagator(): void
    {
        $this->setEnvironmentVariable(Variables::OTEL_PHP_AUTOLOAD_ENABLED, 'true');
        $this->setEnvironmentVariable(Variables::OTEL_SDK_DISABLED, 'true');
        SdkAutoloader::autoload();
        $this->assertNotInstanceOf(NoopTextMapPropagator::class, Globals::propagator());
        $this->assertInstanceOf(NoopMeterProvider::class, Globals::meterProvider());
        $this->assertInstanceOf(NoopTracerProvider::class, Globals::tracerProvider());
        $this->assertInstanceOf(NoopResponsePropagator::class, Globals::responsePropagator());
    }

    public function test_enabled_by_configuration(): void
    {
        $this->setEnvironmentVariable(Variables::OTEL_PHP_AUTOLOAD_ENABLED, 'true');
        SdkAutoloader::autoload();
        $this->assertNotInstanceOf(NoopTextMapPropagator::class, Globals::propagator());
        $this->assertNotInstanceOf(NoopMeterProvider::class, Globals::meterProvider());
        $this->assertNotInstanceOf(NoopTracerProvider::class, Globals::tracerProvider());
        $this->assertNotInstanceOf(NoopLoggerProvider::class, Globals::loggerProvider());
        $this->assertInstanceOf(NoopResponsePropagator::class, Globals::responsePropagator());
    }

    public function test_exclude_urls_without_request_uri(): void
    {
        $this->setEnvironmentVariable(Variables::OTEL_PHP_EXCLUDED_URLS, '.*');
        unset($_SERVER['REQUEST_URI']);
        $this->assertFalse(SdkAutoloader::isExcludedUrl());
    }

    #[DataProvider('excludeUrlsProvider')]
    public function test_exclude_urls(string $exclude, string $uri, bool $expected): void
    {
        $this->setEnvironmentVariable(Variables::OTEL_PHP_EXCLUDED_URLS, $exclude);
        $_SERVER['REQUEST_URI'] = $uri;
        $this->assertSame($expected, SdkAutoloader::isExcludedUrl());
    }

    public static function excludeUrlsProvider(): array
    {
        return [
            [
                'foo',
                '/foo?bar=baz',
                true,
            ],
            [
                'foo',
                '/bar',
                false,
            ],
            [
                'foo,bar',
                'https://example.com/bar?p1=2',
                true,
            ],
            [
                'foo,bar',
                'https://example.com/baz?p1=2',
                false,
            ],
            [
                'client/.*/info,healthcheck',
                'https://site/client/123/info',
                true,
            ],
            [
                'client/.*/info,healthcheck',
                'https://site/xyz/healthcheck',
                true,
            ],
        ];
    }

    public function test_enabled_with_excluded_url(): void
    {
        $this->setEnvironmentVariable(Variables::OTEL_PHP_AUTOLOAD_ENABLED, 'true');
        $this->setEnvironmentVariable(Variables::OTEL_PHP_EXCLUDED_URLS, '.*');
        $_SERVER['REQUEST_URI'] = '/test';
        $this->assertFalse(SdkAutoloader::autoload());
    }

    public function test_autoload_from_config_file(): void
    {
        $this->logger->expects($this->never())->method('log')->with($this->equalTo(LogLevel::ERROR));
        $this->setEnvironmentVariable(Variables::OTEL_PHP_AUTOLOAD_ENABLED, 'true');
        $this->setEnvironmentVariable(Variables::OTEL_CONFIG_FILE, __DIR__ . '/fixtures/otel-sdk.yaml');

        $this->assertTrue(SdkAutoloader::autoload());
        $this->assertNotInstanceOf(NoopTracerProvider::class, Globals::tracerProvider());
    }

    /**
     * @see https://github.com/open-telemetry/opentelemetry-specification/blob/v1.61.0/specification/configuration/sdk-environment-variables.md#declarative-configuration
     */
    public function test_autoload_from_config_file_with_comma_in_path(): void
    {
        $this->logger->expects($this->never())->method('log')->with($this->equalTo(LogLevel::ERROR));
        $this->setEnvironmentVariable(Variables::OTEL_PHP_AUTOLOAD_ENABLED, 'true');
        $this->setEnvironmentVariable(Variables::OTEL_CONFIG_FILE, __DIR__ . '/fixtures/otel-sdk,with-comma.yaml');

        $this->assertTrue(SdkAutoloader::autoload());
        $this->assertNotInstanceOf(NoopTracerProvider::class, Globals::tracerProvider());
    }

    /**
     * Tests the scenario where the SDK is created from config file, but a custom component
     * uses composer's autoload->files to add its own initializer
     */
    public function test_autoload_with_late_globals_initializer(): void
    {
        $this->setEnvironmentVariable(Variables::OTEL_PHP_AUTOLOAD_ENABLED, 'true');
        $this->setEnvironmentVariable(Variables::OTEL_CONFIG_FILE, __DIR__ . '/fixtures/otel-sdk.yaml');
        $this->assertTrue(SdkAutoloader::autoload());
        //SDK is configured, but globals have not been initialized yet, so we can add more initializers
        $propagator = $this->createStub(TextMapPropagatorInterface::class);
        $responsePropagator = $this->createStub(ResponsePropagatorInterface::class);
        Globals::registerInitializer(function (Configurator $configurator) use ($propagator, $responsePropagator) {
            return $configurator->withPropagator($propagator)->withResponsePropagator($responsePropagator);
        });

        $this->assertSame($propagator, Globals::propagator());
        $this->assertSame($responsePropagator, Globals::responsePropagator());
    }

    #[DataProvider('unusableConfigFileProvider')]
    public function test_autoload_with_unusable_config_file(string $file): void
    {
        $logWriter = $this->createMock(LogWriterInterface::class);
        $logWriter->expects($this->atLeastOnce())->method('write')->with(
            $this->anything(),
            $this->stringContains('Error during opentelemetry initialization'),
            $this->anything(),
        );
        Logging::setLogWriter($logWriter);

        $this->setEnvironmentVariable(Variables::OTEL_PHP_AUTOLOAD_ENABLED, 'true');
        $this->setEnvironmentVariable(Variables::OTEL_CONFIG_FILE, $file);

        try {
            $this->assertTrue(SdkAutoloader::autoload());
            $this->assertInstanceOf(NoopTracerProvider::class, Globals::tracerProvider());
        } finally {
            Logging::reset();
        }
    }

    public static function unusableConfigFileProvider(): iterable
    {
        yield 'missing' => [__DIR__ . '/fixtures/does-not-exist.yaml'];
        yield 'malformed' => [__DIR__ . '/fixtures/otel-sdk-malformed.yaml'];
        yield 'schema-invalid' => [__DIR__ . '/fixtures/otel-sdk-invalid.yaml'];
    }

    /**
     * The SDK and instrumentation roots are processed separately, so an `instrumentation/development`
     * node that does not validate must not cost us the SDK configured by the same document.
     */
    public function test_autoload_with_unusable_instrumentation_config(): void
    {
        $logWriter = $this->createMock(LogWriterInterface::class);
        $logWriter->expects($this->atLeastOnce())->method('write')->with(
            $this->anything(),
            $this->stringContains('Error during opentelemetry initialization'),
            $this->anything(),
        );
        Logging::setLogWriter($logWriter);

        $this->setEnvironmentVariable(Variables::OTEL_PHP_AUTOLOAD_ENABLED, 'true');
        $this->setEnvironmentVariable(Variables::OTEL_CONFIG_FILE, __DIR__ . '/fixtures/otel-sdk-invalid-instrumentation.yaml');

        try {
            $this->assertTrue(SdkAutoloader::autoload());
            $this->assertNotInstanceOf(NoopTracerProvider::class, Globals::tracerProvider());
        } finally {
            Logging::reset();
        }
    }

    /**
     * A plugin that throws an {@see \Error} from its own `createPlugin()` must be reported as the
     * fault it is, not as a missing sdk-configuration API.
     *
     * Isolated because the SPI registration below has to happen before anything in this process has
     * loaded the ComponentProvider service.
     */
    #[RunInSeparateProcess]
    #[PreserveGlobalState(false)]
    public function test_autoload_reports_plugin_error_rather_than_version_mismatch(): void
    {
        ServiceLoader::register(ComponentProvider::class, ThrowingInstrumentationProvider::class);

        $messages = [];
        $logWriter = $this->createMock(LogWriterInterface::class);
        $logWriter->method('write')->willReturnCallback(
            static function ($level, string $message) use (&$messages): void {
                $messages[] = $message;
            },
        );
        Logging::setLogWriter($logWriter);

        $this->setEnvironmentVariable(Variables::OTEL_PHP_AUTOLOAD_ENABLED, 'true');
        $this->setEnvironmentVariable(Variables::OTEL_CONFIG_FILE, __DIR__ . '/fixtures/otel-sdk-throwing-instrumentation.yaml');

        try {
            $this->assertTrue(SdkAutoloader::autoload());
            Globals::tracerProvider();
        } finally {
            Logging::reset();
        }

        $logged = implode("\n", $messages);
        $this->assertStringContainsString(ThrowingInstrumentationProvider::MESSAGE, $logged);
        $this->assertStringNotContainsString('requires a newer open-telemetry/sdk-configuration', $logged);
    }
}

/**
 * Stands in for a third-party plugin whose own `createPlugin()` is buggy.
 *
 * @implements ComponentProvider<InstrumentationConfiguration>
 */
final class ThrowingInstrumentationProvider implements ComponentProvider
{
    public const MESSAGE = 'plugin raised an Error of its own';

    #[Override]
    public function createPlugin(array $properties, Context $context): InstrumentationConfiguration
    {
        throw new TypeError(self::MESSAGE);
    }

    #[Override]
    public function getConfig(ComponentProviderRegistry $registry, NodeBuilder $builder): ArrayNodeDefinition
    {
        return $builder->arrayNode('throwing_instrumentation');
    }
}
