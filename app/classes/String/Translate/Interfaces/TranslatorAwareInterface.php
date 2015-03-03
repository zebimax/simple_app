<?php
/**
 * Created by PhpStorm.
 * User: Zebimax
 * Date: 27.02.15
 * Time: 12:27
 */

namespace String\Translate\Interfaces;


interface TranslatorAwareInterface
{
    function setTranslator(TranslatorInterface $translatorInterface);
}