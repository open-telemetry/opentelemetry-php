<?php

/**
 * Minimal "application" for HttpBodyCaptureServerTest, served by the PHP built-in web server.
 *
 * Builds the server request the way the SDK does, records the body with HttpBodyCapture and afterwards reads
 * php://input like an application would, so the test can verify that capturing does not consume the body.
 */

declare(strict_types=1);

use OpenTelemetry\SDK\Trace\AutoRootSpan;
use OpenTelemetry\SDK\Trace\HttpBodyCapture;

require dirname(__DIR__, 4) . '/../vendor/autoload.php';

/** @psalm-suppress InternalMethod */
$request = AutoRootSpan::createRequest();
$max = filter_var($_GET['max'] ?? 1024, FILTER_VALIDATE_INT) ?: 1024;
$captured = $request === null ? null : HttpBodyCapture::extract($request, $max);
$applicationBody = (string) file_get_contents('php://input');

header('Content-Type: application/json');
echo json_encode([
    'captured' => $captured,
    'application_body' => base64_encode($applicationBody),
    'post_fields' => $_POST,
], JSON_THROW_ON_ERROR);
