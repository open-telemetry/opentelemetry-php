<?php

declare(strict_types=1);

namespace OpenTelemetry\Context;

use function assert;
use function basename;
use function class_exists;
use function count;
use function debug_backtrace;
use const DEBUG_BACKTRACE_IGNORE_ARGS;
use Fiber;
use const PHP_VERSION_ID;
use function register_shutdown_function;
use function spl_object_id;
use function sprintf;
use function trigger_error;

/**
 * @internal
 */
final class DebugScope implements ScopeInterface
{
    private static bool $shutdownHandlerInitialized = false;
    private static bool $finalShutdownPhase = false;

    private ContextStorageScopeInterface $scope;
    private ?int $fiberId;
    private array $createdAt;
    private ?array $detachedAt = null;

    public function __construct(ContextStorageScopeInterface $scope)
    {
        $this->scope = $scope;
        $this->fiberId = self::currentFiberId();
        $this->createdAt = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS);

        if (!self::$shutdownHandlerInitialized) {
            self::$shutdownHandlerInitialized = true;
            register_shutdown_function('register_shutdown_function', static fn () => self::$finalShutdownPhase = true);
        }
    }

    public function detach(): int
    {
        $this->detachedAt ??= debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS);

        $flags = $this->scope->detach();

        if (($flags & ScopeInterface::DETACHED) !== 0) {
            trigger_error(sprintf(
                'Scope: unexpected call to Scope::detach() for scope #%d, scope was already detached %s',
                spl_object_id($this),
                self::formatBacktrace($this->detachedAt),
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
        if (!$this->detachedAt) {
            // Handle destructors invoked during final shutdown
            // DebugScope::__destruct() might be called before fiber finally blocks run
            if (self::$finalShutdownPhase && $this->fiberId !== self::currentFiberId()) {
                return;
            }

            trigger_error(sprintf(
                'Scope: missing call to Scope::detach() for scope #%d, created %s',
                spl_object_id($this->scope),
                self::formatBacktrace($this->createdAt),
            ));
        }
    }

    /**
     * @phan-suppress PhanUndeclaredClassReference
     * @phan-suppress PhanUndeclaredClassMethod
     */
    private static function currentFiberId(): ?int
    {
        if (PHP_VERSION_ID < 80100) {
            return null;
        }

        assert(class_exists(Fiber::class, false));
        if (!$fiber = Fiber::getCurrent()) {
            return null;
        }

        return spl_object_id($fiber);
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
