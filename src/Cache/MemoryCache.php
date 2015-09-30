<?php

namespace Phloppy\Cache;

use Phloppy\NodeInfo;

class MemoryCache implements CacheInterface
{
    /**
     * @var array
     */
    protected $records = [];

    /**
     * Retrieve the nodes under the given key.
     *
     * @param string $key
     *
     * @return string[]
     */
    public function get($key)
    {
        if (!isset($this->records[$key])) {
            return null;
        }

        $record = $this->records[$key];

        if ($record['expire'] < time()) {
            unset($this->records[$key]);
            return null;
        }

        return $record['nodes'];
    }


    /**
     * Cache the given Nodes.
     *
     * @param string   $key
     * @param string[] $nodes
     * @param int      $ttl TTL in seconds.
     *
     * @return bool
     */
    public function set($key, array $nodes, $ttl)
    {
        $this->records[$key] = ['nodes' => $nodes, 'expire' => time() + $ttl];
        return true;
    }


    /**
     * Return seconds left until the key expires.
     *
     * @param string $key
     *
     * @return int The number of seconds the key is valid. 0 if expired or unknown.
     */
    public function expires($key)
    {
        if (!isset($this->records[$key])) {
            return 0;
        }

        return max(0, $this->records[$key]['expire'] - time());
    }
}