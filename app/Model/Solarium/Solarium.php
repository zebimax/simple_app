<?php
namespace Model\Solarium;


use Filters\Solarium\FilterMaker;
use Model\AbstractModel;
use Solarium\Core\Client\Client;
use Solarium\Plugin\CustomizeRequest\CustomizeRequest;
use Solarium\QueryType\Select\Query\Query;
use Solarium\QueryType\Select\Result\Result;
use Solarium\QueryType\Select\Result\Spellcheck\Suggestion;

class Solarium extends AbstractModel
{
    const DEFAULT_SUGGEST_DOCUMENTS_LIMIT = 10;
    const DEFAULT_SUGGEST_HANDLER = 'sbsuggest';
    const DEFAULT_SUGGESTER_COMPONENT = 'sbsuggester';
    const SB_SUGGESTER_LIMIT_NAME = 'sbsuggester.mps';
    const DEFAULT_SUGGESTING_LIMIT = 5;

    /** @var Client */
    private $client;

    private $limit = 100;

    protected $sortMap = array(
        'a' => array(array('final_price', 'asc')),
        'b' => array(array('final_price', 'desc')),
        'c' => array(array('products_name_sort', 'asc')),
        'd' => array(array('products_ordered', 'desc'), array('products_name_sort', 'asc')),
        'e' => array(array('products_has_discount', 'desc'), array('products_ordered', 'desc')),
        'f' => array(array('products_name_sort', 'desc')),
        'default' => array(array('products_ordered', 'desc'))
    );

    private $discountsSeacrhMap = array(
        1 => array('*', 10),
        2 => array(11, 30),
        3 => array(31, 50),
        4 => array(50, '*')
    );

    private $solrReplaces = array(
        array('&', '/', ' ', ','),
        array('|', '', '_', '')
    );


    public function __construct($langCode = 'en')
    {
        $this->client = \Macaw::getConfig(
            'solarium.client',
            null,
            array('lang_code' => $langCode)
        );
    }

    /**
     * @param Suggestion[] $suggestions
     * @param string $uri
     * @return array
     */
    public function makeSuggestionsUrls(array $suggestions, $uri = 'advanced_search_result_v3.php')
    {
        $urls = array();
        $words = array();
        foreach ($suggestions as $suggestion) {
            $words = $words + $suggestion->getWords();
        }

        foreach ($words as $word) {
            $urls[] = sprintf(
                '<a href="%s">%s</a>',
                tep_href_link($uri, tep_get_all_get_params(array('keywords')) . 'keywords=' . urlencode($word['word'])),
                $word['word']
            );
        }

        return $urls;
    }

