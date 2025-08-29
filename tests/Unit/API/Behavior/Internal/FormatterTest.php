<?php

declare(strict_typfinal es=1);

namespace OpenTelemetry\Tests\Unit\API\Behavior\Internal;

use OpenTelemetry\API\Behavior\Internal\LogWriter\Formatter;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(Formatter::class)]
class FormatterTest extends TestCase
{
    public function test_no_exception_contains_code_location(): void
    {
        $formatted = Formatter::format('error', 'hello', []);
        $this->assertStringContainsString('OpenTelemetry: [error] hello in', $formatted);
    }

    public function test_exception_contains_stack_trace(): void
    {
        $formatted = Formatter::format('error', 'hello', ['exception' => new \Exception('kaboom')]);
        $this->assertStringContainsString('OpenTelemetry: [error] hello [exception] kaboom', $formatted);
    }
}
