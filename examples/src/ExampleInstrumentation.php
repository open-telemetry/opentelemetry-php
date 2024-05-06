<?php

declare(strict_types=1);

namespace OpenTelemetry\Example;

use Exception;
use OpenTelemetry\API\Globals;
use OpenTelemetry\API\Instrumentation\AutoInstrumentation\ConfigurationRegistry;
use OpenTelemetry\API\Instrumentation\AutoInstrumentation\HookManager;
use OpenTelemetry\API\Instrumentation\AutoInstrumentation\Instrumentation;
use OpenTelemetry\API\Trace\Span;
use OpenTelemetry\Config\SDK\Configuration\Context;
use OpenTelemetry\Context\ContextStorageInterface;

final class ExampleInstrumentation implements Instrumentation
{

    public function register(HookManager $hookManager, ?Context $context, ConfigurationRegistry $configuration, ContextStorageInterface $storage): void
    {
        $config = $configuration->get(ExampleConfig::class) ?? throw new Exception('example instrumentation must be configured');
        if (!$config->enabled) {
            return;
        }

        $tracer = $context ? $context->tracerProvider->getTracer('example-instrumentation') : Globals::tracerProvider()->getTracer('example-instrumentation');

        $hookManager->hook(
            Example::class,
            'test',
            static function () use ($tracer, $config, $storage): void {
                $context = $storage->current();

                $span = $tracer
                    ->spanBuilder($config->spanName)
                    ->setParent($context)
                    ->startSpan();

                $storage->attach($span->storeInContext($context));
            },
            static function () use ($storage): void {
                if (!$scope = $storage->scope()) {
                    return;
                }

                $scope->detach();

                $span = Span::fromContext($scope->context());
                $span->end();
            }
        );
    }
}
