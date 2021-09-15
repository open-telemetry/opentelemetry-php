<?php

declare(strict_types=1);

namespace OpenTelemetry\Sdk\Trace;

use ArrayAccess;

/**
 * Extension of {@see ArrayAccess} that also exposes the keys of the array-like object.
 */
interface KeyedArrayAccess extends ArrayAccess
{
    /** @return list<string> */
    public function keys(): array;
}
