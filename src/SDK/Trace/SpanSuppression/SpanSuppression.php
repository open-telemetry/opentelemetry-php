<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Trace\SpanSuppression;

use OpenTelemetry\Context\ContextInterface;

/**
 * @experimental
 */
interface SpanSuppression
{
    public function isSuppressed(ContextInterface $context): bool;

    public function suppress(ContextInterface $context): ContextInterface;
}
