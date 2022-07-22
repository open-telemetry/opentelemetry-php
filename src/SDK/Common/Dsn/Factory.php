<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Common\Dsn;

use InvalidArgumentException;

final class Factory implements FactoryInterface
{
    /** @var array  */
    public const REQUIRED_ATTRIBUTES = [
        DsnInterface::TYPE_ATTRIBUTE,
        DsnInterface::SCHEME_ATTRIBUTE,
        DsnInterface::HOST_ATTRIBUTE,
    ];

    private array $defaults;

    public function __construct(array $defaults = [])
    {
        $this->defaults = $defaults;
    }

    public static function create(array $defaults = []): self
    {
        return new self($defaults);
    }

    /**
     * @phan-suppress PhanUndeclaredClassAttribute
     */
    #[\ReturnTypeWillChange]
    public function fromArray(array $dsn): Dsn
    {
        return new Dsn(
            $this->getValue(DsnInterface::TYPE_ATTRIBUTE, $dsn),
            $this->getValue(DsnInterface::SCHEME_ATTRIBUTE, $dsn),
            $this->getValue(DsnInterface::HOST_ATTRIBUTE, $dsn),
            $this->getValue(DsnInterface::PATH_ATTRIBUTE, $dsn),
            $this->getValue(DsnInterface::PORT_ATTRIBUTE, $dsn),
            $this->getValue(DsnInterface::OPTIONS_ATTRIBUTE, $dsn, []),
            $this->getValue(DsnInterface::USER_ATTRIBUTE, $dsn),
            $this->getValue(DsnInterface::PASSWORD_ATTRIBUTE, $dsn)
        );
    }

    private function getValue(string $name, array $dsn, $default = null)
    {
        return in_array($name, self::REQUIRED_ATTRIBUTES, true)
            ? $this->requireValue($name, $dsn)
            : $this->resolveValue($name, $dsn, $default);
    }

    private function resolveValue(string $name, array $dsn, $default = null)
    {
        return $dsn[$name] ?? $this->defaults[$name] ?? $default;
    }

    private function requireValue(string $name, array $dsn)
    {
        if (($value = $this->resolveValue($name, $dsn)) === null) {
            throw new InvalidArgumentException(
                'Exporter DSN array must have entry: ' . $name
            );
        }

        return $value;
    }
}
