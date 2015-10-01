<?php

namespace Phloppy\Stream;

use Phloppy\Cache\CacheInterface;
use Phloppy\Cache\MemoryCache;
use Phloppy\Client\Node;
use Phloppy\NodeInfo;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Phloppy\Exception\ConnectException;

/**
 * Phloppy Node Pool.
 */
class Pool implements StreamInterface {

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
     * @var array
     */
    private $nodeUrls;

    /**
     * @var LoggerInterface
     */
    private $log;

    /**
     * @var StreamInterface
     */
    private $connected;


    /**
     * @param array                $nodeUrls
     * @param CacheInterface|null  $cache
     * @param LoggerInterface|null $log
     *
     * @throws ConnectException
     */
    public function __construct(array $nodeUrls = array(), CacheInterface $cache = null, LoggerInterface $log = null)
    {
        $this->nodeUrls = $nodeUrls;

        if (!$log) {
            $log = new NullLogger();
        }

        if (!$cache) {
            $cache = new MemoryCache();
        }

        $this->log       = $log;
        $this->cache     = $cache;
        $this->connected = $this->connect();
    }


    /**
     * @return array
     */
    public function getNodeUrls() {
        return $this->nodeUrls;
    }


    /**
     * @return StreamInterface
     */
    public function getActiveNode()
    {
        return $this->connected;
    }


    /**
     * @return bool
     * @throws ConnectException
     */
    public function reconnect()
    {
        $this->close();
        $this->connected = $this->connect();
        return true;
    }

    /**
     * Connect to a random node in the (cached) node list.
     *
     * @return DefaultStream Stream to a connected node.
     *
     * @throws ConnectException
     */
    private function connect() {
        $key = array_reduce($this->nodeUrls, function($c, $p) { return md5($c . $p);}, '');

        // prefer cached results
        $hit = $nodes = $this->cache->get($key);

        if (!empty($hit)) {
            $this->log->notice('nodelist retrieved from cache', ['nodes' => $nodes, 'key' => $key]);
        } else {
            $nodes = $this->nodeUrls;
        }

        while (count($nodes)) {
          // pick random server
          $idx = rand(0, count($nodes) - 1);

          try {
              $stream = new DefaultStream($nodes[$idx], $this->log);

              // update cache
              if (empty($hit)) {
                  $this->updateNodes($key, $stream);
              }

              return $stream;
          } catch (ConnectException $e) {
             $this->log->warning($e->getMessage());
          }

          // remove the selected server from the list
          array_splice($nodes, $idx, 1);
        }

        throw new ConnectException('unable to connect to any of ['.implode(',', $this->nodeUrls).']');
    }


    /**
     * Read a line from the stream
     *
     * @return string
     */
    public function readLine()
    {
        return $this->connected->readLine();
    }

    /**
     * Read bytes off from the stream.
     *
     * @param int|null $maxlen
     * @return string The response.
     */
    public function readBytes($maxlen = null)
    {
        return $this->connected->readBytes($maxlen);
    }

    /**
     * Read
     *
     * @param $msg
     *
     * @return StreamInterface the instance.
     */
    public function write($msg, $len = null)
    {
        return $this->connected->write($msg, $len);
    }

    /**
     * Close the stream.
     *
     * @return bool True on success.
     */
    public function close()
    {
        return $this->connected->close();
    }

    /**
     * Check if the stream is connected.
     *
     * @return boolean True if the stream is connected.
     */
    public function isConnected()
    {
        return $this->connected->isConnected();
    }


    /**
     * return the internal stream url.
     *
     * @return string
     */
    public function getNodeUrl()
    {
        return $this->connected->getNodeUrl();
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
        $this->ttl = (int) $ttl;
    }


    /**
     * Update the node list using the HELLO command.
     *
     * @param string          $key
     * @param StreamInterface $stream
     */
    private function updateNodes($key, StreamInterface $stream)
    {
        $this->nodeUrls = array_map(function(NodeInfo $e) {
            return $e->getServer();
        }, (new Node($stream, $this->log))->hello());

        $this->log->notice('caching connection info from HELLO', [
            'key'   => $key,
            'nodes' => $this->nodeUrls,
            'ttl'   => $this->getCacheTtl(),
        ]);

        $this->cache->set($key, $this->nodeUrls, $this->getCacheTtl());
    }
}
