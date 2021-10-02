<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Trace;

use ArrayAccess;

/**
 * Extension of {@see ArrayAccess} that also exposes the keys of the array-like object.
 */
interface KeyedArrayAccessInterface extends ArrayAccess
{
    /** @return list<string> */
    public function keys(): array;
}
