<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Trace;

use function array_keys;
use ArrayAccess;
use function get_class;
use function gettype;
use InvalidArgumentException;
use function is_array;
use function is_object;
use function is_string;
use OpenTelemetry\API\Trace as API;
use function sprintf;
use function strtolower;

/**
 * @see https://github.com/open-telemetry/opentelemetry-specification/blob/v1.6.1/specification/context/api-propagators.md#textmap-propagator Getter and Setter.
 *
 * Default implementation of {@see API\PropagationGetterInterface} and {@see API\PropagationSetterInterface}.
 * This type is used if no custom getter/setter is provided to {@see API\TextMapPropagatorInterface::inject()} or {@see API\TextMapPropagatorInterface::extract()}.
 */
class ArrayAccessGetterSetter implements API\PropagationGetterInterface, API\PropagationSetterInterface
{
    /** @var self|null */
    private static $instance;

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
