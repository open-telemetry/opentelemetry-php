<?php

// DO NOT EDIT, this is an Auto-generated file from script/semantic-convention/templates/AttributeValues.php.j2

declare(strict_types=1);

namespace OpenTelemetry\SemConv;

interface TraceAttributeValues
{
    /**
     * The URL of the OpenTelemetry schema for these keys and values.
     */
    public const SCHEMA_URL = 'https://opentelemetry.io/schemas/1.12.0';
    /**
     * @see TraceAttributes::OPENTRACING_REF_TYPE The parent Span depends on the child Span in some capacity
     */
    public const OPENTRACING_REF_TYPE_CHILD_OF = 'child_of';

    /**
     * @see TraceAttributes::OPENTRACING_REF_TYPE The parent Span does not depend in any way on the result of the child Span
     */
    public const OPENTRACING_REF_TYPE_FOLLOWS_FROM = 'follows_from';

    /**
     * @see TraceAttributes::DB_SYSTEM Some other SQL database. Fallback only. See notes
     */
    public const DB_SYSTEM_OTHER_SQL = 'other_sql';

    /**
     * @see TraceAttributes::DB_SYSTEM Microsoft SQL Server
     */
    public const DB_SYSTEM_MSSQL = 'mssql';

    /**
     * @see TraceAttributes::DB_SYSTEM MySQL
     */
    public const DB_SYSTEM_MYSQL = 'mysql';

    /**
     * @see TraceAttributes::DB_SYSTEM Oracle Database
     */
    public const DB_SYSTEM_ORACLE = 'oracle';

    /**
     * @see TraceAttributes::DB_SYSTEM IBM Db2
     */
    public const DB_SYSTEM_DB2 = 'db2';

    /**
     * @see TraceAttributes::DB_SYSTEM PostgreSQL
     */
    public const DB_SYSTEM_POSTGRESQL = 'postgresql';

    /**
     * @see TraceAttributes::DB_SYSTEM Amazon Redshift
     */
    public const DB_SYSTEM_REDSHIFT = 'redshift';

    /**
     * @see TraceAttributes::DB_SYSTEM Apache Hive
     */
    public const DB_SYSTEM_HIVE = 'hive';

    /**
     * @see TraceAttributes::DB_SYSTEM Cloudscape
     */
    public const DB_SYSTEM_CLOUDSCAPE = 'cloudscape';

    /**
     * @see TraceAttributes::DB_SYSTEM HyperSQL DataBase
     */
    public const DB_SYSTEM_HSQLDB = 'hsqldb';

    /**
     * @see TraceAttributes::DB_SYSTEM Progress Database
     */
    public const DB_SYSTEM_PROGRESS = 'progress';

    /**
     * @see TraceAttributes::DB_SYSTEM SAP MaxDB
     */
    public const DB_SYSTEM_MAXDB = 'maxdb';

    /**
     * @see TraceAttributes::DB_SYSTEM SAP HANA
     */
    public const DB_SYSTEM_HANADB = 'hanadb';

    /**
     * @see TraceAttributes::DB_SYSTEM Ingres
     */
    public const DB_SYSTEM_INGRES = 'ingres';

    /**
     * @see TraceAttributes::DB_SYSTEM FirstSQL
     */
    public const DB_SYSTEM_FIRSTSQL = 'firstsql';

    /**
     * @see TraceAttributes::DB_SYSTEM EnterpriseDB
     */
    public const DB_SYSTEM_EDB = 'edb';

    /**
     * @see TraceAttributes::DB_SYSTEM InterSystems Caché
     */
    public const DB_SYSTEM_CACHE = 'cache';

    /**
     * @see TraceAttributes::DB_SYSTEM Adabas (Adaptable Database System)
     */
    public const DB_SYSTEM_ADABAS = 'adabas';

    /**
     * @see TraceAttributes::DB_SYSTEM Firebird
     */
    public const DB_SYSTEM_FIREBIRD = 'firebird';

