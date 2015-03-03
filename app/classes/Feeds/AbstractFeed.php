<?php
/**
 * Created by PhpStorm.
 * User: Zebimax
 * Date: 02.03.15
 * Time: 17:27
 */

namespace Console;


use Feeds\FeedInterface;

abstract class AbstractFeed implements FeedInterface
{
    const EXPORT_MAP_KEY = 'to_export';
    protected $exportMap;
    protected $data;
    protected $id;

    /**
     * @return mixed
     */
    abstract function prepareFeed();
    /**
     * @return mixed
     */
    abstract public function saveData();

    /**
     * @param callable $callback
     * @return mixed
     */
    abstract public function close(callable $callback);

    /**
     * @return mixed
     */
    abstract public function getSavePoint();

    /**
     * @param array $config
     */
    public function __construct(array $config)
    {
        if (!isset($config[self::EXPORT_MAP_KEY]) || !is_array($config[self::EXPORT_MAP_KEY])) {
            throw new \InvalidArgumentException('Feed configuration must contain export map array');
        }
        if (!isset($config['id'])) {
            throw new \InvalidArgumentException('Feed configuration must contain id');
        }
        $this->id = $config['id'];
        $this->exportMap = $config[self::EXPORT_MAP_KEY];
        unset($config[self::EXPORT_MAP_KEY]);
    }

    /**
     * @return mixed
     */
    public function getExportMap()
    {
        return $this->exportMap;
    }

    /**
     * @param $key
     * @param null $default
     * @return null
     */
    public function getDataValue($key, $default = null)
    {
        $result = $default;
        if (isset($this->data[$key])) {
            $result = $this->data[$key];
        }
        return $result;
    }

    /**
     * @param array $data
     * @return $this
     */
    public function setData(array $data)
    {
        $this->data = $data;
        return $this;
    }

    /**
     * @param callable $preSaveDataCallback
     * @return $this
     */
    public function preSaveData(callable $preSaveDataCallback)
    {
        $preSaveDataCallback($this);
        return $this;
    }

    /**
     * @param $key
     * @param $value
     */
    public function setDataValue($key, $value)
    {
        $this->data[$key] = $value;
    }

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param array $data
     */
    protected function prepareData(array $data)
    {

    }
}