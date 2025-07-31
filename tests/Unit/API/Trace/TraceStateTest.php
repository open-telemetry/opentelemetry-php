<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\API\Unit\Trace;

use function array_reverse;
use OpenTelemetry\API\Behavior\Internal\Logging;
use OpenTelemetry\API\LoggerHolder;
use OpenTelemetry\API\Trace\TraceState;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use function str_repeat;

/**
 * @psalm-suppress UndefinedInterfaceMethod
 */
#[CoversClass(TraceState::class)]
class TraceStateTest extends TestCase
{
    private LoggerInterface $logger;

    #[\Override]
    public function setUp(): void
    {
        $this->logger = $this->createMock(LoggerInterface::class);
        LoggerHolder::set($this->logger);
        Logging::reset();
    }

    public function test_get_tracestate_value(): void
    {
        $tracestate = new TraceState('vendor1=value1');

        $this->assertSame('value1', $tracestate->get('vendor1'));
    }

    public function test_get_tracestate_with_empty_string(): void
    {
        $this->logger->expects($this->never())->method('log')->with(
            $this->equalTo('warning'),
            $this->anything(),
            $this->anything(),
        );

        $tracestate = new TraceState('');

        $this->assertSame(0, $tracestate->getListMemberCount());
    }

    public function test_with_tracestate_value(): void
    {
        $tracestate = new TraceState('vendor1=value1');
        $tracestateWithNewValue = $tracestate->with('vendor2', 'value2');

        // New entry is included in the new TraceState object
        $this->assertSame('value2', $tracestateWithNewValue->get('vendor2'));
        $this->assertNull($tracestate->get('vendor2'));

        // New entry is placed at the beginning of the tracestate header
        $this->assertSame('vendor2=value2,vendor1=value1', (string) $tracestateWithNewValue);

        $tracestateWithUpdatedValue = $tracestateWithNewValue->with('vendor1', 'newValue1');

        // The updated entry is overwritten and placed at the beginning of the header
        $this->assertSame('value1', $tracestateWithNewValue->get('vendor1'));
        $this->assertSame('newValue1', $tracestateWithUpdatedValue->get('vendor1'));
        $this->assertSame('vendor1=newValue1,vendor2=value2', (string) $tracestateWithUpdatedValue);

        // A new entry containing an invalid key will not be added
        $tracestateWithInvalidKey = $tracestate->with('@', 'value');
        $this->assertNull($tracestateWithInvalidKey->get('@'));
        $this->assertSame((string) $tracestate, (string) $tracestateWithInvalidKey);

        // A new entry containing an invalid value will not be added
        $tracestateWithInvalidValue = $tracestate->with('vendor2', 'value' . chr(0x19) . '1');
        $this->assertNull($tracestateWithInvalidValue->get('vendor2'));
        $this->assertSame((string) $tracestate, (string) $tracestateWithInvalidValue);
    }

    public function test_without_tracestate_value(): void
    {
        $tracestate = new TraceState('vendor1=value1,vendor2=value2');
        $tracestateWithoutNewValue = $tracestate->without('vendor1');

        $this->assertNull($tracestateWithoutNewValue->get('vendor1'));
        $this->assertSame('value2', $tracestateWithoutNewValue->get('vendor2'));
        $this->assertSame('value1', $tracestate->get('vendor1'));
        $this->assertSame('value2', $tracestate->get('vendor2'));
    }

    public function test_with_allows_only32_members(): void
    {
        $traceState = new TraceState();
        for ($i = 0; $i < 33; $i++) {
            $traceState = $traceState->with('key' . $i, 'test');
        }

        $this->assertSame(32, $traceState->getListMemberCount());
    }

    public function test_to_string_tracestate(): void
    {
        $tracestate = new TraceState('vendor1=value1');
        $emptyTracestate = new TraceState();

        $this->assertSame('vendor1=value1', (string) $tracestate);
        $this->assertSame(0, $emptyTracestate->getListMemberCount());
        $this->assertEmpty((string) $emptyTracestate);
    }

