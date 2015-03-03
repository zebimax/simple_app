<?php

namespace BasicLogger\Writers;


use Model\ModelMysql;

class MysqlWriter implements WriterInterface
{
    const TEXT_FIELD = 'text_field';
    const USER_FIELD = 'user_field';
    const DATE_FIELD = 'date_field';
    const UNIQUE_ID_FIELD = 'unique_id_field';
    const LABEL_FIELD = 'label_field';
    const ADDITIONAL_FIELD = 'additional_field';

    const TEXT = 'text';
    const USER = 'user';
    const DATE = 'date';
    const UNIQUE_ID = 'unique_id';
    const LABEL = 'label';
    const ADDITIONAL = 'additional';
    const DATE_FORMAT = 'date_format';

    const NOT_TEXT_FIELD_ERROR = 'text_field param must be defined!';
    const NOT_TABLE_NAME_ERROR = 'table name unknown!';
    const NOT_VALID_PARAM_ERROR = 'param %s must be not empty string!';

    protected $textField;
    protected $userField;
    protected $dateField;
    protected $uniqueIdField;
    protected $labelField;
    protected $additionalField;
    protected $dateFormat = 'Y-m-d H:i:s';

    protected $adapter;

    /**
     * @param ModelMysql $logTable
     * @param array $params
     * @throws \Exception
     */
    public function __construct(ModelMysql $logTable, array $params = array())
    {
        if (!$this->checkRequiredStringParam($logTable->getTable())) {
            throw new \Exception(self::NOT_TABLE_NAME_ERROR);
        }
        $this->adapter = $logTable;
        $this->setParams($params);
    }

    /**
     * @param array $message
     */
    public function write(array $message)
    {
        $sqlTpl = 'INSERT INTO %s (%s) VALUES (%s)';
        $fields = $values = array();
        if (!isset($message[self::DATE]) || empty($message[self::DATE])) {
            $date = new \DateTime();
            $message[self::DATE] = $date->format($this->dateFormat);
        }
        foreach ($message as $key => $value) {
            switch (true) {
                case $key == self::TEXT:
                    $fields[] = $this->textField;
                    $values[] = is_array($value)
                        ? json_encode($value)
                        : $value;
                    break;
                case $key == self::USER && $this->userField:
                    $fields[] = $this->userField;
                    $values[] = $value;
                    break;
                case $key == self::DATE && $this->dateField:
                    $fields[] = $this->dateField;
                    $values[] = $value;
                    break;
                case $key == self::UNIQUE_ID && $this->uniqueIdField:
                    $fields[] = $this->uniqueIdField;
                    $values[] = $value;
                    break;
                case $key == self::LABEL && $this->labelField:
                    $fields[] = $this->labelField;
                    $values[] = $value;
                    break;
                case $key == self::ADDITIONAL && $this->labelField:
                    $fields[] = $this->additionalField;
                    $values[] = is_array($value)
                        ? json_encode($value)
                        : $value;
                    break;
            }
        }
        if (!empty($fields)) {
            $fieldsSql = implode(',', $fields);
            $mysqli = $this->adapter->getDb()->getConnection();
            $valuesSql = rtrim(array_reduce($values, function($carry, $item) use ($mysqli){
                return $carry . sprintf('"%s",', $mysqli->real_escape_string($item));
            }, ''), ','
            );
            $sql = sprintf($sqlTpl, $this->adapter->getTable(), $fieldsSql, $valuesSql);
            $this->adapter->update(
                $sql
            );
        }
    }

    /**
     * @param array $params
     * @throws \Exception
     */
    protected function setParams(array $params = array())
    {
        if (
            !isset($params[self::TEXT_FIELD]) ||
            !$this->checkRequiredStringParam($params[self::TEXT_FIELD]
            )
        ) {
            throw new \Exception(self::NOT_TEXT_FIELD_ERROR);
        }
        foreach ($params as $key => $value) {
            if (!$this->checkRequiredStringParam($value)) {
                throw new \Exception(self::NOT_VALID_PARAM_ERROR);
            }
            switch ($key) {
                case self::TEXT_FIELD:
                    $this->textField = $value;
                    break;
                case self::USER_FIELD:
                    $this->userField = $value;
                    break;
                case self::DATE_FIELD:
                    $this->dateField = $value;
                    break;
                case self::UNIQUE_ID_FIELD:
                    $this->uniqueIdField = $value;
                    break;
                case self::LABEL_FIELD:
                    $this->labelField = $value;
                    break;
                case self::ADDITIONAL_FIELD:
                    $this->additionalField = $value;
                    break;
                case self::DATE_FORMAT:
                    $this->dateFormat = $value;
                    break;
                default:
                    break;
            }
        }
    }

    /**
     * @param $value
     * @return bool
     */
    private function checkRequiredStringParam($value)
    {
        return is_string($value)  && $value;
    }
}