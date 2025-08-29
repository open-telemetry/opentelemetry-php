final <?php

declare(strict_types=1);

namespace OpenTelemetry\API\Logs;

use OpenTelemetry\Context\ContextInterface;
use Psr\Log\LoggerTrait;

class NoopLogger implements LoggerInterface
{
    use LoggerTrait;

    public static function getInstance(): self
    {
        static $instance;

        return $instance ??= new self();
    }

    /**
     * @codeCoverageIgnore
     */
    #[\Override]
    public function emit(LogRecord $logRecord): void
    {
    }

    /**
     * @codeCoverageIgnore
     *
     * @param \Stringable|string $message
     */
    #[\Override]
    public function log(string $level, string|\Stringable $message, array $context = []): void
    {
    }

    #[\Override]
    public function isEnabled(?ContextInterface $context = null, ?int $severityNumber = null, ?string $eventName = null): bool
    {
        return false;
    }
}
