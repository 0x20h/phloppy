<?php

namespace Phloppy\Cache;

class FileCache extends MemoryCache implements CacheInterface
{

    protected $file;


    public function __construct($file)
    {
        $this->file = @fopen($file, 'c+');

        if (!$this->file) {
            throw new \RuntimeException('unable to open cache file '.$file);
        }
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
     * @param string   $key
     * @param string[] $nodes
     * @param int      $ttl TTL in seconds
     *
     * @return bool
     */
    public function set($key, array $nodes, $ttl)
    {
        $this->read();
        parent::set($key, $nodes, $ttl);

        return $this->write();
    }


    /**
     * Read cache from disk.
     */
    private function read()
    {
        flock($this->file, LOCK_SH);
        rewind($this->file);
        $s = fgets($this->file);
        flock($this->file, LOCK_UN);

        $this->records = unserialize($s);
    }


    /**
     * Write cache to disk.
     *
     * @return bool
     */
    private function write()
    {
        $cache = serialize($this->records);
        flock($this->file, LOCK_EX);
        rewind($this->file);
        ftruncate($this->file, 0);
        $bytes = fwrite($this->file, $cache);
        flock($this->file, LOCK_UN);

        return $bytes === strlen($cache);
    }


    /**
     * Close file handle.
     */
    function __destruct()
    {
        if (is_resource($this->file)) {
            fclose($this->file);
        }
    }

}