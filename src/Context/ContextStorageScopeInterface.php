<?php declare(strict_types=1);
namespace OpenTelemetry\Context;

interface ContextStorageScopeInterface extends ScopeInterface {

    public function context(): Context;
}
