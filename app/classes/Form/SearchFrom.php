<?php

namespace Form;


use Form\Component\AbstractFormComponent;
use Form\Component\Field\AbstractField;
use Form\Component\Field\Input;
use Form\Component\Field\SearchForm\CheckBoxFilters;
use Form\Component\Field\Select;
use Form\Component\TextComponent;

class SearchFrom extends AbstractForm
{
    const KEYWORDS = 'keywords';
    const SORT_OPTIONS = 'sort_options';
    const LIMIT_OPTIONS = 'limit_options';
    const SEARCH_SUBMIT = 'search_submit';
    const END_DIV = 'end_div';
    const START_SEARCH_BAR_DIV = 'start_search_bar_div';
    const CLEAR_DIV = 'clear_div';
    const CHECK_BOX_FILTERS = 'check_box_filters';
    const PAGE = 'page';
    const LIMIT = 'limit';
    const SORT = 'sort';
    const BRAND_OPEN = 'brand_open';
    const DISCOUNTS_OPEN = 'discounts_open';
    const BRANDS_FILTER = 'brands_filter';
    const DISCOUNTS_FILTER = 'discounts_filter';

    const CATEGORY = 'category';
    const MANUFACTURER = 'm';

    protected $name = 'quick_find3';
    protected $fields = array(
        self::KEYWORDS,
        self::SEARCH_SUBMIT,
        self::SORT_OPTIONS,
        self::LIMIT_OPTIONS,
        self::START_SEARCH_BAR_DIV,
        self::END_DIV,
        self::CLEAR_DIV,
        self::CHECK_BOX_FILTERS,
        self::PAGE,
        self::LIMIT,
        self::SORT,
        self::BRAND_OPEN,
        self::DISCOUNTS_OPEN,
        self::BRANDS_FILTER,
        self::DISCOUNTS_FILTER,
        self::CATEGORY,
        self::MANUFACTURER
    );

    public function __construct(array $formOptions = array(), $action = '', $method = 'get')
    {
        parent::__construct($formOptions, $action, $method);
    }

