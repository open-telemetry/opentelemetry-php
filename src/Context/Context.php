<?php

declare(strict_types=1);

namespace OpenTelemetry\Context;

/**
 * @see https://github.com/open-telemetry/opentelemetry-specification/blob/v1.6.1/specification/context/context.md#overview
 */
final class Context
{
    private static ?ContextStorageInterface $storage = null;

    /** @var self|null */
    private static $root;

    /**
     * @internal
     */
    public static function setStorage(ContextStorageInterface $storage): void
    {
        self::$storage = $storage;
    }

    /**
     * @internal
     */
    public static function storage(): ContextStorageInterface
    {
        return self::$storage ??= new ContextStorage(self::getRoot());
    }

    /**
     * This will set the given Context to be the "current" one. We return a token which can be passed to `detach()` to
     * reset the Current Context back to the previous one.
     */
    public static function attach(Context $ctx): ScopeInterface
    {
        return self::storage()->attach($ctx);
    }

    /**
     * @param non-empty-string $key
     *
     * @see https://github.com/open-telemetry/opentelemetry-specification/blob/v1.6.1/specification/context/context.md#create-a-key
     */
    public static function createKey(string $key): ContextKey
    {
        return new ContextKey($key);
    }

    /**
     * Given a token, the current context will be set back to the one prior to the token being generated.
     */
    public static function detach(ScopeInterface $token): Context
    {
        $token->detach();

        return self::getCurrent();
    }

    public static function getCurrent(): Context
    {
        return self::storage()->current();
    }

    public static function getRoot(): self
    {
        if (null === self::$root) {
            self::$root = new self();
        }

        return self::$root;
    }

    /**
     * Static version of get()
     * This is primarily useful when the caller doesn't already have a reference to the Context that they want to mutate.
     * This will operate on the "current" global context in that scenario.
     *
     * There are two ways to call this function:
     * 1) With a $ctx value:
     *    Context::getValue($key, $ctx) is functionally equivalent to $ctx->get($key)
     * 2) Without a $ctx value:
     *    This will fetch the "current" Context if one exists or create one if not, then attempt to get the value from it.
     *
     * @param ContextKey $key
     * @param Context|null $ctx
     *
     * @return mixed
     */
    public static function getValue(ContextKey $key, $ctx=null)
    {
        $ctx = $ctx ?? static::getCurrent();

        return $ctx->get($key);
    }

    /**
     * This is a static version of set().
     * This is primarily useful when the caller doesn't already have a reference to a Context that they want to mutate.
     *
     * There are two ways to call this function.
     * 1) With a $parent parameter:
     *    Context::setValue($key, $value, $ctx) is functionally equivalent to $ctx->set($key, $value)
     * 2) Without a $parent parameter:
     *    In this scenario, setValue() will use the `$current_context` reference as supplied by `getCurrent()`
     *    `getCurrent()` will always return a valid Context. If one does not exist at the global scope,
     *    an "empty" context will be created.
     *
     * @param ContextKey $key
     * @param mixed $value
     * @param Context|null $parent
     *
     * @return Context a new Context containing the k/v
     */
    public static function withValue(ContextKey $key, $value, $parent=null)
    {
        if (null === $parent) {
            // TODO This should not attach, leads to a context that cannot be detached
            self::storage()->attach(new self($key, $value, self::getCurrent()));

            return self::getCurrent();
        }

        return new self($key, $value, $parent);
    }

    /**
     * @var ContextKey|null
     */
    protected $key;

    /**
     * @var mixed|null
     */
    protected $value;

    /** @var self|null */
    protected $parent;

    /**
     * This is a general purpose read-only key-value store. Read-only in the sense that adding a new value does not
     * mutate the existing context, but returns a new Context which has the new value added.
     *
     * In practical terms, this is implemented as a linked list of Context instances, with each one holding a reference
     * to the key object, the value that corresponds to the key, and an optional reference to the parent Context
     * (i.e. the next link in the linked list chain)
     *
     * If you inherit from this class, you should "shadow" $parent into your subclass so that all operations give
     * you back an instance of the same type that you are interacting with and different subclasses should NOT be
     * treated as interoperable. i.e. you should NOT have a Context object chain with both Context instances interleaved
     * with Baggage instances.
     *
     * @param ContextKey|null $key The key object. Should only be null when creating an "empty" context
     * @param mixed|null $value
     * @param self|null $parent Reference to the parent object
     */
    final public function __construct(?ContextKey $key=null, $value=null, $parent=null)
    {
        $this->key = $key;
        $this->value = $value;
        $this->parent = $parent;
    }

    /**
     * This adds a k/v pair to this Context. We do this by instantiating a new Context instance with the k/v and pass
     * a reference to $this as the "parent" creating the linked list chain.
     *
     * @param ContextKey $key
     * @param mixed $value
     *
     * @return Context a new Context containing the k/v
     */
    public function with(ContextKey $key, $value)
    {
        return new self($key, $value, $this);
    }

    /**
     * @todo: Implement this on the API side
     */
    public function withContextValue(ImplicitContextKeyedInterface $value): Context
    {
        return $value->storeInContext($this);
    }

    /**
     * Makes `$this` the currently active {@see Context}.
     *
     * @todo: Implement this on the API side
     */
    public function activate(): ScopeInterface
    {
        return self::attach($this);
    }

    /**
     * Fetch a value from the Context given a key value.
     *
     * @return mixed|null
     */
    public function get(ContextKey $key)
    {
        if ($this->key === $key) {
            return $this->value;
        }

        if (null === $this->parent) {
            return null;
        }

        return $this->parent->get($key);
    }
}
