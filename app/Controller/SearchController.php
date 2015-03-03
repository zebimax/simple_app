<?php
namespace Controller;

use Application\Result;
use Form\Component\Field\AbstractField;
use Form\Component\Field\Select;
use Form\SearchFrom;
use Model\AbstractModel;
use Model\Mysql\Categories;
use Model\Mysql\Manufacturers;
use Model\Mysql\SolrProductsCategories;
use Model\Solarium\Solarium;

class SearchController extends ApplicationController
{
    const ADVANCED_SEARCH_URL = 'advanced_search_result_v4.php';
    const SPECIALS_URL = 'specials2.php';
    protected $manufacturerId;

    private $language = 'dutch';

    /** @var messageStack */
    private $messageStack;

    private $store;

    private $breadcrumb;

    private $page;

    private $currencies;

    private $freeShippingNl;

    private $displayedQuantity = 50;

    private $stock;

    private $special;

    protected $defaultLimit = 40;

    protected $maxLimit = 100;

    private $keywords;

    private $brandsLimit = true;

    private $defaultBrandsLimit = 30;

    private $sort;

    private $brandsFilter = array();

    private $discountsFilter = array();

    private $discountsOpen;

    private $brandsOpen;

    private $sorts = array('a', 'b', 'c', 'd', 'e', 'f');

    private $categoryId;

    private $renderCategoryId;

    private $isBrandPage = false;

    public function preDispatch()
    {
        $definedLanguage = \Macaw::getKey('language');
        $this->language = $definedLanguage ? $definedLanguage : \Macaw::getConfig('default_language');
        $this->messageStack = \Macaw::getKey('messageStack');
        $this->store = \Macaw::getKey('store');
        $this->breadcrumb = \Macaw::getKey('breadcrumb');
        $this->currencies = \Macaw::getKey('currencies');
        $this->freeShippingNl = \Macaw::getKey('freeShippingNl');
        $this->setTitle();
        $this->canonicalUrl();
        parent::preDispatch();
        $this->breadcrumbTrail[] = array('title' => HEADER_TITLE_TOP, 'link' => tep_href_link( '/' ));
        $this->stock =  (int)$this->defaultValue($this->request, 'stock');
        $this->special = (int)$this->defaultValue($this->request, 'special');

        $this->manufacturerId = (int)$this->defaultValue($this->request, 'm');
        if ($this->defaultValue($this->request, 'brands_limit') == 'all') {
            $this->brandsLimit = false;
        }
        $this->detectSort();
        $this->brandsFilter = $this->getIntArrayFromString(
            $this->defaultValue($this->request, SearchFrom::BRANDS_FILTER)
        );
        $this->discountsFilter = $this->getIntArrayFromString(
            $this->defaultValue($this->request, SearchFrom::DISCOUNTS_FILTER)
        );
        $this->brandsOpen = (int)$this->defaultValue(
            $this->request, SearchFrom::BRAND_OPEN
        );
        $this->discountsOpen = (int)$this->defaultValue(
            $this->request, SearchFrom::DISCOUNTS_OPEN
        );
        $this->page = (int)$this->defaultValue($this->request, SearchFrom::PAGE);
        $this->keywords = htmlspecialchars(trim($this->defaultValue($this->request, SearchFrom::KEYWORDS, '')));
        $this->categoryId = $this->validateCategory();
        $this->renderCategoryId = str_replace('/', '_', $this->categoryId);
        $this->modifyMobileUrl();
    }

