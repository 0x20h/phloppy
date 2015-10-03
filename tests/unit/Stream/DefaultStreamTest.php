<?php

namespace Phloppy\Stream;

class DefaultStreamTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @expectedException \Phloppy\Stream\StreamException
     * @expectedExceptionMessage unable to write to stream
     */
    public function testWriteFails()
    {
        $m = $this->getMockBuilder('\Phloppy\Stream\DefaultStream')
            ->enableOriginalConstructor()
            ->setMethods(['streamWrite'])
            ->setConstructorArgs(['foo:0'])
            ->getMock();

        $m->expects($this->once())
            ->method('streamWrite')
            ->willReturn(1);

        $m->write('foo');
    }


    /**
     * @expectedException \Phloppy\Stream\StreamException
     * @expectedExceptionMessage stream_get_contents returned false
     */
    public function testReadBytesFails()
    {
        $m = $this->getMockBuilder('\Phloppy\Stream\DefaultStream')
            ->enableOriginalConstructor()
            ->setMethods(['streamReadBytes', 'streamMeta'])
            ->setConstructorArgs(['foo:0'])
            ->getMock();

        $m->expects($this->once())
            ->method('streamReadBytes')
            ->willReturn(false);

        $m->expects($this->once())
            ->method('streamMeta')
            ->willReturn(['timedOut' => false]);

        $m->readBytes();
    }


    /**
     * @expectedException \Phloppy\Stream\StreamException
     * @expectedExceptionMessage stream_get_line returned false
     */
    public function testReadLineFails()
    {
        $m = $this->getMockBuilder('\Phloppy\Stream\DefaultStream')
            ->enableOriginalConstructor()
            ->setMethods(['streamReadLine', 'streamMeta'])
            ->setConstructorArgs(['foo:0'])
            ->getMock();

        $m->expects($this->once())
            ->method('streamReadLine')
            ->willReturn(false);

        $m->expects($this->once())
            ->method('streamMeta')
            ->willReturn(['timedOut' => false]);

        $m->readLine();
    }
}
