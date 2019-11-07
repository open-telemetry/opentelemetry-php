<?php

require __DIR__.'/../vendor/autoload.php';

use OpenTelemetry\Tracing\Builder;
use OpenTelemetry\Tracing\SpanContext;
use OpenTelemetry\Tracing\Sampler\QpsSampler;
use Cache\Adapter\Memcached\MemcachedCachePool;
use Cache\Adapter\Common\PhpCacheItem;

$client = new \Memcached();
$client->addServer('localhost', 11211);
$cache = new MemcachedCachePool($client);

$sampler = new QpsSampler($cache, [
    'cacheItemClass' => PhpCacheItem::class
]);

if($sampler->shouldSample()) {
    $spanContext = SpanContext::generate(); // or extract from headers
    $tracer = Builder::create()->setSpanContext($spanContext)->getTracer();
    
    // start a span, register some events
    $span = $tracer->createSpan('session.generate');
    
    // set attributes as array
    $span->setAttributes([ 'remote_ip' => '1.2.3.4' ]);
    // set attribute one by one
    $span->setAttribute('country', 'USA');
    
    $span->addEvent('found_login', [
      'id' => 12345,
      'username' => 'bob',
    ]);
    $span->addEvent('generated_session', [
      'id' => md5(microtime(true))
    ]);
    
    $span->end(); // pass status as an optional argument
    print_r($span);  // print the span as a resulting output
} else {
    echo "Sampling is not enabled";
}