<?php

namespace WhatsAPI\Message\Node;

use WhatsAPI\Exception\InvalidArgumentException;

class NodeFactory
{

    /**
     * @param  array                    $data
     * @return Node
     * @throws InvalidArgumentException
     */
    public function fromArray(array $data)
    {
        if (empty($data['name'])) {
            throw new InvalidArgumentException("Key 'name' is required");
        }
        $node = null;
        switch ($data['name']) {
            case 'challenge':
                $node = Challenge::fromArray($data, $this);
                break;

            case 'success':
                $node = Success::fromArray($data, $this);
                break;

            default:
                $node = Node::fromArray($data, $this);
                break;
        }

        return $node;
    }
}
