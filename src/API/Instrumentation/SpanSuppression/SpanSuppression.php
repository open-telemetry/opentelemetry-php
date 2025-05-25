<?php

declare(strict_types=1);

namespace OpenTelemetry\API\Instrumentation\SpanSuppression;

use OpenTelemetry\Context\ContextInterface;

/**
 * @experimental
 */
interface SpanSuppression
{
    public function isSuppressed(ContextInterface $context): bool;

    public function suppress(ContextInterface $context): ContextInterface;
}
