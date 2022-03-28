<?php

declare(strict_types=1);

namespace OpenTelemetry\Contrib\Jaeger\BatchAdapter;

interface BatchAdapterFactoryInterface
{
    public function createBatchAdapter(array $values): BatchAdapterInterface;
}
