<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Logs;

use OpenTelemetry\SDK\Common\Attribute\AttributesFactoryInterface;

/**
 * @see https://github.com/open-telemetry/opentelemetry-specification/blob/main/specification/logs/sdk.md#logrecord-limits
 */
class LogRecordLimits
{
    /**
     * @internal Use {@see LogRecordLimitsBuilder} to create {@see LogRecordLimits} instance.
     */
    public function __construct(private readonly AttributesFactoryInterface $attributesFactory)
    {
    }

    public function getAttributeFactory(): AttributesFactoryInterface
    {
        return $this->attributesFactory;
    }
}
