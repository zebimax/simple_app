<?php

namespace Form;


use Form\Component\AbstractFormComponent;
use Form\Component\Field\AbstractField;
use Form\Component\Field\Input;
use Form\Component\Field\Select;

abstract class AbstractForm
{
    protected $fields = array();
    /**
     * @var AbstractFormComponent[]
     */
    protected $formComponents = array();
    protected $action = '';
    protected $method = 'get';
    protected $name;
    protected $componentsGlue = '';

    protected $formTemplate = '<form name="%s" action="%s" method="%s">%s</form>';

    /**
     * @param array $formOptions
     * @param string $action
     * @param string $method
     */
    public function __construct(array $formOptions = array(), $action = '', $method = 'post')
    {
        $this->setAction($action);
        $this->setMethod($method);
        foreach ($formOptions as $componentParams) {
            if (isset($componentParams['name'])) {
                $params = array();
                if (isset($componentParams['params']) && is_array($componentParams['params'])) {
                    $params = $componentParams['params'];
                }
                $this->setComponent($componentParams['name'], $params);
            }
        }

    }

    /**
     * @param $name
     * @param array $params
     */
    public function setComponent($name, array $params = array())
    {
        if (in_array($name, $this->fields)) {
            $this->formComponents[] = $this->createComponent($name, $params);
        }
    }

    /**
     * @return string
     */
    public function getAction()
    {
        return $this->action;
    }

    /**
     * @param string $action
     */
    public function setAction($action)
    {
        $this->action = $action;
    }

    /**
     * @return string
     */
    public function getMethod()
    {
        return $this->method;
    }

    /**
     * @param string $method
     */
    public function setMethod($method)
    {
        $this->method = $method;
    }

    /**
     * @return mixed
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param mixed $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * @return array
     */
    public function getFormComponents()
    {
        return $this->formComponents;
    }

    /**
     * @return string
     */
    public function make()
    {
        return sprintf(
            $this->formTemplate,
            $this->getName(),
            $this->getAction(),
            $this->getMethod(),
            $this->makeComponents()
        );
    }

    /**
     * @param $name
     * @param array $params
     * @return AbstractFormComponent
     */
    abstract protected function createComponent($name, array $params = array());

    /**
     * @param array $options
     * @return array
     */
    protected function makeComponents(array $options = array())
    {
        return array_reduce(
            $this->formComponents,
            function($carry, $item) {
                /** @var AbstractFormComponent $item */
                if (method_exists($item, 'make')) {
                    $carry .= $item->make();
                }
                return $carry;
            },
            ''
        );
    }

    /**
     * @param array $params
     * @return array
     * @throws \Exception
     */
    protected function checkSelectOptions(array $params)
    {
        if (!isset($params[Select::SELECT_OPTIONS]) || !is_array($params[Select::SELECT_OPTIONS])) {
            throw new \Exception('Options not specified');
        }
        return $params;
    }


    /**
     * @param array $params
     * @param $name
     * @return Input
     */
    protected function getStandardInput(array $params, $name)
    {
        $value = isset($params[AbstractField::FIELD_VALUE])
            ? $params[AbstractField::FIELD_VALUE]
            : '';
        $fieldParams = array_merge($params, array(
            AbstractFormComponent::COMPONENT_NAME => $name,
            AbstractField::FIELD_ID => $name,
            AbstractField::FIELD_TYPE => Input::TEXT_TYPE,
            AbstractField::FIELD_CLASS => '',
            AbstractField::FIELD_VALUE => htmlspecialchars($value),
            AbstractField::FIELD_ATTRIBUTES => array(),
        ));
        return new Input($fieldParams);
    }
}