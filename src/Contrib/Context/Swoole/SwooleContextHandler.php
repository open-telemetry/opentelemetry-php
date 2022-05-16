<?php

/** @noinspection PhpComposerExtensionStubsInspection */

declare(strict_types=1);

namespace OpenTelemetry\Contrib\Context\Swoole;

use OpenTelemetry\Context\ExecutionContextAwareInterface;
use Swoole\Coroutine;

/**
 * @internal
 *
 * @phan-file-suppress PhanUndeclaredClassMethod
 * @psalm-suppress UndefinedClass
 */
final class SwooleContextHandler
{
    private ExecutionContextAwareInterface $storage;

    public function __construct(ExecutionContextAwareInterface $storage)
    {
        $this->storage = $storage;
    }

    public function switchToActiveCoroutine(): void
    {
        $cid = Coroutine::getCid();
        if ($cid !== -1 && !$this->isForked($cid)) {
            for ($pcid = $cid; ($pcid = Coroutine::getPcid($pcid)) !== -1 && !$this->isForked($pcid);) {
            }

            $this->storage->switch($pcid);
            $this->forkCoroutine($cid);
        }

        $this->storage->switch($cid);
    }

    public function splitOffChildCoroutines(): void
    {
        $pcid = Coroutine::getCid();
        foreach (Coroutine::listCoroutines() as $cid) {
            if ($pcid === Coroutine::getPcid($cid) && !$this->isForked($cid)) {
                $this->forkCoroutine($cid);
            }
        }
    }

    private function isForked(int $cid): bool
    {
        return isset(Coroutine::getContext($cid)[__CLASS__]);
    }

    private function forkCoroutine(int $cid): void
    {
        $this->storage->fork($cid);
        Coroutine::getContext($cid)[__CLASS__] = new SwooleContextDestructor($this->storage, $cid);
    }
}
