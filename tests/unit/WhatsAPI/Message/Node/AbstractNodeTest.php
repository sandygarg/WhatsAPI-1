<?php

namespace WhatsAPI\Message\Node;

use \Mockery as m;

class AbstractNodeTest extends \WhatsAPITestCase
{
    /**
     * @var AbstractNodeMock
     */
    protected $object;

    public function setUp()
    {
        $this->object = new AbstractNodeMock();
    }

    public function testGetNodeFactory()
    {
        $this->assertInstanceOf('\WhatsAPI\Message\Node\NodeFactory', $this->object->getNodeFactory());
    }

    public function testFromArrayMethod()
    {
        $nodeMock = m::mock('\WhatsAPI\Message\Node\Node');
        $nodeFactoryMock = m::mock('\WhatsAPI\Message\Node\NodeFactory');
        $nodeFactoryMock->shouldReceive('fromArray')->once()->andReturn($nodeMock);

        $data = array(
            'name' => 'nodename',
            'data' => 'mydata',
            'attributes' => array('foo' => 'baz'),
            'children' => array(
                array(
                    'name' => 'iq'
                )
            )
        );
        $object = AbstractNodeMock::fromArray($data, $nodeFactoryMock);
        $this->assertEquals($data['name'], $object->getName());
        $this->assertEquals($data['data'], $object->getData());
        $this->assertEquals($data['attributes'], $object->getAttributes());
        $this->assertEquals(array($nodeMock), $object->getChildren());
    }

    /**
     * @expectedException \WhatsAPI\Exception\InvalidArgumentException
     */
    public function testFromArrayMethodException()
    {
        $nodeFactoryMock = m::mock('\WhatsAPI\Message\Node\NodeFactory');

        $data = array(
            'name' => 'mynode',
            'children' => array(
                new \stdClass()
            )
        );

        AbstractNodeMock::fromArray($data, $nodeFactoryMock);
    }

    public function testSettersAndGetters()
    {
        $this->object->setName('nodename');
        $this->assertEquals('nodename', $this->object->getName());

        $this->object->setData('testdata');
        $this->assertEquals('testdata', $this->object->getData());

        $attributes = array(
            'first' => 'foo',
            'second' => 'foo2'
        );
        $this->object->setAttributes($attributes);
        $this->assertEquals($attributes, $this->object->getAttributes());
        $this->assertEquals('foo', $this->object->getAttribute('first'));

        $nodeMock = m::mock('\WhatsAPI\Message\Node\Node');
        $nodeMock->shouldReceive('getName')->andReturn('iq');
        $nodeFactoryMock = m::mock('\WhatsAPI\Message\Node\NodeFactory');
        $nodeFactoryMock->shouldReceive('fromArray')->once()->andReturn($nodeMock);

        $this->object->setNodeFactory($nodeFactoryMock);
        $this->object->setChildren(
            array(
                array(
                    'name' => 'iq'
                )
            )
        );

        $this->assertEquals(array($nodeMock), $this->object->getChildren());
        $this->assertTrue($this->object->hasChild('iq'));
        $this->assertFalse($this->object->hasChild('iq2'));
    }

    /**
     * @expectedException \WhatsAPI\Exception\InvalidArgumentException
     */
    public function testSetNameException()
    {
        $this->object->setName('name1');
        // Trying to change it
        $this->object->setName('name2');
    }

    /**
     * @expectedException \WhatsAPI\Exception\InvalidArgumentException
     */
    public function testAddChildException()
    {
        $this->object->addChild(new \stdClass());
    }

    public function testToArrayMethod()
    {
        $attributes = array(
            'foo' => 'baz'
        );
        $childArray = array(
            'name' => 'child'
        );
        $childMock = m::mock('\WhatsAPI\Message\Node\Node');
        $childMock->shouldReceive('toArray')->once()->andReturn($childArray);

        $this->object->setName('nodename');
        $this->object->setData('nodedata');
        $this->object->setAttributes($attributes);
        $this->object->setChildren(array($childMock));

        $array = $this->object->toArray();
        $this->assertTrue(is_array($array));
        $this->assertArrayHasKey('name', $array);
        $this->assertArrayHasKey('data', $array);
        $this->assertArrayHasKey('attributes', $array);
        $this->assertArrayHasKey('children', $array);

        $this->assertEquals('nodename', $array['name']);
        $this->assertEquals('nodedata', $array['data']);
        $this->assertEquals($attributes, $array['attributes']);
        $this->assertEquals(array($childArray), $array['children']);
    }

    public function testToStringMethod()
    {
        $attributes = array(
            'foo' => 'baz',
            'baz' => 'foo'
        );
        $childString = '<child></child>';
        $childMock = m::mock('\WhatsAPI\Message\Node\Node');
        $childMock->shouldReceive('toString')->once()->andReturn($childString);

        $this->object->setName('nodename');
        $this->object->setAttributes($attributes);
        $this->object->setChildren(array($childMock));

        $string = $this->object->toString();
        $nodeString = <<<TXT
<nodename foo="baz" baz="foo">
  <child></child>
</nodename>
TXT;

        $this->assertEquals($nodeString, $string);

    }

    protected function tearDown()
    {
        m::close();
    }

}
