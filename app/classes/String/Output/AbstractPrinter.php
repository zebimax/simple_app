<?php
/**
 * Created by PhpStorm.
 * User: Zebimax
 * Date: 27.02.15
 * Time: 12:28
 */

namespace String\Output;


use String\Translate\Interfaces\TranslatorAwareInterface;
use String\Translate\Interfaces\TranslatorInterface;

abstract class AbstractPrinter implements TranslatorAwareInterface
{
    /**@var TranslatorInterface */
    protected $translator;

    /**
     * @param TranslatorInterface $translatorInterface
     */
    function setTranslator(TranslatorInterface $translatorInterface)
    {
        $this->translator = $translatorInterface;
    }

    /**
     * @param $string
     * @return mixed
     */
    protected function translate($string)
    {
        return $this->translator
            ? $this->translator->translate($string)
            : $string;
    }
}