    /**
     * @see TraceAttributes::DB_SYSTEM Apache Derby
     */
    public const DB_SYSTEM_DERBY = 'derby';

    /**
     * @see TraceAttributes::DB_SYSTEM FileMaker
     */
    public const DB_SYSTEM_FILEMAKER = 'filemaker';

    /**
     * @see TraceAttributes::DB_SYSTEM Informix
     */
    public const DB_SYSTEM_INFORMIX = 'informix';

    /**
     * @see TraceAttributes::DB_SYSTEM InstantDB
     */
    public const DB_SYSTEM_INSTANTDB = 'instantdb';

    /**
     * @see TraceAttributes::DB_SYSTEM InterBase
     */
    public const DB_SYSTEM_INTERBASE = 'interbase';

    /**
     * @see TraceAttributes::DB_SYSTEM MariaDB
     */
    public const DB_SYSTEM_MARIADB = 'mariadb';

    /**
     * @see TraceAttributes::DB_SYSTEM Netezza
     */
    public const DB_SYSTEM_NETEZZA = 'netezza';

    /**
     * @see TraceAttributes::DB_SYSTEM Pervasive PSQL
     */
    public const DB_SYSTEM_PERVASIVE = 'pervasive';

    /**
     * @see TraceAttributes::DB_SYSTEM PointBase
     */
    public const DB_SYSTEM_POINTBASE = 'pointbase';

    /**
     * @see TraceAttributes::DB_SYSTEM SQLite
     */
    public const DB_SYSTEM_SQLITE = 'sqlite';

    /**
     * @see TraceAttributes::DB_SYSTEM Sybase
     */
    public const DB_SYSTEM_SYBASE = 'sybase';

    /**
     * @see TraceAttributes::DB_SYSTEM Teradata
     */
    public const DB_SYSTEM_TERADATA = 'teradata';

    /**
     * @see TraceAttributes::DB_SYSTEM Vertica
     */
    public const DB_SYSTEM_VERTICA = 'vertica';

    /**
     * @see TraceAttributes::DB_SYSTEM H2
     */
    public const DB_SYSTEM_H2 = 'h2';

    /**
     * @see TraceAttributes::DB_SYSTEM ColdFusion IMQ
     */
    public const DB_SYSTEM_COLDFUSION = 'coldfusion';

    /**
     * @see TraceAttributes::DB_SYSTEM Apache Cassandra
     */
    public const DB_SYSTEM_CASSANDRA = 'cassandra';

    /**
     * @see TraceAttributes::DB_SYSTEM Apache HBase
     */
    public const DB_SYSTEM_HBASE = 'hbase';

    /**
     * @see TraceAttributes::DB_SYSTEM MongoDB
     */
    public const DB_SYSTEM_MONGODB = 'mongodb';

    /**
     * @see TraceAttributes::DB_SYSTEM Redis
     */
    public const DB_SYSTEM_REDIS = 'redis';

    /**
     * @see TraceAttributes::DB_SYSTEM Couchbase
     */
    public const DB_SYSTEM_COUCHBASE = 'couchbase';

    /**
     * @see TraceAttributes::DB_SYSTEM CouchDB
     */
    public const DB_SYSTEM_COUCHDB = 'couchdb';

    /**
     * @see TraceAttributes::DB_SYSTEM Microsoft Azure Cosmos DB
     */
    public const DB_SYSTEM_COSMOSDB = 'cosmosdb';

    /**
     * @see TraceAttributes::DB_SYSTEM Amazon DynamoDB
     */
    public const DB_SYSTEM_DYNAMODB = 'dynamodb';

    /**
     * @see TraceAttributes::DB_SYSTEM Neo4j
     */
    public const DB_SYSTEM_NEO4J = 'neo4j';

    /**
     * @see TraceAttributes::DB_SYSTEM Apache Geode
     */
    public const DB_SYSTEM_GEODE = 'geode';

    /**
     * @see TraceAttributes::DB_SYSTEM Elasticsearch
     */
    public const DB_SYSTEM_ELASTICSEARCH = 'elasticsearch';

