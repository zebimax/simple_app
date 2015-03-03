<?php

namespace Model\Mysql;


use Model\ModelMysql;

class SolrProductsCategories extends ModelMysql
{
    protected $table = '';
    public function getProductsCategories(array $products, $languageId)
    {
        $productsCategories = array();

        if (!empty($products)) {
            $productsCategories = $this->getRows(sprintf(
                'SELECT product_id id, solr_cats_names FROM %s WHERE product_id IN (%s) AND language_id = %d',
                self::TABLE_SOLR_PRODUCTS_CATEGORIES,
                implode(',', $products),
                $languageId
            ));
        }

        return $productsCategories;
    }
}