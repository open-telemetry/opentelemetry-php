<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Common\Dev\Compatibility\BC;

use OpenTelemetry\SDK\Common\Instrumentation\InstrumentationLibraryInterface as Moved;

interface InstrumentationLibraryInterface extends Moved
{

}

/**
 * @codeCoverageIgnoreStart
 */
class_alias(InstrumentationLibraryInterface::class, 'OpenTelemetry\SDK\InstrumentationLibraryInterface');
/**
 * @codeCoverageIgnoreEnd
 */
