<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\Unit\SDK\Common\Dsn;

use Generator;
use InvalidArgumentException;
use OpenTelemetry\SDK\Common\Dsn\DsnInterface;
use OpenTelemetry\SDK\Common\Dsn\Factory;
use PHPUnit\Framework\TestCase;

/**
 * @covers \OpenTelemetry\SDK\Common\Dsn\Factory
 */
class FactoryTest extends TestCase
{
    private const ARGUMENTS = [
        DsnInterface::TYPE_ATTRIBUTE => 'foo',
        DsnInterface::PROTOCOL_ATTRIBUTE => 'bar',
        DsnInterface::HOST_ATTRIBUTE => 'example.com',
        DsnInterface::PATH_ATTRIBUTE => '/path',
        DsnInterface::PORT_ATTRIBUTE => 42,
        DsnInterface::OPTIONS_ATTRIBUTE => ['key' => 'value'],
        DsnInterface::USER_ATTRIBUTE => 'root',
        DsnInterface::PASSWORD_ATTRIBUTE => 'secret',
    ];

    /**
     * @dataProvider provideArguments
     */
    public function test_default_values(string $getter, $value): void
    {
        $factory = Factory::create(
            self::ARGUMENTS
        );

        $this->assertSame(
            $value,
            $factory->fromArray([])
                ->{$getter}()
        );
    }

    /**
     * @dataProvider provideArguments
     */
    public function test_from_array(string $getter, $value): void
    {
        $this->assertSame(
            $value,
            Factory::create()
                ->fromArray(self::ARGUMENTS)
                ->{$getter}()
        );
    }

    /**
     * @dataProvider provideMissingAttributeValues
     */
    public function test_exception_on_missing_attributes(array $arguments): void
    {
        $this->expectException(InvalidArgumentException::class);

        Factory::create()->fromArray($arguments);
    }

    private function provideArguments(): Generator
    {
        foreach (self::ARGUMENTS as $name => $value) {
            yield ['get' . ucfirst($name), $value];
        }
    }

    private function provideMissingAttributeValues(): Generator
    {
        foreach (Factory::REQUIRED_ATTRIBUTES as $name) {
            $arguments = self::ARGUMENTS;
            unset($arguments[$name]);

            yield [$arguments];
        }
    }
}
