<?php
namespace MagicDI\Container;

use InvalidArgumentException;
use Exception;

class Container implements ContainerInterface
{
    protected $properties;

    /** @var array */
    protected $childContainers;

    /** @var ContainerInterface */
    protected $parentContainer;

    /**
     * @param ContainerInterface $container
     * @return $this
     */
    public function setParentContainer(ContainerInterface $container)
    {
        $this->parentContainer = $container;

        return $this;
    }

    /**
     * @param $name
     * @param $value
     * @return Container
     * @throws Exception
     */
    public function __set($name, $value)
    {
        $this->validateString('name', $name);

        if (is_callable($value)) {
            return $this->addCallable($name, $value);
        }

        if ($value instanceof ContainerInterface) {
            $this->childContainers[] = $name;
        }

        $this->properties[$name] = $value;

        return $this;
    }

    /**
     * @param $name
     * @return bool
     */
    public function __isset($name)
    {
        return (bool)$this->hasProperty($name);
    }

    /**
     * @param $name
     * @return mixed
     * @throws InvalidArgumentException
     */
    public function __get($name)
    {
        $hasProperty = $this->hasProperty($name);

        if ($hasProperty === null) {
            throw new InvalidArgumentException("Property {$name} not found!");
        }

        if ($hasProperty !== $this) {
            return $hasProperty->$name;
        }

        $output = $this->properties[$name];

        if (!is_callable($output)) {
            return $output;
        }

        $this->properties[$name] = call_user_func($output, $this->parentContainer);

        return $this->properties[$name];
    }

    /**
     * @param $name
     * @return ContainerInterface|null
     */
    protected function hasProperty($name)
    {
        if (isset($this->properties[$name])) {
            return $this;
        }

        if (!is_array($this->childContainers)) {
            return null;
        }

        foreach ($this->childContainers as $container) {
            if (isset($this->properties[$container]->$name)) {
                return $this->properties[$container];
            }
        }

        return null;
    }

    /**
     * @param $name
     * @param $callable
     * @return Container
     * @throws Exception
     */
    protected function addCallable($name, $callable)
    {
        $hasProperty = $this->hasProperty($name);

        if ($hasProperty !== null) {
            $class = get_class($hasProperty);
            throw new Exception("Property {$name} already defined in {$class}");
        }

        $this->properties[$name] = $callable;

        return $this;
    }

    /**
     * @param string $name
     * @param mixed $value
     * @throws InvalidArgumentException
     */
    protected function validateString($name, $value)
    {
        if (!is_string($value)) {
            $type = gettype($value);
            throw new InvalidArgumentException("Property {$name} must be a string! Given value: {$value} and type: {$type}");
        }
    }
}