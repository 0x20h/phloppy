<?php

namespace Disque;

class ClientTest extends \PHPUnit_Framework_TestCase {

    public function testAuthOk()
    {
        $mock = $this->getMock('\Disque\Stream');
        $mock->expects($this->once())->method('write')->willReturn($mock);
        $mock->expects($this->once())->method('readLine')->willReturn('+OK');

        $client = new Client($mock);
        $this->assertTrue($client->auth('test'));
    }


    public function testPingPong()
    {
        $mock = $this->getMock('\Disque\Stream');
        $mock->expects($this->once())->method('write')->willReturn($mock);
        $mock->expects($this->once())->method('readLine')->willReturn('+PONG');

        $client = new Client($mock);
        $this->assertTrue($client->ping());
    }
}
