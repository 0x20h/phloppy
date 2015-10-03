<?php

namespace Phloppy\Stream;

use Phloppy\Cache\CacheInterface;
use Phloppy\Cache\MemoryCache;
use Phloppy\Client\Node;
use Phloppy\Exception\ConnectException;
use Phloppy\NodeInfo;
use Psr\Log\LoggerInterface;

/**
 * Node Connection Pool with the ability to cache cluster nodes.
 *
 * After establishing the connection with one of the provided node addresses a HELLO command will be
 * issued to retrieve all available nodes. The information will be stored in the provided CacheInterface implementation
 * and retrieved the next time when a connect() is issued.
 */
class CachingPool extends Pool
{

    /**
     * @var CacheInterface
     */
    private $cache;

    /**
     * Cache TTL in seconds.
     *
     * @var int Default is 10 minutes
     */
    private $ttl = 600;


    /**
     * @param array                $nodeUrls
     * @param CacheInterface|null  $cache
     * @param LoggerInterface|null $log
     *
     * @throws ConnectException
     */
    public function __construct(array $nodeUrls = array(), CacheInterface $cache = null, LoggerInterface $log = null)
    {
        if (!$cache) {
            $cache = new MemoryCache();
        }

        $this->cache = $cache;
        parent::__construct($nodeUrls, $log);
    }


    /**
     * Connect to a random node in the (cached) node list.
     *
     * @return DefaultStream Stream to a connected node.
     *
     * @throws ConnectException
     */
    public function connect()
    {
        $key = array_reduce($this->nodeUrls, function ($current, $prev) {
            return md5($current.$prev);
        }, '');

        // prefer cached results
        $hit = $this->cache->get($key);

        if (!empty($hit)) {
            $this->log->notice('nodelist retrieved from cache', ['nodes' => $hit, 'key' => $key]);
            $this->nodeUrls = $hit;
        }

        $stream = parent::connect();

        // update cache
        if (empty($hit)) {
            $this->updateNodes($key, $stream);
        }

        return $stream;
    }


    /**
     * Return the internal node cache TTL.
     *
     * @return int
     */
    public function getCacheTtl()
    {
        return $this->ttl;
    }


    /**
     * Set the internal node cache TTL.
     *
     * @param int $ttl
     */
    public function setCacheTtl($ttl)
    {
        $this->ttl = (int)$ttl;
    }


    /**
     * Update the node list using the HELLO command.
     *
     * @param string          $key
     * @param StreamInterface $stream
     */
    private function updateNodes($key, StreamInterface $stream)
    {
        $this->nodeUrls = array_map(function (NodeInfo $element) {
            return $element->getServer();
        }, (new Node($stream, $this->log))->hello());

        $this->log->notice('caching connection info from HELLO', [
            'key' => $key,
            'nodes' => $this->nodeUrls,
            'ttl' => $this->getCacheTtl(),
        ]);

        $this->cache->set($key, $this->nodeUrls, $this->getCacheTtl());
    }
}
