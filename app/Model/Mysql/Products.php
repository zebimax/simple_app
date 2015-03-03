<?php

namespace Model\Mysql;

use Model\ModelMysql;

class Products extends ModelMysql
{
    protected $sql;

    protected $filters = array(

    );

    /**
     * @param array $filters
     * @return array
     */
    public function getUnipharmaExpensiveList(array $filters = array())
    {
        $sql = $this->applyFilters(sprintf(
            'SELECT
              p.products_id id,
              p.omschrijving definition,
              p.artnr_up article,
              IF(
                p.products_tax_class_id = 3,
                ROUND(p.products_price * 1.06, 2),
                ROUND(p.products_price * 1.21, 2)
              ) AS price,
              up.advies advice_price
            FROM
              products p
              LEFT JOIN unipharma_prijzen up
                ON p.artnr_up = up.artnr_up
            WHERE IF(
                p.products_tax_class_id = 3,
                ROUND(p.products_price * 1.06, 2),
                ROUND(p.products_price * 1.21, 2)
              ) > up.advies
              AND p.products_status = 1 '
        ), $filters);
        $countSql = $this->getCountSql($sql);
        return array(
            'list' => $this->getRows($sql),
            'count' => $this->getCount($countSql)
        );
    }
}