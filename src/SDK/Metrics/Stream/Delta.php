<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Metrics\Stream;

use GMP;

/**
 * @internal
 * @phan-file-suppress PhanUndeclaredTypeParameter, PhanUndeclaredTypeProperty
 */
final class Delta
{
    public function __construct(
        public Metric $metric,
        public int|GMP $readers,
        public ?self $prev = null,
    ) {
    }
}
