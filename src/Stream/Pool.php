<?php

namespace Phloppy\Stream;

use Phloppy\Stream;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Phloppy\Exception\ConnectException;

/**
 * Phloppy Node Pool.
 */
class Pool implements Stream {
    /**
     * @var array
     */
    private $servers;

    /**
     * @var LoggerInterface
     */
    private $log;

    /**
     * @var Stream
     */
    private $connected;

    /**
     * @param array $servers
     * @param LoggerInterface $log
     * @throws ConnectException
     */
    public function __construct(array $servers = array(), LoggerInterface $log = null)
    {
        $this->servers = $servers;

        if (!$log) {
            $log = new NullLogger();
        }

        $this->log = $log;
        $this->connected = $this->connect();
    }


    /**
     * @return array
     */
    public function getServers() {
        return $this->servers;
    }


    /**
     * @return Stream
     */
    public function getActiveServer()
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
     * Connect to a random node in the node list.
     *
     * @return Stream Stream to a connected node.
     *
     * @throws ConnectException
     */
    private function connect() {
        $nodes = $this->servers;

        while(count($nodes)) {
          // pick random server
          $idx = rand(0, count($nodes) - 1);

          try {
              return new DefaultStream($nodes[$idx], $this->log);
          } catch (ConnectException $e) {
             $this->log->warning($e->getMessage());
          }

          // remove the selected server from the list
          array_splice($nodes, $idx, 1);
        }

        throw new ConnectException('unable to connect to any of [' . implode(',', $this->servers) .']');
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
     * @param int $maxlen
     * @return string The response.
     */
    public function readBytes($maxlen = null)
    {
        return $this->connected->readBytes($maxlen);
    }

    /**
     * Read
     * @param $msg
     * @return Stream the instance.
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
}