    /**
     * @see TraceAttributes::DB_SYSTEM Memcached
     */
    public const DB_SYSTEM_MEMCACHED = 'memcached';

    /**
     * @see TraceAttributes::DB_SYSTEM CockroachDB
     */
    public const DB_SYSTEM_COCKROACHDB = 'cockroachdb';

    /**
     * @see TraceAttributes::NET_TRANSPORT ip_tcp
     */
    public const NET_TRANSPORT_IP_TCP = 'ip_tcp';

    /**
     * @see TraceAttributes::NET_TRANSPORT ip_udp
     */
    public const NET_TRANSPORT_IP_UDP = 'ip_udp';

    /**
     * @see TraceAttributes::NET_TRANSPORT Another IP-based protocol
     */
    public const NET_TRANSPORT_IP = 'ip';

    /**
     * @see TraceAttributes::NET_TRANSPORT Unix Domain socket. See below
     */
    public const NET_TRANSPORT_UNIX = 'unix';

    /**
     * @see TraceAttributes::NET_TRANSPORT Named or anonymous pipe. See note below
     */
    public const NET_TRANSPORT_PIPE = 'pipe';

    /**
     * @see TraceAttributes::NET_TRANSPORT In-process communication
     *
     * Signals that there is only in-process communication not using a &quot;real&quot; network protocol in cases where network attributes would normally be expected. Usually all other network attributes can be left out in that case.
     */
    public const NET_TRANSPORT_INPROC = 'inproc';

    /**
     * @see TraceAttributes::NET_TRANSPORT Something else (non IP-based)
     */
    public const NET_TRANSPORT_OTHER = 'other';

    /**
     * @see TraceAttributes::DB_CASSANDRA_CONSISTENCY_LEVEL all
     */
    public const DB_CASSANDRA_CONSISTENCY_LEVEL_ALL = 'all';

    /**
     * @see TraceAttributes::DB_CASSANDRA_CONSISTENCY_LEVEL each_quorum
     */
    public const DB_CASSANDRA_CONSISTENCY_LEVEL_EACH_QUORUM = 'each_quorum';

    /**
     * @see TraceAttributes::DB_CASSANDRA_CONSISTENCY_LEVEL quorum
     */
    public const DB_CASSANDRA_CONSISTENCY_LEVEL_QUORUM = 'quorum';

    /**
     * @see TraceAttributes::DB_CASSANDRA_CONSISTENCY_LEVEL local_quorum
     */
    public const DB_CASSANDRA_CONSISTENCY_LEVEL_LOCAL_QUORUM = 'local_quorum';

    /**
     * @see TraceAttributes::DB_CASSANDRA_CONSISTENCY_LEVEL one
     */
    public const DB_CASSANDRA_CONSISTENCY_LEVEL_ONE = 'one';

    /**
     * @see TraceAttributes::DB_CASSANDRA_CONSISTENCY_LEVEL two
     */
    public const DB_CASSANDRA_CONSISTENCY_LEVEL_TWO = 'two';

    /**
     * @see TraceAttributes::DB_CASSANDRA_CONSISTENCY_LEVEL three
     */
    public const DB_CASSANDRA_CONSISTENCY_LEVEL_THREE = 'three';

    /**
     * @see TraceAttributes::DB_CASSANDRA_CONSISTENCY_LEVEL local_one
     */
    public const DB_CASSANDRA_CONSISTENCY_LEVEL_LOCAL_ONE = 'local_one';

    /**
     * @see TraceAttributes::DB_CASSANDRA_CONSISTENCY_LEVEL any
     */
    public const DB_CASSANDRA_CONSISTENCY_LEVEL_ANY = 'any';

    /**
     * @see TraceAttributes::DB_CASSANDRA_CONSISTENCY_LEVEL serial
     */
    public const DB_CASSANDRA_CONSISTENCY_LEVEL_SERIAL = 'serial';

