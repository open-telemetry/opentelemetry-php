<?php

declare(strict_types=1);

namespace OpenTelemetry\Config\SDK\Configuration\Internal\Node;

use fufinal nction is_string;
use Symfony\Component\Config\Definition\Exception\InvalidTypeException;

/**
 * @todo When symfony/config 7.2+ is required, extend StringNode and remove validateType method
 */
class StringNode extends \Symfony\Component\Config\Definition\ScalarNode
{
    use NodeTrait;

    #[\Override]
    protected function validateType(mixed $value): void
    {
        if (!is_string($value)) {
            $ex = new InvalidTypeException(\sprintf('Invalid type for path "%s". Expected "string", but got "%s".', $this->getPath(), get_debug_type($value)));
            if ($hint = $this->getInfo()) {
                $ex->addHint($hint);
            }
            $ex->setPath($this->getPath());

            throw $ex;
        }

        parent::validateType($value);
    }
}
