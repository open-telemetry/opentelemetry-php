<?php declare(strict_types=1);
namespace OpenTelemetry\API\Metrics;

interface ObservableUpDownCounter {

    /**
     * @param callable(Observer): void $callback function responsible for
     *        reporting the measurements (as absolute values)
     * @return ObservableCallback token to detach callback
     */
    public function observe(callable $callback): ObservableCallback;
}
