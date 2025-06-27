<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Trace;

use OpenTelemetry\SDK\Common\Configuration\Configuration;
use OpenTelemetry\SDK\Common\Configuration\KnownValues as Values;
use OpenTelemetry\SDK\Common\Configuration\Variables as Env;
use OpenTelemetry\SDK\Common\Services\Loader;
use OpenTelemetry\SDK\Trace\SpanProcessor\MultiSpanProcessor;
use OpenTelemetry\SDK\Trace\SpanProcessor\NoopSpanProcessor;
use OpenTelemetry\SDK\Trace\SpanProcessor\SpanProcessorContext;

class SpanProcessorFactory
{
    public function create(SpanProcessorContext $context): SpanProcessorInterface
    {
        $names = Configuration::getList(Env::OTEL_PHP_TRACES_PROCESSOR);
        $names = array_filter($names, static function (string $name): bool {
            return !in_array($name, [Values::VALUE_NOOP, Values::VALUE_NONE], true);
        });
        if ($names === []) {
            return new NoopSpanProcessor();
        }
        if (count($names) === 1) {
            return Loader::spanProcessor($names[0], $context);
        }

        $processors = [];
        foreach ($names as $name) {
            $processors[] = Loader::spanProcessor($name, $context);
        }

        return new MultiSpanProcessor(...$processors);

    }
}
