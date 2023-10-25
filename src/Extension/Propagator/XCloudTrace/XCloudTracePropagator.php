<?php

declare(strict_types=1);

namespace OpenTelemetry\Extension\Propagator\XCloudTrace;

use OpenTelemetry\API\Trace\Span;
use OpenTelemetry\Context\Context;
use OpenTelemetry\Context\ContextInterface;
use OpenTelemetry\Context\Propagation\ArrayAccessGetterSetter;
use OpenTelemetry\Context\Propagation\PropagationGetterInterface;
use OpenTelemetry\Context\Propagation\PropagationSetterInterface;
use OpenTelemetry\Context\Propagation\TextMapPropagatorInterface;

final class XCloudTracePropagator implements TextMapPropagatorInterface
{

    private static ?TextMapPropagatorInterface $oneWayInstance = null;
    private static ?TextMapPropagatorInterface $instance = null;


    public static function getOneWayInstance(): TextMapPropagatorInterface
    {
        if (self::$oneWayInstance === null) {
            self::$oneWayInstance = new XCloudTracePropagator(true);
        }

        return self::$oneWayInstance;
    }

    public static function getInstance(): TextMapPropagatorInterface
    {
        if (self::$instance === null) {
            self::$instance = new XCloudTracePropagator(false);
        }

        return self::$instance;
    }

    private const XCLOUD = 'x-cloud-trace-context';

    private const FIELDS = [
        self::XCLOUD,
    ];

    private bool $oneWay;

    private function __construct(bool $oneWay) {
        $this->oneWay = $oneWay;
    }

    /** {@inheritdoc} */
    public function fields(): array
    {
        return self::FIELDS;
    }

    /** {@inheritdoc} */
    public function inject(&$carrier, PropagationSetterInterface $setter = null, ContextInterface $context = null): void
    {
        if($this->oneWay) {
            return;
        }

        $setter ??= ArrayAccessGetterSetter::getInstance();
        $context ??= Context::getCurrent();
        $spanContext = Span::fromContext($context)->getContext();

        if (!$spanContext->isValid()) {
            return;
        }

        $headerValue = XCloudTraceFormatter::serialize($spanContext);
        $setter->set($carrier, self::XCLOUD, $headerValue);
    }

    /** {@inheritdoc} */
    public function extract($carrier, PropagationGetterInterface $getter = null, ContextInterface $context = null): ContextInterface
    {
        $getter ??= ArrayAccessGetterSetter::getInstance();
        $context ??= Context::getCurrent();

        $headerValue = $getter->get($carrier, self::XCLOUD);
        if ($headerValue === null) {
            return $context;
        }

        $spanContext = XCloudTraceFormatter::deserialize($headerValue);
        if (!$spanContext->isValid()) {
            return $context;
        }

        return $context->withContextValue(Span::wrap($spanContext));
    }

}
