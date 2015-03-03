<?php

namespace Plugins\View;


class StringByCondition
{
    /**
     * @param $condition
     * @param $value
     * @param $altValue
     * @return mixed
     */
    public function __invoke($condition, $value, $altValue)
    {
        return $condition ?
            (is_callable($value) ? $value() : $value)
            : (is_callable($altValue) ? $altValue() : $altValue);
    }
}