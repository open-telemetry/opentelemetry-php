<?php

declare(strict_types=1);

namespace OpenTelemetry\API\Metrics;

interface ObservableGauge
{

    /**
     * @param callable(Observer): void $callback function responsible for
     *        reporting the measurements
     * @return ObservableCallback token to detach callback
     */
    public function observe(callable $callback): ObservableCallback;
}
