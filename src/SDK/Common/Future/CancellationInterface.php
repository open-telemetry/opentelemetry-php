<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Common\Future;

use Closure;
use Throwable;

interface CancellationInterface
{
    /**
     * @param Closure(Throwable): void $callback
     */
    public function subscribe(Closure $callback): string;

    public function unsubscribe(string $id): void;
}