    /**
     * @param array $searchParams
     * @return array
     */
    public function getProducts($searchParams = array())
    {
        $result = array(
            'documents' => array(),
            'brands' => array(),
            'categories' => array(),
            'discounts' => array(),
            'count' => 0,
            'suggestions' => array(),
            'search_category' => array()
        );
        $initialParams = array(
            'brandFilter' => 0,
            'cKeyWords' => '',
            'category' => 0,
            'sort' => null,
            'special' => false,
            'stock' => false,
            'start' => 0,
            'limit' => $this->limit,
            'discounts' => 0,
            'exclude_categories' => true,
            'exclude_brands' => true,
            'add_discounts' => true,
            'spell_check' => false,
            'total' => true,
            'brand_id'
        );

        $params = array_merge($initialParams, $searchParams);
        $query = $this->client->createSelect();

        $filterMaker = new FilterMaker(array(
            'manufacturers_id' => array(
                'value' => $params['brandFilter'],
                'type' => 'in',
                'constraints' => array('int', 'notEmpty')
            )
        ), $query->getHelper());

        $query->setStart($params['start']);
        $query->setRows($params['limit']);

        $facetSet = $query->getFacetSet()->setLimit(-1);
        $categoryFacet = $facetSet->createFacetField('category_facet')->setField('category_facet')->setMincount(1);
        $facetSet->createFacetField('category_facet_names')->setField('category_facet_names')->setMincount(1);
        $brandFacet = $facetSet->createFacetField('manufacturers_name_literal')->setField('manufacturers_name_literal')->setMincount(1);
        if ($params['add_discounts']) {
            $facetSet->createFacetField('products_discount_percent')->setField('products_discount_percent')->setMincount(1);
            if ($params['discounts'] && is_array($params['discounts'])) {
                $discountsFilters = array();
                foreach ($params['discounts'] as $discountKey) {
                    $key = (int) $discountKey;
                    if (isset($this->discountsSeacrhMap[$key])) {
                        $tag = 'discounts_tag_' . $key;
                        $discountsTags[] = $tag;
                        $discountsFilters[] = $this->discountsSeacrhMap[$key];
                    }
                }
                if (
                    $discountsFilter = $filterMaker->makeFilter(
                        'orRanges',
                        'products_discount_percent',
                        $discountsFilters
                    )
                ) {
                    $query->createFilterQuery('products_discount_percent')
                        ->setQuery($discountsFilter)->addTag('products_discount_percent_tag');
                }

            }
        }

        if ($params['special']) {
            $query->createFilterQuery('specials_new_products_price')
                ->setQuery(
                    $filterMaker->makeFilter(
                        'range',
                        'specials_new_products_price',
                        array('0.01', '*')
                    )
                );
        }

        if ($params['stock']) {
            $query->createFilterQuery('products_quantity')
                ->setQuery(
                    $filterMaker->makeFilter(
                        'range',
                        'products_quantity',
                        array(1, '*'))
                );
        }

        if ($manufactureFilter = $filterMaker->get('manufacturers_id') && !$params['brand_id']) {
            $query->createFilterQuery('manufacturers_id')
                ->setQuery($filterMaker->get('manufacturers_id'))
                ->addTag('manufacturer_tag');
            if ($params['exclude_brands']) {
                $brandFacet->addExclude('manufacturer_tag');
            }
        }

        if ($params['category']) {
            $query->createFilterQuery('category_facet')
                ->setQuery('category_facet:' . $query->getHelper()->escapeTerm($params['category']))
                ->addTag('category_tag');
            $firstSlashPos = strpos($params['category'], '/');
            $level = substr($params['category'], 0, $firstSlashPos);
            $categoryPrefix = ++$level . '/' . substr($params['category'], $firstSlashPos + 1);
            if ($params['exclude_categories']) {
                $categoryFacet->addExcludes(array('category_tag'));

            }
        } else {
            $categoryPrefix = '0/';
        }

        $categoryFacet->setPrefix($categoryPrefix);

        if ($params['brand_id'] && !$manufactureFilter) {
            $query->createFilterQuery('manufacturers_id')
                ->setQuery($filterMaker->makeFilter('id', 'manufacturers_id', $params['brand_id']))
                ->addTag('manufacturer_tag');
            if ($params['exclude_brands']) {
                $brandFacet->addExclude('manufacturer_tag');
            }
        }

        if ($params['spell_check']) {
            $query->getSpellcheck()->setDictionary($params['spell_check'])->setCollate(true)->setExtendedResults(true);
        }

        $this->addSort($query, $params['sort']);


        /** @var CustomizeRequest $customizer */
        $customizer = $this->client->getPlugin('customizerequest');
        $customizer->createCustomization('qt')
            ->setType('param')
            ->setName('qt')
            ->setValue('dod');

        $customizer->createCustomization('qalt')
            ->setType('param')
            ->setName('q.alt')
            ->setValue('*:*');

        $query->setQuery($params['cKeyWords']);

        try {

            /** @var \Solarium\QueryType\Select\Result\Result $queryResult */
            $queryResult = $this->client->execute($query);
            $result['documents'] = $queryResult->getDocuments();
            $result['count'] = $queryResult->getNumFound();

            if ($params['cKeyWords'] && $params['total']) {
                $totalQuery = clone $query;
                $totalQuery->setQuery('*')->setStart(0)->setRows(0)->clearSorts()
                    ->removeComponent(Query::COMPONENT_FACETSET);
                /** @var \Solarium\QueryType\Select\Result\Result $totalQueryResult */
                $totalQueryResult = $this->client->execute($totalQuery);
                $result['total']  = $totalQueryResult->getNumFound();
            } else {
                $result['total'] = $result['count'];
            } //todo second query to solr it's not good, but ...

            $brands = $queryResult->getFacetSet()->getFacet('manufacturers_name_literal');
            $discounts = $queryResult->getFacetSet()->getFacet('products_discount_percent');
            $result['brands'] = is_array($brands) ? $brands : $brands->getValues();
            $result['categories'] = $this->filterCategories($queryResult, $params['category']);

            if ($params['add_discounts']) {
                $result['discounts'] = is_array($discounts) ? $this->mergeDiscounts($discounts) : $this->mergeDiscounts($discounts->getValues());
            }
            if ($params['spell_check'] && $spellCheck = $queryResult->getSpellcheck()) {
                $result['suggestions'] = $spellCheck->getSuggestions();

            }
        } catch (\Exception $e) {

        }

        return $result;
    }

