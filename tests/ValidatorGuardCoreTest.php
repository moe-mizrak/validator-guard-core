<?php

namespace MoeMizrak\ValidatorGuardCore\Tests;

use BadMethodCallException;
use MoeMizrak\ValidatorGuardCore\Exceptions\ValidatorGuardCoreException;
use MoeMizrak\ValidatorGuardCore\Tests\src\Services\ExampleConstructorService;
use MoeMizrak\ValidatorGuardCore\Tests\src\Services\ExampleForBindingService;
use MoeMizrak\ValidatorGuardCore\Tests\src\Services\ExampleService;
use MoeMizrak\ValidatorGuardCore\ValidatorGuardCore;
use PHPUnit\Framework\Attributes\Test;
use ReflectionException;

class ValidatorGuardCoreTest extends TestCase
{
    private ExampleService $example;

    public function setUp(): void
    {
        parent::setUp();

        $this->example = new ExampleService();
    }

    #[Test]
    public function it_tests_comparison_attribute_failure_which_depends_on_the_response_of_the_method()
    {
        /* SETUP */
        $validationGuardCore = new ValidatorGuardCore($this->example);
        $this->expectException(ValidatorGuardCoreException::class);

        /* EXECUTE */
        $validationGuardCore->comparisonFailedMethod();
    }

    #[Test]
    public function it_tests_comparison_attribute_succeed_which_depends_on_the_response_of_the_method()
    {
        /* SETUP */
        $param = 123;
        $validationGuardCore = new ValidatorGuardCore($this->example);

        /* EXECUTE */
        $result = $validationGuardCore->comparisonSucceedMethod($param);

        /* ASSERT */
        $this->assertEquals($result, $param);
    }

    #[Test]
    public function it_tests_non_past_date_attribute_failure_which_depends_on_the_date_param_of_the_method()
    {
        /* SETUP */
        $param = 2;
        $dateParam = '2014-12-12 15:00:00';
        $validationGuardCore = new ValidatorGuardCore($this->example);
        $this->expectException(ValidatorGuardCoreException::class);

        /* EXECUTE */
        $validationGuardCore->nonPastDateMethod($param, $dateParam);
    }

    #[Test]
    public function it_tests_non_past_date_attribute_succeed_which_depends_on_the_date_param_of_the_method()
    {
        /* SETUP */
        $param = 2;
        $dateParam = '2044-12-12 15:00:00';
        $validationGuardCore = new ValidatorGuardCore($this->example);

        /* EXECUTE */
        $result = $validationGuardCore->nonPastDateMethod($param, $dateParam);

        /* ASSERT */
        $this->assertEquals($result, $param . ' / ' . $dateParam);
    }

    #[Test]
    public function it_tests_state_attribute_failure_which_depends_on_class_description_given_inside_attribute()
    {
        /* SETUP */
        $validationGuardCore = new ValidatorGuardCore($this->example);
        $this->expectException(ValidatorGuardCoreException::class);

        /* EXECUTE */
        $validationGuardCore->callbackFailedMethod();
    }

    #[Test]
    public function it_tests_state_attribute_succeed_which_depends_on_class_description_given_inside_attribute()
    {
        /* SETUP */
        $validationGuardCore = new ValidatorGuardCore($this->example);

        /* EXECUTE */
        $result = $validationGuardCore->callbackSucceedMethod();

        /* ASSERT */
        $this->assertEquals($result, 'callbackSucceedMethod response');
    }

    #[Test]
    public function it_throws_exception_when_no_method_is_found_with_given_method_name()
    {
        /* SETUP */
        $validationGuardCore = new ValidatorGuardCore($this->example);
        $this->expectException(BadMethodCallException::class);
        $this->expectExceptionMessage('Method invalidMethod does not exist !');

        /* EXECUTE */
        $validationGuardCore->invalidMethod();
    }

    #[Test]
    public function it_throws_exception_when_no_method_is_found_in_given_state_method()
    {
        /* SETUP */
        $validationGuardCore = new ValidatorGuardCore($this->example);
        $this->expectException(ReflectionException::class);
        $this->expectExceptionMessage('Method MoeMizrak\ValidatorGuardCore\Tests\src\Services\ExampleService::invalidMethod() does not exist');

        /* EXECUTE */
        $validationGuardCore->callbackInvalidMethod();
    }

    #[Test]
    public function it_tests_multiple_attribute_for_a_method_while_first_one_valid_second_one_invalid()
    {
        /* SETUP */
        $param = 40;
        $dateParam = '2004-12-12 15:00:00';
        $validationGuardCore = new ValidatorGuardCore($this->example);
        $this->expectException(ValidatorGuardCoreException::class);

        /* EXECUTE */
        $validationGuardCore->multipleAttributeMethod($param, $dateParam);
    }

    #[Test]
    public function it_tests_multiple_attribute_for_a_method_while_both_attribute_passes_validation()
    {
        /* SETUP */
        $param = 40;
        $dateParam = '2044-12-12 15:00:00';
        $validationGuardCore = new ValidatorGuardCore($this->example);

        /* EXECUTE */
        $result = $validationGuardCore->multipleAttributeMethod($param, $dateParam);

        /* ASSERT */
        $this->assertEquals($result, $param);
    }

    #[Test]
    public function it_tests_binding_classes_through_config_class_list_when_dependency_resolved_from_service_container()
    {
        /* EXECUTE */
        $exampleForBindingService = app(ExampleForBindingService::class);

        /* ASSERT */
        $this->assertInstanceOf(ValidatorGuardCore::class, $exampleForBindingService);
    }

    #[Test]
    public function it_tests_failure_binding_classes_through_when_NOT_dependency_resolved_from_service_container()
    {
        /* EXECUTE */
        $exampleForBindingService = new ExampleForBindingService();

        /* ASSERT */
        $this->assertInstanceOf(ExampleForBindingService::class, $exampleForBindingService);
        $this->assertNotInstanceOf(ValidatorGuardCore::class, $exampleForBindingService);
    }

    #[Test]
    public function it_tests_manuel_resolve_class_where_it_requires_constructor_params()
    {
        /* SETUP */
        $intValue = 11;
        $stringValue = 'my string value';
        $exampleConstructorService = new ExampleConstructorService($intValue, $stringValue);
        $validationGuardCore = new ValidatorGuardCore($exampleConstructorService);

        /* EXECUTE */
        $result = $validationGuardCore->comparisonSucceedMethod(20);

        /* ASSERT */
        $this->assertEquals($result, 20);
    }

    #[Test]
    public function it_tests_helper_valguard()
    {
        /* SETUP */
        $intValue = 11;
        $stringValue = 'my string value';
        $exampleConstructorService = new ExampleConstructorService($intValue, $stringValue);
        $validationGuardCore = valguard($exampleConstructorService);

        /* EXECUTE */

        $result = $validationGuardCore->comparisonSucceedMethod(20);

        /* ASSERT */
        $this->assertEquals($result, 20);
    }
}