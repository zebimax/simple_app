<?php

namespace Plugins\View;

class ConditionTemplate
{
    /**
     * @param $template
     * @param array $values
     * @param $condition
     * @return string
     */
    public function __invoke($template, array $values = array(), $condition)
    {
        return $condition
            ? vsprintf($template, $values)
            : '';
    }
}