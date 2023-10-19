<?php

// DO NOT EDIT, this is an Auto-generated file from script/semantic-convention/templates/Attributes.php.j2

declare(strict_types=1);

namespace OpenTelemetry\SemConv;

interface TraceAttributes
{
    /**
     * The URL of the OpenTelemetry schema for these keys and values.
     */
    public const SCHEMA_URL = 'https://opentelemetry.io/schemas/1.22.0';

    /**
     * Client address - domain name if available without reverse DNS lookup, otherwise IP address or Unix domain socket name.
     *
     * When observed from the server side, and when communicating through an intermediary, `client.address` SHOULD represent the client address behind any intermediaries (e.g. proxies) if it's available.
     *
     * @example client.example.com
     * @example 10.1.2.80
     * @example /tmp/my.sock
     */
    public const CLIENT_ADDRESS = 'client.address';

    /**
     * Client port number.
     *
     * When observed from the server side, and when communicating through an intermediary, `client.port` SHOULD represent the client port behind any intermediaries (e.g. proxies) if it's available.
     *
     * @example 65123
     */
    public const CLIENT_PORT = 'client.port';

    /**
     * Deprecated, use `server.address`.
     *
     * @deprecated Deprecated, use `server.address`..
     * @example example.com
     */
    public const NET_HOST_NAME = 'net.host.name';

    /**
     * Deprecated, use `server.port`.
     *
     * @deprecated Deprecated, use `server.port`..
     * @example 8080
     */
    public const NET_HOST_PORT = 'net.host.port';

    /**
     * Deprecated, use `server.address` on client spans and `client.address` on server spans.
     *
     * @deprecated Deprecated, use `server.address` on client spans and `client.address` on server spans..
     * @example example.com
     */
    public const NET_PEER_NAME = 'net.peer.name';

    /**
     * Deprecated, use `server.port` on client spans and `client.port` on server spans.
     *
     * @deprecated Deprecated, use `server.port` on client spans and `client.port` on server spans..
     * @example 8080
     */
    public const NET_PEER_PORT = 'net.peer.port';

    /**
     * Deprecated, use `network.protocol.name`.
     *
     * @deprecated Deprecated, use `network.protocol.name`..
     * @example amqp
     * @example http
     * @example mqtt
     */
    public const NET_PROTOCOL_NAME = 'net.protocol.name';

    /**
     * Deprecated, use `network.protocol.version`.
     *
     * @deprecated Deprecated, use `network.protocol.version`..
     * @example 3.1.1
     */
    public const NET_PROTOCOL_VERSION = 'net.protocol.version';

    /**
     * Deprecated, use `network.transport` and `network.type`.
     */
    public const NET_SOCK_FAMILY = 'net.sock.family';

    /**
     * Deprecated, use `network.local.address`.
     *
     * @deprecated Deprecated, use `network.local.address`..
     * @example /var/my.sock
     */
    public const NET_SOCK_HOST_ADDR = 'net.sock.host.addr';

    /**
     * Deprecated, use `network.local.port`.
     *
     * @deprecated Deprecated, use `network.local.port`..
     * @example 8080
     */
    public const NET_SOCK_HOST_PORT = 'net.sock.host.port';

    /**
     * Deprecated, use `network.peer.address`.
     *
     * @deprecated Deprecated, use `network.peer.address`..
     * @example 192.168.0.1
     */
    public const NET_SOCK_PEER_ADDR = 'net.sock.peer.addr';

    /**
     * Deprecated, no replacement at this time.
     *
     * @deprecated Deprecated, no replacement at this time..
     * @example /var/my.sock
     */
    public const NET_SOCK_PEER_NAME = 'net.sock.peer.name';

    /**
     * Deprecated, use `network.peer.port`.
     *
     * @deprecated Deprecated, use `network.peer.port`..
     * @example 65531
     */
    public const NET_SOCK_PEER_PORT = 'net.sock.peer.port';

    /**
     * Deprecated, use `network.transport`.
     */
    public const NET_TRANSPORT = 'net.transport';

    /**
     * Destination address - domain name if available without reverse DNS lookup, otherwise IP address or Unix domain socket name.
     *
     * When observed from the source side, and when communicating through an intermediary, `destination.address` SHOULD represent the destination address behind any intermediaries (e.g. proxies) if it's available.
     *
     * @example destination.example.com
     * @example 10.1.2.80
     * @example /tmp/my.sock
     */
    public const DESTINATION_ADDRESS = 'destination.address';

    /**
     * Destination port number.
     *
     * @example 3389
     * @example 2888
     */
    public const DESTINATION_PORT = 'destination.port';

    /**
     * Describes a class of error the operation ended with.
     *
     * The `error.type` SHOULD be predictable and SHOULD have low cardinality.
     * Instrumentations SHOULD document the list of errors they report.The cardinality of `error.type` within one instrumentation library SHOULD be low, but
     * telemetry consumers that aggregate data from multiple instrumentation libraries and applications
     * should be prepared for `error.type` to have high cardinality at query time, when no
     * additional filters are applied.If the operation has completed successfully, instrumentations SHOULD NOT set `error.type`.If a specific domain defines its own set of error codes (such as HTTP or gRPC status codes),
     * it's RECOMMENDED to use a domain-specific attribute and also set `error.type` to capture
     * all errors, regardless of whether they are defined within the domain-specific set or not.
     *
     * @example timeout
     * @example java.net.UnknownHostException
     * @example server_certificate_invalid
     * @example 500
     */
    public const ERROR_TYPE = 'error.type';

    /**
     * The exception message.
     *
     * @example Division by zero
     * @example Can't convert 'int' object to str implicitly
     */
    public const EXCEPTION_MESSAGE = 'exception.message';

    /**
     * A stacktrace as a string in the natural representation for the language runtime. The representation is to be determined and documented by each language SIG.
     *
     * @example Exception in thread "main" java.lang.RuntimeException: Test exception\n at com.example.GenerateTrace.methodB(GenerateTrace.java:13)\n at com.example.GenerateTrace.methodA(GenerateTrace.java:9)\n at com.example.GenerateTrace.main(GenerateTrace.java:5)
     */
    public const EXCEPTION_STACKTRACE = 'exception.stacktrace';

    /**
     * The type of the exception (its fully-qualified class name, if applicable). The dynamic type of the exception should be preferred over the static type in languages that support it.
     *
     * @example java.net.ConnectException
     * @example OSError
     */
    public const EXCEPTION_TYPE = 'exception.type';

    /**
     * The name of the invoked function.
     *
     * SHOULD be equal to the `faas.name` resource attribute of the invoked function.
     *
     * @example my-function
     */
    public const FAAS_INVOKED_NAME = 'faas.invoked_name';

    /**
     * The cloud provider of the invoked function.
     *
     * SHOULD be equal to the `cloud.provider` resource attribute of the invoked function.
     */
    public const FAAS_INVOKED_PROVIDER = 'faas.invoked_provider';

    /**
     * The cloud region of the invoked function.
     *
     * SHOULD be equal to the `cloud.region` resource attribute of the invoked function.
     *
     * @example eu-central-1
     */
    public const FAAS_INVOKED_REGION = 'faas.invoked_region';

    /**
     * Type of the trigger which caused this function invocation.
     */
    public const FAAS_TRIGGER = 'faas.trigger';

    /**
     * The `service.name` of the remote service. SHOULD be equal to the actual `service.name` resource attribute of the remote service if any.
     *
     * @example AuthTokenCache
     */
    public const PEER_SERVICE = 'peer.service';

    /**
     * Username or client_id extracted from the access token or Authorization header in the inbound request from outside the system.
     *
     * @example username
     */
    public const ENDUSER_ID = 'enduser.id';

    /**
     * Actual/assumed role the client is making the request under extracted from token or application security context.
     *
     * @example admin
     */
    public const ENDUSER_ROLE = 'enduser.role';

    /**
     * Scopes or granted authorities the client currently possesses extracted from token or application security context. The value would come from the scope associated with an OAuth 2.0 Access Token or an attribute value in a SAML 2.0 Assertion.
     *
     * @example read:message, write:files
     */
    public const ENDUSER_SCOPE = 'enduser.scope';

    /**
     * Whether the thread is daemon or not.
     */
    public const THREAD_DAEMON = 'thread.daemon';

    /**
     * Current &quot;managed&quot; thread ID (as opposed to OS thread ID).
     *
     * @example 42
     */
    public const THREAD_ID = 'thread.id';

    /**
     * Current thread name.
     *
     * @example main
     */
    public const THREAD_NAME = 'thread.name';

