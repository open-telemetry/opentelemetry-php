<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Common\Instrumentation;

use Nevay\SPI\ServiceLoader;
use OpenTelemetry\API\Instrumentation\InstrumentationInterface;
use Throwable;

/**
 * @internal
 * @phan-file-suppress PhanUndeclaredClassMethod
 */
final class AutoInstrument
{
    public static function bootstrap(): void
    {
        /** @var InstrumentationInterface $instrumentation */
        foreach (ServiceLoader::load(InstrumentationInterface::class) as $instrumentation) {
            try {
                if ($instrumentation->init()) {
                    $instrumentation->activate();
                }
            } catch (Throwable $e) {
                trigger_error("Error during opentelemetry auto-instrumentation: {$e->getMessage()}", E_USER_WARNING);
            }
        }
    }
}
