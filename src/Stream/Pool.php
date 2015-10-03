<?php

namespace Phloppy\Stream;

use Phloppy\Exception\ConnectException;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

/**
 * Phloppy Node Pool.
 */
class Pool implements StreamInterface
{

    /**
     * @var array
     */
    protected $nodeUrls;

    /**
     * @var LoggerInterface
     */
    protected $log;

    /**
     * @var StreamInterface
     */
    protected $connected;


    /**
     * @param array                $nodeUrls
     * @param LoggerInterface|null $log
     *
     * @throws ConnectException
     */
    public function __construct(array $nodeUrls = array(), LoggerInterface $log = null)
    {
        $this->nodeUrls = $nodeUrls;

        if (!$log) {
            $log = new NullLogger();
        }

        $this->log = $log;
        $this->connected = $this->connect($nodeUrls);
    }


    /**
     * @return array
     */
    public function getNodeUrls()
    {
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
        $this->connected = $this->connect($this->getNodeUrls());

        return true;
    }


    /**
     * Connect to a random node in the node list.
     *
     * @return DefaultStream Stream to a connected node.
     *
     * @throws ConnectException
     */
    public function connect()
    {
        $nodes = $this->nodeUrls;

        while (count($nodes)) {
            // pick random server
            $idx = rand(0, count($nodes) - 1);

            try {
                $stream = new DefaultStream($nodes[$idx], $this->log);
                $stream->connect();

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
     *
     * @return string The response.
     */
    public function readBytes($maxlen = null)
    {
        return $this->connected->readBytes($maxlen);
    }


    /**
     * @param string   $msg
     * @param int|null $len
     *
     * @return StreamInterface the Stream instance.
     */
    public function write($msg, $len = null)
    {
        $this->connected->write($msg, $len);

        return $this;
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
}
