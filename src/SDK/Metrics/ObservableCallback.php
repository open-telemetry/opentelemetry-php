<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Metrics;

use OpenTelemetry\API\Metrics\ObservableCallbackInterface;

final class ObservableCallback implements ObservableCallbackInterface
{
    private MetricObserverInterface $metricObserver;
    private ?int $token;

    public function __construct(MetricObserverInterface $metricObserver, int $token)
    {
        $this->metricObserver = $metricObserver;
        $this->token = $token;
    }

    public function detach(): void
    {
        if ($this->token === null) {
            return;
        }

        $this->metricObserver->cancel($this->token);
        $this->token = null;
    }
}
