<?php

namespace MoeMizrak\ValidatorGuardCore;

use BadMethodCallException;
use Illuminate\Support\Arr;
use MoeMizrak\ValidatorGuardCore\Data\MethodContextData;
use MoeMizrak\ValidatorGuardCore\Exceptions\ValidatorGuardCoreException;

/**
 * Wrapper class for classes that uses attributes. All classes uses attributes for validation gets bind-ed to this class.
 *
 * @class ValidatorGuardCore
 */
final readonly class ValidatorGuardCore extends ValidatorGuardCoreAPI
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
    final public function __call(string $_methodName, array $_params): mixed
    {
        // Check if the method exists in the original service and call it
        if (! method_exists($this->_class, $_methodName)) {
            // If the method does not exist, throw an error
            throw new BadMethodCallException("Method {$_methodName} does not exist !");
        }

        // Retrieve the attribute pairs for the given method name and class name.
        $_attributePairs = $this->_retrieveAttributePairs($this->_class, $_methodName);

        $_methodResult = null;
        $_isCalled = false;

        // If method attributes have after key which is not empty, then execute method
        if (Arr::has($_attributePairs, self::_AFTER) && ! empty($_attributePairs[self::_AFTER])){
            // Call the original method and store the result
            $_methodResult = $this->_callOriginalMethod($_methodName, ...$_params);
            // set isCalled to true in order to prevent calling it twice.
            $_isCalled = true;
        }

        // Set method context including method result and parameters
        $_methodContextData = new MethodContextData(
            methodResult: $_methodResult ?? null,
            params: $_params,
        );

        // Validate the method based on the attributes
        $this->_validate($_attributePairs, $_methodName, $_methodContextData);

        return $_isCalled ? $_methodResult : $this->_callOriginalMethod($_methodName, ...$_params);
    }
}