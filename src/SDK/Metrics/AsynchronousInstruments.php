<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Metrics;

use ArrayAccess;
use OpenTelemetry\API\Metrics\ObservableCallbackInterface;
use function OpenTelemetry\SDK\Common\Util\closure;
use function OpenTelemetry\SDK\Common\Util\weaken;
use OpenTelemetry\SDK\Metrics\MetricRegistry\MetricWriterInterface;

/**
 * @internal
 */
final class AsynchronousInstruments
{
    /**
     * @param ArrayAccess<object, ObservableCallbackDestructor> $destructors
     * @param non-empty-list<Instrument> $instruments
     * @psalm-suppress PossiblyNullArgument,PossiblyNullPropertyAssignment,PossiblyNullPropertyFetch
     */
    public static function observe(
        MetricWriterInterface $writer,
        ArrayAccess $destructors,
        callable $callback,
        array $instruments,
        ReferenceCounterInterface $referenceCounter,
    ): ObservableCallbackInterface {
        $target = null;
        $callback = weaken(closure($callback), $target);

        $callbackId = $writer->registerCallback($callback, ...$instruments);
        $referenceCounter->acquire();

        $destructor = null;
        if ($target) {
            $destructor = $destructors[$target] ??= new ObservableCallbackDestructor($destructors, $writer);
            $destructor->callbackIds[$callbackId] = $referenceCounter;
        }

        return new ObservableCallback($writer, $referenceCounter, $callbackId, $destructor, $target);
    }
}
