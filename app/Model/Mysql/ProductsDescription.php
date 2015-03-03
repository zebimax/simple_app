<?php

namespace Model\Mysql;


use Model\ModelMysql;

class ProductsDescription extends ModelMysql
{
    protected $required = array('id', 'language_id', 'description', 'name', 'tagline');
    protected $int = array('id', 'language');
    protected $infoFilters = array(
        'not_exist_translate' => true,
        'names_not_translated' => true,
        'descriptions_not_translated' => true,
        'taglines_not_translated' => true
    );
    /**
     * @param $id
     * @param $languageId
     * @param array $data
     * @return bool|\mysqli_result
     */
    public function saveTranslatedProduct($id, $languageId, array $data)
    {
        $updateData = array_merge(
            array('id' => $id, 'language_id' => $languageId),
            $data
        );
        if (!$this->checkRequired($updateData)) {
            return false;
        }

        $sql = $this->getProductsDescription($id, $languageId)
            ? $this->createSaveTranslatedUpdate($updateData)
            : $this->createSaveTranslatedInsert($updateData);
        return $this->update($sql);
    }

    public function getProductsDescription($productId, $languageId)
    {
        $sql = $this->applyFilters(
            sprintf(
                'SELECT pd.products_id FROM %s pd',
                self::TABLE_PRODUCTS_DESCRIPTION
            ),
            array(
                'product_id' => $productId,
                'base_language_id' => $languageId)
            );
        return $this->getOneRow($sql);
    }

    /**
     * @param array $filters
     * @return array
     */
    public function getTranslateList(array $filters = array())
    {
        $sql = $this->applyFilters(
            sprintf('
                SELECT
                  p.products_id id,
                  pd.products_name name,
                  pd.products_description description,
                  pd.products_tagline tagline,
                  COALESCE(pd_translate.products_name, "") translate_name,
                  COALESCE(pd_translate.products_description, "")translate_description,
                  COALESCE(pd_translate.products_tagline, "") translate_tagline,
                  pd_translate.language_id translate_language_id
                FROM %s p
                LEFT JOIN %s pd ON pd.products_id = p.products_id
                LEFT JOIN %s pd_translate ON pd_translate.products_id = p.products_id
                    AND pd_translate.language_id = %s',
                self::TABLE_PRODUCTS,
                self::TABLE_PRODUCTS_DESCRIPTION,
                self::TABLE_PRODUCTS_DESCRIPTION,
                $this->getCustomToken('translate_language_id')
            ),
            $filters
        );
        $countSql = $this->getCountSql($sql);
        return array_merge(
            array(
            'list' => $this->getRows($sql),
            'count' => $this->getCount($countSql)
            ),
            $this->getTranslateInfo($countSql)
        );
    }

    /**
     * @param $sql
     * @return array
     */
    private function getTranslateInfo($sql)
    {
        $result = array();
        foreach ($this->infoFilters as $filter => $value) {
            $result[$filter] = $this->getCount($this->addFilter($sql, $filter, $value));
        }
        return $result;
    }

    /**
     * @param array $data
     * @return string
     */
    private function createSaveTranslatedInsert(array $data)
    {
        return sprintf('
            INSERT INTO %s (products_name, products_description, products_tagline, products_id, language_id)
            VALUES ("%s", "%s", "%s", %d, %d)',
            self::TABLE_PRODUCTS_DESCRIPTION,
            $this->escape($data['name']),
            $this->escape($data['description']),
            $this->escape($data['tagline']),
            (int)$data['id'],
            (int)$data['language_id']
        );
    }

    /**
     * @param array $data
     * @return bool|string
     */
    private function createSaveTranslatedUpdate(array $data)
    {
        return sprintf('
            UPDATE %s SET products_name = "%s", products_description = "%s", products_tagline ="%s"
            WHERE products_id = %d AND language_id = %d',
            self::TABLE_PRODUCTS_DESCRIPTION,
            $this->escape($data['name']),
            $this->escape($data['description']),
            $this->escape($data['tagline']),
            (int)$data['id'],
            (int)$data['language_id']
        );
    }
}