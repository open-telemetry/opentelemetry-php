<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Common\Dev\Compatibility\BC;

use OpenTelemetry\SDK\Common\Attribute\AttributeLimitsInterface as Moved;

interface AttributeLimitsInterface extends Moved
{
}

/**
 * @codeCoverageIgnoreStart
 */
class_alias(AttributeLimitsInterface::class, 'OpenTelemetry\SDK\AttributeLimitsInterface');
/**
 * @codeCoverageIgnoreEnd
 */
