<?php

namespace Form\Component;


class TextComponent extends AbstractFormComponent
{
    protected $text;
    const TEXT = 'text';

    /**
     * @param $name
     * @param $text
     */
    public function __construct($name, $text)
    {
        $this->name = $name;
        $this->text = $text;
    }

    /**
     * @param mixed $text
     */
    public function setText($text)
    {
        $this->text = $text;
    }

    /**
     * @return mixed
     */
    public function make()
    {
        return $this->text;
    }
}