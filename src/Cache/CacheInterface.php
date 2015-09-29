<?php

namespace Phloppy\Cache;

interface CacheInterface
{
    /**
     * Retrieve the nodes under the given key.
     *
     * @param string $key
     * @return string[]
     */
    public function get($key);


    /**
     * Cache the given Nodes.
     *
     * @param string  $key
     * @param string[]  $nodes
     * @param int     $ttl
     *
     * @return bool
     */
    public function set($key, array $nodes, $ttl);
}