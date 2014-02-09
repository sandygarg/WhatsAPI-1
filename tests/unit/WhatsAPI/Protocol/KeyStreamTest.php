<?php

namespace WhatsAPI\Protocol;

use \Mockery as m;

class KeyStreamTest extends \WhatsAPITestCase
{
    /**
     * @var KeyStream
     */
    protected $object;

    public function setUp()
    {
        $key = 'key';
        $rc4 = new RC4($key, 256);
        $this->object = new KeyStream($rc4, $key);
    }

    public function testDecodeMethod()
    {
        $data = hex2bin('7a1fb3b37abe7247af7363e53ddc8aa5964575bc');
        $res = $this->object->decode($data, 0, strlen($data));
        $this->assertEquals(hex2bin('74010c8479d25a70baf5eca410fa0ea7'), $res);
    }

    public function testEncodeMethod()
    {
        $data = hex2bin('7a1fb3b37abe7247af7363e53ddc8aa5964575bc');
        $res = $this->object->encode($data, 0, strlen($data), true);
        $this->assertEquals(hex2bin('74a0cd70ac1f4bd2285a05e4bb63f1be49b3bc3253905f81'), $res);

        $res = $this->object->encode($data, 0, strlen($data), false);
        $this->assertEquals(hex2bin('40c184c7cf5618aed61518179561a4ce4597f49190aee5c5'), $res);
    }

    protected function tearDown()
    {
        m::close();
    }

}
