<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Common\Dev\Compatibility\BC;

use OpenTelemetry\SDK\Common\Future\CancellationInterface;
use OpenTelemetry\SDK\Common\Future\ErrorFuture;
use OpenTelemetry\SDK\Common\Future\FutureInterface;
use OpenTelemetry\SDK\Trace\SpanExporterInterface;
use RuntimeException;

class OtlpExporter implements SpanExporterInterface
{
    private static string $message = 'Class has been replaced by Contrib\Otlp\SpanExporter + Transports';

    private static function error(): void
    {
        trigger_error(self::$message, E_USER_ERROR);
    }

    public static function fromConnectionString(string $endpointUrl, string $name, string $args)
    {
        self::error();
    }

    public function export(iterable $batch, ?CancellationInterface $cancellation = null): FutureInterface
    {
        self::error();

        return new ErrorFuture(new RuntimeException('class is deprecated'));
    }

    public function shutdown(?CancellationInterface $cancellation = null): bool
    {
        self::error();

        return false;
    }

    public function forceFlush(?CancellationInterface $cancellation = null): bool
    {
        self::error();

        return false;
    }
}

class_alias(OtlpExporter::class, 'OpenTelemetry\Contrib\OtlpGrpc\Exporter');
class_alias(OtlpExporter::class, 'OpenTelemetry\Contrib\OtlpHttp\Exporter');
class_alias(OtlpExporter::class, 'OpenTelemetry\Contrib\Otlp\Exporter');
