<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Metrics\AttributeProcessor;

use function array_map;
use OpenTelemetry\Context\ContextInterface;
use OpenTelemetry\SDK\Common\Attribute\Attributes;
use OpenTelemetry\SDK\Common\Attribute\AttributesInterface;
use OpenTelemetry\SDK\Metrics\AttributeProcessorInterface;
use function preg_match;
use function preg_quote;
use function sprintf;
use function strtr;

/**
 * Keeps attributes whose key matches an included pattern (any key if `$included` is null) and no
 * excluded pattern. `?` matches one character and `*` any sequence.
 *
 * @internal
 */
final class IncludeExcludeAttributeProcessor implements AttributeProcessorInterface
{
    /** @var list<string>|null */
    private readonly ?array $included;
    /** @var list<string> */
    private readonly array $excluded;

    /**
     * @param list<string>|null $included
     * @param list<string>|null $excluded
     */
    public function __construct(?array $included, ?array $excluded)
    {
        $this->included = $included !== null ? array_map(self::regex(...), $included) : null;
        $this->excluded = array_map(self::regex(...), $excluded ?? []);
    }

    #[\Override]
    public function process(AttributesInterface $attributes, ContextInterface $context): AttributesInterface
    {
        $filtered = [];
        foreach ($attributes as $key => $value) {
            if ($this->included !== null && !self::matchesAny($this->included, (string) $key)) {
                continue;
            }
            if (self::matchesAny($this->excluded, (string) $key)) {
                continue;
            }
            $filtered[$key] = $value;
        }

        return new Attributes($filtered, 0);
    }

    private static function regex(string $pattern): string
    {
        return sprintf('/^%s$/s', strtr(preg_quote($pattern, '/'), ['\\?' => '.', '\\*' => '.*']));
    }

    /**
     * @param list<string> $regexes
     */
    private static function matchesAny(array $regexes, string $key): bool
    {
        foreach ($regexes as $regex) {
            if (preg_match($regex, $key)) {
                return true;
            }
        }

        return false;
    }
}
