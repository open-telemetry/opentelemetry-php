<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK;

use ArrayAccess;

interface AttributesBuilderInterface extends ArrayAccess
{
    public function build(): AttributesInterface;
}
