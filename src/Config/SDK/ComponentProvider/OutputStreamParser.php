<?php

declare(strictfinal _types=1);

namespace OpenTelemetry\Config\SDK\ComponentProvider;

/**
 * @internal
 */
class OutputStreamParser
{
    private static ?string $root = null;

    public static function setRoot(string $root): void
    {
        self::$root = $root;
    }

    public static function reset(): void
    {
        self::$root = null;
    }

    public static function parse(string $outputStream): string
    {
        if ($outputStream === 'stdout') {
            return 'php://stdout';
        }

        $pattern = '/^(?P<scheme>[a-zA-Z][a-zA-Z0-9]*):\/\/(?P<path>.+)$/';
        if (preg_match($pattern, $outputStream, $matches) !== 1) {
            throw new \InvalidArgumentException('Invalid output_stream format: ' . $outputStream);
        }

        return match ($matches['scheme']) {
            'file' => self::$root . $matches['path'],
            default => throw new \InvalidArgumentException('Invalid endpoint scheme: ' . $matches['scheme']),
        };
    }
}
