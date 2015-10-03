<?php

namespace Phloppy\Stream;

class StreamExceptionTest extends \PHPUnit_Framework_TestCase
{

    public function testGetOperation()
    {
        $e = new StreamException(StreamException::OP_READ, 'foo');
        $this->assertEquals(StreamException::OP_READ, $e->getOperation());
    }
}
