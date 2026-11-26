<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\Unit\SDK\Common\Configuration;

use OpenTelemetry\API\Behavior\Internal\Logging;
use OpenTelemetry\API\Behavior\Internal\LogWriter\LogWriterInterface;
use OpenTelemetry\SDK\Common\Configuration\EnvResolver;
use OpenTelemetry\SDK\Common\Configuration\Variables;
use OpenTelemetry\Tests\TestState;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Psr\Log\LogLevel;

#[CoversClass(EnvResolver::class)]
class EnvResolverTest extends TestCase
{
    use TestState;

    public function test_int(): void
    {
        $this->setEnvironmentVariable('OTEL_FOO', '42');

        $this->assertSame(42, (new EnvResolver())->int('OTEL_FOO'));
    }

    /**
     * @see https://github.com/open-telemetry/opentelemetry-specification/blob/v1.62.0/specification/configuration/sdk-environment-variables.md#numeric
     */
    public function test_invalid_int_is_treated_as_unset(): void
    {
        $this->expectWarning('Invalid integer value "foo" for OTEL_FOO');
        $this->setEnvironmentVariable('OTEL_FOO', 'foo');

        $this->assertNull((new EnvResolver())->int('OTEL_FOO'));
    }

    /**
     * @see https://github.com/open-telemetry/opentelemetry-specification/blob/v1.62.0/specification/configuration/sdk-environment-variables.md#numeric
     */
    public function test_invalid_int_with_library_default_is_treated_as_unset(): void
    {
        $this->expectWarning('Invalid integer value "abc" for OTEL_BSP_MAX_QUEUE_SIZE');
        $this->setEnvironmentVariable(Variables::OTEL_BSP_MAX_QUEUE_SIZE, 'abc');

        $this->assertNull((new EnvResolver())->int(Variables::OTEL_BSP_MAX_QUEUE_SIZE));
    }

    public function test_int_out_of_range_is_treated_as_unset(): void
    {
        $this->setEnvironmentVariable('OTEL_FOO', '-1');

        $this->assertNull((new EnvResolver())->int('OTEL_FOO'));
    }

    public function test_numeric(): void
    {
        $this->setEnvironmentVariable('OTEL_FOO', '1.5');

        $this->assertSame(1.5, (new EnvResolver())->numeric('OTEL_FOO'));
    }

    /**
     * @see https://github.com/open-telemetry/opentelemetry-specification/blob/v1.62.0/specification/configuration/sdk-environment-variables.md#numeric
     */
    public function test_invalid_numeric_is_treated_as_unset(): void
    {
        $this->expectWarning('Invalid float value "foo" for OTEL_FOO');
        $this->setEnvironmentVariable('OTEL_FOO', 'foo');

        $this->assertNull((new EnvResolver())->numeric('OTEL_FOO'));
    }

    private function expectWarning(string $message): void
    {
        $logWriter = $this->createMock(LogWriterInterface::class);
        $logWriter->expects($this->once())
            ->method('write')
            ->with(LogLevel::WARNING, $this->stringContains($message), $this->anything());
        Logging::setLogWriter($logWriter);
    }
}
