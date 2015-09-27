<?php

namespace Phloppy;

class JobTest extends \PHPUnit_Framework_TestCase {

    public function testToString()
    {
        $job = Job::create(['queue' => 'bar', 'body' => 42, 'id' => 'foo', 'ttl' => 34]);
        $this->assertEquals('foo', (string) $job);
        $this->assertEquals('bar', $job->getQueue());
        $this->assertEquals(42, $job->getBody());
    }


    public function testOriginNode()
    {
        $job = Job::create(['id' => 'DIcf53204d8bd1f9e6b4312c07121f0e5228b3400a003cSQ', 'body' => '']);
        $this->assertEquals('f53204d8bd1', $job->getOriginNode());

        $job = Job::create(['body' => '']);
        $this->assertNull($job->getOriginNode());
    }
}