    public function indexAction()
    {
        $this->checkKeyWords();
        $this->defaultLimit = 30;
        $defaultLimit = $this->skinId ? 24 : $this->defaultLimit;
        $oldPhpSelf = $this->getOldPhpSelf();

        /** @var Solarium $solarium */
        $solarium = $this->getModel('Solarium', AbstractModel::SOLARIUM);

        $limit = $this->getLimit();
        $start = $this->page ? ($this->page - 1) * $limit : 0;

        $result = $solarium->getProducts(
            array(
                'cKeyWords' => $this->keywords,
                'category' => $this->categoryId,
                'sort' => $this->sort,
                'special' => $this->special,
                'stock' => $this->stock,
                'start' => $start,
                'limit' => $limit,
                'brand_id' => $this->manufacturerId,
                'add_discounts' => false,
                'spell_check' => 'spelling',
                'exclude_brands' => false,
                'total' => false
            )
        );
        $this->breadcrumbTrail[] = array(
            'title' => NAVBAR_TITLE_2,
            'link' => tep_href_link(
                $oldPhpSelf,
                $this->makeLink(array('category', 'm')),
                'NONSSL',
                true,
                false
            ),
            'marked' => true
        );

        /** @var Manufacturers $manufacturersModel */
        $manufacturersModel = $this->getModel('Manufacturers');
        /** @var Categories $categoryModel*/
        $categoryModel = $this->getModel('Categories');
        $categoryInfo = $categoryModel->getCategoriesInfo(
            $result['categories'],
            array(
                'categoryId' => $this->renderCategoryId,
                'manufacturer' => $this->manufacturerId,
                'limit' => $limit,
                'back_uri' => $oldPhpSelf,
                'keywords' => $this->keywords,
                'sort' => $this->sort
            )
        );
        $brandsInfo = $manufacturersModel->getBrandsInfo(
            $result['brands'],
            array(
                'categoryId' => $this->renderCategoryId,
                'manufacturer' => $this->manufacturerId,
                'back_uri' => $oldPhpSelf,
                'limit' => $limit,
                'brandsLimit' => $this->brandsLimit,
                'defaultBrandsLimit' => $this->defaultBrandsLimit,
                'keywords' => $this->keywords,
            )
        );

        $lastNumber = $this->skinId ? 4 : 5;
        $this->addToBreadcrumb($categoryInfo['parents'], true, true);
        $this->addToBreadcrumb($brandsInfo['brand_link'], true, empty($categoryInfo['parents']));

        $searchFrom = new SearchFrom(
            $this->getSearchFormOptions($oldPhpSelf, $result['count'] > $this->defaultLimit)
        );
        $numberOfPages = ceil($result['count'] / $limit);

        $current = $this->makeLink();
        $pagination = $numberOfPages > 1
            ? $this->paginationLinks(
                MAX_DISPLAY_PAGE_LINKS,
                $current,
                $this->page ? $this->page : 1,
                $numberOfPages
            ) : '';
        $this->render(
            array(
                'categories' => $categoryInfo['categories'],
                'brands' => $brandsInfo['brands'],
                'leftBackLink' => $categoryInfo['leftBackLink'],
                'suggestionsUrls' => $solarium->makeSuggestionsUrls($result['suggestions']),
                'limit' => $limit,
                'default_limit' => $defaultLimit,
                'products' => $this->makeProducts($result['documents'], $start, $lastNumber),
                'productsCount' => $result['count'],
                'clearCategoryLink' => $categoryInfo['clearCategoryLink'],
                'leftBrandBackLink' => $brandsInfo['leftBrandBackLink'],
                'breadcrumb' => $this->breadcrumbTrail,
                'searchForm' => $searchFrom->make(),
                'pagination' => $pagination,
            ), '', $this->getLayout()
        );
    }

