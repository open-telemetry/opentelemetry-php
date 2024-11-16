<?php

declare(strict_types=1);

namespace OpenTelemetry\API\Instrumentation;

use Attribute;

/**
 * For function and methods that have the {@link WithSpan}
 * attribute, adding this attribute to an argument will
 * add the argument as a span attribute.
 */
#[Attribute(Attribute::TARGET_PARAMETER | Attribute::TARGET_PROPERTY)]
final class SpanAttribute
{
    /**
     * @param string|null $name Optional name to use for the attribute. Default: argument name.
     */
    public function __construct(
        public readonly ?string $name = null,
    ) {
    }
}
