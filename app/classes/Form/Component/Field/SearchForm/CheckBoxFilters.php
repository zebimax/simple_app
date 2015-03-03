<?php

namespace Form\Component\Field\SearchForm;


use Form\Component\AbstractFormComponent;

class CheckBoxFilters extends AbstractFormComponent
{
    private $heads = array();
    private $options = array();
    private $template = '<table width="100%%"><tbody>%s</tbody></table>';
    private $frameTemplate = '<tr>%s</tr>';
    private $headsCaptionTemplate = '<td width="50%%" valign="top"><span class="filter"><span class="%s %s">%s</span></span></td>';
    private $headsOptionsTemplate = '<td valign="top"><div class="filter_result">%s</div></td>';
    private $headOptionSelectedTemplate1 = '<a href="javascript:void(0);" onclick="%s(%s);">%s <img src="img/icon_close.gif" align="absmiddle"></a>';
    private $headOptionSelectedTemplate2 = '&nbsp;&nbsp;&nbsp;&nbsp;<a href="javascript:void(0);" onclick="%s();">alles wissen</a>';
    private $optionsTemplate =
        '<td valign="top">
            <div class="%s-box"%s>
                <ul>%s</ul>
            </div>
        </td>';
    private $optionTemplate =
        '<li>
            <input type="checkbox" id="%s" name="%s" value="%d" %sonclick="%s(%d);">
            <label for="%s%d"><a href="javascript:void(0)" onclick="%s(%d)">%s</a></label>
        </li>';
    private $nonDisplay = ' style="display: none;"';



    public function __construct(array $params = array())
    {
        foreach ($params as $componentParams) {
            if (isset($componentParams['head'])) {
                $options = isset($componentParams['options']) && is_array($componentParams['options'])
                    ? $componentParams['options']
                    : array();
                $this->heads[] = $componentParams['head'];
                $this->options[] = $options;
            }
        }

    }
    public function make()
    {
        $headsHtml = $headSelectedHtml = $optionOptionsHtml = '';

        foreach ($this->heads as $head) {
            $headsHtml .= sprintf(
                $this->headsCaptionTemplate,
                $head['class'],
                $head['is_open'] ? 'open' : 'closed',
                $head['title']
            );
            $headSelected = isset($head['selected']) && is_array($head['selected'])
                ? $head['selected'] : array();
            $part2 = !empty($headSelected) ? sprintf(
                $this->headOptionSelectedTemplate2,
                $head['onclick_selected_all']
            ) : '';
            $part1 = '';
            if ($head['selected_part_1']) {
                foreach ($headSelected as $selected) {
                    $part1 .= sprintf(
                        $this->headOptionSelectedTemplate1,
                        $head['onclick_selected'],
                        $selected['id'],
                        $selected['name']
                    );
            }
            }

            $headSelectedHtml .= sprintf($this->headsOptionsTemplate, $part1 . $part2);
        }
        foreach ($this->options as $option) {
            $optionOptions = isset($option['options']) && is_array($option['options'])
                ? $option['options'] : array();
            $optionsHtml = '';
            foreach ($optionOptions as $optionOption) {
                if ($optionId = $optionOption['id']) {
                    $brandNameId = sprintf('%s_%d', $option['id_name'], $optionOption['id']);
                    $checked = in_array($optionOption['id'], $option['selected']) ? 'checked ' : '';
                    $optionsHtml .= sprintf(
                        $this->optionTemplate,
                        $brandNameId,
                        $option['id_name'],
                        $optionOption['id'],
                        $checked,
                        $option['onclick'],
                        $optionOption['id'],
                        $brandNameId,
                        $option['id'],
                        $option['onclick_text'],
                        $optionOption['id'],
                        $optionOption['name']
                    );
                }
            }
            $optionOptionsHtml .= sprintf(
                $this->optionsTemplate,
                $option['box_class'],
                $option['is_open'] ? '' : $this->nonDisplay,
                $optionsHtml
            );
        }
        $html = sprintf(
            $this->frameTemplate . $this->frameTemplate . $this->frameTemplate,
            $headsHtml, $headSelectedHtml, $optionOptionsHtml
        );
        return sprintf($this->template, $html);
    }
}
