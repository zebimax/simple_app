<?php

namespace Form\Component;


abstract class AbstractFormComponent
{
    const COMPONENT_NAME = 'name';
    protected $name;

    abstract public function make();

    /**
     * @return mixed
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param mixed $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }
}