<?php

namespace Phloppy\Stream;

use Phloppy\Exception\ConnectException;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

class DefaultStream implements StreamInterface
{

    /**
     * The remote socket url.
     *
     * @var string
     */
    private $streamUrl;

    /**
     * @var LoggerInterface
     */
    private $log;

    /**
     * @var resource
     */
    private $stream;


    public function __construct($streamUrl, LoggerInterface $log = null)
    {
        if (!$log) {
            $log = new NullLogger();
        }

        $this->log = $log;
        $this->streamUrl = $streamUrl;

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
        $errstr = '';
        $errno = 0;
        $stream = @stream_socket_client($this->streamUrl, $errno, $errstr, $connectTimeout);

        if (!$stream) {
            $this->log->warning('unable to connect to '.$this->streamUrl.': '.$errstr);
            throw new ConnectException('Unable to connect to resource '.$this->streamUrl.'. '.$errstr, $errno);
        }

        $this->log->info('connected to '.$this->streamUrl);
        $this->stream = $stream;

        return true;
    }


    public function close()
    {
        $this->log->info('closing connection: '.$this->streamUrl);
        stream_socket_shutdown($this->stream, STREAM_SHUT_RDWR);
        $this->stream = null;
        $this->log->info('connection closed: '.$this->streamUrl);

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
     *
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
     * @param string   $msg
     * @param int|null $len
     *
     * @return DefaultStream this instance.
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


    /**
     * return the internal stream url.
     *
     * @return string
     */
    public function getStreamUrl()
    {
        return $this->streamUrl;
    }
}
