<?php
namespace MagicDI\Container;

use InvalidArgumentException;

class Factory extends Container
{
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

        return call_user_func($output, $this->parentContainer);
    }
}