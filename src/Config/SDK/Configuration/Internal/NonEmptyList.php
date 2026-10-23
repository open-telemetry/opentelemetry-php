<?php

declare(strict_types=1);

namespace OpenTelemetry\Config\SDK\Configuration\Internal;

use function array_key_exists;
use function is_array;
use function sprintf;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;

/**
 * Rejects lists that are explicitly set to an empty value, for schema properties with `minItems: 1`.
 * Omitting the property stays valid; the node default applies.
 *
 * @internal
 */
final class NonEmptyList
{
    private function __construct()
    {
    }

    public static function require(ArrayNodeDefinition $node, string ...$keys): void
    {
        $node->beforeNormalization()->always(static function (mixed $value) use ($keys): mixed {
            if (is_array($value)) {
                foreach ($keys as $key) {
                    if (array_key_exists($key, $value) && ($value[$key] === [] || $value[$key] === null)) {
                        throw new InvalidConfigurationException(sprintf('"%s" must contain at least one item when set.', $key));
                    }
                }
            }

            return $value;
        })->end();
    }
}