    /**
     * @see TraceAttributes::DB_CASSANDRA_CONSISTENCY_LEVEL local_serial
     */
    public const DB_CASSANDRA_CONSISTENCY_LEVEL_LOCAL_SERIAL = 'local_serial';

    /**
     * @see TraceAttributes::FAAS_TRIGGER A response to some data source operation such as a database or filesystem read/write
     */
    public const FAAS_TRIGGER_DATASOURCE = 'datasource';

    /**
     * @see TraceAttributes::FAAS_TRIGGER To provide an answer to an inbound HTTP request
     */
    public const FAAS_TRIGGER_HTTP = 'http';

    /**
     * @see TraceAttributes::FAAS_TRIGGER A function is set to be executed when messages are sent to a messaging system
     */
    public const FAAS_TRIGGER_PUBSUB = 'pubsub';

    /**
     * @see TraceAttributes::FAAS_TRIGGER A function is scheduled to be executed regularly
     */
    public const FAAS_TRIGGER_TIMER = 'timer';

    /**
     * @see TraceAttributes::FAAS_TRIGGER If none of the others apply
     */
    public const FAAS_TRIGGER_OTHER = 'other';

    /**
     * @see TraceAttributes::FAAS_DOCUMENT_OPERATION When a new object is created
     */
    public const FAAS_DOCUMENT_OPERATION_INSERT = 'insert';

    /**
     * @see TraceAttributes::FAAS_DOCUMENT_OPERATION When an object is modified
     */
    public const FAAS_DOCUMENT_OPERATION_EDIT = 'edit';

    /**
     * @see TraceAttributes::FAAS_DOCUMENT_OPERATION When an object is deleted
     */
    public const FAAS_DOCUMENT_OPERATION_DELETE = 'delete';

    /**
     * @see TraceAttributes::HTTP_FLAVOR HTTP/1.0
     */
    public const HTTP_FLAVOR_HTTP_1_0 = '1.0';

    /**
     * @see TraceAttributes::HTTP_FLAVOR HTTP/1.1
     */
    public const HTTP_FLAVOR_HTTP_1_1 = '1.1';

    /**
     * @see TraceAttributes::HTTP_FLAVOR HTTP/2
     */
    public const HTTP_FLAVOR_HTTP_2_0 = '2.0';

    /**
     * @see TraceAttributes::HTTP_FLAVOR HTTP/3
     */
    public const HTTP_FLAVOR_HTTP_3_0 = '3.0';

    /**
     * @see TraceAttributes::HTTP_FLAVOR SPDY protocol
     */
    public const HTTP_FLAVOR_SPDY = 'SPDY';

    /**
     * @see TraceAttributes::HTTP_FLAVOR QUIC protocol
     */
    public const HTTP_FLAVOR_QUIC = 'QUIC';

    /**
     * @see TraceAttributes::NET_HOST_CONNECTION_TYPE wifi
     */
    public const NET_HOST_CONNECTION_TYPE_WIFI = 'wifi';

    /**
     * @see TraceAttributes::NET_HOST_CONNECTION_TYPE wired
     */
    public const NET_HOST_CONNECTION_TYPE_WIRED = 'wired';

    /**
     * @see TraceAttributes::NET_HOST_CONNECTION_TYPE cell
     */
    public const NET_HOST_CONNECTION_TYPE_CELL = 'cell';

    /**
     * @see TraceAttributes::NET_HOST_CONNECTION_TYPE unavailable
     */
    public const NET_HOST_CONNECTION_TYPE_UNAVAILABLE = 'unavailable';

    /**
     * @see TraceAttributes::NET_HOST_CONNECTION_TYPE unknown
     */
    public const NET_HOST_CONNECTION_TYPE_UNKNOWN = 'unknown';

    /**
     * @see TraceAttributes::NET_HOST_CONNECTION_SUBTYPE GPRS
     */
    public const NET_HOST_CONNECTION_SUBTYPE_GPRS = 'gprs';

    /**
     * @see TraceAttributes::NET_HOST_CONNECTION_SUBTYPE EDGE
     */
    public const NET_HOST_CONNECTION_SUBTYPE_EDGE = 'edge';