    public function test_max_tracestate_list_members(): void
    {
        // Build a tracestate with the max 32 values. Ex '0=0,1=1,...,31=31'
        $rawTraceState = range(0, TraceState::MAX_LIST_MEMBERS - 1);
        array_walk($rawTraceState, static function (&$v, $k) {
            $v = 'k' . $k . TraceState::LIST_MEMBER_KEY_VALUE_SPLITTER . 'v' . $v;
        });

        /**
         * @var array $rawTraceState
         * @see https://github.com/vimeo/psalm/issues/6394
         */
        $this->assertCount(TraceState::MAX_LIST_MEMBERS, $rawTraceState);

        $validTracestate = new TraceState(implode(TraceState::LIST_MEMBERS_SEPARATOR, $rawTraceState));
        $this->assertSame(TraceState::MAX_LIST_MEMBERS, $validTracestate->getListMemberCount());

        // Add a list-member to the tracestate that exceeds the max of 32. This will cause the tracestate to be discarded.
        $rawTraceState[32] = 'k32' . TraceState::LIST_MEMBER_KEY_VALUE_SPLITTER . 'v32';
        $this->assertCount(TraceState::MAX_LIST_MEMBERS + 1, $rawTraceState);

        $truncatedTracestate = new TraceState(implode(TraceState::LIST_MEMBERS_SEPARATOR, $rawTraceState));
        $this->assertSame(0, $truncatedTracestate->getListMemberCount());
    }

    public function test_max_tracestate_length(): void
    {
        // Build a vendor key with a length of 256 characters. The max characters allowed.
        $vendorKey = str_repeat('k', 256);

        // Build a vendor value with a length of 256 characters. The max characters allowed.
        $vendorValue = str_repeat('v', 256);

        // tracestate length = 513 characters (accepted).
        $rawTraceState = $vendorKey . TraceState::LIST_MEMBER_KEY_VALUE_SPLITTER . $vendorValue;
        $validTracestate = new TraceState($rawTraceState);
        $this->assertSame($rawTraceState, (string) $validTracestate);
    }

    public function test_parse_validate_key(): void
    {
        // Valid keys
        $validKeys = 'a-b=1,c*d=2,e/f=3,g_h=4,01@i-j=5';
        $tracestate = new TraceState($validKeys);

        $this->assertSame('1', $tracestate->get('a-b'));
        $this->assertSame('2', $tracestate->get('c*d'));
        $this->assertSame('3', $tracestate->get('e/f'));
        $this->assertSame('4', $tracestate->get('g_h'));
        $this->assertSame('5', $tracestate->get('01@i-j'));
        $this->assertSame($validKeys, (string) $tracestate);

        // Mixed invalid keys in with valid ones
        $mixedInvalidKeys = 'a=1=,c*d=2,@e/f=3,g_h=4,I-j=5,k&l=6';
        $tracestate = new TraceState($mixedInvalidKeys);

        // Drop all keys on an invalid key
        $this->assertSame(0, $tracestate->getListMemberCount());
    }

    public function test_parse_validate_value(): void
    {
        // Tests values are within the range of 0x20 to 0x7E characters
        $tracestate =    'char1=value' . chr(0x19) . '1'
                      . ',char2=value' . chr(0x20) . '2'
                      . ',char3=value' . chr(0x7E) . '3'
                      . ',char4=value' . chr(0x7F) . '4';

        $parsedTracestate = new TraceState($tracestate);

        // Entire tracestate is dropped since the value for char1 is invalid.
        $this->assertSame(0, $parsedTracestate->getListMemberCount());
    }

    #[DataProvider('validKeyValueProvider')]
    public function test_valid_key_value(string $key, string $value): void
    {
        $traceState = new TraceState();
        $traceState = $traceState->with($key, $value);

        $this->assertSame($value, $traceState->get($key));
        $this->assertSame($key . '=' . $value, (string) $traceState);
    }

