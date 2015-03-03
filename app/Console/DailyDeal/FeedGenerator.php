<?php
/**
 * Created by PhpStorm.
 * User: Zebimax
 * Date: 02.03.15
 * Time: 17:06
 */

namespace Console\DailyDeal;


use Feeds\FeedInterface;
use Solarium\Exception\InvalidArgumentException;

class FeedGenerator
{
    /**
     * @var FeedInterface[]
     */
    private $feeds;

    /**
     * @param array $feeds
     */
    public function __construct(array $feeds)
    {
        foreach ($feeds as $feed) {
            if (!$feed instanceof FeedInterface) {
                throw new InvalidArgumentException('Feed generator must be configured with array of FeedInterface instances');
            }
        }
        $this->feeds = $feeds;
    }

    /**
     * @param array $data
     * @param callable $preSaveDataCallback
     * @return $this
     */
    public function saveFeedData(array $data, callable $preSaveDataCallback = null)
    {
        $preSaveData = $preSaveDataCallback
            ? $preSaveDataCallback
            : function () {};

        foreach ($this->feeds as $feed) {
            $feed
                ->setData($data)
                ->preSaveData($preSaveData)
                ->saveData();
        }
        return $this;
    }

    /**
     * @param callable $afterFeedGeneratedCallback
     */
    public function closeFeeds(callable $afterFeedGeneratedCallback = null)
    {
        $afterFeedGenerated = $afterFeedGeneratedCallback
            ? $afterFeedGeneratedCallback
            : function () {};
        foreach ($this->feeds as $feed) {
            $feed->close($afterFeedGenerated);
        }

    }

    /**
     * @param callable $feedCallback
     */
    public function prepareFeeds(callable $feedCallback = null)
    {
        foreach ($this->feeds as $feed) {
            if ($feedCallback) {
                $feedCallback($feed);
            }
            $feed->prepareFeed();
        }

    }
}