<?php

namespace Plugins\Controller;


class DefaultValue
{
    /**
     * @param array $array
     * @param $key
     * @param null $default
     * @return null
     */
    public function __invoke(array $array = array(), $key, $default = null)
    {
        $result = $default;
        if (isset($array[$key])) {
            $result = $array[$key];
        }
        return $result;
    }
}