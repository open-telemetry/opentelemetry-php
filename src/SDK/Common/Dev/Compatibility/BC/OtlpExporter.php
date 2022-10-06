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
    private static string $message = 'Class has been replaced by Contrib\Otlp\Exporter + Transports';

    private static function deprecated(): void
    {
        trigger_error(self::$message, E_USER_DEPRECATED);
    }

    public static function fromConnectionString(string $endpointUrl, string $name, string $args)
    {
        self::deprecated();
    }

    public function export(iterable $spans, ?CancellationInterface $cancellation = null): FutureInterface
    {
        self::deprecated();

        return new ErrorFuture(new RuntimeException('class is deprecated'));
    }

    public function shutdown(?CancellationInterface $cancellation = null): bool
    {
        self::deprecated();

        return false;
    }

    public function forceFlush(?CancellationInterface $cancellation = null): bool
    {
        self::deprecated();

        return false;
    }
}

class_alias(OtlpExporter::class, 'OpenTelemetry\Contrib\OtlpGrpc\Exporter');
class_alias(OtlpExporter::class, 'OpenTelemetry\Contrib\OtlpHttp\Exporter');
