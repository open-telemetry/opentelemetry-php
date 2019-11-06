<?php
namespace OpenTelemetry\Tracing\Sampler;

 use Psr\Cache\CacheItemPoolInterface;
 use Cache\Adapter\Common\CacheItem;



class QpsSampler implements SamplerInterface
{
    const DEFAULT_CACHE_KEY = '__opentelemetry_trace__';
    const DEFAULT_QPS_RATE = 0.1;

    /**
     * @var CacheItemPoolInterface The cache store used for storing the last
     */
    private $cache;

    /**
     * @var float The QPS rate.
     */
    private $rate;

    /**
     * @var string The class name of the cache item interface to use
     */
    private $cacheItemClass;

    /**
     * @var string The cache key
     */
    private $key;

    public function __construct(CacheItemPoolInterface $cache = null, $options = [])
    {
        $this->cache = $cache ?: $this->defaultCache();
        if (!$this->cache) {
            throw new \InvalidArgumentException('Cannot use QpsSampler without providing a PSR-6 $cache');
        }

        $options += [
            'cacheItemClass' => CacheItem::class,
            'rate' => self::DEFAULT_QPS_RATE,
            'key' => self::DEFAULT_CACHE_KEY
        ];

        if (array_key_exists('cacheItemClass', $options)) {
            $this->cacheItemClass = $options['cacheItemClass'];
        }

        $this->rate = $options['rate'];
        $this->key = $options['key'];

        if ($this->rate > 1 || $this->rate <= 0) {
            throw new \InvalidArgumentException('QPS sampling rate must be less that 1 query per second');
        }
    }

    /**
     * Returns whether or not the request should be sampled.
     *
     * @return bool
     */
    public function shouldSample()
    {
        if ($item = $this->cache->getItem($this->key)) {
            if ((float) $item->get() > microtime(true)) {
                return false;
            }
        }

        $item = new $this->cacheItemClass($this->key);
        $item->set(microtime(true) + 1.0 / $this->rate);

        // TODO: what if the cache fails to save?
        $this->cache->save($item);

        return true;
    }
    /**
     * Return the query-per-second rate
     *
     * @return float
     */
    public function rate()
    {
        return $this->rate;
    }
}