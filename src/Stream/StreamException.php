<?php

namespace Phloppy\Stream;

use Phloppy\Exception;

class StreamException extends Exception {

    const OP_READ  = 0;
    const OP_WRITE = 1;

    private $operation;

    public function __construct($operation, $message = '', $code = null, \Exception $prev = null)
    {
        parent::__construct($message, $code, $prev);
        $this->operation = $operation;
    }


    /**
     * @return mixed
     */
    public function getOperation()
    {
        return $this->operation;
    }
}