<?php

declare(strict_types=1);

namespace SDK\Trace;

use OpenTelemetry\API\Trace\SpanKind;
use OpenTelemetry\API\Trace\SpanSuppression\SemanticConvention;
use OpenTelemetry\API\Trace\SpanSuppression\SemanticConventionResolver;
use OpenTelemetry\Context\Context;
use OpenTelemetry\SDK\Trace\SpanSuppression\NoopSuppressionStrategy\NoopSuppression;
use OpenTelemetry\SDK\Trace\SpanSuppression\NoopSuppressionStrategy\NoopSuppressionStrategy;
use OpenTelemetry\SDK\Trace\SpanSuppression\NoopSuppressionStrategy\NoopSuppressor;
use OpenTelemetry\SDK\Trace\SpanSuppression\SemanticConventionSuppressionStrategy\SemanticConventionSuppression;
use OpenTelemetry\SDK\Trace\SpanSuppression\SemanticConventionSuppressionStrategy\SemanticConventionSuppressionStrategy;
use OpenTelemetry\SDK\Trace\SpanSuppression\SemanticConventionSuppressionStrategy\SemanticConventionSuppressor;
use OpenTelemetry\SDK\Trace\SpanSuppression\SpanKindSuppressionStrategy\SpanKindSuppression;
use OpenTelemetry\SDK\Trace\SpanSuppression\SpanKindSuppressionStrategy\SpanKindSuppressionStrategy;
use OpenTelemetry\SDK\Trace\SpanSuppression\SpanKindSuppressionStrategy\SpanKindSuppressor;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(NoopSuppressionStrategy::class)]
#[CoversClass(NoopSuppressor::class)]
#[CoversClass(NoopSuppression::class)]
#[CoversClass(SpanKindSuppressionStrategy::class)]
#[CoversClass(SpanKindSuppressor::class)]
#[CoversClass(SpanKindSuppression::class)]
#[CoversClass(SemanticConventionSuppressionStrategy::class)]
#[CoversClass(SemanticConventionSuppressor::class)]
#[CoversClass(SemanticConventionSuppression::class)]
class SpanSuppressionTest extends TestCase
{
    public function test_noop_suppression_strategy_is_never_suppressed(): void
    {
        $strategy = new NoopSuppressionStrategy();
        $suppressor = $strategy->getSuppressor('test', null, null);
        $suppression = $suppressor->resolveSuppression(SpanKind::KIND_CLIENT, []);

        $context = Context::getCurrent();
        $this->assertFalse($suppression->isSuppressed($context));

        $context = $suppression->suppress($context);
        $this->assertFalse($suppression->isSuppressed($context));
    }

    public function test_span_kind_suppression_strategy_only_affects_specific_span_kind(): void
    {
        $strategy = new SpanKindSuppressionStrategy();
        $suppressor = $strategy->getSuppressor('test', null, null);

        $clientSuppression = $suppressor->resolveSuppression(SpanKind::KIND_CLIENT, []);
        $serverSuppression = $suppressor->resolveSuppression(SpanKind::KIND_SERVER, []);

        $context = Context::getCurrent();
        $this->assertFalse($clientSuppression->isSuppressed($context));
        $this->assertFalse($serverSuppression->isSuppressed($context));

        $context = $clientSuppression->suppress($context);
        $this->assertTrue($clientSuppression->isSuppressed($context));
        $this->assertFalse($serverSuppression->isSuppressed($context));
    }

    public function test_span_kind_suppression_strategy_internal_is_never_suppressed(): void
    {
        $strategy = new SpanKindSuppressionStrategy();
        $suppressor = $strategy->getSuppressor('test', null, null);

        $internalSuppression = $suppressor->resolveSuppression(SpanKind::KIND_INTERNAL, []);

        $context = Context::getCurrent();
        $this->assertFalse($internalSuppression->isSuppressed($context));

        $context = $internalSuppression->suppress($context);
        $this->assertFalse($internalSuppression->isSuppressed($context));
    }

