<?php

class WhatsAPITestCase extends PHPUnit_Framework_TestCase
{

    /**
     * Return a method by reflection
     *
     * @param string $className  The class name
     * @param string $methodName The method name
     *
     * @return ReflectionMethod
     */
    protected static function getMethod($className, $methodName)
    {
        $class = new ReflectionClass($className);
        $method = $class->getMethod($methodName);
        $method->setAccessible(true);
        return $method;
    }

}
