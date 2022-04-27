<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Common\Dev\Compatibility\BC;

use OpenTelemetry\SDK\Common\Attribute\AttributeLimits as Moved;
use OpenTelemetry\SDK\Common\Dev\Compatibility\Util;

/**
 * @codeCoverageIgnoreStart
 */
const OpenTelemetry_SDK_AttributeLimits = 'OpenTelemetry\SDK\AttributeLimits';

final class AttributeLimits implements AttributeLimitsInterface
{
    private Moved $adapted;

    public function __construct(
        int $attributeCountLimit = AttributeLimitsInterface::DEFAULT_COUNT_LIMIT,
        int $attributeValueLengthLimit = AttributeLimitsInterface::DEFAULT_VALUE_LENGTH_LIMIT
    ) {
        $this->adapted = new Moved(
            $attributeCountLimit,
            $attributeValueLengthLimit
        );
        Util::triggerClassDeprecationNotice(
            OpenTelemetry_SDK_AttributeLimits,
            Moved::class
        );
    }

    public function getAttributeCountLimit(): int
    {
        return $this->adapted->getAttributeCountLimit();
    }

    public function getAttributeValueLengthLimit(): int
    {
        return $this->adapted->getAttributeValueLengthLimit();
    }
}

class_alias(AttributeLimits::class, OpenTelemetry_SDK_AttributeLimits);
/**
 * @codeCoverageIgnoreEnd
 */
