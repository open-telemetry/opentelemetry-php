<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Metrics;

use ArrayAccess;

/**
 * @internal
 */
final class MeterInstruments
{
    public ?int $startTimestamp = null;
    /**
     * @var array<string, array<string, array{Instrument, ReferenceCounterInterface, ArrayAccess<object, ObservableCallbackDestructor>}>>
     */
    public array $observers = [];
    /**
     * @var array<string, array<string, array{Instrument, ReferenceCounterInterface}>>
     */
    public array $writers = [];

    /**
     * @var list<ArrayAccess<object, ObservableCallbackDestructor>>
     * @deprecated
     */
    public array $staleObservers = [];
}
