<?php

declare(strict_types=1);

namespace OpenTelemetry\API\Metrics;

interface ObservableGaugeInterface
{

    /**
     * @param callable(ObserverInterface): void $callback function responsible for
     *        reporting the measurements
     * @param bool $weaken Create a weak reference to the callback so that it
     *        does not stop garbage collection
     * @return ObservableCallbackInterface token to detach callback
     */
    public function observe(callable $callback, bool $weaken = false): ObservableCallbackInterface;
}
