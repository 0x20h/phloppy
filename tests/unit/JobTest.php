<?php

namespace Phloppy;

class JobTest extends \PHPUnit_Framework_TestCase {

    public function testToString()
    {
        $job = Job::create(['body' => 42, 'id' => 'foo']);
        $this->assertEquals('foo', (string) $job);
    }
}