    public function brandAction()
    {
        $this->isBrandPage = true;
        $this->checkKeyWords();
        $this->defaultLimit = 30;
        $defaultLimit = $this->skinId ? 24 : $this->defaultLimit;
        $oldPhpSelf = $this->getOldPhpSelf();

        /** @var Solarium $solarium */
        $solarium = $this->getModel('Solarium', AbstractModel::SOLARIUM);

        $limit = $this->getLimit();
        $start = $this->page ? ($this->page - 1) * $limit : 0;

        $result = $solarium->getProducts(
            array(
                'cKeyWords' => $this->keywords,
                'category' => $this->categoryId,
                'sort' => $this->sort,
                'special' => $this->special,
                'stock' => $this->stock,
                'start' => $start,
                'limit' => $limit,
                'brand_id' => $this->manufacturerId,
                'add_discounts' => false,
                'exclude_brands' => false,
                'total' => false
            )
        );
        if (!empty($result['brands'])) {
            reset($result['brands']);
            $this->breadcrumbTrail[] = array(
                'title' => key($result['brands']),
                'link' => tep_href_link(
                    $oldPhpSelf,
                    $this->makeLink(array('category', 'm')),
                    'NONSSL',
                    true,
                    false
                ),
                'marked' => true
            );
        }

        /** @var Categories $categoryModel*/
        $categoryModel = $this->getModel('Categories');
        $categoryInfo = $categoryModel->getCategoriesInfo(
            $result['categories'],
            array(
                'categoryId' => $this->renderCategoryId,
                'manufacturer' => false,
                'limit' => $limit,
                'back_uri' => $oldPhpSelf,
                'keywords' => $this->keywords,
                'sort' => $this->sort
            )
        );

        $lastNumber = $this->skinId ? 4 : 5;
        $this->addToBreadcrumb($categoryInfo['parents'], true, true);

        $searchFrom = new SearchFrom(
            $this->getSearchFormOptions($oldPhpSelf, $result['count'] > $this->defaultLimit)
        );
        $numberOfPages = ceil($result['count'] / $limit);

        $current = $this->makeLink(array('m'));
        $pagination = $numberOfPages > 1
            ? $this->paginationLinks(
                MAX_DISPLAY_PAGE_LINKS,
                $current,
                $this->page ? $this->page : 1,
                $numberOfPages
            ) : '';
        $this->render(
            array(
                'categories' => $categoryInfo['categories'],
                'leftBackLink' => $categoryInfo['leftBackLink'],
                'suggestionsUrls' => $solarium->makeSuggestionsUrls($result['suggestions']),
                'limit' => $limit,
                'default_limit' => $defaultLimit,
                'products' => $this->makeProducts($result['documents'], $start, $lastNumber),
                'productsCount' => $result['count'],
                'clearCategoryLink' => $categoryInfo['clearCategoryLink'],
                'breadcrumb' => $this->breadcrumbTrail,
                'searchForm' => $searchFrom->make(),
                'pagination' => $pagination
            ), '', $this->getLayout()
        );
    }

