<?php
/**
 * Created by PhpStorm.
 * User: Zebimax
 * Date: 27.02.15
 * Time: 18:13
 */

namespace Admin\Application\Model\Mysql;


use Model\ModelMysql;

class OrdersStats extends ModelMysql
{
    /**
     * @param callable $function
     * @return array|null
     */
    public function getOrderStats(callable $function = null)
    {
        $sql = sprintf(
            'SELECT format, value FROM %s WHERE value != "" AND value != "" ORDER BY sort',
            self::TABLE_ORDERS_STATS
        );
        return $function ? $this->getRowsFunction($sql, $function) : $this->getRows($sql);
    }
}