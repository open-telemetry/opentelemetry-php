<?php

// DO NOT EDIT, this is an Auto-generated file from script/semantic-conventions

declare(strict_types=1);

namespace OpenTelemetry\SemConv\Incubating\Metrics;

/**
 * Metrics for nfs.
 * May contain @experimental Semantic Conventions which may change or be removed in the future.
 */
interface NfsIncubatingMetrics
{
    /**
     * Reports the count of kernel NFS client TCP segments and UDP datagrams handled.
     * Linux: this metric is taken from the Linux kernel's svc_stat.netudpcnt and svc_stat.nettcpcnt
     *
     * Instrument: counter
     * Unit: {record}
     * @experimental
     */
    public const NFS_CLIENT_NET_COUNT = 'nfs.client.net.count';

    /**
     * Reports the count of kernel NFS client TCP connections accepted.
     * Linux: this metric is taken from the Linux kernel's svc_stat.nettcpconn
     *
     * Instrument: counter
     * Unit: {connection}
     * @experimental
     */
    public const NFS_CLIENT_NET_TCP_CONNECTION_ACCEPTED = 'nfs.client.net.tcp.connection.accepted';

    /**
     * Reports the count of kernel NFSv4+ client operations.
     *
     * Instrument: counter
     * Unit: {operation}
     * @experimental
     */
    public const NFS_CLIENT_OPERATION_COUNT = 'nfs.client.operation.count';

    /**
     * Reports the count of kernel NFS client procedures.
     *
     * Instrument: counter
     * Unit: {procedure}
     * @experimental
     */
    public const NFS_CLIENT_PROCEDURE_COUNT = 'nfs.client.procedure.count';

    /**
     * Reports the count of kernel NFS client RPC authentication refreshes.
     * Linux: this metric is taken from the Linux kernel's svc_stat.rpcauthrefresh
     *
     * Instrument: counter
     * Unit: {authrefresh}
     * @experimental
     */
    public const NFS_CLIENT_RPC_AUTHREFRESH_COUNT = 'nfs.client.rpc.authrefresh.count';

    /**
     * Reports the count of kernel NFS client RPCs sent, regardless of whether they're accepted/rejected by the server.
     * Linux: this metric is taken from the Linux kernel's svc_stat.rpccnt
     *
     * Instrument: counter
     * Unit: {request}
     * @experimental
     */
    public const NFS_CLIENT_RPC_COUNT = 'nfs.client.rpc.count';

    /**
     * Reports the count of kernel NFS client RPC retransmits.
     * Linux: this metric is taken from the Linux kernel's svc_stat.rpcretrans
     *
     * Instrument: counter
     * Unit: {retransmit}
     * @experimental
     */
    public const NFS_CLIENT_RPC_RETRANSMIT_COUNT = 'nfs.client.rpc.retransmit.count';

    /**
     * Reports the count of kernel NFS server stale file handles.
     * Linux: this metric is taken from the Linux kernel NFSD_STATS_FH_STALE counter in the nfsd_net struct
     *
     * Instrument: counter
     * Unit: {fh}
     * @experimental
     */
    public const NFS_SERVER_FH_STALE_COUNT = 'nfs.server.fh.stale.count';

    /**
     * Reports the count of kernel NFS server bytes returned to receive and transmit (read and write) requests.
     * Linux: this metric is taken from the Linux kernel NFSD_STATS_IO_READ and NFSD_STATS_IO_WRITE counters in the nfsd_net struct
     *
     * Instrument: counter
     * Unit: By
     * @experimental
     */
    public const NFS_SERVER_IO = 'nfs.server.io';

    /**
     * Reports the count of kernel NFS server TCP segments and UDP datagrams handled.
     * Linux: this metric is taken from the Linux kernel's svc_stat.nettcpcnt and svc_stat.netudpcnt
     *
     * Instrument: counter
     * Unit: {record}
     * @experimental
     */
    public const NFS_SERVER_NET_COUNT = 'nfs.server.net.count';

    /**
     * Reports the count of kernel NFS server TCP connections accepted.
     * Linux: this metric is taken from the Linux kernel's svc_stat.nettcpconn
     *
     * Instrument: counter
     * Unit: {connection}
     * @experimental
     */
    public const NFS_SERVER_NET_TCP_CONNECTION_ACCEPTED = 'nfs.server.net.tcp.connection.accepted';

    /**
     * Reports the count of kernel NFSv4+ server operations.
     *
     * Instrument: counter
     * Unit: {operation}
     * @experimental
     */
    public const NFS_SERVER_OPERATION_COUNT = 'nfs.server.operation.count';

    /**
     * Reports the count of kernel NFS server procedures.
     *
     * Instrument: counter
     * Unit: {procedure}
     * @experimental
     */
    public const NFS_SERVER_PROCEDURE_COUNT = 'nfs.server.procedure.count';

    /**
     * Reports the kernel NFS server reply cache request count by cache hit status.
     *
     * Instrument: counter
     * Unit: {request}
     * @experimental
     */
    public const NFS_SERVER_REPCACHE_REQUESTS = 'nfs.server.repcache.requests';

    /**
     * Reports the count of kernel NFS server RPCs handled.
     * Linux: this metric is taken from the Linux kernel's svc_stat.rpccnt, the count of good RPCs. This metric can have
     * an error.type of "format", "auth", or "client" for svc_stat.badfmt, svc_stat.badauth, and svc_stat.badclnt.
     *
     * Instrument: counter
     * Unit: {request}
     * @experimental
     */
    public const NFS_SERVER_RPC_COUNT = 'nfs.server.rpc.count';

    /**
     * Reports the count of kernel NFS server available threads.
     * Linux: this metric is taken from the Linux kernel nfsd_th_cnt variable
     *
     * Instrument: updowncounter
     * Unit: {thread}
     * @experimental
     */
    public const NFS_SERVER_THREAD_COUNT = 'nfs.server.thread.count';

}
