<?php

namespace Plugins\Controller\Colors;


interface ColorsInterface
{
    function getColoredString($string, $foreGroundColor = null, $backGroundColor = null);
}