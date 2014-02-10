<?php

namespace WhatsAPI\Message\Node;

use WhatsAPI\Exception\InvalidArgumentException;
use Zend\Stdlib\Hydrator\Aggregate\AggregateHydrator;
use Zend\Stdlib\Hydrator\ClassMethods;

abstract class AbstractNode implements NodeInterface
{
    /**
     * @var string
     */
    protected $name;
    /**
     * @var array
     */
    protected $attributes = array();
    /**
     * @var Node[]
     */
    protected $children = array();
    /**
     * @var string
     */
    protected $data;

    /**
     * @var NodeFactory
     */
    protected $nodeFactory;

    /**
     * @param  array                    $data
     * @param  NodeFactory              $factory
     * @return Node
     * @throws InvalidArgumentException
     */
    public static function fromArray(array $data, NodeFactory $factory)
    {
        /** @var NodeInterface $node */
        $node = new static();
        $children = array();
        if (isset($data['children']) && is_array($data['children'])) {
            foreach ($data['children'] as $child) {
                if (is_array($child)) {
                    $child = $factory->fromArray($child);
                }
                if (!$child instanceof NodeInterface) {
                    throw new InvalidArgumentException("Argument passed in children is not an instance of NodeInterface");
                }
                $children[] = $child;
            }
        }
        $data['children'] = $children;
        $hydrator = new AggregateHydrator();
        $hydrator->add(new ClassMethods());
        $hydrator->hydrate($data, $node);

        return $node;
    }

    /**
     * @return string
     */
    abstract public function getName();

    /**
     * @param  string                   $name
     * @return $this
     * @throws InvalidArgumentException
     */
    public function setName($name)
    {
        // Check if we already have a name
        if (null !== $this->getName() && $this->getName() != $name) {
            throw new InvalidArgumentException("Name can't be setted or changed");
        }
        $this->name = $name;

        return $this;
    }

    /**
     * @return array
     */
    public function getAttributes()
    {
        return $this->attributes;
    }

    /**
     * @param  array $attributes
     * @return $this
     */
    public function setAttributes(array $attributes)
    {
        $this->attributes = array();
        foreach ($attributes as $name => $value) {
            $this->setAttribute($name, $value);
        }

        return $this;
    }

    /**
     * @param $name
     * @param $value
     * @return $this
     */
    public function setAttribute($name, $value)
    {
        $this->attributes[$name] = $value;

        return $this;
    }

    /**
     * @return string
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * @param  string $data
     * @return $this
     */
    public function setData($data)
    {
        $this->data = $data;

        return $this;
    }

    /**
     * @return Node[]
     */
    public function getChildren()
    {
        return $this->children;
    }

    /**
     * @param  array $children
     * @return $this
     */
    public function setChildren(array $children)
    {
        $this->children = array();
        foreach ($children as $child) {
            $this->addChild($child);
        }

        return $this;
    }

    /**
     * @param  NodeInterface|array      $child
     * @return $this
     * @throws InvalidArgumentException
     */
    public function addChild($child)
    {
        if (is_array($child)) {
            $child = $this->getNodeFactory()->fromArray($child);
        }
        if (!$child instanceof NodeInterface) {
            throw new InvalidArgumentException("Argument passed is not an instance of NodeInterface");
        }
        $this->children[] = $child;

        return $this;
    }

    /**
     * @param $name
     * @return bool
     */
    public function hasChild($name)
    {
        foreach ($this->getChildren() as $child) {
            if ($child->getName() == $name) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param $name
     * @return string
     */
    public function getAttribute($name)
    {
        return isset($this->attributes[$name]) ? $this->attributes[$name] : null;
    }

    /**
     * @param  \WhatsAPI\Message\Node\NodeFactory $nodeFactory
     * @return $this
     */
    public function setNodeFactory($nodeFactory)
    {
        $this->nodeFactory = $nodeFactory;

        return $this;
    }

    /**
     * @return \WhatsAPI\Message\Node\NodeFactory
     */
    public function getNodeFactory()
    {
        if (!$this->nodeFactory) {
            $this->nodeFactory = new NodeFactory();
        }

        return $this->nodeFactory;
    }

    /**
     * @return array
     */
    public function toArray()
    {
        $children = array();
        foreach ($this->getChildren() as $child) {
            $children[] = $child->toArray();
        }
        $array = array(
            'name' => $this->getName(),
            'attributes' => $this->getAttributes(),
            'data' => $this->getData(),
            'children' => $children
        );

        return $array;
    }

    public function toString()
    {
        return $this->nodeString();
    }

    public function __toString()
    {
        return $this->toString();
    }

    /**
     * @return string
     */
    protected function nodeString()
    {
        //formatters
        $lt = "<";
        $gt = ">";
        $nl = PHP_EOL;

        $indent = "";

        $ret = $indent . $lt . $this->getName();
        foreach ($this->getAttributes() as $key => $value) {
            $ret .= " " . $key . "=\"" . $value . "\"";
        }
        $ret .= $gt;
        if (null != $this->getData() && strlen($this->getData()) > 0) {
            if (strlen($this->getData()) <= 1024) {
                //message
                $ret .= $this->getData();
            } else {
                //raw data
                $ret .= " " . strlen($this->getData()) . " byte data";
            }
        }
        if (count($this->getChildren())) {
            $ret .= $nl;
            $foo = array();
            foreach ($this->getChildren() as $child) {
                $childString = $child->toString();
                $childString =
                    implode("\n",
                        array_map(
                        function ($value) use ($indent) {
                            return $indent . "  " . $value;
                        },
                        explode($nl, $childString)
                    )
                );
                $foo[] = $childString;
            }
            $ret .= implode($nl, $foo);
            $ret .= $nl . $indent;
        }
        $ret .= $lt . "/" . $this->getName() . $gt;

        return $ret;
    }
}
