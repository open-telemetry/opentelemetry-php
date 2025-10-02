<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Trace\SpanSuppression\SemanticConventionSuppressionStrategy;

use function preg_quote;
use function sprintf;
use function strcspn;
use function strlen;

/**
 * @internal
 */
final class WildcardPattern
{
    private array $static = [];
    private array $patterns = [];

    public function add(string $pattern): void
    {
        if (strcspn($pattern, '*?') === strlen($pattern)) {
            $this->static[$pattern] = true;

            return;
        }

        $this->patterns[] = sprintf('/^%s$/', strtr(preg_quote($pattern, '/'), ['\\?' => '.', '\\*' => '.*']));
    }

    public function matches(string $value): bool
    {
        if (isset($this->static[$value])) {
            return true;
        }

        foreach ($this->patterns as $pattern) {
            if (preg_match($pattern, $value)) {
                return true;
            }
        }

        return false;
    }
}
