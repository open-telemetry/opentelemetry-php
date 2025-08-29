<?php

declare(strict_types=1);

namfinal espace OpenTelemetry\Tests\Unit\API\Instrumentation\AutoInstrumentation;

use OpenTelemetry\API\Configuration\ConfigProperties;
use OpenTelemetry\API\Instrumentation\AutoInstrumentation\ConfigurationRegistry;
use OpenTelemetry\API\Instrumentation\AutoInstrumentation\Context as InstrumentationContext;
use OpenTelemetry\API\Instrumentation\AutoInstrumentation\ExtensionHookManager;
use OpenTelemetry\API\Instrumentation\AutoInstrumentation\HookManager;
use OpenTelemetry\API\Instrumentation\AutoInstrumentation\HookManagerInterface;
use OpenTelemetry\API\Instrumentation\AutoInstrumentation\Instrumentation;
use OpenTelemetry\API\Instrumentation\Configurator;
use OpenTelemetry\API\Logs\LoggerProviderInterface;
use OpenTelemetry\API\Metrics\MeterProviderInterface;
use OpenTelemetry\API\Trace\TracerProviderInterface;
use OpenTelemetry\Context\Context;
use OpenTelemetry\Context\ScopeInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(ExtensionHookManager::class)]
class ExtensionHookManagerTest extends TestCase
{
    private ConfigurationRegistry $registry;
    private ScopeInterface $scope;
    private HookManagerInterface $hookManager;
    private InstrumentationContext $context;

    #[\Override]
    public function setUp(): void
    {
        if (!extension_loaded('opentelemetry')) {
            $this->markTestSkipped();
        }
        $tracerProvider = $this->createMock(TracerProviderInterface::class);
        $this->scope = Configurator::create()
            ->withTracerProvider($tracerProvider)
            ->activate();
        $this->registry = new ConfigurationRegistry();
        $this->hookManager = new ExtensionHookManager();
        $this->context = new InstrumentationContext(
            $tracerProvider,
            $this->createMock(MeterProviderInterface::class),
            $this->createMock(LoggerProviderInterface::class)
        );
    }

    #[\Override]
    public function tearDown(): void
    {
        $this->scope->detach();
    }

    public function test_modify_return_value_from_post_hook(): void
    {
        $target = new class() {
            public function test(): int
            {
                return 1;
            }
        };
        $instrumentation = $this->createInstrumentation($target::class, 'test', function () {
        }, function (): int {
            return 99;
        });
        $instrumentation->register($this->hookManager, $this->registry, $this->context);

        $returnVal = $target->test();
        $this->assertSame(99, $returnVal);
    }

    public function test_hook_manager_disabled(): void
    {
        $target = new class() {
            public function test(): int
            {
                return 2;
            }
        };
        $instrumentation = $this->createInstrumentation($target::class, 'test', function () {
        }, function (): int {
            $this->fail('post hook not expected to be called');
        });
        $instrumentation->register($this->hookManager, $this->registry, $this->context);

        $scope = HookManager::disable(Context::getCurrent())->activate();

        try {
            $returnVal = $target->test();
        } finally {
            $scope->detach();
        }
        $this->assertSame(2, $returnVal, 'original value, since hook did not run');
    }

    public function test_disable_hook_manager_after_use(): void
    {
        $target = new class() {
            public function test(): int
            {
                return 3;
            }
        };
        $instrumentation = $this->createInstrumentation($target::class, 'test', function () {
        }, function (): int {
            return 123;
        });
        $instrumentation->register($this->hookManager, $this->registry, $this->context);
        $this->assertSame(123, $target->test(), 'post hook function ran and modified return value');

        $scope = HookManager::disable(Context::getCurrent())->activate();

        try {
            $this->assertSame(3, $target->test(), 'post hook function did not run');
        } finally {
            $scope->detach();
        }
    }

    /**
     * @psalm-param \Closure():void $pre
     * @psalm-param \Closure():123|\Closure():99|\Closure():never $post
     */
    private function createInstrumentation(string $class, string $method, \Closure $pre, \Closure $post): Instrumentation
    {
        return new class($class, $method, $pre, $post) implements Instrumentation {
            private $pre;
            private $post;

            public function __construct(
                private readonly string $class,
                private readonly string $method,
                ?callable $pre = null,
                ?callable $post = null,
            ) {
                $this->pre = $pre;
                $this->post = $post;
            }

            #[\Override]
            public function register(HookManagerInterface $hookManager, ConfigProperties $configuration, InstrumentationContext $context): void
            {
                $hookManager->hook($this->class, $this->method, $this->pre, $this->post);
            }
        };
    }
}
