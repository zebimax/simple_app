<?php

namespace Form\Component\Field;


use Form\Component\AbstractFormComponent;

abstract class AbstractField extends AbstractFormComponent
{
    const FIELD_CLASS = 'class';
    const FIELD_VALUE = 'value';
    const FIELD_ID = 'id';
    const FIELD_ATTRIBUTES = 'attributes';
    const FIELD_TYPE = 'type';

    protected $class;
    protected $value;
    protected $id;
    protected $attributes = array();
    protected $type;
    protected $template = '<%s type="%s" name="%s" id="%s" class="%s" value="%s" %s/>';

    protected $tag;

    public function __construct(array $params = array())
    {
        foreach ($params as $key => $value) {
            switch ($key) {
                case self::COMPONENT_NAME:
                    $this->setName($value);
                    break;
                case self::FIELD_CLASS:
                    $this->setClass($value);
                    break;
                case self::FIELD_VALUE:
                    $this->setValue($value);
                    break;
                case self::FIELD_ID:
                    $this->setId($value);
                    break;
                case self::FIELD_ATTRIBUTES:
                    $this->setAttributes($value);
                    break;
                case self::FIELD_TYPE:
                    $this->setType($value);
                    break;
                default:
                    break;
            }
        }

    }


    /**
     * @return mixed
     */
    public function getClass()
    {
        return $this->class;
    }

    /**
     * @param mixed $class
     */
    public function setClass($class)
    {
        $this->class = $class;
    }

    /**
     * @return mixed
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * @param mixed $value
     */
    public function setValue($value)
    {
        $this->value = $value;
    }

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param mixed $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * @return array
     */
    public function getAttributes()
    {
        return $this->attributes;
    }

    /**
     * @param array $attributes
     */
    public function setAttributes(array $attributes = array())
    {
        $this->attributes = $attributes;
    }

    /**
     * @return mixed
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @param mixed $type
     */
    public function setType($type)
    {
        $this->type = $type;
    }

    /**
     * @return mixed
     */
    public function getTag()
    {
        return $this->tag;
    }

    /**
     * @param mixed $tag
     */
    public function setTag($tag)
    {
        $this->tag = $tag;
    }

    /**
     * @return string
     */
    public function makeField()
    {
        return vsprintf($this->template, $this->getTemplateParameters());
    }

    public function make()
    {
        return self::makeField();
    }

    /**
     * @return array
     */
    protected function getTemplateParameters()
    {
        $tag = $this->getTag();
        return array(
            $tag,
            $this->getType(),
            $this->getName(),
            $this->getId(),
            $this->getClass(),
            $this->getValue(),
            $this->makeAttributes(),
            $tag
        );
    }

    protected function makeAttributes()
    {
        $str = '';
        foreach ($this->attributes as $key => $value) {
            $str .= sprintf('%s="%s" ', $key, $value);
        }
        return $str;
    }
}