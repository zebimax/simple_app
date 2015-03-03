<?php
namespace Plugins\View;


class Breadcrumb
{
    private $skin = '';
    private $separator;
    private $notShown = array(
        'biodavitymea', 
        'biodermal', 
        'cattier', 
        'dagvangratisverz', 
        'dermalex', 
        'eyefresh', 
        'exclusief', 
        'xls', 
        'wratweg', 
        'waterwratjes', 
        'waterpokken', 
        'wartner', 
        'unicare', 
        'sabaiolie', 
        'predictor', 
        'nailner', 
        'gehwol', 
        'nuon', 
        'easydiet', 
        'limisan'
    );

    private $trail = array();
    
    public function __construct()
    {
        $this->separator = "&nbsp;" . tep_draw_separator('pixel_trans.gif', '10', '1');
    }

    /**
     * @param array $trail
     * @param null $skin
     * @return string
     */
    public function __invoke(array $trail = array(), $skin = null)
    {
        $this->skin = $skin;
        $trailStr = '';
        if ($this->isShow()) {
            $this->makeTrail($trail);
            $trailStr = $this->googleMicroDataTrail();
        }
        return $this->separator . $trailStr;
    }

    /**
     * @return bool
     */
    private function isShow()
    {
        return !in_array($this->skin, $this->notShown);
    }

    private function googleMicroDataTrail($separator = ' &raquo; ')
    {
        $trailString = '';
        foreach ($this->trail as $item) {
            $markBefore = '';
            $markAfter = '';
            $link = '';
            $linkClose = '';
            if ($item['marked']) {
                $markBefore = '<span>';
                $markAfter = '</span>';
            }
            if (tep_not_null($item['link'])) {
                $link = sprintf('<a href="%s" itemprop="url">', $item['link']);
                $linkClose = '</a>';
            }

            $trailString .= sprintf(
                '%s<span class="grey_555" itemprop="title">%s%s%s</span>%s%s',
                $link,
                $markBefore,
                $item['title'],
                $markAfter,
                $linkClose,
                $separator
                );
        }

        return sprintf(
            '<span itemscope itemtype="http://data-vocabulary.org/Breadcrumb">%s</span>',
            rtrim($trailString, $separator)
        );
    }

    private function add($item)
    {
        $this->trail[] = array(
            'title' => isset($item['title']) ? $item['title'] : false,
            'link' => isset($item['link']) ? $item['link'] : '',
            'marked' => isset($item['marked']) ? $item['marked'] : false
        );
    }

    private function makeTrail(array $trail)
    {
        foreach ($trail as $link) {
            $this->add($link);
        }
    }
}