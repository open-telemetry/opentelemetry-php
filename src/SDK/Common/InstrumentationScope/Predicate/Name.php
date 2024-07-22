<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Common\InstrumentationScope\Predicate;

use InvalidArgumentException;
use OpenTelemetry\SDK\Common\Instrumentation\InstrumentationScopeInterface;
use OpenTelemetry\SDK\Common\InstrumentationScope\Predicate;

class Name implements Predicate
{
    /**
     * @throws InvalidArgumentException
     */
    public function __construct(private readonly string $regex)
    {
        self::validate_regex($this->regex);
    }

    /**
     * @psalm-suppress ArgumentTypeCoercion
     */
    public function matches(InstrumentationScopeInterface $scope): bool
    {
        $result = preg_match($this->regex, $scope->getName());

        return $result > 0;
    }

    /**
     * @throws InvalidArgumentException
     * @phan-suppress PhanParamSuspiciousOrder
     * @psalm-suppress ArgumentTypeCoercion
     */
    private static function validate_regex(string $regex): void
    {
        set_error_handler(static fn (int $errno, string $errstr)
            => throw new InvalidArgumentException('Invalid regex pattern', $errno));

        try {
            preg_match($regex, '');
        } finally {
            restore_error_handler();
        }
    }
}