    public function specialsAction()
    {
        require DIR_WS_LANGUAGES . $this->language . '/' . FILENAME_SPECIALS;
        $this->breadcrumbTrail[] = array(
            'title' => HEADER_TITLE_AANBIEDINGEN,
            'link' => tep_href_link(FILENAME_SPECIALS),
            'marked' => true
        );

        $limit = $this->getLimit();

        /** @var Solarium $solarium */
        $solarium = $this->getModel('Solarium', AbstractModel::SOLARIUM);

        $start = $this->page ? ($this->page - 1) * $limit : 0;
        $result = $solarium->getProducts(
            array(
                'brandFilter' => $this->brandsFilter,
                'discounts' => $this->discountsFilter,
                'cKeyWords' => $this->keywords,
                'category' => $this->categoryId,
                'brand_id' => $this->manufacturerId,
                'sort' => $this->sort,
                'special' => true,
                'stock' => $this->stock,
                'start' => $start,
                'limit' => $limit,
                'exclude_categories' => false,
                'exclude_brands' => !$this->manufacturerId
            )
        );

        /** @var Categories $categoriesModel */
        $categoriesModel = $this->getModel('Categories');

        /** @var Manufacturers $brandsModel */
        $brandsModel = $this->getModel('Manufacturers');

        $categoryInfo = $categoriesModel->getCategoriesInfo(
            $result['categories'],
            array(
                'categoryId' => $this->renderCategoryId,
                'manufacturer' => $this->manufacturerId,
                'limit' => $limit,
            )
        );

        $brandsInfo = $brandsModel->getBrandsInfo(
            $result['brands'],
            array(
                'categoryId' => $this->renderCategoryId,
                'manufacturer' => $this->manufacturerId,
                'limit' => $limit,
                'brandsLimit' => $this->brandsLimit,
                'defaultBrandsLimit' => $this->defaultBrandsLimit
            )
        );
        $this->addToBreadcrumb($categoryInfo['parents'], true, true);
        $this->addToBreadcrumb($brandsInfo['brand_link'], true, empty($categoryInfo['parents']));
        $searchForm = new SearchFrom(
            $this->getSearchFormOptions(
                $this->getOldPhpSelf(),
                $result['count'] > $this->defaultLimit,
                $brandsInfo['brands'],
                $result['discounts']
            )
        );
        $numberOfPages = ceil($result['count'] / $limit);
        $pagination = $numberOfPages > 1
            ? $this->paginationLinks(
                MAX_DISPLAY_PAGE_LINKS,
                $this->makeLink(),
                $this->page ? $this->page : 1,
                $numberOfPages
            ) : '';
        $this->render(
            array(
                'categories' => $categoryInfo['categories'],
                'leftBackLink' => $categoryInfo['leftBackLink'],
                'clearCategoryLink' => $categoryInfo['clearCategoryLink'],
                'brands' => $brandsInfo['brands'],
                'leftBrandBackLink' => $brandsInfo['leftBrandBackLink'],
                'productsCount' => $result['count'],
                'defaultLimit' => $this->defaultLimit,
                'products' => $this->makeProducts($result['documents'], $start),
                'breadcrumb' => $this->breadcrumbTrail,
                'searchForm' => $searchForm->make(),
                'showBrands' => true,
                'pagination' => $pagination
            ), '', $this->getLayout()
        );
    }

    public function suggestionsAction()
    {
        /** @var Solarium $solarium */
        $solarium = $this->getModel('Solarium', AbstractModel::SOLARIUM);
        $this->renderJson(Result::create(array(
            Result::PARAM_DATA => $solarium->getSuggestions($this->defaultValue($_GET, 'term', '')),
            Result::PARAM_SUCCESS => true
        )));
    }

    protected function addToBreadcrumb(array $parents, $markLast = false, $removePreviousMarks = false)
    {
        if (!empty($parents)) {
            $this->breadcrumbTrail = array_merge($this->breadcrumbTrail, $parents);
            if ($removePreviousMarks) {
                foreach ($this->breadcrumbTrail as &$item) {
                    $item['marked'] = false;
                }
            }
            if ($markLast) {
                end($this->breadcrumbTrail);
                $this->breadcrumbTrail[key($this->breadcrumbTrail)]['marked'] = true;
            }
        }
    }

    protected function getLayout()
    {
        $layout = 'main';
        return $layout;
    }

    protected function setTitle()
    {
        switch($this->action) {
            case 'index':
                if (!$this->manufacturerId) {
                    $this->title = HEAD_TITLE_TAG_ALL . " | " . HEAD_TITLE_KOOPINTENTIE . " | " . HEAD_TITLE_TAG_DEFAULT;
                } else {
                    /** @var Manufacturers $brandModel */
                    $brandModel = $this->getModel('Manufacturers');

                    if ($brandDesc = $brandModel->getBrandDescription($this->manufacturerId, $this->languageId)) {
                        $categoryPart = $brandDesc['name'];
                        $this->description = $brandDesc['htc_desc_tag'];
                        $this->keyw = $brandDesc['htc_keyw_tag'];

                    } else {
                        $categoryPart = \Macaw::getKey('default_title_category');
                    }

                    $this->title = $categoryPart == 'De Online Drogist'
                        ? sprintf('%s | %s', $categoryPart, HEAD_TITLE_KOOPINTENTIE)
                        : sprintf('%s | %s | %s', $categoryPart, HEAD_TITLE_KOOPINTENTIE, HEAD_TITLE_TAG_DEFAULT);
                }
                break;
            case 'specials':
                $this->title = 'Speciale aanbiedingen' . ' | ' . HEAD_TITLE_KOOPINTENTIE . ' | ' . HEAD_TITLE_TAG_DEFAULT;
                break;
            default:
                $this->title = HEAD_TITLE_TAG_ALL . " | " . HEAD_TITLE_KOOPINTENTIE . " | " . HEAD_TITLE_TAG_DEFAULT;
                break;
        }
    }

