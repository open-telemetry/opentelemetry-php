<?php

declare(strict_types=1);

namespace OpenTelemetry\Context\Propagation;

use function array_keys;
use ArrayAccess;
use function get_class;
use function gettype;
use InvalidArgumentException;
use function is_array;
use function is_object;
use function is_string;
use function sprintf;
use function strtolower;

/**
 * @see https://github.com/open-telemetry/opentelemetry-specification/blob/v1.6.1/specification/context/api-propagators.md#textmap-propagator Getter and Setter.
 *
 * Default implementation of {@see PropagationGetterInterface} and {@see PropagationSetterInterface}.
 * This type is used if no custom getter/setter is provided to {@see TextMapPropagatorInterface::inject()} or {@see TextMapPropagatorInterface::extract()}.
 */
final class ArrayAccessGetterSetter implements PropagationGetterInterface, PropagationSetterInterface
{
    private static ?self $instance = null;

    /**
     * Returns a singleton instance of `self` to avoid, multiple runtime allocations.
     */
    public static function getInstance(): self
    {
        if (null === self::$instance) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    /** {@inheritdoc} */
    public function keys($carrier): array
    {
        if (is_array($carrier)) {
            return array_keys($carrier);
        }

        if ($carrier instanceof KeyedArrayAccessInterface) {
            return $carrier->keys();
        }

        throw new InvalidArgumentException(
            sprintf(
                'Unsupported carrier type: %s.',
                is_object($carrier) ? get_class($carrier) : gettype($carrier),
            )
        );
    }

    /** {@inheritdoc} */
    public function get($carrier, string $key): ?string
    {
        $lKey = strtolower($key);
        if ($carrier instanceof ArrayAccess) {
            return $carrier->offsetExists($lKey) ? $carrier->offsetGet($lKey) : null;
        }

        if (is_array($carrier)) {
            if (empty($carrier)) {
                return null;
            }

            foreach ($carrier as $k => $value) {
                // Ensure traceparent and tracestate header keys are lowercase
                if (is_string($k)) {
                    if (strtolower($k) === $lKey) {
                        if (is_array($value)) {
                            return empty($value) ? null : $value[0];
                        }

                        return $value;
                    }
                }
            }

            return null;
        }

        throw new InvalidArgumentException(
            sprintf(
                'Unsupported carrier type: %s. Unable to get value associated with key:%s',
                is_object($carrier) ? get_class($carrier) : gettype($carrier),
                $key
            )
        );
    }

    /** {@inheritdoc} */
    public function set(&$carrier, string $key, string $value): void
    {
        if ($key === '') {
            throw new InvalidArgumentException('Unable to set value with an empty key');
        }

        if ($carrier instanceof ArrayAccess || is_array($carrier)) {
            $carrier[strtolower($key)] = $value;

            return;
        }

        throw new InvalidArgumentException(
            sprintf(
                'Unsupported carrier type: %s. Unable to set value associated with key:%s',
                is_object($carrier) ? get_class($carrier) : gettype($carrier),
                $key
            )
        );
    }
}
