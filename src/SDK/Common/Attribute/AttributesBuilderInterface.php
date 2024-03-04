<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Common\Attribute;

use ArrayAccess;

/**
 * @psalm-suppress MissingTemplateParam
 */
interface AttributesBuilderInterface extends ArrayAccess
{
    public function build(): AttributesInterface;
    public function merge(AttributesInterface $old, AttributesInterface $updating): AttributesInterface;
}