    /**
     * The column number in `code.filepath` best representing the operation. It SHOULD point within the code unit named in `code.function`.
     *
     * @example 16
     */
    public const CODE_COLUMN = 'code.column';

    /**
     * The source code file name that identifies the code unit as uniquely as possible (preferably an absolute file path).
     *
     * @example /usr/local/MyApplication/content_root/app/index.php
     */
    public const CODE_FILEPATH = 'code.filepath';

    /**
     * The method or function name, or equivalent (usually rightmost part of the code unit's name).
     *
     * @example serveRequest
     */
    public const CODE_FUNCTION = 'code.function';

    /**
     * The line number in `code.filepath` best representing the operation. It SHOULD point within the code unit named in `code.function`.
     *
     * @example 42
     */
    public const CODE_LINENO = 'code.lineno';

    /**
     * The &quot;namespace&quot; within which `code.function` is defined. Usually the qualified class or module name, such that `code.namespace` + some separator + `code.function` form a unique identifier for the code unit.
     *
     * @example com.example.MyHttpService
     */
    public const CODE_NAMESPACE = 'code.namespace';

    /**
     * HTTP request method.
     *
     * HTTP request method value SHOULD be &quot;known&quot; to the instrumentation.
     * By default, this convention defines &quot;known&quot; methods as the ones listed in RFC9110
     * and the PATCH method defined in RFC5789.If the HTTP request method is not known to instrumentation, it MUST set the `http.request.method` attribute to `_OTHER`.If the HTTP instrumentation could end up converting valid HTTP request methods to `_OTHER`, then it MUST provide a way to override
     * the list of known HTTP methods. If this override is done via environment variable, then the environment variable MUST be named
     * OTEL_INSTRUMENTATION_HTTP_KNOWN_METHODS and support a comma-separated list of case-sensitive known HTTP methods
     * (this list MUST be a full override of the default known method, it is not a list of known methods in addition to the defaults).HTTP method names are case-sensitive and `http.request.method` attribute value MUST match a known HTTP method name exactly.
     * Instrumentations for specific web frameworks that consider HTTP methods to be case insensitive, SHOULD populate a canonical equivalent.
     * Tracing instrumentations that do so, MUST also set `http.request.method_original` to the original value.
     *
     * @example GET
     * @example POST
     * @example HEAD
     */
    public const HTTP_REQUEST_METHOD = 'http.request.method';

    /**
     * HTTP response status code.
     *
     * @example 200
     */
    public const HTTP_RESPONSE_STATUS_CODE = 'http.response.status_code';

    /**
     * OSI application layer or non-OSI equivalent.
     *
     * The value SHOULD be normalized to lowercase.
     *
     * @example http
     * @example spdy
     */
    public const NETWORK_PROTOCOL_NAME = 'network.protocol.name';

    /**
     * Version of the protocol specified in `network.protocol.name`.
     *
     * `network.protocol.version` refers to the version of the protocol used and might be different from the protocol client's version. If the HTTP client used has a version of `0.27.2`, but sends HTTP version `1.1`, this attribute should be set to `1.1`.
     *
     * @example 1.0
     * @example 1.1
     * @example 2
     * @example 3
     */
    public const NETWORK_PROTOCOL_VERSION = 'network.protocol.version';

    /**
     * Host identifier of the &quot;URI origin&quot; HTTP request is sent to.
     *
     * Determined by using the first of the following that applies<ul>
     * <li>Host identifier of the request target
     * if it's sent in absolute-form</li>
     * <li>Host identifier of the `Host` header</li>
     * </ul>
     * If an HTTP client request is explicitly made to an IP address, e.g. `http://x.x.x.x:8080`, then
     * `server.address` SHOULD be the IP address `x.x.x.x`. A DNS lookup SHOULD NOT be used.
     *
     * @example example.com
     * @example 10.1.2.80
     * @example /tmp/my.sock
     */
    public const SERVER_ADDRESS = 'server.address';

    /**
     * Port identifier of the &quot;URI origin&quot; HTTP request is sent to.
     *
     * When request target is absolute URI, `server.port` MUST match URI port identifier, otherwise it MUST match `Host` header port identifier.
     *
     * @example 80
     * @example 8080
     * @example 443
     */
    public const SERVER_PORT = 'server.port';

    /**
     * The matched route (path template in the format used by the respective server framework). See note below.
     *
     * MUST NOT be populated when this is not supported by the HTTP server framework as the route attribute should have low-cardinality and the URI path can NOT substitute it.
     * SHOULD include the application root if there is one.
     *
     * @example /users/:userID?
     * @example {controller}/{action}/{id?}
     */
    public const HTTP_ROUTE = 'http.route';

    /**
     * The URI scheme component identifying the used protocol.
     *
     * @example http
     * @example https
     */
    public const URL_SCHEME = 'url.scheme';

    /**
     * The domain identifies the business context for the events.
     *
     * Events across different domains may have same `event.name`, yet be
     * unrelated events.
     */
    public const EVENT_DOMAIN = 'event.domain';

    /**
     * The name identifies the event.
     *
     * @example click
     * @example exception
     */
    public const EVENT_NAME = 'event.name';

    /**
     * A unique identifier for the Log Record.
     *
     * If an id is provided, other log records with the same id will be considered duplicates and can be removed safely. This means, that two distinguishable log records MUST have different values.
     * The id MAY be an Universally Unique Lexicographically Sortable Identifier (ULID), but other identifiers (e.g. UUID) may be used as needed.
     *
     * @example 01ARZ3NDEKTSV4RRFFQ69G5FAV
     */
    public const LOG_RECORD_UID = 'log.record.uid';

    /**
     * The unique identifier of the feature flag.
     *
     * @example logo-color
     */
    public const FEATURE_FLAG_KEY = 'feature_flag.key';

    /**
     * The name of the service provider that performs the flag evaluation.
     *
     * @example Flag Manager
     */
    public const FEATURE_FLAG_PROVIDER_NAME = 'feature_flag.provider_name';

    /**
     * SHOULD be a semantic identifier for a value. If one is unavailable, a stringified version of the value can be used.
     *
     * A semantic identifier, commonly referred to as a variant, provides a means
     * for referring to a value without including the value itself. This can
     * provide additional context for understanding the meaning behind a value.
     * For example, the variant `red` maybe be used for the value `#c05543`.A stringified version of the value can be used in situations where a
     * semantic identifier is unavailable. String representation of the value
     * should be determined by the implementer.
     *
     * @example red
     * @example true
     * @example on
     */
    public const FEATURE_FLAG_VARIANT = 'feature_flag.variant';

    /**
     * The stream associated with the log. See below for a list of well-known values.
     */
    public const LOG_IOSTREAM = 'log.iostream';

    /**
     * The basename of the file.
     *
     * @example audit.log
     */
    public const LOG_FILE_NAME = 'log.file.name';

    /**
     * The basename of the file, with symlinks resolved.
     *
     * @example uuid.log
     */
    public const LOG_FILE_NAME_RESOLVED = 'log.file.name_resolved';

    /**
     * The full path to the file.
     *
     * @example /var/log/mysql/audit.log
     */
    public const LOG_FILE_PATH = 'log.file.path';

    /**
     * The full path to the file, with symlinks resolved.
     *
     * @example /var/lib/docker/uuid.log
     */
    public const LOG_FILE_PATH_RESOLVED = 'log.file.path_resolved';

    /**
     * The name of the connection pool; unique within the instrumented application. In case the connection pool implementation does not provide a name, then the db.connection_string should be used.
     *
     * @example myDataSource
     */
    public const POOL_NAME = 'pool.name';

    /**
     * The state of a connection in the pool.
     *
     * @example idle
     */
    public const STATE = 'state';

    /**
     * Name of the buffer pool.
     *
     * Pool names are generally obtained via BufferPoolMXBean#getName().
     *
     * @example mapped
     * @example direct
     */
    public const JVM_BUFFER_POOL_NAME = 'jvm.buffer.pool.name';

    /**
     * Name of the memory pool.
     *
     * Pool names are generally obtained via MemoryPoolMXBean#getName().
     *
     * @example G1 Old Gen
     * @example G1 Eden space
     * @example G1 Survivor Space
     */
    public const JVM_MEMORY_POOL_NAME = 'jvm.memory.pool.name';

    /**
     * The type of memory.
     *
     * @example heap
     * @example non_heap
     */
    public const JVM_MEMORY_TYPE = 'jvm.memory.type';

