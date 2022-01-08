<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK;

use ArrayAccess;

/**
 * @template-extends ArrayAccess<non-empty-string, bool|int|float|string|array|null>
 */
interface AttributesBuilderInterface extends ArrayAccess
{
    public function build(): AttributesInterface;
}
