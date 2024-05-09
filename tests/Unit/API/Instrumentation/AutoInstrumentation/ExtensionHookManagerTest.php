<?php

declare(strict_types=1);

namespace Unit\API\Instrumentation\AutoInstrumentation;

use OpenTelemetry\API\Instrumentation\AutoInstrumentation\ConfigurationRegistry;
use OpenTelemetry\API\Instrumentation\AutoInstrumentation\Context as InstrumentationContext;
use OpenTelemetry\API\Instrumentation\AutoInstrumentation\ExtensionHookManager;
use OpenTelemetry\API\Instrumentation\AutoInstrumentation\HookManager;
use OpenTelemetry\API\Instrumentation\AutoInstrumentation\Instrumentation;
use OpenTelemetry\API\Trace\TracerProviderInterface;
use OpenTelemetry\Context\Context;
use OpenTelemetry\Context\ContextStorageInterface;
use OpenTelemetry\Context\ScopeInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(ExtensionHookManager::class)]
class ExtensionHookManagerTest extends TestCase
{
    private object $target;
    private InstrumentationContext $context;
    private ConfigurationRegistry $registry;
    private ScopeInterface $scope;
    private HookManager $hookManager;
    private ContextStorageInterface $storage;

    public function setUp(): void
    {
        if (!extension_loaded('opentelemetry')) {
            $this->markTestSkipped();
        }
        $tracerProvider = $this->createMock(TracerProviderInterface::class);
        $this->context = new InstrumentationContext($tracerProvider);
        $this->registry = new ConfigurationRegistry();
        $this->hookManager = new ExtensionHookManager();
        $this->storage = Context::storage();
        $this->scope = $this->storage->attach($this->hookManager->enable($this->storage->current()));
        $this->target = new class() {
            public function test(): int
            {
                return 3;
            }
        };
    }

    public function tearDown(): void
    {
        $this->scope->detach();
    }

    public function test_modify_return_value_from_post_hook(): void
    {
        $instrumentation = $this->createInstrumentation($this->target::class, 'test', function () {
        }, function (): int {
            return 99;
        });
        $instrumentation->register($this->hookManager, $this->context, $this->registry, $this->storage);

        $returnVal = $this->target->test();
        $this->assertSame(99, $returnVal);
    }

    public function test_modify_return_value_from_post_hook_when_hook_manager_disabled(): void
    {
        $scope = $this->storage->attach($this->hookManager->disable($this->storage->current()));
        $instrumentation = $this->createInstrumentation($this->target::class, 'test', function () {
        }, function (): int {
            return 99;
        });
        $instrumentation->register($this->hookManager, $this->context, $this->registry, $this->storage);

        try {
            $returnVal = $this->target->test();
            $this->assertSame(3, $returnVal, 'original value, since hook did not run');
        } finally {
            $scope->detach();
        }
    }

    private function createInstrumentation(string $class, string $method, $pre, $post): Instrumentation
    {
        return new class($class, $method, $pre, $post) implements Instrumentation {
            private $pre;
            private $post;
            public function __construct(private readonly string $class, private readonly string $method, ?callable $pre = null, ?callable $post = null)
            {
                $this->pre = $pre;
                $this->post = $post;
            }
            public function register(HookManager $hookManager, InstrumentationContext $context, ConfigurationRegistry $configuration, ContextStorageInterface $storage): void
            {
                $hookManager->hook($this->class, $this->method, $this->pre, $this->post); /*function(){
                    echo 'pre';
                }, function(){
                    echo 'post';
                });*/
            }
        };
    }
}
