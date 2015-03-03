<?php
/**
 * Created by PhpStorm.
 * User: Zebimax
 * Date: 27.02.15
 * Time: 16:10
 */

namespace Admin\Application\Form;


use Form\AbstractForm;
use Form\Component\AbstractFormComponent;
use Form\Component\Field\AbstractField;
use Form\Component\Field\Select;

class LanguageForm extends AbstractForm
{
    const LANGUAGES = 'languages';

    protected $name = 'adminlanguage';
    protected $fields = array(self::LANGUAGES);

    /**
     * @param $fieldName
     * @param array $params
     * @return bool|Select
     * @throws \Exception
     */
    protected function createComponent($fieldName, array $params = array())
    {
        switch ($fieldName) {
            case self::LANGUAGES :
                $params = $this->checkSelectOptions($params);
                $fieldParams = array_merge($params, array(
                    AbstractField::FIELD_ATTRIBUTES => array(
                        'onchange' => 'this.form.submit();'
                    ),
                    AbstractFormComponent::COMPONENT_NAME => 'language',
                ));
                return new Select($fieldParams);
                break;
            default:
                return false;
                break;
        }
    }
}