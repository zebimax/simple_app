<?php

namespace Form\Component\Field;


class Select extends AbstractField
{
    const SELECT_OPTIONS = 'options';
    const SELECT_SELECTED = 'selected';
    const SELECT_BEFORE = 'before';
    const SELECT_AFTER = 'after';

    protected $tag = 'select';
    protected $options = array();
    protected $template = '<select name="%s" id="%s" class="%s" %s>%s</select>';
    protected $selected;

    protected $before = '';
    protected $after = '';

    public function __construct(array $options)
    {
        if (isset($options[self::SELECT_OPTIONS]) && is_array($options[self::SELECT_OPTIONS])) {
            $this->options = $options[self::SELECT_OPTIONS];
            unset($options[self::SELECT_OPTIONS]);
        }
        if (isset($options[self::SELECT_SELECTED])) {
            $this->selected = $options[self::SELECT_SELECTED];
            unset($options[self::SELECT_SELECTED]);
        }
        if (isset($options[self::SELECT_BEFORE])) {
            $this->before = $options[self::SELECT_BEFORE];
            unset($options[self::SELECT_BEFORE]);
        }
        if (isset($options[self::SELECT_AFTER])) {
            $this->after = $options[self::SELECT_AFTER];
            unset($options[self::SELECT_AFTER]);
        }
        parent::__construct($options);
    }

    /**
     * @param string $before
     */
    public function setBefore($before)
    {
        $this->before = $before;
    }

    /**
     * @param string $after
     */
    public function setAfter($after)
    {
        $this->after = $after;
    }

    protected function getTemplateParameters()
    {
        return array(
            $this->getName(),
            $this->getId(),
            $this->getClass(),
            $this->makeAttributes(),
            $this->makeOptions()
        );
    }

    protected function makeOptions()
    {
        $selected = $this->selected;
        $before = $this->before;
        $after = $this->after;
        return array_reduce(
            $this->options,
            function($carry, $item) use ($selected, $before, $after) {
                $carry .= sprintf(
                    '<option value="%s"%s>%s%s%s</option>',
                    $item['value'],
                    $item['value'] === $selected ? ' selected' : '',
                    $before,
                    $item['name'],
                    $after
                );
                return $carry;
            },
            ''
        );
    }
}