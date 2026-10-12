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
 * Factory for common {@see PredicateInterface} implementations backed by closures.
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

    // ─── Static factories ──────────────────────────────────────────────────────

    /**
     * Returns a predicate that matches root spans only (no valid parent span context).
     */
    public static function isRootSpan(): self
    {
        return new self(
            static fn (ContextInterface $ctx) => !Span::fromContext($ctx)->getContext()->isValid(),
            'isRootSpan',
        );
    }

    /**
     * Returns a predicate that matches spans with a local (non-remote) parent, including root spans.
     */
    public static function hasLocalParent(): self
    {
        return new self(
            static function (ContextInterface $ctx): bool {
                $sc = Span::fromContext($ctx)->getContext();

                return !$sc->isValid() || !$sc->isRemote();
            },
            'hasLocalParent',
        );
    }

    /**
     * Returns a predicate that matches spans with a remote parent.
     */
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
     * Returns a predicate that matches spans with a valid, local (non-remote) parent. Root spans do not match.
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

    /**
     * Returns a predicate that matches spans of any of the given {@see \OpenTelemetry\API\Trace\SpanKind} values.
     */
    public static function spanKindIn(int ...$spanKinds): self
    {
        return new self(
            static fn (ContextInterface $ctx, string $name, int $kind) => in_array($kind, $spanKinds, true),
            sprintf('spanKindIn(%s)', implode(',', $spanKinds)),
        );
    }

    /**
     * Returns a predicate that matches when the attribute $key is present and its string representation
     * equals one of $values. For array attributes, a match on any item is a match.
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
     * Returns a predicate that matches when the attribute $key is present and its string representation
     * matches one of the $included patterns (all values if empty) and none of the $excluded patterns.
     * Patterns are case-sensitive; '?' matches any single character and '*' any sequence of characters.
     * For array attributes, a match on any item is a match.
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

    /**
     * Returns a predicate that matches all spans.
     */
    public static function anySpan(): self
    {
        return new self(
            static fn () => true,
            'anySpan',
        );
    }

    /**
     * Returns a predicate that is true when both $p1 and $p2 match.
     */
    public static function and(PredicateInterface $p1, PredicateInterface $p2): self
    {
        return new self(
            static fn (ContextInterface $ctx, string $name, int $kind, AttributesInterface $attrs, array $links)
                => $p1->spanMatches($ctx, $name, $kind, $attrs, $links)
                && $p2->spanMatches($ctx, $name, $kind, $attrs, $links),
            sprintf('and(%s,%s)', self::describe($p1), self::describe($p2)),
        );
    }

    /**
     * Returns a predicate that is true when either $p1 or $p2 matches.
     */
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
     * Returns a predicate that negates $p.
     */
    public static function not(PredicateInterface $p): self
    {
        return new self(
            static fn (ContextInterface $ctx, string $name, int $kind, AttributesInterface $attrs, array $links)
                => !$p->spanMatches($ctx, $name, $kind, $attrs, $links),
            sprintf('not(%s)', self::describe($p)),
        );
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
     * String representation used to compare attribute values: booleans as "true"/"false", everything else cast.
     */
    public static function attributeValueToString(mixed $value): string
    {
        return is_bool($value) ? ($value ? 'true' : 'false') : (string) $value;
    }

    private static function wildcardToRegex(string $pattern): string
    {
        return '/\A' . strtr(preg_quote($pattern, '/'), ['\\?' => '.', '\\*' => '.*']) . '\z/s';
    }

    /**
     * @param list<string> $regexes
     */
    private static function matchesAny(array $regexes, string $candidate, bool $defaultIfEmpty): bool
    {
        if ($regexes === []) {
            return $defaultIfEmpty;
        }
        foreach ($regexes as $regex) {
            // Prefer unicode matching so that '?' matches one character; for candidates that are not valid UTF-8
            // preg_match() fails, so fall back to byte-wise matching instead of silently not matching.
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
        return $p instanceof self ? (string) $p : get_class($p);
    }
}
