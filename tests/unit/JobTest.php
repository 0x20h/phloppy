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
        $job = Job::create(['id' => 'D-dcb833cf-8YL1NT17e9+wsA/09NqxscQI-05a1', 'body' => '']);
        $this->assertEquals('f53204d8bd1', $job->getOriginNode());

        $job = Job::create(['body' => '']);
        $this->assertNull($job->getOriginNode());
    }
}
