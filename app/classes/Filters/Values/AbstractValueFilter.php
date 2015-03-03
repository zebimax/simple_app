<?php

namespace Filters\Values;


abstract class AbstractValueFilter implements ValueFilterInterface
{
    protected $value;
    protected $key;

    public function __construct($key)
    {
        $this->key = $key;
    }

    /**
     * @param $value
     * @return $this
     */
    public function setValue($value)
    {
        $this->value = $value;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getKey()
    {
        return $this->key;
    }

    /**
     * @param $key
     * @return $this
     */
    public function setKey($key)
    {
        $this->key = $key;
        return $this;
    }

    abstract function filterValue($filterValue);
}