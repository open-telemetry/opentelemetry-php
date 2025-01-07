<?php

declare(strict_types=1);

namespace OpenTelemetry\Config\SDK\Configuration;

use Closure;
use InvalidArgumentException;
use function is_string;
use function preg_match;
use function restore_error_handler;
use function set_error_handler;
use function str_starts_with;
use function strlen;
use function substr;
use Symfony\Component\Config\Definition\Builder\NodeDefinition;

/**
 * Provides validation closures for {@see NodeDefinition}s.
 *
 * @see NodeDefinition::validate()
 */
final class Validation
{
    public static function ensureString(): Closure
    {
        return static function (mixed $value): ?string {
            if ($value === null) {
                return null;
            }
            if (!is_string($value)) {
                throw new InvalidArgumentException('must be of type string');
            }

            return $value;
        };
    }

    /**
     * @psalm-suppress ArgumentTypeCoercion
     */
    public static function ensureRegexPattern(): Closure
    {
        return static function (mixed $value): ?string {
            if ($value === null) {
                return null;
            }
            if (!is_string($value)) {
                throw new InvalidArgumentException('must be of type string');
            }

            set_error_handler(static fn (int $errno, string $errstr)
                => throw new InvalidArgumentException('must be a valid regex pattern: ' . self::stripPrefix($errstr, 'preg_match(): '), $errno));

            try {
                preg_match($value, '');
            } finally {
                restore_error_handler();
            }

            return $value;
        };
    }

    private static function stripPrefix(string $string, string $prefix): string
    {
        if (str_starts_with($string, $prefix)) {
            return substr($string, strlen($prefix));
        }

        return $string;
    }
}
