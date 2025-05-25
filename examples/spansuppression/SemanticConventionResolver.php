<?php

declare(strict_types=1);

namespace OpenTelemetry\Example;

use OpenTelemetry\API\Instrumentation\SpanSuppression\SemanticConvention;
use OpenTelemetry\API\Trace\SpanKind;

final class SemanticConventionResolver implements \OpenTelemetry\API\Instrumentation\SpanSuppression\SemanticConventionResolver
{
    public function resolveSemanticConventions(string $name, ?string $version, ?string $schemaUrl): array
    {
        if ($schemaUrl === null || !\str_starts_with($schemaUrl, 'https://opentelemetry.io/schemas/')) {
            return [];
        }

        return [
            new SemanticConvention('span.azure.cosmosdb.client', SpanKind::KIND_CLIENT, ['db.operation.name'], ['azure.client.id', 'azure.cosmosdb.request.body.size', 'error.type', 'azure.cosmosdb.consistency.level', 'azure.cosmosdb.response.sub_status_code', 'az.namespace', 'azure.cosmosdb.connection.mode', 'azure.cosmosdb.operation.contacted_regions', 'azure.cosmosdb.operation.request_charge', 'user_agent.original', 'db.query.text', 'db.operation.batch.size', 'db.stored_procedure.name', 'server.address', 'db.query.parameter', 'db.namespace', 'db.collection.name', 'db.response.returned_rows', 'db.response.status_code', 'server.port']),
            new SemanticConvention('span.cicd.pipeline.task.internal', SpanKind::KIND_INTERNAL, ['cicd.pipeline.task.name', 'cicd.pipeline.task.run.id', 'cicd.pipeline.task.run.url.full'], ['error.type']),
            new SemanticConvention('span.db.client', SpanKind::KIND_CLIENT, ['db.system.name'], ['error.type', 'network.peer.address', 'network.peer.port', 'db.operation.batch.size', 'db.response.status_code', 'db.stored_procedure.name', 'db.operation.name', 'server.address', 'server.port', 'db.query.parameter', 'db.query.summary', 'db.query.text', 'db.collection.name', 'db.namespace', 'db.response.returned_rows']),
            new SemanticConvention('span.db.elasticsearch.client', SpanKind::KIND_CLIENT, ['http.request.method', 'url.full', 'db.operation.name'], ['error.type', 'elasticsearch.node.name', 'db.operation.batch.size', 'server.address', 'server.port', 'db.collection.name', 'db.namespace', 'db.operation.parameter', 'db.query.text', 'db.response.status_code']),
            new SemanticConvention('span.db.hbase.client', SpanKind::KIND_CLIENT, ['db.operation.name'], ['error.type', 'db.operation.batch.size', 'server.address', 'server.port', 'db.collection.name', 'db.namespace', 'db.response.status_code']),
            new SemanticConvention('span.db.mongodb.client', SpanKind::KIND_CLIENT, ['db.collection.name', 'db.operation.name'], ['error.type', 'db.operation.batch.size', 'server.address', 'server.port', 'db.namespace', 'db.response.status_code']),
            new SemanticConvention('span.db.redis.client', SpanKind::KIND_CLIENT, ['db.operation.name'], ['error.type', 'network.peer.port', 'network.peer.address', 'db.operation.batch.size', 'server.address', 'server.port', 'db.namespace', 'db.query.text', 'db.response.status_code', 'db.stored_procedure.name']),
            new SemanticConvention('span.http.client', SpanKind::KIND_CLIENT, ['http.request.method', 'server.address', 'server.port', 'url.full'], ['network.peer.address', 'network.peer.port', 'error.type', 'http.request.body.size', 'http.request.header', 'http.request.method_original', 'http.request.resend_count', 'http.request.size', 'http.response.body.size', 'http.response.header', 'http.response.size', 'http.response.status_code', 'network.protocol.name', 'network.protocol.version', 'network.transport', 'user_agent.original', 'user_agent.synthetic.type', 'url.scheme', 'url.template']),
            new SemanticConvention('span.http.server', SpanKind::KIND_SERVER, ['http.request.method', 'url.path', 'url.scheme'], ['network.peer.address', 'network.peer.port', 'error.type', 'http.request.body.size', 'http.request.method_original', 'http.request.size', 'http.response.body.size', 'http.response.header', 'http.response.size', 'http.response.status_code', 'network.protocol.name', 'network.protocol.version', 'network.transport', 'user_agent.synthetic.type', 'client.address', 'client.port', 'http.request.header', 'http.route', 'network.local.address', 'network.local.port', 'user_agent.original', 'server.address', 'server.port', 'url.query']),
        ];
    }
}
