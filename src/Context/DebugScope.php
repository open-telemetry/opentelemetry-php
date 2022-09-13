<?php

declare(strict_types=1);

namespace OpenTelemetry\Context;

use function basename;
use function count;
use function debug_backtrace;
use const DEBUG_BACKTRACE_IGNORE_ARGS;
use function sprintf;
use function trigger_error;

/**
 * @internal
 */
final class DebugScope implements ScopeInterface
{
    private const DEBUG_TRACE_CREATE = '__debug_trace_create';
    private const DEBUG_TRACE_DETACH = '__debug_trace_detach';

    private ContextStorageScopeInterface $scope;

    public function __construct(ContextStorageScopeInterface $node)
    {
        $this->scope = $node;
        $this->scope[self::DEBUG_TRACE_CREATE] = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS);
    }

    public function detach(): int
    {
        $this->scope[self::DEBUG_TRACE_DETACH] ??= debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS);

        $flags = $this->scope->detach();

        if (($flags & ScopeInterface::DETACHED) !== 0) {
            trigger_error(sprintf(
                'Scope: unexpected call to Scope::detach() for scope #%d, scope was already detached %s',
                spl_object_id($this),
                self::formatBacktrace($this->scope[self::DEBUG_TRACE_DETACH]),
            ));
        } elseif (($flags & ScopeInterface::MISMATCH) !== 0) {
            trigger_error(sprintf(
                'Scope: unexpected call to Scope::detach() for scope #%d, scope successfully detached but another scope should have been detached first',
                spl_object_id($this),
            ));
        } elseif (($flags & ScopeInterface::INACTIVE) !== 0) {
            trigger_error(sprintf(
                'Scope: unexpected call to Scope::detach() for scope #%d, scope successfully detached from different execution context',
                spl_object_id($this),
            ));
        }

        return $flags;
    }

    public function __destruct()
    {
        if (!isset($this->scope[self::DEBUG_TRACE_DETACH])) {
            trigger_error(sprintf(
                'Scope: missing call to Scope::detach() for scope #%d, created %s',
                spl_object_id($this->scope),
                self::formatBacktrace($this->scope[self::DEBUG_TRACE_CREATE]),
            ));
        }
    }

    private static function formatBacktrace(array $trace): string
    {
        $s = '';
        for ($i = 0, $n = count($trace) + 1; ++$i < $n;) {
            $s .= "\n\t";
            $s .= 'at ';
            if (isset($trace[$i]['class'])) {
                $s .= strtr($trace[$i]['class'], ['\\' => '.']);
                $s .= '.';
            }
            $s .= strtr($trace[$i]['function'] ?? '{main}', ['\\' => '.']);
            $s .= '(';
            if (isset($trace[$i - 1]['file'])) {
                $s .= basename($trace[$i - 1]['file']);
                if (isset($trace[$i - 1]['line'])) {
                    $s .= ':';
                    $s .= $trace[$i - 1]['line'];
                }
            } else {
                $s .= 'Unknown Source';
            }
            $s .= ')';
        }

        return $s . "\n";
    }
}