    /**
     * @see TraceAttributes::NET_HOST_CONNECTION_SUBTYPE UMTS
     */
    public const NET_HOST_CONNECTION_SUBTYPE_UMTS = 'umts';

    /**
     * @see TraceAttributes::NET_HOST_CONNECTION_SUBTYPE CDMA
     */
    public const NET_HOST_CONNECTION_SUBTYPE_CDMA = 'cdma';

    /**
     * @see TraceAttributes::NET_HOST_CONNECTION_SUBTYPE EVDO Rel. 0
     */
    public const NET_HOST_CONNECTION_SUBTYPE_EVDO_0 = 'evdo_0';

    /**
     * @see TraceAttributes::NET_HOST_CONNECTION_SUBTYPE EVDO Rev. A
     */
    public const NET_HOST_CONNECTION_SUBTYPE_EVDO_A = 'evdo_a';

    /**
     * @see TraceAttributes::NET_HOST_CONNECTION_SUBTYPE CDMA2000 1XRTT
     */
    public const NET_HOST_CONNECTION_SUBTYPE_CDMA2000_1XRTT = 'cdma2000_1xrtt';

    /**
     * @see TraceAttributes::NET_HOST_CONNECTION_SUBTYPE HSDPA
     */
    public const NET_HOST_CONNECTION_SUBTYPE_HSDPA = 'hsdpa';

    /**
     * @see TraceAttributes::NET_HOST_CONNECTION_SUBTYPE HSUPA
     */
    public const NET_HOST_CONNECTION_SUBTYPE_HSUPA = 'hsupa';

    /**
     * @see TraceAttributes::NET_HOST_CONNECTION_SUBTYPE HSPA
     */
    public const NET_HOST_CONNECTION_SUBTYPE_HSPA = 'hspa';

    /**
     * @see TraceAttributes::NET_HOST_CONNECTION_SUBTYPE IDEN
     */
    public const NET_HOST_CONNECTION_SUBTYPE_IDEN = 'iden';

    /**
     * @see TraceAttributes::NET_HOST_CONNECTION_SUBTYPE EVDO Rev. B
     */
    public const NET_HOST_CONNECTION_SUBTYPE_EVDO_B = 'evdo_b';

    /**
     * @see TraceAttributes::NET_HOST_CONNECTION_SUBTYPE LTE
     */
    public const NET_HOST_CONNECTION_SUBTYPE_LTE = 'lte';

    /**
     * @see TraceAttributes::NET_HOST_CONNECTION_SUBTYPE EHRPD
     */
    public const NET_HOST_CONNECTION_SUBTYPE_EHRPD = 'ehrpd';

    /**
     * @see TraceAttributes::NET_HOST_CONNECTION_SUBTYPE HSPAP
     */
    public const NET_HOST_CONNECTION_SUBTYPE_HSPAP = 'hspap';

    /**
     * @see TraceAttributes::NET_HOST_CONNECTION_SUBTYPE GSM
     */
    public const NET_HOST_CONNECTION_SUBTYPE_GSM = 'gsm';

    /**
     * @see TraceAttributes::NET_HOST_CONNECTION_SUBTYPE TD-SCDMA
     */
    public const NET_HOST_CONNECTION_SUBTYPE_TD_SCDMA = 'td_scdma';

    /**
     * @see TraceAttributes::NET_HOST_CONNECTION_SUBTYPE IWLAN
     */
    public const NET_HOST_CONNECTION_SUBTYPE_IWLAN = 'iwlan';

    /**
     * @see TraceAttributes::NET_HOST_CONNECTION_SUBTYPE 5G NR (New Radio)
     */
    public const NET_HOST_CONNECTION_SUBTYPE_NR = 'nr';

    /**
     * @see TraceAttributes::NET_HOST_CONNECTION_SUBTYPE 5G NRNSA (New Radio Non-Standalone)
     */
    public const NET_HOST_CONNECTION_SUBTYPE_NRNSA = 'nrnsa';

