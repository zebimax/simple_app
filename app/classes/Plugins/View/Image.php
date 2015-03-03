<?php
/**
 * Created by PhpStorm.
 * User: Zebimax
 * Date: 27.02.15
 * Time: 12:05
 */

namespace Plugins\View;


use String\Output\Html\Printer;

class Image
{
    const ALT        = 'alt';
    const TITLE      = 'title';

    protected $template = '<img src="%s"%s%s%s>';

    /** @var Printer */
    protected $stringPrinter;

    private $strict;

    public function __construct(Printer $printer)
    {
        $this->stringPrinter = $printer;
    }

    public function __invoke($src, array $params = array(), $strict = false)
    {
        $this->strict = $strict;
        $alt = $title = '';
        if (isset($params[self::ALT])) {
            $alt = sprintf(' alt="%s"', $this->stringPrinter->printString($params[self::ALT]));
            unset($params[self::ALT]);
        }

        if (isset($params[self::TITLE])) {
            $title = sprintf(' alt="%s"', $this->stringPrinter->printString($params[self::TITLE]));
            unset($params[self::TITLE]);
        }

        return sprintf(
            $this->template,
            $this->stringPrinter->printString($src),
            $alt,
            $title,
            $this->makeAttributes($params)
        );
    }

    private function makeAttributes(array $attributes = array())
    {
        $keys = array_keys($attributes);
        if ($this->strict) {
            $printer = $this->stringPrinter;
            $printFunc = function ($key, $val) use ($printer) {
                return sprintf(' %s="%s"', $key, $printer->printString($val));
            };
        } else {
            $printFunc = function ($key, $val) {
                return sprintf(' %s="%s"', $key, $val);
            };
        }

        $attributesReduce = array_reduce(
            $attributes,
            function ($carry, $item) use ($keys, $printFunc) {
                $carry['attr'] .= $printFunc($keys[$carry['i']], $item);
                $carry['i']++;
                return $carry;
            },
            array('i' => 0, 'attr' => '')
        );

        return $this->strict
            ? $this->stringPrinter->printString($attributesReduce['attr'])
            : $attributesReduce['attr'];
    }
}