<?php

namespace Disque;

class RespTest extends \PHPUnit_Framework_TestCase {

    public function testReadString()
    {
        $mock = $this->getMock('\Disque\Stream');
        $mock->expects($this->any())->method('readLine')->willReturn('+FOO');
        $this->assertEquals('FOO', Resp::deserialize($mock));
    }

    public function testReadInt()
    {
        $mock = $this->getMock('\Disque\Stream');
        $mock->expects($this->any())->method('readLine')->willReturn(':42');
        $this->assertEquals(42, Resp::deserialize($mock));
    }

    /**
     * @expectedException \Disque\Exception\CommandException
     * @expectedExceptionMessage ERR Foo
     */
    public function testErrResponseThrowsCommandException()
    {
        $mock = $this->getMock('\Disque\Stream');
        $mock->expects($this->any())->method('readLine')->willReturn("-ERR Foo");
        Resp::deserialize($mock);
    }

    /**
     * @expectedException \RuntimeException
     * @expectedExceptionMessage unhandled protocol response: /FOO
     */
    public function testInvalidReponseThrowsRuntimeException()
    {
        $mock = $this->getMock('\Disque\Stream');
        $mock->expects($this->any())->method('readLine')->willReturn("/FOO");
        Resp::deserialize($mock);
    }
}