    /**
     * @see TraceAttributes::NET_HOST_CONNECTION_SUBTYPE LTE CA
     */
    public const NET_HOST_CONNECTION_SUBTYPE_LTE_CA = 'lte_ca';

    /**
     * @see TraceAttributes::MESSAGING_DESTINATION_KIND A message sent to a queue
     */
    public const MESSAGING_DESTINATION_KIND_QUEUE = 'queue';

    /**
     * @see TraceAttributes::MESSAGING_DESTINATION_KIND A message sent to a topic
     */
    public const MESSAGING_DESTINATION_KIND_TOPIC = 'topic';

    /**
     * @see TraceAttributes::FAAS_INVOKED_PROVIDER Alibaba Cloud
     */
    public const FAAS_INVOKED_PROVIDER_ALIBABA_CLOUD = 'alibaba_cloud';

    /**
     * @see TraceAttributes::FAAS_INVOKED_PROVIDER Amazon Web Services
     */
    public const FAAS_INVOKED_PROVIDER_AWS = 'aws';

    /**
     * @see TraceAttributes::FAAS_INVOKED_PROVIDER Microsoft Azure
     */
    public const FAAS_INVOKED_PROVIDER_AZURE = 'azure';

    /**
     * @see TraceAttributes::FAAS_INVOKED_PROVIDER Google Cloud Platform
     */
    public const FAAS_INVOKED_PROVIDER_GCP = 'gcp';

    /**
     * @see TraceAttributes::FAAS_INVOKED_PROVIDER Tencent Cloud
     */
    public const FAAS_INVOKED_PROVIDER_TENCENT_CLOUD = 'tencent_cloud';

    /**
     * @see TraceAttributes::RPC_SYSTEM gRPC
     */
    public const RPC_SYSTEM_GRPC = 'grpc';

    /**
     * @see TraceAttributes::RPC_SYSTEM Java RMI
     */
    public const RPC_SYSTEM_JAVA_RMI = 'java_rmi';

    /**
     * @see TraceAttributes::RPC_SYSTEM .NET WCF
     */
    public const RPC_SYSTEM_DOTNET_WCF = 'dotnet_wcf';

    /**
     * @see TraceAttributes::RPC_SYSTEM Apache Dubbo
     */
    public const RPC_SYSTEM_APACHE_DUBBO = 'apache_dubbo';

    /**
     * @see TraceAttributes::MESSAGING_OPERATION receive
     */
    public const MESSAGING_OPERATION_RECEIVE = 'receive';

    /**
     * @see TraceAttributes::MESSAGING_OPERATION process
     */
    public const MESSAGING_OPERATION_PROCESS = 'process';

    /**
     * @see TraceAttributes::MESSAGING_ROCKETMQ_MESSAGE_TYPE Normal message
     */
    public const MESSAGING_ROCKETMQ_MESSAGE_TYPE_NORMAL = 'normal';

    /**
     * @see TraceAttributes::MESSAGING_ROCKETMQ_MESSAGE_TYPE FIFO message
     */
    public const MESSAGING_ROCKETMQ_MESSAGE_TYPE_FIFO = 'fifo';

    /**
     * @see TraceAttributes::MESSAGING_ROCKETMQ_MESSAGE_TYPE Delay message
     */
    public const MESSAGING_ROCKETMQ_MESSAGE_TYPE_DELAY = 'delay';

    /**
     * @see TraceAttributes::MESSAGING_ROCKETMQ_MESSAGE_TYPE Transaction message
     */
    public const MESSAGING_ROCKETMQ_MESSAGE_TYPE_TRANSACTION = 'transaction';

    /**
     * @see TraceAttributes::MESSAGING_ROCKETMQ_CONSUMPTION_MODEL Clustering consumption model
     */
    public const MESSAGING_ROCKETMQ_CONSUMPTION_MODEL_CLUSTERING = 'clustering';

