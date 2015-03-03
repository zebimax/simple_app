<?php

namespace Filters\Values;


class EqFilter extends AbstractValueFilter
{
    function filterValue($filterValue)
    {
        return $filterValue == $this->value;
    }
}