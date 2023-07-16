<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Metrics;

use ArrayAccess;
use OpenTelemetry\API\Metrics\ObservableCallbackInterface;
use OpenTelemetry\API\Metrics\ObserverInterface;
use function OpenTelemetry\SDK\Common\Util\closure;
use function OpenTelemetry\SDK\Common\Util\weaken;
use OpenTelemetry\SDK\Metrics\MetricRegistry\MetricWriterInterface;

/**
 * @internal
 */
trait ObservableInstrumentTrait
{
    private MetricWriterInterface $writer;
    private Instrument $instrument;
    private ReferenceCounterInterface $referenceCounter;
    private ArrayAccess $destructors;

    public function __construct(
        MetricWriterInterface $writer,
        Instrument $instrument,
        ReferenceCounterInterface $referenceCounter,
        ArrayAccess $destructors
    ) {
        $this->writer = $writer;
        $this->instrument = $instrument;
        $this->referenceCounter = $referenceCounter;
        $this->destructors = $destructors;

        $this->referenceCounter->acquire();
    }

    public function __destruct()
    {
        $this->referenceCounter->release();
    }

    /**
     * @param callable(ObserverInterface): void $callback
     */
    public function observe(callable $callback): ObservableCallbackInterface
    {
        $callback = weaken(closure($callback), $target);

        $callbackId = $this->writer->registerCallback($callback, $this->instrument);
        $this->referenceCounter->acquire();

        $destructor = null;
        if ($target) {
            $destructor = $this->destructors[$target] ??= new ObservableCallbackDestructor($this->writer, $this->referenceCounter);
            $destructor->callbackIds[$callbackId] = $callbackId;
        }

        return new ObservableCallback($this->writer, $this->referenceCounter, $callbackId, $destructor, $target);
    }
}