    /**
     * @param $queryString
     * @param array $suggestParams
     * @return array
     */
    public function getSuggestions($queryString, array $suggestParams = array())
    {
        $initialParams = array(
            'limit' => self::DEFAULT_SUGGEST_DOCUMENTS_LIMIT,
            'handler' => self::DEFAULT_SUGGEST_HANDLER,
            'suggester_component' => self::DEFAULT_SUGGESTER_COMPONENT,
            'suggester_limit_param' => self::SB_SUGGESTER_LIMIT_NAME,
            'suggester_limit' => self::DEFAULT_SUGGESTING_LIMIT
        );

        $params = array_merge($initialParams, $suggestParams);
        $suggester = $this->client->createSelect(array('handler' => $params['handler']));
        $suggester
            ->setStart(0)
            ->setRows($params['limit'])
            ->setQuery($suggester->getHelper()->escapeTerm($queryString))
            ->setOmitHeader(true)
            ->addParam('indent', true);
            // ->addParam($params['suggester_limit_param'], $params['suggester_limit']);

        $suggestions = array();

        try {
            /** @var \Solarium\QueryType\Select\Result\Result $queryResult*/
            $queryResult = $this->client->execute($suggester);

//            $data = $queryResult->getData();
//            $suggesterResult = isset($data[$params['suggester_component']]) && is_array($data[$params['suggester_component']]) ?
//                array_reduce(
//                    array_keys($data[$params['suggester_component']]), function ($carry, $item) {
//                        $carry[] = array(
//                            'value' => $item,
//                            'label' => $item,
//                        );
//                        return $carry;
//                    }, array()
//                ) : array();

            $suggestions = array();

            if (is_array($queryResult->getDocuments())) {
                foreach ($queryResult->getDocuments() as $item) {
                    $suggestions[] = array(
                        'value' => $item['products_name'],
                        'label' => str_ireplace($queryString, '<span style="font-weight:bold;">'.ucfirst($queryString).'</span>', $item['products_name']),
                        'image' => tep_image(DIR_WS_IMAGES . ''. $item['products_image'], addslashes($item['products_name']), 30, 30, "style='padding-right: 5px; vertical-align: middle;'"),
                    );
                }
            }

        } catch (\Exception $e) { }

        return $suggestions;
    }

    /**
     * @param Query $query
     * @param null $sort
     */
    protected function addSort(Query $query, $sort = null)
    {
        if (!isset($this->sortMap[$sort])) {
            $sort = 'default';
        }
        foreach ($this->sortMap[$sort] as $sortParams) {
            $query->addSort($sortParams[0], $sortParams[1]);
        }
    }

    private function mergeDiscounts(array $discounts)
    {
        return array_reduce(
            array_keys($discounts),
            function($carry, $item) {
                if ($item <= 10) {
                    $carry[1]++;
                } elseif ($item > 10 && $item <= 30) {
                    $carry[2]++;
                } elseif ($item > 30 && $item <= 50) {
                    $carry[3]++;
                } else {
                    $carry[4]++;
                }
                return $carry;
            },
            array(1 => 0, 2 => 0, 3 => 0, 4 => 0)
        );
    }

    protected function checkRequired(array $data = array())
    {
        $select = false;
        $required = array('id', 'language_id', 'description', 'name', 'tagline');
        $int = array('id', 'language');
        foreach ($required as $field) {
            if (!isset($data[$field]) || (in_array($field, $int) && !(int)$data[$field])) {
                return $select;
            }
        }
    }

    private function filterCategories(Result $queryResult, $currentCategory = false)
    {
        $categories = $queryResult->getFacetSet()->getFacet('category_facet_names');
        $categoriesArray = is_array($categories) ? $categories : $categories->getValues();
        $categoriesIds = $queryResult->getFacetSet()->getFacet('category_facet');
        $categoriesIdsArray = is_array($categoriesIds) ? $categoriesIds : $categoriesIds->getValues();
        $result = array();
        $parents = array();

        if ($currentCategory) {

            $categoriesIdsArray = array_merge($categoriesIdsArray, array($currentCategory => 0));
            $parentsIds = explode('/', substr($currentCategory, strpos($currentCategory, '/') + 1));
            $parentsSize = sizeof($parentsIds);
            for ($i = 0; $i < $parentsSize; $i++) {
                $previous = array_slice($parentsIds, 0, $i);
                $previousIds = !empty($previous) ? implode('/', $previous) . '/' : '';
                $parentId = $i . '/' . $previousIds . $parentsIds[$i];
                $parents[$parentId] = $i;
                $categoriesIdsArray = array_merge($categoriesIdsArray, array($parentId => 0));
            }
        }
        foreach ($categoriesArray as $category => $quantity) {
            $categoryId = $this->makeSolrCategoryId($category);
            if (in_array($categoryId, array_keys($categoriesIdsArray))) {
                $lastSlashPos = strrpos($category, '/');
                $categoryName = str_replace($this->solrReplaces[1], $this->solrReplaces[0], substr($category, $lastSlashPos + 1));

                $result[$categoryId] = array(
                    'name' => $categoryName,
                    'quantity' => $quantity,
                    'current' => $categoryId == $currentCategory,
                    'parent' => isset($parents[$categoryId]) ? $parents[$categoryId] : false
                );
            }
        }
        return $result;
    }

    private function makeSolrCategoryId($category)
    {
        $parts = explode('/', $category);
        return array_reduce(array_keys($parts), function($carry, $index) use ($parts) {
            if ($index & 1) {
                $carry .= '/' . $parts[$index];
            }
            return $carry;
        }, $parts[0]);
    }
}