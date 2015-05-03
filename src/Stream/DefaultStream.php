<?php

namespace Disque\Stream;

use Disque\Stream;
use Disque\Exception\ConnectException;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

class DefaultStream implements Stream {

    /**
     * Server information.
     *
     * @var string
     */
    private $server;

    /**
     * @var LoggerInterface
     */
    private $log;

    /**
     * @var resource
     */
    private $stream;

    public function __construct($server, LoggerInterface $log = null) {
        if (!$log) {
            $log = new NullLogger();
        }

        $this->log = $log;
        $this->server = $server;
        $this->connect($server);
    }

    /**
     * Connect the stream.
     *
     * @return boolean True if connection could be established.
     *
     * @throws ConnectException
     */
    private function connect()
    {
        $timeout = 3;
        $stream = @stream_socket_client($this->server, $errno, $errstr, $timeout);

        if (!$stream) {
            throw new ConnectException('Unable to connect to any servers '. $this->server .': '. $errstr, $errno);
        }

        $this->log->info('connected to ' . $this->server);
        $this->stream = $stream;
        return true;
    }


    public function close()
    {
        stream_socket_shutdown($this->stream, STREAM_SHUT_RDWR);
        return $this->connect($this->server);
    }


    /**
     * Read a line from the stream
     *
     * @return string
     */
    public function readLine()
    {
        $line = stream_get_line($this->stream, 65536, "\r\n");
        $this->log->debug("readLine()", [$line]);
        return $line;
    }

    /**
     * Read bytes off from the stream.
     *
     * @param int $maxlen
     * @return string
     */
    public function readBytes($maxlen = null)
    {
        $out = stream_get_contents($this->stream, $maxlen);
        $this->log->debug("readBytes()", [$maxlen, $out]);
        return $out;
    }

    /**
     * Write bytes to the stream.
     *
     * @param string $msg
     * @param int $len
     * @return Stream this instance.
     */
    public function write($msg, $len = null)
    {
        $bytes = fwrite($this->stream, $msg);
        $this->log->debug("write()", ['written' => $bytes, 'len' => $len, 'msg' => $msg]);
        return $this;
    }
}
