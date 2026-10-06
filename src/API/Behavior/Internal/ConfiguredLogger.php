<?php

declare(strict_types=1);

namespace OpenTelemetry\API\Behavior\Internal;

use Psr\Log\LoggerInterface;
use Psr\Log\LoggerTrait;

/**
 * Writes internal log messages through {@see Logging::logWriter()}, filtered by a level held on the
 * instance rather than by the global minimum. This lets a configured `log_level` apply to messages
 * emitted while the SDK is being created, before {@see Logging::setLogLevel()} has been called.
 *
 * A null level defers to {@see Logging::logLevel()}, so an unconfigured logger behaves exactly as
 * {@see \OpenTelemetry\API\Behavior\LogsMessagesTrait} does.
 */
final class ConfiguredLogger implements LoggerInterface
{
    use LoggerTrait;

    /**
     * @param 'debug'|'info'|'notice'|'warning'|'error'|'critical'|'alert'|'emergency'|'none'|null $level
     */
    public function __construct(
        private readonly ?string $level = null,
    ) {
    }

    /**
     * @psalm-suppress MoreSpecificImplementedParamType
     */
    #[\Override]
    public function log($level, $message, array $context = []): void
    {
        $threshold = $this->level !== null ? Logging::level($this->level) : Logging::logLevel();
        if (Logging::level((string) $level) >= $threshold) {
            Logging::logWriter()->write((string) $level, (string) $message, $context);
        }
    }
}
