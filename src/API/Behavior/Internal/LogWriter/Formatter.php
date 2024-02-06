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
            $previous = $exception->getPrevious() ? $exception->getPrevious()->getMessage() : '';
            $message = sprintf(
                'OpenTelemetry: [%s] %s [exception] %s [previous] %s%s%s',
                $level,
                $message,
                $exception->getMessage(),
                $previous,
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
