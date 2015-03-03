<?php
namespace Model;

use MysqlDb;

abstract class ModelMysql extends AbstractModel
{
    const TABLE_SPECIALS = 'specials';
    const TABLE_PRODUCTS_DESCRIPTION = 'products_description';
    const TABLE_PRODUCTS = 'products';
    const TABLE_PRODUCTS_TO_CATEGORIES = 'products_to_categories';
    const TABLE_CATEGORIES = 'categories';
    const TABLE_SOLR_CATEGORIES_NAMES = 'solr_categories_names';
    const TABLE_CATEGORIES_DESCRIPTION = 'categories_description';
    const TABLE_MANUFACTURERS = 'manufacturers';
    const TABLE_MANUFACTURERS_INFO = 'manufacturers_info';
    const TABLE_LANGUAGES = 'languages';
    const TABLE_SOLR_PRODUCTS_CATEGORIES = 'solr_products_categories';
    const TABLE_ORDERS_STATS = 'orders_stats';
    const DEFAULT_LIMIT = 100;
    /**
     * @var MysqlDb
     */
    protected $db;
    protected $languageId;
    protected $filters = array();
    protected $sql;
    protected $table = '';
    protected $required = array();
    protected $int = array();

    /**
     * @param MysqlDb $db
     */
    public function __construct(MysqlDb $db)
    {
        $this->db = $db;
        $this->languageId = \Macaw::getKey('languages_id');
    }

    /**
     * @return string
     */
    public function getTable()
    {
        return $this->table;
    }

    /**
     * @return MysqlDb
     */
    public function getDb()
    {
        return $this->db;
    }

    /**
     * @param $query
     * @return bool|\mysqli_result
     */
    public function query($query)
    {
        return $query ? $this->db->query($query) : false;
    }

    /**
     * @param $query
     * @return array|null
     */
    public function getOneRow($query)
    {
        $result = $this->query($query);
        return $result ? $this->fetchOne($result) : null;
    }

    /**
     * @param $query
     * @return array|null
     */
    public function getRows($query)
    {
        $result = $this->query($query);
        return $result ? $this->fetchAll($result) : null;
    }

    /**
     * @param $sql
     * @return int
     */
    public function update($sql)
    {
        $this->db->getConnection()->query($sql);
        return $this->db->getConnection()->affected_rows;
    }

    /**
     * @param $query
     * @param callable $function
     * @return array
     */
    public function getRowsFunction($query, callable $function)
    {
        $result = $this->query($query);
        $results = array();
        while ($row = $result->fetch_assoc()) {
            $results[] = $function($row);
        }
        return $results;
    }

    /**
     * @param $name
     * @param $value
     * @return string
     */
    protected function filter($name, $value)
    {
        $filterSql = '';
        if (isset($this->filters[$name])) {
            $filterSql = sprintf($this->filters[$name], $value);
        }
        return $filterSql;
    }

    /**
     * @param array $filters
     */
    protected function addLimit(array $filters = array())
    {
        $start = 0;
        $limit = self::DEFAULT_LIMIT;
        foreach (array_filter($filters) as $field => $filterValue) {
            switch ($field) {
                case 'start':
                    $start = (int) $filterValue;
                    break;
                case 'limit':
                    $limit = (int) $filterValue;
                    break;
                default:
                    break;
            }
        }
        if ($limit) {
            $this->sql .= sprintf(' LIMIT %s, %s', $start, $limit);
        }
    }

    /**
     * @param $string
     * @return string
     */
    protected function escape($string)
    {
        return trim($this->db->getConnection()->real_escape_string($string));
    }

    /**
     * @param $sql
     * @return int
     */
    protected function getCount($sql)
    {
        $oneRow = $this->getOneRow($sql);
        return $oneRow ? $oneRow['count'] : 0;
    }

    /**
     * @param $sql
     * @param string $aggregateSql
     * @return mixed
     */
    protected function replaceSelectWithAggregate($sql, $aggregateSql = ' COUNT(*) count ')
    {
        $start = stripos($sql, 'select') + 6;
        return str_replace(
            substr($sql, $start, stripos($sql, 'from') - $start),
            $aggregateSql,
            $sql
        );
    }

    /**
     * @param $sql
     * @return mixed
     */
    protected function cleanFromLimitSql($sql)
    {
        return str_replace(
            substr($sql, strripos($sql, 'limit')),
            '',
            $sql
        );
    }

