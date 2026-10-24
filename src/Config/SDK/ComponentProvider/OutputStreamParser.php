<?php

declare(strict_types=1);

namespace OpenTelemetry\Config\SDK\ComponentProvider;

/**
 * @internal
 */
final class OutputStreamParser
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

    /**
     * @see https://www.rfc-editor.org/rfc/rfc8089
     */
    public static function parse(string $outputStream): string
    {
        if ($outputStream === 'stdout') {
            return 'php://stdout';
        }

        $pattern = '~^(?P<scheme>[a-zA-Z][a-zA-Z0-9+.-]*):(?://(?P<host>[^/?#]*))?(?P<path>[^?#]*)$~D';
        if (preg_match($pattern, $outputStream, $matches) !== 1) {
            throw new \InvalidArgumentException('Invalid output_stream format: ' . $outputStream);
        }
        if (strtolower($matches['scheme']) !== 'file') {
            throw new \InvalidArgumentException('Invalid endpoint scheme: ' . $matches['scheme']);
        }
        if ($matches['host'] !== '' && strtolower($matches['host']) !== 'localhost') {
            throw new \InvalidArgumentException('Invalid output_stream host, only localhost is supported: ' . $outputStream);
        }
        if (!str_starts_with($matches['path'], '/')) {
            throw new \InvalidArgumentException('Invalid output_stream path, it must be absolute: ' . $outputStream);
        }

        $path = rawurldecode($matches['path']);
        if (str_contains($path, "\0")) {
            throw new \InvalidArgumentException('Invalid output_stream path: ' . $outputStream);
        }

        return self::$root . $path;
    }
}
