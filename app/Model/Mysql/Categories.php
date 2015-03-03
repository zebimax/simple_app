<?php

namespace Model\Mysql;


use Controller\SearchController;
use Model\ModelMysql;

class Categories extends ModelMysql
{
    private $excludedInLeftMenu = array(1129, 1652, 1651, 2246, 2244, 685);

    /**
     * @param array $filters
     * @return array|null
     */
    public function getList(array $filters = array())
    {
        $this->sql = sprintf('
            SELECT
              c.categories_id id,
              scn.solr_category_name long_name,
              scn.name
            FROM
              %s c
              LEFT JOIN %s scn
                ON scn.id = c.categories_id ORDER BY scn.name',
            self::TABLE_CATEGORIES,
            self::TABLE_SOLR_CATEGORIES_NAMES
        );
        return $this->getRows($this->sql);
    }

    /**
     * @param $categoryId
     * @return array|null
     */
    public function getParent($categoryId)
    {
        $query = sprintf(
            'SELECT c.parent_id, cd.categories_name category_name, scn.solr_category_name
            FROM %s cd
            LEFT JOIN %s c
              ON cd.categories_id = c.categories_id
            LEFT JOIN %s scn ON scn.id = c.categories_id
            WHERE cd.categories_id = %s AND cd.language_id = "%s"',
            self::TABLE_CATEGORIES_DESCRIPTION,
            self::TABLE_CATEGORIES,
            self::TABLE_SOLR_CATEGORIES_NAMES,
            (int)$categoryId,
            $this->languageId
        );
        return $this->getOneRow($query);
    }

    /**
     * @param array $categories
     * @param array $params
     * @return array
     */
    public function getCategoriesInfo(array $categories = array(), array $params = array())
    {
        $initParams = array(
            'categoryId' => false,
            'manufacturer' => false,
            'limit' => 30,
            'back_uri' => SearchController::SPECIALS_URL,
            'brandParam' => 'm',
            'categoryParam' => 'category',
            'keywords' => false,
        );
        $params = array_merge($initParams, $params);
        $backUri = $params['back_uri'];
        $backCategoryName = $parentParam = '';

        $result = array();
        $linkMore = '';

        if ($params['keywords']) {
            $linkMore .= sprintf('keywords=%s&', $params['keywords']);
        }
        if ($params['limit']) {
            $linkMore .= sprintf('limit=%s&', $params['limit']);
        }
        $breadcrumbParams = $linkMore;
        if($params['manufacturer']) {
            $linkMore .= sprintf('%s=%d&', $params['brandParam'], $params['manufacturer']);
        }

        $parentCategories = array();
        $currentParent = false;
        foreach ($categories as $key => $category) {
            $id = str_replace('/', '_', $key);
            if (!$category['name']) {
                continue;
            }
            if ($category['current']) {
                $currentParent = $category['parent'];
            }

            $categoryStr = $params['categoryParam'] . '=' . $id;
            if ($category['parent'] !== false) {
                $parentCategories[$category['parent']] = array(
                    'title' => $category['name'],
                    'link' => tep_href_link($backUri, $breadcrumbParams . $categoryStr),
                    'id' => $id
                );
            } else {
                $result[] = array(
                    'link' => tep_href_link($backUri, $linkMore . $categoryStr),
                    'name' => $category['name'],
                    'quantity' => $category['quantity']
                );
            }
        }
        if ($currentParent !== false) {
            $backCatTitle = $parentCategories[$currentParent]['title'];
            $backCategoryName = strlen($backCatTitle) > 15
                ? substr($backCatTitle, 0, 12) . '...'
                : $backCatTitle;
            $parentParam = $currentParent
                ? sprintf(
                    '%s=%s',
                    $params['categoryParam'],
                    $parentCategories[$currentParent - 1]['id']
                ): '1';
        }
        $additionalLink = $parentParam . '&' . $linkMore;

        return array(
            'clearCategoryLink' => ($currentParent !== false || ((int)$params['manufacturer'] && $params['brandParam'] !== 'manufacturers_id')) ?
                sprintf('<span>x <a href="%s">wissen</a></span>', $backUri)
                : '',
            'leftBackLink' => $currentParent !== false ?
                sprintf(
                    '<input type="button" class="nav-btn" value="Terug naar %s" onclick="window.location.href=\'%s\'">',
                    $backCategoryName,
                    tep_href_link($backUri, $additionalLink)
                )
                : '',
            'categories' => $result,
            'parents' => $parentCategories
        );
    }
}