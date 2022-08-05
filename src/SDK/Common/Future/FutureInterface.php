<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Common\Future;

/**
 * @template T
 */
interface FutureInterface
{
    /**
     * @psalm-return T
     */
    public function await(?CancellationInterface $cancellation = null);
}
