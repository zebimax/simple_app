<?php

namespace Plugins\View;


class BoxCounter
{
    private $template = '<div class="box-counter">in deze categorie: <span class="box-counter-container"><strong>%s</strong> <span>artikelen</span></span></div>';

    /**
     * @param $count
     * @return string
     */
    public function __invoke($count)
    {
        return (int)$count
            ? sprintf($this->template, $count)
            : '';
    }
}