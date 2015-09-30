<?php

namespace Phloppy\Cache;

class FileCacheTest extends \PHPUnit_Framework_TestCase
{
    private $file;

    protected function setUp()
    {
        $this->file = '/tmp/'.uniqid('phloppy-unittest');
    }

    protected function tearDown()
    {
        unlink($this->file);
    }

    public function testCacheSet()
    {
        $c = new FileCache($this->file);
        $this->assertTrue($c->set('foo', ['bar'], 5000));
    }


    public function testGet()
    {
        $key = uniqid();
        $c = new FileCache($this->file);
        $this->assertTrue($c->set($key, ['bar' => ['baz']], 5000));
        $this->assertEquals(['bar' => ['baz']], $c->get($key));
    }


    public function testExpire()
    {
        $key = uniqid();
        $c = new FileCache($this->file);
        $this->assertTrue($c->set($key, ['bar' => ['baz']], 500));
        $this->assertEquals(['bar' => ['baz']], $c->get($key));
        usleep(550000);
        $this->assertNull($c->get($key));
    }
}