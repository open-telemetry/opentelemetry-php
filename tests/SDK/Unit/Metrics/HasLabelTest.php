<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\SDK\Unit\Metrics;

use OpenTelemetry\SDK\Metrics\HasLabelsTrait;
use PHPUnit\Framework\TestCase;

class HasLabelTest extends TestCase
{
    protected $labelable;

    public function setUp(): void
    {
        $this->labelable = new class() {
            use HasLabelsTrait;
        };
    }

    public function testHasLabelAcceptsValues()
    {
        $this->assertEmpty($this->labelable->getLabels());

        $expected = ['label_one', 'label_two'];

        $this->labelable->setLabels($expected);

        $this->assertSame($expected, $this->labelable->getLabels());
    }

    public function testHasLabelAcceptsOnlyStrings()
    {
        $this->expectException(\InvalidArgumentException::class);

        $this->labelable->setLabels([new \stdClass()]);
    }
}
