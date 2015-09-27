<?php

namespace Phloppy\Client;

use Phloppy\Job;

class ClusterTest extends \PHPUnit_Framework_TestCase {

    public function testMeetWithOkResponse()
    {
        $mock = $this->getMock('\Phloppy\Stream\StreamInterface');

        $mock->expects($this->any())
            ->method('write')
            ->willReturn($mock);

        $mock->expects($this->any())
            ->method('readLine')
            ->willReturn('+OK');

        $mock->expects($this->once())
            ->method('getNodeUrl')
            ->willReturn('a');

        $cluster = new Cluster($mock);
        $urls = ['tcp://a:1234','tcp://b:1234','https://c:12'];

        $result = $cluster->meet($urls);
        $this->assertEquals($urls, $result);
    }


    public function testMeetWithCommandException()
    {
        $mock = $this->getMock('\Phloppy\Stream\StreamInterface');

        $mock->expects($this->any())
            ->method('write')
            ->willReturn($mock);

        $mock->expects($this->at(6))
            ->method('readLine')
            ->willReturn('-ERR Invalid node address specified: c:12');

        $mock->expects($this->any())
            ->method('readLine')
            ->willReturn('+OK');

        $mock->expects($this->once())
            ->method('getNodeUrl')
            ->willReturn('a');

        $cluster = new Cluster($mock);
        $urls = ['tcp://a:1234','tcp://b:1234','https://c:12'];

        $result = $cluster->meet($urls);
        $this->assertEquals(array_slice($urls, 0, 2), $result);
    }
}
