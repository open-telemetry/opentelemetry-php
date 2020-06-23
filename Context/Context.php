<?php

declare(strict_types=1);

namespace OpenTelemetry\Context;

trait Context
{
    /**
     * @var ContextKey|null
     */
    private $key;

    /**
     * @var mixed|null
     */
    private $value;

    /**
     * @var Context|null
     */
    private $parent;

    private static $current_context = null;

    /**
     * The constructor for a Context object. Because this is a trait, a `Context` cannot be instantiated directly,
     * this constructor is intended for use by the classes that use this trait. Throughout the docsblocks and comments
     * in this class, we will refer to "Context instance" which really refers to an instance of the class
     * that uses this trait.
     *
     * This is a general purpose read-only key-value store. Read-only in the sense that adding a new value does not
     * mutate the existing context, but returns a new Context which has the new value added.
     *
     * In practical terms, this is implemented as a linked list of Context instances, with each one holding a reference
     * to the key object, the value that corresponds to the key, and an optional reference to the parent Context
     * (i.e. the next link in the linked list chain)
     *
     * @param ContextKey|null $key The key object. Should only be null when creating an "empty" context
     * @param mixed|null $value
     * @param Context|null $parent Reference to the parent object
     */
    public function __construct(?ContextKey $key=null, $value=null, $parent=null)
    {
        $this->key = $key;
        $this->value = $value;
        $this->parent = $parent;
    }

    /**
     * This adds a k/v pair to this Context. We do this by instantiating a new Context instance with the k/v and pass
     * a reference to $this as the "parent" creating the linked list chain.
     *
     * The object instantiated will be of the same type as the concrete class using this trait as referenced by __CLASS__
     *
     * @param ContextKey $key
     * @param mixed $value
     *
     * @suppress PhanTypeInstantiateTrait
     *
     * @return Context a new Context containing the k/v
     */
    public function set(ContextKey $key, $value)
    {
        $cls = __CLASS__;

        return new $cls($key, $value, $this);
    }

    /**
     * This is a static version of set().
     * This is primarily useful when the caller doesn't already have a reference to a Context that they want to mutate.
     *
     * There are two ways to call this function.
     * 1) With a $parent parameter:
     *    This should be an instance of __CLASS__ which is a concrete class that uses this Trait.
     *    Context::setValue($key, $value, $ctx) is functionally equivalent to $ctx->set($key, $value)
     * 2) Without a $parent parameter:
     *    In this scenario, setValue() will use the `$current_context` reference as supplied by `getCurrent()`
     *    `getCurrent()` will always return a valid Context/__CLASS__. If one does not exist at the global scope,
     *    an "empty" context will be created.
     *
     * @param ContextKey $key
     * @param mixed $value
     * @param Context|null $parent
     *
     * @suppress PhanTypeInstantiateTrait
     *
     * @return Context a new Context containing the k/v
     */
    public static function setValue(ContextKey $key, $value, $parent=null)
    {
        $cls = __CLASS__;
        if (null === $parent) {
            return static::$current_context = new $cls($key, $value, $cls::getCurrent());
        }

        return new $cls($key, $value, $parent);
    }

    /**
     * Fetch a value from the Context given a key value.
     *
     * @param ContextKey $key
     *
     * @throws ContextValueNotFoundException
     * @return mixed
     */
    public function get(ContextKey $key)
    {
        if ($this->key === $key) {
            return $this->value;
        }
        if (null === $this->parent) {
            throw new ContextValueNotFoundException();
        }

        return $this->parent->get($key);
    }

    /**
     * Static version of get()
     * This is primarily useful when the caller doesn't already have a reference to the Context that they want to mutate.
     * This will operate on the "current" global context in that scenario.
     *
     * There are two ways to call this function:
     * 1) With a $ctx value:
     *    This should be an instance of __CLASS__ which is a concrete class that uses this trait.
     *    Context::getValue($key, $ctx) is functionally equivalent to $ctx->get($key)
     * 2) Without a $ctx value:
     *    This will fetch the "current" Context if one exists or create one if not, then attempt to get the value from it.
     *
     * @param ContextKey $key
     * @param Context|null $ctx
     *
     * @throws ContextValueNotFoundException
     * @return mixed
     */
    public static function getValue(ContextKey $key, $ctx=null)
    {
        $cls = __CLASS__;
        $ctx = $ctx ?? $cls::getCurrent();

        return $ctx->get($key);
    }

    /**
     * @suppress PhanTypeInstantiateTrait
     *
     * @return Context
     */
    public static function getCurrent()
    {
        if (null === static::$current_context) {
            $cls = __CLASS__;
            static::$current_context = new $cls();
        }

        return static::$current_context;
    }

    /**
     * This will set the given Context to be the "current" one. We return a token which can be passed to `detach()` to
     * reset the Current Context back to the previous one.
     *
     * @param Context $ctx
     *
     * @return callable token for resetting the $current_context back
     */
    public static function attach($ctx): callable
    {
        $former_ctx = static::$current_context;
        static::$current_context = $ctx;

        return function () use ($former_ctx) {
            return $former_ctx;
        };
    }

    /**
     * Given a token, the current context will be set back to the one prior to the token being generated.
     *
     * @param callable $token
     *
     * @return Context
     */
    public static function detach(callable $token)
    {
        return static::$current_context = call_user_func($token);
    }
}
