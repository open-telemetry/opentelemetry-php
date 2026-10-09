<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Trace\Sampler\Consistent;

use Closure;
use OpenTelemetry\Context\ContextInterface;
use OpenTelemetry\SDK\Common\Attribute\AttributesInterface;
use OpenTelemetry\SDK\Trace\LinkInterface;
use OpenTelemetry\SDK\Trace\Span;

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

    private static function describe(PredicateInterface $p): string
    {
        return $p instanceof self ? (string) $p : get_class($p);
    }
}
