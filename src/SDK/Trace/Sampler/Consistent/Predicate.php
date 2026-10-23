<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Trace\Sampler\Consistent;

use function array_map;
use function array_values;
use Closure;
use function implode;
use function in_array;
use function is_array;
use function is_bool;
use OpenTelemetry\Context\ContextInterface;
use OpenTelemetry\SDK\Common\Attribute\AttributesInterface;
use OpenTelemetry\SDK\Trace\LinkInterface;
use OpenTelemetry\SDK\Trace\Span;
use function preg_match;
use function preg_quote;
use function sprintf;
use function strtr;

/**
 * Closure based {@see PredicateInterface} with named constructors for the common conditions.
 *
 * @experimental
 */
final class Predicate implements PredicateInterface
{
    /**
     * @param Closure(ContextInterface, string, int, AttributesInterface, list<LinkInterface>): bool $fn
     */
    public function __construct(
        private readonly Closure $fn,
        private readonly string $description = '',
    ) {
    }

    #[\Override]
    public function spanMatches(
        ContextInterface $parentContext,
        string $spanName,
        int $spanKind,
        AttributesInterface $attributes,
        array $links,
    ): bool {
        return ($this->fn)($parentContext, $spanName, $spanKind, $attributes, $links);
    }

    public function __toString(): string
    {
        return $this->description;
    }

    /**
     * Matches spans without a valid parent span context.
     */
    public static function isRootSpan(): self
    {
        return new self(
            static fn (ContextInterface $ctx) => !Span::fromContext($ctx)->getContext()->isValid(),
            'isRootSpan',
        );
    }

    public static function hasRemoteParent(): self
    {
        return new self(
            static function (ContextInterface $ctx): bool {
                $sc = Span::fromContext($ctx)->getContext();

                return $sc->isValid() && $sc->isRemote();
            },
            'hasRemoteParent',
        );
    }

    /**
     * Matches spans with a valid, local parent. Root spans do not match.
     */
    public static function hasLocalSpanParent(): self
    {
        return new self(
            static function (ContextInterface $ctx): bool {
                $sc = Span::fromContext($ctx)->getContext();

                return $sc->isValid() && !$sc->isRemote();
            },
            'hasLocalSpanParent',
        );
    }

    public static function spanKindIn(int ...$spanKinds): self
    {
        return new self(
            static fn (ContextInterface $ctx, string $name, int $kind) => in_array($kind, $spanKinds, true),
            sprintf('spanKindIn(%s)', implode(',', $spanKinds)),
        );
    }

    /**
     * Matches when the attribute is present and its string representation equals one of the values.
     * For array attributes, a match of any item is a match.
     *
     * @param list<string> $values
     */
    public static function attributeValues(string $key, array $values): self
    {
        return new self(
            static function (ContextInterface $ctx, string $name, int $kind, AttributesInterface $attrs) use ($key, $values): bool {
                foreach (self::attributeStrings($attrs, $key) as $candidate) {
                    if (in_array($candidate, $values, true)) {
                        return true;
                    }
                }

                return false;
            },
            sprintf('attributeValues(%s,[%s])', $key, implode(',', $values)),
        );
    }

    /**
     * Matches when the attribute is present, its string representation matches one of the included patterns
     * (any value if there are none) and none of the excluded patterns. Patterns are case-sensitive, `?` matches a
     * single character and `*` any sequence of characters. For array attributes, a match of any item is a match.
     *
     * @param list<string> $included
     * @param list<string> $excluded
     */
    public static function attributePatterns(string $key, array $included = [], array $excluded = []): self
    {
        $includedRegexes = array_map(self::wildcardToRegex(...), $included);
        $excludedRegexes = array_map(self::wildcardToRegex(...), $excluded);

        return new self(
            static function (ContextInterface $ctx, string $name, int $kind, AttributesInterface $attrs) use ($key, $includedRegexes, $excludedRegexes): bool {
                foreach (self::attributeStrings($attrs, $key) as $candidate) {
                    if (self::matchesAny($includedRegexes, $candidate, true) && !self::matchesAny($excludedRegexes, $candidate, false)) {
                        return true;
                    }
                }

                return false;
            },
            sprintf('attributePatterns(%s,[%s],[%s])', $key, implode(',', $included), implode(',', $excluded)),
        );
    }

    public static function anySpan(): self
    {
        return new self(
            static fn () => true,
            'anySpan',
        );
    }

    public static function and(PredicateInterface $p1, PredicateInterface $p2): self
    {
        return new self(
            static fn (ContextInterface $ctx, string $name, int $kind, AttributesInterface $attrs, array $links)
                => $p1->spanMatches($ctx, $name, $kind, $attrs, $links)
                && $p2->spanMatches($ctx, $name, $kind, $attrs, $links),
            sprintf('and(%s,%s)', self::describe($p1), self::describe($p2)),
        );
    }

    public static function or(PredicateInterface $p1, PredicateInterface $p2): self
    {
        return new self(
            static fn (ContextInterface $ctx, string $name, int $kind, AttributesInterface $attrs, array $links)
                => $p1->spanMatches($ctx, $name, $kind, $attrs, $links)
                || $p2->spanMatches($ctx, $name, $kind, $attrs, $links),
            sprintf('or(%s,%s)', self::describe($p1), self::describe($p2)),
        );
    }

    /**
     * Booleans are compared as "true" and "false", everything else by its string cast.
     */
    public static function attributeValueToString(mixed $value): string
    {
        return is_bool($value) ? ($value ? 'true' : 'false') : (string) $value;
    }

    /**
     * @return list<string>
     */
    private static function attributeStrings(AttributesInterface $attributes, string $key): array
    {
        if (!$attributes->has($key)) {
            return [];
        }

        $value = $attributes->get($key);

        return array_map(
            self::attributeValueToString(...),
            is_array($value) ? array_values($value) : [$value],
        );
    }

    /**
     * @return non-empty-string
     */
    private static function wildcardToRegex(string $pattern): string
    {
        return '/\A' . strtr(preg_quote($pattern, '/'), ['\\?' => '.', '\\*' => '.*']) . '\z/s';
    }

    /**
     * @param list<non-empty-string> $regexes
     */
    private static function matchesAny(array $regexes, string $candidate, bool $defaultIfEmpty): bool
    {
        if ($regexes === []) {
            return $defaultIfEmpty;
        }
        foreach ($regexes as $regex) {
            // Unicode matching lets `?` match one character; candidates that are not valid UTF-8 make it fail,
            // so fall back to byte matching instead of silently not matching
            $result = preg_match($regex . 'u', $candidate);
            if ($result === false) {
                $result = preg_match($regex, $candidate);
            }
            if ($result === 1) {
                return true;
            }
        }

        return false;
    }

    private static function describe(PredicateInterface $p): string
    {
        return $p instanceof self ? (string) $p : $p::class;
    }
}
