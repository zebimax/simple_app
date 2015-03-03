<?php

namespace Plugins\View;


class PaginationLinks 
{
    private $phpUrlPath;
    private $requestType;

    public function __construct()
    {
        $this->phpUrlPath = \Macaw::getKey('php_url_path');
        $this->requestType = \Macaw::getKey('request_type');
    }

    /**
     * @param $maxPageLinks
     * @param string $parameters
     * @param $currentPageNumber
     * @param $numberOfPages
     * @param string $pageName
     * @return string
     */
    public function __invoke($maxPageLinks, $parameters = '', $currentPageNumber, $numberOfPages, $pageName = 'page')
    {
        $display_links_string = '<ul>';

        if (tep_not_null($parameters) && (substr($parameters, -1) != '&')) {
            $parameters .= '&';
        }

        if ($currentPageNumber > 1) {
            $display_links_string .=
                '<li><a'
                . $this->getPagLinkId($currentPageNumber, $currentPageNumber - 1, $numberOfPages)
                . ' href="' . tep_href_link($this->phpUrlPath, $parameters . $pageName . '=' . ($currentPageNumber - 1), $this->requestType) .
                '"  title=" ' . PREVNEXT_TITLE_PREVIOUS_PAGE . ' "><img src="' . STATIC_PREPEND . 'img/arrow-left.png" width="5" height="5" class="arrow"/> vorige</a></li>';
        }

        $cur_window_num = intval($currentPageNumber / $maxPageLinks);
        if ($currentPageNumber % $maxPageLinks) {
            $cur_window_num++;
        }

        $max_window_num = intval($numberOfPages / $maxPageLinks);
        if ($numberOfPages % $maxPageLinks) {
            $max_window_num++;
        }

        // previous window of pages
        if ($cur_window_num > 1) {
            $display_links_string .=
                '<li><a href="'
                . tep_href_link($this->phpUrlPath, $parameters . $pageName . '=' . (($cur_window_num - 1) * $maxPageLinks), $this->requestType) .
                '"  title=" ' . sprintf(PREVNEXT_TITLE_PREV_SET_OF_NO_PAGE, $maxPageLinks) . ' ">...</a></li>';
        }

        // page nn button
        for (
            $jump_to_page = 1 + (($cur_window_num - 1) * $maxPageLinks);
            ($jump_to_page <= ($cur_window_num * $maxPageLinks)) && ($jump_to_page <= $numberOfPages);
            $jump_to_page++
        ) {
            if ($jump_to_page == $currentPageNumber) {
                $display_links_string .= '<li class="active_page"><a href="javascript:void(0);">' . $jump_to_page . '</a></li>';
            } else {
                $display_links_string .=
                    '<li><a'
                    . $this->getPagLinkId($currentPageNumber, $jump_to_page, $numberOfPages)
                    . ' href="'
                    . tep_href_link($this->phpUrlPath, $parameters . $pageName . '=' . $jump_to_page, $this->requestType)
                    . '"  title=" ' . sprintf(PREVNEXT_TITLE_PAGE_NO, $jump_to_page) . ' ">' . $jump_to_page . '</a></li>';
            }
        }

        // next window of pages
        if ($cur_window_num < $max_window_num) {
            $display_links_string .=
                '<li><a href="'
                . tep_href_link($this->phpUrlPath, $parameters . $pageName . '=' . (($cur_window_num) * $maxPageLinks + 1), $this->requestType)
                . '" title=" ' . sprintf(PREVNEXT_TITLE_NEXT_SET_OF_NO_PAGE, $maxPageLinks) . ' ">...</a></li>';
        }

        // next button
        if (($currentPageNumber < $numberOfPages) && ($numberOfPages != 1)) {
            $display_links_string .=
                '<li><a'
                . $this->getPagLinkId($currentPageNumber, $currentPageNumber + 1, $numberOfPages)
                . ' href="' . tep_href_link($this->phpUrlPath, $parameters . 'page=' . ($currentPageNumber + 1), $this->requestType)
                . '"  title=" ' . PREVNEXT_TITLE_NEXT_PAGE . ' ">volgende <img src="' . STATIC_PREPEND . 'img/arrow-right.png" width="5" height="5" class="arrow"/></a></li>';
        }

        $display_links_string .= '</ul>';
        return $display_links_string;
    }

    /**
     *  make pagination links class for searching them and place into <head> section by javascript
     * @param $currentPage
     * @param $linkPageNum
     * @param $maxPage
     * @return string
     */
    private function getPagLinkId($currentPage, $linkPageNum, $maxPage)
    {
        $id= ' id=';

        if (
            ($currentPage == $linkPageNum) ||
            ($currentPage == 1 && $linkPageNum != 2) ||
            ($currentPage == $maxPage && $linkPageNum != $maxPage - 1)
        ) {
            return '';
        } elseif ($currentPage + 1 == $linkPageNum) {
            return $id . "pag_next";
        } elseif ($currentPage - 1 == $linkPageNum){
            return $id . "pag_prev";
        } else {
            return '';
        }
    }
}