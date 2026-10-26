<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Metrics\AttributeProcessor;

use OpenTelemetry\Context\ContextInterface;
use OpenTelemetry\SDK\Common\Attribute\AttributeKeyFilter;
use OpenTelemetry\SDK\Common\Attribute\Attributes;
use OpenTelemetry\SDK\Common\Attribute\AttributesInterface;
use OpenTelemetry\SDK\Metrics\AttributeProcessorInterface;

/**
 * Keeps attributes whose key matches an included pattern (any key if `$included` is null) and no
 * excluded pattern. `?` matches one character and `*` any sequence.
 *
 * @internal
 */
final class IncludeExcludeAttributeProcessor implements AttributeProcessorInterface
{
    private readonly AttributeKeyFilter $filter;

    /**
     * @param list<string>|null $included
     * @param list<string>|null $excluded
     */
    public function __construct(?array $included, ?array $excluded)
    {
        $this->filter = new AttributeKeyFilter($included, $excluded);
    }

    #[\Override]
    public function process(AttributesInterface $attributes, ContextInterface $context): AttributesInterface
    {
        $filtered = [];
        foreach ($attributes as $key => $value) {
            if ($this->filter->accepts((string) $key)) {
                $filtered[$key] = $value;
            }
        }

        return new Attributes($filtered, 0);
    }
}
