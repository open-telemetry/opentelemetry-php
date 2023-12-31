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
     * @param ArrayAccess<object, BatchObservableCallbackDestructor> $destructors
     * @param non-empty-list<Instrument> $instruments
     * @param list<ReferenceCounterInterface> $referenceCounters
     */
    public static function batchObserve(
        MetricWriterInterface $writer,
        ArrayAccess $destructors,
        callable $callback,
        array $instruments,
        array $referenceCounters
    ): ObservableCallbackInterface {
        $target = null;
        $callback = weaken(closure($callback), $target);

        $callbackId = $writer->registerCallback($callback, ...$instruments);
        foreach ($referenceCounters as $referenceCounter) {
            $referenceCounter->acquire();
        }

        $destructor = null;
        if ($target) {
            $destructor = $destructors[$target] ??= new BatchObservableCallbackDestructor($destructors, $writer);
            $destructor->callbackIds[$callbackId] = $referenceCounters;
        }

        return new BatchObservableCallback($writer, $referenceCounters, $callbackId, $destructor, $target);
    }

    /**
     * @param ArrayAccess<object, ObservableCallbackDestructor> $destructors
     */
    public static function observe(
        MetricWriterInterface $writer,
        ArrayAccess $destructors,
        callable $callback,
        Instrument $instrument,
        ReferenceCounterInterface $referenceCounter
    ): ObservableCallbackInterface {
        $target = null;
        $callback = weaken(closure($callback), $target);

        $callbackId = $writer->registerCallback($callback, $instrument);
        $referenceCounter->acquire();

        $destructor = null;
        if ($target) {
            $destructor = $destructors[$target] ??= new ObservableCallbackDestructor($destructors, $writer);
            $destructor->callbackIds[$callbackId] = $referenceCounter;
        }

        return new ObservableCallback($writer, $referenceCounter, $callbackId, $destructor, $target);
    }
}
