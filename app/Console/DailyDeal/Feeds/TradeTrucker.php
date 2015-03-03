<?php
/**
 * Created by PhpStorm.
 * User: Zebimax
 * Date: 02.03.15
 * Time: 17:07
 */

namespace Console\DailyDeal\Feeds;


use Console\AbstractFeed;

class TradeTrucker extends AbstractFeed
{
    protected $fileName;
    protected $file;
    protected $saveDir;
    protected $tempPrefix = 'temp_';
    protected $separator = "\t";

    /**
     * @param array $config
     */
    public function __construct(array $config)
    {
        if ($this->checkConfig($config)) {
            $this->fileName = $config['file_name'];
            $this->saveDir = $config['save_dir'];

            if (isset($config['temp_prefix'])) {
                $this->tempPrefix = $config['temp_prefix'];
            }
            if (isset($config['separator'])) {
                $this->separator = $config['separator'];
            }
            parent::__construct($config);
        } else {
            throw new \InvalidArgumentException('Feed TradeTrucker must be configured with file_name to save feed');
        }

    }

    public function saveData()
    {
        $output = array();
        foreach ($this->exportMap as $key => $value) {
            $output[$key] = $this->stripCsvTags($key . ':' . $this->data[$key]) .PHP_EOL;
        }
        $content = join($this->separator, $output) . "\n";
        fwrite($this->file, $content);
    }

    /**
     * @param callable $callback
     * @return $this
     */
    public function close(callable $callback)
    {
        fclose($this->file);
        rename(
            $this->saveDir . $this->tempPrefix . $this->fileName,
            $this->saveDir . $this->fileName
        );
        $callback($this);
        return $this;
    }

    public function prepareFeed()
    {
        $this->file = fopen($this->saveDir . $this->tempPrefix . $this->fileName, 'w');
        fwrite($this->file, join($this->separator, array_values($this->getExportMap())) . "\n");
    }

    /**
     * @return mixed
     */
    public function getSavePoint()
    {
        return $this->fileName;
    }

    /**
     * @param $value
     * @return mixed
     */
    protected function stripCsvTags($value)
    {
        return str_replace(
            $this->separator, " ",
            str_replace( "\t", " ",
                str_replace( "\r", " ",
                    str_replace( "\n", " ",
                        str_replace( "\n\r", " ", $value
                        )
                    )
                )
            )
        );
    }

    /**
     * @param array $config
     * @return bool
     */
    protected function checkConfig(array $config)
    {
        if (isset($config['file_name']) && isset($config['save_dir'])) {
            return is_writable($config['save_dir']);
        }
        return false;
    }
}