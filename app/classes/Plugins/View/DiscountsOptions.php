<?php

namespace Plugins\View;


class DiscountsOptions
{
    private $discountsBlock = '<td valign="top"><div class="korting-box"%s><ul>';
    private $bottomPart = '</ul></div></td>';
    private $valueTextMap = array(
        1 => '0 - 10%',
        2 => '10 - 30%',
        3 => '30 - 50%',
        4 => 'meer dan 50%'
    );

    private $linkTemplate;

    private $nonDisplay = ' style="display: none;"';

    public function __construct()
    {
        $this->linkTemplate = '<li><input type="checkbox" name="korts" id="korts_%s" value="%s" '
            . '%sonclick="checkKorts(%s);"><label%s><a href="javascript:void(0)"'
            . ' onclick="checkKortsByText(%s)">%s</a></label></li>';
    }

    /**
     * @param array $discounts
     * @param array $selectedDiscounts
     * @param $isOpen
     * @return string
     */
    public function __invoke(array $discounts = array(), array $selectedDiscounts = array(), $isOpen)
    {
        $this->discountsBlock = sprintf($this->discountsBlock, $isOpen ? '' : $this->nonDisplay);
        foreach ($discounts as $key => $value) {
            if ($value && isset($this->valueTextMap[$key])) {
                $this->discountsBlock .= sprintf(
                    $this->linkTemplate,
                    $key,
                    $key,
                    in_array($key, $selectedDiscounts) ? 'checked ' : '',
                    $key,
                    $key == 1 ? ' for="brand1"' :'',
                    $key,
                    $this->valueTextMap[$key]
                );
            }
        }
        return $this->discountsBlock . $this->bottomPart;
    }
}