<?php

namespace Phloppy\Client;

class ServerTest extends \PHPUnit_Framework_TestCase {

    public function testAuthOk()
    {
        $mock = $this->getMock('\Phloppy\Stream\StreamInterface');
        $mock->expects($this->once())->method('write')->willReturn($mock);
        $mock->expects($this->once())->method('readLine')->willReturn('+OK');

        $client = new Server($mock);
        $this->assertTrue($client->auth('test'));
    }


    public function testPingPong()
    {
        $mock = $this->getMock('\Phloppy\Stream\StreamInterface');
        $mock->expects($this->once())->method('write')->willReturn($mock);
        $mock->expects($this->once())->method('readLine')->willReturn('+PONG');

        $client = new Server($mock);
        $this->assertTrue($client->ping());
    }
}
