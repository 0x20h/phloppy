<?php

namespace Phloppy\Cache;

interface CacheInterface
{
    /**
     * Retrieve the nodes under the given key.
     *
     * @param string $key
     *
     * @return string[]
     */
    public function get($key);


    /**
     * Cache the given Nodes.
     *
     * @param string   $key
     * @param string[] $nodes
     * @param int      $ttl TTL in seconds.
     *
     * @return bool
     */
    public function set($key, array $nodes, $ttl);


    /**
     * Return seconds left until the key expires.
     *
     * @param string $key
     *
     * @return int The number of seconds the key is valid. 0 if expired or unknown.
     */
    public function expires($key);
}