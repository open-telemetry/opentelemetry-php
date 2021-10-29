<?php

declare(strict_types=1);

namespace OpenTelemetry\Context;

use const PHP_INT_SIZE;

interface ScopeInterface
{
    public const DETACHED = 1 << (PHP_INT_SIZE << 3) - 1;
    public const INACTIVE = 1 << (PHP_INT_SIZE << 3) - 2;
    public const MISMATCH = 1 << (PHP_INT_SIZE << 3) - 3;

    /**
     * Detaches the context associated with this scope and restores the
     * previously associated context.
     *
     * @return int zero indicating an expected call, or a non-zero value
     *         indicating that the call was unexpected
     *
     * @see self::DETACHED
     * @see self::INACTIVE
     * @see self::MISMATCH
     *
     * @see https://github.com/open-telemetry/opentelemetry-specification/blob/v1.7.0/specification/context/context.md#detach-context
     */
    public function detach(): int;
}