    /**
     * OSI transport layer or inter-process communication method.
     *
     * The value SHOULD be normalized to lowercase.Consider always setting the transport when setting a port number, since
     * a port number is ambiguous without knowing the transport, for example
     * different processes could be listening on TCP port 12345 and UDP port 12345.
     *
     * @example tcp
     * @example udp
     */
    public const NETWORK_TRANSPORT = 'network.transport';

    /**
     * OSI network layer or non-OSI equivalent.
     *
     * The value SHOULD be normalized to lowercase.
     *
     * @example ipv4
     * @example ipv6
     */
    public const NETWORK_TYPE = 'network.type';

    /**
     * The name of the (logical) method being called, must be equal to the $method part in the span name.
     *
     * This is the logical name of the method from the RPC interface perspective, which can be different from the name of any implementing method/function. The `code.function` attribute may be used to store the latter (e.g., method actually executing the call on the server side, RPC client stub method on the client side).
     *
     * @example exampleMethod
     */
    public const RPC_METHOD = 'rpc.method';

    /**
     * The full (logical) name of the service being called, including its package name, if applicable.
     *
     * This is the logical name of the service from the RPC interface perspective, which can be different from the name of any implementing class. The `code.namespace` attribute may be used to store the latter (despite the attribute name, it may include a class name; e.g., class with method actually executing the call on the server side, RPC client stub class on the client side).
     *
     * @example myservice.EchoService
     */
    public const RPC_SERVICE = 'rpc.service';

    /**
     * A string identifying the remoting system. See below for a list of well-known identifiers.
     */
    public const RPC_SYSTEM = 'rpc.system';

    /**
     * The device identifier.
     *
     * @example (identifier)
     */
    public const SYSTEM_DEVICE = 'system.device';

    /**
     * The logical CPU number [0..n-1].
     *
     * @example 1
     */
    public const SYSTEM_CPU_LOGICAL_NUMBER = 'system.cpu.logical_number';

    /**
     * The state of the CPU.
     *
     * @example idle
     * @example interrupt
     */
    public const SYSTEM_CPU_STATE = 'system.cpu.state';

    /**
     * The memory state.
     *
     * @example free
     * @example cached
     */
    public const SYSTEM_MEMORY_STATE = 'system.memory.state';

    /**
     * The paging access direction.
     *
     * @example in
     */
    public const SYSTEM_PAGING_DIRECTION = 'system.paging.direction';

    /**
     * The memory paging state.
     *
     * @example free
     */
    public const SYSTEM_PAGING_STATE = 'system.paging.state';

    /**
     * The memory paging type.
     *
     * @example minor
     */
    public const SYSTEM_PAGING_TYPE = 'system.paging.type';

    /**
     * The disk operation direction.
     *
     * @example read
     */
    public const SYSTEM_DISK_DIRECTION = 'system.disk.direction';

    /**
     * The filesystem mode.
     *
     * @example rw, ro
     */
    public const SYSTEM_FILESYSTEM_MODE = 'system.filesystem.mode';

    /**
     * The filesystem mount path.
     *
     * @example /mnt/data
     */
    public const SYSTEM_FILESYSTEM_MOUNTPOINT = 'system.filesystem.mountpoint';

    /**
     * The filesystem state.
     *
     * @example used
     */
    public const SYSTEM_FILESYSTEM_STATE = 'system.filesystem.state';

    /**
     * The filesystem type.
     *
     * @example ext4
     */
    public const SYSTEM_FILESYSTEM_TYPE = 'system.filesystem.type';

    /**
     * .
     *
     * @example transmit
     */
    public const SYSTEM_NETWORK_DIRECTION = 'system.network.direction';

    /**
     * A stateless protocol MUST NOT set this attribute.
     *
     * @example close_wait
     */
    public const SYSTEM_NETWORK_STATE = 'system.network.state';

    /**
     * The process state, e.g., Linux Process State Codes.
     *
     * @example running
     */
    public const SYSTEM_PROCESSES_STATUS = 'system.processes.status';

    /**
     * Local address of the network connection - IP address or Unix domain socket name.
     *
     * @example 10.1.2.80
     * @example /tmp/my.sock
     */
    public const NETWORK_LOCAL_ADDRESS = 'network.local.address';

    /**
     * Local port number of the network connection.
     *
     * @example 65123
     */
    public const NETWORK_LOCAL_PORT = 'network.local.port';

    /**
     * Peer address of the network connection - IP address or Unix domain socket name.
     *
     * @example 10.1.2.80
     * @example /tmp/my.sock
     */
    public const NETWORK_PEER_ADDRESS = 'network.peer.address';

    /**
     * Peer port number of the network connection.
     *
     * @example 65123
     */
    public const NETWORK_PEER_PORT = 'network.peer.port';

    /**
     * The ISO 3166-1 alpha-2 2-character country code associated with the mobile carrier network.
     *
     * @example DE
     */
    public const NETWORK_CARRIER_ICC = 'network.carrier.icc';

    /**
     * The mobile carrier country code.
     *
     * @example 310
     */
    public const NETWORK_CARRIER_MCC = 'network.carrier.mcc';

    /**
     * The mobile carrier network code.
     *
     * @example 001
     */
    public const NETWORK_CARRIER_MNC = 'network.carrier.mnc';

    /**
     * The name of the mobile carrier.
     *
     * @example sprint
     */
    public const NETWORK_CARRIER_NAME = 'network.carrier.name';

    /**
     * This describes more details regarding the connection.type. It may be the type of cell technology connection, but it could be used for describing details about a wifi connection.
     *
     * @example LTE
     */
    public const NETWORK_CONNECTION_SUBTYPE = 'network.connection.subtype';

    /**
     * The internet connection type.
     *
     * @example wifi
     */
    public const NETWORK_CONNECTION_TYPE = 'network.connection.type';

    /**
     * Deprecated, use `http.request.method` instead.
     *
     * @deprecated Deprecated, use `http.request.method` instead..
     * @example GET
     * @example POST
     * @example HEAD
     */
    public const HTTP_METHOD = 'http.method';

    /**
     * Deprecated, use `http.request.body.size` instead.
     *
     * @deprecated Deprecated, use `http.request.body.size` instead..
     * @example 3495
     */
    public const HTTP_REQUEST_CONTENT_LENGTH = 'http.request_content_length';

    /**
     * Deprecated, use `http.response.body.size` instead.
     *
     * @deprecated Deprecated, use `http.response.body.size` instead..
     * @example 3495
     */
    public const HTTP_RESPONSE_CONTENT_LENGTH = 'http.response_content_length';

    /**
     * Deprecated, use `url.scheme` instead.
     *
     * @deprecated Deprecated, use `url.scheme` instead..
     * @example http
     * @example https
     */
    public const HTTP_SCHEME = 'http.scheme';

    /**
     * Deprecated, use `http.response.status_code` instead.
     *
     * @deprecated Deprecated, use `http.response.status_code` instead..
     * @example 200
     */
    public const HTTP_STATUS_CODE = 'http.status_code';

    /**
     * Deprecated, use `url.path` and `url.query` instead.
     *
     * @deprecated Deprecated, use `url.path` and `url.query` instead..
     * @example /search?q=OpenTelemetry#SemConv
     */
    public const HTTP_TARGET = 'http.target';

    /**
     * Deprecated, use `url.full` instead.
     *
     * @deprecated Deprecated, use `url.full` instead..
     * @example https://www.foo.bar/search?q=OpenTelemetry#SemConv
     */
    public const HTTP_URL = 'http.url';

    /**
     * The size of the request payload body in bytes. This is the number of bytes transferred excluding headers and is often, but not always, present as the Content-Length header. For requests using transport encoding, this should be the compressed size.
     *
     * @example 3495
     */
    public const HTTP_REQUEST_BODY_SIZE = 'http.request.body.size';

    /**
     * Original HTTP method sent by the client in the request line.
     *
     * @example GeT
     * @example ACL
     * @example foo
     */
    public const HTTP_REQUEST_METHOD_ORIGINAL = 'http.request.method_original';

    /**
     * The ordinal number of request resending attempt (for any reason, including redirects).
     *
     * The resend count SHOULD be updated each time an HTTP request gets resent by the client, regardless of what was the cause of the resending (e.g. redirection, authorization failure, 503 Server Unavailable, network issues, or any other).
     *
     * @example 3
     */
    public const HTTP_RESEND_COUNT = 'http.resend_count';

    /**
     * The size of the response payload body in bytes. This is the number of bytes transferred excluding headers and is often, but not always, present as the Content-Length header. For requests using transport encoding, this should be the compressed size.
     *
     * @example 3495
     */
    public const HTTP_RESPONSE_BODY_SIZE = 'http.response.body.size';

