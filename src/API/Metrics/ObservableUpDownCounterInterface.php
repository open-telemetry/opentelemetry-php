<?php

declare(strict_types=1);

namespace OpenTelemetry\API\Metrics;

interface ObservableUpDownCounterInterface
{

    /**
     * @param callable(ObserverInterface): void $callback function responsible for
     *        reporting the measurements (as absolute values)
     * @return ObservableCallbackInterface token to detach callback
     */
    public function observe(callable $callback, bool $weaken = false): ObservableCallbackInterface;
}
