<?php

namespace WhatsAPI\Message\Node;

class Success extends AbstractNode
{

    /**
     * @return string
     */
    public function getName()
    {
        return 'success';
    }

    /**
     * @return int
     */
    public function getTimestamp()
    {
        return (int) $this->getAttribute('t');
    }

    /**
     * @return string
     */
    public function getKind()
    {
        return $this->getAttribute('kind');
    }

    /**
     * @return string
     */
    public function getStatus()
    {
        return $this->getAttribute('status');
    }

    /**
     * @return int
     */
    public function getCreation()
    {
        return (int) $this->getAttribute('creation');
    }

    /**
     * @return int
     */
    public function getExpiration()
    {
        return (int) $this->getAttribute('expiration');
    }
}
