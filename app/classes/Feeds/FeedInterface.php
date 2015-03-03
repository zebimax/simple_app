<?php
/**
 * Created by PhpStorm.
 * User: Zebimax
 * Date: 02.03.15
 * Time: 18:35
 */

namespace Feeds;


interface FeedInterface
{
    function saveData();
    function prepareFeed();
    function getId();
    function getSavePoint();

    /**
     * @param callable $callback
     * @return mixed
     */
    function close(callable $callback);

    /**
     * @param array $data
     * @return $this
     */
    function setData(array $data);

    /**
     * @param callable $preSaveDataCallback
     * @return $this
     */
    function preSaveData(callable $preSaveDataCallback);
}