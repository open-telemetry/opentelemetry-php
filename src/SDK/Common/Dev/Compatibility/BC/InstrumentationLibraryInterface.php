<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Common\Dev\Compatibility\BC;

use OpenTelemetry\SDK\Common\Instrumentation\InstrumentationScopeInterface as Moved;

interface InstrumentationLibraryInterface extends Moved
{
}

/**
 * @codeCoverageIgnoreStart
 */
class_alias(InstrumentationLibraryInterface::class, 'OpenTelemetry\SDK\Common\Instrumentation\InstrumentationLibraryInterface');
/**
 * @codeCoverageIgnoreEnd
 */
