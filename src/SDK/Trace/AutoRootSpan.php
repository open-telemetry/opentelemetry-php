<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Trace;

use Http\Discovery\Exception\NotFoundException;
use Http\Discovery\Psr17FactoryDiscovery;
use Nyholm\Psr7Server\ServerRequestCreator;
use OpenTelemetry\API\Behavior\LogsMessagesTrait;
use OpenTelemetry\API\Common\Time\ClockInterface;
use OpenTelemetry\API\Globals;
use OpenTelemetry\API\Instrumentation\AutoInstrumentation\HookManagerInterface;
use OpenTelemetry\API\Trace\Span;
use OpenTelemetry\API\Trace\SpanInterface;
use OpenTelemetry\API\Trace\SpanKind;
use OpenTelemetry\Context\Context;
use OpenTelemetry\Context\ContextStorageScopeInterface;
use OpenTelemetry\SDK\Common\Configuration\Configuration;
use OpenTelemetry\SDK\Common\Configuration\Variables;
use OpenTelemetry\SDK\Common\Util\ShutdownHandler;
use OpenTelemetry\SDK\Common\Util\WildcardListMatcher;
use OpenTelemetry\SDK\SdkAutoloader;
use OpenTelemetry\SemConv\TraceAttributes;
use OpenTelemetry\SemConv\Version;
use Psr\Http\Message\ServerRequestInterface;

class AutoRootSpan
{
    use LogsMessagesTrait;

    private static ?ContextStorageScopeInterface $rootScope = null;

    /** @var array<string, string[]> Response headers captured via the header() hook, reset for each request */
    private static array $capturedResponseHeaders = [];

    private static bool $responseHeaderHookRegistered = false;

    private static ?HookManagerInterface $hookManager = null;

    /**
     * Whether a root span should be created for the current web request.
     * OTEL_PHP_EXPERIMENTAL_AUTO_ROOT_SPAN is a legacy alias of OTEL_PHP_TRANSACTION_SPAN_ENABLED, used only when the latter is unset.
     */
    public static function isEnabled(): bool
    {
        return
            !empty($_SERVER['REQUEST_METHOD'] ?? null)
            && self::isWebSpanEnabled();
    }

    /**
     * OTEL_PHP_TRANSACTION_SPAN_ENABLED wins when it is set (also to false), otherwise the legacy alias decides.
     */
    private static function isWebSpanEnabled(): bool
    {
        return Configuration::has(Variables::OTEL_PHP_TRANSACTION_SPAN_ENABLED)
            ? Configuration::getBoolean(Variables::OTEL_PHP_TRANSACTION_SPAN_ENABLED)
            // @phan-suppress-next-line PhanDeprecatedClassConstant the deprecated alias is supported on purpose
            : Configuration::getBoolean(Variables::OTEL_PHP_EXPERIMENTAL_AUTO_ROOT_SPAN);
    }

    /**
     * Creates the root span for the current request or CLI script, the same way the OpenTelemetry PHP distro does.
     * Enabled by OTEL_PHP_TRANSACTION_SPAN_ENABLED (web; OTEL_PHP_EXPERIMENTAL_AUTO_ROOT_SPAN is a legacy alias used only when it is unset) or
     * OTEL_PHP_TRANSACTION_SPAN_ENABLED_CLI (CLI). Both default to false, a distro can default them to true.
     * Does nothing when a root span was already started by this class.
     *
     * @param callable|null $notifySpanEnded called at shutdown, right before the root span ends
     */
    public static function start(?callable $notifySpanEnded = null): void
    {
        if (self::$rootScope !== null) {
            self::logDebug('Root span already started');

            return;
        }
        $cli = self::isCliScript();
        if ($cli) {
            if (!Configuration::getBoolean(Variables::OTEL_PHP_TRANSACTION_SPAN_ENABLED_CLI)) {
                self::logDebug('OTEL_PHP_TRANSACTION_SPAN_ENABLED_CLI not set to true');

                return;
            }
        } elseif (!self::isWebSpanEnabled()) {
            self::logDebug('neither OTEL_PHP_TRANSACTION_SPAN_ENABLED nor OTEL_PHP_EXPERIMENTAL_AUTO_ROOT_SPAN set to true');

            return;
        }

        if (SdkAutoloader::isExcludedUrl()) {
            self::logDebug('URL is excluded by OTEL_PHP_EXCLUDED_URLS');

            return;
        }

        $request = self::createRequest();
        if ($request === null) {
            self::logWarning('Unable to create server request');

            return;
        }
        self::create($request, $cli);
        self::registerShutdownHandler($notifySpanEnded);
    }

