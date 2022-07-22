<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Common\Dsn;

use InvalidArgumentException;

class Parser implements ParserInterface
{
    private const QUERY_ATTRIBUTE = 'query';
    private const PASS_ATTRIBUTE = 'pass';
    private const SCHEME_ATTRIBUTE = 'scheme';

    private FactoryInterface $factory;

    public function __construct(FactoryInterface $factory = null)
    {
        $this->factory = $factory ?? Factory::create();
    }

    public static function create(FactoryInterface $factory = null): self
    {
        return new self($factory);
    }

    public function parse(string $dsn): DsnInterface
    {
        return $this->factory->fromArray(
            $this->parseToArray($dsn)
        );
    }

    public function parseToArray(string $dsn): array
    {
        return self::resolvePassword(
            self::resolveOptions(
                self::parseDsnString($dsn)
            )
        );
    }

    private static function parseDsnString(string $dsn): array
    {
        if (($components = parse_url($dsn)) === false) {
            throw new InvalidArgumentException('Could not parse DSN');
        }

        self::validateComponents($components);

        [$components[DsnInterface::TYPE_ATTRIBUTE], $components[DsnInterface::SCHEME_ATTRIBUTE]]
            = explode('+', $components[DsnInterface::SCHEME_ATTRIBUTE] ?? '+');

        $components[DsnInterface::PATH_ATTRIBUTE] ??= null;
        $components[DsnInterface::PORT_ATTRIBUTE] ??= null;
        $components[DsnInterface::USER_ATTRIBUTE] ??= null;

        return $components;
    }

    private static function validateComponents(array $components): void
    {
        if (!isset($components[self::SCHEME_ATTRIBUTE])
            || (int) strpos($components[self::SCHEME_ATTRIBUTE], '+') === 0) {
            throw new InvalidArgumentException(
                'An exporter DSN must have a exporter type and a scheme: type+scheme://host:port'
            );
        }
    }

    private static function resolveOptions(array $components): array
    {
        $components[DsnInterface::OPTIONS_ATTRIBUTE] = [];
        if (isset($components[self::QUERY_ATTRIBUTE])) {
            foreach (explode('&', $components[self::QUERY_ATTRIBUTE]) as $part) {
                [$key, $value] = explode('=', $part);
                $components[DsnInterface::OPTIONS_ATTRIBUTE][$key] = $value;
            }
        }

        unset($components[self::QUERY_ATTRIBUTE]);

        return $components;
    }

    private static function resolvePassword(array $components): array
    {
        if (!isset($components[self::PASS_ATTRIBUTE])) {
            $components[DsnInterface::PASSWORD_ATTRIBUTE] = null;

            return $components;
        }

        $components[DsnInterface::PASSWORD_ATTRIBUTE] = $components[self::PASS_ATTRIBUTE];
        unset($components[self::PASS_ATTRIBUTE]);

        return $components;
    }
}
