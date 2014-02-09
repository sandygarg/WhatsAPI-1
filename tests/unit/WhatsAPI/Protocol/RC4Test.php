<?php

namespace WhatsAPI\Protocol;

use \Mockery as m;

class RC4Test extends \WhatsAPITestCase
{
    /**
     * @var RC4
     */
    protected $object;

    public function setUp()
    {
        $this->object = new RC4('key', 256);
    }

    public function testChipherMethod()
    {
        $rc4 = new Rc4(hex2bin('7a1fb3b37abe7247af7363e53ddc8aa5964575bc'), 256);
        $res = $rc4->cipher(hex2bin('f80790cb1276fc0654686f6d61734dfc1570726573656e63652d313339313938353938312d32'), 0, 38);
        $this->assertEquals(hex2bin('35be4eb77f495de73d952cc9c910041450114e12efbe1422ac85e021e15d9e26ca7689fea70b'), $res);
    }

    protected function tearDown()
    {
        m::close();
    }

}