    /**
     * A script run in the CLI SAPI that does not emulate a web request (it has no REQUEST_METHOD).
     * A request emulated in the CLI (e.g. by a test harness) is still treated as a web request, as it always was.
     */
    private static function isCliScript(): bool
    {
        return php_sapi_name() === 'cli' && empty($_SERVER['REQUEST_METHOD'] ?? null);
    }

    /**
     * @psalm-suppress ArgumentTypeCoercion
     * @internal
     */
    public static function create(ServerRequestInterface $request, bool $cli = false): void
    {
        $tracer = Globals::tracerProvider()->getTracer(
            'io.opentelemetry.php.auto-root-span',
            null,
            Version::VERSION_1_25_0->url(),
        );
        $parent = Globals::propagator()->extract($request->getHeaders());
        $startTimeInNanos = array_key_exists('REQUEST_TIME_FLOAT', $request->getServerParams())
            ? (int) ((float) $request->getServerParams()['REQUEST_TIME_FLOAT'] * (float) ClockInterface::NANOS_PER_SECOND)
            : (int) (microtime(true) * (float) ClockInterface::NANOS_PER_SECOND);
        $spanBuilder = $tracer->spanBuilder(self::getSpanName($request, $cli))
            ->setSpanKind(SpanKind::KIND_SERVER)
            ->setStartTimestamp($startTimeInNanos)
            ->setParent($parent);
        if ($cli) {
            $span = $spanBuilder->startSpan();
            self::$capturedResponseHeaders = [];
            self::$rootScope = Context::storage()->attach($span->storeInContext($parent));

            return;
        }
        $span = $spanBuilder
            ->setAttribute(TraceAttributes::URL_FULL, (string) $request->getUri())
            ->setAttribute(TraceAttributes::HTTP_REQUEST_METHOD, $request->getMethod())
            ->setAttribute(TraceAttributes::HTTP_REQUEST_BODY_SIZE, $request->getHeaderLine('Content-Length'))
            ->setAttribute(TraceAttributes::USER_AGENT_ORIGINAL, $request->getHeaderLine('User-Agent'))
            ->setAttribute(TraceAttributes::SERVER_ADDRESS, $request->getUri()->getHost())
            ->setAttribute(TraceAttributes::SERVER_PORT, $request->getUri()->getPort())
            ->setAttribute(TraceAttributes::URL_SCHEME, $request->getUri()->getScheme())
            ->setAttribute(TraceAttributes::URL_PATH, $request->getUri()->getPath())
            ->startSpan();
        self::captureRequestHeaders($request, $span);
        self::$capturedResponseHeaders = [];
        self::registerResponseHeaderHookIfNeeded();
        self::$rootScope = Context::storage()->attach($span->storeInContext($parent));
    }

    /**
     * @return non-empty-string
     */
    private static function getSpanName(ServerRequestInterface $request, bool $cli = false): string
    {
        if ($cli) {
            $scriptName = $_SERVER['SCRIPT_NAME'] ?? null;
            $name = is_string($scriptName) ? self::processPathMatchers($scriptName) : '';

            return $name === '' ? '<script>' : $name;
        }

        return $request->getMethod() . ' ' . self::processPathMatchers($request->getUri()->getPath());
    }

