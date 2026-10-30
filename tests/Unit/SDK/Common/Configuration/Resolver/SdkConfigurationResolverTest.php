<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\Unit\SDK\Common\Configuration\Resolver;

use OpenTelemetry\SDK\Common\Configuration\Resolver\SdkConfigurationResolver;
use OpenTelemetry\Tests\TestState;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(SdkConfigurationResolver::class)]
class SdkConfigurationResolverTest extends TestCase
{
    use TestState;

    public function test_retrieve_value_trims_spaces_and_tabs(): void
    {
        $this->setEnvironmentVariable('OTEL_TRACES_SAMPLER', " \talways_on \t");

        $this->assertSame('always_on', (new SdkConfigurationResolver())->retrieveValue('OTEL_TRACES_SAMPLER'));
    }

    public function test_whitespace_only_value_is_not_set(): void
    {
        $this->setEnvironmentVariable('OTEL_TRACES_SAMPLER', " \t");

        $this->assertFalse((new SdkConfigurationResolver())->hasVariable('OTEL_TRACES_SAMPLER'));
    }
}
