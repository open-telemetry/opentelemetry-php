<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\Unit\SDK\Common\Dsn;

use Generator;
use OpenTelemetry\SDK\Common\Dsn\Dsn;
use OpenTelemetry\SDK\Common\Dsn\DsnInterface;
use PHPUnit\Framework\TestCase;

/**
 * @covers \OpenTelemetry\SDK\Common\Dsn\Dsn
 */
class DsnTest extends TestCase
{
    private const ARGUMENTS = [
        DsnInterface::TYPE_ATTRIBUTE => 'foo',
        DsnInterface::SCHEME_ATTRIBUTE => 'bar',
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
    public function test_getters(string $getter, $value): void
    {
        $this->assertSame(
            $value,
            $this->createInstance()->{$getter}()
        );
    }

    /**
     * @dataProvider provideToStringValues
     */
    public function test_to_string(string $dsn, array $droppedArguments): void
    {
        $this->assertSame(
            $dsn,
            (string) $this->createInstance($droppedArguments)
        );
    }

    /**
     * @dataProvider provideEndpointValues
     */
    public function test_get_endpoint(string $uri, array $droppedArguments): void
    {
        $this->assertSame(
            $uri,
            $this->createInstance($droppedArguments)->getEndpoint()
        );
    }

    public function test_as_config_array(): void
    {
        $this->assertSame(
            [
                'type' => 'foo',
                'url' => 'bar://root:secret@example.com:42/path',
                'options' => ['key'=>'value'],
            ],
            $this->createInstance()->asConfigArray()
        );
    }

    /**
     * @psalm-suppress PossiblyInvalidArgument
     */
    private function createInstance(array $droppedArguments = []): Dsn
    {
        $arguments = self::ARGUMENTS;

        foreach ($droppedArguments as $argument) {
            $arguments[$argument] = null;
        }

        return Dsn::create(
            $arguments[DsnInterface::TYPE_ATTRIBUTE],
            $arguments[DsnInterface::SCHEME_ATTRIBUTE],
            $arguments[DsnInterface::HOST_ATTRIBUTE],
            $arguments[DsnInterface::PATH_ATTRIBUTE],
            $arguments[DsnInterface::PORT_ATTRIBUTE],
            $arguments[DsnInterface::OPTIONS_ATTRIBUTE],
            $arguments[DsnInterface::USER_ATTRIBUTE],
            $arguments[DsnInterface::PASSWORD_ATTRIBUTE]
        );
    }

    public function provideArguments(): Generator
    {
        foreach (self::ARGUMENTS as $name => $value) {
            yield ['get' . ucfirst($name), $value];
        }
    }

    public function provideToStringValues(): Generator
    {
        // all
        yield ['foo+bar://root:secret@example.com:42/path?key=value', []];
        // no path
        yield ['foo+bar://root:secret@example.com:42?key=value', [
            DsnInterface::PATH_ATTRIBUTE,
        ]];
        // no path, port
        yield ['foo+bar://root:secret@example.com?key=value', [
            DsnInterface::PATH_ATTRIBUTE,
            DsnInterface::PORT_ATTRIBUTE,
        ]];
        // no path, port, options
        yield ['foo+bar://root:secret@example.com', [
            DsnInterface::PATH_ATTRIBUTE,
            DsnInterface::PORT_ATTRIBUTE,
            DsnInterface::OPTIONS_ATTRIBUTE,
        ]];
        // no path, port, options, user
        yield ['foo+bar://example.com', [
            DsnInterface::PATH_ATTRIBUTE,
            DsnInterface::PORT_ATTRIBUTE,
            DsnInterface::OPTIONS_ATTRIBUTE,
            DsnInterface::USER_ATTRIBUTE,
        ]];
        // no path, port, options, user, password
        yield ['foo+bar://example.com', [
            DsnInterface::PATH_ATTRIBUTE,
            DsnInterface::PORT_ATTRIBUTE,
            DsnInterface::OPTIONS_ATTRIBUTE,
            DsnInterface::USER_ATTRIBUTE,
            DsnInterface::PASSWORD_ATTRIBUTE,
        ]];
        // no port
        yield ['foo+bar://root:secret@example.com/path?key=value', [
            DsnInterface::PORT_ATTRIBUTE,
        ]];
        // no port, options
        yield ['foo+bar://root:secret@example.com/path', [
            DsnInterface::PORT_ATTRIBUTE,
            DsnInterface::OPTIONS_ATTRIBUTE,
        ]];
        // no port, options, user
        yield ['foo+bar://example.com/path', [
            DsnInterface::PORT_ATTRIBUTE,
            DsnInterface::OPTIONS_ATTRIBUTE,
            DsnInterface::USER_ATTRIBUTE,
        ]];
        // no port, options, user, password
        yield ['foo+bar://example.com/path', [
            DsnInterface::PORT_ATTRIBUTE,
            DsnInterface::OPTIONS_ATTRIBUTE,
            DsnInterface::USER_ATTRIBUTE,
            DsnInterface::PASSWORD_ATTRIBUTE,
        ]];
        // no options,
        yield ['foo+bar://root:secret@example.com:42/path', [
            DsnInterface::OPTIONS_ATTRIBUTE,
        ]];
        // no options, user
        yield ['foo+bar://example.com:42/path', [
            DsnInterface::OPTIONS_ATTRIBUTE,
            DsnInterface::USER_ATTRIBUTE,
        ]];
        // no options, user, password
        yield ['foo+bar://example.com:42/path', [
            DsnInterface::OPTIONS_ATTRIBUTE,
            DsnInterface::USER_ATTRIBUTE,
            DsnInterface::PASSWORD_ATTRIBUTE,
        ]];
        // no user
        yield ['foo+bar://example.com:42/path?key=value', [
            DsnInterface::USER_ATTRIBUTE,
        ]];
        // no user, password
        yield ['foo+bar://example.com:42/path?key=value', [
            DsnInterface::USER_ATTRIBUTE,
            DsnInterface::PASSWORD_ATTRIBUTE,
        ]];
        // no password
        yield ['foo+bar://root@example.com:42/path?key=value', [
            DsnInterface::PASSWORD_ATTRIBUTE,
        ]];
    }

    public function provideEndpointValues(): Generator
    {
        // all
        yield ['bar://root:secret@example.com:42/path', []];
        // no path
        yield ['bar://root:secret@example.com:42', [
            DsnInterface::PATH_ATTRIBUTE,
        ]];
        // no path, port
        yield ['bar://root:secret@example.com', [
            DsnInterface::PATH_ATTRIBUTE,
            DsnInterface::PORT_ATTRIBUTE,
        ]];
        // no path, port, options
        yield ['bar://root:secret@example.com', [
            DsnInterface::PATH_ATTRIBUTE,
            DsnInterface::PORT_ATTRIBUTE,
            DsnInterface::OPTIONS_ATTRIBUTE,
        ]];
        // no path, port, options, user
        yield ['bar://example.com', [
            DsnInterface::PATH_ATTRIBUTE,
            DsnInterface::PORT_ATTRIBUTE,
            DsnInterface::OPTIONS_ATTRIBUTE,
            DsnInterface::USER_ATTRIBUTE,
        ]];
        // no path, port, options, user, password
        yield ['bar://example.com', [
            DsnInterface::PATH_ATTRIBUTE,
            DsnInterface::PORT_ATTRIBUTE,
            DsnInterface::OPTIONS_ATTRIBUTE,
            DsnInterface::USER_ATTRIBUTE,
            DsnInterface::PASSWORD_ATTRIBUTE,
        ]];
        // no port
        yield ['bar://root:secret@example.com/path', [
            DsnInterface::PORT_ATTRIBUTE,
        ]];
        // no port, options
        yield ['bar://root:secret@example.com/path', [
            DsnInterface::PORT_ATTRIBUTE,
            DsnInterface::OPTIONS_ATTRIBUTE,
        ]];
        // no port, options, user
        yield ['bar://example.com/path', [
            DsnInterface::PORT_ATTRIBUTE,
            DsnInterface::OPTIONS_ATTRIBUTE,
            DsnInterface::USER_ATTRIBUTE,
        ]];
        // no port, options, user, password
        yield ['bar://example.com/path', [
            DsnInterface::PORT_ATTRIBUTE,
            DsnInterface::OPTIONS_ATTRIBUTE,
            DsnInterface::USER_ATTRIBUTE,
            DsnInterface::PASSWORD_ATTRIBUTE,
        ]];
        // no options,
        yield ['bar://root:secret@example.com:42/path', [
            DsnInterface::OPTIONS_ATTRIBUTE,
        ]];
        // no options, user
        yield ['bar://example.com:42/path', [
            DsnInterface::OPTIONS_ATTRIBUTE,
            DsnInterface::USER_ATTRIBUTE,
        ]];
        // no options, user, password
        yield ['bar://example.com:42/path', [
            DsnInterface::OPTIONS_ATTRIBUTE,
            DsnInterface::USER_ATTRIBUTE,
            DsnInterface::PASSWORD_ATTRIBUTE,
        ]];
        // no user
        yield ['bar://example.com:42/path', [
            DsnInterface::USER_ATTRIBUTE,
        ]];
        // no user, password
        yield ['bar://example.com:42/path', [
            DsnInterface::USER_ATTRIBUTE,
            DsnInterface::PASSWORD_ATTRIBUTE,
        ]];
        // no password
        yield ['bar://root@example.com:42/path', [
            DsnInterface::PASSWORD_ATTRIBUTE,
        ]];
    }
}
