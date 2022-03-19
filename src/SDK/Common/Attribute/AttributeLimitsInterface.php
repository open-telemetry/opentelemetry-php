<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Common\Attribute;

interface AttributeLimitsInterface
{
    /** @return int Maximum allowed attribute count */
    public function getAttributeCountLimit(): int;

    /** @return int Maximum allowed attribute value length */
    public function getAttributeValueLengthLimit(): int;
}