    protected function modifyMobileUrl()
    {
        if ($this->old_php_self == self::ADVANCED_SEARCH_URL && $this->keywords) {
            $this->mobileUrl = $this->mobileUrlBase . 'search/?search=' . $this->keywords;
        } elseif ($this->old_php_self == self::SPECIALS_URL) {
            $this->mobileUrl = $this->mobileUrlBase . 'special';
        }
    }

    /**
     * @param array $products
     * @return array //todo make this for all routes with products
     */
    private function makeProducts(array $products = array(), $start, $last = 4)
    {
        $productCounter = 0;
        $result = array();
        $productsIds = array_reduce($products, function($carry, $item) {
            $carry []= $item['products_id'];
            return $carry;
        }, array());
        /** @var SolrProductsCategories $solrProductsCategories*/
        $solrProductsCategories = $this->getMysqlModel('SolrProductsCategories');
        $solrCategories = $solrProductsCategories->getProductsCategories($productsIds, $this->languageId);
        $categories = array();
        if (is_array($solrCategories)) {
            $categories = array_reduce($solrCategories, function($carry, $item) {
                $carry[$item['id']] = array_slice(
                    explode(
                        '/',
                        str_replace(
                            array('|', '_'),
                            array('&', ' '),
                            substr(
                                $item['solr_cats_names'],
                                strrpos($item['solr_cats_names'], ', ') + 2
                            )
                        )
                    ),
                    2
                );
                return $carry;
            }, array());
        }
        $catsNamesAllowed = array(0, 2, 4, 6, 8);

        foreach ($products as $row) {
            if($row['specials_new_products_price']) {
                $priceValue = tep_add_tax($row['specials_new_products_price'], tep_get_tax_rate($row['products_tax_class_id']));
                $oldPrice = $this->currencies->display_price($row['products_price'], tep_get_tax_rate($row['products_tax_class_id']));
            }else {
                $priceValue = tep_add_tax($row['products_price'], tep_get_tax_rate($row['products_tax_class_id']));
                $oldPrice = '';
            }
            $price = $this->currencies->display_price($row['products_price'], tep_get_tax_rate($row['products_tax_class_id']));
            $product = array(
                'id' => $row['products_id'],
                'name' => $row['products_name'],
                'old_price' => $oldPrice,
                'price' => str_replace(',' , ',', $price),
                'new_price' => $this->currencies->display_price($priceValue, 0),
                'isLast' => ($productCounter + 1) % $last == 0,
                'freeShipping' => ($row["products_freeshipping"] || round($priceValue, 2) > $this->freeShippingNl),
                'discount' => $row['products_price'] ? round((1 - $row['specials_new_products_price'] / $row['products_price']) * 100) : 0,
                'image' => tep_image(DIR_WS_IMAGES . $row['products_image'], $row['products_name'], 160, 110),
                'volume' => $row['inhoud'],
                'quantity' => $row['products_quantity'],
                'quantity_for_display' => $row['products_quantity'] > $this->displayedQuantity ? $this->displayedQuantity : $row['products_quantity'],
                'link' => tep_href_link(FILENAME_PRODUCT_INFO, 'products_id=' . $row['products_id']),
                'min_order_qty' => $row['products_min_order_qty'],
                'brand_name' => $row['manufacturers_name'],
                'category' => isset($categories[$row['products_id']])
                    ? implode('/', array_filter(array_keys($categories[$row['products_id']]),
                                                function($val) use ($catsNamesAllowed) {
                                                    return in_array($val, $catsNamesAllowed);
                                                }))
                    : '',
                'position' => ++$start
            );
            $productCounter++;
            $result[] = $product;
        }
        return $result;
    }

