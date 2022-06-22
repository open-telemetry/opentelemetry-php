<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Common\Attribute;

use ArrayAccess;

interface AttributesBuilderInterface extends ArrayAccess
{
    public function incrementDroppedAttributesCount(int $count = 1): AttributesBuilderInterface;

    public function build(): AttributesInterface;
}
