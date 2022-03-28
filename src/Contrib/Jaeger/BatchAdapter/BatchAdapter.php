<?php

declare(strict_types=1);

namespace OpenTelemetry\Contrib\Jaeger\BatchAdapter;

use Jaeger\Thrift\Batch;
use Thrift\Protocol\TProtocol;

class BatchAdapter implements BatchAdapterInterface
{
    private Batch $batchInstance;

    public function __construct(array $values)
    {
        $this->batchInstance = new Batch($values);
    }

    public function write(TProtocol $output): void
    {
        $this->batchInstance->write($output);
    }
}
