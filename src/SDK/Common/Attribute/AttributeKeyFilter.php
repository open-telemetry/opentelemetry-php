<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Common\Attribute;

use function array_filter;
use const ARRAY_FILTER_USE_KEY;
use function array_map;
use function count;
use function preg_match;
use function preg_quote;
use function sprintf;
use function strtr;

/**
 * Accepts keys that match an included pattern (any key if `$included` is null) and no excluded
 * pattern. Matching is case-sensitive, `?` matches one character and `*` any sequence.
 *
 * @internal
 */
final class AttributeKeyFilter
{
    // Bounds memory if keys are unbounded, such as keys built from request data.
    private const MAX_CACHED_KEYS = 1024;

    /** @var list<non-empty-string>|null */
    private readonly ?array $included;
    /** @var list<non-empty-string> */
    private readonly array $excluded;
    /** @var array<string, bool> */
    private array $accepted = [];

    /**
     * @param list<string>|null $included
     * @param list<string>|null $excluded
     */
    public function __construct(?array $included, ?array $excluded)
    {
        $this->included = $included !== null ? array_map(self::regex(...), $included) : null;
        $this->excluded = array_map(self::regex(...), $excluded ?? []);
    }

    public function accepts(string $key): bool
    {
        if (isset($this->accepted[$key])) {
            return $this->accepted[$key];
        }
        if (count($this->accepted) >= self::MAX_CACHED_KEYS) {
            $this->accepted = [];
        }

        return $this->accepted[$key] = ($this->included === null || self::matchesAny($this->included, $key))
            && !self::matchesAny($this->excluded, $key);
    }

    public function filter(array $attributes): array
    {
        return array_filter($attributes, fn ($key): bool => $this->accepts((string) $key), ARRAY_FILTER_USE_KEY);
    }

    /** @return non-empty-string */
    private static function regex(string $pattern): string
    {
        return sprintf('/\A%s\z/s', strtr(preg_quote($pattern, '/'), ['\\?' => '.', '\\*' => '.*']));
    }

    /**
     * @param list<non-empty-string> $regexes
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
