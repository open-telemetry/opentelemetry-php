<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Metrics\Stream;

use GMP;

final class Delta
{
    public Metric $metric;
    /**
     * @psalm-suppress UndefinedDocblockClass
     * @var int|GMP
     */
    public $readers;
    public ?self $prev;
    /**
     * @psalm-suppress UndefinedDocblockClass
     * @param int|GMP $readers
     */
    public function __construct(Metric $metric, $readers, ?self $prev = null)
    {
        $this->metric = $metric;
        $this->readers = $readers;
        $this->prev = $prev;
    }
}
