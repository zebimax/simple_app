<?php

namespace Plugins\View;


use Controller\SearchController;

class HeaderNavigation
{
    private $activeCategory;
    private $template;

    public function __construct()
    {
        $this->template = '<ul class="nav-level-1">
                <li class="li-home"><a href="%s"><span class="dod-ficon-home"></span>Home</a></li>
                <li%s>%s</li>
                <li%s>%s</li>
                <li%s>%s</li>
                <li%s>%s</li>
                <li%s>%s</li>
                <li%s>%s</li>
                <li%s>%s</li>
            </ul>
            <ul class="nav-level-2">
                <li%s>%s</li>
                <li%s>%s</li>
                <li%s>%s</li>
                <li%s>%s</li>
                <li%s>%s</li>
                <li%s>%s</li>
                <li class="li-superfoods%s">%s</li>
            </ul>';
    }

    /**
     * @param $urlPath
     * @param null $activeCategory
     * @param null $secondCategory
     * @return string
     */
    public function __invoke($urlPath, $activeCategory = null, $secondCategory = null)
    {
        $this->activeCategory = (int)$activeCategory;

        $categories = array(
            'injury' => array('class' => '', 'link' => $this->makeCategoryLink(650, HEADER_TITLE_AFVALLEN), 'id' => 650),
            'care' => array('class' => '', 'link' => $this->makeCategoryLink(361, HEADER_TITLE_VERZORGING), 'id' => 361),
            'baby' => array('class' => '', 'link' => $this->makeCategoryLink(598, HEADER_TITLE_ZWANGER_BABY), 'id' => 598),
            'drink' => array('class' => '', 'link' => $this->makeCategoryLink(1120, HEADER_TITLE_ETEN_DRINKEN), 'id' => 1120),
            'parfum' => array('class' => '', 'link' => $this->makeCategoryLink(1207, 'Parfum &amp; Cosmetica'), 'id' => 1207),
            'sexuality' => array('class' => '', 'link' => $this->makeCategoryLink(1147, HEADER_TITLE_SEKSUALITEIT), 'id' => 1147),
            'sport' => array('class' => '', 'link' => $this->makeCategoryLink(917, HEADER_TITLE_SPORT), 'id' => 917),
            'misc' => array('class' => '','link' => $this->makeCategoryLink(1277, HEADER_TITLE_DIVERSEN), 'id' => 1277),
            'gift' => array('class' => '', 'link' => $this->makeCategoryLink(650, 'Cadeau'), 'id' => 596),
            'health' => array(
                'class' => '',
                'link' => $this->makeCategoryLink(1648, 'Gezondheid'),
                'id' => 1648,
                'condition' => in_array($this->activeCategory, array(685, 1652, 1651, 2246, 2244, 1129, 1648, 2107))
            ),
            'vitamines' => array(
                'class' => '',
                'link' => $this->makeCategoryLink(964, HEADER_TITLE_VITAMINES),
                'id' => 964,
                'condition' => $this->activeCategory == 964 && $secondCategory != 3131
            )
        );
        if ($this->activeCategory) {
            foreach ($categories as $category) {
                $category['class'] = isset($category['condition'])
                    ? $this->getActiveStringWithCondition($category['condition'])
                    : $this->getActiveString($category['id']);
            }
            $categories['top'] = array(
                'class' => $this->getActiveStringWithCondition(($this->activeCategory == 964 && $secondCategory == 3131), ' active'),
                'link' => $this->makeCategoryLink('964_3131', 'Superfoods <span class="icon-sprite-sheet"></span><span class="icon-sprite-tooltip-actueel">Actueel</span>'),
            );
        }
        $categories['top']['link'] = $this->makeCategoryLink(
            '964_3131',
            'Superfoods <span class="icon-sprite-sheet"></span><span class="icon-sprite-tooltip-actueel">Actueel</span>'
        );
        $newProductsCond = $urlPath == 'products_new.php';
        $specialsCond = ($urlPath == 'specials.php' || $urlPath == SearchController::SPECIALS_URL);
        return sprintf(
            $this->template,
            tep_href_link(FILENAME_DEFAULT),
            $categories['injury']['class'],
            $categories['injury']['link'],
            $categories['care']['class'],
            $categories['care']['link'],
            $categories['baby']['class'],
            $categories['baby']['link'],
            $categories['health']['class'],
            $categories['health']['link'],
            $categories['vitamines']['class'],
            $categories['vitamines']['link'],
            $categories['drink']['class'],
            $categories['drink']['link'],
            $categories['parfum']['class'],
            $categories['parfum']['link'],
            $categories['sexuality']['class'],
            $categories['sexuality']['link'],
            $categories['sport']['class'],
            $categories['sport']['link'],
            $categories['misc']['class'],
            $categories['misc']['link'],
            $categories['gift']['class'],
            $categories['gift']['link'],
            $this->getActiveStringWithCondition($newProductsCond),
            $this->makePageLink('products_new.php', HEADER_TITLE_NIEUW),
            $this->getActiveStringWithCondition($specialsCond),
            $this->makePageLink('specials.php', HEADER_TITLE_AANBIEDINGEN),
            $categories['top']['class'],
            $categories['top']['link']
        );
    }

    /**
     * @param $toCheck
     * @return string
     */
    private function getActiveString($toCheck)
    {
        return $this->activeCategory == $toCheck ? ' class="active"' : '';
    }

    /**
     * @param $condition
     * @param string $classString
     * @return string
     */
    private function getActiveStringWithCondition($condition, $classString = ' class="active"')
    {
        return $condition ?  $classString : '';
    }

    /**
     * @param $id
     * @param $title
     * @return string
     */
    private function makeCategoryLink($id, $title)
    {
        $href = tep_href_link('index.php', "cPath={$id}");
        return "<a href=\"{$href}\">{$title}</a>";
    }

    /**
     * @param $name
     * @param $title
     * @return string
     */
    private function makePageLink($name, $title)
    {
        $href = tep_href_link($name);
        return "<a href=\"{$href}\">{$title}</a>";
    }
}