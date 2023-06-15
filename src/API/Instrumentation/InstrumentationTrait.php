<?php

declare(strict_types=1);

namespace OpenTelemetry\API\Instrumentation;

use OpenTelemetry\API\Metrics\MeterInterface;
use OpenTelemetry\API\Metrics\MeterProviderInterface;
use OpenTelemetry\API\Metrics\Noop\NoopMeter;
use OpenTelemetry\API\Trace\NoopTracer;
use OpenTelemetry\API\Trace\NoopTracerProvider;
use OpenTelemetry\API\Trace\TracerInterface;
use OpenTelemetry\API\Trace\TracerProviderInterface;
use OpenTelemetry\Context\Propagation\NoopTextMapPropagator;
use OpenTelemetry\Context\Propagation\TextMapPropagatorInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use RuntimeException;

/**
 This trait in conjunction with the InstrumentationInterface is meant as a base for instrumentations for the
 OpenTelemetry API.
 Instrumentations need to implement the abstract methods of this trait (besides any instrumentation specific code)

 A very simplified instrumentation could look like this:

class Instrumentation implements InstrumentationInterface
{
    use InstrumentationTrait;

    public function getName(): string
    {
        return 'foo-instrumentation';
    }

    public function getVersion(): ?string
    {
        return '0.0.1';
    }

    public function getSchemaUrl(): ?string
    {
        return null;
    }

    public function init(): bool
    {
        // This is just an example. In a real-world scenario one should only create spans in reaction of things
        // happening in the instrumented code, not just for the sake of it.
        $span = $this->getTracer()->spanBuilder($this->getName())->startSpan();
        // do stuff
        $span->end();
    }
}

An user of the instrumentation and API/SDK would the call:

$instrumentation = new Instrumentation;
$instrumentation->activate()

to activate and use the instrumentation with the API/SDK.
 **/

trait InstrumentationTrait
{
    private TextMapPropagatorInterface $propagator;
    private TracerProviderInterface $tracerProvider;
    private TracerInterface $tracer;
    private MeterInterface $meter;
    private LoggerInterface $logger;

    public function __construct()
    {
        $this->initDefaults();
    }

    /**
     * The name of the instrumenting/instrumented library/package/project.
     * @see https://github.com/open-telemetry/opentelemetry-specification/blob/v1.12.0/specification/glossary.md#instrumentation-scope
     * @see https://github.com/open-telemetry/opentelemetry-specification/blob/v1.12.0/specification/glossary.md#instrumentation-library
     */
    abstract public function getName(): string;

    /**
     * The version of the instrumenting/instrumented library/package/project.
     * If unknown or a lookup is too expensive simply return NULL.
     * @see https://github.com/open-telemetry/opentelemetry-specification/blob/v1.12.0/specification/glossary.md#instrumentation-scope
     * @see https://github.com/open-telemetry/opentelemetry-specification/blob/v1.12.0/specification/glossary.md#instrumentation-library
     */
    abstract public function getVersion(): ?string;

    /**
     * The version of the instrumenting/instrumented library/package/project.
     * If unknown simply return NULL.
     * @see https://github.com/open-telemetry/opentelemetry-specification/blob/v1.12.0/specification/glossary.md#instrumentation-scope
     * @see https://github.com/open-telemetry/opentelemetry-specification/blob/v1.12.0/specification/glossary.md#instrumentation-library
     */
    abstract public function getSchemaUrl(): ?string;

    /**
     * This method will be called from the API when the instrumentation has been activated (via activate()).
     * Here you can put any bootstrapping code needed by the instrumentation.
     * If not needed simply implement a method which returns TRUE.
     */
    abstract public function init(): bool;

    /**
     * This method registers and activates the instrumentation with the OpenTelemetry API/SDK and thus
     * the instrumentation will be used to generate telemetry data.
     */
    public function activate(): bool
    {
        $this->validateImplementation();
        // activate instrumentation with the API. not implemented yet.
        return true;
    }

    public function setPropagator(TextMapPropagatorInterface $propagator): void
    {
        $this->propagator = $propagator;
    }

    public function getPropagator(): TextMapPropagatorInterface
    {
        return $this->propagator;
    }

    public function setTracerProvider(TracerProviderInterface $tracerProvider): void
    {
        $this->tracerProvider = $tracerProvider;
        // @see https://github.com/open-telemetry/opentelemetry-specification/blob/v1.12.0/specification/trace/api.md#get-a-tracer
        $this->tracer = $tracerProvider->getTracer(
            $this->getName(),
            $this->getVersion(),
            $this->getSchemaUrl(),
        );
    }

    public function getTracerProvider(): TracerProviderInterface
    {
        return $this->tracerProvider;
    }

    public function getTracer(): TracerInterface
    {
        return $this->tracer;
    }

    public function setMeterProvider(MeterProviderInterface $meterProvider): void
    {
        // @see https://github.com/open-telemetry/opentelemetry-specification/blob/v1.12.0/specification/metrics/api.md#get-a-meter
        $this->meter = $meterProvider->getMeter(
            $this->getName(),
            $this->getVersion(),
        );
    }

    public function getMeter(): MeterInterface
    {
        return $this->meter;
    }

    public function setLogger(LoggerInterface $logger): void
    {
        $this->logger = $logger;
    }

    public function getLogger(): LoggerInterface
    {
        return $this->logger;
    }

    private function validateImplementation(): void
    {
        if (!$this instanceof InstrumentationInterface) {
            throw new RuntimeException(sprintf(
                '"%s" is meant to implement "%s"',
                InstrumentationTrait::class,
                InstrumentationInterface::class
            ));
        }
    }

    private function initDefaults(): void
    {
        $this->propagator = new NoopTextMapPropagator();
        $this->tracer = new NoopTracer();
        $this->tracerProvider = new NoopTracerProvider();
        /** @phan-suppress-next-line PhanAccessMethodInternal */
        $this->meter = new NoopMeter();
        $this->logger = new NullLogger();
    }
}
