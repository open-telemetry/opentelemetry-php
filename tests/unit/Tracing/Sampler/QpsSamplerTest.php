<?php

namespace OpenTelemetry\Tests\Unit\Tracing\Sampler;

use OpenTelemetry\Tracing\Sampler\QpsSampler;
use Psr\Cache\CacheItemInterface;
use Psr\Cache\CacheItemPoolInterface;
use Prophecy\Argument;
use PHPUnit\Framework\TestCase;

/**
 * @group trace
 */
class QpsSamplerTest extends TestCase
{

    public function testCachedValue()
    {
        $item = $this->prophesize(CacheItemInterface::class);
        $item->get()->willReturn(microtime(true) + 100);
        $cache = $this->prophesize(CacheItemPoolInterface::class);
        $cache->getItem(Argument::any())->willReturn($item->reveal());

        $sampler = new QpsSampler($cache->reveal());
        $this->assertFalse($sampler->shouldSample());
    }

    public function testCachedValueExpired()
    {
        $item = $this->prophesize(CacheItemInterface::class);
        $item->get()->willReturn(microtime(true) - 100);
        $cache = $this->prophesize(CacheItemPoolInterface::class);
        $cache->getItem(Argument::any())->willReturn($item->reveal());
        $cache->save(Argument::any())->willReturn(true);

        $sampler = new QpsSampler($cache->reveal());
        $this->assertTrue($sampler->shouldSample());
    }

    public function testNotCached()
    {
        $cache = $this->prophesize(CacheItemPoolInterface::class);
        $cache->getItem(Argument::any())->willReturn(null);
        $cache->save(Argument::any())->willReturn(true);

        $sampler = new QpsSampler($cache->reveal());
        $this->assertTrue($sampler->shouldSample());
    }

    /**
     * @dataProvider invalidRates
     * @expectedException \InvalidArgumentException
     */
    //public function testInvalidRate($rate)
    //{
    //    $cache = $this->prophesize(CacheItemPoolInterface::class);
    //    $sampler = new QpsSampler($cache->reveal(), [
    //        'rate' => $rate
    //    ]);
    //}

    public function invalidRates()
    {
        return [
            [0],
            [-1],
            [10],
            [1.1]
        ];
    }
}
