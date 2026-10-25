<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Common\Util;

use function implode;

/**
 * Code in this file is part of implementation internals and thus it is not covered by the backward compatibility.
 *
 * @internal
 */
final class WildcardListMatcher
{
    /** @var WildcardMatcher[] */
    private array $matchers;

    /**
     * @param iterable<string> $wildcardExprs
     */
    public function __construct(iterable $wildcardExprs)
    {
        $this->matchers = [];
        foreach ($wildcardExprs as $wildcardExpr) {
            $this->matchers[] = new WildcardMatcher($wildcardExpr);
        }
    }

    public function match(string $text): ?string
    {
        foreach ($this->matchers as $matcher) {
            if ($matcher->match($text)) {
                return $matcher->groupName();
            }
        }

        return null;
    }

    public function __toString(): string
    {
        return implode(', ', $this->matchers);
    }
}
