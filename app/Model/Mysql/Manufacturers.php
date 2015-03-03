<?php

namespace Model\Mysql;


use Controller\SearchController;
use Model\ModelMysql;

class Manufacturers extends ModelMysql
{
    public function getBrandsInfo(array $brands = array(), array $params = array())
    {

        $initialParams = array(
            'brands' => array(),
            'categoryId' => false,
            'manufacturer' => false,
            'limit' => 30,
            'back_uri' => SearchController::SPECIALS_URL,
            'brandParam' => 'm',
            'categoryParam' => 'category',
            'leftBrandBackLink' => '',
            'keywords' => false,
            'brandsLimit' => true,
            'defaultBrandsLimit' => 0
        );
        $params = array_merge($initialParams, $params);
        $backUri = $params['back_uri'];
        $manufactureId = $params['manufacturer'];
        $categoryId = $params['categoryId'];
        $brandInfo['brands'] = array();
        $brandInfo['brand_link'] = array();
        $addToBrandsLinks = $addToBackLink = '';
        if ($params['keywords']) {
            $addToBrandsLinks = sprintf('&%s=%s', 'keywords', $params['keywords']);
            $addToBackLink = ltrim($addToBrandsLinks, '&');
        }
        if ($categoryId) {
            $addToBrandsLinks .= sprintf('&%s=%s', $params['categoryParam'], $categoryId);
            $addToBackLink = ltrim($addToBrandsLinks, '&');
        }
        if ($params['limit']) {
            $addToBrandsLinks .= sprintf('&limit=%s', $params['limit']);
            $addToBackLink = ltrim($addToBrandsLinks, '&');
        }
        $breadCrumbParams = $addToBrandsLinks;
        if ($params['manufacturer']) {
            $breadCrumbParams .= sprintf('&m=%d', $params['manufacturer']);
        }
        $brandsLimit = $params['brandsLimit'] ? $params['defaultBrandsLimit'] : false;
        foreach ($this->getBrandsByNames($brands, array('limit' => $brandsLimit)) as $brandRow) {
            $paramsStr = "{$params['brandParam']}={$brandRow['id']}" . $addToBrandsLinks;

            if ($isCurrent = $brandRow['id'] == $manufactureId) {
                $brandInfo['brand_link'][] = array(
                    'title' => $brandRow['name'],
                    'link' => tep_href_link($backUri, $breadCrumbParams),
                );
            }
            $brandInfo['brands'][$brandRow['id']] = array_merge(
                $brandRow,
                array(
                    'link' => tep_href_link($backUri, $paramsStr),
                    'quantity' => $brands[$brandRow['name']],
                    'current' => $isCurrent
                )
            );
        }

        $leftBrandBackLink = $manufactureId
            ? sprintf(
                '<input type="button" class="nav-btn" value="Terug" onclick="window.location.href=\'%s\'">',
                tep_href_link($backUri, $addToBackLink)
            )
            : '';
        $brandInfo['leftBrandBackLink'] = $leftBrandBackLink;
        $sizeOfBrands = sizeof($brands);
        if (!$params['brandsLimit'] && !$manufactureId && $params['defaultBrandsLimit'] < $sizeOfBrands) {
            $brandInfo['brands'][] = array(
                'link' => tep_href_link($backUri, $addToBrandsLinks),
                'quantity' => false,
                'current' => false,
                'name' => '>Toon minder merken'
            );
        } elseif (!$manufactureId && $params['defaultBrandsLimit'] < $sizeOfBrands) {
            $brandInfo['brands'][] = array(
                'link' => tep_href_link($backUri, 'brands_limit=all' . $addToBrandsLinks),
                'quantity' => false,
                'current' => false,
                'name' => '>Toon alle merken'
            );
        }
        return $brandInfo;
    }

    public function getBrandDescription($brandId, $languageId)
    {
        $sql = sprintf(
            'SELECT m.manufacturers_name name, mi.manufacturers_htc_title_tag htc_title_tag,
            mi.manufacturers_htc_desc_tag htc_desc_tag, mi.manufacturers_htc_keywords_tag htc_keyw_tag
            FROM %s m LEFT JOIN %s mi ON m.manufacturers_id = mi.manufacturers_id
            WHERE m.manufacturers_id = %s and mi.languages_id = %s',
            self::TABLE_MANUFACTURERS,
            self::TABLE_MANUFACTURERS_INFO,
            (int)$brandId,
            (int)$languageId
        );
        return $this->getOneRow($sql);
    }

    private function getBrandsByNames(array $brandsIds, array $params = array())
    {
        $rows = array();

        if (!empty($brandsIds)) {
            $this->sql = sprintf(
                'SELECT m.manufacturers_name name, m.manufacturers_id id
            FROM %s m
            WHERE m.manufacturers_name IN (%s)',
                self::TABLE_MANUFACTURERS,
                sprintf('"%s"', implode('","', array_keys($brandsIds)))
            );
            if (isset($params['limit']) && (int)$params['limit']) {
                $this->addLimit($params);
            }
            $rows = $this->getRows($this->sql);
        }

        return $rows;
    }

    public function getBrandById($id)
    {
        $brandSelect = 'SELECT m.manufacturers_name FROM manufacturers m WHERE m.manufacturers_id= '. (int)$id;
        $row = $this->getOneRow($brandSelect);
        return $row;
    }

    public function getList(array $filters = array())
    {
        $this->sql = sprintf(
            'SELECT m.manufacturers_id id, m.manufacturers_name name FROM %s m ORDER BY m.manufacturers_name',
            self::TABLE_MANUFACTURERS
        );
        return $this->getRows($this->sql);
    }
}