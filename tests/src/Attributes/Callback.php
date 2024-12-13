<?php

namespace MoeMizrak\ValidatorGuardCore\Tests\src\Attributes;

use MoeMizrak\ValidatorGuardCore\Contracts\ValidationAttributeInterface;
use MoeMizrak\ValidatorGuardCore\Data\MethodContextData;
use ReflectionClass;

/**
 * Callback attribute for making a call to given class method including parameters and comparing result.
 */
#[\Attribute(\Attribute::TARGET_PROPERTY | \Attribute::TARGET_METHOD | \Attribute::IS_REPEATABLE)]
class Callback implements ValidationAttributeInterface
{
    public function __construct(
        public string $className,
        public string $methodName,
        public array $params
    ) {
    }

    /**
     * @param MethodContextData $methodContextData
     *
     * @return bool
     * @throws \ReflectionException
     */
    public function handle(MethodContextData $methodContextData): bool
    {
        // Get a reflection class instance
        $class = new ReflectionClass($this->className);
        // Instantiate an object of the class
        $instance = $class->newInstance();
        // Get a reflection method instance
        $method = $class->getMethod($this->methodName);
        // Call the method with the provided parameters
        $result =  $method->invoke($instance, $this->params[0]);

        return $result > 100;
    }
}