    public static function validKeyValueProvider(): array
    {
        return [
            ['valid@key', 'value'],
            ['valid@key', '0'],
            ['abcdefghijklmnopqrstuvwxyz0123456789_-*/', 'value'],
            ['abcdefghijklmnopqrstuvwxyz0123456789_-*/@a1234_-*/', 'value'],
            ['key', strtr(implode(range(chr(0x20), chr(0x7E))), [',' => '', '=' => ''])],
            [str_repeat('a', 256), 'value'],
            ['key', str_repeat('a', 256)],
        ];
    }

    #[DataProvider('invalidKeyValueProvider')]
    public function test_invalid_key_value(string $key, string $value): void
    {
        $traceState = new TraceState();
        $traceState = $traceState->with($key, $value);

        $this->assertNull($traceState->get($key));
    }

    public static function invalidKeyValueProvider(): array
    {
        return [
            ['', 'value'],
            ['invalid.key', 'value'],
            ['invalid-key@', 'value'],
            ['0invalid-key', 'value'],
            [str_repeat('a', 257), 'value'],
            ['invalid@key_____________', 'value'],
            ['-invalid-key@id', 'value'],
            ['invalid-key@0id', 'value'],
            ['invalid-key@@id', 'value'],

            ['key', ''],
            ['key', 'invalid-value '],
            ['key', 'invalid,value'],
            ['key', 'invalid=value'],
            ['key', str_repeat('a', 257)],
        ];
    }

    #[DataProvider('toStringProvider')]
    public function test_to_string(array $entries, ?int $limit, string $expected): void
    {
        $traceState = new TraceState();
        foreach (array_reverse($entries) as $key => $value) {
            $traceState = $traceState->with($key, $value);
        }

        $this->assertSame($expected, $traceState->toString(limit: $limit));
    }

    public static function toStringProvider(): array
    {
        return [
            [[], null, ''],
            [['key' => 'value'], null, 'key=value'],
            [['key' => 'value', 'key2' => 'value2'], null, 'key=value,key2=value2'],
            [['key' => 'value', 'key2' => 'value2'], 21, 'key=value,key2=value2'],
            [['key' => 'value', 'key2' => 'value2'], 14, 'key=value'],
            [['key' => 'value', 'key2' => 'value2'], 9, 'key=value'],
            [['key' => 'value', 'key2' => 'value2'], 5, ''],
            [['key' => 'value', 'a' => 'b'], 5, ''],
            [[str_repeat('a', 10) => str_repeat('v', 50), 'key' => 'value', 'key2' => 'value2'], 50, ''],
            [[str_repeat('a', 10) => str_repeat('v', 150), 'key' => 'value', 'key2' => 'value2'], 50, 'key=value,key2=value2'],
            [[str_repeat('a', 10) => str_repeat('v', 117), 'key' => 'value', 'key2' => 'value2'], 50, ''],
            [[str_repeat('a', 10) => str_repeat('v', 118), 'key' => 'value', 'key2' => 'value2'], 50, 'key=value,key2=value2'],
            [[str_repeat('a', 10) => str_repeat('v', 118), 'key' => 'value', 'key2' => 'value2'], 14, 'key=value'],
        ];
    }

    #[DataProvider('parseProvider')]
    public function test_parse(string $tracestate, ?string $expected): void
    {
        $this->logger
            ->expects($expected === null ? $this->once() : $this->never())
            ->method('log')
            ->with('warning', $this->anything(), $this->anything());

        $traceState = new TraceState($tracestate);
        $this->assertSame($expected ?? '', (string) $traceState);
    }

    public static function parseProvider(): array
    {
        return [
            ['key=value', 'key=value'],
            ['key=value,key2=value2', 'key=value,key2=value2'],
            ['key=value,key2=value2,key=value3', 'key=value,key2=value2'],

            ['', ''],
            ['  ', ''],
            ['key=value,,key2=value2', 'key=value,key2=value2'],
            ['key=value,   ,key2=value2', 'key=value,key2=value2'],

            ['0', null],
            ['key =value', null],
        ];
    }
}
