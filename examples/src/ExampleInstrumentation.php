<?php

declare(strict_types=1);

namespace OpenTelemetry\Example;

use OpenTelemetry\API\Configuration\ConfigProperties;
use OpenTelemetry\API\Instrumentation\AutoInstrumentation\Context as InstrumentationContext;
use OpenTelemetry\API\Instrumentation\AutoInstrumentation\HookManagerInterface;
use OpenTelemetry\API\Instrumentation\AutoInstrumentation\Instrumentation;
use OpenTelemetry\API\Trace\Span;
use OpenTelemetry\Context\Context;

final class ExampleInstrumentation implements Instrumentation
{

    public function register(HookManagerInterface $hookManager, ConfigProperties $configuration, InstrumentationContext $context): void
    {
        $config = $configuration->get(ExampleConfig::class) ?? new ExampleConfig('example');
        if (!$config->enabled) {
            return;
        }

        $tracer = $context->tracerProvider->getTracer('example-instrumentation');

        $hookManager->hook(
            Example::class,
            'test',
            static function () use ($tracer, $config): void {
                $context = Context::getCurrent();

                $span = $tracer
                    ->spanBuilder($config->spanName)
                    ->setParent($context)
                    ->startSpan();

                Context::storage()->attach($span->storeInContext($context));
            },
            static function (): void {
                if (!$scope = Context::storage()->scope()) {
                    return;
                }

                $scope->detach();

                $span = Span::fromContext($scope->context());
                $span->end();
            }
        );
    }
}
