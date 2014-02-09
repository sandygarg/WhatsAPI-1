<?php

namespace WhatsAPI\Message\Node;

interface NodeInterface
{

    public static function fromArray(array $data, NodeFactory $factory);
    public function getName();
    public function getAttributes();
    public function getData();
    public function getChildren();
    public function hasChild($name);
    public function getAttribute($name);
    public function setAttribute($name, $value);
}
