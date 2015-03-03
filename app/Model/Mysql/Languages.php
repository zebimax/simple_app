<?php

namespace Model\Mysql;


use Model\ModelMysql;

class Languages extends ModelMysql
{
    /**
     * @param array $filter
     * @return array|null
     */
    public function getList()
    {
        $this->sql = sprintf(
            'SELECT l.languages_id id, l.code, l.name, l.image, l.directory FROM languages l',
            self::TABLE_LANGUAGES
            );
        return $this->getRows($this->sql);
    }

    /**
     * @param $id
     * @return array|null
     */
    public function getLanguage($id)
    {
        $this->sql = sprintf('SELECT * FROM %s l WHERE l.languages_id = %d', self::TABLE_LANGUAGES, $id);
        return $this->getOneRow($this->sql);
    }

    /**
     * @return array|null
     */
    public function getLanguagesOptions()
    {
        $this->sql = sprintf(
            'SELECT l.code value, l.name FROM %s l',
            self::TABLE_LANGUAGES
        );
        return $this->getRows($this->sql);
    }
}