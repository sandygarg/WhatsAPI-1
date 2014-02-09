<?php

namespace WhatsAPI\Message\Node;

class Iq extends AbstractNode
{

    /**
     * @return string
     */
    public function getName()
    {
        return 'iq';
    }

    /**
     * @param  string $type
     * @return $this
     */
    public function setType($type)
    {
        $this->setAttribute('type', $type);

        return $this;
    }

    /**
     * @return string
     */
    public function getType()
    {
        return $this->getAttribute('type');
    }

    /**
     * @param  string $type
     * @return $this
     */
    public function setTo($type)
    {
        $this->setAttribute('to', $type);

        return $this;
    }

    /**
     * @return string
     */
    public function getTo()
    {
        return $this->getAttribute('to');
    }
}
