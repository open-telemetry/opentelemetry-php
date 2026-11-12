<?php

declare(strict_types=1);

namespace OpenTelemetry\Contrib\Zipkin;

use OpenTelemetry\SDK\RegistrarInterface;
use OpenTelemetry\SDK\Registry;

/**
 * @internal
 */
final class Registrar implements RegistrarInterface
{
    #[\Override]
    public function register(): void
    {
        Registry::registerSpanExporterFactory('zipkin', SpanExporterFactory::class);
    }
}
