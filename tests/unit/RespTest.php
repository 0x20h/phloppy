<?php

namespace Phloppy;

class RespTest extends \PHPUnit_Framework_TestCase {

    public function testReadString()
    {
        $mock = $this->getMock('\Phloppy\Stream');
        $mock->expects($this->any())->method('readLine')->willReturn('+FOO');
        $this->assertEquals('FOO', RespUtils::deserialize($mock));
    }

    public function testReadInt()
    {
        $mock = $this->getMock('\Phloppy\Stream');
        $mock->expects($this->any())->method('readLine')->willReturn(':42');
        $this->assertEquals(42, RespUtils::deserialize($mock));
    }

    /**
     * @expectedException \Phloppy\Exception\CommandException
     * @expectedExceptionMessage ERR Foo
     */
    public function testErrResponseThrowsCommandException()
    {
        $mock = $this->getMock('\Phloppy\Stream');
        $mock->expects($this->any())->method('readLine')->willReturn("-ERR Foo");
        RespUtils::deserialize($mock);
    }

    /**
     * @expectedException \RuntimeException
     * @expectedExceptionMessage unhandled protocol response: /FOO
     */
    public function testInvalidReponseThrowsRuntimeException()
    {
        $mock = $this->getMock('\Phloppy\Stream');
        $mock->expects($this->any())->method('readLine')->willReturn("/FOO");
        RespUtils::deserialize($mock);
    }
}
