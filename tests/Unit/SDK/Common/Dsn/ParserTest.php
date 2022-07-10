<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\Unit\SDK\Common\Dsn;

use Generator;
use InvalidArgumentException;
use OpenTelemetry\SDK\Common\Dsn\DsnInterface;
use OpenTelemetry\SDK\Common\Dsn\FactoryInterface;
use OpenTelemetry\SDK\Common\Dsn\Parser;
use PHPUnit\Framework\TestCase;

/**
 * @covers \OpenTelemetry\SDK\Common\Dsn\Parser
 */
class ParserTest extends TestCase
{
    private const FULL_DSN = 'foo+bar://root:secret@baz:1234/path?key=value';

    private const PARTS = [
        DsnInterface::TYPE_ATTRIBUTE => 'foo',
        DsnInterface::PROTOCOL_ATTRIBUTE => 'bar',
        DsnInterface::HOST_ATTRIBUTE => 'example.com',
        DsnInterface::PATH_ATTRIBUTE => '/path',
        DsnInterface::PORT_ATTRIBUTE => 42,
        DsnInterface::OPTIONS_ATTRIBUTE => ['key' => 'value'],
        DsnInterface::USER_ATTRIBUTE => 'root',
        DsnInterface::PASSWORD_ATTRIBUTE => 'secret',
    ];

    private Parser $instance;

    protected function setUp(): void
    {
        $factory = $this->createMock(FactoryInterface::class);
        $factory->method('fromArray')
            ->willReturn(
                $this->createMock(DsnInterface::class)
            );

        $this->instance = Parser::create($factory);
    }

    public function test_parse(): void
    {
        $this->assertInstanceOf(
            DsnInterface::class,
            $this->instance->parse(
                self::FULL_DSN
            )
        );
    }

    /**
     * @dataProvider provideParts
     */
    public function test_parse_to_array(string $dsn, array $droppedParts = []): void
    {
        $parts = self::PARTS;

        foreach ($droppedParts as $part) {
            $parts[$part] = $part === DsnInterface::OPTIONS_ATTRIBUTE ? [] : null;
        }

        $this->assertEquals(
            $parts,
            $this->instance->parseToArray($dsn)
        );
    }

    /**
     * @dataProvider provideInvalidDsn
     */
    public function test_parse_to_array_throws_exception_on_missing_mandatory_part(string $dsn): void
    {
        $this->expectException(InvalidArgumentException::class);

        $this->instance->parseToArray($dsn);
    }

    private function provideParts(): Generator
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
        // no path, port, options, password
        yield ['foo+bar://root@example.com', [
            DsnInterface::PATH_ATTRIBUTE,
            DsnInterface::PORT_ATTRIBUTE,
            DsnInterface::OPTIONS_ATTRIBUTE,
            DsnInterface::PASSWORD_ATTRIBUTE,
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
        // no port, options, password
        yield ['foo+bar://root@example.com/path', [
            DsnInterface::PORT_ATTRIBUTE,
            DsnInterface::OPTIONS_ATTRIBUTE,
            DsnInterface::PASSWORD_ATTRIBUTE,
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
        // no options, password
        yield ['foo+bar://root@example.com:42/path', [
            DsnInterface::OPTIONS_ATTRIBUTE,
            DsnInterface::PASSWORD_ATTRIBUTE,
        ]];
        // no options, user, password
        yield ['foo+bar://example.com:42/path', [
            DsnInterface::OPTIONS_ATTRIBUTE,
            DsnInterface::USER_ATTRIBUTE,
            DsnInterface::PASSWORD_ATTRIBUTE,
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

    private function provideInvalidDsn(): Generator
    {
        // invalid
        yield ['http://user@:80"'];
        // no scheme
        yield ['root:secret@baz:1234/path'];
        // no type
        yield ['bar://root:secret@baz:1234/path'];
    }
}
