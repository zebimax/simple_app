<?php
/**
 * Created by PhpStorm.
 * User: Zebimax
 * Date: 27.02.15
 * Time: 12:15
 */

namespace String\Output\Html;


use String\Output\AbstractPrinter;

class Printer extends AbstractPrinter
{
    /**
     * @param $string
     * @return string
     */
    public function printString($string)
    {
        return $this->sanitize($this->translate($string));
    }

    /**
     * @param $string
     * @return string
     */
    protected function sanitize($string)
    {
        return htmlspecialchars($string);
    }
}