<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\Unit\SDK;

use Nevay\SPI\ServiceLoader;
use OpenTelemetry\API\Behavior\Internal\Logging;
use OpenTelemetry\API\LoggerHolder;
use OpenTelemetry\API\Trace\Propagation\TraceContextPropagator;
use OpenTelemetry\Contrib\Otlp\OtlpHttpTransportFactory;
use OpenTelemetry\Contrib\Otlp\SpanExporterFactory as OtlpSpanExporterFactory;
use OpenTelemetry\Extension\Propagator\B3\B3Propagator;
use OpenTelemetry\SDK\RegistrarInterface;
use OpenTelemetry\SDK\Registry;
use OpenTelemetry\SDK\Trace\SpanExporter\ConsoleSpanExporterFactory;
use OpenTelemetry\SDK\Trace\SpanExporter\InMemorySpanExporterFactory;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use ReflectionClass;

#[CoversClass(Registry::class)]
class RegistryRegistrarTest extends TestCase
{
    private array $registryState;
    private array $serviceLoaderMappings;

    #[\Override]
    protected function setUp(): void
    {
        $this->registryState = (new ReflectionClass(Registry::class))->getStaticProperties();
        $this->serviceLoaderMappings = (new ReflectionClass(ServiceLoader::class))->getStaticPropertyValue('mappings');
        $this->resetRegistry();
        CountingRegistrar::$runs = 0;
        Logging::reset();
        LoggerHolder::set($this->createMock(LoggerInterface::class));
    }

    #[\Override]
    protected function tearDown(): void
    {
        LoggerHolder::unset();
        Logging::reset();
        $registry = new ReflectionClass(Registry::class);
        foreach ($this->registryState as $name => $value) {
            $registry->setStaticPropertyValue($name, $value);
        }
        (new ReflectionClass(ServiceLoader::class))->setStaticPropertyValue('mappings', $this->serviceLoaderMappings);
    }

    /**
     * Registration must not depend on the order in which Composer includes the `autoload.files` of the packages: a
     * registry that was never touched by a `_register.php` file still knows the factories contributed through SPI.
     */
    public function test_components_are_registered_by_registrars_on_first_lookup(): void
    {
        $this->assertSame(OtlpSpanExporterFactory::class, Registry::spanExporterFactory('otlp')::class);
        $this->assertSame(OtlpHttpTransportFactory::class, Registry::transportFactory('http')::class);
        $this->assertSame(TraceContextPropagator::getInstance(), Registry::textMapPropagator('tracecontext'));
        $this->assertSame(B3Propagator::getB3SingleHeaderInstance(), Registry::textMapPropagator('b3'));
    }

    public function test_registrar_provided_later_is_run_once(): void
    {
        ServiceLoader::register(RegistrarInterface::class, CountingRegistrar::class);

        $this->assertSame(ConsoleSpanExporterFactory::class, Registry::spanExporterFactory('counting')::class);
        Registry::spanExporterFactory('counting');
        Registry::transportFactory('stream');

        $this->assertSame(1, CountingRegistrar::$runs);
    }

    public function test_explicit_registration_made_before_first_lookup_wins(): void
    {
        ServiceLoader::register(RegistrarInterface::class, CountingRegistrar::class);
        Registry::registerSpanExporterFactory('counting', InMemorySpanExporterFactory::class);

        $this->assertSame(InMemorySpanExporterFactory::class, Registry::spanExporterFactory('counting')::class);
    }

    public function test_clobbering_registration_wins_after_registrars_ran(): void
    {
        ServiceLoader::register(RegistrarInterface::class, CountingRegistrar::class);
        $this->assertSame(ConsoleSpanExporterFactory::class, Registry::spanExporterFactory('counting')::class);

        Registry::registerSpanExporterFactory('counting', InMemorySpanExporterFactory::class, true);

        $this->assertSame(InMemorySpanExporterFactory::class, Registry::spanExporterFactory('counting')::class);
    }

    public function test_missing_component_error_has_no_plugin_hint_when_registrars_exist(): void
    {
        try {
            Registry::spanExporterFactory('nope');
            $this->fail('expected an exception');
        } catch (\RuntimeException $e) {
            $this->assertSame('Span exporter factory not defined for: nope', $e->getMessage());
        }
    }

    public function test_missing_component_error_points_to_the_spi_plugin_without_registrars(): void
    {
        $mappings = (new ReflectionClass(ServiceLoader::class))->getStaticPropertyValue('mappings');
        $mappings[RegistrarInterface::class] = [];
        (new ReflectionClass(ServiceLoader::class))->setStaticPropertyValue('mappings', $mappings);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessageMatches('/^Span exporter factory not defined for: nope\. .*tbachert\/spi.*allow-plugins/');

        Registry::spanExporterFactory('nope');
    }

    public function test_failing_registrar_does_not_stop_the_others(): void
    {
        ServiceLoader::register(RegistrarInterface::class, FailingRegistrar::class);
        ServiceLoader::register(RegistrarInterface::class, CountingRegistrar::class);

        $this->assertSame(ConsoleSpanExporterFactory::class, Registry::spanExporterFactory('counting')::class);
        $this->assertSame(ConsoleSpanExporterFactory::class, Registry::spanExporterFactory('console')::class);
    }

    public function test_registrar_registered_after_the_first_lookup_is_picked_up_on_a_miss(): void
    {
        $this->assertSame(ConsoleSpanExporterFactory::class, Registry::spanExporterFactory('console')::class);

        // without the SPI plugin, a `_register.php` file that runs later registers its registrar late
        ServiceLoader::register(RegistrarInterface::class, CountingRegistrar::class);

        $this->assertSame(ConsoleSpanExporterFactory::class, Registry::spanExporterFactory('counting')::class);
        $this->assertSame(1, CountingRegistrar::$runs);
    }

    private function resetRegistry(): void
    {
        $registry = new ReflectionClass(Registry::class);
        foreach ($registry->getStaticProperties() as $name => $value) {
            $registry->setStaticPropertyValue($name, match (true) {
                is_bool($value) => false,
                is_int($value) => 0,
                default => [],
            });
        }
    }
}

/**
 * @internal
 */
final class CountingRegistrar implements RegistrarInterface
{
    public static int $runs = 0;

    #[\Override]
    public function register(): void
    {
        self::$runs++;
        Registry::registerSpanExporterFactory('counting', ConsoleSpanExporterFactory::class);
    }
}

/**
 * @internal
 */
final class FailingRegistrar implements RegistrarInterface
{
    #[\Override]
    public function register(): void
    {
        throw new \LogicException('registrar failed');
    }
}
