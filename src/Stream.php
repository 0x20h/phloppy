<?php

namespace Phloppy;

interface Stream {

    /**
     * Close the stream.
     *
     * @return bool True on success.
     */
    public function close();

    /**
     * Check if the stream is connected.
     *
     * @return boolean True if the stream is connected.
     */
    public function isConnected();

    /**
     * Read a line from the stream
     *
     * @return string
     */
    public function readLine();

    /**
     * Read bytes off from the stream.
     *
     * @param int $maxlen
     * @return string The response.
     */
    public function readBytes($maxlen = null);


    /**
     * Read
     * @param $msg
     * @return Stream the instance.
     */
    public function write($msg, $len = null);
}
