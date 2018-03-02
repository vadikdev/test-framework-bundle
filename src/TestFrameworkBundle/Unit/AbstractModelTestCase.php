<?php

namespace Vadiktok\TestFrameworkBundle\Unit;

abstract class AbstractModelTestCase extends UnitTestCase
{
    /**
     * @return string
     */
    abstract protected function getModelClass() : string;

    /**
     * List of field => value pairs to test setters and getters.
     *
     * @return array
     */
    protected function getFields() : array
    {
        return [];
    }

    protected function makeDecodedJson($data)
    {
        return json_decode(json_encode($data));
    }

    /**
     * Get all arguments to pass into constructor
     *
     * @return array
     */
    protected function getModelArguments() : array
    {
        return [];
    }

    /**
     * @return mixed
     */
    protected function getModel()
    {
        $modelClass = $this->getModelClass();
        return new $modelClass(...$this->getModelArguments());
    }

    public function testModelClassExists()
    {
        $entityClass = $this->getModelClass();
        $this->assertClassExists($entityClass);
    }

    public function testSettersAndGetters()
    {
        $model = $this->getModel();

        // Here we get all entity fields and assert that both setters and getters methods exist.
        $fields = array_merge($this->listProperties(), $this->getFields());
        foreach ($fields as $field => $value) {
            // Some DTOs use construct to pass arguments into model.
            if ([] === $this->getModelArguments()) {
                $setter = sprintf('set%s', ucfirst($field));
                $this->assertMethodExists($model, $setter);
                $this->assertInstanceOf($this->getModelClass(), $model->{$setter}($value));
            }

            $result = $this->getModelValue($model, $field);

            $this->assertSame($value, $result);
        }
    }

    /**
     * Get all fields and assert their default values.
     */
    public function testDefaultValues()
    {
        $model = $this->getModel();

        // Assert that all fields will return null
        $fields = array_merge($this->listProperties(), $this->getFields());
        $defaultValues = array_map(function ($value) { return null; }, $fields);

        // Exclude some of them if required
        foreach ($this->excludeDefaultValues() as $exclude) {
            if (array_key_exists($exclude, $defaultValues)) {
                unset($defaultValues[$exclude]);
            }
        }

        // Merge non-nullable default values and use them as base.
        // All other fields are considered as nullable
        foreach (array_merge($defaultValues, $this->getDefaultValues()) as $field => $defaultValue) {
            $result = $this->getModelValue($model, $field);

            // In case of default values we can not use assertSame as objects are not same.
            // But they
            if (is_object($defaultValue)) {
                $this->assertEquals($defaultValue, $result);
            } else {
                $this->assertSame($defaultValue, $result);
            }
        }
    }

    /**
     * Get the list of constant => value, ensure constant exists and assert it's value
     */
    public function testConstants()
    {
        foreach ($this->getConstants() as $name => $value) {
            $name = sprintf('%s::%s', $this->getModelClass(), $name);
            $this->assertTrue(
                defined($name),
                sprintf('Failed asserting that constant %s is defined', $name)
            );

            $this->assertSame(
                constant($name),
                $value,
                sprintf('Failed asserting that constant %s has value "%s"', $name, $value)
            );
        }
    }

    /**
     * Array of constant name => expected value
     *
     * @return array
     */
    protected function getConstants() : array
    {
        return [];
    }

    /**
     * Array of field name => default value
     *
     * @return array
     */
    protected function getDefaultValues() : array
    {
        return ([] === $this->getModelArguments())
            ? []
            : array_merge($this->listProperties(), $this->getFields());
    }

    /**
     * @param $model
     * @param $property
     * @return mixed
     */
    protected function getModelValue($model, $property)
    {
        $is = sprintf('get%s', ucfirst($property));
        $getter = method_exists($model, $is)
            ? $is
            : sprintf('is%s', ucfirst($property));

        $this->assertMethodExists($model, $getter);
        return $model->{$getter}();
    }

    /**
     * Return the list of fields to exclude from default value asserting.
     *
     * @return array
     */
    protected function excludeDefaultValues() : array
    {
        return [];
    }

    /**
     * Get all properties defined in class.
     * To override them either override this method or override getFields method.
     * In final case we will take getFields result as base array of fields and merge the result of listProperties into.
     *
     * @return array
     */
    protected function listProperties()
    {
        $reflect = new \ReflectionClass($this->getModelClass());
        $properties = $reflect->getProperties();

        $keys = array_map(function (\ReflectionProperty $property) {
            return $property->getName();
        }, $properties);

        $values = array_map(function (\ReflectionProperty $property) {
            return 'test ' . $property->getName();
        }, $properties);

        return array_combine($keys, $values);
    }
}
