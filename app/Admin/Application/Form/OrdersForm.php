<?php
/**
 * Created by PhpStorm.
 * User: Zebimax
 * Date: 27.02.15
 * Time: 19:02
 */

namespace Admin\Application\Form;


use Form\AbstractForm;
use Form\Component\AbstractFormComponent;
use Form\Component\TextComponent;

class OrdersForm extends AbstractForm
{
    const TITLE = 'title';

    protected $name = 'orders';
    protected $fields = array(self::TITLE);

    /**
     * @param $name
     * @param array $params
     * @return AbstractFormComponent
     */
    protected function createComponent($name, array $params = array())
    {
        if (empty($this->formComponents)) {
            $text = isset($params[TextComponent::TEXT]) ? $params[TextComponent::TEXT] : '';
            return new TextComponent(self::TITLE, $text);
        }
        return false;
    }

    /**
     * @param AbstractFormComponent $component
     * @return $this
     */
    public function addComponent(AbstractFormComponent $component)
    {
        array_push($this->formComponents, $component);
        return $this;
    }

    /**
     * @param bool $last
     * @return $this
     */
    public function removeComponent($last = true)
    {
        $last
            ? array_pop($this->formComponents)
            : array_shift($this->formComponents);
        return $this;
    }

    /**
     * @param $componentI
     * @param $newValue
     * @param string $property
     * @return $this
     */
    public function changeComponent($componentI, $newValue, $property = AbstractFormComponent::COMPONENT_NAME)
    {
        if (isset($this->formComponents[$componentI])) {
            $component = $this->formComponents[$componentI];
            switch ($property) {
                case AbstractFormComponent::COMPONENT_NAME :
                    $component->setName($newValue);
                    break;
                case  TextComponent::TEXT :
                    if ($component instanceof TextComponent) {
                        $component->setText($newValue);
                    }
                    break;
                default :
                    break;
            }
        }
        return $this;
    }
}