<?php

declare(strict_types=1);

namespace OpenTelemetry\Context;

/**
 * @ internal //@todo internal or not?
 */
final class ContextStorage implements ContextStorageInterface
{
    private static ?ContextStorageInterface $default = null;
    private static string $defaultStorageClass = ContextStorage::class;
    private static int $id = 0;

    public ContextStorageHead $current;
    private ContextStorageHead $main;
    /** @var array<int, ContextStorageHead> */
    private array $forks = [];
    public string $name; //debugging

    public static function setDefaultStorageClass(string $class): void
    {
        self::$defaultStorageClass = $class;
    }

    public static function create(?string $name = null): ContextStorageInterface
    {
        $context = new Context();
        $self = new self($context, $name ?? 'storage-' . ++self::$id);
        $context->setStorage($self);
        if (self::$defaultStorageClass === FiberNotSupportedContextStorage::class) {
            return new FiberNotSupportedContextStorage($self);
        }

        return $self;
    }

    public static function default(): ContextStorageInterface
    {
        if (self::$default === null) {
            self::$default = self::create('default');
        }

        return self::$default;
    }

    public function __construct(Context $context, string $name)
    {
        $this->current = $this->main = new ContextStorageHead($this);
        $this->current->node = new ContextStorageNode($context, $this->current);
        $this->name = $name;
    }

    public function fork(int $id): void
    {
        $this->forks[$id] = clone $this->current;
    }

    public function switch(int $id): void
    {
        $this->current = $this->forks[$id] ?? $this->main;
    }

    public function destroy(int $id): void
    {
        unset($this->forks[$id]);
    }

    public function current(): Context
    {
        return $this->current->node->context;
    }

    public function attach(Context $context): ScopeInterface
    {
        return $this->current->node = new ContextStorageNode($context, $this->current, $this->current->node);
    }

    private function __clone()
    {
    }
}
