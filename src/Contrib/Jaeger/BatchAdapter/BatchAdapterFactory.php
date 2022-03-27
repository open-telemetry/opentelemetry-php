<?php

declare(strict_types=1);

namespace OpenTelemetry\Contrib\Jaeger\BatchAdapter;

class BatchAdapterFactory implements BatchAdapterFactoryInterface
{
    public function createBatchAdapter(array $vals): BatchAdapterInterface
    {
        return new BatchAdapter($vals);
    }
}