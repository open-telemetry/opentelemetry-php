<?php declare(strict_types=1);
namespace OpenTelemetry\SDK\Metrics;

use Closure;
use OpenTelemetry\API\Metrics\ObservableCallback;
use OpenTelemetry\API\Metrics\ObservableGauge;

final class SdkObservableGauge implements ObservableGauge {

    private MetricObserver $metricObserver;
    private ReferenceCounter $referenceCounter;

    public function __construct(MetricObserver $metricObserver, ReferenceCounter $referenceCounter) {
        $this->metricObserver = $metricObserver;
        $this->referenceCounter = $referenceCounter;

        $this->referenceCounter->acquire();
    }

    public function __destruct() {
        $this->referenceCounter->release();
    }

    public function observe(callable $callback): ObservableCallback {
        $token = $this->metricObserver->observe(Closure::fromCallable($callback));

        return new SdkObservableCallback($this->metricObserver, $token);
    }
}
