<?php

namespace Phloppy\Client;

class NodeTest extends \PHPUnit_Framework_TestCase
{

    public function testMeetWithOkResponse()
    {
        $stream = $this->getMock('\Phloppy\Stream\StreamInterface');
        $stream
            ->expects($this->once())
            ->method('getNodeUrl')
            ->willReturn('tcp://127.0.12.1:7712');

        $node = $this->getMockBuilder('\Phloppy\Client\Node')
            ->enableOriginalConstructor()
            ->setConstructorArgs([$stream])
            ->setMethods(['send'])
            ->getMock();

        $node
            ->expects($this->once())
            ->method('send')
            ->willReturn(['1', 'nodesha1', ['nodesha1', '', '7712', '10']]);

        $nodes = $node->hello();

        $this->assertEquals(1, count($nodes));
        $this->assertEquals('tcp://127.0.12.1:7712', $nodes[0]->getServer());
    }
}
