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
}