    /**
     * @return int
     */
    protected function getLimit()
    {
        return (int)$this->defaultValue($this->request, 'limit') == 100
            ? 100
            : $this->defaultLimit;
    }

    /**
     * @return string
     */
    private function validateCategory()
    {
        $category = $this->defaultValue($this->request, 'category');
        return implode('/', array_filter(explode('_', $category), 'is_numeric'));
    }

    private function checkKeyWords()
    {
        $keywords = array();
        if (tep_not_null($this->keywords)) {
            if (!tep_parse_search_string($this->keywords, $keywords)) {
                $this->messageStack->add_session('search', ERROR_INVALID_KEYWORDS);
            }
        }
        $only_up = true;
        for ($i = 0, $n = sizeof($keywords); $i < $n; $i++) {
            if ($keywords[$i] == "or")
                continue;
            else if (!is_numeric($keywords[$i]) || strlen($keywords[$i]) != 6)
                $only_up = false;
            else
                $up_nrs[] = tep_db_input($keywords[$i]);
        }
        if ($only_up && $this->keywords) {
            tep_redirect(tep_href_link("advanced_search_result_v2.php", tep_get_all_get_params(array("force")) . "force=1"));
        }

        $this->keywords = str_replace(array('kopen', 'online'), array('', ''), $this->keywords);
    }

    /**
     * @param $oldPhpSelf
     * @param $showLimitOptions
     * @param array $brands
     * @param array $discounts
     * @return array
     */
    private function getSearchFormOptions($oldPhpSelf, $showLimitOptions, array $brands = array(), array $discounts = array())
    {
        $formOptions = array();
        if ($oldPhpSelf !== self::ADVANCED_SEARCH_URL) {
            $formOptions[] = array('name' => SearchFrom::START_SEARCH_BAR_DIV);
            $formOptions[] = array('name' => SearchFrom::KEYWORDS, 'params' => array(AbstractField::FIELD_VALUE => $this->keywords));
            $formOptions[] = array('name' => SearchFrom::SEARCH_SUBMIT);
            $formOptions[] = array('name' => SearchFrom::END_DIV);
        }
        $formOptions[] = array('name' => SearchFrom::CLEAR_DIV);
        if ($oldPhpSelf == self::SPECIALS_URL) {
            $filterConfig = \Macaw::getConfig('search_filter_options');
            if (!$this->manufacturerId) {
                $filterConfig['brands']['head']['is_open'] = $filterConfig['brands']['options']['is_open'] = $this->brandsOpen;

                $filterConfig['brands']['options']['options'] = $brands;
                $filterConfig['brands']['head']['selected'] = $this->extractItemsFromArrayByIntKey(
                    $this->brandsFilter,
                    $brands
                );
                $filterConfig['brands']['options']['selected'] = $this->brandsFilter;
            } else {
                unset($filterConfig['brands']);
            }

            $filterConfig['discounts']['head']['is_open'] = $filterConfig['discounts']['options']['is_open'] = $this->discountsOpen;
            $filterConfig['discounts']['options']['selected'] = $this->discountsFilter;
            $filterConfig['discounts']['options']['options'] = $this->extractItemsFromArrayByIntKey(
                array_keys(array_filter($discounts)),
                $filterConfig['discounts']['options']['options']
            );

            $formOptions[] = array(
                'name' => SearchFrom::CHECK_BOX_FILTERS,
                'params' => $filterConfig
            );
            $formOptions[] = array('name' => SearchFrom::CLEAR_DIV);
            $formOptions[] = array('name' => SearchFrom::SORT, 'params' => array(AbstractField::FIELD_VALUE => $this->sort));
            $formOptions[] = array('name' => SearchFrom::LIMIT, 'params' => array(AbstractField::FIELD_VALUE => $this->getLimit()));
            $formOptions[] = array('name' => SearchFrom::BRAND_OPEN, 'params' => array(AbstractField::FIELD_VALUE => $this->brandsOpen));
            $formOptions[] = array('name' => SearchFrom::BRANDS_FILTER, 'params' => array(AbstractField::FIELD_VALUE => $this->brandsFilter));
            $formOptions[] = array('name' => SearchFrom::DISCOUNTS_OPEN, 'params' => array(AbstractField::FIELD_VALUE => $this->discountsOpen));
            $formOptions[] = array('name' => SearchFrom::DISCOUNTS_FILTER, 'params' => array(AbstractField::FIELD_VALUE => $this->discountsFilter));
        }

        $searchSortOptions = \Macaw::getConfig('search_sort_options');
        $formOptions[] = array(
            'name' => SearchFrom::SORT_OPTIONS,
            'params' => array(
                Select::SELECT_OPTIONS => $oldPhpSelf == self::ADVANCED_SEARCH_URL
                    ? $searchSortOptions['default']
                    : $searchSortOptions['specials'],
                Select::SELECT_SELECTED => $this->sort
            )
        );
        if ($showLimitOptions) {
            $formOptions[] = array(
                'name' => SearchFrom::LIMIT_OPTIONS,
                'params' => array(
                    Select::SELECT_OPTIONS => array(
                        array('name' => $this->defaultLimit, 'value' => $this->defaultLimit),
                        array('name' => $this->maxLimit, 'value' => $this->maxLimit),
                    ),
                    Select::SELECT_BEFORE => '<strong>',
                    Select::SELECT_AFTER => '</strong> per pagina',
                    Select::SELECT_SELECTED => $this->getLimit()
                )
            );
        }
        $formOptions[] = array(
            'name' => SearchFrom::CATEGORY,
            'params' => array(AbstractField::FIELD_VALUE => $this->renderCategoryId)
        );
        if (!$this->isBrandPage) {
            $formOptions[] = array(
                'name' => SearchFrom::MANUFACTURER,
                'params' => array(AbstractField::FIELD_VALUE => $this->manufacturerId)
            );
        }
        return $formOptions;
    }

