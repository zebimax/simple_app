<?php

namespace Plugins\View;


class BrandOptions
{
    private $nonDisplay = ' style="display: none;"';
    private $template = '<td valign="top"><div class="options-box"%s><ul>%s</ul></div></td>';
    private $linkTemplate;

    public function __construct()
    {
        $this->linkTemplate = '<li><input type="checkbox" id="brands_%s" name="brands" value="'
            . '%s"%s onclick="checkBrandSpecail(%s);"><label for="brand1"><a href="javascript:void(0)" onclick="checkBrandSpecailText('
            . '%s)">%s</a></label></li>';
    }


    public function __invoke(array $brands = array(), $brandOpen, array $selectedBrands = array())
    {
        $optionsLinks = '';

        foreach ($brands as $brand) {
            $optionsLinks .= sprintf(
                $this->linkTemplate,
                $brand['id'],
                $brand['id'],
                in_array($brand['id'], $selectedBrands) ? ' checked' : '',
                $brand['id'],
                $brand['id'],
                $brand['name']
            );
        }

        return sprintf(
            $this->template,
            !$brandOpen ? $this->nonDisplay : '',
            $optionsLinks
        );
    }
}