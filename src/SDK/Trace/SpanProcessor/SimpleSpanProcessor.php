<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Trace\SpanProcessor;

use Closure;
use OpenTelemetry\API\Behavior\LogsMessagesTrait;
use OpenTelemetry\Context\Context;
use OpenTelemetry\Context\ContextInterface;
use OpenTelemetry\SDK\Common\Future\CancellationInterface;
use OpenTelemetry\SDK\Trace\ReadableSpanInterface;
use OpenTelemetry\SDK\Trace\ReadWriteSpanInterface;
use OpenTelemetry\SDK\Trace\SpanExporterInterface;
use OpenTelemetry\SDK\Trace\SpanProcessorInterface;
use SplQueue;
use function sprintf;
use Throwable;

class SimpleSpanProcessor implements SpanProcessorInterface
{
    use LogsMessagesTrait;
    private ContextInterface $exportContext;

    private bool $running = false;
    /** @var SplQueue<array{Closure, string, bool, ContextInterface}> */
    private SplQueue $queue;

    private bool $closed = false;

    public function __construct(private readonly SpanExporterInterface $exporter)
    {
        $this->exportContext = Context::getCurrent();
        $this->queue = new SplQueue();
    }

    public function onStart(ReadWriteSpanInterface $span, ContextInterface $parentContext): void
    {
    }

    public function onEnd(ReadableSpanInterface $span): void
    {
        if ($this->closed) {
            return;
        }
        if (!$span->getContext()->isSampled()) {
            return;
        }

        $spanData = $span->toSpanData();
        $this->flush(fn () => $this->exporter->export([$spanData])->await(), 'export', false, $this->exportContext);
    }

    public function forceFlush(?CancellationInterface $cancellation = null): bool
    {
        if ($this->closed) {
            return false;
        }

        return $this->flush(fn (): bool => $this->exporter->forceFlush($cancellation), __FUNCTION__, true, Context::getCurrent());
    }

    public function shutdown(?CancellationInterface $cancellation = null): bool
    {
        if ($this->closed) {
            return false;
        }

        $this->closed = true;

        return $this->flush(fn (): bool => $this->exporter->shutdown($cancellation), __FUNCTION__, true, Context::getCurrent());
    }

    private function flush(Closure $task, string $taskName, bool $propagateResult, ContextInterface $context): bool
    {
        $this->queue->enqueue([$task, $taskName, $propagateResult && !$this->running, $context]);

        if ($this->running) {
            return false;
        }

        $success = true;
        $exception = null;
        $this->running = true;

        try {
            while (!$this->queue->isEmpty()) {
                [$task, $taskName, $propagateResult, $context] = $this->queue->dequeue();
                $scope = $context->activate();

                try {
                    $result = $task();
                    if ($propagateResult) {
                        $success = $result;
                    }
                } catch (Throwable $e) {
                    if ($propagateResult) {
                        $exception = $e;
                    } else {
                        self::logError(sprintf('Unhandled %s error', $taskName), ['exception' => $e]);
                    }
                } finally {
                    $scope->detach();
                }
            }
        } finally {
            $this->running = false;
        }

        if ($exception !== null) {
            throw $exception;
        }

        return $success;
    }
}
