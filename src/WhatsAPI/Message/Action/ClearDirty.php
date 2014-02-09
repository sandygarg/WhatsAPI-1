<?php

namespace WhatsAPI\Message\Action;

use WhatsAPI\Client\Client;

/**
 * Class ClearDirty
 * Clears the "dirty" status on your account
 *
 * @package WhatsAPI\Message\Action
 */
class ClearDirty extends AbstractAction
{

    /**
     * @var array
     */
    protected $categories = array();

    /**
     * @param array $categories
     */
    public function __construct(array $categories)
    {
        $this->setCategories($categories);
    }

    /**
     * @param  array $categories
     * @return $this
     */
    public function setCategories($categories)
    {
        $this->categories = $categories;

        return $this;
    }

    /**
     * @return array
     */
    public function getCategories()
    {
        return $this->categories;
    }

    /**
     * @param Client $client
     */
    public function send(Client $client)
    {
        $client->sendNode($this->getNode($client));
    }

    /**
     * @return \WhatsAPI\Message\Node\NodeInterface
     */
    public function getNode()
    {
        $catNodes = array();
        foreach ($this->getCategories() as $category) {
            $catNodes[] = array(
                'name'       => 'category',
                'attributes' => array('name' => $category)
            );
        }
        $cleanNode = array(
            'name'       => 'clean',
            'attributes' => array("xmlns" => "urn:xmpp:whatsapp:dirty"),
            $catNodes
        );
        $node = $this->getNodeFactory()->fromArray(
            array(
                'name'       => 'iq',
                'attributes' => array(
                    'type' => 'set',
                    'to'   => 's.whatsapp.net'
                ),
                'children'   => array($cleanNode)
            )
        );

        return $node;
    }
}
