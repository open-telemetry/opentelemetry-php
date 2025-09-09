<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\Unit\SDK\Propagation;

use OpenTelemetry\API\Behavior\Internal\Logging;
use OpenTelemetry\API\LoggerHolder;
use OpenTelemetry\Context\Propagation\NoopResponsePropagator;
use OpenTelemetry\SDK\Common\Configuration\KnownValues;
use OpenTelemetry\SDK\Common\Configuration\Variables;
use OpenTelemetry\SDK\Propagation\ResponsePropagatorFactory;
use OpenTelemetry\Tests\TestState;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

#[CoversClass(ResponsePropagatorFactory::class)]
class ResponsePropagatorFactoryTest extends TestCase
{
    use TestState;

    #[\Override]
    public function setUp(): void
    {
        LoggerHolder::disable();
        Logging::disable();
    }

    /**
     * @psalm-suppress ArgumentTypeCoercion
     */
    #[DataProvider('responsePropagatorsProvider')]
    public function test_create(string $responsePropagators, string $expected): void
    {
        $this->setEnvironmentVariable(Variables::OTEL_EXPERIMENTAL_RESPONSE_PROPAGATORS, $responsePropagators);
        $responsePropagator = (new ResponsePropagatorFactory())->create();
        $this->assertInstanceOf($expected, $responsePropagator);
    }

    public static function responsePropagatorsProvider(): array
    {
        return [
            [KnownValues::VALUE_NONE, NoopResponsePropagator::class],
            ['unknown', NoopResponsePropagator::class],
        ];
    }
}
