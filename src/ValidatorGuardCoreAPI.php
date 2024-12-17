<?php

namespace MoeMizrak\ValidatorGuardCore;

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
 * ValidatorGuardCoreAPI is responsible for encapsulating the ValidatorGuardCore class
 *
 * @class Abstract ValidatorGuardCoreAPI
 */
readonly abstract class ValidatorGuardCoreAPI
{
    /*
     *  After execution key
     */
    protected const _AFTER = 'after';

    /*
     * Before execution key
     */
    protected const _BEFORE = 'before';

    /*
     * Name of the handle method defined in attributes
     */
    protected const _HANDLE_METHOD_NAME = 'handle';

    /**
     * ValidatorGuardCoreAPI constructor.
     *
     * @param object $_class
     */
    public function __construct(protected object $_class) {}

    /**
     * Call the original method on the class
     *
     * @param string $_methodName
     * @param ...$_params
     *
     * @return mixed
     */
    protected function _callOriginalMethod(string $_methodName, ...$_params): mixed
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
    protected function _validate(
        array $_attributePairs,
        string $_methodName,
        MethodContextData $_methodContextData
    ): void {
        // We don't need before and after keys, we run all attribute validations in loop
        $_flattenedAttributePairs = Arr::flatten($_attributePairs);

        foreach ($_flattenedAttributePairs as $_attribute) {
            // Invoke the handle method of the attribute class, if fails throw/log descriptive exception
            if (! $this->_invokeHandle($_attribute, $_methodContextData)) {
                // Message that will be thrown and/or logged
                $_message = "{$_attribute} handle method failed for the {$_methodName}";
                // Throw the exception if enabled and/or log it if enabled
                $this->_handleValidationFailure($_message);
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
            $_message = "Attribute {$_attribute} does not exist or missing {$_handleMethodName} method !";
            // Throw the exception if enabled and/or log it if enabled
            $this->_handleValidationFailure($_message);
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
    protected function _retrieveAttributePairs(object $_class, string $_methodName): array
    {
        // Get the attributes from config
        $_attributeList = config('validator-guard-core.attributes');
        // Create reflection class from the given class
        $_reflection = new ReflectionClass($_class);
        // Create reflection methods of
        $_method = $_reflection->getMethod($_methodName);

        $_result = [];

        // Add method attributes
        $this->_addAttributeArrayIfExists($_attributeList, $_method->getAttributes(), $_result);

        foreach ($_method->getParameters() as $_parameter) {
            // Add parameter attributes
            $this->_addAttributeArrayIfExists($_attributeList, $_parameter->getAttributes(), $_result);
        }

        return $_result;
    }

    /**
     * Assign method attributes or parameter attributes to before/after groups based on the attribute list on config.
     *
     * @param $_attributeList
     * @param $_attributes
     * @param $_result
     *
     * @return void
     */
    private function _addAttributeArrayIfExists($_attributeList, $_attributes, &$_result): void
    {
        foreach ($_attributes as $_attribute) {
            $_name = $_attribute->getName();

            // Add attribute to result array if exists in before group in attribute list
            if (in_array($_name, $_attributeList[self::_BEFORE], true)) {
                $_result[self::_BEFORE][] = $_attribute;
            }
            // Add attribute to result array if exists in after group in attribute list
            if (in_array($_name, $_attributeList[self::_AFTER], true)) {
                $_result[self::_AFTER][] = $_attribute;
            }
        }
    }

    /**
     * Handle the failure by throwing exceptions and/or logging the validation message.
     *
     * @param string $_message
     *
     * @return void
     * @throws ValidatorGuardCoreException
     */
    private function _handleValidationFailure(string $_message): void
    {
        /*
         * Handle logging exceptions related conditions
         */
        if (config('validator-guard-core.log_exceptions', false)) {
            $this->_logException($_message);
        }

        /*
         * Handle throwing exceptions related conditions
         */
        if (config('validator-guard-core.throw_exceptions', true)) {
            throw new ValidatorGuardCoreException($_message);
        }
    }

    /**
     * Log exception considering the environment.
     *
     * @param $_message
     *
     * @return void
     */
    private function _logException($_message): void
    {
        if (app()->environment('testing')) {
            $_packageLogPath = storage_path('logs/laravel.log');
            // Ensure the logs directory exists
            $_logDir = dirname($_packageLogPath);
            // Create the directory and set permissions
            if (! is_dir($_logDir)) {
                mkdir($_logDir, 0777, true);
            }
            // Create Monolog/Logger with a simple descriptive name
            $_logger = new Logger('validator-guard-core');
            // Push the error handler to the logger
            $_logger->pushHandler(new StreamHandler($_packageLogPath, Level::Error));
            // Log exception message
            $_logger->error($_message);
        } else {
            // Get the configured channel for logging
            $_channel = config('validator-guard-core.log_channel', 'stack');
            // Log exception message using Laravel Log facade if it exists
            Log::channel($_channel)->error($_message);
        }
    }
}