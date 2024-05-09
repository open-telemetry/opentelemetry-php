<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\Unit\SDK\Metrics\View;

use OpenTelemetry\SDK\Common\Attribute\Attributes;
use OpenTelemetry\SDK\Common\Instrumentation\InstrumentationScope;
use OpenTelemetry\SDK\Metrics\Instrument;
use OpenTelemetry\SDK\Metrics\InstrumentType;
use OpenTelemetry\SDK\Metrics\View\CriteriaViewRegistry;
use OpenTelemetry\SDK\Metrics\View\SelectionCriteria\InstrumentNameCriteria;
use OpenTelemetry\SDK\Metrics\View\ViewTemplate;
use OpenTelemetry\SDK\Metrics\ViewProjection;
use PHPUnit\Framework\TestCase;

#[\PHPUnit\Framework\Attributes\CoversClass(\OpenTelemetry\SDK\Metrics\View\CriteriaViewRegistry::class)]
final class CriteriaViewRegistryTest extends TestCase
{
    public function test_empty_registry_returns_null(): void
    {
        $views = new CriteriaViewRegistry();
        $this->assertNull($views->find(
            new Instrument(InstrumentType::COUNTER, 'name', null, null),
            new InstrumentationScope('name', null, null, Attributes::create([])),
        ));
    }

    /**
     * @psalm-suppress InvalidOperand
     */
    public function test_registry_returns_matching_entry(): void
    {
        $views = new CriteriaViewRegistry();
        $views->register(new InstrumentNameCriteria('name'), ViewTemplate::create());
        $this->assertEquals(
            [
                new ViewProjection('name', null, null, null, null),
            ],
            [...$views->find(
                new Instrument(InstrumentType::COUNTER, 'name', null, null),
                new InstrumentationScope('name', null, null, Attributes::create([])),
            )],
        );
    }

    public function test_registry_does_not_return_not_matching_entry(): void
    {
        $views = new CriteriaViewRegistry();
        $views->register(new InstrumentNameCriteria('foo'), ViewTemplate::create());
        $this->assertNull($views->find(
            new Instrument(InstrumentType::COUNTER, 'name', null, null),
            new InstrumentationScope('name', null, null, Attributes::create([])),
        ));
    }
}