    /**
     * @param array $data
     * @return bool
     */
    protected function checkRequired(array $data = array())
    {
        foreach ($this->required as $field) {
            if (!isset($data[$field]) || (in_array($field, $this->int) && !(int)$data[$field])) {
                return false;
            }
        }
        return true;
    }

    /**
     * @param $tokenName
     * @return string
     */
    protected function getCustomToken($tokenName)
    {
        return sprintf('{$%s$}', $tokenName);
    }

    /**
     * @param $sql
     * @return mixed
     */
    protected function getCountSql($sql)
    {
        return $this->replaceSelectWithAggregate(
            $this->cleanFromLimitSql($sql)
        );
    }

    protected function createFiltersSql($sql, array $filters = array())
    {
        $filtersStr = '';
        foreach (array_filter($filters) as $field => $filterValue) {
            switch ($field) {
                case 'product_id':
                    $filtersStr .= sprintf(' AND pd.products_id = %d', $filterValue);
                    break;
                case 'base_language_id':
                    $filtersStr .= sprintf(' AND pd.language_id = %d', $filterValue);
                    break;
                case 'category_id':
                    $filtersStr .= sprintf(
                        ' AND EXISTS(
                        SELECT ptc.`categories_id` FROM products_to_categories ptc WHERE ptc.`products_id` = p.`products_id` AND ptc.`categories_id` = %d)',
                        $filterValue
                    );
                    break;
                case 'brand_id':
                    $filtersStr .= sprintf(' AND p.manufacturers_id = %d', $filterValue);
                    break;
                case 'not_exist_translate':
                    $filtersStr .= ' AND pd_translate.language_id IS NULL';
                    break;
                case 'exist_translate':
                    $filtersStr .= ' AND pd_translate.language_id IS NOT NULL';
                    break;
                case 'names_not_translated':
                    $filtersStr .= ' AND pd_translate.products_name = ""';
                    break;
                case 'descriptions_not_translated':
                    $filtersStr .= ' AND (pd_translate.products_description = "" OR pd_translate.products_description IS NULL)';
                    break;
                case 'taglines_not_translated':
                    $filtersStr .= ' AND pd_translate.products_tagline = ""';
                    break;
                default:
                    if(is_callable($filterValue)) {
                        $filtersStr .= $filterValue();
                    }
                    break;
            }
        }
        if ($filtersStr) {
            if (stripos($sql, 'where') === false) {
                $filtersStr = ' WHERE ' . trim(substr($filtersStr, stripos('and', $filtersStr) + 4));
            }
        }
        return $filtersStr;
    }

    /**
     * @param array $filters
     * @return string
     */
    protected function createLimitSql(array $filters)
    {
        $start = 0;
        $limitSql = '';
        $limit = ModelMysql::DEFAULT_LIMIT;
        if (isset($filters['start'])) {
            $start = (int)$filters['start'];
        }
        if (isset($filters['limit'])) {
            $limit = (int)$filters['limit'];
        }

        if ($limit) {
            $limitSql = sprintf(' LIMIT %s, %s', $start, $limit);
        }
        return $limitSql;
    }

    /**
     * @param $sql
     * @param array $filters
     * @return mixed|string
     */
    protected function applyFilters($sql, array $filters = array())
    {
        if (isset($filters['translate_language_id'])) {
            $sql = str_replace(
                $this->getCustomToken('translate_language_id'),
                (int)$filters['translate_language_id'], $sql);
            unset($filters['translate_language_id']);
        }
        return $sql . $this->createFiltersSql($sql, $filters) . $this->createLimitSql($filters);
    }

    /**
     * @param $sql
     * @param $filter
     * @param null $value
     * @return string
     */
    protected function addFilter($sql, $filter, $value = null)
    {
        return $sql . $this->createFiltersSql($sql, array($filter => $value));
    }

    /**
     * @param \mysqli_result $result
     * @return array
     */
    private function fetchOne(\mysqli_result $result)
    {
        return $result->fetch_assoc();
    }

    /**
     * @param \mysqli_result $result
     * @return array
     */
    private function fetchAll(\mysqli_result $result)
    {
        $results = array();
        while ($row = $result->fetch_assoc()) {
            $results[] = $row;
        }
        return $results;
    }
}