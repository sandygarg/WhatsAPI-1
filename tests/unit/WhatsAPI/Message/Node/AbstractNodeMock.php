<?php

namespace WhatsAPI\Message\Node;

class AbstractNodeMock extends AbstractNode
{

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }
}
