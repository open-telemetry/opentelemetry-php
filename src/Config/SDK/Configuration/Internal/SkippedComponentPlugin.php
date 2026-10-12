<?php

declare(strict_types=1);

namespace OpenTelemetry\Config\SDK\Configuration\Internal;

use function array_map;
use function implode;
use function json_encode;
use OpenTelemetry\API\Configuration\Context;
use Psr\Log\LogLevel;
use function sprintf;

/**
 * Stands in for a component whose provider is not registered, where the spec calls for the reference
 * to be warned about and skipped rather than treated as an error.
 *
 * The warning is emitted on creation rather than while parsing, so that it goes through
 * {@see Context::$logger} and observes a configured `log_level`. Creation yields null, which callers
 * filter out of the surrounding list or map.
 *
 * @implements \OpenTelemetry\API\Configuration\Config\ComponentPlugin<null>
 *
 * @internal
 */
final class SkippedComponentPlugin implements \OpenTelemetry\API\Configuration\Config\ComponentPlugin
{
    /**
     * @param list<string> $knownNames
     */
    public function __construct(
        private readonly string $node,
        private readonly string $name,
        private readonly array $knownNames,
    ) {
    }

    #[\Override]
    public function create(Context $context): mixed
    {
        $context->logger->log(LogLevel::WARNING, sprintf(
            'Ignoring "%s" entry "%s": no provider is registered for it. Known entries are %s',
            $this->node,
            $this->name,
            implode(', ', array_map(json_encode(...), $this->knownNames) ?: ['none'])
        ));

        return null;
    }
}
