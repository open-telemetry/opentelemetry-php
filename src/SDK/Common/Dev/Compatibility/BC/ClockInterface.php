<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Common\Dev\Compatibility\BC;

use OpenTelemetry\SDK\Common\Time\ClockInterface as Moved;

interface ClockInterface extends Moved
{
}

/**
 * @codeCoverageIgnoreStart
 */
class_alias(ClockInterface::class, 'OpenTelemetry\SDK\ClockInterface');
/**
 * @codeCoverageIgnoreEnd
 */
