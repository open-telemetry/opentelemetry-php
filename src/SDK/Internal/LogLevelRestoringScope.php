<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Internal;

use OpenTelemetry\API\Behavior\Internal\Logging;
use OpenTelemetry\Context\ScopeInterface;

/**
 * Gives a log level set by {@see \OpenTelemetry\SDK\SdkBuilder::buildAndRegisterGlobal()} the same
 * lifetime as the global registration it accompanies: the level is process-global, so leaving it in
 * place after detaching would outlive the SDK that asked for it.
 *
 * @internal
 */
final class LogLevelRestoringScope implements ScopeInterface
{
    public function __construct(
        private readonly ScopeInterface $scope,
        private readonly int $previousLogLevel,
    ) {
    }

    #[\Override]
    public function detach(): int
    {
        try {
            return $this->scope->detach();
        } finally {
            Logging::restoreLogLevel($this->previousLogLevel);
        }
    }
}
