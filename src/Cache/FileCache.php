<?php

namespace Phloppy\Cache;

class FileCache extends MemoryCache implements CacheInterface
{
    protected $file;

    public function __construct($file)
    {
        $this->file = fopen($file, 'w');

        if (!$this->file) {
            throw new \RuntimeException('unable to open cache file '.$file);
        }

        fclose($this->file);
    }

    /**
     * Retrieve the nodes under the given key.
     *
     * @param string $key
     *
     * @return string[]
     */
    public function get($key)
    {
        $this->read();
        return parent::get($key);
    }


    /**
     * Cache the given Nodes.
     *
     * @param string $key
     * @param string[] $nodes
     * @param int    $ttl
     *
     * @return bool
     */
    public function set($key, array $nodes, $ttl)
    {
        $this->read();
        parent::set($key, $nodes, $ttl);
        $this->write();
    }

    private function read()
    {
        flock($this->file, LOCK_SH);
        $s = fgets($this->file);
        flock($this->file, LOCK_UN);

        $this->records = unserialize($s);
    }


    private function write()
    {
        flock($this->file, LOCK_EX);
        fwrite($this->file, serialize($this->records));
        flock($this->file, LOCK_UN);
    }
}