<?php

declare(strict_types=1);

namespace OpenTelemetry\Context;

class Scope
{
    /** @var ScopeInterface Context token, result of Context::attach() */
    private ScopeInterface $contextToken;

    public function __construct(ScopeInterface $contextToken)
    {
        $this->contextToken = $contextToken;
    }

    public function close(): void
    {
        $this->contextToken->detach();
    }
}
