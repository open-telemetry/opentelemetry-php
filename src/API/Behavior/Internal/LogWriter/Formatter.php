<?php

declare(strict_types=1);

namespace OpenTelemetry\API\Behavior\Internal\LogWriter;

class Formatter
{
    public static function format($level, string $message, array $context): string
    {
        $exception = (array_key_exists('exception', $context) && $context['exception'] instanceof \Throwable)
            ? $context['exception']
            : null;
        if ($exception) {
            $message = sprintf(
                'OpenTelemetry: [%s] %s [exception] %s%s%s',
                $level,
                $message,
                $exception->getMessage(),
                PHP_EOL,
                $exception->getTraceAsString()
            );
        } else {
            //get calling location, skipping over trait, formatter etc
            $caller = debug_backtrace()[3];
            $message = sprintf(
                'OpenTelemetry: [%s] %s in %s(%s)',
                $level,
                $message,
                $caller['file'],
                $caller['line'],
            );
        }

        return $message;
    }
}
