<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Common\Future;

use Closure;

final class NullCancellation implements CancellationInterface
{
    public function subscribe(Closure $callback): string
    {
        return self::class;
    }

    public function unsubscribe(string $id): void
    {
        // no-op
    }
}