    /**
     * A unique id to identify a session.
     *
     * @example 00112233-4455-6677-8899-aabbccddeeff
     */
    public const SESSION_ID = 'session.id';

    /**
     * Source address - domain name if available without reverse DNS lookup, otherwise IP address or Unix domain socket name.
     *
     * When observed from the destination side, and when communicating through an intermediary, `source.address` SHOULD represent the source address behind any intermediaries (e.g. proxies) if it's available.
     *
     * @example source.example.com
     * @example 10.1.2.80
     * @example /tmp/my.sock
     */
    public const SOURCE_ADDRESS = 'source.address';

    /**
     * Source port number.
     *
     * @example 3389
     * @example 2888
     */
    public const SOURCE_PORT = 'source.port';

    /**
     * The full invoked ARN as provided on the `Context` passed to the function (`Lambda-Runtime-Invoked-Function-Arn` header on the `/runtime/invocation/next` applicable).
     *
     * This may be different from `cloud.resource_id` if an alias is involved.
     *
     * @example arn:aws:lambda:us-east-1:123456:function:myfunction:myalias
     */
    public const AWS_LAMBDA_INVOKED_ARN = 'aws.lambda.invoked_arn';

    /**
     * The event_id uniquely identifies the event.
     *
     * @example 123e4567-e89b-12d3-a456-426614174000
     * @example 0001
     */
    public const CLOUDEVENTS_EVENT_ID = 'cloudevents.event_id';

    /**
     * The source identifies the context in which an event happened.
     *
     * @example https://github.com/cloudevents
     * @example /cloudevents/spec/pull/123
     * @example my-service
     */
    public const CLOUDEVENTS_EVENT_SOURCE = 'cloudevents.event_source';

    /**
     * The version of the CloudEvents specification which the event uses.
     *
     * @example 1.0
     */
    public const CLOUDEVENTS_EVENT_SPEC_VERSION = 'cloudevents.event_spec_version';

    /**
     * The subject of the event in the context of the event producer (identified by source).
     *
     * @example mynewfile.jpg
     */
    public const CLOUDEVENTS_EVENT_SUBJECT = 'cloudevents.event_subject';

    /**
     * The event_type contains a value describing the type of event related to the originating occurrence.
     *
     * @example com.github.pull_request.opened
     * @example com.example.object.deleted.v2
     */
    public const CLOUDEVENTS_EVENT_TYPE = 'cloudevents.event_type';

    /**
     * Parent-child Reference type.
     *
     * The causal relationship between a child Span and a parent Span.
     */
    public const OPENTRACING_REF_TYPE = 'opentracing.ref_type';

    /**
     * The connection string used to connect to the database. It is recommended to remove embedded credentials.
     *
     * @example Server=(localdb)\v11.0;Integrated Security=true;
     */
    public const DB_CONNECTION_STRING = 'db.connection_string';

    /**
     * The fully-qualified class name of the Java Database Connectivity (JDBC) driver used to connect.
     *
     * @example org.postgresql.Driver
     * @example com.microsoft.sqlserver.jdbc.SQLServerDriver
     */
    public const DB_JDBC_DRIVER_CLASSNAME = 'db.jdbc.driver_classname';

    /**
     * This attribute is used to report the name of the database being accessed. For commands that switch the database, this should be set to the target database (even if the command fails).
     *
     * In some SQL databases, the database name to be used is called &quot;schema name&quot;. In case there are multiple layers that could be considered for database name (e.g. Oracle instance name and schema name), the database name to be used is the more specific layer (e.g. Oracle schema name).
     *
     * @example customers
     * @example main
     */
    public const DB_NAME = 'db.name';

    /**
     * The name of the operation being executed, e.g. the MongoDB command name such as `findAndModify`, or the SQL keyword.
     *
     * When setting this to an SQL keyword, it is not recommended to attempt any client-side parsing of `db.statement` just to get this property, but it should be set if the operation name is provided by the library being instrumented. If the SQL statement has an ambiguous operation, or performs more than one operation, this value may be omitted.
     *
     * @example findAndModify
     * @example HMSET
     * @example SELECT
     */
    public const DB_OPERATION = 'db.operation';

    /**
     * The database statement being executed.
     *
     * @example SELECT * FROM wuser_table
     * @example SET mykey "WuValue"
     */
    public const DB_STATEMENT = 'db.statement';

    /**
     * An identifier for the database management system (DBMS) product being used. See below for a list of well-known identifiers.
     */
    public const DB_SYSTEM = 'db.system';

    /**
     * Username for accessing the database.
     *
     * @example readonly_user
     * @example reporting_user
     */
    public const DB_USER = 'db.user';

    /**
     * The Microsoft SQL Server instance name connecting to. This name is used to determine the port of a named instance.
     *
     * If setting a `db.mssql.instance_name`, `server.port` is no longer required (but still recommended if non-standard).
     *
     * @example MSSQLSERVER
     */
    public const DB_MSSQL_INSTANCE_NAME = 'db.mssql.instance_name';

    /**
     * The consistency level of the query. Based on consistency values from CQL.
     */
    public const DB_CASSANDRA_CONSISTENCY_LEVEL = 'db.cassandra.consistency_level';

    /**
     * The data center of the coordinating node for a query.
     *
     * @example us-west-2
     */
    public const DB_CASSANDRA_COORDINATOR_DC = 'db.cassandra.coordinator.dc';

    /**
     * The ID of the coordinating node for a query.
     *
     * @example be13faa2-8574-4d71-926d-27f16cf8a7af
     */
    public const DB_CASSANDRA_COORDINATOR_ID = 'db.cassandra.coordinator.id';

    /**
     * Whether or not the query is idempotent.
     */
    public const DB_CASSANDRA_IDEMPOTENCE = 'db.cassandra.idempotence';

    /**
     * The fetch size used for paging, i.e. how many rows will be returned at once.
     *
     * @example 5000
     */
    public const DB_CASSANDRA_PAGE_SIZE = 'db.cassandra.page_size';

    /**
     * The number of times a query was speculatively executed. Not set or `0` if the query was not executed speculatively.
     *
     * @example 2
     */
    public const DB_CASSANDRA_SPECULATIVE_EXECUTION_COUNT = 'db.cassandra.speculative_execution_count';

    /**
     * The name of the primary table that the operation is acting upon, including the keyspace name (if applicable).
     *
     * This mirrors the db.sql.table attribute but references cassandra rather than sql. It is not recommended to attempt any client-side parsing of `db.statement` just to get this property, but it should be set if it is provided by the library being instrumented. If the operation is acting upon an anonymous table, or more than one table, this value MUST NOT be set.
     *
     * @example mytable
     */
    public const DB_CASSANDRA_TABLE = 'db.cassandra.table';

    /**
     * The index of the database being accessed as used in the `SELECT` command, provided as an integer. To be used instead of the generic `db.name` attribute.
     *
     * @example 1
     * @example 15
     */
    public const DB_REDIS_DATABASE_INDEX = 'db.redis.database_index';

    /**
     * The collection being accessed within the database stated in `db.name`.
     *
     * @example customers
     * @example products
     */
    public const DB_MONGODB_COLLECTION = 'db.mongodb.collection';

    /**
     * Represents the identifier of an Elasticsearch cluster.
     *
     * @example e9106fc68e3044f0b1475b04bf4ffd5f
     */
    public const DB_ELASTICSEARCH_CLUSTER_NAME = 'db.elasticsearch.cluster.name';

    /**
     * Represents the human-readable identifier of the node/instance to which a request was routed.
     *
     * @example instance-0000000001
     */
    public const DB_ELASTICSEARCH_NODE_NAME = 'db.elasticsearch.node.name';

    /**
     * Absolute URL describing a network resource according to RFC3986.
     *
     * For network calls, URL usually has `scheme://host[:port][path][?query][#fragment]` format, where the fragment is not transmitted over HTTP, but if it is known, it should be included nevertheless.
     * `url.full` MUST NOT contain credentials passed via URL in form of `https://username:password@www.example.com/`. In such case username and password should be redacted and attribute's value should be `https://REDACTED:REDACTED@www.example.com/`.
     * `url.full` SHOULD capture the absolute URL when it is available (or can be reconstructed) and SHOULD NOT be validated or modified except for sanitizing purposes.
     *
     * @example https://localhost:9200/index/_search?q=user.id:kimchy
     */
    public const URL_FULL = 'url.full';

