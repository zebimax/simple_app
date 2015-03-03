<?php

namespace Filters\Values;


class LikeFilter extends AbstractValueFilter
{
    function filterValue($filterValue)
    {
        return strpos($filterValue, $this->value) !== false;
    }
}