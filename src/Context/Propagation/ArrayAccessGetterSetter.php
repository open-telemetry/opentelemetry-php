<?php

declare(strict_types=1);

namespace OpenTelemetry\Context\Propagation;

use function array_key_first;
use ArrayAccess;
use function get_class;
use function gettype;
use InvalidArgumentException;
use function is_array;
use function is_object;
use function is_string;
use function sprintf;
use function strcasecmp;
use Traversable;

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
        if ($this->isSupportedCarrier($carrier)) {
            $keys = [];
            foreach ($carrier as $key => $_) {
                $keys[] = (string) $key;
            }

            return $keys;
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
        if ($this->isSupportedCarrier($carrier)) {
            $value = $carrier[$this->resolveKey($carrier, $key)] ?? null;
            if (is_array($value) && $value) {
                $value = $value[array_key_first($value)];
            }

            return is_string($value)
                ? $value
                : null;
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
        if ($this->isSupportedCarrier($carrier)) {
            if (($r = $this->resolveKey($carrier, $key)) !== $key) {
                unset($carrier[$r]);
            }

            $carrier[$key] = $value;

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

    private function isSupportedCarrier($carrier): bool
    {
        return is_array($carrier) || $carrier instanceof ArrayAccess && $carrier instanceof Traversable;
    }

    private function resolveKey($carrier, string $key): string
    {
        if (isset($carrier[$key])) {
            return $key;
        }

        foreach ($carrier as $k => $_) {
            $k = (string) $k;
            if (strcasecmp($k, $key) === 0) {
                return $k;
            }
        }

        return $key;
    }
}