    /**
     * The name of the primary table that the operation is acting upon, including the database name (if applicable).
     *
     * It is not recommended to attempt any client-side parsing of `db.statement` just to get this property, but it should be set if it is provided by the library being instrumented. If the operation is acting upon an anonymous table, or more than one table, this value MUST NOT be set.
     *
     * @example public.users
     * @example customers
     */
    public const DB_SQL_TABLE = 'db.sql.table';

    /**
     * Unique Cosmos client instance id.
     *
     * @example 3ba4827d-4422-483f-b59f-85b74211c11d
     */
    public const DB_COSMOSDB_CLIENT_ID = 'db.cosmosdb.client_id';

    /**
     * Cosmos client connection mode.
     */
    public const DB_COSMOSDB_CONNECTION_MODE = 'db.cosmosdb.connection_mode';

    /**
     * Cosmos DB container name.
     *
     * @example anystring
     */
    public const DB_COSMOSDB_CONTAINER = 'db.cosmosdb.container';

    /**
     * CosmosDB Operation Type.
     */
    public const DB_COSMOSDB_OPERATION_TYPE = 'db.cosmosdb.operation_type';

    /**
     * RU consumed for that operation.
     *
     * @example 46.18
     * @example 1.0
     */
    public const DB_COSMOSDB_REQUEST_CHARGE = 'db.cosmosdb.request_charge';

    /**
     * Request payload size in bytes.
     */
    public const DB_COSMOSDB_REQUEST_CONTENT_LENGTH = 'db.cosmosdb.request_content_length';

    /**
     * Cosmos DB status code.
     *
     * @example 200
     * @example 201
     */
    public const DB_COSMOSDB_STATUS_CODE = 'db.cosmosdb.status_code';

    /**
     * Cosmos DB sub status code.
     *
     * @example 1000
     * @example 1002
     */
    public const DB_COSMOSDB_SUB_STATUS_CODE = 'db.cosmosdb.sub_status_code';

    /**
     * Full user-agent string is generated by Cosmos DB SDK.
     *
     * The user-agent value is generated by SDK which is a combination of&lt;br&gt; `sdk_version` : Current version of SDK. e.g. 'cosmos-netstandard-sdk/3.23.0'&lt;br&gt; `direct_pkg_version` : Direct package version used by Cosmos DB SDK. e.g. '3.23.1'&lt;br&gt; `number_of_client_instances` : Number of cosmos client instances created by the application. e.g. '1'&lt;br&gt; `type_of_machine_architecture` : Machine architecture. e.g. 'X64'&lt;br&gt; `operating_system` : Operating System. e.g. 'Linux 5.4.0-1098-azure 104 18'&lt;br&gt; `runtime_framework` : Runtime Framework. e.g. '.NET Core 3.1.32'&lt;br&gt; `failover_information` : Generated key to determine if region failover enabled.
     *    Format Reg-{D (Disabled discovery)}-S(application region)|L(List of preferred regions)|N(None, user did not configure it).
     *    Default value is &quot;NS&quot;.
     *
     * @example cosmos-netstandard-sdk/3.23.0\|3.23.1\|1\|X64\|Linux 5.4.0-1098-azure 104 18\|.NET Core 3.1.32\|S\|
     */
    public const USER_AGENT_ORIGINAL = 'user_agent.original';

    /**
     * Name of the code, either &quot;OK&quot; or &quot;ERROR&quot;. MUST NOT be set if the status code is UNSET.
     */
    public const OTEL_STATUS_CODE = 'otel.status_code';

    /**
     * Description of the Status if it has a value, otherwise not set.
     *
     * @example resource not found
     */
    public const OTEL_STATUS_DESCRIPTION = 'otel.status_description';

    /**
     * Cloud provider-specific native identifier of the monitored cloud resource (e.g. an ARN on AWS, a fully qualified resource ID on Azure, a full resource name on GCP).
     *
     * On some cloud providers, it may not be possible to determine the full ID at startup,
     * so it may be necessary to set `cloud.resource_id` as a span attribute instead.The exact value to use for `cloud.resource_id` depends on the cloud provider.
     * The following well-known definitions MUST be used if you set this attribute and they apply:<ul>
     * <li><strong>AWS Lambda:</strong> The function ARN.
     * Take care not to use the &quot;invoked ARN&quot; directly but replace any
     * alias suffix
     * with the resolved function version, as the same runtime instance may be invokable with
     * multiple different aliases.</li>
     * <li><strong>GCP:</strong> The URI of the resource</li>
     * <li><strong>Azure:</strong> The Fully Qualified Resource ID of the invoked function,
     * <em>not</em> the function app, having the form
     * `/subscriptions/<SUBSCIPTION_GUID>/resourceGroups/<RG>/providers/Microsoft.Web/sites/<FUNCAPP>/functions/<FUNC>`.
     * This means that a span attribute MUST be used, as an Azure function app can host multiple functions that would usually share
     * a TracerProvider.</li>
     * </ul>
     *
     * @example arn:aws:lambda:REGION:ACCOUNT_ID:function:my-function
     * @example //run.googleapis.com/projects/PROJECT_ID/locations/LOCATION_ID/services/SERVICE_ID
     * @example /subscriptions/<SUBSCIPTION_GUID>/resourceGroups/<RG>/providers/Microsoft.Web/sites/<FUNCAPP>/functions/<FUNC>
     */
    public const CLOUD_RESOURCE_ID = 'cloud.resource_id';

    /**
     * The invocation ID of the current function invocation.
     *
     * @example af9d5aa4-a685-4c5f-a22b-444f80b3cc28
     */
    public const FAAS_INVOCATION_ID = 'faas.invocation_id';

    /**
     * The name of the source on which the triggering operation was performed. For example, in Cloud Storage or S3 corresponds to the bucket name, and in Cosmos DB to the database name.
     *
     * @example myBucketName
     * @example myDbName
     */
    public const FAAS_DOCUMENT_COLLECTION = 'faas.document.collection';

    /**
     * The document name/table subjected to the operation. For example, in Cloud Storage or S3 is the name of the file, and in Cosmos DB the table name.
     *
     * @example myFile.txt
     * @example myTableName
     */
    public const FAAS_DOCUMENT_NAME = 'faas.document.name';

    /**
     * Describes the type of the operation that was performed on the data.
     */
    public const FAAS_DOCUMENT_OPERATION = 'faas.document.operation';

    /**
     * A string containing the time when the data was accessed in the ISO 8601 format expressed in UTC.
     *
     * @example 2020-01-23T13:47:06Z
     */
    public const FAAS_DOCUMENT_TIME = 'faas.document.time';

    /**
     * The URI path component.
     *
     * When missing, the value is assumed to be `/`
     *
     * @example /search
     */
    public const URL_PATH = 'url.path';

    /**
     * The URI query component.
     *
     * Sensitive content provided in query string SHOULD be scrubbed when instrumentations can identify it.
     *
     * @example q=OpenTelemetry
     */
    public const URL_QUERY = 'url.query';

    /**
     * The number of messages sent, received, or processed in the scope of the batching operation.
     *
     * Instrumentations SHOULD NOT set `messaging.batch.message_count` on spans that operate with a single message. When a messaging client library supports both batch and single-message API for the same operation, instrumentations SHOULD use `messaging.batch.message_count` for batching APIs and SHOULD NOT use it for single-message APIs.
     *
     * @example 1
     * @example 2
     */
    public const MESSAGING_BATCH_MESSAGE_COUNT = 'messaging.batch.message_count';

    /**
     * A unique identifier for the client that consumes or produces a message.
     *
     * @example client-5
     * @example myhost@8742@s8083jm
     */
    public const MESSAGING_CLIENT_ID = 'messaging.client_id';

    /**
     * A boolean that is true if the message destination is anonymous (could be unnamed or have auto-generated name).
     */
    public const MESSAGING_DESTINATION_ANONYMOUS = 'messaging.destination.anonymous';

    /**
     * The message destination name.
     *
     * Destination name SHOULD uniquely identify a specific queue, topic or other entity within the broker. If
     * the broker does not have such notion, the destination name SHOULD uniquely identify the broker.
     *
     * @example MyQueue
     * @example MyTopic
     */
    public const MESSAGING_DESTINATION_NAME = 'messaging.destination.name';

    /**
     * Low cardinality representation of the messaging destination name.
     *
     * Destination names could be constructed from templates. An example would be a destination name involving a user name or product id. Although the destination name in this case is of high cardinality, the underlying template is of low cardinality and can be effectively used for grouping and aggregation.
     *
     * @example /customers/{customerId}
     */
    public const MESSAGING_DESTINATION_TEMPLATE = 'messaging.destination.template';

    /**
     * A boolean that is true if the message destination is temporary and might not exist anymore after messages are processed.
     */
    public const MESSAGING_DESTINATION_TEMPORARY = 'messaging.destination.temporary';

