<?php

namespace Plugins\View;


class BrandsArrayLinks
{
    private $closeLink = '&nbsp;&nbsp;&nbsp;&nbsp;<a href="javascript:void(0);" onclick="closeAllBrandsSpecail();"> alles wissen</a>';
    private $template = '';

    public function __construct()
    {
        $this->template = ' <a href="javascript:void(0);" onclick="removeBrandSpecail(%s);">%s <img src="
            ' . STATIC_PREPEND .
            'img/icon_close.gif" align="absmiddle"></a>';
    }

    /**
     * @param array $selectedBrands
     * @return string
     */
    public function __invoke(array $selectedBrands)
    {
        $result = '';
        foreach($selectedBrands as $value){
            $linkText = $value ? tep_getmanufacturer_name($value) : 'Geen merk';
            $result .= sprintf($this->template, $value, $linkText);
        }
        if (count($selectedBrands)) {
            $result .= $this->closeLink;
        }
        return $result;
    }
}