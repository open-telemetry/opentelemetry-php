<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Common\Attribute;

interface AttributeLimitsInterface
{
    public const DEFAULT_COUNT_LIMIT = 128;
    public const DEFAULT_VALUE_LENGTH_LIMIT = PHP_INT_MAX;

    /** @return int Maximum allowed attribute count */
    public function getAttributeCountLimit(): int;

    /** @return int Maximum allowed attribute value length */
    public function getAttributeValueLengthLimit(): int;
}
