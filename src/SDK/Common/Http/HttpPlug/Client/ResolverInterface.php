<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Common\Http\HttpPlug\Client;

use Http\Client\HttpAsyncClient;
use Http\Client\HttpClient;

interface ResolverInterface
{
    public function resolveHttpPlugClient(): HttpClient;

    public function resolveHttpPlugAsyncClient(): HttpAsyncClient;
}
