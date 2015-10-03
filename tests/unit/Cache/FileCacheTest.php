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
        if (is_file($this->file)) {
            unlink($this->file);
        }
    }


    /**
     * @expectedException \RuntimeException
     */
    public function testFileNotFound()
    {
       new FileCache('/foo/bar/baz/bam');
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
        $this->assertTrue($c->set($key, ['bar' => ['baz']], 1));
        $this->assertEquals(['bar' => ['baz']], $c->get($key));
        $this->assertTrue($c->expires($key) <= time() + 1);
        sleep(2);
        $this->assertNull($c->get($key));
    }
}