    /**
     * The size of the message body in bytes.
     *
     * This can refer to both the compressed or uncompressed body size. If both sizes are known, the uncompressed
     * body size should be used.
     *
     * @example 1439
     */
    public const MESSAGING_MESSAGE_BODY_SIZE = 'messaging.message.body.size';

    /**
     * The conversation ID identifying the conversation to which the message belongs, represented as a string. Sometimes called &quot;Correlation ID&quot;.
     *
     * @example MyConversationId
     */
    public const MESSAGING_MESSAGE_CONVERSATION_ID = 'messaging.message.conversation_id';

    /**
     * The size of the message body and metadata in bytes.
     *
     * This can refer to both the compressed or uncompressed size. If both sizes are known, the uncompressed
     * size should be used.
     *
     * @example 2738
     */
    public const MESSAGING_MESSAGE_ENVELOPE_SIZE = 'messaging.message.envelope.size';

    /**
     * A value used by the messaging system as an identifier for the message, represented as a string.
     *
     * @example 452a7c7c7c7048c2f887f61572b18fc2
     */
    public const MESSAGING_MESSAGE_ID = 'messaging.message.id';

    /**
     * A string identifying the kind of messaging operation as defined in the Operation names section above.
     *
     * If a custom value is used, it MUST be of low cardinality.
     */
    public const MESSAGING_OPERATION = 'messaging.operation';

    /**
     * A string identifying the messaging system.
     *
     * @example kafka
     * @example rabbitmq
     * @example rocketmq
     * @example activemq
     * @example AmazonSQS
     */
    public const MESSAGING_SYSTEM = 'messaging.system';

    /**
     * A string containing the schedule period as Cron Expression.
     *
     * @example 0/5 * * * ? *
     */
    public const FAAS_CRON = 'faas.cron';

    /**
     * A string containing the function invocation time in the ISO 8601 format expressed in UTC.
     *
     * @example 2020-01-23T13:47:06Z
     */
    public const FAAS_TIME = 'faas.time';

    /**
     * A boolean that is true if the serverless function is executed for the first time (aka cold-start).
     */
    public const FAAS_COLDSTART = 'faas.coldstart';

    /**
     * The AWS request ID as returned in the response headers `x-amz-request-id` or `x-amz-requestid`.
     *
     * @example 79b9da39-b7ae-508a-a6bc-864b2829c622
     * @example C9ER4AJX75574TDJ
     */
    public const AWS_REQUEST_ID = 'aws.request_id';

    /**
     * The value of the `AttributesToGet` request parameter.
     *
     * @example lives
     * @example id
     */
    public const AWS_DYNAMODB_ATTRIBUTES_TO_GET = 'aws.dynamodb.attributes_to_get';

    /**
     * The value of the `ConsistentRead` request parameter.
     */
    public const AWS_DYNAMODB_CONSISTENT_READ = 'aws.dynamodb.consistent_read';

    /**
     * The JSON-serialized value of each item in the `ConsumedCapacity` response field.
     *
     * @example { "CapacityUnits": number, "GlobalSecondaryIndexes": { "string" : { "CapacityUnits": number, "ReadCapacityUnits": number, "WriteCapacityUnits": number } }, "LocalSecondaryIndexes": { "string" : { "CapacityUnits": number, "ReadCapacityUnits": number, "WriteCapacityUnits": number } }, "ReadCapacityUnits": number, "Table": { "CapacityUnits": number, "ReadCapacityUnits": number, "WriteCapacityUnits": number }, "TableName": "string", "WriteCapacityUnits": number }
     */
    public const AWS_DYNAMODB_CONSUMED_CAPACITY = 'aws.dynamodb.consumed_capacity';

    /**
     * The value of the `IndexName` request parameter.
     *
     * @example name_to_group
     */
    public const AWS_DYNAMODB_INDEX_NAME = 'aws.dynamodb.index_name';

    /**
     * The JSON-serialized value of the `ItemCollectionMetrics` response field.
     *
     * @example { "string" : [ { "ItemCollectionKey": { "string" : { "B": blob, "BOOL": boolean, "BS": [ blob ], "L": [ "AttributeValue" ], "M": { "string" : "AttributeValue" }, "N": "string", "NS": [ "string" ], "NULL": boolean, "S": "string", "SS": [ "string" ] } }, "SizeEstimateRangeGB": [ number ] } ] }
     */
    public const AWS_DYNAMODB_ITEM_COLLECTION_METRICS = 'aws.dynamodb.item_collection_metrics';

    /**
     * The value of the `Limit` request parameter.
     *
     * @example 10
     */
    public const AWS_DYNAMODB_LIMIT = 'aws.dynamodb.limit';

    /**
     * The value of the `ProjectionExpression` request parameter.
     *
     * @example Title
     * @example Title, Price, Color
     * @example Title, Description, RelatedItems, ProductReviews
     */
    public const AWS_DYNAMODB_PROJECTION = 'aws.dynamodb.projection';

    /**
     * The value of the `ProvisionedThroughput.ReadCapacityUnits` request parameter.
     *
     * @example 1.0
     * @example 2.0
     */
    public const AWS_DYNAMODB_PROVISIONED_READ_CAPACITY = 'aws.dynamodb.provisioned_read_capacity';

    /**
     * The value of the `ProvisionedThroughput.WriteCapacityUnits` request parameter.
     *
     * @example 1.0
     * @example 2.0
     */
    public const AWS_DYNAMODB_PROVISIONED_WRITE_CAPACITY = 'aws.dynamodb.provisioned_write_capacity';

    /**
     * The value of the `Select` request parameter.
     *
     * @example ALL_ATTRIBUTES
     * @example COUNT
     */
    public const AWS_DYNAMODB_SELECT = 'aws.dynamodb.select';

    /**
     * The keys in the `RequestItems` object field.
     *
     * @example Users
     * @example Cats
     */
    public const AWS_DYNAMODB_TABLE_NAMES = 'aws.dynamodb.table_names';

    /**
     * The JSON-serialized value of each item of the `GlobalSecondaryIndexes` request field.
     *
     * @example { "IndexName": "string", "KeySchema": [ { "AttributeName": "string", "KeyType": "string" } ], "Projection": { "NonKeyAttributes": [ "string" ], "ProjectionType": "string" }, "ProvisionedThroughput": { "ReadCapacityUnits": number, "WriteCapacityUnits": number } }
     */
    public const AWS_DYNAMODB_GLOBAL_SECONDARY_INDEXES = 'aws.dynamodb.global_secondary_indexes';

    /**
     * The JSON-serialized value of each item of the `LocalSecondaryIndexes` request field.
     *
     * @example { "IndexArn": "string", "IndexName": "string", "IndexSizeBytes": number, "ItemCount": number, "KeySchema": [ { "AttributeName": "string", "KeyType": "string" } ], "Projection": { "NonKeyAttributes": [ "string" ], "ProjectionType": "string" } }
     */
    public const AWS_DYNAMODB_LOCAL_SECONDARY_INDEXES = 'aws.dynamodb.local_secondary_indexes';

    /**
     * The value of the `ExclusiveStartTableName` request parameter.
     *
     * @example Users
     * @example CatsTable
     */
    public const AWS_DYNAMODB_EXCLUSIVE_START_TABLE = 'aws.dynamodb.exclusive_start_table';

    /**
     * The the number of items in the `TableNames` response parameter.
     *
     * @example 20
     */
    public const AWS_DYNAMODB_TABLE_COUNT = 'aws.dynamodb.table_count';

    /**
     * The value of the `ScanIndexForward` request parameter.
     */
    public const AWS_DYNAMODB_SCAN_FORWARD = 'aws.dynamodb.scan_forward';

    /**
     * The value of the `Count` response parameter.
     *
     * @example 10
     */
    public const AWS_DYNAMODB_COUNT = 'aws.dynamodb.count';

    /**
     * The value of the `ScannedCount` response parameter.
     *
     * @example 50
     */
    public const AWS_DYNAMODB_SCANNED_COUNT = 'aws.dynamodb.scanned_count';

    /**
     * The value of the `Segment` request parameter.
     *
     * @example 10
     */
    public const AWS_DYNAMODB_SEGMENT = 'aws.dynamodb.segment';

    /**
     * The value of the `TotalSegments` request parameter.
     *
     * @example 100
     */
    public const AWS_DYNAMODB_TOTAL_SEGMENTS = 'aws.dynamodb.total_segments';

