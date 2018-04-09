<?php
namespace MagicDI\Container;

interface ContainerInterface
{
    public function setParentContainer(ContainerInterface $container);
}