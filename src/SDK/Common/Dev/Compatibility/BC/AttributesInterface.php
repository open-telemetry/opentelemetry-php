<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Common\Dev\Compatibility\BC;

use OpenTelemetry\SDK\Common\Attribute\AttributesInterface as Moved;

interface AttributesInterface extends Moved
{
}

/**
 * @codeCoverageIgnoreStart
 */
class_alias(AttributesInterface::class, 'OpenTelemetry\SDK\AttributesInterface');
/**
 * @codeCoverageIgnoreEnd
 */