    /**
     * The JSON-serialized value of each item in the `AttributeDefinitions` request field.
     *
     * @example { "AttributeName": "string", "AttributeType": "string" }
     */
    public const AWS_DYNAMODB_ATTRIBUTE_DEFINITIONS = 'aws.dynamodb.attribute_definitions';

    /**
     * The JSON-serialized value of each item in the the `GlobalSecondaryIndexUpdates` request field.
     *
     * @example { "Create": { "IndexName": "string", "KeySchema": [ { "AttributeName": "string", "KeyType": "string" } ], "Projection": { "NonKeyAttributes": [ "string" ], "ProjectionType": "string" }, "ProvisionedThroughput": { "ReadCapacityUnits": number, "WriteCapacityUnits": number } }
     */
    public const AWS_DYNAMODB_GLOBAL_SECONDARY_INDEX_UPDATES = 'aws.dynamodb.global_secondary_index_updates';

    /**
     * The S3 bucket name the request refers to. Corresponds to the `--bucket` parameter of the S3 API operations.
     *
     * The `bucket` attribute is applicable to all S3 operations that reference a bucket, i.e. that require the bucket name as a mandatory parameter.
     * This applies to almost all S3 operations except `list-buckets`.
     *
     * @example some-bucket-name
     */
    public const AWS_S3_BUCKET = 'aws.s3.bucket';

    /**
     * The source object (in the form `bucket`/`key`) for the copy operation.
     *
     * The `copy_source` attribute applies to S3 copy operations and corresponds to the `--copy-source` parameter
     * of the copy-object operation within the S3 API.
     * This applies in particular to the following operations:<ul>
     * <li>copy-object</li>
     * <li>upload-part-copy</li>
     * </ul>
     *
     * @example someFile.yml
     */
    public const AWS_S3_COPY_SOURCE = 'aws.s3.copy_source';

    /**
     * The delete request container that specifies the objects to be deleted.
     *
     * The `delete` attribute is only applicable to the delete-object operation.
     * The `delete` attribute corresponds to the `--delete` parameter of the
     * delete-objects operation within the S3 API.
     *
     * @example Objects=[{Key=string,VersionId=string},{Key=string,VersionId=string}],Quiet=boolean
     */
    public const AWS_S3_DELETE = 'aws.s3.delete';

    /**
     * The S3 object key the request refers to. Corresponds to the `--key` parameter of the S3 API operations.
     *
     * The `key` attribute is applicable to all object-related S3 operations, i.e. that require the object key as a mandatory parameter.
     * This applies in particular to the following operations:<ul>
     * <li>copy-object</li>
     * <li>delete-object</li>
     * <li>get-object</li>
     * <li>head-object</li>
     * <li>put-object</li>
     * <li>restore-object</li>
     * <li>select-object-content</li>
     * <li>abort-multipart-upload</li>
     * <li>complete-multipart-upload</li>
     * <li>create-multipart-upload</li>
     * <li>list-parts</li>
     * <li>upload-part</li>
     * <li>upload-part-copy</li>
     * </ul>
     *
     * @example someFile.yml
     */
    public const AWS_S3_KEY = 'aws.s3.key';

    /**
     * The part number of the part being uploaded in a multipart-upload operation. This is a positive integer between 1 and 10,000.
     *
     * The `part_number` attribute is only applicable to the upload-part
     * and upload-part-copy operations.
     * The `part_number` attribute corresponds to the `--part-number` parameter of the
     * upload-part operation within the S3 API.
     *
     * @example 3456
     */
    public const AWS_S3_PART_NUMBER = 'aws.s3.part_number';

    /**
     * Upload ID that identifies the multipart upload.
     *
     * The `upload_id` attribute applies to S3 multipart-upload operations and corresponds to the `--upload-id` parameter
     * of the S3 API multipart operations.
     * This applies in particular to the following operations:<ul>
     * <li>abort-multipart-upload</li>
     * <li>complete-multipart-upload</li>
     * <li>list-parts</li>
     * <li>upload-part</li>
     * <li>upload-part-copy</li>
     * </ul>
     *
     * @example dfRtDYWFbkRONycy.Yxwh66Yjlx.cph0gtNBtJ
     */
    public const AWS_S3_UPLOAD_ID = 'aws.s3.upload_id';

    /**
     * The GraphQL document being executed.
     *
     * The value may be sanitized to exclude sensitive information.
     *
     * @example query findBookById { bookById(id: ?) { name } }
     */
    public const GRAPHQL_DOCUMENT = 'graphql.document';

    /**
     * The name of the operation being executed.
     *
     * @example findBookById
     */
    public const GRAPHQL_OPERATION_NAME = 'graphql.operation.name';

    /**
     * The type of the operation being executed.
     *
     * @example query
     * @example mutation
     * @example subscription
     */
    public const GRAPHQL_OPERATION_TYPE = 'graphql.operation.type';

    /**
     * A boolean that is true if the publish message destination is anonymous (could be unnamed or have auto-generated name).
     */
    public const MESSAGING_DESTINATION_PUBLISH_ANONYMOUS = 'messaging.destination_publish.anonymous';

    /**
     * The name of the original destination the message was published to.
     *
     * The name SHOULD uniquely identify a specific queue, topic, or other entity within the broker. If
     * the broker does not have such notion, the original destination name SHOULD uniquely identify the broker.
     *
     * @example MyQueue
     * @example MyTopic
     */
    public const MESSAGING_DESTINATION_PUBLISH_NAME = 'messaging.destination_publish.name';

    /**
     * RabbitMQ message routing key.
     *
     * @example myKey
     */
    public const MESSAGING_RABBITMQ_DESTINATION_ROUTING_KEY = 'messaging.rabbitmq.destination.routing_key';

    /**
     * Name of the Kafka Consumer Group that is handling the message. Only applies to consumers, not producers.
     *
     * @example my-group
     */
    public const MESSAGING_KAFKA_CONSUMER_GROUP = 'messaging.kafka.consumer.group';

    /**
     * Partition the message is sent to.
     *
     * @example 2
     */
    public const MESSAGING_KAFKA_DESTINATION_PARTITION = 'messaging.kafka.destination.partition';

    /**
     * Message keys in Kafka are used for grouping alike messages to ensure they're processed on the same partition. They differ from `messaging.message.id` in that they're not unique. If the key is `null`, the attribute MUST NOT be set.
     *
     * If the key type is not string, it's string representation has to be supplied for the attribute. If the key has no unambiguous, canonical string form, don't include its value.
     *
     * @example myKey
     */
    public const MESSAGING_KAFKA_MESSAGE_KEY = 'messaging.kafka.message.key';

    /**
     * The offset of a record in the corresponding Kafka partition.
     *
     * @example 42
     */
    public const MESSAGING_KAFKA_MESSAGE_OFFSET = 'messaging.kafka.message.offset';

    /**
     * A boolean that is true if the message is a tombstone.
     */
    public const MESSAGING_KAFKA_MESSAGE_TOMBSTONE = 'messaging.kafka.message.tombstone';

    /**
     * Name of the RocketMQ producer/consumer group that is handling the message. The client type is identified by the SpanKind.
     *
     * @example myConsumerGroup
     */
    public const MESSAGING_ROCKETMQ_CLIENT_GROUP = 'messaging.rocketmq.client_group';

    /**
     * Model of message consumption. This only applies to consumer spans.
     */
    public const MESSAGING_ROCKETMQ_CONSUMPTION_MODEL = 'messaging.rocketmq.consumption_model';

    /**
     * The delay time level for delay message, which determines the message delay time.
     *
     * @example 3
     */
    public const MESSAGING_ROCKETMQ_MESSAGE_DELAY_TIME_LEVEL = 'messaging.rocketmq.message.delay_time_level';

    /**
     * The timestamp in milliseconds that the delay message is expected to be delivered to consumer.
     *
     * @example 1665987217045
     */
    public const MESSAGING_ROCKETMQ_MESSAGE_DELIVERY_TIMESTAMP = 'messaging.rocketmq.message.delivery_timestamp';

    /**
     * It is essential for FIFO message. Messages that belong to the same message group are always processed one by one within the same consumer group.
     *
     * @example myMessageGroup
     */
    public const MESSAGING_ROCKETMQ_MESSAGE_GROUP = 'messaging.rocketmq.message.group';

    /**
     * Key(s) of message, another way to mark message besides message id.
     *
     * @example keyA
     * @example keyB
     */
    public const MESSAGING_ROCKETMQ_MESSAGE_KEYS = 'messaging.rocketmq.message.keys';

    /**
     * The secondary classifier of message besides topic.
     *
     * @example tagA
     */
    public const MESSAGING_ROCKETMQ_MESSAGE_TAG = 'messaging.rocketmq.message.tag';

