<?php

namespace Phloppy\Client\Queue;

use Phloppy\Exception\CommandException;

class QScanIteratorTest extends \PHPUnit_Framework_TestCase {

    public function testCount()
    {
        $client = new QScanIterator($this->getMock('\Phloppy\Stream'));
        $this->assertSame($client, $client->setCount(100));
        $this->assertSame(100, $client->getCount());
    }

    public function testRate()
    {
        $client = new QScanIterator($this->getMock('\Phloppy\Stream'));
        $this->assertSame($client, $client->setRate(100));
        $this->assertSame(100, $client->getRate());
    }

    public function testMin()
    {
        $client = new QScanIterator($this->getMock('\Phloppy\Stream'));
        $this->assertSame($client, $client->setMin(100));
        $this->assertSame(100, $client->getMin());
    }

    public function testMax()
    {
        $client = new QScanIterator($this->getMock('\Phloppy\Stream'));
        $this->assertSame($client, $client->setMax(100));
        $this->assertSame(100, $client->getMax());
    }

    public function testScan()
    {
        $mock = $this->getMock('\Phloppy\Stream');
        $mock->expects($this->once())
            ->method('write')
            ->with("*5\r\n$5\r\nQSCAN\r\n$1\r\n0\r\n$8\r\nBUSYLOOP\r\n$10\r\nIMPORTRATE\r\n$2\r\n42\r\n")
            ->willReturn($mock);

        $mock->expects($this->once())
            ->method('readLine')
            ->willReturn('-ERR Fo');

        $iterator = new QScanIterator($mock);
        $iterator->setCount(0)
            ->setRate(42);

        try {
            $iterator->valid();
        } catch (CommandException $e) {
        }
    }

    public function testScanBusyloop()
    {
        $mock = $this->getMockBuilder('\Phloppy\Client\Queue\QScanIterator')
            ->setConstructorArgs([$this->getMock('\Phloppy\Stream')])
            ->setMethods(['send'])
            ->getMock();

        $mock->expects($this->once())
            ->method('send')
            ->with(['QSCAN', 0, 'COUNT', 5, 'IMPORTRATE', 53])
            ->willReturn([0, ['a','b','c']]);

        $mock->setCount(5);
        $mock->setRate(53);
        $mock->valid();
        $this->assertSame('a', $mock->current());
        $mock->next();
        $this->assertSame('b', $mock->current());
        $mock->next();
        $this->assertSame('c', $mock->current());
        $mock->next();
        $this->assertFalse($mock->valid());
    }
}
