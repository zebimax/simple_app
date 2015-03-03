<?php

namespace Plugins\Controller\Colors;


class Colors implements ColorsInterface {
    private $foreGroundColors = array();
    private $backGroundColors = array();

    public function __construct()
    {
        // Set up shell colors
        $this->foreGroundColors['black'] = '0;30';
        $this->foreGroundColors['dark_gray'] = '1;30';
        $this->foreGroundColors['blue'] = '0;34';
        $this->foreGroundColors['light_blue'] = '1;34';
        $this->foreGroundColors['green'] = '0;32';
        $this->foreGroundColors['light_green'] = '1;32';
        $this->foreGroundColors['cyan'] = '0;36';
        $this->foreGroundColors['light_cyan'] = '1;36';
        $this->foreGroundColors['red'] = '0;31';
        $this->foreGroundColors['light_red'] = '1;31';
        $this->foreGroundColors['purple'] = '0;35';
        $this->foreGroundColors['light_purple'] = '1;35';
        $this->foreGroundColors['brown'] = '0;33';
        $this->foreGroundColors['yellow'] = '1;33';
        $this->foreGroundColors['light_gray'] = '0;37';
        $this->foreGroundColors['white'] = '1;37';

        $this->backGroundColors['black'] = '40';
        $this->backGroundColors['red'] = '41';
        $this->backGroundColors['green'] = '42';
        $this->backGroundColors['yellow'] = '43';
        $this->backGroundColors['blue'] = '44';
        $this->backGroundColors['magenta'] = '45';
        $this->backGroundColors['cyan'] = '46';
        $this->backGroundColors['light_gray'] = '47';
    }

    // Returns colored string
    public function getColoredString($string, $foreGroundColor = null, $backGroundColor = null)
    {
        $coloredString = "";

        // Check if given foreground color found
        if (isset($this->foreGroundColors[$foreGroundColor])) {
            $coloredString .= "\033[" . $this->foreGroundColors[$foreGroundColor] . "m";
        }
        // Check if given background color found
        if (isset($this->backGroundColors[$backGroundColor])) {
            $coloredString .= "\033[" . $this->backGroundColors[$backGroundColor] . "m";
        }

        // Add string and end coloring
        $coloredString .=  $string . "\033[0m";

        return $coloredString;
    }

    // Returns all foreground color names
    public function getForegroundColors()
    {
        return array_keys($this->foreGroundColors);
    }

    // Returns all background color names
    public function getBackgroundColors()
    {
        return array_keys($this->backGroundColors);
    }
}