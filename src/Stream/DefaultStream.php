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
    private $nodeUrl;

    /**
     * @var LoggerInterface
     */
    private $log;

    /**
     * @var resource
     */
    private $stream;


    public function __construct($nodeUrl, LoggerInterface $log = null)
    {
        if (!$log) {
            $log = new NullLogger();
        }

        $this->log = $log;
        $this->nodeUrl = $nodeUrl;
    }


    /**
     * Connect the stream.
     *
     * @return boolean True if connection could be established.
     *
     * @throws ConnectException
     */
    public function connect()
    {
        $connectTimeout = 1;
        $errstr = '';
        $errno = 0;
        $stream = @stream_socket_client($this->nodeUrl, $errno, $errstr, $connectTimeout);

        if (!$stream) {
            $this->log->warning('unable to connect to '.$this->nodeUrl.': '.$errstr);
            throw new ConnectException('Unable to connect to resource '.$this->nodeUrl.'. '.$errstr, $errno);
        }

        $this->log->info('connected to '.$this->nodeUrl);
        $this->stream = $stream;

        return true;
    }


    public function close()
    {
        $this->log->info('closing connection: '.$this->nodeUrl);
        stream_socket_shutdown($this->stream, STREAM_SHUT_RDWR);
        $this->stream = null;
        $this->log->info('connection closed: '.$this->nodeUrl);

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
        $line = $this->streamReadLine($this->stream);

        if (false === $line) {
            $meta = $this->streamMeta($this->stream);
            $this->log->warning('fgets returned false', $meta);
            throw new StreamException(StreamException::OP_READ, 'stream_get_line returned false');
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
     * @throws StreamException If an error occurs while reading from the stream.
     */
    public function readBytes($maxlen = null)
    {
        $this->log->debug('calling readbytes()', array('maxlen' => $maxlen));
        $out = $this->streamReadBytes($this->stream, $maxlen);
        $this->log->debug('readBytes()', [$maxlen, $out]);

        if (false === $out) {
            $meta = $this->streamMeta($this->stream);
            $this->log->warning('stream_get_contents returned false', $meta);
            throw new StreamException(StreamException::OP_READ, 'stream_get_contents returned false');
        }

        return $out;
    }


    /**
     * Write bytes to the stream.
     *
     * @param string   $msg
     * @param int|null $len
     *
     * @return DefaultStream this instance.
     * @throws StreamException
     */
    public function write($msg, $len = null)
    {
        if (!$len) {
            $len = strlen($msg);
        }

        $bytes = $this->streamWrite($this->stream, $msg);
        $this->log->debug('write()', ['written' => $bytes, 'len' => $len, 'msg' => $msg]);

        if ($bytes !== $len) {
            throw new StreamException(StreamException::OP_WRITE, 'unable to write to stream');
        }

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
     * return the internal node url.
     *
     * @return string
     */
    public function getNodeUrl()
    {
        return $this->nodeUrl;
    }


    /**
     * Internal wrapper method, mainly for testing.
     *
     * @param resource $stream
     * @param string   $msg
     *
     * @return int
     */
    protected function streamWrite($stream, $msg)
    {
        return fwrite($this->stream, $msg);
    }


    /**
     * Internal wrapper method, mainly for testing.
     *
     * @param resource $stream
     * @param          $len
     *
     * @return string
     */
    protected function streamReadBytes($stream, $len)
    {
        return stream_get_contents($stream, $len);
    }


    /**
     * Internal wrapper method, mainly for testing.
     *
     * @param resource $stream
     *
     * @return string
     */
    protected function streamReadLine($stream)
    {
        return fgets($stream, 8192);
    }


    /**
     * Internal wrapper method, mainly for testing.
     *
     * @param $stream
     *
     * @return array
     */
    protected function streamMeta($stream)
    {
        return stream_get_meta_data($stream);
    }
}
