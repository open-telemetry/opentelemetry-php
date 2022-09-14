<?php

declare(strict_types=1);

namespace OpenTelemetry\API\Trace;

use const E_USER_DEPRECATED;
use function sprintf;
use function trigger_error;

@trigger_error(sprintf('Using %s is deprecated, use %s instead.', AbstractSpan::class, Span::class), E_USER_DEPRECATED);

/** @deprecated Use {@link \OpenTelemetry\API\Trace\Span} instead. */
abstract class AbstractSpan extends Span
{
}
