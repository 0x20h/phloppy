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

        if ($record['expire'] < microtime(true)) {
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
     * @param int      $ttl TTL in microseconds.
     *
     * @return bool
     */
    public function set($key, array $nodes, $ttl)
    {
        $this->records[$key] = ['nodes' => $nodes, 'expire' => microtime(true) + $ttl];
        return true;
    }
}