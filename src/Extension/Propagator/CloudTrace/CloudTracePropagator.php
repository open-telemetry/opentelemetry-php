<?php

declare(strict_types=1);

namespace OpenTelemetry\Extension\Propagator\CloudTrace;

use OpenTelemetry\API\Trace\Span;
use OpenTelemetry\Context\Context;
use OpenTelemetry\Context\ContextInterface;
use OpenTelemetry\Context\Propagation\ArrayAccessGetterSetter;
use OpenTelemetry\Context\Propagation\PropagationGetterInterface;
use OpenTelemetry\Context\Propagation\PropagationSetterInterface;
use OpenTelemetry\Context\Propagation\TextMapPropagatorInterface;

/**
 * CloudTracePropagator is a propagator that supports the specification for the X-Cloud-Trace-Context
 * header used for trace context propagation across service boundaries.
 * (https://cloud.google.com/trace/docs/setup#force-trace)
 */
final class CloudTracePropagator implements TextMapPropagatorInterface
{
    private static ?TextMapPropagatorInterface $oneWayInstance = null;
    private static ?TextMapPropagatorInterface $instance = null;

    public static function getOneWayInstance(): TextMapPropagatorInterface
    {
        if (self::$oneWayInstance === null) {
            self::$oneWayInstance = new CloudTracePropagator(true);
        }

        return self::$oneWayInstance;
    }

    public static function getInstance(): TextMapPropagatorInterface
    {
        if (self::$instance === null) {
            self::$instance = new CloudTracePropagator(false);
        }

        return self::$instance;
    }

    private const XCLOUD = 'x-cloud-trace-context';

    private const FIELDS = [
        self::XCLOUD,
    ];

    private function __construct(private readonly bool $oneWay)
    {
    }

    /** {@inheritdoc} */
    #[\Override]
    public function fields(): array
    {
        return self::FIELDS;
    }

    /** {@inheritdoc} */
    #[\Override]
    public function inject(&$carrier, ?PropagationSetterInterface $setter = null, ?ContextInterface $context = null): void
    {
        if ($this->oneWay) {
            return;
        }

        $setter ??= ArrayAccessGetterSetter::getInstance();
        $context ??= Context::getCurrent();
        $spanContext = Span::fromContext($context)->getContext();

        if (!$spanContext->isValid()) {
            return;
        }

        $headerValue = CloudTraceFormatter::serialize($spanContext);
        $setter->set($carrier, self::XCLOUD, $headerValue);
    }

    /** {@inheritdoc} */
    #[\Override]
    public function extract($carrier, ?PropagationGetterInterface $getter = null, ?ContextInterface $context = null): ContextInterface
    {
        $getter ??= ArrayAccessGetterSetter::getInstance();
        $context ??= Context::getCurrent();

        $headerValue = $getter->get($carrier, self::XCLOUD);
        if ($headerValue === null) {
            return $context;
        }

        $spanContext = CloudTraceFormatter::deserialize($headerValue);
        if (!$spanContext->isValid()) {
            return $context;
        }

        return $context->withContextValue(Span::wrap($spanContext));
    }
}
