<?php
namespace MagicDI\Container;

use InvalidArgumentException;
use Exception;

class Container implements ContainerInterface
{
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

        $this->$name = $value;

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

        $output = $this->$name;

        if (!is_callable($output)) {
            return $output;
        }

        $this->$name = call_user_func($output($this->parentContainer));

        return $this->$name;
    }

    /**
     * @param $name
     * @return ContainerInterface|null
     */
    protected function hasProperty($name)
    {
        if (isset($this->$name)) {
            return $this;
        }

        foreach ($this->childContainers as $container) {
            if (isset($container->$name)) {
                return $container;
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

        $this->$name = $callable;

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