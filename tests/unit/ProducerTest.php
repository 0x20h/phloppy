<?php

namespace Disque;

class ProducerTest extends \PHPUnit_Framework_TestCase {

    public function testAddJob()
    {

        $id = 'DI87152bb815a18ae31dc0d8be1bcd1a6ff2cbbd050002SQ';
        $mock = $this->getMock('\Disque\Stream');
        $mock->expects($this->once())->method('write')->willReturn($mock);
        $mock->expects($this->once())->method('readLine')->willReturn('+' . $id);

        $p = new Producer($mock);
        $job = $p->addJob('test', Job::create(['body' => '42']));
        $this->assertEquals($id, $job->getId());
    }


    /**
     * @expectedException \Disque\Exception\CommandException
     * @expectedExceptionMessage ERR Foo
     */
    public function testAddJobWithInvalidReplicate()
    {
        $mock = $this->getMock('\Disque\Stream');
        $mock->expects($this->any())->method('write')->willReturn($mock);
        $mock->expects($this->any())->method('readLine')->willReturn("-ERR Foo");
        $p = new Producer($mock);
        $p->addJob('test', Job::create(['body' => 42]));
    }

    public function testReplicationTimeout()
    {
        $mock = $this->getMock('\Disque\Stream');
        $p = new Producer($mock);
        $rand = rand();
        $p->setReplicationTimeout($rand);
        $this->assertEquals($rand, $p->getReplicationTimeout());
    }

    public function testReplicationFactor()
    {
        $mock = $this->getMock('\Disque\Stream');
        $p = new Producer($mock);
        $rand = rand();
        $p->setReplicationFactor($rand);
        $this->assertEquals($rand, $p->getReplicationFactor());
    }
}
