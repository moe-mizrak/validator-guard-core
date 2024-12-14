<?php

namespace MoeMizrak\ValidatorGuardCore;

use BadMethodCallException;
use Illuminate\Support\Arr;
use MoeMizrak\ValidatorGuardCore\Data\MethodContextData;
use MoeMizrak\ValidatorGuardCore\Exceptions\ValidatorGuardCoreException;
use ReflectionAttribute;
use ReflectionClass;

/**
 * Wrapper class for classes that uses attributes. All classes uses attributes for validation gets bind-ed to this class.
 *
 * Class ValidatorGuardCore
 */
class ValidatorGuardCore extends ValidatorGuardCoreAPI
{
    /**
     * Dynamically handle method calls.
     * This magic method triggered when a non-existed method is called.
     * And here we check wrapped original class methods and validate them based on the attributes assigned to them.
     *
     * @param string $_methodName
     * @param array $_params
     *
     * @return mixed
     * @throws ValidatorGuardCoreException
     * @throws \ReflectionException
     */
    public function __call(string $_methodName, array $_params): mixed
    {
        // Check if the method exists in the original service and call it
        if (! method_exists($this->_class, $_methodName)) {
            // If the method does not exist, throw an error
            throw new BadMethodCallException("Method {$_methodName} does not exist !");
        }

        $_attributeMethodPairs = $this->_retrieveAttributeMethodPairs($this->_class, $_methodName);

        $_methodResponse = null;
        $_isCalled = false;
        $after = 'after'; // After method execution key

        // If method attributes have after key which is not empty, then execute method
        if (Arr::has($_attributeMethodPairs, $after) && ! empty($_attributeMethodPairs[$after])){
            // Call the original method and store the response
            $_methodResponse = $this->_callOriginalFunction($_methodName, ...$_params);
            // set isCalled to true in order to prevent calling it twice.
            $_isCalled = true;
        }

        $_methodContextData = new MethodContextData(
            methodResponse: $_methodResponse ?? null, // Set to method response or null in cases of undefined or null
            params: $_params, // Set to method parameters
        );

        $this->_validate($_attributeMethodPairs, $_methodName, $_methodContextData);

        return $_isCalled ? $_methodResponse : $this->_callOriginalFunction($_methodName, ...$_params);
    }

    /**
     * Call the original method on the class
     *
     * @param string $_methodName
     * @param ...$_params
     *
     * @return mixed
     */
    private function _callOriginalFunction(string $_methodName, ...$_params): mixed
    {
        return $this->_class->{$_methodName}(...$_params);
    }

    /**
     * Validate method attributes by invoking handle.
     *
     * @param array $_attributeMethodPairs
     * @param string $_methodName
     * @param MethodContextData $_methodContextData
     *
     * @return void
     * @throws ValidatorGuardCoreException
     * @throws \ReflectionException
     */
    private function _validate(array $_attributeMethodPairs, string $_methodName, MethodContextData $_methodContextData): void
    {
        // We don't need before and after keys, we run all attribute validations in loop
        $_flattenedMethodPairs = Arr::flatten($_attributeMethodPairs);

        foreach ($_flattenedMethodPairs as $_attribute) {
            // Invoke the handle method of the attribute class, if fails throw descriptive exception
            if (! $this->_invokeHandle($_attribute, $_methodContextData)) {
                throw new ValidatorGuardCoreException("{$_attribute} handle method failed for the {$_methodName}");
            }
        }
    }

    /**
     * Invoke the handle method for the attribute.
     *
     * @param ReflectionAttribute|null $_attribute
     * @param MethodContextData $_methodContextData
     *
     * @return mixed
     * @throws ValidatorGuardCoreException
     * @throws \ReflectionException
     */

    private function _invokeHandle(?ReflectionAttribute $_attribute, MethodContextData $_methodContextData): mixed
    {
        $_handleMethodName = 'handle';
        $_attributeInstance = $_attribute->newInstance();

        $_reflectionClass = new ReflectionClass($_attributeInstance);

        // Ensure the attribute has a handle method
        if ($_reflectionClass->hasMethod($_handleMethodName)) {
            // get the handle method of the attribute class from the reflection
            $_handle = $_reflectionClass->getMethod($_handleMethodName);

            // we make the call with attribute instance, not with reflection class instance
            return $_handle->invoke($_attributeInstance, $_methodContextData);
        }

        // Throw exception if the handle method is missing
        throw new ValidatorGuardCoreException("Attribute {$_attribute} does not exist or missing {$_handleMethodName} method !");
    }

    /**
     * Retrieve the attributes assigned to the method
     *
     * @param object $_class
     * @param string $_methodName
     *
     * @return array
     * @throws \ReflectionException
     */
    private function _retrieveAttributeMethodPairs(object $_class, string $_methodName): array
    {
        // Get the attributes from config
        $_attributeList = config('validator-guard-core.attributes');
        // Create reflection class from the given class
        $_reflection = new ReflectionClass($_class);
        // Create reflection methods of
        $_method = $_reflection->getMethod($_methodName);

        $_attributes = [];

        /*
         * Assign attributes to before/after groups based on the attribute list on config,
         * basically if an attribute is used for method, we add them to attributes along with before/after category
         */
        foreach ($_method->getAttributes() as $_attribute) {
            $_attributeName = $_attribute->getName();

            if (in_array($_attributeName, $_attributeList['before'], true)) {
                $_attributes['before'][] = $_attribute;
            }

            if (in_array($_attributeName, $_attributeList['after'], true)) {
                $_attributes['after'][] = $_attribute;
            }
        }

        return $_attributes;
    }
}