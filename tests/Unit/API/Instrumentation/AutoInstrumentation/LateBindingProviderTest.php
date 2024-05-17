<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\Unit\API\Instrumentation\AutoInstrumentation;

use Nevay\SPI\ServiceLoader;
use OpenTelemetry\API\Behavior\Internal\Logging;
use OpenTelemetry\API\Globals;
use OpenTelemetry\API\Instrumentation\AutoInstrumentation\ConfigurationRegistry;
use OpenTelemetry\API\Instrumentation\AutoInstrumentation\Context;
use OpenTelemetry\API\Instrumentation\AutoInstrumentation\HookManager;
use OpenTelemetry\API\Instrumentation\AutoInstrumentation\Instrumentation;
use OpenTelemetry\API\Instrumentation\Configurator;
use OpenTelemetry\API\Trace\LateBindingTracer;
use OpenTelemetry\API\Trace\LateBindingTracerProvider;
use OpenTelemetry\API\Trace\TracerInterface;
use OpenTelemetry\API\Trace\TracerProviderInterface;
use OpenTelemetry\SDK\Common\Configuration\Variables;
use OpenTelemetry\SDK\SdkAutoloader;
use OpenTelemetry\Tests\TestState;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(LateBindingTracerProvider::class)]
#[CoversClass(LateBindingTracer::class)]
class LateBindingProviderTest extends TestCase
{
    use TestState;

    public function setUp(): void
    {
        Logging::disable();
    }

    public function test_late_binding_tracer(): void
    {
        $instrumentation = new class() implements Instrumentation {
            private static ?Context $context;
            public function register(HookManager $hookManager, ConfigurationRegistry $configuration, Context $context): void
            {
                self::$context = $context;
            }
            public function getTracer(): TracerInterface
            {
                assert(self::$context !== null);

                return self::$context->tracerProvider->getTracer('test');
            }
        };
        $this->setEnvironmentVariable(Variables::OTEL_PHP_AUTOLOAD_ENABLED, 'true');
        $called = false;
        //the "real" tracer, which will be accessed through a late binding tracer
        $tracerProvider = $this->createMock(TracerProviderInterface::class);
        $tracer = $this->createMock(TracerInterface::class);
        $tracerProvider->method('getTracer')->willReturnCallback(function () use (&$called, $tracer): TracerInterface {
            $called = true;

            return $tracer;
        });
        ServiceLoader::register(Instrumentation::class, $instrumentation::class);
        //@todo reset?
        $this->assertTrue(SdkAutoloader::autoload());
        //tracer initializer added _after_ autoloader has run and instrumentation registered
        Globals::registerInitializer(function (Configurator $configurator) use ($tracerProvider): Configurator {
            return $configurator->withTracerProvider($tracerProvider);
        });

        $this->assertFalse($called);
        $tracer = $instrumentation->getTracer();
        $this->assertFalse($called);
        $tracer->spanBuilder('test-span')->startSpan(); /** @phpstan-ignore-next-line */
        $this->assertTrue($called);
    }
}
