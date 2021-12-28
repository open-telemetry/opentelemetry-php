<?php

declare(strict_types=1);

namespace OpenTelemetry\Context;

interface ContextStorageInterface
{
    public function fork(int $id): void;

    public function switch(int $id): void;

    public function destroy(int $id): void;

    public function current(): Context;

    public function attach(Context $context): ScopeInterface;
}
