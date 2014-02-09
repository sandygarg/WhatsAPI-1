<?php

namespace WhatsAPI\Message\Action;

use WhatsAPI\Message\Node\NodeFactory;

abstract class AbstractAction implements ActionInterface
{
    /**
     * @var NodeFactory
     */
    protected $nodeFactory;

    /**
     * @param  NodeFactory $nodeFactory
     * @return $this
     */
    public function setNodeFactory(NodeFactory $nodeFactory)
    {
        $this->nodeFactory = $nodeFactory;

        return $this;
    }

    /**
     * @return NodeFactory
     */
    public function getNodeFactory()
    {
        if (!$this->nodeFactory) {
            $this->nodeFactory = new NodeFactory();
        }

        return $this->nodeFactory;
    }
}
