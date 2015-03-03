<?php

namespace Plugins\View;


class DiscountsArrayLinks
{
    private $template = '';
    private $closeLink = '&nbsp;&nbsp;&nbsp;&nbsp;<a href="javascript:void(0);" onclick="closeAllKorts();"> alles wissen</a>';
    private $valueTextMap = array(
        1 => '0 - 10%',
        2 => '10 - 30%',
        3 => '30 - 50%',
        4 => 'meer dan 50%'
    );

    public function __construct()
    {
        $this->template = ' <a href="javascript:void(0);" onclick="removeKorts(%s);">%s<img src="
            ' . STATIC_PREPEND . 'img/icon_close.gif" align="absmiddle"></a>';
    }

    /**
     * @param array $discounts
     * @return string
     */
    public function __invoke(array $discounts)
    {
        $result = '';
        foreach($discounts as $value){
            if (isset($this->valueTextMap[$value])) {
                $result .= sprintf($this->template, $value, $this->valueTextMap[$value]);
            }
        }
        if(count($discounts)) {
            $result .= $this->closeLink;
        }
        return $result;
    }
}