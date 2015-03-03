<?php

namespace Plugins\View;


class SearchSortOptions
{
    private $options = array(
        'default' => array(
            array('value' => '', 'name' => 'Sorteer op:'),
            array('value' => 'a', 'name' => 'Laagste prijs'),
            array('value' => 'b', 'name' => 'Hoogste prijs'),
            array('value' => 'c', 'name' => 'Naam'),
            array('value' => 'd', 'name' => 'Populariteit')
        ),
        'specials' => array(
            array('value' => '', 'name' => 'Sorteer op:'),
            array('value' => 'a', 'name' => 'Laagste prijs'),
            array('value' => 'b', 'name' => 'Hoogste prijs'),
            array('value' => 'c', 'name' => 'Naam A-Z'),
            array('value' => 'f', 'name' => 'Naam Z-A'),
            array('value' => 'd', 'name' => 'Populariteit'),
            array('value' => 'e', 'name' => 'Aanbieding'),
        )
    );
    public function __invoke($sort, $sortFormat = 'default')
    {
        $sortOptions = isset($this->options[$sortFormat])
            ? $this->options[$sortFormat]
            : $this->options['default'];

        return array_reduce(
            $sortOptions,
            function($carry, $item) use ($sort) {
                $carry .= sprintf(
                    '<option value="%s"%s>%s</option>',
                    $item['value'],
                    $item['value'] == $sort ? ' selected' : '',
                    $item['name']
                );
                return $carry;
            },
            ''
        );
    }
}