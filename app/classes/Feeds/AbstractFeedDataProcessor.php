<?php
/**
 * Created by PhpStorm.
 * User: Zebimax
 * Date: 03.03.15
 * Time: 17:32
 */

namespace Feeds;


use Console\AbstractFeed;

abstract class AbstractFeedDataProcessor
{
    /**
     * @var AbstractFeed
     */
    protected $feed;
    protected $config;

    abstract protected function process();

    public function __construct(array $config = array())
    {
        $this->config = $config;
    }

    public final function processFeed(AbstractFeed $feed)
    {
        $this->feed = $feed;
        $this->process();
    }

    protected function getConfig($key, $default = null)
    {
        $result = $default;
        if (isset($this->config[$key])) {
            $result = $this->config[$key];
        }
        return $result;
    }
}