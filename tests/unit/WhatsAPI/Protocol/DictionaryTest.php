<?php

namespace WhatsAPI\Protocol;

use \Mockery as m;

class DictionaryTest extends \WhatsAPITestCase
{
    /**
     * @var Dictionary
     */
    protected $object;

    public function setUp()
    {
        $this->object = new Dictionary();
    }

    public function testCountMethod()
    {
        $this->assertEquals(249, count($this->object));
    }

    public function testOffsetExistsMethod()
    {
        $this->assertTrue(isset($this->object[0]));
        $this->assertFalse(isset($this->object[249]));
    }

    public function testOffsetGetMethod()
    {
        $this->assertEquals(0, $this->object[1]);
    }

    public function testOffsetSetMethod()
    {
        $this->object[1] = 1;
        $this->assertEquals(1, $this->object[1]);
        $this->object[] = 249;
        $this->assertTrue(isset($this->object[249]));
        $this->assertEquals(249, $this->object[249]);
    }

    public function testOffsetUnsetMethod()
    {
        unset($this->object[0]);
        $this->assertFalse(isset($this->object[0]));
    }

    public function testGetIteratorMethod()
    {
        /** @var \Iterator $iterator */
        $iterator = $this->object->getIterator();
        $this->assertInstanceOf('\Iterator', $iterator);
    }

}
