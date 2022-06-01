<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Common\Util;

use Throwable;

class TracingUtil
{
    /**
         * This function provides a more java-like stacktrace
         * that supports exception chaining and provides exact
         * lines of where exceptions are thrown
         *
         * Example:
         * Exception: Thrown from grandparent
         *  at grandparent_func(test.php:56)
         *  at parent_func(test.php:51)
         *  at child_func(test.php:44)
         *  at (main)(test.php:62)
         *
         * Credit: https://www.php.net/manual/en/exception.gettraceasstring.php#114980
         *
         */
    public static function formatStackTrace(Throwable $e, array &$seen = null): string
    {
        $starter = $seen ? 'Caused by: ' : '';
        $result = [];
        if (!$seen) {
            $seen = [];
        }
        $trace  = $e->getTrace();
        $prev   = $e->getPrevious();
        $result[] = sprintf('%s%s: %s', $starter, get_class($e), $e->getMessage());
        $file = $e->getFile();
        $line = $e->getLine();
        while (true) {
            $current = "$file:$line";
            if (in_array($current, $seen, true)) {
                $result[] = sprintf(' ... %d more', count($trace)+1);

                break;
            }

            // Lambda to format traces -- we want to format the trace with '.' separators
            $slashToDot = function ($str) {
                return str_replace('\\', '.', $str);
            };

            $traceHasKey = array_key_exists(0, $trace);
            $traceKeyHasClass = $traceHasKey && array_key_exists('class', $trace[0]);
            $traceKeyHasFunction = $traceKeyHasClass && array_key_exists('function', $trace[0]);

            $result[] = sprintf(
                ' at %s%s%s(%s%s%s)',
                $traceKeyHasClass ? $slashToDot($trace[0]['class']) : '',
                $traceKeyHasFunction ? '.' : '',
                $traceKeyHasFunction ? $slashToDot($trace[0]['function']) : 'main',
                $line === null ? $file : basename($file),
                $line === null ? '' : ':',
                $line ?? ''
            );
            $seen[] = "$file:$line";
            if (!count($trace)) {
                break;
            }
            $file = array_key_exists('file', $trace[0]) ? $trace[0]['file'] : 'Unknown Source';
            $line = array_key_exists('file', $trace[0]) && array_key_exists('line', $trace[0]) && $trace[0]['line'] ? $trace[0]['line'] : null;
            array_shift($trace);
        }
        $result = implode("\n", $result);
        if ($prev) {
            $result  .= "\n" . self::formatStackTrace($prev, $seen);
        }

        return $result;
    }
}
