<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\Unit\SDK\Metrics;

use OpenTelemetry\SDK\Metrics\HasLabelsTrait;
use PHPUnit\Framework\TestCase;

/**
 * @covers OpenTelemetry\SDK\Metrics\HasLabelsTrait
 */
class HasLabelTest extends TestCase
{
    protected $labelable;

    public function setUp(): void
    {
        $this->labelable = new class() {
            use HasLabelsTrait;
        };
    }

    public function test_has_label_accepts_values(): void
    {
        $this->assertEmpty($this->labelable->getLabels());

        $expected = ['label_one', 'label_two'];

        $this->labelable->setLabels($expected);

        $this->assertSame($expected, $this->labelable->getLabels());
    }

    public function test_has_label_accepts_only_strings(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        $this->labelable->setLabels([new \stdClass()]);
    }
}
