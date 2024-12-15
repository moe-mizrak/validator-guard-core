<?php

namespace MoeMizrak\ValidatorGuardCore;

use BadMethodCallException;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Log;
use MoeMizrak\ValidatorGuardCore\Data\MethodContextData;
use MoeMizrak\ValidatorGuardCore\Exceptions\ValidatorGuardCoreException;
use Monolog\Handler\StreamHandler;
use Monolog\Level;
use Monolog\Logger;
use ReflectionAttribute;
use ReflectionClass;

/**
 * Wrapper class for classes that uses attributes. All classes uses attributes for validation gets bind-ed to this class.
 *
 * Class ValidatorGuardCore
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

        $_attributeMethodPairs = $this->_retrieveAttributeMethodPairs($this->_class, $_methodName);

        $_methodResponse = null;
        $_isCalled = false;

        // If method attributes have after key which is not empty, then execute method
        if (Arr::has($_attributeMethodPairs, self::_AFTER) && ! empty($_attributeMethodPairs[self::_AFTER])){
            // Call the original method and store the response
            $_methodResponse = $this->_callOriginalMethod($_methodName, ...$_params);
            // set isCalled to true in order to prevent calling it twice.
            $_isCalled = true;
        }

        $_methodContextData = new MethodContextData(
            methodResponse: $_methodResponse ?? null, // Set to method response or null in cases of undefined or null
            params: $_params, // Set to method parameters
        );

        $this->_validate($_attributeMethodPairs, $_methodName, $_methodContextData);

        return $_isCalled ? $_methodResponse : $this->_callOriginalMethod($_methodName, ...$_params);
    }

    /**
     * Call the original method on the class
     *
     * @param string $_methodName
     * @param ...$_params
     *
     * @return mixed
     */
    private function _callOriginalMethod(string $_methodName, ...$_params): mixed
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
            // Invoke the handle method of the attribute class, if fails throw/log descriptive exception
            if (! $this->_invokeHandle($_attribute, $_methodContextData)) {
                // Message that will be thrown and/or logged
                $message = "{$_attribute} handle method failed for the {$_methodName}";
                // Throw the exception if enabled and/or log it if enabled
                $this->_handleValidationFailure($message);
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
        $_handleMethodName = self::_HANDLE_METHOD_NAME;
        $_attributeInstance = $_attribute->newInstance();
        $_reflectionClass = new ReflectionClass($_attributeInstance);

        // Ensure the attribute has a handle method
        if (! $_reflectionClass->hasMethod($_handleMethodName)) {
            // Message that will be thrown and/or logged
            $message = "Attribute {$_attribute} does not exist or missing {$_handleMethodName} method !";
            // Throw the exception if enabled and/or log it if enabled
            $this->_handleValidationFailure($message);
        }

        // get the handle method of the attribute class from the reflection
        $_handle = $_reflectionClass->getMethod($_handleMethodName);

        // we make the call with attribute instance, not with reflection class instance
        return $_handle->invoke($_attributeInstance, $_methodContextData);
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

    /**
     * Handle the failure by throwing exceptions and/or logging the validation message.
     *
     * @param string $message
     *
     * @return void
     * @throws ValidatorGuardCoreException
     */
    private function _handleValidationFailure(string $message): void
    {
        /*
         * Handle logging exceptions related conditions
         */
        if (config('validator-guard-core.log_exceptions', false)) {
            $this->_logException($message);
        }

        /*
         * Handle throwing exceptions related conditions
         */
        if (config('validator-guard-core.throw_exceptions', true)) {
            throw new ValidatorGuardCoreException($message);
        }
    }

    /**
     * Log exception considering the environment.
     *
     * @param $message
     *
     * @return void
     */
    private function _logException($message): void
    {
        if (app()->environment('testing')) {
            $packageLogPath = storage_path('logs/laravel.log');
            // Ensure the logs directory exists
            $logDir = dirname($packageLogPath);
            // Create the directory and set permissions
            if (! is_dir($logDir)) {
                mkdir($logDir, 0777, true);
            }
            // Create Monolog/Logger with a simple descriptive name
            $logger = new Logger('validator-guard-core');
            // Push the error handler to the logger
            $logger->pushHandler(new StreamHandler($packageLogPath, Level::Error));
            // Log exception message
            $logger->error($message);
        } else {
            // Get the configured channel for logging
            $channel = config('validator-guard-core.log_channel', 'stack'); // Fallback to 'stack' channel
            // Log exception message using Laravel Log facade if it exists
            Log::channel($channel)->error($message);
        }
    }
}