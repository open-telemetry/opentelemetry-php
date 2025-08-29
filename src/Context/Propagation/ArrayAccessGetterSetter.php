<?php

declare(strict_types=1);

namespace OpenTelemetry\Context\Propagation;

use function array_key_first;
use ArrayAccess;
use InvalidArgumentException;
use function is_array;
use function is_string;
use function sprintf;
use function strcasecmp;
use Traversable;

/**
 * @see https://github.com/open-telemetry/opentelemetry-specification/blob/v1.6.1/specification/context/api-propagators.md#textmap-propagator Getter and Setter.
 *
 * Default implementation of {@see ExtendedPropagationGetterInterface} and {@see PropagationSetterInterface}.
 * This type is used if no custom getter/setter is provided to {@see TextMapPropagatorInterface::inject()} or {@see TextMapPropagatorInterface::extract()}.
 */
final class ArrayAccessGetterSetter implements ExtendedPropagationGetterInterface, PropagationSetterInterface
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

    /**
     * {@inheritdoc}
     *
     * @param mixed $carrier
     *
     * @psalm-param \ArrayObject<'a'|'b', 'alpha'|'bravo'>|\stdClass|array{a?: 'alpha'|list{'alpha', 'beta'}, b?: 'bravo'|list{'bravo'}, 1?: list{0: 'alpha', 1?: 'beta'}} $carrier
     */
    #[\Override]
    public function keys(mixed $carrier): array
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
                get_debug_type($carrier),
            )
        );
    }

    /**
     * {@inheritdoc}
     *
     * @param mixed $carrier
     *
     * @psalm-param \ArrayObject<'A', 'alpha'>|\stdClass|array{a?: 'alpha', b?: 'bravo'|list{'bravo'}, 1?: list{'alpha'}} $carrier
     */
    #[\Override]
    public function get(mixed $carrier, string $key): ?string
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
                get_debug_type($carrier),
                $key
            )
        );
    }

    /**
     * {@inheritdoc}
     *
     * @param mixed $carrier
     *
     * @psalm-param \stdClass|array{a?: 'alpha'|list{'alpha', 'beta'}, b?: 'bravo', 1?: list{'alpha', 'beta'}} $carrier
     */
    #[\Override]
    public function getAll(mixed $carrier, string $key): array
    {
        if ($this->isSupportedCarrier($carrier)) {
            $value = $carrier[$this->resolveKey($carrier, $key)] ?? null;
            if (is_array($value) && $value) {
                return array_values(array_filter($value, 'is_string'));
            }

            return is_string($value)
                ? [$value]
                : [];
        }

        throw new InvalidArgumentException(
            sprintf(
                'Unsupported carrier type: %s. Unable to get value associated with key:%s',
                get_debug_type($carrier),
                $key
            )
        );
    }

    /**
     * {@inheritdoc}
     *
     * @param mixed $carrier
     *
     * @psalm-param \ArrayObject<'A'|'a', 'alpha'>|\Countable|\stdClass|array{a: 'alpha'}|iterable $carrier
     */
    #[\Override]
    public function set(mixed &$carrier, string $key, string $value): void
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
                get_debug_type($carrier),
                $key
            )
        );
    }

    /**
     * @param (string|string[])[]|\ArrayObject|\Countable|\stdClass|iterable $carrier
     *
     * @psalm-param \ArrayObject<'A'|'a'|'b', 'alpha'|'bravo'>|\Countable|\stdClass|array{a?: 'alpha'|list{'alpha', 'beta'}, b?: 'bravo'|list{'bravo'}, 1?: list{0: 'alpha', 1?: 'beta'}}|iterable $carrier
     */
    private function isSupportedCarrier(array|\ArrayObject|iterable|\stdClass|\Countable $carrier): bool
    {
        return is_array($carrier) || $carrier instanceof ArrayAccess && $carrier instanceof Traversable;
    }

    /**
     * @param (string|string[])[]|\ArrayObject|\Countable|\stdClass|iterable $carrier
     *
     * @psalm-param \ArrayObject<'A'|'a', 'alpha'>|\Countable|\stdClass|array{a?: 'alpha'|list{'alpha', 'beta'}, b?: 'bravo'|list{'bravo'}, 1?: list{0: 'alpha', 1?: 'beta'}}|iterable $carrier
     */
    private function resolveKey(array|\ArrayObject|iterable|\stdClass|\Countable $carrier, string $key): string
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
