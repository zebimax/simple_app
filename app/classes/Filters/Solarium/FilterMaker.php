<?php

namespace Filters\Solarium;


use Solarium\Core\Query\Helper;

class FilterMaker
{
    private $filters = array();

    private $delimiter = ',';

    /** @var Helper */
    private $helper;

    /**
     * @param array $filters
     * @param null $helper
     */
    public function __construct(array $filters = array(), $helper = null)
    {
        $this->helper = $helper;

        foreach ($filters as $field => $filterParams) {
            if (isset($filterParams['type'])) {
                $this->createFilter($filterParams['type'], $field, $filterParams);
            }
        }
    }

    /**
     * @param $type
     * @param $field
     * @param $filterParams
     */
    protected function createFilter($type, $field, $filterParams)
    {
        if ($filter = $this->makeFilter($type, $field, $filterParams)) {
            $this->filters[$field] = $filter;
        }
    }

    public function get($field)
    {
        $filter = isset($this->filters[$field]) ? $this->filters[$field] : '';
        return $filter;
    }

    public function makeFilter($type, $field, $filterParams)
    {
        $filter = '';
        if (method_exists($this, $type)) {
            if ($created = $this->$type($filterParams)) {
                $filter = sprintf('%s:%s', $field, $created);
            }
        }
        return $filter;
    }

    private function in(array $filterParams)
    {
        if (isset($filterParams['value'])) {
            $value = $filterParams['value'];
            if (is_array($value)) {
                $valueArray = $this->applyConstraints($filterParams, $value);
            } else {
                $delimiter = isset($filterParams['delimiter']) ? $filterParams['delimiter'] : $this->delimiter;
                $valueArray = $this->applyConstraints($filterParams, explode($delimiter, $value));
            }
        } else {
            $value = $filterParams;
            $valueArray = (is_array($value)) ? $value : explode($this->delimiter, $value);
        }

        return !empty($valueArray) ? sprintf('(%s)', implode(" OR ", $valueArray)) : false;
    }

    private function range($filterParams)
    {
        $value = isset($filterParams['value']) ? $filterParams['value'] : $filterParams;
        if (!isset($value[0]) || !isset($value[1]) || (!is_numeric($value[0]) && !is_numeric($value[1]))) {
            return false;
        }
        return sprintf('[%s TO %s]', $value[0], $value[1]);
    }

    private function orRanges(array $filterParams)
    {
        $filter = '';
        $filters = array();
        foreach ($filterParams as $orRange) {
            $filters[] = $this->range($orRange);
        }
        return !empty($filters) ? implode(' OR ', $filters) : false;
    }

    private function id($filterParams)
    {
        return (int)$filterParams;
    }

    private function applyConstraints(array $params, $value)
    {
        $formattedValue = $value;
        $isArray = false;
        $constraintApplied = false;
        if (isset($params['constraints']) && is_array($params['constraints'])) {
            if (is_array($value)) {
                $isArray = true;
            }
            foreach ($params['constraints'] as $constraint) {
                $formattedValue = $value;
                if (method_exists($this, 'constraint' . ucfirst($constraint))) {
                    $constraintApplied = true;
                    $formattedValue = array_filter((array)$formattedValue, array($this, 'constraint' . $constraint));
                }
            }
        }
        if ($isArray) {
            return $formattedValue;
        } elseif (isset($formattedValue[0]) && $constraintApplied) {
            return $formattedValue[0];
        } elseif ($constraintApplied) {
            return false;
        } else {
            return $formattedValue;
        }
    }

    private function constraintNotEmpty($value)
    {
        return !empty($value);
    }

    private function constraintInt($value)
    {
        return (int)$value;
    }

    private function constraintString($value)
    {
        return (string)($value);
    }

    private function categoryFacet($filterParams)
    {
        $categoryId = isset($filterParams['value']) ? $filterParams['value'] : $filterParams;
        $cat_tree_categories = array();
        $cat_tree = tep_generate_category_path($categoryId);
        $cat_tree = (array_reverse($cat_tree[0]));
        $cat_tree_value_a = array();
        while (list(,$value) = each($cat_tree)) {
            $cat_tree_value_a[] = str_replace(array("&", "/", " ", ","), array("|", "", "_", ""), $value['text']);
        }
        $listed_cats = array();
        while (list($key,$value) = each($cat_tree_value_a)) {
            $listed_cats[] = $value;
            $cat_tree_categories[] = $key . "/" . implode("/", $listed_cats);
        }
        $cat_tree_categories = array_unique($cat_tree_categories);
        $category_facet = array_pop($cat_tree_categories);
        $n = ((int) substr_replace($category_facet, '', 1)) + 1;
        $category_facet_prefix = substr_replace($category_facet, $n, 0, 1);
        return $this->helper->escapeTerm($category_facet);
        //return array('category_facet' => $category_facet, 'category_facet_prefix' => $category_facet_prefix);
    }
}