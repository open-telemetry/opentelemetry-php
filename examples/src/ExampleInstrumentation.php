<?php

declare(strict_types=1);

namespace OpenTelemetry\Example;

use Exception;
use OpenTelemetry\API\Globals;
use OpenTelemetry\API\Instrumentation\AutoInstrumentation\ConfigurationRegistry;
use OpenTelemetry\API\Instrumentation\AutoInstrumentation\Context as InstrumentationContext;
use OpenTelemetry\API\Instrumentation\AutoInstrumentation\HookManager;
use OpenTelemetry\API\Instrumentation\AutoInstrumentation\Instrumentation;
use OpenTelemetry\API\Trace\Span;
use OpenTelemetry\Context\Context;

final class ExampleInstrumentation implements Instrumentation
{

    /**
     * @todo can we pass in just the config for _this_ instrumentation, rather than all?
     */
    public function register(HookManager $hookManager, ConfigurationRegistry $configuration, InstrumentationContext $context): void
    {
        $config = $configuration->get(ExampleConfig::class) ?? throw new Exception('example instrumentation must be configured');
        if (!$config->enabled) {
            return;
        }

        $tracer = Globals::tracerProvider()->getTracer('example-instrumentation');

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