    /**
     * @see TraceAttributes::MESSAGING_ROCKETMQ_CONSUMPTION_MODEL Broadcasting consumption model
     */
    public const MESSAGING_ROCKETMQ_CONSUMPTION_MODEL_BROADCASTING = 'broadcasting';

    /**
     * @see TraceAttributes::RPC_GRPC_STATUS_CODE OK
     */
    public const RPC_GRPC_STATUS_CODE_OK = '0';

    /**
     * @see TraceAttributes::RPC_GRPC_STATUS_CODE CANCELLED
     */
    public const RPC_GRPC_STATUS_CODE_CANCELLED = '1';

    /**
     * @see TraceAttributes::RPC_GRPC_STATUS_CODE UNKNOWN
     */
    public const RPC_GRPC_STATUS_CODE_UNKNOWN = '2';

    /**
     * @see TraceAttributes::RPC_GRPC_STATUS_CODE INVALID_ARGUMENT
     */
    public const RPC_GRPC_STATUS_CODE_INVALID_ARGUMENT = '3';

    /**
     * @see TraceAttributes::RPC_GRPC_STATUS_CODE DEADLINE_EXCEEDED
     */
    public const RPC_GRPC_STATUS_CODE_DEADLINE_EXCEEDED = '4';

    /**
     * @see TraceAttributes::RPC_GRPC_STATUS_CODE NOT_FOUND
     */
    public const RPC_GRPC_STATUS_CODE_NOT_FOUND = '5';

    /**
     * @see TraceAttributes::RPC_GRPC_STATUS_CODE ALREADY_EXISTS
     */
    public const RPC_GRPC_STATUS_CODE_ALREADY_EXISTS = '6';

    /**
     * @see TraceAttributes::RPC_GRPC_STATUS_CODE PERMISSION_DENIED
     */
    public const RPC_GRPC_STATUS_CODE_PERMISSION_DENIED = '7';

    /**
     * @see TraceAttributes::RPC_GRPC_STATUS_CODE RESOURCE_EXHAUSTED
     */
    public const RPC_GRPC_STATUS_CODE_RESOURCE_EXHAUSTED = '8';

    /**
     * @see TraceAttributes::RPC_GRPC_STATUS_CODE FAILED_PRECONDITION
     */
    public const RPC_GRPC_STATUS_CODE_FAILED_PRECONDITION = '9';

    /**
     * @see TraceAttributes::RPC_GRPC_STATUS_CODE ABORTED
     */
    public const RPC_GRPC_STATUS_CODE_ABORTED = '10';

    /**
     * @see TraceAttributes::RPC_GRPC_STATUS_CODE OUT_OF_RANGE
     */
    public const RPC_GRPC_STATUS_CODE_OUT_OF_RANGE = '11';

    /**
     * @see TraceAttributes::RPC_GRPC_STATUS_CODE UNIMPLEMENTED
     */
    public const RPC_GRPC_STATUS_CODE_UNIMPLEMENTED = '12';

    /**
     * @see TraceAttributes::RPC_GRPC_STATUS_CODE INTERNAL
     */
    public const RPC_GRPC_STATUS_CODE_INTERNAL = '13';

    /**
     * @see TraceAttributes::RPC_GRPC_STATUS_CODE UNAVAILABLE
     */
    public const RPC_GRPC_STATUS_CODE_UNAVAILABLE = '14';

    /**
     * @see TraceAttributes::RPC_GRPC_STATUS_CODE DATA_LOSS
     */
    public const RPC_GRPC_STATUS_CODE_DATA_LOSS = '15';

    /**
     * @see TraceAttributes::RPC_GRPC_STATUS_CODE UNAUTHENTICATED
     */
    public const RPC_GRPC_STATUS_CODE_UNAUTHENTICATED = '16';

    /**
     * @see TraceAttributes::MESSAGE_TYPE sent
     */
    public const MESSAGE_TYPE_SENT = 'SENT';

    /**
     * @see TraceAttributes::MESSAGE_TYPE received
     */
    public const MESSAGE_TYPE_RECEIVED = 'RECEIVED';
}
