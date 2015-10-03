<?php

namespace Phloppy\Cache;

class MemoryCacheTest extends \PHPUnit_Framework_TestCase
{
    public function testCacheSet()
    {
        $c = new MemoryCache();
        $this->assertTrue($c->set('foo', ['bar'], 5000));
    }


    public function testGet()
    {
        $key = uniqid();
        $c = new MemoryCache();
        $this->assertTrue($c->set($key, ['bar' => ['baz']], 5000));
        $this->assertEquals(['bar' => ['baz']], $c->get($key));

        $this->assertNull($c->get('foobar'));
    }


    public function testExpire()
    {
        $key = uniqid();
        $c = new MemoryCache();
        $this->assertTrue($c->set($key, ['bar' => ['baz']], 1));
        $this->assertEquals(['bar' => ['baz']], $c->get($key));
        $this->assertTrue($c->expires($key) <= time() + 1);
        sleep(2);
        $this->assertNull($c->get($key));

        $this->assertEquals(0, $c->expires('foobar'));
    }
}