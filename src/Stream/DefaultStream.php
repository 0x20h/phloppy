<?php

namespace Phloppy\Stream;

use Phloppy\Stream;
use Phloppy\Exception\ConnectException;
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

        $this->log    = $log;
        $this->server = $server;

        $this->connect();
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
        $errstr  = '';
        $errno   = 0;
        $stream  = @stream_socket_client($this->server, $errno, $errstr, $timeout);

        if (!$stream) {
            $this->log->warning('unable to connect to '. $this->server. ': '. $errstr);
            throw new ConnectException('Unable to connect to server '. $this->server .'. '. $errstr, $errno);
        }

        $this->log->info('connected to ' . $this->server);
        $this->stream = $stream;
        return true;
    }


    public function close()
    {
        stream_socket_shutdown($this->stream, STREAM_SHUT_RDWR);
        $this->stream = null;
        return true;
    }


    /**
     * Read a line from the stream
     *
     * @return string
     */
    public function readLine()
    {
        $line = stream_get_line($this->stream, 65536, "\r\n");
        $this->log->debug('readLine()', [$line]);
        return $line;
    }

    /**
     * Read bytes off from the stream.
     *
     * @param int|null $maxlen
     * @return string
     */
    public function readBytes($maxlen = null)
    {
        $out = stream_get_contents($this->stream, $maxlen);
        $this->log->debug('readBytes()', [$maxlen, $out]);
        return $out;
    }

    /**
     * Write bytes to the stream.
     *
     * @param string $msg
     * @param int|null $len
     * @return DefaultStream This instance.
     */
    public function write($msg, $len = null)
    {
        $bytes = fwrite($this->stream, $msg);
        $this->log->debug('write()', ['written' => $bytes, 'len' => $len, 'msg' => $msg]);
        assert($bytes == $len ? $len : strlen($msg));

        return $this;
    }

    /**
     * Check if the stream is connected.
     *
     * @return boolean True if the stream is connected.
     */
    public function isConnected()
    {
        return is_resource($this->stream);
    }
}