    public function test_semantic_convention_suppression_strategy_affects_only_resolved_semantic_convention(): void
    {
        $strategy = new SemanticConventionSuppressionStrategy([
            new class() implements SemanticConventionResolver {
                #[\Override]
                public function resolveSemanticConventions(string $name, ?string $version, ?string $schemaUrl): array
                {
                    return [
                        new SemanticConvention('span.db.elasticsearch.client', SpanKind::KIND_CLIENT, ['http.request.method', 'url.full', 'db.operation.name'], ['error.type', 'elasticsearch.node.name', 'db.operation.batch.size', 'server.address', 'server.port', 'db.collection.name', 'db.namespace', 'db.operation.parameter', 'db.query.text', 'db.response.status_code', 'code.*']),
                        new SemanticConvention('span.http.client', SpanKind::KIND_CLIENT, ['http.request.method', 'server.address', 'server.port', 'url.full'], ['network.peer.address', 'network.peer.port', 'error.type', 'http.request.body.size', 'http.request.header.*', 'http.request.method_original', 'http.request.resend_count', 'http.request.size', 'http.response.body.size', 'http.response.header.*', 'http.response.size', 'http.response.status_code', 'network.protocol.name', 'network.protocol.version', 'network.transport', 'user_agent.original', 'user_agent.synthetic.type', 'url.scheme', 'url.template', 'code.*']),
                    ];
                }
            },
        ]);
        $suppressor = $strategy->getSuppressor('test', null, null);

        $elasticsearchSuppression = $suppressor->resolveSuppression(SpanKind::KIND_CLIENT, [
            'http.request.method' => 'GET',
            'server.address' => 'https://example.com',
            'server.port' => '443',
            'url.full' => 'https://example.com',
            'db.operation.name' => 'SELECT',
        ]);
        $httpSuppression = $suppressor->resolveSuppression(SpanKind::KIND_CLIENT, [
            'http.request.method' => 'GET',
            'server.address' => 'https://example.com',
            'server.port' => '443',
            'url.full' => 'https://example.com',
        ]);
        $httpSuppression2 = $suppressor->resolveSuppression(SpanKind::KIND_CLIENT, [
            'http.request.method' => 'GET',
            'server.address' => 'https://example.com',
            'server.port' => '443',
            'url.full' => 'https://example.com',
            'url.scheme' => 'https',
            'http.request.body.size' => 0,
            'network.peer.address' => '127.0.0.1',
            'network.peer.port' => '443',
            'network.protocol.name' => 'http',
            'network.transport' => 'tcp',
            'user_agent.original' => 'i-am-a-test',
        ]);

        $context = Context::getCurrent();
        $this->assertFalse($elasticsearchSuppression->isSuppressed($context));
        $this->assertFalse($httpSuppression->isSuppressed($context));
        $this->assertFalse($httpSuppression2->isSuppressed($context));

        $context = $elasticsearchSuppression->suppress($context);
        $this->assertTrue($elasticsearchSuppression->isSuppressed($context));
        $this->assertFalse($httpSuppression->isSuppressed($context));
        $this->assertFalse($httpSuppression2->isSuppressed($context));

        $context = $httpSuppression->suppress($context);
        $this->assertTrue($elasticsearchSuppression->isSuppressed($context));
        $this->assertTrue($httpSuppression->isSuppressed($context));
        $this->assertTrue($httpSuppression2->isSuppressed($context));
    }

    public function test_semantic_convention_suppression_strategy_falls_back_to_span_kind_suppression_for_non_internal(): void
    {
        $strategy = new SemanticConventionSuppressionStrategy([
            new class() implements SemanticConventionResolver {
                #[\Override]
                public function resolveSemanticConventions(string $name, ?string $version, ?string $schemaUrl): array
                {
                    return [
                        new SemanticConvention('span.http.client', SpanKind::KIND_CLIENT, ['http.request.method', 'server.address', 'server.port', 'url.full'], ['network.peer.address', 'network.peer.port', 'error.type', 'http.request.body.size', 'http.request.header.*', 'http.request.method_original', 'http.request.resend_count', 'http.request.size', 'http.response.body.size', 'http.response.header.*', 'http.response.size', 'http.response.status_code', 'network.protocol.name', 'network.protocol.version', 'network.transport', 'user_agent.original', 'user_agent.synthetic.type', 'url.scheme', 'url.template', 'code.*']),
                    ];
                }
            },
        ]);
        $suppressor = $strategy->getSuppressor('test', null, null);

        $httpSuppression = $suppressor->resolveSuppression(SpanKind::KIND_CLIENT, [
            'http.request.method' => 'GET',
            'server.address' => 'https://example.com',
            'server.port' => '443',
            'url.full' => 'https://example.com',
        ]);
        $clientSuppression = $suppressor->resolveSuppression(SpanKind::KIND_CLIENT, []);

        $context = Context::getCurrent();
        $this->assertFalse($httpSuppression->isSuppressed($context));
        $this->assertFalse($clientSuppression->isSuppressed($context));

        $context = $httpSuppression->suppress($context);
        $this->assertTrue($httpSuppression->isSuppressed($context));
        $this->assertTrue($clientSuppression->isSuppressed($context));

        $context = Context::getCurrent();
        $context = $clientSuppression->suppress($context);
        $this->assertFalse($httpSuppression->isSuppressed($context));
        $this->assertTrue($clientSuppression->isSuppressed($context));
    }

    public function test_semantic_convention_suppression_strategy_does_not_fail_on_empty_semantic_conventions(): void
    {
        $this->expectNotToPerformAssertions();

        $strategy = new SemanticConventionSuppressionStrategy([]);
        $strategy->getSuppressor('test', null, null)->resolveSuppression(SpanKind::KIND_CLIENT, []);
    }
}