    private static function processPathMatchers(string $path): string
    {
        /** @var string[] $groups */
        $groups = Configuration::getList(Variables::OTEL_PHP_TRANSACTION_URL_GROUPS, []);
        if ($groups === []) {
            return $path;
        }

        return (new WildcardListMatcher($groups))->match($path) ?? $path;
    }

    private static function captureRequestHeaders(ServerRequestInterface $request, SpanInterface $span): void
    {
        /** @var string[] $headerNames */
        $headerNames = Configuration::getList(Variables::OTEL_INSTRUMENTATION_HTTP_SERVER_CAPTURE_REQUEST_HEADERS, []);
        foreach ($headerNames as $name) {
            $values = $request->getHeader($name);
            if ($values !== []) {
                $span->setAttribute('http.request.header.' . strtolower($name), $values);
            }
        }
    }

    private static function registerResponseHeaderHookIfNeeded(): void
    {
        if (self::$responseHeaderHookRegistered) {
            return;
        }
        /** @var string[] $headerNames */
        $headerNames = Configuration::getList(Variables::OTEL_INSTRUMENTATION_HTTP_SERVER_CAPTURE_RESPONSE_HEADERS, []);
        if ($headerNames === []) {
            return;
        }
        self::$responseHeaderHookRegistered = true;
        $namesLower = array_map(strtolower(...), $headerNames);
        self::$hookManager ??= SdkAutoloader::getHookManager();
        self::$hookManager->hook(
            null,
            'header',
            static function (object|string|null $objectOrClass, array $params) use ($namesLower): void {
                $header = $params[0] ?? null;
                $replace = $params[1] ?? true;
                if (!is_string($header) || ($colon = strpos($header, ':')) === false) {
                    return;
                }
                $name = strtolower(trim(substr($header, 0, $colon)));
                if (!in_array($name, $namesLower, true)) {
                    return;
                }
                $value = trim(substr($header, $colon + 1));
                if ($replace !== false) {
                    self::$capturedResponseHeaders[$name] = [$value];
                } else {
                    self::$capturedResponseHeaders[$name][] = $value;
                }
            },
        );
    }

    /**
     * @internal
     */
    public static function createRequest(): ?ServerRequestInterface
    {
        try {
            $creator = new ServerRequestCreator(
                Psr17FactoryDiscovery::findServerRequestFactory(),
                Psr17FactoryDiscovery::findUriFactory(),
                Psr17FactoryDiscovery::findUploadedFileFactory(),
                Psr17FactoryDiscovery::findStreamFactory(),
            );

            return $creator->fromGlobals();
        } catch (NotFoundException $e) {
            self::logError('Unable to initialize server request creator for auto root span creation', ['exception' => $e]);
        }

        return null;
    }

    /**
     * @internal
     */
    public static function registerShutdownHandler(?callable $notifySpanEnded = null): void
    {
        ShutdownHandler::register(static function () use ($notifySpanEnded): void {
            if ($notifySpanEnded !== null) {
                $notifySpanEnded();
            }
            self::shutdownHandler();
        });
    }

    /**
     * @internal
     */
    public static function shutdownHandler(): void
    {
        // Use the saved root scope: storage->scope() may return another scope if user code detached scopes out of order
        $scope = self::$rootScope ?? Context::storage()->scope();
        self::$rootScope = null;
        if (!$scope) {
            return;
        }
        $scope->detach();
        $span = Span::fromContext($scope->context());

        $statusCode = http_response_code();
        if (is_int($statusCode)) {
            $span->setAttribute(TraceAttributes::HTTP_RESPONSE_STATUS_CODE, $statusCode);
        }
        foreach (self::$capturedResponseHeaders as $name => $values) {
            $span->setAttribute('http.response.header.' . $name, $values);
        }
        $span->end();
    }
}
