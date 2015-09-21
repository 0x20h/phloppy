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
        $connectTimeout = 1;
        $errstr  = '';
        $errno   = 0;

        $stream  = @stream_socket_client($this->server, $errno, $errstr, $connectTimeout);

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
        $this->log->info('closing connection: '. $this->server);
        stream_socket_shutdown($this->stream, STREAM_SHUT_RDWR);
        $this->stream = null;
        $this->log->info('connection closed: '. $this->server);
        return true;
    }


    /**
     * Read a line from the stream.
     *
     * @return string
     * @throws StreamException If an error occurs while reading from the stream.
     */
    public function readLine()
    {
        $this->log->debug('going to read a line from the stream');
        $line = fgets($this->stream, 8192);

        if (false === $line) {
            $meta = stream_get_meta_data($this->stream);
            $this->log->warning('fgets returned false', $meta);
            throw new StreamException('stream_get_line returned false');
        }

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
        $this->log->debug('calling readbytes()', array('maxlen' => $maxlen));
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
