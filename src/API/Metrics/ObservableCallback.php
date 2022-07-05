<?php

declare(strict_types=1);

namespace OpenTelemetry\API\Metrics;

interface ObservableCallback
{

    /**
     * Detaches the associated callback from the instrument.
     */
    public function detach(): void;
}