    protected function createComponent($fieldName, array $params = array())
    {
        switch ($fieldName) {
            case self::KEYWORDS :
                $value = isset($params[AbstractField::FIELD_VALUE])
                    ? $params[AbstractField::FIELD_VALUE]
                    : '';
                $fieldParams = array_merge($params, array(
                    AbstractFormComponent::COMPONENT_NAME => self::KEYWORDS,
                    AbstractField::FIELD_ID => 'ckeywords',
                    AbstractField::FIELD_TYPE => Input::SEARCH_TYPE,
                    AbstractField::FIELD_CLASS => 'input-search',
                    AbstractField::FIELD_VALUE => htmlspecialchars($value),
                    AbstractField::FIELD_ATTRIBUTES => array(
                        'autocomplete' => 'off',
                        'role' => 'textbox',
                        'aria-autocomplete' => 'list',
                        'aria-haspopup' => 'true',
                        'placeholder' => MAIN_SEARCH,
                        'onkeydown' => 'if(event.keyCode==13)searchProducts();',
                        'onchange' => 'clearKeyWordsInput();'
                    ),
                ));
                return new Input($fieldParams);
                break;
            case self::SORT_OPTIONS :
                $params = $this->checkSelectOptions($params);
                $fieldParams = array_merge($params, array(
                    AbstractField::FIELD_ATTRIBUTES => array(
                        'style' => 'width:120px;',
                        'onchange' => 'loadProducts();'
                    ),
                    AbstractFormComponent::COMPONENT_NAME => 'sort',
                    AbstractField::FIELD_ID => 'sort'
                ));
                return new Select($fieldParams);
                break;
            case self::LIMIT_OPTIONS:
                $params = $this->checkSelectOptions($params);
                $fieldParams = array_merge($params, array(
                    AbstractField::COMPONENT_NAME => 'limit',
                    AbstractField::FIELD_ATTRIBUTES => array(
                        'style' => 'width:120px;',
                        'onchange' => 'searchProducts();'
                    ),
                ));
                return new Select($fieldParams);
                break;
            case self::SEARCH_SUBMIT:
                $fieldParams = array_merge($params, array(
                    AbstractFormComponent::COMPONENT_NAME => self::SEARCH_SUBMIT,
                    AbstractField::FIELD_TYPE => Input::SUBMIT_TYPE,
                    AbstractField::FIELD_VALUE => 'zoek',
                    AbstractField::FIELD_ATTRIBUTES => array(
                        'onclick' => 'searchProducts();'
                    ),
                ));
                return new Input($fieldParams);
                break;
            case self::START_SEARCH_BAR_DIV:
                return new TextComponent(self::START_SEARCH_BAR_DIV, '<div class="search_bar">');
                break;
            case self::END_DIV:
                return new TextComponent(self::END_DIV, '</div>');
                break;
            case self::CLEAR_DIV:
                return new TextComponent(self::CLEAR_DIV, '<div class="clear"></div>');
                break;
            case self::CHECK_BOX_FILTERS:
                return new CheckBoxFilters($params);
                break;
            case self::PAGE:
                $value = isset($params[AbstractField::FIELD_VALUE])
                    ? $params[AbstractField::FIELD_VALUE]
                    : '';
                $fieldParams = array_merge($params, array(
                    AbstractFormComponent::COMPONENT_NAME => self::PAGE,
                    AbstractField::FIELD_ID => self::PAGE,
                    AbstractField::FIELD_TYPE => Input::HIDDEN_TYPE,
                    AbstractField::FIELD_VALUE => (int)($value),
                ));
                return new Input($fieldParams);
                break;
            case self::LIMIT:
                $value = isset($params[AbstractField::FIELD_VALUE])
                    ? $params[AbstractField::FIELD_VALUE]
                    : '';
                $fieldParams = array_merge($params, array(
                    AbstractFormComponent::COMPONENT_NAME => self::LIMIT,
                    AbstractField::FIELD_ID => self::LIMIT,
                    AbstractField::FIELD_TYPE => Input::HIDDEN_TYPE,
                    AbstractField::FIELD_VALUE => (int)($value),
                ));
                return new Input($fieldParams);
                break;
            case self::SORT:
                $value = isset($params[AbstractField::FIELD_VALUE])
                    ? $params[AbstractField::FIELD_VALUE]
                    : '';
                $fieldParams = array_merge($params, array(
                    AbstractFormComponent::COMPONENT_NAME => self::SORT,
                    AbstractField::FIELD_ID => self::SORT,
                    AbstractField::FIELD_TYPE => Input::HIDDEN_TYPE,
                    AbstractField::FIELD_VALUE => $value,
                ));
                return new Input($fieldParams);
                break;
            case self::BRAND_OPEN:
                $value = isset($params[AbstractField::FIELD_VALUE])
                    ? $params[AbstractField::FIELD_VALUE]
                    : '';
                $fieldParams = array_merge($params, array(
                    AbstractFormComponent::COMPONENT_NAME => self::BRAND_OPEN,
                    AbstractField::FIELD_ID => self::BRAND_OPEN,
                    AbstractField::FIELD_TYPE => Input::HIDDEN_TYPE,
                    AbstractField::FIELD_VALUE => (int)($value),
                ));
                return new Input($fieldParams);
                break;
            case self::DISCOUNTS_OPEN:
                $value = isset($params[AbstractField::FIELD_VALUE])
                    ? $params[AbstractField::FIELD_VALUE]
                    : '';
                $fieldParams = array_merge($params, array(
                    AbstractFormComponent::COMPONENT_NAME => self::DISCOUNTS_OPEN,
                    AbstractField::FIELD_ID => self::DISCOUNTS_OPEN,
                    AbstractField::FIELD_TYPE => Input::HIDDEN_TYPE,
                    AbstractField::FIELD_VALUE => (int)($value),
                ));
                return new Input($fieldParams);
                break;
            case self::BRANDS_FILTER:
                $value = isset($params[AbstractField::FIELD_VALUE])
                    ? $params[AbstractField::FIELD_VALUE]
                    : array();
                $fieldParams = array_merge($params, array(
                    AbstractFormComponent::COMPONENT_NAME => self::BRANDS_FILTER,
                    AbstractField::FIELD_ID => self::BRANDS_FILTER,
                    AbstractField::FIELD_TYPE => Input::HIDDEN_TYPE,
                    AbstractField::FIELD_VALUE => implode(',', $value),
                ));
                return new Input($fieldParams);
                break;
            case self::DISCOUNTS_FILTER:
                $value = isset($params[AbstractField::FIELD_VALUE])
                    ? $params[AbstractField::FIELD_VALUE]
                    : array();
                $fieldParams = array_merge($params, array(
                    AbstractFormComponent::COMPONENT_NAME => self::DISCOUNTS_FILTER,
                    AbstractField::FIELD_ID => self::DISCOUNTS_FILTER,
                    AbstractField::FIELD_TYPE => Input::HIDDEN_TYPE,
                    AbstractField::FIELD_VALUE => implode(',', $value),
                ));
                return new Input($fieldParams);
                break;
            case self::CATEGORY:
                $value = isset($params[AbstractField::FIELD_VALUE])
                    ? $params[AbstractField::FIELD_VALUE]
                    : '';
                $fieldParams = array_merge($params, array(
                    AbstractFormComponent::COMPONENT_NAME => self::CATEGORY,
                    AbstractField::FIELD_ID => self::CATEGORY,
                    AbstractField::FIELD_TYPE => Input::HIDDEN_TYPE,
                    AbstractField::FIELD_VALUE => $value,
                ));
                return new Input($fieldParams);
                break;
            case self::MANUFACTURER:
                $value = isset($params[AbstractField::FIELD_VALUE])
                    ? $params[AbstractField::FIELD_VALUE]
                    : '';
                $fieldParams = array_merge($params, array(
                    AbstractFormComponent::COMPONENT_NAME => self::MANUFACTURER,
                    AbstractField::FIELD_ID => self::MANUFACTURER,
                    AbstractField::FIELD_TYPE => Input::HIDDEN_TYPE,
                    AbstractField::FIELD_VALUE => $value
                ));
                return new Input($fieldParams);
                break;
            default:
                break;
        }
    }

    /**
     * @param array $options
     * @return string
     */
    protected function makeComponents(array $options = array())
    {
        return parent::makeComponents(array('initial' => '<div class="clear"></div>'));
    }
}