    /**
     * @param $string
     * @param string $delimiter
     * @return array
     */
    private function getIntArrayFromString($string, $delimiter = ',')
    {
        return array_reduce(
            explode($delimiter, $string),
            function ($carry, $item) {
                if ($int = (int)$item) {
                    $carry[] = $int;
                }
                return $carry;
            }, array()
        );
    }

    /**
     * @param array $toExtract
     * @param array $array
     * @return mixed
     */
    private function extractItemsFromArrayByIntKey(array $toExtract, array $array)
    {
        return array_reduce(
            $toExtract,
            function ($carry, $item) use ($array) {
                $key = (int)$item;
                if (isset($array[$key])) {
                    $carry[] = array(
                        'id' => $array[$key]['id'],
                        'name' => $array[$key]['name']
                    );
                }

                return $carry;
            },
            array()
        );
    }

    private function detectSort()
    {
        $defaultSort = $this->getOldPhpSelf() == self::ADVANCED_SEARCH_URL ? '' : 'd';
        $sort = $this->defaultValue($this->request, 'sort', $defaultSort);
        if (in_array($sort, $this->sorts)) {
            $this->sort = $sort;
        }
    }

    /**
     * @param array $excluded
     * @return mixed
     */
    private function makeLink(array $excluded = array())
    {
        $paramsArray = array(
            'category' => $this->renderCategoryId,
            'brands_filter' => implode(',', $this->brandsFilter),
            'discounts_filter' => implode(',', $this->discountsFilter),
            'brand_open' => $this->brandsOpen,
            'discounts_open' => $this->discountsOpen,
            'sort' => $this->sort,
            'limit' => $this->getLimit(),
            'skin' => $this->skinId,
            'stock' => $this->stock,
            'keywords' => $this->keywords,
            'm' => $this->manufacturerId
        );
        return $this->linkMaker(array_diff_key($paramsArray, array_flip($excluded)));
    }
}