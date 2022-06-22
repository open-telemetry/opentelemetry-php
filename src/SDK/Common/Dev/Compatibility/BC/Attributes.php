<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Common\Dev\Compatibility\BC;

use OpenTelemetry\SDK\Common\Attribute\Attributes as Moved;
use OpenTelemetry\SDK\Common\Dev\Compatibility\Util;

/**
 * @codeCoverageIgnoreStart
 */
const OpenTelemetry_SDK_Attributes = 'OpenTelemetry\SDK\Attributes';

final class Attributes extends Moved implements AttributesInterface
{
    public function __construct(iterable $attributes = [], AttributeLimitsInterface $attributeLimits = null)
    {
        // @phan-suppress-next-line PhanAccessMethodInternal
        parent::__construct($attributes, $attributeLimits);

        Util::triggerClassDeprecationNotice(
            OpenTelemetry_SDK_Attributes,
            Moved::class
        );
    }
}

class_alias(Attributes::class, OpenTelemetry_SDK_Attributes);
/**
 * @codeCoverageIgnoreEnd
 */
