<?php

declare(strict_types=1);

namespace OpenTelemetry\API\Instrumentation\AutoInstrumentation;

use Closure;
use OpenTelemetry\Context\ContextInterface;
use Throwable;

interface HookManager
{

    /**
     * @param Closure(object|string|null,array,string,string,string|null,int|null):void|null $preHook
     * @param Closure(object|string|null,array,mixed,Throwable|null,string,string,string|null,int|null):void|null $postHook
     */
    public function hook(?string $class, string $function, ?Closure $preHook = null, ?Closure $postHook = null): void;

    public static function enable(ContextInterface $context): ContextInterface;

    public static function disable(ContextInterface $context): ContextInterface;
}