    /**
     * Type of message.
     */
    public const MESSAGING_ROCKETMQ_MESSAGE_TYPE = 'messaging.rocketmq.message.type';

    /**
     * Namespace of RocketMQ resources, resources in different namespaces are individual.
     *
     * @example myNamespace
     */
    public const MESSAGING_ROCKETMQ_NAMESPACE = 'messaging.rocketmq.namespace';

    /**
     * The numeric status code of the gRPC request.
     */
    public const RPC_GRPC_STATUS_CODE = 'rpc.grpc.status_code';

    /**
     * `error.code` property of response if it is an error response.
     *
     * @example -32700
     * @example 100
     */
    public const RPC_JSONRPC_ERROR_CODE = 'rpc.jsonrpc.error_code';

    /**
     * `error.message` property of response if it is an error response.
     *
     * @example Parse error
     * @example User already exists
     */
    public const RPC_JSONRPC_ERROR_MESSAGE = 'rpc.jsonrpc.error_message';

    /**
     * `id` property of request or response. Since protocol allows id to be int, string, `null` or missing (for notifications), value is expected to be cast to string for simplicity. Use empty string in case of `null` value. Omit entirely if this is a notification.
     *
     * @example 10
     * @example request-7
     */
    public const RPC_JSONRPC_REQUEST_ID = 'rpc.jsonrpc.request_id';

    /**
     * Protocol version as in `jsonrpc` property of request/response. Since JSON-RPC 1.0 does not specify this, the value can be omitted.
     *
     * @example 2.0
     * @example 1.0
     */
    public const RPC_JSONRPC_VERSION = 'rpc.jsonrpc.version';

    /**
     * Compressed size of the message in bytes.
     */
    public const MESSAGE_COMPRESSED_SIZE = 'message.compressed_size';

    /**
     * MUST be calculated as two different counters starting from `1` one for sent messages and one for received message.
     *
     * This way we guarantee that the values will be consistent between different implementations.
     */
    public const MESSAGE_ID = 'message.id';

    /**
     * Whether this is a received or sent message.
     */
    public const MESSAGE_TYPE = 'message.type';

    /**
     * Uncompressed size of the message in bytes.
     */
    public const MESSAGE_UNCOMPRESSED_SIZE = 'message.uncompressed_size';

    /**
     * The error codes of the Connect request. Error codes are always string values.
     */
    public const RPC_CONNECT_RPC_ERROR_CODE = 'rpc.connect_rpc.error_code';

    /**
     * SHOULD be set to true if the exception event is recorded at a point where it is known that the exception is escaping the scope of the span.
     *
     * An exception is considered to have escaped (or left) the scope of a span,
     * if that span is ended while the exception is still logically &quot;in flight&quot;.
     * This may be actually &quot;in flight&quot; in some languages (e.g. if the exception
     * is passed to a Context manager's `__exit__` method in Python) but will
     * usually be caught at the point of recording the exception in most languages.It is usually not possible to determine at the point where an exception is thrown
     * whether it will escape the scope of a span.
     * However, it is trivial to know that an exception
     * will escape, if one checks for an active exception just before ending the span,
     * as done in the example above.It follows that an exception may still escape the scope of the span
     * even if the `exception.escaped` attribute was not set or set to false,
     * since the event might have been recorded at a time where it was not
     * clear whether the exception will escape.
     */
    public const EXCEPTION_ESCAPED = 'exception.escaped';

    /**
     * The URI fragment component.
     *
     * @example SemConv
     */
    public const URL_FRAGMENT = 'url.fragment';

    /**
     * @deprecated
     */
    public const FAAS_EXECUTION = 'faas.execution';

    /**
     * @deprecated
     */
    public const HTTP_HOST = 'http.host';

    /**
     * @deprecated
     */
    public const HTTP_REQUEST_CONTENT_LENGTH_UNCOMPRESSED = 'http.request_content_length_uncompressed';

    /**
     * @deprecated
     */
    public const HTTP_RESPONSE_CONTENT_LENGTH_UNCOMPRESSED = 'http.response_content_length_uncompressed';

    /**
     * @deprecated
     */
    public const HTTP_RETRY_COUNT = 'http.retry_count';

    /**
     * @deprecated
     */
    public const HTTP_SERVER_NAME = 'http.server_name';

    /**
     * @deprecated
     */
    public const HTTP_USER_AGENT = 'http.user_agent';

    /**
     * @deprecated
     */
    public const MESSAGING_CONVERSATION_ID = 'messaging.conversation_id';

    /**
     * @deprecated
     */
    public const MESSAGING_DESTINATION = 'messaging.destination';

    /**
     * @deprecated
     */
    public const MESSAGING_KAFKA_PARTITION = 'messaging.kafka.partition';

    /**
     * @deprecated
     */
    public const MESSAGING_KAFKA_TOMBSTONE = 'messaging.kafka.tombstone';

    /**
     * @deprecated
     */
    public const MESSAGING_PROTOCOL = 'messaging.protocol';

    /**
     * @deprecated
     */
    public const MESSAGING_PROTOCOL_VERSION = 'messaging.protocol_version';

    /**
     * @deprecated
     */
    public const MESSAGING_RABBITMQ_ROUTING_KEY = 'messaging.rabbitmq.routing_key';

    /**
     * @deprecated
     */
    public const MESSAGING_TEMP_DESTINATION = 'messaging.temp_destination';

    /**
     * @deprecated
     */
    public const MESSAGING_URL = 'messaging.url';

    /**
     * @deprecated
     */
    public const NET_HOST_IP = 'net.host.ip';

    /**
     * @deprecated
     */
    public const NET_PEER_IP = 'net.peer.ip';

    /**
     * @deprecated
     */
    public const HTTP_CLIENT_IP = 'http.client_ip';

    /**
     * @deprecated
     */
    public const HTTP_FLAVOR = 'http.flavor';

    /**
     * @deprecated
     */
    public const MESSAGING_CONSUMER_ID = 'messaging.consumer.id';

    /**
     * @deprecated
     */
    public const MESSAGING_DESTINATION_KIND = 'messaging.destination.kind';

    /**
     * @deprecated
     */
    public const MESSAGING_KAFKA_CLIENT_ID = 'messaging.kafka.client_id';

    /**
     * @deprecated
     */
    public const MESSAGING_KAFKA_SOURCE_PARTITION = 'messaging.kafka.source.partition';

    /**
     * @deprecated
     */
    public const MESSAGING_MESSAGE_PAYLOAD_COMPRESSED_SIZE_BYTES = 'messaging.message.payload_compressed_size_bytes';

    /**
     * @deprecated
     */
    public const MESSAGING_MESSAGE_PAYLOAD_SIZE_BYTES = 'messaging.message.payload_size_bytes';

    /**
     * @deprecated
     */
    public const MESSAGING_ROCKETMQ_CLIENT_ID = 'messaging.rocketmq.client_id';

    /**
     * @deprecated
     */
    public const MESSAGING_SOURCE_ANONYMOUS = 'messaging.source.anonymous';

    /**
     * @deprecated
     */
    public const MESSAGING_SOURCE_KIND = 'messaging.source.kind';

    /**
     * @deprecated
     */
    public const MESSAGING_SOURCE_NAME = 'messaging.source.name';

    /**
     * @deprecated
     */
    public const MESSAGING_SOURCE_TEMPLATE = 'messaging.source.template';

    /**
     * @deprecated
     */
    public const MESSAGING_SOURCE_TEMPORARY = 'messaging.source.temporary';

    /**
     * @deprecated
     */
    public const NET_APP_PROTOCOL_NAME = 'net.app.protocol.name';

    /**
     * @deprecated
     */
    public const NET_APP_PROTOCOL_VERSION = 'net.app.protocol.version';

    /**
     * @deprecated
     */
    public const NET_HOST_CARRIER_ICC = 'net.host.carrier.icc';

    /**
     * @deprecated
     */
    public const NET_HOST_CARRIER_MCC = 'net.host.carrier.mcc';

    /**
     * @deprecated
     */
    public const NET_HOST_CARRIER_MNC = 'net.host.carrier.mnc';

    /**
     * @deprecated
     */
    public const NET_HOST_CARRIER_NAME = 'net.host.carrier.name';

    /**
     * @deprecated
     */
    public const NET_HOST_CONNECTION_SUBTYPE = 'net.host.connection.subtype';

    /**
     * @deprecated
     */
    public const NET_HOST_CONNECTION_TYPE = 'net.host.connection.type';
}
