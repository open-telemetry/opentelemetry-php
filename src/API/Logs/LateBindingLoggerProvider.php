<?php

declare(strict_types=1);

namespace OpenTelemetry\API\Logs;

use Closure;

class LateBindingLoggerProvider implements LoggerProviderInterface
{
    private ?LoggerProviderInterface $loggerProvider = null;

    /** @param Closure(): LoggerProviderInterface $factory */
    public function __construct(
        private readonly Closure $factory,
    ) {
    }

    #[\Override]
    public function getLogger(string $name, ?string $version = null, ?string $schemaUrl = null, iterable $attributes = []): LoggerInterface
    {
        return $this->loggerProvider?->getLogger($name, $version, $schemaUrl, $attributes)
            ?? new LateBindingLogger(fn (): LoggerInterface => ($this->loggerProvider ??= ($this->factory)())->getLogger($name, $version, $schemaUrl, $attributes));
    }
}
